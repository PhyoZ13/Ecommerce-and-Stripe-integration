<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    protected $fillable = ['user_id', 'status', 'total_price'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
