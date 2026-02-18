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
     * Muestra lista de paquetes y opciones de fila.
     */
    public function index(Request $request)
    {
        // 1. Iniciamos consulta BASE con SEGURIDAD
        // IMPORTANTE: Usamos el scope 'visibleForChecker' para ocultar rezagados (>15 días)
        // Esto cumple la nueva regla de negocio: El checador ya no gestiona lo viejo.
        $query = Pickup::visibleForChecker();

        // 2. Filtros (Mantenemos la lógica de filtrado existente)
        if ($request->has('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        } else {
            // Por defecto, solo mostrar lo que está en custodia (pendiente)
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

        // Ordenamos: Primero lo más reciente
        $pickups = $query->orderBy('created_at', 'desc')
                         ->paginate(9)
                         ->withQueryString();
        
        // CÁLCULO DE LA FILA
        // Mostramos cuánta gente hay esperando en total (Ventas + Caja)
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
     * CONFIRMAR ENTREGA (Con Firma y Evidencia)
     */
    public function confirm(Request $request, $id)
    {
        $request->validate([
            'signature' => 'required|string', // Base64 de la firma
            'is_third_party' => 'nullable',
            'receiver_name' => 'nullable|string|max:150',
            // NUEVO: Validamos observaciones y foto
            'notes' => 'nullable|string|max:500',
            'evidence_file' => 'nullable|image|max:10240', // Máx 10MB (ajustable)
        ]);

        $pickup = Pickup::findOrFail($id);
        
        // 1. Procesar Firma (Base64 -> Imagen PNG)
        $signatureBase64 = $request->signature;
        $signatureBase64 = str_replace('data:image/png;base64,', '', $signatureBase64);
        $signatureBase64 = str_replace(' ', '+', $signatureBase64);
        $sigFilename = 'signatures/pickup_' . $pickup->ticket_folio . '_' . time() . '.png';
        Storage::disk('public')->put($sigFilename, base64_decode($signatureBase64));

        // 2. Procesar Evidencia (Foto subida desde la cámara/input file)
        $evidencePath = null;
        if ($request->hasFile('evidence_file')) {
            // Guardamos en la carpeta 'evidence' dentro del disco público
            $evidencePath = $request->file('evidence_file')->store('evidence', 'public');
        }

        // 3. Guardar usando el Helper del Modelo (Limpio y encapsulado)
        $isThirdParty = $request->has('is_third_party');
        $receiverName = $isThirdParty ? $request->receiver_name : $pickup->client_name;

        // Llamamos al método inteligente que creamos en el Paso 4
        $pickup->markAsDelivered(
            $receiverName,
            $isThirdParty,
            $sigFilename,
            $evidencePath, // Pasamos la foto (puede ser null)
            $request->notes // Pasamos las notas
        );

        return redirect()->route('recepcion.dashboard')->with('success', "Entrega confirmada: {$pickup->ticket_folio}");
    }

    /**
     * AGREGAR A FILA (Kiosco Manual)
     * Ahora soporta Ventas vs Caja
     */
    public function addToQueue(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string|max:100',
            'service_type' => 'required|in:SALES,CASHIER', // NUEVO: Validamos el destino
        ]);

        SalesQueue::create([
            'client_name' => $request->client_name,
            'source' => 'MANUAL_KIOSK',
            'status' => 'WAITING',
            'service_type' => $request->service_type, // Guardamos si va a Caja o Ventas
            'queued_at' => now(),
        ]);

        $tipo = $request->service_type === 'SALES' ? 'Ventas' : 'Caja';

        return redirect()->route('recepcion.dashboard')
                         ->with('success', "Cliente agregado a la fila de {$tipo}.");
    }
}