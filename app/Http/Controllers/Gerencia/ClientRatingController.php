<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesQueue;
use App\Models\SaleRating;

class ClientRatingController extends Controller
{
    public function index()
    {
        return view('gerencia.client-rating');
    }

    public function getRecentSales()
    {
        // Traemos las ventas completadas en las últimas 2 horas para que el gerente elija
        $sales = SalesQueue::with('assignedShift.employee')
            ->where('status', 'COMPLETED')
            ->where('completed_at', '>=', now()->subHours(2))
            // NUEVO FILTRO: Excluir las ventas que ya tengan una calificación registrada por un CLIENTE
            ->whereNotIn('id', function ($query) {
                $query->select('sales_queue_id')
                    ->from('sale_ratings')
                    ->where('rater_type', 'CLIENT');
            })
            ->orderBy('completed_at', 'desc')
            ->get();

        return response()->json(['sales' => $sales]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'queue_id' => 'required|exists:sales_queue,id',
            'stars' => 'required|integer|min:1|max:5',
            'tags' => 'nullable|array',
            'comments' => 'nullable|string'
        ]);

        // Validar que el cliente no haya calificado ya esta misma venta
        $alreadyRated = SaleRating::where('sales_queue_id', $request->queue_id)
            ->where('rater_type', 'CLIENT')
            ->exists();

        if (!$alreadyRated) {
            SaleRating::create([
                'sales_queue_id' => $request->queue_id,
                'rater_type' => 'CLIENT',
                'stars' => $request->stars,
                'tags' => $request->tags ?? [], // El casteo en el modelo lo vuelve JSON
                'comments' => $request->comments
            ]);
        }

        return response()->json(['success' => true]);
    }
}
