<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use App\Models\DailyShift;
use App\Models\Employee;
use App\Models\ShiftStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless($user->isAdmin() || $user->canManageShifts(), 403, 'No tienes permisos para gestionar los turnos de los vendedores.');

        $sellers = Employee::sellers()
            ->with(['user', 'todayShift.catalogBreakReason'])
            ->get();

        return view('gerencia.staff', compact('sellers'));
    }

    public function toggleShift(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless($user->isAdmin() || $user->canManageShifts(), 403, 'No tienes permisos para gestionar turnos.');

        $request->validate(['employee_id' => 'required|exists:employees,id']);

        $employee = Employee::findOrFail($request->employee_id);

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
}
