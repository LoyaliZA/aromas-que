<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pickup;
use App\Models\PickupEdit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PickupController extends Controller
{
    /**
     * DASHBOARD: Solo Métricas y KPIs (Solo lectura).
     */
    public function index()
    {
        $pendingCount = Pickup::inCustody()->count();
        $deliveredTodayCount = Pickup::where('status', 'DELIVERED')
                                     ->whereDate('updated_at', today())
                                     ->count();
        $totalTodayCount = Pickup::whereDate('created_at', today())->count();

        return view('gerencia.dashboard', compact('pendingCount', 'deliveredTodayCount', 'totalTodayCount'));
    }

    /**
     * OPERACIÓN DIARIA: Tabla de trabajo con Filtros y Modales.
     * MODIFICADO: Ahora EXCLUYE los rezagados (>15 días). Esos van en su propia vista.
     */
    public function daily(Request $request)
    {
        // 1. Iniciamos consulta BASE
        // Regla: Mostrar lo de HOY -O- lo EN CUSTODIA (pero menor a 15 días)
        $query = Pickup::query()->where(function($q) {
            $q->whereDate('created_at', today())
              ->orWhere(function($subQ) {
                  $subQ->where('status', 'IN_CUSTODY')
                       ->where('created_at', '>=', now()->subDays(15)->startOfDay());
              });
        });

        // 2. Aplicamos Filtros (Buscador, Estatus, Depto)
        $query->search($request->search)
              ->byStatus($request->status)
              ->byDepartment($request->department);

        // 3. Obtenemos resultados ordenados
        $todaysPickups = $query->orderByRaw("FIELD(status, 'IN_CUSTODY', 'DELIVERED')")
                               ->orderBy('created_at', 'desc')
                               ->get();

        if ($request->ajax()) {
            return view('gerencia.partials.daily-table', compact('todaysPickups'))->render();
        }

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
     * VISTA EXCLUSIVA: REZAGADOS (+15 Días)
     */
    public function rezagados()
    {
        // CORREGIDO: Leemos la propiedad directamente para evitar errores del editor
        if (!Auth::user()->can_manage_rezagados) {
            return redirect()->route('gerencia.dashboard')->with('error', 'No tienes permisos para acceder a la bóveda de rezagados.');
        }

        // Usamos el scope que ya tenías creado en el modelo
        $rezagados = Pickup::rezagados()->orderBy('created_at', 'asc')->get();

        return view('gerencia.rezagados', compact('rezagados'));
    }

    /**
     * ENTREGAR REZAGADO (Acción exclusiva)
     */
    public function entregarRezagado(Request $request, $id)
    {
        // CORREGIDO: Leemos la propiedad directamente para evitar errores del editor
        if (!Auth::user()->can_manage_rezagados) {
            return redirect()->route('gerencia.dashboard')->with('error', 'No tienes permisos para entregar paquetes rezagados.');
        }

        $pickup = Pickup::rezagados()->findOrFail($id);

        $request->validate([
            'receiver_name' => 'required|string|max:150',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function() use ($pickup, $request) {
            // Actualizamos el paquete a entregado
            $pickup->update([
                'status' => 'DELIVERED',
                'receiver_name' => $request->receiver_name,
                // Le forzamos una nota indicando que fue una entrega excepcional
                'notes' => $request->notes ? "ENTREGA DE REZAGO. Notas: " . $request->notes : "ENTREGA DE REZAGO.",
                'delivered_at' => now(),
            ]);

            // Auditoría Obligatoria
            PickupEdit::create([
                'pickup_id' => $pickup->id,
                'user_id' => Auth::id(),
                'changes' => json_encode(['status' => ['old' => 'IN_CUSTODY', 'new' => 'DELIVERED']]),
                'reason' => 'Entrega especial de resguardo rezagado (+15 días) gestionada por: ' . Auth::user()->name
            ]);
        });

        return redirect()->route('gerencia.rezagados.index')->with('success', 'El paquete rezagado ha sido entregado de forma segura.');
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
            'notes'        => 'nullable|string|max:500',
            'is_third_party'=> 'nullable|boolean',
            'receiver_name' => 'nullable|string|max:150',
        ]);

        $validated['is_third_party'] = $request->has('is_third_party');
        
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

        if (!$pickup->created_at->isToday()) {
             return redirect()->route('gerencia.daily')->with('error', 'Los registros de días anteriores son de solo lectura.');
        }

        $validated = $request->validate([
            'ticket_folio' => 'required|string|max:50|unique:pickups,ticket_folio,'.$id,
            'client_name'  => 'required|string|max:150',
            'department'   => 'required|in:AROMAS,BELLAROMA',
            'pieces'       => 'required|integer|min:1',
            'notes'        => 'nullable|string|max:500',
            'is_third_party'=> 'nullable|boolean',
            'receiver_name' => 'nullable|string|max:150',
        ]);

        $validated['is_third_party'] = $request->has('is_third_party');
        
        $pickup->fill($validated);
        
        if ($pickup->isDirty()) {
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