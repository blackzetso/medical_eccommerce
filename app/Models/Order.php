<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'billing_address',
        'shipping_address',
        'payment_method',
        'payment_status',
        'notes',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // العلاقات
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // حالات الطلب
    public static function getStatuses(): array
    {
        return [
            'pending' => 'جديد',
            'processing' => 'قيد المعالجة',
            'shipped' => 'خرج للتوصيل',
            'delivered' => 'تم التسليم',
            'cancelled' => 'ملغي',
        ];
    }

    // حالات الدفع
    public static function getPaymentStatuses(): array
    {
        return [
            'pending' => 'في الانتظار',
            'paid' => 'مدفوع',
            'failed' => 'فشل الدفع',
            'refunded' => 'مسترد',
        ];
    }

    // تحويل حالة الطلب للعربية
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    // تحويل حالة الدفع للعربية
    public function getPaymentStatusLabelAttribute(): string
    {
        return self::getPaymentStatuses()[$this->payment_status] ?? $this->payment_status;
    }
}
