<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommentFabulousModel extends Model
{
    use HasFactory;

    public $table = 'comment_fabulous';

    public $fillable = [
        'user_id',
        'app_users_id',
        'comment_id',
        'content_id',
        'type'
    ];
}
