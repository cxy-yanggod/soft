<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SoftModel extends Model
{
    use HasFactory;

    public $table = 'soft';

    public $fillable = [
        'user_id',
        'menu_id',
        'link',
        'name',
        'icon',
        'desc',
        'size',
        'down',
        'nums',
        'soft_screenshot'
    ];

    public function menu()
    {
        return $this->hasOne(SoftMenuModel::class,'id','menu_id');
    }
}
