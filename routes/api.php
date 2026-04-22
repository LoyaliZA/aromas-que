<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Customer;
use App\Models\Employee;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Estas rutas alimentarán a la Nueva App de Pedidos en el futuro.
| Son de solo lectura y están aisladas del sistema web.
*/

Route::middleware('auth:sanctum')->group(function () {

    // 1. Endpoint para buscar clientes (Misma lógica que usamos en Livewire)
    Route::get('/customers', function (Request $request) {
        $query = Customer::query();

        if ($request->has('search')) {
            $searchTerm = trim($request->search);
            if (strlen($searchTerm) >= 2 || (is_numeric($searchTerm) && strlen($searchTerm) >= 1)) {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('customer_number', $searchTerm);
            }
        }

        return response()->json($query->limit(10)->get());
    });

    // 2. Endpoint para obtener vendedoras por departamento
    Route::get('/employees', function (Request $request) {
        $query = Employee::where('is_active', true);

        if ($request->has('department')) {
            $query->where('department', strtoupper($request->department));
        }

        return response()->json($query->orderBy('full_name', 'asc')->get());
    });

    // 3. Endpoint de validación de conexión
    Route::get('/ping', function () {
        return response()->json(['status' => 'Conexión a TERA exitosa', 'timestamp' => now()]);
    });
});
