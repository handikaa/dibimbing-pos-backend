<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Infrastructure\Persistence\Eloquent\Models\CashierSession;
use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItem;
use App\Infrastructure\Persistence\Eloquent\Models\Payment;

class Sale extends Model
{
    use SoftDeletes;

    protected $table = 'sales';

    protected $fillable = [
        'sale_number',
        'cashier_session_id',
        'cashier_id',
        'customer_name',
        'customer_phone',
        'table_code',
        'order_code',
        'daily_sequence',
        'subtotal',
        'discount_total',
        'tax_total',
        'grand_total',
        'paid_amount',
        'change_amount',
        'payment_method',
        'status',
        'notes',
        'paid_at',
        'cancelled_at',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'discount_total' => 'float',
        'tax_total' => 'float',
        'grand_total' => 'float',
        'paid_amount' => 'float',
        'change_amount' => 'float',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'customer_phone' => 'string',
        'table_code' => 'string',
        'order_code' => 'string',
        'daily_sequence' => 'integer',
    ];

    // =========================
    // RELATIONS
    // =========================

    public function cashierSession()
    {
        return $this->belongsTo(CashierSession::class, 'cashier_session_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'sale_id');
    }
}
