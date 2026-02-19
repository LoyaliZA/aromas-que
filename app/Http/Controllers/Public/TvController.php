<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesQueue;
use App\Models\TvAd;

class TvController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Turnos en atención
            $serving = SalesQueue::with('assignedShift.employee')
                                 ->where('status', 'SERVING')
                                 ->orderBy('started_serving_at', 'desc')
                                 ->take(5)
                                 ->get();

            // Turnos en espera
            $waiting = SalesQueue::where('status', 'WAITING')
                                 ->orderBy('queued_at', 'asc')
                                 ->take(5)
                                 ->get();

            // NUEVO: Enviamos también los anuncios activos en formato JSON para Javascript
            $ads = [];
            if (class_exists(TvAd::class)) {
                $ads = TvAd::currentlyActive()->get()->map(function($ad) {
                    return [
                        'type' => $ad->media_type,
                        'url' => $ad->media_url,
                        'duration' => $ad->duration_seconds * 1000 // A milisegundos
                    ];
                });
            }

            return response()->json([
                'serving' => $serving,
                'waiting' => $waiting,
                'ads' => $ads
            ]);
        }

        // Carga inicial (Primera vez que se abre la página)
        $ads = [];
        if (class_exists(TvAd::class)) {
            $ads = TvAd::currentlyActive()->get();
        }

        return view('welcome', compact('ads'));
    }
}