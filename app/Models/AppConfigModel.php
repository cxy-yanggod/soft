<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppConfigModel extends Model
{
    use HasFactory;

    public $table = 'app_config';

    public $fillable = [
        'user_id',
        'notice',
        'logo',
        'alert_notice',
        'one_alert_button',
        'one_alert_type',
        'tow_alert_button',
        'tow_alert_type',
        'one_alert_content',
        'tow_alert_content',
        'three_alert_button',
        'three_alert_type',
        'three_alert_content',
        'share_contents',
        'qq_qun',
        'app_name',
        'app_desc',
        'three_link',
        'password_text',
        'alert_open',
        'password_button',
        'password_content',
        'password_type',
        'version',
        'agreement',
        'share_link',
        'app_download',
        'share_soft',
        'share_article',
        'is_one_day_carmy',
        'qq',
        'wechat',
        'lanzouyun_detail',
        'lanzouyun_download',
        'lanzouyun_search',
        'lanzouyun_static',
        'lanzouyun_ajax',
        'update_link'
    ];
}
