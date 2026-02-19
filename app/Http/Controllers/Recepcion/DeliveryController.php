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
        // 1. Iniciamos consulta BASE con SEGURIDAD
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
        $pickups = $query->orderBy('created_at', 'desc')
                         ->paginate(9)
                         ->withQueryString();
        
        // CÁLCULO DE LA FILA
        $peopleInQueue = SalesQueue::where('status', 'WAITING')->count();

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
     * CONFIRMAR ENTREGA
     */
    public function confirm(Request $request, $id)
    {
        $request->validate([
            'signature' => 'required|string',
            'is_third_party' => 'nullable',
            'receiver_name' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:500',
            'evidence_file' => 'nullable|image|max:10240',
        ]);

        $pickup = Pickup::findOrFail($id);
        
        $signatureBase64 = $request->signature;
        $signatureBase64 = str_replace('data:image/png;base64,', '', $signatureBase64);
        $signatureBase64 = str_replace(' ', '+', $signatureBase64);
        $sigFilename = 'signatures/pickup_' . $pickup->ticket_folio . '_' . time() . '.png';
        Storage::disk('public')->put($sigFilename, base64_decode($signatureBase64));

        $evidencePath = null;
        if ($request->hasFile('evidence_file')) {
            $evidencePath = $request->file('evidence_file')->store('evidence', 'public');
        }

        $isThirdParty = $request->has('is_third_party');
        $receiverName = $isThirdParty ? $request->receiver_name : $pickup->client_name;

        $pickup->markAsDelivered(
            $receiverName,
            $isThirdParty,
            $sigFilename,
            $evidencePath,
            $request->notes 
        );

        return redirect()->route('recepcion.dashboard')->with('success', "Entrega confirmada: {$pickup->ticket_folio}");
    }

    /**
     * AGREGAR A FILA (Kiosco Manual)
     */
    public function addToQueue(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string|max:100',
            'service_type' => 'required|in:SALES,CASHIER',
        ]);

        // --- LÓGICA DE NÚMERO DE TURNO AUTOMÁTICO ---
        
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
            'turn_number' => $turnNumber, // <-- Se inserta el nuevo número
            'source' => 'MANUAL_KIOSK',
            'status' => 'WAITING',
            'service_type' => $request->service_type,
            'queued_at' => now(),
        ]);

        $tipo = $request->service_type === 'SALES' ? 'Ventas' : 'Caja';

        // Retornamos pasando el turno creado para que la tablet pueda mostrarlo en grande
        return redirect()->route('recepcion.dashboard')
                         ->with('success', "Cliente agregado a la fila.")
                         ->with('new_turn', $turnNumber)
                         ->with('client_name', $request->client_name)
                         ->with('destination', $tipo);
    }
}