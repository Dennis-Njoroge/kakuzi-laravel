<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id','name', 'sku', 'price', 'quantity', 'description', 'units', 'image', 'discount',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
