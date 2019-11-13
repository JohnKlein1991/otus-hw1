<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UpdatingOrdersModel extends Model
{
    protected $table = 'updating_orders';
    public $timestamps = false;
    protected $fillable = [
            'ordercode',
            'message',
            'status',
            'eventtime',
            'paytype',
        ];
}