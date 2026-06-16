<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pickup;

class BellaromaController extends Controller
{
    /**
     * Muestra el dashboard principal de Bellaroma con sus remisiones ACTIVAS.
     */
    public function index()
    {
        $remissions = Pickup::byDepartment('BELLAROMA')
            ->whereHas('currentStatus', function ($query) {
                // Ocultamos los entregados y cancelados del panel de operación diaria
                $query->whereNotIn('code', ['DELIVERED', 'CANCELLED']);
            })
            ->with(['logistic', 'currentStatus', 'seller'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('bellaroma.dashboard', compact('remissions'));
    }

    /**
     * Muestra el historial completo de Bellaroma (incluyendo los Entregados).
     */
    public function history(Request $request)
    {
        $query = Pickup::byDepartment('BELLAROMA')
            ->with(['logistic', 'currentStatus', 'seller']);
            
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('ticket_folio', 'like', '%' . $request->search . '%')
                  ->orWhere('client_name', 'like', '%' . $request->search . '%');
            });
        }

        $remissions = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('bellaroma.history', compact('remissions'));
    }
}