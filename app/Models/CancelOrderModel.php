<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CancelOrderModel extends Model
{
    protected $table = 'cancel_order';
    public $timestamps = false;
    protected $fillable = [
            'ordercode',
            'orderno',
        ];
}