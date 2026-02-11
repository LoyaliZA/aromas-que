<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    /**
     * Dashboard del Vendedor (Para atender turnos).
     */
    public function index()
    {
        return "Hola Vendedor, aquí verás la cola de espera.";
    }
}