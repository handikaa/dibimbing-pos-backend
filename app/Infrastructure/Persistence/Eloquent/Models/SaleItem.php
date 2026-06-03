<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $table = 'sale_items';

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'product_sku',
        'quantity',
        'cost_price',
        'selling_price',
        'discount_amount',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'cost_price' => 'float',
        'selling_price' => 'float',
        'discount_amount' => 'float',
        'line_total' => 'float',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}