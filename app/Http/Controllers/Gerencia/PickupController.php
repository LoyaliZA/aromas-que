<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pickup;
use App\Models\PickupEdit; // Asegúrate de que este modelo exista (Paso 2)
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PickupController extends Controller
{
    /**
     * DASHBOARD: Solo Métricas y KPIs (Solo lectura).
     */
    public function index()
    {
        // KPIs para los Widgets
        $pendingCount = Pickup::inCustody()->count();
        $deliveredTodayCount = Pickup::where('status', 'DELIVERED')
                                     ->whereDate('updated_at', today())
                                     ->count();
        $totalTodayCount = Pickup::today()->count();

        return view('gerencia.dashboard', compact('pendingCount', 'deliveredTodayCount', 'totalTodayCount'));
    }

    /**
     * OPERACIÓN DIARIA: Tabla de trabajo con Filtros y Modales.
     */
    public function daily(Request $request)
    {
        // 1. Iniciamos consulta restringida a HOY
        $query = Pickup::today();

        // 2. Aplicamos Filtros (Igual que en historial)
        // Nota: No filtramos por fecha porque ya estamos forzados a 'today()'
        $query->search($request->search)
              ->byStatus($request->status)
              ->byDepartment($request->department);

        // 3. Obtenemos resultados
        $todaysPickups = $query->orderBy('created_at', 'desc')->get();

        // 4. Si es AJAX (Búsqueda en vivo), devolvemos solo la tabla
        if ($request->ajax()) {
            return view('gerencia.partials.daily-table', compact('todaysPickups'))->render();
        }

        // 5. Carga normal
        return view('gerencia.daily', compact('todaysPickups'));
    }

    /**
     * HISTORIAL: Buscador AJAX.
     */
    public function history(Request $request)
    {
        $query = Pickup::query();

        $query->search($request->search)
              ->byStatus($request->status)
              ->byDepartment($request->department)
              ->byDate($request->date_start, $request->date_end);

        $pickups = $query->orderBy('created_at', 'desc')
                         ->paginate(15)
                         ->withQueryString();

        if ($request->ajax()) {
            return view('gerencia.partials.history-table', compact('pickups'))->render();
        }

        return view('gerencia.history', compact('pickups'));
    }

    /**
     * STORE: Crear nuevo resguardo.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ticket_folio' => 'required|string|max:50|unique:pickups,ticket_folio',
            'ticket_date'  => 'required|date',
            'client_ref_id'=> 'required|string|max:50',
            'client_name'  => 'required|string|max:150',
            'department'   => 'required|in:AROMAS,BELLAROMA',
            'pieces'       => 'required|integer|min:1',
            // Nuevos campos opcionales
            'notes'        => 'nullable|string|max:500',
            'is_third_party'=> 'nullable|boolean',
            'receiver_name' => 'nullable|string|max:150',
        ]);

        // Si el checkbox de "Tercero" no se marca, nos aseguramos que se guarde como falso
        $validated['is_third_party'] = $request->has('is_third_party');
        
        // Si no es tercero, limpiamos el nombre por seguridad (o podrías dejarlo si el gerente lo llenó)
        if (!$validated['is_third_party']) {
            $validated['receiver_name'] = null; 
        }

        Pickup::create($validated);

        return redirect()->route('gerencia.daily')
                         ->with('success', 'Paquete registrado correctamente.');
    }

    /**
     * UPDATE: Editar con Auditoría.
     */
    public function update(Request $request, $id)
    {
        $pickup = Pickup::findOrFail($id);

        $validated = $request->validate([
            'ticket_folio' => 'required|string|max:50|unique:pickups,ticket_folio,'.$id,
            'client_name'  => 'required|string|max:150',
            'department'   => 'required|in:AROMAS,BELLAROMA',
            'pieces'       => 'required|integer|min:1',
            // Nuevos campos
            'notes'        => 'nullable|string|max:500',
            'is_third_party'=> 'nullable|boolean',
            'receiver_name' => 'nullable|string|max:150',
        ]);

        // Ajuste de booleanos
        $validated['is_third_party'] = $request->has('is_third_party');
        
        // Lógica de auditoría (Mantenemos tu código actual, solo agregando los campos)
        $pickup->fill($validated);
        
        if ($pickup->isDirty()) {
            // ... (Tu código de auditoría DB::transaction aquí se mantiene igual) ...
             $changes = [];
            foreach ($pickup->getDirty() as $field => $newValue) {
                $changes[$field] = [
                    'old' => $pickup->getOriginal($field),
                    'new' => $newValue
                ];
            }

            DB::transaction(function() use ($pickup, $changes) {
                $pickup->save();
                PickupEdit::create([
                    'pickup_id' => $pickup->id,
                    'user_id' => Auth::id(),
                    'changes' => json_encode($changes),
                    'reason' => 'Edición manual desde Operación Diaria'
                ]);
            });

            return redirect()->route('gerencia.daily')->with('success', 'Resguardo actualizado y auditado.');
        }

        return redirect()->route('gerencia.daily')->with('info', 'No se detectaron cambios.');
    }
}