<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pickup;

class CedisController extends Controller
{
    public function index()
    {
        // Traemos Bellaroma y Call Center
        $remissions = Pickup::whereIn('department', ['BELLAROMA', 'CALLCENTER'])
            // Filtramos a través de la nueva relación del catálogo
            ->whereHas('currentStatus', function ($query) {
                $query->where('code', '!=', 'DELIVERED');
            })
            // Cargamos las relaciones para evitar lentitud (N+1)
            ->with(['logistic', 'customer', 'currentStatus']) 
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('cedis.dashboard', compact('remissions'));
    }
}