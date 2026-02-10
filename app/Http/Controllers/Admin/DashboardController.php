<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Muestra la pantalla principal del administrador.
     */
    public function index()
    {
        // AQUÍ es donde el Chef (Controlador) prepara los ingredientes.
        // En el futuro, aquí calcularemos:
        // $activeEmployees = Employee::where('is_active', true)->count();
        // $todaySales = SalesQueue::today()->count();
        
        // Por ahora, pasamos datos vacíos para probar la conexión
        return view('admin.dashboard');
    }
}