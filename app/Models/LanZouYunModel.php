<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class LanZouYunModel extends Model
{
    use HasFactory;

    public $table = 'lanzouyun';

    public $fillable = [
        'user_id',
        'username',
        'password',
        'cookie',
        'is_vip'
    ];
}
