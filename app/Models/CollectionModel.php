<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CollectionModel extends Model
{
    use HasFactory;

    public $table = 'collection';

    public $fillable = [
        'user_id',
        'app_users_id',
        'content_id',
        'type',
    ];
}
