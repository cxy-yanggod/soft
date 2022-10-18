<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommentModel extends Model
{
    use HasFactory;

    public $table = 'comment';

    public $fillable = [
        'user_id',
        'app_users_id',
        'type',
        'content_id',
        'parent_id',
        'fabulous',
        'content',
    ];

    public function app_users()
    {
        return $this->hasOne(AppUsersModel::class,'id','app_users_id');
    }

    public function reply()
    {
        return $this->hasMany(CommentModel::class,'parent_id','id');
    }
}
