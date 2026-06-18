<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientType;
use App\Models\Customer;
use App\Services\ClientTypeImportMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CustomerController extends Controller
{
    public function __construct(
        private readonly ClientTypeImportMapper $clientTypeImportMapper,
    ) {}

    /**
     * Mostrar la lista de clientes con buscador y filtros.
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('client_type') && $request->client_type !== 'ALL') {
            $query->byClientType($request->client_type);
        }

        if ($request->filled('type_lock') && $request->type_lock !== 'ALL') {
            $query->where('client_type_locked', $request->type_lock === 'locked');
        }

        $customers = $query->with('catalogClientType')->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $clientTypes = ClientType::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.customers.index', compact('customers', 'clientTypes'));
    }

    /**
     * Importar clientes masivamente desde un archivo CSV (Soporta Actualizaciones Parciales).
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ], [
            'csv_file.required' => 'Por favor selecciona un archivo CSV.',
            'csv_file.mimes' => 'El archivo debe ser un CSV válido.',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle, 1000, ',');

        if (!$header) {
            return back()->withErrors(['csv_file' => 'El archivo está vacío o no se pudo leer.']);
        }

        $header[0] = preg_replace('/\x{FEFF}/u', '', $header[0]);
        $header = array_map('strtolower', $header);
        $header = array_map('trim', $header);

        if (!in_array('numero_cliente', $header)) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'El CSV debe contener obligatoriamente la columna: "numero_cliente".']);
        }

        $usesCodigoLista = in_array('codigo_lista', $header, true);
        $imported = 0;
        $updated = 0;
        $unrecognized = 0;
        $typeSkippedLocked = 0;

        while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {

            if(count($header) !== count($row)) continue;

            $data = array_combine($header, $row);
            $numeroCliente = trim($data['numero_cliente'] ?? '');

            if (empty($numeroCliente)) continue;

            $customer = Customer::where('customer_number', $numeroCliente)->first();
            $listInput = $this->listInputFromRow($data, $usesCodigoLista);
            $typeResult = $this->clientTypeImportMapper->resolveWithMeta($listInput);

            if ($listInput !== null && trim($listInput) !== '' && !$typeResult['recognized']) {
                $unrecognized++;
            }

            if ($customer) {
                $updateData = [];

                if (isset($data['nombre']) && trim($data['nombre']) !== '') {
                    $updateData['name'] = trim($data['nombre']);
                }

                if (array_key_exists('telefono', $data)) {
                    $updateData['phone'] = trim($data['telefono']) === '' ? null : trim($data['telefono']);
                }

                if (array_key_exists('email', $data)) {
                    $updateData['email'] = trim($data['email']) === '' ? null : trim($data['email']);
                }

                if ($this->rowHasListAssignment($data, $usesCodigoLista)) {
                    if ($customer->client_type_locked) {
                        $typeSkippedLocked++;
                    } else {
                        $updateData = array_merge($updateData, $this->clientTypeAttributes($typeResult['type']));
                    }
                }

                if (!empty($updateData)) {
                    $customer->update($updateData);
                    $updated++;
                }

            } else {
                $nombre = trim($data['nombre'] ?? '');

                if (empty($nombre)) continue;

                Customer::create(array_merge([
                    'customer_number' => $numeroCliente,
                    'name' => $nombre,
                    'phone' => (array_key_exists('telefono', $data) && trim($data['telefono']) !== '') ? trim($data['telefono']) : null,
                    'email' => (array_key_exists('email', $data) && trim($data['email']) !== '') ? trim($data['email']) : null,
                ], $this->clientTypeAttributes($typeResult['type'])));

                $imported++;
            }
        }

        fclose($handle);

        $message = "Proceso finalizado. Clientes Nuevos: {$imported} | Actualizados: {$updated} | Sin lista reconocida: {$unrecognized} | Tipos omitidos por bloqueo: {$typeSkippedLocked}";

        return back()->with('success', $message);
    }

    /**
     * Actualizar un solo cliente manualmente desde la interfaz.
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'customer_number' => 'nullable|string|unique:customers,customer_number,' . $customer->id,
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'client_type' => 'required|string',
            'client_type_locked' => 'nullable|boolean',
        ]);

        $resolved = ClientType::resolveFromInput($request->client_type);
        if (!$resolved) {
            return back()->withErrors(['client_type' => 'Tipo de cliente inválido.']);
        }

        $willBeLocked = $request->boolean('client_type_locked');
        $currentTypeCode = $customer->resolveClientTypeCode();
        $typeChanging = $resolved->code !== $currentTypeCode;

        if ($customer->client_type_locked && $willBeLocked && $typeChanging) {
            return back()->withErrors(['client_type' => 'El tipo de cliente está bloqueado y no puede modificarse.']);
        }

        $customer->update([
            'customer_number' => $request->customer_number,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'client_type' => $resolved->code,
            'client_type_locked' => $willBeLocked,
        ]);

        return back()->with('success', 'Cliente actualizado correctamente.');
    }

    private function listInputFromRow(array $data, bool $usesCodigoLista): ?string
    {
        if ($usesCodigoLista && array_key_exists('codigo_lista', $data)) {
            return $data['codigo_lista'];
        }

        if (array_key_exists('tipo_cliente', $data)) {
            return $data['tipo_cliente'];
        }

        return null;
    }

    private function rowHasListAssignment(array $data, bool $usesCodigoLista): bool
    {
        if ($usesCodigoLista && array_key_exists('codigo_lista', $data)) {
            return true;
        }

        return array_key_exists('tipo_cliente', $data);
    }

    private function clientTypeAttributes(ClientType $type): array
    {
        $attrs = ['client_type_id' => $type->id];

        if (Schema::hasColumn('customers', 'client_type')) {
            $attrs['client_type'] = $type->code;
        }

        return $attrs;
    }
}
