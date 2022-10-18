<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeedBackModel extends Model
{
    use HasFactory;

    public $table = 'feedback';

    public $fillable = [
        'user_id',
        'app_users_id',
        'content',
        'qq',
        'ip',
        'parent_id'
    ];

    public function app_users()
    {
        return $this->hasOne(AppUsersModel::class,'id','app_users_id');
    }

    public function reply()
    {
        return $this->hasOne(FeedBackModel::class,'parent_id','id');
    }
}
