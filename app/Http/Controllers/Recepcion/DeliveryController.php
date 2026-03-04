<?php

namespace App\Http\Controllers\Recepcion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pickup;
use App\Models\SalesQueue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DeliveryController extends Controller
{
    /**
     * DASHBOARD RECEPCIÓN (Tablet)
     */
    public function index(Request $request)
    {
        // 1. Iniciamos consulta BASE con SEGURIDAD (Oculta los de +15 días)
        $query = Pickup::visibleForChecker();

        // 2. Filtros
        if ($request->has('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'IN_CUSTODY');
        }

        if ($request->has('department') && $request->department !== 'ALL') {
            $query->where('department', $request->department);
        }

        // 3. Buscador
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_folio', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%")
                  ->orWhere('client_ref_id', 'like', "%{$search}%")
                  ->orWhere('receiver_name', 'like', "%{$search}%");
            });
        }

        // Ordenamos
        $query->orderBy('created_at', 'desc');

        // Paginación
        $pickups = $query->paginate(12)->withQueryString();

        // Para el botón de Tickets: Cuántos hay en fila HOY
        $peopleInQueue = SalesQueue::waiting()->count();

        // Si es AJAX (Polling o Filtros/Buscador), solo devolvemos el HTML del grid
        if ($request->ajax()) {
            $html = view('recepcion.partials.card-grid', compact('pickups'))->render();
            return response()->json([
                'html' => $html,
                'queueCount' => $peopleInQueue
            ]);
        }

        return view('recepcion.dashboard', compact('pickups', 'peopleInQueue'));
    }

    /**
     * CONFIRMAR ENTREGA DE PAQUETE
     */
    public function confirm(Request $request, $id)
    {
        $pickup = Pickup::findOrFail($id);

        $request->validate([
            'signature' => 'required|string',
            'is_third_party' => 'nullable|boolean',
            'receiver_name' => 'nullable|string|max:255',
            'evidence_file' => 'nullable|image|max:5120',
            'notes' => 'nullable|string'
        ]);

        $signaturePath = null;
        if (preg_match('/^data:image\/(\w+);base64,/', $request->signature, $type)) {
            $data = substr($request->signature, strpos($request->signature, ',') + 1);
            $data = base64_decode($data);
            $fileName = 'signatures/' . uniqid() . '.png';
            Storage::disk('public')->put($fileName, $data);
            $signaturePath = $fileName;
        }

        $evidencePath = null;
        if ($request->hasFile('evidence_file')) {
            $evidencePath = $request->file('evidence_file')->store('evidences', 'public');
        }

        $pickup->update([
            'status' => 'DELIVERED',
            'delivered_at' => now(),
            'checker_id' => auth()->user->id(),
            'signature_path' => $signaturePath,
            'is_third_party' => $request->boolean('is_third_party'),
            'receiver_name' => $request->boolean('is_third_party') ? $request->receiver_name : $pickup->client_name,
            'evidence_path' => $evidencePath,
            'checker_notes' => $request->notes,
        ]);

        return redirect()->route('recepcion.dashboard')->with('success', 'Paquete entregado correctamente.');
    }

    /**
     * AGREGAR A LA FILA DE VENTAS/CAJA (Kiosco Manual)
     */
    public function addToQueue(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string|max:100',
            'service_type' => 'required|in:SALES,CASHIER',
        ]);

        // 1. Definimos la letra inicial según el destino
        $prefix = $request->service_type === 'SALES' ? 'V' : 'C';

        // 2. Contamos cuántos turnos de ese TIPO se han dado HOY (para reiniciar a 001 cada día)
        $todayCount = SalesQueue::where('service_type', $request->service_type)
                                ->whereDate('queued_at', today())
                                ->count();

        // 3. Formateamos el número (Ej: V-001, C-014)
        $turnNumber = sprintf('%s-%03d', $prefix, $todayCount + 1);

        // Guardamos en Base de Datos
        SalesQueue::create([
            'client_name' => $request->client_name,
            'turn_number' => $turnNumber, 
            'source' => 'MANUAL_KIOSK',
            'status' => 'WAITING',
            'service_type' => $request->service_type,
            'queued_at' => now(),
        ]);

        $tipo = $request->service_type === 'SALES' ? 'Ventas' : 'Caja';

        return redirect()->route('recepcion.dashboard')
                         ->with('success', "Cliente agregado a la fila.")
                         ->with('new_turn', $turnNumber)
                         ->with('client_name', $request->client_name)
                         ->with('destination', $tipo);
    }

    /**
     * NUEVO: OBTENER LISTA DE CLIENTES EN FILA (Para modal de recepcionista)
     */
    public function getQueueList(Request $request)
    {
        if ($request->ajax()) {
            $waitingClients = SalesQueue::whereDate('queued_at', today())
                                        ->where('status', 'WAITING')
                                        ->orderBy('queued_at', 'asc')
                                        ->get();

            return response()->json([
                'clients' => $waitingClients
            ]);
        }
        return response()->json(['error' => 'No autorizado'], 403);
    }

    /**
     * NUEVO: MARCAR COMO ABANDONADO
     */
    public function markAsAbandoned(Request $request, $id)
    {
        $client = SalesQueue::findOrFail($id);
        
        // Solo podemos marcar como abandonado si estaba esperando
        if ($client->status === 'WAITING') {
            $client->update([
                'status' => 'ABANDONED',
                'completed_at' => now(), // Guardamos la fecha de cierre
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Turno marcado como abandonado.']);
            }
        }

        return response()->json(['success' => false, 'message' => 'No se pudo actualizar el estado.'], 400);
    }
}