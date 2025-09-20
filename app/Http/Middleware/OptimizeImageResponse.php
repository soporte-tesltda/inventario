<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OptimizeImageResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Solo aplicar a rutas de imágenes
        if (str_contains($request->path(), 'images/products/')) {
            // Configurar headers adicionales para optimización
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'DENY');
            
            // Añadir compresión si está disponible
            if (function_exists('gzencode') && $request->header('Accept-Encoding')) {
                $acceptEncoding = $request->header('Accept-Encoding');
                if (str_contains($acceptEncoding, 'gzip') && $response->getContent()) {
                    $content = $response->getContent();
                    if (strlen($content) > 1024) { // Solo comprimir archivos > 1KB
                        $compressed = gzencode($content, 6); // Nivel 6 de compresión
                        if ($compressed && strlen($compressed) < strlen($content)) {
                            $response->setContent($compressed);
                            $response->headers->set('Content-Encoding', 'gzip');
                            $response->headers->set('Content-Length', strlen($compressed));
                        }
                    }
                }
            }
        }
        
        return $response;
    }
}