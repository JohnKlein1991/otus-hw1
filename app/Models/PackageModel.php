<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PackageModel extends Model
{
    protected $table = 'packages';
    public $timestamps = false;
    protected $fillable = [
            'order_id',
            'strbarcode',
            'mass',
            'message',
            'length',
            'width',
            'height'
        ];
}