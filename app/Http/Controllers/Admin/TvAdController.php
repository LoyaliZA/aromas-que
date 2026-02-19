<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TvAd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TvAdController extends Controller
{
    // Mostrar la lista de anuncios
    public function index()
    {
        $ads = TvAd::orderBy('created_at', 'desc')->get();
        return view('admin.tv_ads.index', compact('ads'));
    }

    // Guardar un nuevo anuncio
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            // Aceptamos imágenes y videos de hasta 50MB
            'media_file' => 'required|file|mimes:jpeg,png,jpg,mp4|max:51200', 
            'duration_seconds' => 'required|integer|min:5|max:120',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $file = $request->file('media_file');
        $extension = $file->getClientOriginalExtension();
        
        // Magia: Detectamos automáticamente si subiste un video o una foto
        $mediaType = in_array(strtolower($extension), ['mp4']) ? 'VIDEO' : 'IMAGE';

        // Guardamos el archivo en la carpeta storage/app/public/tv_ads
        $path = $file->store('tv_ads', 'public');

        TvAd::create([
            'title' => $request->title,
            'media_path' => $path,
            'media_type' => $mediaType,
            'duration_seconds' => $request->duration_seconds,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => true, // Por defecto se sube encendido
        ]);

        return redirect()->back()->with('success', 'Anuncio subido y configurado correctamente.');
    }

    // Botón rápido para apagar/prender un anuncio sin borrarlo
    public function toggle(TvAd $tvAd)
    {
        $tvAd->update(['is_active' => !$tvAd->is_active]);
        return redirect()->back()->with('success', 'Estado del anuncio actualizado.');
    }

    // Eliminar anuncio permanentemente (Borra BD y Archivo físico)
    public function destroy(TvAd $tvAd)
    {
        if (Storage::disk('public')->exists($tvAd->media_path)) {
            Storage::disk('public')->delete($tvAd->media_path);
        }
        $tvAd->delete();
        
        return redirect()->back()->with('success', 'Anuncio eliminado de la TV.');
    }
}