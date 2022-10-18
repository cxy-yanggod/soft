<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppStatisticsModel extends Model
{
    use HasFactory;

    public $table = 'app_statistics';

    public $fillable = [
        'user_id',
        'app_users_id',
        'type',
        'ip',
    ];
}
