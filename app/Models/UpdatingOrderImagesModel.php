<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UpdatingOrderImagesModel extends Model
{
    protected $table = 'updating_order_images';
    public $timestamps = false;
    protected $fillable = [
            'updating_order_id',
            'filename',
            'link',
        ];
}