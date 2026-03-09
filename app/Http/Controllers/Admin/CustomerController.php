<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
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
            $query->where('client_type', $request->client_type);
        }

        $customers = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('admin.customers.index', compact('customers'));
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

        // Limpieza de encabezados
        $header[0] = preg_replace('/\x{FEFF}/u', '', $header[0]);
        $header = array_map('strtolower', $header);
        $header = array_map('trim', $header);

        // Validación: Solo numero_cliente es estrictamente necesario para que el archivo sea procesable
        if (!in_array('numero_cliente', $header)) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'El CSV debe contener obligatoriamente la columna: "numero_cliente".']);
        }

        $imported = 0;
        $updated = 0;

        while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
            
            if(count($header) !== count($row)) continue; 
            
            $data = array_combine($header, $row);
            $numeroCliente = trim($data['numero_cliente'] ?? '');
            
            if (empty($numeroCliente)) continue; 

            // Buscamos si el cliente ya existe
            $customer = Customer::where('customer_number', $numeroCliente)->first();

            if ($customer) {
                // ACTUALIZACIÓN PARCIAL: Solo actualiza las columnas que vengan en el CSV
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
                
                if (array_key_exists('tipo_cliente', $data)) {
                    $tipo = strtoupper(trim($data['tipo_cliente']));
                    $updateData['client_type'] = in_array($tipo, ['REGULAR', 'VIP', 'DISCAPACITY']) ? $tipo : 'REGULAR';
                }

                if (!empty($updateData)) {
                    $customer->update($updateData);
                    $updated++;
                }

            } else {
                // CREACIÓN DE NUEVO CLIENTE: Requiere nombre forzosamente
                $nombre = trim($data['nombre'] ?? '');
                
                if (empty($nombre)) continue; // Si no hay nombre, ignoramos la fila para no crear registros corruptos
                
                $tipo = strtoupper(trim($data['tipo_cliente'] ?? 'REGULAR'));
                $tipo = in_array($tipo, ['REGULAR', 'VIP', 'DISCAPACITY']) ? $tipo : 'REGULAR';

                Customer::create([
                    'customer_number' => $numeroCliente,
                    'name' => $nombre,
                    'phone' => (array_key_exists('telefono', $data) && trim($data['telefono']) !== '') ? trim($data['telefono']) : null,
                    'email' => (array_key_exists('email', $data) && trim($data['email']) !== '') ? trim($data['email']) : null,
                    'client_type' => $tipo,
                ]);
                
                $imported++;
            }
        }

        fclose($handle);

        return back()->with('success', "Proceso finalizado. Clientes Nuevos: {$imported} | Actualizados: {$updated}");
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
            'client_type' => 'required|in:REGULAR,VIP,DISCAPACITY',
        ]);

        $customer->update($request->all());

        return back()->with('success', 'Cliente actualizado correctamente.');
    }
}