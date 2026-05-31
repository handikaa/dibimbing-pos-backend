<?php
namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $table = 'store_settings';

    protected $fillable = [
        'store_name',
        'store_address',
        'store_phone',
        'receipt_footer',
        'tax_percentage',
        'currency',
        'logo_url',
    ];

    protected $casts = [
        'tax_percentage' => 'float',
    ];
}