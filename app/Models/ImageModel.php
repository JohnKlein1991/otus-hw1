<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ImageModel extends Model
{
    protected $table = 'images';
    public $timestamps = false;
    protected $fillable = [
        'filename',
        'address_code'
    ];
}