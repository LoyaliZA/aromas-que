<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AbandonmentReason;
use App\Models\Bank;
use App\Models\Courier;
use App\Models\Warehouse;

class CatalogController extends Controller
{
    private function getCatalogConfig(string $type)
    {
        return match($type) {
            'bank' => ['model' => Bank::class, 'column' => 'name'],
            'courier' => ['model' => Courier::class, 'column' => 'name'],
            'warehouse' => ['model' => Warehouse::class, 'column' => 'name'],
            'abandonment_reason' => ['model' => AbandonmentReason::class, 'column' => 'reason'],
            default => null,
        };
    }

    public function index()
    {
        $banks = Bank::orderBy('name', 'asc')->get();
        $couriers = Courier::orderBy('name', 'asc')->get();
        $warehouses = Warehouse::orderBy('name', 'asc')->get();
        $abandonmentReasons = AbandonmentReason::orderBy('reason', 'asc')->get();

        return view('admin.settings.catalogs', compact('banks', 'couriers', 'warehouses', 'abandonmentReasons'));
    }

    public function store(Request $request)
    {
        $config = $this->getCatalogConfig($request->catalog_type);
        if (!$config) return back()->withErrors(['error' => 'Catálogo inválido.']);

        $request->validate(['value' => 'required|string|max:100']);

        $config['model']::create([
            $config['column'] => trim($request->value),
            'is_active' => true,
        ]);

        return back()->with('success', 'Registro agregado correctamente.')->with('active_tab', $request->catalog_type);
    }

    public function update(Request $request, $id)
    {
        $config = $this->getCatalogConfig($request->catalog_type);
        if (!$config) return back()->withErrors(['error' => 'Catálogo inválido.']);

        $request->validate(['value' => 'required|string|max:100']);

        $item = $config['model']::findOrFail($id);
        $item->update([$config['column'] => trim($request->value)]);

        return back()->with('success', 'Registro actualizado correctamente.')->with('active_tab', $request->catalog_type);
    }

    public function toggle(Request $request, $id)
    {
        $config = $this->getCatalogConfig($request->catalog_type);
        if (!$config) return back()->withErrors(['error' => 'Catálogo inválido.']);

        $item = $config['model']::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);

        $status = $item->is_active ? 'activado' : 'desactivado';
        return back()->with('success', "Registro {$status} correctamente.")->with('active_tab', $request->catalog_type);
    }

    /**
     * Elimina un registro permanentemente de la base de datos.
     */
    public function destroy(Request $request, $id)
    {
        $config = $this->getCatalogConfig($request->catalog_type);
        if (!$config) return back()->withErrors(['error' => 'Catálogo inválido.']);

        $item = $config['model']::findOrFail($id);
        
        try {
            $item->delete();
            return back()->with('success', 'Registro eliminado permanentemente.')->with('active_tab', $request->catalog_type);
        } catch (\Exception $e) {
            // Protección por si intentas borrar un registro que ya está en uso por otra tabla
            return back()->withErrors(['error' => 'No se puede eliminar el registro porque ya está en uso. Te sugerimos desactivarlo.'])->with('active_tab', $request->catalog_type);
        }
    }
}