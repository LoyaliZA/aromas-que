<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TvAd;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class TvAdController extends Controller
{
    public function index(Request $request)
    {
        $query = TvAd::query();

        // Implementación de Filtros
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('date')) {
            $date = $request->date;
            $query->where(function($q) use ($date) {
                // Si la fecha cae entre el inicio y fin, o si no tiene límites
                $q->whereDate('start_date', '<=', $date)
                  ->whereDate('end_date', '>=', $date)
                  ->orWhere(function($subQ) {
                      $subQ->whereNull('start_date')->whereNull('end_date');
                  });
            });
        }

        $ads = $query->orderBy('created_at', 'desc')->get();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && ($user->isAuxiliar() || ($user->isAdmin() && request()->has('preview')))) {
            return view('auxiliar.dashboard', compact('ads'));
        }

        return view('admin.tv_ads.index', compact('ads'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'media_file' => 'required|file|mimes:jpeg,png,jpg,mp4|max:51200', 
            'duration_seconds' => 'nullable|numeric|min:1', // Permitimos nullable y numeric para cálculo JS
            'start_date' => 'nullable|date_format:Y-m-d\TH:i', // Formato de input datetime-local HTML5
            'end_date' => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:start_date',
        ]);

        try {
            $file = $request->file('media_file');
            $extension = strtolower($file->getClientOriginalExtension());
            $mediaType = in_array($extension, ['mp4']) ? 'VIDEO' : 'IMAGE';

            $path = $file->store('tv_ads', 'public');

            // Si es imagen y no envían duración, por defecto 15s. Si es video, guardamos lo calculado por JS.
            $duration = $mediaType === 'VIDEO' 
                ? round((float) $request->duration_seconds ?? 0) 
                : ($request->duration_seconds ?? 15);

            TvAd::create([
                'title' => $request->title,
                'media_path' => $path,
                'media_type' => $mediaType,
                'duration_seconds' => $duration,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => true,
            ]);

            return redirect()->back()->with('success', 'Anuncio subido y configurado correctamente.');
            
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Error al procesar el archivo: ' . $e->getMessage()]);
        }
    }

    public function toggle(TvAd $tvAd)
    {
        $tvAd->update(['is_active' => !$tvAd->is_active]);
        return redirect()->back()->with('success', 'Estado del anuncio actualizado.');
    }

    public function destroy(TvAd $tvAd)
    {
        if (Storage::disk('public')->exists($tvAd->media_path)) {
            Storage::disk('public')->delete($tvAd->media_path);
        }
        $tvAd->delete();
        return redirect()->back()->with('success', 'Anuncio eliminado correctamente.');
    }

    /**
     * Actualizar la información de un anuncio (Sin cambiar el archivo).
     */
    public function update(Request $request, TvAd $tvAd)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'duration_seconds' => 'required|numeric|min:5|max:120',
            'start_date' => 'nullable|date_format:Y-m-d\TH:i',
            'end_date' => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:start_date',
        ]);

        $tvAd->update([
            'title' => $request->title,
            'duration_seconds' => $request->duration_seconds,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->back()->with('success', 'Anuncio actualizado correctamente.');
    }
}