<?php

namespace App\Http\Controllers\Recepcion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    /**
     * Muestra el Dashboard de Recepción (Checador).
     */
    public function index()
    {
        return "Hola Checador, aquí entregarás los pedidos.";
    }
}