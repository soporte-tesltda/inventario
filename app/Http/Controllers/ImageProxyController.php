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
        
        // Configurar timeout ultra corto para evitar gateway timeouts
        set_time_limit(3); // Máximo 3 segundos - muy agresivo
        
        try {
            // PASO 1: Servir desde caché inmediatamente si existe
            $cachedImage = Cache::get($cacheKey);
            if ($cachedImage) {
                return $this->serveImage($cachedImage['content'], $cachedImage['mime_type'], $path);
            }
            
            // PASO 2: Si no hay caché, verificar si podemos hacer una descarga rápida
            $quickDownloadKey = 'quick_download_' . md5($imagePath);
            if (Cache::has($quickDownloadKey)) {
                // Ya intentamos descargar esta imagen recientemente, servir placeholder
                return $this->servePlaceholder($path);
            }
            
            // PASO 3: Marcar que estamos intentando descargar para evitar requests concurrentes
            Cache::put($quickDownloadKey, true, 30); // 30 segundos
            
            // PASO 4: Verificación ultrarrápida de existencia
            if (!Storage::disk('private')->exists($imagePath)) {
                Cache::forget($quickDownloadKey);
                return $this->servePlaceholder($path, 404);
            }

            // PASO 5: Descarga con timeout súper agresivo
            $startTime = microtime(true);
            $fileContent = Storage::disk('private')->get($imagePath);
            $downloadTime = microtime(true) - $startTime;
            
            if (!$fileContent || $downloadTime > 2) { // Si tarda más de 2 segundos
                Cache::forget($quickDownloadKey);
                return $this->servePlaceholder($path);
            }
            
            // PASO 6: Procesar y cachear rápidamente
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $mimeType = match(strtolower($extension)) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                default => 'application/octet-stream'
            };

            // Cachear por 24 horas en lugar de 1 hora
            Cache::put($cacheKey, [
                'content' => $fileContent,
                'mime_type' => $mimeType,
                'cached_at' => now(),
                'download_time' => $downloadTime
            ], 86400); // 24 horas
            
            Cache::forget($quickDownloadKey); // Limpiar flag de descarga

            return $this->serveImage($fileContent, $mimeType, $path);
            
        } catch (\Exception $e) {
            Cache::forget($quickDownloadKey);
            
            \Log::error('Error serving image: ' . $e->getMessage(), [
                'path' => $imagePath,
                'requested_path' => $path,
                'error_type' => get_class($e),
                'memory_usage' => memory_get_usage(true),
                'time_limit' => ini_get('max_execution_time')
            ]);
            
            // Servir placeholder en caso de error
            return $this->servePlaceholder($path);
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
            'X-Served-By' => 'ImageProxy-Cache',
        ]);
    }
    
    private function servePlaceholder($path, $statusCode = 202)
    {
        // Crear un SVG placeholder simple y rápido
        $svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg width="300" height="200" xmlns="http://www.w3.org/2000/svg">
    <rect width="100%" height="100%" fill="#f3f4f6"/>
    <text x="50%" y="45%" dominant-baseline="middle" text-anchor="middle" font-family="Arial, sans-serif" font-size="14" fill="#6b7280">
        Cargando imagen...
    </text>
    <text x="50%" y="65%" dominant-baseline="middle" text-anchor="middle" font-family="Arial, sans-serif" font-size="10" fill="#9ca3af">
        ' . basename($path) . '
    </text>
</svg>';

        return response($svg, $statusCode, [
            'Content-Type' => 'image/svg+xml',
            'Content-Length' => strlen($svg),
            'Cache-Control' => 'no-cache, no-store, must-revalidate', // No cachear placeholder
            'X-Served-By' => 'ImageProxy-Placeholder',
            'Refresh' => '5', // Auto-refresh después de 5 segundos
        ]);
    }
}