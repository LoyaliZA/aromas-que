<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pickup;
use App\Models\PickupEdit;
use App\Models\PickupStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\SalesQueue;
use Illuminate\Support\Facades\Storage;

class PickupController extends Controller
{

    /**
     * DASHBOARD: Panel de Control Global de Gerencia
     */
    public function index()
    {
        $today = today();

        // 1. Métricas de Atención en Piso
        $queueMetrics = [
            'waiting' => SalesQueue::whereDate('queued_at', $today)->where('status', 'WAITING')->count(),
            'serving' => SalesQueue::whereDate('queued_at', $today)->where('status', 'SERVING')->count(),
            'completed' => SalesQueue::whereDate('queued_at', $today)->where('status', 'COMPLETED')->count(),
            'abandoned' => SalesQueue::whereDate('queued_at', $today)->whereIn('status', ['ABANDONED', 'CANCELED'])->count(),
        ];

        // 2. Control de Personal Activo (Vendedores)
        $sellers = Employee::sellers()
            ->with(['user', 'todayShift'])
            ->get();

        // 3. Lista de Clientes en Espera (Ordenados por prioridad y llegada)
        $waitingClients = SalesQueue::today()->waiting()->get();

        return view('gerencia.dashboard', compact('queueMetrics', 'sellers', 'waitingClients'));
    }

    /**
     * OPERACIÓN DIARIA: Tabla de trabajo con Filtros y Modales.
     * MODIFICADO: Ahora EXCLUYE los rezagados (>15 días). Esos van en su propia vista.
     */
    public function daily(Request $request)
    {
        // 1. Iniciamos consulta BASE seleccionando explicitamente la tabla
        $query = Pickup::query()
            ->select('pickups.*')
            ->with('currentStatus')
            ->where(function ($q) {
                $q->whereDate('pickups.created_at', today())
                    ->orWhere(function ($subQ) {
                        $subQ->whereHas('currentStatus', function ($statusQ) {
                            $statusQ->where('code', 'IN_CUSTODY');
                        })
                            ->where('pickups.created_at', '>=', now()->subDays(15)->startOfDay());
                    });
            });

        // 2. Aplicamos Filtros (Buscador, Estatus, Depto)
        $query->search($request->search)
            ->byStatus($request->status)
            ->byDepartment($request->department);

        // 3. Obtenemos resultados ordenados aprovechando el sort_order del catálogo
        $todaysPickups = $query->join('pickup_statuses', 'pickups.status_id', '=', 'pickup_statuses.id')
            ->orderBy('pickup_statuses.sort_order', 'asc')
            ->orderBy('pickups.created_at', 'desc')
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
        if (!Auth::user()->can_manage_rezagados) {
            return redirect()->route('gerencia.dashboard')->with('error', 'No tienes permisos para entregar paquetes rezagados.');
        }

        $pickup = Pickup::rezagados()->findOrFail($id);

        $request->validate([
            'receiver_name' => 'required|string|max:150',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($pickup, $request) {
            $deliveredStatus = \App\Models\PickupStatus::where('code', 'DELIVERED')->first();

            $pickup->update([
                'status_id' => $deliveredStatus->id,
                'receiver_name' => $request->receiver_name,
                'notes' => $request->notes ? "ENTREGA DE REZAGO. Notas: " . $request->notes : "ENTREGA DE REZAGO.",
                'delivered_at' => now(),
            ]);

            PickupEdit::create([
                'pickup_id' => $pickup->id,
                'user_id' => Auth::id(),
                'changes' => json_encode(['status' => ['old' => 'IN_CUSTODY', 'new' => 'DELIVERED']]),
                'reason' => 'Entrega especial de resguardo rezagado gestionada por: ' . Auth::user()->name
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
            'client_ref_id' => 'required|string|max:50',
            'client_name'  => 'required|string|max:150',
            'department'   => 'required|in:AROMAS,BELLAROMA',
            'pieces'       => 'required|integer|min:1',
            'notes'        => 'nullable|string|max:500',
            'is_third_party' => 'nullable|boolean',
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
            'ticket_folio' => 'required|string|max:50|unique:pickups,ticket_folio,' . $id,
            'client_name'  => 'required|string|max:150',
            'department'   => 'required|in:AROMAS,BELLAROMA,CALLCENTER',
            'pieces'       => 'required|integer|min:1',
            'notes'        => 'nullable|string|max:500',
            'is_third_party' => 'nullable|boolean',
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

            DB::transaction(function () use ($pickup, $changes) {
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

    public function destroy($id)
    {
        $pickup = Pickup::findOrFail($id);

        // Eliminamos las fotos del disco local si existen
        if ($pickup->initial_evidence_path) {
            Storage::disk('public')->delete($pickup->initial_evidence_path);
        }
        if ($pickup->package_evidence_path) {
            Storage::disk('public')->delete($pickup->package_evidence_path);
        }

        $pickup->delete();

        return redirect()->route('gerencia.daily')->with('success', 'Resguardo eliminado permanentemente.');
    }

    /**
     * FASE 1: Registro Preliminar por el Gerente
     */
    public function storePreliminar(Request $request)
    {
        $request->validate([
            'ticket_folio' => 'required|string|max:50|unique:pickups,ticket_folio',
            'department' => 'required|in:AROMAS,BELLAROMA,CALLCENTER',
            'pieces' => 'required|integer|min:1',
            'initial_evidence' => 'nullable|image|max:5120',
            'notes' => 'nullable|string|max:500',
            'is_complementary' => 'nullable|boolean',
            'parent_folio' => 'nullable|string|required_if:is_complementary,1',
        ]);

        // Guardamos la foto inicial tomada por el gerente
        $evidencePath = null;
        if ($request->hasFile('initial_evidence')) {
            $evidencePath = $request->file('initial_evidence')->store('pickups/initial_evidence', 'public');
        }


        $parentId = null;
        if ($request->filled('is_complementary') && $request->filled('parent_folio')) {
            $parentPickup = Pickup::where('ticket_folio', $request->parent_folio)->first();
            if ($parentPickup) {
                $parentId = $parentPickup->id;
            }
        }

        // Ahora el estatus inicial es PRE_REGISTERED (Pre-Registro)
        $statusId = PickupStatus::where('code', 'PRE_REGISTERED')->value('id');

        Pickup::create([
            'ticket_folio' => $request->ticket_folio,
            'ticket_date' => now(),
            'department' => $request->department,
            'pieces' => $request->pieces,
            'notes' => $request->notes,
            'initial_evidence_path' => $evidencePath,
            'is_complementary' => $request->has('is_complementary'),
            'parent_pickup_id' => $parentId,
            'status_id' => $statusId,

            // Valores Provisionales
            'client_name' => 'Pendiente por Checador',
            'amount' => 0,
            'balance' => 0,
        ]);

        return redirect()->route('gerencia.daily')->with('success', 'Resguardo preliminar registrado con éxito. En espera del checador.');
    }

    /**
     * FASE 3: Aprobar resguardo (Pasa a Custodia oficial)
     */
    public function approveAudit($id)
    {
        $pickup = Pickup::findOrFail($id);
        $inCustodyId = \App\Models\PickupStatus::where('code', 'IN_CUSTODY')->value('id');

        $pickup->update([
            'status_id' => $inCustodyId,
            'correction_notes' => null
        ]);

        return redirect()->route('gerencia.daily')->with('success', 'Resguardo aprobado. Ya está en custodia oficial.');
    }

    /**
     * FASE 3: Rechazar resguardo (Regresa al checador)
     */
    public function rejectAudit(Request $request, $id)
    {
        $pickup = Pickup::findOrFail($id);

        $request->validate([
            'correction_notes' => 'required|string|max:500'
        ]);

        $needsCorrectionId = \App\Models\PickupStatus::where('code', 'NEEDS_CORRECTION')->value('id');

        $pickup->update([
            'status_id' => $needsCorrectionId,
            'correction_notes' => $request->correction_notes
        ]);

        return redirect()->route('gerencia.daily')->with('error', 'Resguardo devuelto al checador para corrección.');
    }

    /**
     * BUSCAR FOLIOS PARA RESGUARDOS COMPLEMENTARIOS
     */
    public function searchFolio(Request $request)
    {
        if ($request->ajax()) {
            $search = trim($request->get('q'));
            if (strlen($search) < 2) return response()->json([]);

            $pickups = Pickup::select('id', 'ticket_folio', 'client_name')
                ->where('ticket_folio', 'LIKE', "%{$search}%")
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json($pickups);
        }
        return response()->json(['error' => 'No autorizado'], 403);
    }
}
