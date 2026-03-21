<?php

namespace App\Http\Controllers\Recepcion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pickup;
use App\Models\SalesQueue;
use App\Models\Customer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class DeliveryController extends Controller
{
    /**
     * DASHBOARD RECEPCIÓN (Tablet)
     */
    public function index(Request $request)
    {
        $query = Pickup::visibleForChecker();

        if ($request->has('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'IN_CUSTODY');
        }

        if ($request->has('department') && $request->department !== 'ALL') {
            $query->where('department', $request->department);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_folio', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%")
                  ->orWhere('client_ref_id', 'like', "%{$search}%")
                  ->orWhere('receiver_name', 'like', "%{$search}%");
            });
        }

        $query->orderBy('created_at', 'desc');
        $pickups = $query->paginate(12)->withQueryString();
        $peopleInQueue = SalesQueue::waiting()->count();

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
            'checker_id' => Auth::id(),
            'signature_path' => $signaturePath,
            'is_third_party' => $request->boolean('is_third_party'),
            'receiver_name' => $request->boolean('is_third_party') ? $request->receiver_name : $pickup->client_name,
            'evidence_path' => $evidencePath,
            'checker_notes' => $request->notes,
        ]);

        return redirect()->route('recepcion.dashboard')->with('success', 'Paquete entregado correctamente.');
    }

    /**
     * BUSCAR CLIENTES PARA AUTOCOMPLETADO
     */
    public function searchCustomers(Request $request)
    {
        if ($request->ajax()) {
            $search = $request->get('q');
            $customers = Customer::where('name', 'LIKE', "%{$search}%")
                                 ->orWhere('customer_number', 'LIKE', "%{$search}%")
                                 ->orWhere('phone', 'LIKE', "%{$search}%")
                                 ->limit(10)
                                 ->get();
                                 
            return response()->json($customers);
        }
        return response()->json([], 403);
    }

    /**
     * AGREGAR A LA FILA DE VENTAS/CAJA
     */
    public function addToQueue(Request $request)
    {
        $request->validate([
            'service_type' => 'required|in:SALES,CASHIER',
            'customer_id' => 'nullable|exists:customers,id',
            'client_name' => 'required_without:customer_id|string|max:100|nullable',
            'is_third_party' => 'nullable|boolean',
            'representative_name' => 'required_if:is_third_party,1|string|max:100|nullable',
            'has_disability' => 'nullable|boolean', 
        ]);

        $customer = null;
        $clientName = $request->client_name;
        $clientType = 'REGULAR'; 

        if ($request->customer_id) {
            $customer = Customer::find($request->customer_id);
            // Lógica de representante: Si viene alguien más, el nombre en la fila será el del representante
            $clientName = $request->boolean('is_third_party') ? $request->representative_name : $customer->name;
            $clientType = $customer->client_type; 
        } else {
            // Cliente nuevo sin registro previo
            $customer = Customer::create([
                'name' => $clientName,
                'client_type' => 'REGULAR'
            ]);
        }

        $prefix = $request->service_type === 'SALES' ? 'V' : 'C';
        $todayCount = SalesQueue::where('service_type', $request->service_type)
                                ->whereDate('queued_at', today())
                                ->count();
                                
        $turnNumber = sprintf('%s-%03d', $prefix, $todayCount + 1);

        // TODO: En la siguiente migración añadiremos 'has_disability' a la base de datos de SalesQueue
        SalesQueue::create([
            'customer_id' => $customer->id,
            'client_name' => $clientName,
            'client_type' => $clientType,
            'has_disability' => $request->boolean('has_disability'),
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
                         ->with('client_name', $clientName)
                         ->with('destination', $tipo);
    }

    /**
     * OBTENER LISTA DE CLIENTES EN FILA
     */
    public function getQueueList(Request $request)
    {
        if ($request->ajax()) {
            $waitingClients = SalesQueue::with('customer') 
                                        ->whereDate('queued_at', today())
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
     * MARCAR COMO ABANDONADO
     */
    public function markAsAbandoned(Request $request, $id)
    {
        // 1. Forzamos la extracción y validación estricta del payload JSON
        $validated = $request->validate([
            'abandonment_reason_id' => 'required|integer',
            'custom_abandonment_reason' => 'nullable|string'
        ]);

        $client = SalesQueue::findOrFail($id);
        
        if ($client->status === 'WAITING') {
            // 2. Inyectamos los datos validados directamente, garantizando su captura
            $client->update([
                'status' => 'ABANDONED',
                'completed_at' => now(),
                'abandonment_reason_id' => $validated['abandonment_reason_id'], 
                'custom_abandonment_reason' => $validated['custom_abandonment_reason'] ?? null,
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Turno marcado como abandonado.']);
            }
        }

        return response()->json(['success' => false, 'message' => 'No se pudo actualizar el estado.'], 400);
    }

    /**
     * MARCAR PAQUETE COMO RECIBIDO EN ALMACÉN
     */
    public function markAsReceived(Request $request, $id)
    {
        $pickup = Pickup::findOrFail($id);
        
        if ($pickup->status === 'IN_CUSTODY' && is_null($pickup->received_by_checker_at)) {
            $pickup->update([
                'received_by_checker_at' => now(),
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Recepción confirmada.']);
            }
        }

        return response()->json(['success' => false, 'message' => 'No se pudo confirmar la recepción.'], 400);
    }
}