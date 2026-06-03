<?php
namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use App\Infrastructure\Persistence\Eloquent\Models\Sale;

class Payment extends Model
{

    protected $table = 'payments';

    protected $fillable = [
        'sale_id',
        'payment_method',
        'provider',
        'provider_reference',
        'provider_transaction_id',
        'amount',
        'paid_amount',
        'change_amount',
        'status',
        'payment_url',
        'snap_token',
        'raw_response',
        'paid_at',
        'expired_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'paid_amount' => 'float',
        'change_amount' => 'float',
        'raw_response' => 'array',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    // =========================
    // RELATIONS
    // =========================

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }
}