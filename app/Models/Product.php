<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'stock_quantity',
        'sku',
        'image_url',
        'is_active',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
    ];


    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }


    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }


    public function decreaseStock(int $quantity): bool
    {
        if ($this->stock_quantity >= $quantity) {
            $this->stock_quantity -= $quantity;
            return $this->save();
        }
        return false;
    }


    public function increaseStock(int $quantity): bool
    {
        $this->stock_quantity += $quantity;
        return $this->save();
    }


    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }


    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
