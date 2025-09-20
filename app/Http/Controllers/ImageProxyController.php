<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

class ImageProxyController extends Controller
{
    public function show(Request $request, $path)
    {
        // Decodificar el path
        $imagePath = 'products/' . $path;
        
        // Verificar que el archivo existe en R2
        if (!Storage::disk('private')->exists($imagePath)) {
            abort(404, 'Imagen no encontrada');
        }

        try {
            // Obtener el contenido del archivo desde R2
            $fileContent = Storage::disk('private')->get($imagePath);
            
            // Determinar el tipo MIME basado en la extensión
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $mimeType = match(strtolower($extension)) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                default => 'application/octet-stream'
            };

            // Crear respuesta con headers apropiados para caché
            return response($fileContent, 200, [
                'Content-Type' => $mimeType,
                'Content-Length' => strlen($fileContent),
                'Cache-Control' => 'public, max-age=31536000', // Cache por 1 año
                'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
                'Last-Modified' => gmdate('D, d M Y H:i:s', Storage::disk('private')->lastModified($imagePath)) . ' GMT',
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error serving image: ' . $e->getMessage(), [
                'path' => $imagePath,
                'requested_path' => $path
            ]);
            
            abort(500, 'Error al cargar la imagen');
        }
    }
}