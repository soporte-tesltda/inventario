<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'product_type',
    ];

    protected $casts = [
        'product_type' => 'string',
    ];

    public function isHardware(): bool
    {
        return $this->product_type === 'hardware';
    }

    public function isConsumable(): bool
    {
        return $this->product_type === 'consumable';
    }

    /**
     * Generar un slug único basado en el título de la categoría
     */
    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        // Limpiar el título y crear slug base
        $baseSlug = Str::slug($title);
        
        // Si el slug está vacío después de limpiar, usar un valor por defecto
        if (empty($baseSlug)) {
            $baseSlug = 'categoria';
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): HasMany
    {
        return $this->hasMany(ProductSupplier::class);
    }
}
