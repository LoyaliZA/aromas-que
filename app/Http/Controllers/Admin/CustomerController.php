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
    private const DEBUG_LOG_PATH = null; // resolved via debugLogPath()

    public function __construct(
        private readonly ClientTypeImportMapper $clientTypeImportMapper,
    ) {}

    /**
     * Mostrar la lista de clientes con buscador y filtros.
     */
    public function index(Request $request)
    {
        // #region agent log
        $indexStartedAt = microtime(true);
        $this->debugLog('CustomerController.php:index:entry', 'customers index started', [
            'hypothesisId' => 'A,B',
            'hasSearch' => $request->filled('search'),
            'hasClientTypeFilter' => $request->filled('client_type') && $request->client_type !== 'ALL',
        ]);
        // #endregion

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

        $customers = $query->with('catalogClientType')->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // #region agent log
        $this->debugLog('CustomerController.php:index:afterPaginate', 'customers paginate completed', [
            'hypothesisId' => 'A,B',
            'elapsedMs' => (int) round((microtime(true) - $indexStartedAt) * 1000),
            'totalCustomers' => $customers->total(),
            'currentPage' => $customers->currentPage(),
            'itemsOnPage' => $customers->count(),
        ]);
        // #endregion

        $clientTypes = ClientType::where('is_active', true)->orderBy('sort_order')->get();

        // #region agent log
        $this->debugLog('CustomerController.php:index:exit', 'customers index ready to render', [
            'hypothesisId' => 'A,B,E',
            'elapsedMs' => (int) round((microtime(true) - $indexStartedAt) * 1000),
            'clientTypesCount' => $clientTypes->count(),
        ]);
        // #endregion

        return view('admin.customers.index', compact('customers', 'clientTypes'));
    }

    /**
     * Importar clientes masivamente desde un archivo CSV (Soporta Actualizaciones Parciales).
     */
    public function importCsv(Request $request)
    {
        // #region agent log
        $importStartedAt = microtime(true);
        $this->debugLog('CustomerController.php:importCsv:entry', 'csv import started', [
            'hypothesisId' => 'C,D,E',
        ]);
        // #endregion

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
        $rowCount = 0;
        $dbLookups = 0;
        $writes = 0;

        while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $rowCount++;

            if(count($header) !== count($row)) continue;

            $data = array_combine($header, $row);
            $numeroCliente = trim($data['numero_cliente'] ?? '');

            if (empty($numeroCliente)) continue;

            $customer = Customer::where('customer_number', $numeroCliente)->first();
            $dbLookups++;
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
                    $updateData = array_merge($updateData, $this->clientTypeAttributes($typeResult['type']));
                }

                if (!empty($updateData)) {
                    $customer->update($updateData);
                    $updated++;
                    $writes++;
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
                $writes++;
            }

            // #region agent log
            if ($rowCount === 1 || $rowCount % 500 === 0) {
                $this->debugLog('CustomerController.php:importCsv:progress', 'csv import progress', [
                    'hypothesisId' => 'C,D',
                    'rowCount' => $rowCount,
                    'dbLookups' => $dbLookups,
                    'writes' => $writes,
                    'elapsedMs' => (int) round((microtime(true) - $importStartedAt) * 1000),
                ]);
            }
            // #endregion
        }

        fclose($handle);

        // #region agent log
        $this->debugLog('CustomerController.php:importCsv:completed', 'csv import finished before redirect', [
            'hypothesisId' => 'C,D,E',
            'rowCount' => $rowCount,
            'imported' => $imported,
            'updated' => $updated,
            'unrecognized' => $unrecognized,
            'dbLookups' => $dbLookups,
            'writes' => $writes,
            'elapsedMs' => (int) round((microtime(true) - $importStartedAt) * 1000),
        ]);
        // #endregion

        $message = "Proceso finalizado. Clientes Nuevos: {$imported} | Actualizados: {$updated} | Sin lista reconocida: {$unrecognized}";

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
        ]);

        $resolved = ClientType::resolveFromInput($request->client_type);
        if (!$resolved) {
            return back()->withErrors(['client_type' => 'Tipo de cliente inválido.']);
        }

        $customer->update([
            'customer_number' => $request->customer_number,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'client_type' => $resolved->code,
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

    private function debugLog(string $location, string $message, array $data = []): void
    {
        // #region agent log
        $payload = array_merge([
            'sessionId' => 'b2f1c7',
            'timestamp' => (int) round(microtime(true) * 1000),
            'location' => $location,
            'message' => $message,
            'runId' => 'pre-fix',
        ], $data);

        @file_put_contents($this->debugLogPath(), json_encode($payload, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
        // #endregion
    }

    private function debugLogPath(): string
    {
        return base_path('.cursor/debug-b2f1c7.log');
    }
}
