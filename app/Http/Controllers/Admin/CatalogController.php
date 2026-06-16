<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbandonmentReason;
use App\Models\Bank;
use App\Models\BreakReason;
use App\Models\ClientType;
use App\Models\Courier;
use App\Models\Department;
use App\Models\JobPosition;
use App\Models\Role;
use App\Models\ServiceType;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    private function getCatalogConfig(string $type)
    {
        return match($type) {
            'bank' => ['model' => Bank::class, 'column' => 'name', 'protected' => []],
            'courier' => ['model' => Courier::class, 'column' => 'name', 'protected' => []],
            'warehouse' => ['model' => Warehouse::class, 'column' => 'name', 'protected' => []],
            'abandonment_reason' => ['model' => AbandonmentReason::class, 'column' => 'reason', 'protected' => []],
            'role' => ['model' => Role::class, 'column' => 'name', 'protected' => config('catalog_labels.protected_catalog_names.roles', [])],
            'department' => ['model' => Department::class, 'column' => 'name', 'protected' => config('catalog_labels.protected_catalog_names.departments', [])],
            'job_position' => ['model' => JobPosition::class, 'column' => 'name', 'protected' => config('catalog_labels.protected_catalog_names.job_positions', [])],
            'client_type' => ['model' => ClientType::class, 'column' => 'label', 'code_column' => 'code', 'protected' => config('catalog_labels.protected_catalog_names.client_types', [])],
            'service_type' => ['model' => ServiceType::class, 'column' => 'name', 'protected' => []],
            'break_reason' => ['model' => BreakReason::class, 'column' => 'label', 'code_column' => 'code', 'protected' => ['LUNCH', 'GENERAL']],
            default => null,
        };
    }

    public function index()
    {
        $banks = Bank::orderBy('name', 'asc')->get();
        $couriers = Courier::orderBy('name', 'asc')->get();
        $warehouses = Warehouse::orderBy('name', 'asc')->get();
        $abandonmentReasons = AbandonmentReason::orderBy('reason', 'asc')->get();
        $roles = Role::orderBy('name', 'asc')->get();
        $departments = Department::orderBy('name', 'asc')->get();
        $jobPositions = JobPosition::orderBy('name', 'asc')->get();
        $clientTypes = ClientType::orderBy('sort_order', 'asc')->get();
        $serviceTypes = ServiceType::orderBy('name', 'asc')->get();
        $breakReasons = BreakReason::orderBy('sort_order', 'asc')->get();

        return view('admin.settings.catalogs', compact(
            'banks',
            'couriers',
            'warehouses',
            'abandonmentReasons',
            'roles',
            'departments',
            'jobPositions',
            'clientTypes',
            'serviceTypes',
            'breakReasons'
        ));
    }

    public function store(Request $request)
    {
        $config = $this->getCatalogConfig($request->catalog_type);
        if (!$config) {
            return back()->withErrors(['error' => 'Catálogo inválido.']);
        }

        if ($request->catalog_type === 'client_type') {
            return $this->storeClientType($request);
        }

        $request->validate(['value' => 'required|string|max:100']);

        if ($request->catalog_type === 'break_reason') {
            BreakReason::create([
                'code' => strtoupper(str_replace(' ', '_', trim($request->value))),
                'label' => trim($request->value),
                'sort_order' => (BreakReason::max('sort_order') ?? 0) + 10,
                'is_active' => true,
            ]);
        } else {
            $config['model']::create([
                $config['column'] => trim($request->value),
                'is_active' => true,
            ]);
        }

        return back()->with('success', 'Registro agregado correctamente.')->with('active_tab', $request->catalog_type);
    }

    public function update(Request $request, $id)
    {
        $config = $this->getCatalogConfig($request->catalog_type);
        if (!$config) {
            return back()->withErrors(['error' => 'Catálogo inválido.']);
        }

        if ($request->catalog_type === 'client_type') {
            return $this->updateClientType($request, $id);
        }

        $request->validate(['value' => 'required|string|max:100']);

        $item = $config['model']::findOrFail($id);

        if ($request->catalog_type === 'break_reason') {
            $item->update(['label' => trim($request->value)]);
        } else {
            $item->update([$config['column'] => trim($request->value)]);
        }

        return back()->with('success', 'Registro actualizado correctamente.')->with('active_tab', $request->catalog_type);
    }

    public function toggle(Request $request, $id)
    {
        $config = $this->getCatalogConfig($request->catalog_type);
        if (!$config) {
            return back()->withErrors(['error' => 'Catálogo inválido.']);
        }

        $item = $config['model']::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);

        $status = $item->is_active ? 'activado' : 'desactivado';

        return back()->with('success', "Registro {$status} correctamente.")->with('active_tab', $request->catalog_type);
    }

    public function destroy(Request $request, $id)
    {
        $config = $this->getCatalogConfig($request->catalog_type);
        if (!$config) {
            return back()->withErrors(['error' => 'Catálogo inválido.']);
        }

        $item = $config['model']::findOrFail($id);
        $identifier = $request->catalog_type === 'break_reason' || $request->catalog_type === 'client_type'
            ? $item->code
            : ($item->{$config['column']} ?? '');

        if (in_array($identifier, $config['protected'] ?? [], true)) {
            return back()->withErrors(['error' => 'Este registro es del sistema y no puede eliminarse. Desactívalo en su lugar.'])
                ->with('active_tab', $request->catalog_type);
        }

        try {
            $item->delete();

            return back()->with('success', 'Registro eliminado permanentemente.')->with('active_tab', $request->catalog_type);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'No se puede eliminar el registro porque ya está en uso. Te sugerimos desactivarlo.'])
                ->with('active_tab', $request->catalog_type);
        }
    }

    private function storeClientType(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:client_types,code',
            'label' => 'required|string|max:100',
            'sort_order' => 'required|integer|min:1|max:9998',
            'prioritize_in_queue' => 'nullable|boolean',
            'hide_on_public_tv' => 'nullable|boolean',
            'use_premium_alert' => 'nullable|boolean',
        ]);

        ClientType::create([
            'code' => strtoupper(trim($validated['code'])),
            'label' => trim($validated['label']),
            'name' => strtoupper(trim($validated['code'])),
            'sort_order' => $validated['sort_order'],
            'prioritize_in_queue' => $request->boolean('prioritize_in_queue'),
            'hide_on_public_tv' => $request->boolean('hide_on_public_tv'),
            'use_premium_alert' => $request->boolean('use_premium_alert'),
            'is_active' => true,
        ]);

        return back()->with('success', 'Tipo de cliente agregado correctamente.')->with('active_tab', 'client_type');
    }

    private function updateClientType(Request $request, int $id)
    {
        $item = ClientType::findOrFail($id);

        $validated = $request->validate([
            'label' => 'required|string|max:100',
            'sort_order' => 'required|integer|min:1|max:9998',
            'prioritize_in_queue' => 'nullable|boolean',
            'hide_on_public_tv' => 'nullable|boolean',
            'use_premium_alert' => 'nullable|boolean',
        ]);

        $item->update([
            'label' => trim($validated['label']),
            'sort_order' => $validated['sort_order'],
            'prioritize_in_queue' => $request->boolean('prioritize_in_queue'),
            'hide_on_public_tv' => $request->boolean('hide_on_public_tv'),
            'use_premium_alert' => $request->boolean('use_premium_alert'),
        ]);

        return back()->with('success', 'Tipo de cliente actualizado correctamente.')->with('active_tab', 'client_type');
    }
}
