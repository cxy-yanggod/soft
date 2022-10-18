<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppCarMyModel extends Model
{
    use HasFactory;

    public $table = 'app_carmy';

    public $fillable = [
        'user_id',
        'carmy',
        'type',
        'day',
        'use_app_users_id',
        'use_time',
        'status',
    ];

    public function app_users_info()
    {
        return $this->hasOne(AppUsersModel::class,'id','use_app_users_id');
    }
}
