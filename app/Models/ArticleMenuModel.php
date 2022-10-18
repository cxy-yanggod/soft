<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArticleMenuModel extends Model
{
    use HasFactory;

    public $table = 'article_menu';

    public $fillable = [
        'user_id',
        'name',
        'soft',
        'index',
        'password',
        'is_vip'
    ];

    public function article()
    {
        return $this->hasMany(ArticleModel::class,'menu_id','id');
    }
}
