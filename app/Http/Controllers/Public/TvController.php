<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SalesQueue;
use App\Models\TvAd;
use Illuminate\Http\Request;

class TvController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $serving = SalesQueue::with(['assignedShift.employee', 'catalogClientType'])
                ->serving()
                ->orderBy('started_serving_at', 'desc')
                ->take(5)
                ->get()
                ->map(fn ($ticket) => $this->formatTicket($ticket));

            $waiting = SalesQueue::waiting()
                ->with('catalogClientType')
                ->where(function ($q) {
                    $q->whereDoesntHave('catalogClientType', fn ($q2) => $q2->where('hide_on_public_tv', true))
                        ->orWhereNull('client_type_id');
                })
                ->take(5)
                ->get()
                ->map(fn ($ticket) => $this->formatTicket($ticket));

            $ads = [];
            if (class_exists(TvAd::class)) {
                $ads = TvAd::currentlyActive()->orderBy('order_index', 'asc')->get()->map(function ($ad) {
                    return [
                        'type' => $ad->media_type,
                        'url' => $ad->media_url,
                        'duration' => $ad->duration_seconds * 1000,
                        'volume' => ($ad->volume ?? 100) / 100,
                    ];
                });
            }

            return response()->json([
                'serving' => $serving,
                'waiting' => $waiting,
                'ads' => $ads,
            ]);
        }

        $ads = [];
        if (class_exists(TvAd::class)) {
            $ads = TvAd::currentlyActive()->get();
        }

        return view('welcome', compact('ads'));
    }

    private function formatTicket(SalesQueue $ticket): array
    {
        return array_merge($ticket->toArray(), $ticket->clientTypeMetadata(), [
            'client_type' => $ticket->resolveClientTypeCode(),
            'client_type_label' => $ticket->resolveClientTypeLabel(),
        ]);
    }
}
