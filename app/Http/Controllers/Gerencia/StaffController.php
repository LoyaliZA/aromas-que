<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\DailyShift;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // BLOQUEO: Si no es admin y no tiene permiso, lo mandamos al error 403
        abort_unless($user->isAdmin() || $user->canManageShifts(), 403, 'No tienes permisos para gestionar los turnos de los vendedores.');

        $sellers = Employee::sellers()
            ->with(['user', 'todayShift'])
            ->get();

        return view('gerencia.staff', compact('sellers'));
    }

    public function toggleShift(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // BLOQUEO: Doble seguridad por si intentan enviar la petición por otro lado
        abort_unless($user->isAdmin() || $user->canManageShifts(), 403, 'No tienes permisos para gestionar turnos.');

        $request->validate(['employee_id' => 'required|exists:employees,id']);
        
        $employee = Employee::findOrFail($request->employee_id);
        
        $shift = DailyShift::firstOrCreate(
            ['employee_id' => $employee->id, 'work_date' => today()],
            ['current_status' => 'OFFLINE', 'customers_served_count' => 0]
        );

        if ($shift->current_status === 'OFFLINE') {
            $shift->update(['current_status' => 'ONLINE', 'last_status_change_at' => now()]);
            $msg = "{$employee->full_name} ahora está ACTIVO.";
        } else {
            $shift->update(['current_status' => 'OFFLINE']);
            $msg = "{$employee->full_name} ha cerrado turno.";
        }

        return back()->with('success', $msg);
    }
}