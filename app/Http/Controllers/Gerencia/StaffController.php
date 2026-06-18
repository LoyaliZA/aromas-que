<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use App\Models\DailyShift;
use App\Models\Department;
use App\Models\Employee;
use App\Models\ShiftStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless(
            $user->isAdmin() || $user->canManageShifts() || $user->canManageSellers(),
            403,
            'No tienes permisos para gestionar el personal de ventas.'
        );

        $rosterSellers = collect();
        if ($user->isAdmin() || $user->canManageSellers()) {
            $rosterSellers = Employee::activeSellers()
                ->with(['user', 'catalogDepartment'])
                ->orderBy('full_name')
                ->get();
        }

        $shiftSellers = collect();
        if ($user->isAdmin() || $user->canManageShifts()) {
            $shiftSellers = Employee::sellers()
                ->with(['user', 'todayShift.catalogBreakReason'])
                ->orderBy('full_name')
                ->get();
        }

        return view('gerencia.staff', compact('rosterSellers', 'shiftSellers'));
    }

    public function create()
    {
        $this->ensureCanManageSellers();

        return view('gerencia.staff-create', array_merge(
            $this->catalogFormData(),
            ['inactiveSellers' => $this->inactiveSellersQuery()->get()]
        ));
    }

    public function store(Request $request)
    {
        $this->ensureCanManageSellers();

        if ($request->input('registration_mode') === 'existing') {
            return $this->reactivateSeller($request);
        }

        $validated = $this->validateNewSeller($request);

        try {
            DB::transaction(function () use ($validated) {
                Employee::create([
                    'user_id' => null,
                    'full_name' => $validated['full_name'],
                    'employee_code' => $validated['employee_code'],
                    'job_position' => 'SELLER',
                    'department' => $validated['department'],
                    'appears_in_sales_queue' => true,
                    'is_active' => true,
                    'hire_date' => now(),
                ]);
            });

            return redirect()->route('gerencia.staff.index')
                ->with('success', 'Vendedor registrado en la pantalla de ventas.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Error al guardar: ' . $e->getMessage()]);
        }
    }

    private function reactivateSeller(Request $request)
    {
        $validated = $request->validate([
            'registration_mode' => 'required|in:existing',
            'employee_id' => 'required|exists:employees,id',
            'reactivate_department' => ['nullable', 'string', Rule::exists('departments', 'name')->where('is_active', true)],
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $this->ensureIsSeller($employee);

        if ($employee->is_active) {
            return back()->withInput()->withErrors(['employee_id' => 'Este colaborador ya está activo en el sistema.']);
        }

        try {
            DB::transaction(function () use ($employee, $validated) {
                $updateData = [
                    'is_active' => true,
                    'appears_in_sales_queue' => true,
                ];

                if (!empty($validated['reactivate_department'])) {
                    $updateData['department'] = $validated['reactivate_department'];
                }

                $employee->update($updateData);
            });

            return redirect()->route('gerencia.staff.index')
                ->with('success', "{$employee->full_name} ha sido reactivado en la pantalla de ventas.");
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Error al reactivar: ' . $e->getMessage()]);
        }
    }

    public function toggleShift(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless($user->isAdmin() || $user->canManageShifts(), 403, 'No tienes permisos para gestionar turnos.');

        $request->validate(['employee_id' => 'required|exists:employees,id']);

        $employee = Employee::findOrFail($request->employee_id);

        abort_unless($employee->appears_in_sales_queue && $employee->is_active, 403, 'Este colaborador no está en la pantalla de ventas.');

        $shift = DailyShift::firstOrCreate(
            ['employee_id' => $employee->id, 'work_date' => today()],
            ['current_status' => 'OFFLINE', 'customers_served_count' => 0]
        );

        $previousStatus = $shift->current_status;

        if ($shift->current_status === 'OFFLINE') {
            $shift->update(['current_status' => 'ONLINE', 'last_status_change_at' => now()]);
            ShiftStatusLog::create([
                'daily_shift_id' => $shift->id,
                'previous_status' => $previousStatus,
                'new_status' => 'ONLINE',
                'changed_at' => now(),
            ]);
            $msg = "{$employee->full_name} ahora está ACTIVO.";
        } else {
            $shift->update(array_merge(
                DailyShift::breakReasonAttributes(null),
                ['current_status' => 'OFFLINE', 'last_status_change_at' => now()]
            ));
            ShiftStatusLog::create([
                'daily_shift_id' => $shift->id,
                'previous_status' => $previousStatus,
                'new_status' => 'OFFLINE',
                'changed_at' => now(),
            ]);
            $msg = "{$employee->full_name} ha cerrado turno.";
        }

        return back()->with('success', $msg);
    }

    public function toggleQueue(Request $request)
    {
        $this->ensureCanManageSellers();

        $request->validate(['employee_id' => 'required|exists:employees,id']);

        $employee = Employee::findOrFail($request->employee_id);
        $this->ensureIsSeller($employee);

        $newValue = !$employee->appears_in_sales_queue;

        if (!$newValue) {
            $this->forceOfflineIfActive($employee);
        }

        $employee->update(['appears_in_sales_queue' => $newValue]);

        $msg = $newValue
            ? "{$employee->full_name} ahora aparece en la pantalla de ventas."
            : "{$employee->full_name} ya no aparece en la pantalla de ventas.";

        return back()->with('success', $msg);
    }

    public function deactivate(int $id)
    {
        $this->ensureCanManageSellers();

        $employee = Employee::with('user')->findOrFail($id);
        $this->ensureIsSeller($employee);

        $this->forceOfflineIfActive($employee);

        $employee->update([
            'is_active' => false,
            'appears_in_sales_queue' => false,
        ]);

        if ($employee->user) {
            $employee->user->update(['is_active' => false]);
        }

        return back()->with('success', "{$employee->full_name} ha sido retirado del personal de ventas.");
    }

    private function ensureCanManageSellers(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless($user->isAdmin() || $user->canManageSellers(), 403, 'No tienes permisos para gestionar vendedores.');
    }

    private function ensureIsSeller(Employee $employee): void
    {
        abort_unless(
            $employee->resolveJobPositionName() === 'SELLER',
            403,
            'Solo se pueden gestionar colaboradores con puesto de vendedor.'
        );
    }

    private function forceOfflineIfActive(Employee $employee): void
    {
        $shift = DailyShift::where('employee_id', $employee->id)
            ->where('work_date', today())
            ->first();

        if (!$shift || $shift->current_status === 'OFFLINE') {
            return;
        }

        $previousStatus = $shift->current_status;

        $shift->update(array_merge(
            DailyShift::breakReasonAttributes(null),
            ['current_status' => 'OFFLINE', 'last_status_change_at' => now()]
        ));

        ShiftStatusLog::create([
            'daily_shift_id' => $shift->id,
            'previous_status' => $previousStatus,
            'new_status' => 'OFFLINE',
            'changed_at' => now(),
        ]);
    }

    private function catalogFormData(): array
    {
        $departmentLabels = config('catalog_labels.departments', []);

        return [
            'departments' => Department::active()->orderBy('name')->get(),
            'departmentLabels' => $departmentLabels,
        ];
    }

    private function inactiveSellersQuery()
    {
        return Employee::sellerPosition()
            ->where('is_active', false)
            ->orderBy('full_name');
    }

    private function validateNewSeller(Request $request): array
    {
        $departmentRule = Rule::exists('departments', 'name')->where('is_active', true);

        return $request->validate([
            'registration_mode' => 'required|in:new',
            'full_name' => 'required|string|max:100',
            'employee_code' => 'required|unique:employees,employee_code',
            'department' => ['required', 'string', $departmentRule],
        ]);
    }
}
