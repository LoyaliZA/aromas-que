<?php

namespace App\Http\Controllers\Recepcion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pickup;
use App\Models\PickupStatus;
use App\Models\AbandonmentReason;
use App\Models\ServiceType;
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
        // 1. Obtener IDs de los estatus
        $inCustodyId = PickupStatus::where('code', 'IN_CUSTODY')->value('id');
        $preRegisteredId = PickupStatus::where('code', 'PRE_REGISTERED')->value('id');
        $needsCorrectionId = PickupStatus::where('code', 'NEEDS_CORRECTION')->value('id');
        $pendingConfirmationId = PickupStatus::where('code', 'PENDING_CONFIRMATION')->value('id');
        $dispatchedId = PickupStatus::where('code', 'DISPATCHED')->value('id');

        // 2. Consulta principal (Agregamos with('customer') para escalabilidad y limpieza)
        $query = Pickup::visibleForChecker()->with('customer');

        if ($request->has('status') && $request->status !== 'ALL') {
            $statusId = PickupStatus::where('code', $request->status)->value('id');
            $query->where('status_id', $statusId);
        } else {
            // Por defecto mostramos Custodia, Pre-Registros y los que requieren corrección
            $query->whereIn('status_id', [$inCustodyId, $preRegisteredId, $needsCorrectionId, $pendingConfirmationId]);
        }

        if ($request->has('department') && $request->department !== 'ALL') {
            $query->byDepartment($request->department);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_folio', 'like', "%{$search}%")
                    ->orWhere('client_name', 'like', "%{$search}%")
                    ->orWhere('client_ref_id', 'like', "%{$search}%")
                    ->orWhere('receiver_name', 'like', "%{$search}%");
            });
        }

        $pickups = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();
        $peopleInQueue = SalesQueue::waiting()->count();

        // 3. NUEVO: Consultar paquetes EN CAMINO (Solo los que son para recoger en tienda)
        $incomingPickups = Pickup::where('status_id', $dispatchedId)
            ->whereHas('logistic', function($q) {
                $q->where('is_store_pickup', true);
            })
            ->with('logistic')
            ->orderBy('updated_at', 'desc')
            ->get();

        $departments = \App\Models\Department::where('is_active', true)->get();
        $abandonmentReasons = AbandonmentReason::where('is_active', true)->orderBy('reason')->get();
        $otherAbandonmentReason = $abandonmentReasons->firstWhere('reason', 'Otro motivo');

        if ($request->ajax()) {
            $html = view('recepcion.partials.card-grid', compact('pickups'))->render();
            return response()->json([
                'html' => $html,
                'queueCount' => $peopleInQueue
            ]);
        }

        return view('recepcion.dashboard', compact(
            'pickups',
            'peopleInQueue',
            'incomingPickups',
            'departments',
            'abandonmentReasons',
            'otherAbandonmentReason'
        ));
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

        // NUEVO: Obtener el ID oficial del estatus DELIVERED desde el catálogo
        $deliveredStatusId = PickupStatus::where('code', 'DELIVERED')->value('id');

        $pickup->update([
            'status_id' => $deliveredStatusId, // <-- CORRECCIÓN: Usar status_id
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
     * BUSCAR CLIENTES PARA AUTOCOMPLETADO (Optimizado para +5000 registros)
     */
    public function searchCustomers(Request $request)
    {
        if ($request->ajax()) {
            $search = trim($request->get('q'));
            
            // Si está completamente vacío, no buscar
            if (empty($search)) {
                return response()->json([]);
            }

            // OPTIMIZACIÓN INTELIGENTE: 
            // Si es texto, requerimos 2 caracteres para no saturar.
            // Si es un número (ej. "4"), permitimos la búsqueda con 1 solo dígito.
            if (!is_numeric($search) && strlen($search) < 2) {
                return response()->json([]);
            }

            $query = Customer::select('id', 'name', 'customer_number', 'client_type_id')
                ->with('catalogClientType');

            // Búsqueda rápida por número de cliente o coincidencia de nombre
            if (is_numeric($search) || preg_match('/^[A-Za-z0-9]+$/', $search)) {
                $query->where('customer_number', 'LIKE', "{$search}%")
                      ->orWhere('name', 'LIKE', "%{$search}%");
            } else {
                $query->where('name', 'LIKE', "%{$search}%");
            }
                                 
            return response()->json(
                $query->limit(15)->get()->map(fn ($customer) => array_merge([
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'customer_number' => $customer->customer_number,
                    'client_type' => $customer->resolveClientTypeCode(),
                    'client_type_label' => $customer->resolveClientTypeLabel(),
                ], $customer->clientTypeMetadata()))
            );
        }
        return response()->json(['error' => 'No autorizado'], 403);
    }

    /**
     * AGREGAR A LA FILA DE VENTAS/CAJA (Blindado contra creación de basura)
     */
    public function addToQueue(Request $request)
    {
        $request->validate([
            'service_type' => ['required', 'string', \Illuminate\Validation\Rule::exists('service_types', 'name')->where('is_active', true)],
            'is_new_customer' => 'nullable|boolean',
            'customer_id' => 'required_without:is_new_customer|exists:customers,id|nullable',
            'new_client_name' => 'required_if:is_new_customer,1|string|max:100|nullable',
            'is_third_party' => 'nullable|boolean',
            'representative_name' => 'required_if:is_third_party,1|string|max:100|nullable',
            'has_disability' => 'nullable|boolean',
        ]);

        $customerId = null;
        $clientName = null;
        $clientType = \App\Models\ClientType::DEFAULT_CODE;

        // 1. Validamos de dónde viene el cliente
        if (!$request->boolean('is_new_customer')) {
            // A) Es un cliente registrado seleccionado de la base de datos
            $customer = Customer::findOrFail($request->customer_id);
            $customerId = $customer->id;
            $clientName = $request->boolean('is_third_party') ? $request->representative_name : $customer->name;
            $clientType = $customer->resolveClientTypeCode() ?? \App\Models\ClientType::DEFAULT_CODE;
        } else {
            // B) Es un cliente NUEVO (NO lo guardamos en la tabla customers, solo tomamos su nombre para el ticket)
            $clientName = strtoupper($request->new_client_name);
            $clientType = \App\Models\ClientType::DEFAULT_CODE;
        }

        // 2. Generar Folio
        $prefix = $request->service_type === 'SALES' ? 'V' : 'C';
        $todayCount = SalesQueue::byServiceType($request->service_type)
            ->whereDate('queued_at', today())
            ->count();

        $turnNumber = sprintf('%s-%03d', $prefix, $todayCount + 1);

        // 3. Crear el ticket en la fila
        SalesQueue::create(array_merge(
            [
                'customer_id' => $customerId,
                'client_name' => $clientName,
                'has_disability' => $request->boolean('has_disability'),
                'turn_number' => $turnNumber,
                'queued_at' => now(),
            ],
            SalesQueue::attributesForClientType($clientType),
            SalesQueue::attributesForServiceType($request->service_type),
            SalesQueue::attributesForSource('MANUAL_KIOSK'),
            SalesQueue::attributesForStatus('WAITING')
        ));

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
            $waitingClients = SalesQueue::with(['customer.catalogClientType', 'catalogClientType', 'catalogServiceType'])
                ->whereDate('queued_at', today())
                ->waiting()
                ->get()
                ->map(fn ($client) => array_merge($client->toArray(), $client->clientTypeMetadata(), [
                    'client_type' => $client->resolveClientTypeCode(),
                    'client_type_label' => $client->resolveClientTypeLabel(),
                ]));

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
            'abandonment_reason_id' => 'required|integer|exists:abandonment_reasons,id',
            'custom_abandonment_reason' => 'nullable|string'
        ]);

        $client = SalesQueue::findOrFail($id);

        if ($client->resolveStatusCode() === 'WAITING') {
            $client->update(array_merge(
                SalesQueue::attributesForStatus('ABANDONED'),
                [
                    'completed_at' => now(),
                    'abandonment_reason_id' => $validated['abandonment_reason_id'],
                    'custom_abandonment_reason' => $validated['custom_abandonment_reason'] ?? null,
                ]
            ));

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
    
    public function completePreliminar(Request $request, $id)
    {
        $pickup = Pickup::findOrFail($id);

        // Agregamos department y pieces a la validación
        $request->validate([
            'department' => 'required|exists:departments,name',
            'pieces' => 'required|integer|min:1',
            'client_name' => 'required|string|max:150',
            'customer_id' => 'nullable|exists:customers,id', // ID que viene del buscador
            'received_at' => 'required|date',
            'bags' => 'nullable|integer|min:1',
            'package_evidence' => 'required|image|max:5120',
            'notes' => 'nullable|string|max:500'
        ]);

        // Guardamos la foto tomada por el checador
        $evidencePath = $request->file('package_evidence')->store('pickups/package_evidence', 'public');
        
        $pendingStatusId = PickupStatus::where('code', 'PENDING_CONFIRMATION')->value('id');

        $finalNotes = $pickup->notes;
        if ($request->filled('notes')) {
            $finalNotes .= "\n[Checador]: " . $request->notes;
        }

        // Actualizamos todos los datos, incluyendo el departamento y las piezas corregidas
        $pickup->update([
            'department' => $request->department,
            'pieces' => $request->pieces,
            'client_name' => $request->client_name,
            'client_ref_id' => $request->customer_id ?? $pickup->client_ref_id,
            'bags' => $request->bags,
            'package_evidence_path' => $evidencePath,
            'notes' => $finalNotes,
            'status_id' => $pendingStatusId,
            'correction_notes' => null,
            'received_by_checker_at' => $request->received_at, 
        ]);

        return redirect()->route('recepcion.dashboard')->with('success', 'Información completada. Enviado a Gerencia para confirmación.');
    }

    /**
     * NUEVO FLUJO: Checador registra el resguardo desde cero
     */
    public function storePickup(Request $request)
    {
        $request->validate([
            'ticket_folio' => 'required|string|max:50|unique:pickups,ticket_folio',
            'department' => 'required|exists:departments,name',
            'client_name' => 'required|string|max:150',
            'customer_id' => 'nullable|exists:customers,id',
            'pieces' => 'required|integer|min:1',
            'bags' => 'required|integer|min:1',
            'ticket_evidence' => 'required|image|max:5120',
            'package_evidence' => 'required|image|max:5120',
            'notes' => 'nullable|string|max:500',
            'is_complementary' => 'nullable|boolean',
            'parent_folio' => 'nullable|string|required_if:is_complementary,1',
        ]);

        // Guardamos las dos evidencias en los campos que el gerente ya audita
        $ticketPath = $request->file('ticket_evidence')->store('pickups/initial_evidence', 'public');
        $packagePath = $request->file('package_evidence')->store('pickups/package_evidence', 'public');

        $pendingStatusId = PickupStatus::where('code', 'PENDING_CONFIRMATION')->value('id');

        // Búsqueda del folio base si es complementario
        $parentId = null;
        if ($request->filled('is_complementary') && $request->filled('parent_folio')) {
            $parentPickup = Pickup::where('ticket_folio', $request->parent_folio)->first();
            if ($parentPickup) {
                $parentId = $parentPickup->id;
            }
        }

        Pickup::create([
            'ticket_folio' => $request->ticket_folio,
            'ticket_date' => now(),
            'department' => $request->department,
            'client_name' => $request->client_name,
            'client_ref_id' => $request->customer_id,
            'pieces' => $request->pieces,
            'bags' => $request->bags,
            'initial_evidence_path' => $ticketPath,   // Aquí va el Ticket
            'package_evidence_path' => $packagePath,  // Aquí van las Bolsas
            'notes' => $request->filled('notes') ? "[Checador]: " . $request->notes : null,
            'is_complementary' => $request->boolean('is_complementary'),
            'parent_pickup_id' => $parentId,
            'status_id' => $pendingStatusId,
            'amount' => 0,
            'balance' => 0,
            'received_by_checker_at' => now(), // Se asume recibido al instante
        ]);

        return redirect()->route('recepcion.dashboard')->with('success', 'Resguardo registrado exitosamente. Enviado a Gerencia para auditoría.');
    }
}
