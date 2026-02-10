<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PickupController extends Controller
{
    /**
     * Muestra el Dashboard de Gerencia (Lista de Resguardos).
     */
    public function index()
    {
        // Por ahora, solo retornaremos un texto simple para probar que la conexión funciona.
        // Más adelante, aquí cargaremos la vista 'gerencia.dashboard'.
        return "Hola Gerente, aquí verás tus resguardos.";
    }
}