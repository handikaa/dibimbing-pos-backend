<?php
namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\Models\Sale;

class CashierSession extends Model
{
    use SoftDeletes;

    protected $table = 'cashier_sessions';

    protected $fillable = [
        'user_id',
        'session_code',
        'status',
        'opening_cash',
        'cash_sales_total',
        'midtrans_sales_total',
        'refund_total',
        'transaction_count',
        'expected_cash',
        'actual_cash',
        'cash_difference',
        'opening_note',
        'closing_note',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'opening_cash' => 'float',
        'cash_sales_total' => 'float',
        'midtrans_sales_total' => 'float',
        'refund_total' => 'float',
        'expected_cash' => 'float',
        'actual_cash' => 'float',
        'cash_difference' => 'float',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // =========================
    // RELATIONS
    // =========================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'cashier_session_id');
    }
}