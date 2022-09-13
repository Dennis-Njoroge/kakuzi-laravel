<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id', 'product_id', 'quantity', 'price', 'discount'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
