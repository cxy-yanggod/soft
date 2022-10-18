<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArticleModel extends Model
{
    use HasFactory;

    public $table = 'article';

    public $fillable = [
        'user_id',
        'menu_id',
        'title',
        'author',
        'content',
        'html',
        'cover',
        'link',
        'type',
        'open_type',
        'zhiding',
        'nums',
        'is_vip'
    ];

    public function article_menu()
    {
        return $this->hasOne(ArticleMenuModel::class,'id','menu_id');
    }
}
