<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SoftMenuModel extends Model
{
    use HasFactory;

    public $table = 'soft_menu';

    public $fillable = [
        'user_id',
        'name',
        'notice',
        'password',
        'link',
        'l_password',
        'index',
        'sort',
        'is_vip'
    ];

    public function soft()
    {
        return $this->hasMany(SoftModel::class,'menu_id','id');
    }
}
