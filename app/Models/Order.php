<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_number',
        'customer_id',
        'total_amount',
        'status',
        'shipping_address',
        'billing_address',
        'notes',
        'assigned_to',
        'shipped_at',
        'delivered_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];


    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }


    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }


    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }


    public function isPending(): bool
    {
        return $this->status === 'pending';
    }


    public function isShipped(): bool
    {
        return $this->status === 'shipped';
    }


    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function markAsShipped(): bool
    {
        $this->status = 'shipped';
        $this->shipped_at = now();
        return $this->save();
    }


    public function markAsDelivered(): bool
    {
        $this->status = 'delivered';
        $this->delivered_at = now();
        return $this->save();
    }

    public function assignTo(User $user): bool
    {
        $this->assigned_to = $user->id;
        return $this->save();
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(uniqid());
    }


    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }
}
