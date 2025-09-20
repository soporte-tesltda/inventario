<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'serial',
        'location',
        'rental_status',
        'company_client',
        'area',
        'detailed_location',
        'brand_model',
        'expiration_date',
        'image',
        'price',
        'quantity',
        'product_categories_id',
        'product_suppliers_id',
        // Campos específicos para computadores de escritorio (legacy)
        'monitor_serial',
        'monitor_brand_model',
        'monitor_status',
        'keyboard_serial',
        'keyboard_brand_model',
        'keyboard_status',
        'mouse_serial',
        'mouse_brand_model',
        'mouse_status',
        'processor',
        'ram_memory',
        'storage_type',
        'storage_capacity',
        'operating_system',
        'additional_components',
        // Nuevos campos para computadores modernos
        'computer_type',
        'computer_brand',
        'computer_model',
        'computer_serial',
        'computer_status',
        'computer_location',
        'assigned_user',
        'mouse_info',
        'keyboard_info',
        'charger_info',
        'monitor_info',
        'accessories',
        'computer_specifications',
        'computer_observations',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'expiration_date' => 'date',
    ];    protected static function boot()
    {
        parent::boot();
    }

    /**
     * Generar un slug único basado en el nombre del producto
     */
    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        // Limpiar el nombre y crear slug base
        $baseSlug = Str::slug($name);
        
        // Si el slug está vacío después de limpiar, usar un valor por defecto
        if (empty($baseSlug)) {
            $baseSlug = 'producto';
        }
        
        $slug = $baseSlug;
        $counter = 1;

        while (static::slugExists($slug, $ignoreId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Verificar si un slug ya existe
     */
    private static function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = static::where('slug', $slug);
        
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }
        
        return $query->exists();
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(ProductSupplier::class, 'product_suppliers_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_categories_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function activeContract(): HasOne
    {
        return $this->hasOne(Contract::class)->where('status', 'activo')->latest();
    }

    /**
     * Accessor para la imagen que maneja las rutas correctamente con URLs firmadas para S3
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        // Si la imagen está en S3 (prefijo products/), construir URL correcta para Cloudflare R2
        if (str_starts_with($this->image, 'products/')) {
            // Usar configuración del disco directamente (con fallbacks incorporados)
            $diskConfig = config('filesystems.disks.private');
            $endpoint = $diskConfig['endpoint'] ?? null;
            $bucket = $diskConfig['bucket'] ?? null;
            
            // Si tenemos endpoint y bucket, construir URL manualmente para path-style
            if ($endpoint && $bucket) {
                // Para Cloudflare R2 con path-style: endpoint/bucket/file
                return "{$endpoint}/{$bucket}/{$this->image}";
            }
            
            // Fallback: usar Storage URL nativo
            try {
                return \Illuminate\Support\Facades\Storage::disk('private')->url($this->image);
            } catch (\Exception $e) {
                return null;
            }
        }

        // Si la imagen ya tiene el prefijo storage, devolverla tal como está
        if (str_starts_with($this->image, 'storage/')) {
            return asset($this->image);
        }

        // Para imágenes sin prefijo, asumir que están en el directorio raíz de storage público
        return asset('storage/' . $this->image);
    }

    /**
     * Verificar si la imagen existe físicamente
     */
    public function getImageExistsAttribute(): bool
    {
        if (!$this->image) {
            return false;
        }

        // Si es una imagen de S3, verificar en el disco privado
        if (str_starts_with($this->image, 'products/')) {
            return \Illuminate\Support\Facades\Storage::disk('private')->exists($this->image);
        }

        $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');
        
        // Verificar diferentes posibilidades de ubicación
        return $publicDisk->exists($this->image) || 
               $publicDisk->exists('products/' . $this->image) ||
               $publicDisk->exists(str_replace('products/', '', $this->image));
    }
}
