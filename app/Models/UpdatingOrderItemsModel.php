<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UpdatingOrderItemsModel extends Model
{
    protected $table = 'updating_order_items';
    public $timestamps = false;
    protected $fillable = [
            'updating_order_id',
            'code',
            'quantity',
            'reason'
        ];
}