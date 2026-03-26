<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SaleRating;
use App\Models\Employee;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Dashboard Principal del Módulo de Calidad
     */
    public function index(Request $request)
    {
        // Estadísticas Globales Históricas
        $totalReviews = SaleRating::count();
        $averageSystemRating = SaleRating::where('rater_type', 'CLIENT')->avg('stars');

        // Directorio de Vendedores con su promedio
        $sellers = Employee::sellers()->get()->map(function ($emp) {
            $ratings = SaleRating::where('rater_type', 'CLIENT')
                ->whereHas('salesQueue.assignedShift', function ($q) use ($emp) {
                    $q->where('employee_id', $emp->id);
                })->get();

            $emp->avg_stars = $ratings->count() > 0 ? round($ratings->avg('stars'), 1) : null;
            $emp->total_reviews = $ratings->count();
            return $emp;
        })->sortByDesc('avg_stars')->values(); // <--- AQUÍ ESTÁ EL FIX

        // Flujo general de reseñas recientes (con filtros)
        $query = SaleRating::with(['salesQueue.assignedShift.employee', 'salesQueue.customer']);

        if ($request->filled('type')) {
            $query->where('rater_type', $request->type); // CLIENT o SELLER
        }

        if ($request->filled('stars')) {
            $query->where('stars', $request->stars);
        }

        $recentReviews = $query->orderBy('created_at', 'desc')->paginate(15)->appends(request()->query());

        return view('admin.reviews.index', compact('sellers', 'recentReviews', 'totalReviews', 'averageSystemRating'));
    }

    /**
     * Perfil Individual del Vendedor (Lo que opinan los clientes de él)
     */
    public function showSeller(Request $request, $id)
    {
        $seller = Employee::findOrFail($id);

        $query = SaleRating::with(['salesQueue.customer'])
            ->where('rater_type', 'CLIENT')
            ->whereHas('salesQueue.assignedShift', function ($q) use ($seller) {
                $q->where('employee_id', $seller->id);
            });

        $totalReviews = (clone $query)->count();
        $avgStars = $totalReviews > 0 ? round((clone $query)->avg('stars'), 1) : 0;
        
        // FIX: Usar clone para no ensuciar la consulta principal con el GROUP BY
        $starsDistribution = (clone $query)->select('stars', DB::raw('count(*) as total'))
                                   ->groupBy('stars')
                                   ->pluck('total', 'stars')
                                   ->toArray();

        // Ordenamiento por URL
        if ($request->input('sort') === 'worst') {
            $query->orderBy('stars', 'asc');
        } elseif ($request->input('sort') === 'best') {
            $query->orderBy('stars', 'desc');
        }
        
        $reviews = $query->orderBy('created_at', 'desc')->paginate(10)->appends(request()->query());

        return view('admin.reviews.seller', compact('seller', 'reviews', 'avgStars', 'totalReviews', 'starsDistribution'));
    }

    /**
     * Perfil Individual del Cliente (Lo que opinan los vendedores de él)
     */
    public function showCustomer(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $query = SaleRating::with(['salesQueue.assignedShift.employee'])
            ->where('rater_type', 'SELLER')
            ->whereHas('salesQueue', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            });

        $totalReviews = (clone $query)->count();
        $avgStars = $totalReviews > 0 ? round((clone $query)->avg('stars'), 1) : 0;

        // FIX: Usar clone para no ensuciar la consulta principal con el GROUP BY
        $starsDistribution = (clone $query)->select('stars', DB::raw('count(*) as total'))
                                   ->groupBy('stars')
                                   ->pluck('total', 'stars')
                                   ->toArray();

        if ($request->input('sort') === 'worst') {
            $query->orderBy('stars', 'asc');
        } elseif ($request->input('sort') === 'best') {
            $query->orderBy('stars', 'desc');
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(10)->appends(request()->query());

        return view('admin.reviews.customer', compact('customer', 'reviews', 'avgStars', 'totalReviews', 'starsDistribution'));
    }

    /**
     * Buscador Global AJAX para Expedientes
     */
    public function search(Request $request)
    {
        $q = $request->get('q');
        
        if (empty($q) || strlen($q) < 2) {
            return response()->json(['sellers' => [], 'customers' => []]);
        }

        // Buscar Vendedores (Por Nombre o Código)
        $sellers = Employee::sellers()
            ->where(function($query) use ($q) {
                $query->where('full_name', 'like', "%{$q}%")
                      ->orWhere('employee_code', 'like', "%{$q}%");
            })
            ->select('id', 'full_name', 'employee_code')
            ->limit(5)
            ->get();

        // Buscar Clientes (Por Nombre o Número)
        $customers = Customer::where('name', 'like', "%{$q}%")
            ->orWhere('customer_number', 'like', "%{$q}%")
            ->select('id', 'name', 'customer_number', 'client_type')
            ->limit(5)
            ->get();

        return response()->json([
            'sellers' => $sellers,
            'customers' => $customers
        ]);
    }
}
