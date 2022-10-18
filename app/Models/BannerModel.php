<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class BannerModel extends Model
{
    use HasFactory;
    public $table = 'banner';

    public $fillable = [
        'user_id',
        'path',
        'link',
        'type'
    ];
}
