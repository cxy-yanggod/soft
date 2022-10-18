<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ToolsPlateModel extends Model
{
    use HasFactory;

    public $table = 'tools_plate';

    public $fillable = [
        'user_id',
        'title',
        'desc',
        'link',
        'type',
    ];
}
