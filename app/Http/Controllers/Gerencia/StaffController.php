<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\DailyShift;

class StaffController extends Controller
{
    public function index()
    {
        // Traemos vendedores marcados para salir en cola
        $sellers = Employee::sellers()
            ->with(['user', 'todayShift'])
            ->get();

        return view('gerencia.staff', compact('sellers'));
    }

    public function toggleShift(Request $request)
    {
        $request->validate(['employee_id' => 'required|exists:employees,id']);
        
        $employee = Employee::findOrFail($request->employee_id);
        
        $shift = DailyShift::firstOrCreate(
            ['employee_id' => $employee->id, 'work_date' => today()],
            ['current_status' => 'OFFLINE', 'customers_served_count' => 0]
        );

        // Lógica de Toggle
        if ($shift->current_status === 'OFFLINE') {
            $shift->update(['current_status' => 'ONLINE', 'last_status_change_at' => now()]);
            $msg = "{$employee->full_name} ahora está ACTIVO.";
        } else {
            // Forzar salida (incluso si está en Break)
            $shift->update(['current_status' => 'OFFLINE']);
            $msg = "{$employee->full_name} ha cerrado turno.";
        }

        return back()->with('success', $msg);
    }
}