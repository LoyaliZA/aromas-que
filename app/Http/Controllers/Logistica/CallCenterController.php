<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use App\Models\Pickup;
use Illuminate\Http\Request;

class CallCenterController extends Controller
{
    public function index()
    {
        $remissions = Pickup::where('department', 'CALLCENTER')
            ->whereHas('currentStatus', function ($query) {
                // Ocultamos los entregados y cancelados para limpiar la vista
                $query->whereNotIn('code', ['DELIVERED', 'CANCELLED']);
            })
            ->with(['logistic', 'customer', 'currentStatus', 'seller'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('callcenter.dashboard', compact('remissions'));
    }
}