<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class ImageProxyController extends Controller
{
    public function show(Request $request, $path)
    {
        // Decodificar el path
        $imagePath = 'products/' . $path;
        $cacheKey = 'image_cache_' . md5($imagePath);
        
        // Configurar timeout corto para evitar gateway timeouts
        set_time_limit(15); // Máximo 15 segundos
        
        try {
            // Intentar servir desde caché local primero
            $cachedImage = Cache::get($cacheKey);
            if ($cachedImage) {
                return $this->serveImage($cachedImage['content'], $cachedImage['mime_type'], $path);
            }
            
            // Verificar que el archivo existe en R2 con timeout
            if (!Storage::disk('private')->exists($imagePath)) {
                abort(404, 'Imagen no encontrada');
            }

            // Obtener el contenido del archivo desde R2 con timeout optimizado
            $fileContent = Storage::disk('private')->get($imagePath);
            
            if (!$fileContent) {
                abort(404, 'Imagen no disponible');
            }
            
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

            // Cachear la imagen por 1 hora para evitar descargas repetidas
            Cache::put($cacheKey, [
                'content' => $fileContent,
                'mime_type' => $mimeType,
                'cached_at' => now()
            ], 3600); // 1 hora

            return $this->serveImage($fileContent, $mimeType, $path);
            
        } catch (\Exception $e) {
            \Log::error('Error serving image: ' . $e->getMessage(), [
                'path' => $imagePath,
                'requested_path' => $path,
                'error_type' => get_class($e)
            ]);
            
            // En caso de error, devolver respuesta rápida en lugar de 500
            return response('Image temporarily unavailable', 503, [
                'Content-Type' => 'text/plain',
                'Retry-After' => '60' // Reintentar en 60 segundos
            ]);
        }
    }
    
    private function serveImage($fileContent, $mimeType, $path)
    {
        return response($fileContent, 200, [
            'Content-Type' => $mimeType,
            'Content-Length' => strlen($fileContent),
            'Cache-Control' => 'public, max-age=2592000', // Cache por 30 días
            'Expires' => gmdate('D, d M Y H:i:s', time() + 2592000) . ' GMT',
            'ETag' => '"' . md5($fileContent) . '"',
            'X-Served-By' => 'ImageProxy',
        ]);
    }
}