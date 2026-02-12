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
    public function index(Request $request)
    {
        // ... (Toda la lógica de consulta $query se mantiene IGUAL) ...
        $query = Pickup::query();

        // Filtros (Mismo código de antes)
        if ($request->has('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'IN_CUSTODY');
        }
        if ($request->has('department') && $request->department !== 'ALL') {
            $query->where('department', $request->department);
        }
        // Buscador (Mismo código de antes)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_folio', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%")
                  ->orWhere('client_ref_id', 'like', "%{$search}%")
                  ->orWhere('receiver_name', 'like', "%{$search}%");
            });
        }

        $pickups = $query->orderBy('created_at', 'desc')->paginate(9)->withQueryString();
        
        // CÁLCULO DE LA FILA (Lo necesitamos siempre)
        $peopleInQueue = SalesQueue::where('status', 'WAITING')->count();

        // --- CAMBIO CLAVE AQUÍ ---
        if ($request->ajax()) {
            // Renderizamos la vista parcial a HTML
            $html = view('recepcion.partials.card-grid', compact('pickups'))->render();
            
            // Devolvemos JSON con AMBOS datos: el HTML de la lista y el número de la fila
            return response()->json([
                'html' => $html,
                'queueCount' => $peopleInQueue
            ]);
        }

        return view('recepcion.dashboard', compact('pickups', 'peopleInQueue'));
    }

    public function confirm(Request $request, $id)
    {
        $request->validate([
            'signature' => 'required|string',
            'is_third_party' => 'nullable',
            'receiver_name' => 'nullable|string|max:150',
        ]);

        $pickup = Pickup::findOrFail($id);
        
        // Procesar imagen Base64 y guardar en Disco Público
        $image = $request->signature;
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $filename = 'signatures/pickup_' . $pickup->ticket_folio . '_' . time() . '.png';
        
        Storage::disk('public')->put($filename, base64_decode($image));

        $pickup->update([
            'status' => 'DELIVERED',
            'delivered_at' => now(),
            'signature_path' => $filename,
            'is_third_party' => $request->has('is_third_party'),
            'receiver_name' => $request->has('is_third_party') ? $request->receiver_name : $pickup->client_name,
        ]);

        return redirect()->route('recepcion.dashboard')->with('success', "Entrega confirmada: {$pickup->ticket_folio}");
    }

    public function addToQueue(Request $request)
    {
        $request->validate(['client_name' => 'required|string|max:100']);
        SalesQueue::create([
            'client_name' => $request->client_name,
            'source' => 'MANUAL_KIOSK',
            'status' => 'WAITING',
            'queued_at' => now(),
        ]);
        return redirect()->route('recepcion.dashboard')->with('success', 'Cliente agregado a la fila.');
    }
}