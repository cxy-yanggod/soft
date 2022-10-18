<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ToolsModel extends Model
{
    use HasFactory;

    public $table = 'tools';

    public $fillable = [
        'user_id',
        'name',
        'link',
        'type',
    ];
}
