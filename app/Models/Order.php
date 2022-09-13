<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_number', 'user_id', 'order_date', 'transaction_code', 'shipment_fee', 'total_amount', 'paid_amount',
        'status', 'driver_id', 'delivery_address',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function orderItems (): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItems::class);
    }

    public function getStatus (): array
    {
        return ['Pending','Approved','Dispatched','Delivered','Completed'];
    }

    public function getOrderNumber () {
        $latestOrder = Order::orderBy('created_at','DESC')->first();
        if ($latestOrder){
            $orderNumber = '#'.str_pad($latestOrder->id + 1, 8, "0", STR_PAD_LEFT);
        }
        else{
            $orderNumber = '#'.str_pad( 1, 8, "0", STR_PAD_LEFT);
        }
        return $orderNumber;
    }
}
