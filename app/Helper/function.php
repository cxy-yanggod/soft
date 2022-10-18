<?php

use GuzzleHttp\Client;



function lanzouyun_file_download($file_id)
{
    $client = new Client();
    $html = $client->request('get','https://lanzoul.com/'.$file_id,[
        'headers'=>[
            'User-Agent'=>'Mozilla/5.0 (Linux; Android 11; Pixel 5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.91 Mobile Safari/537.36',
            'referer'=>'https://lanzoul.com/',
            'X-FORWARDED-FOR'=>getIp(),
            'CLIENT-IP'=>getIp(),
        ]
    ])->getBody()->getContents();
    $download_pattern = '#var link = \'(.*?)\'#';
    preg_match_all($download_pattern,$html,$download);
    $download_url = 'https://developer.lanzoug.com/file/';
    if(!strstr($download[1][0],'tp')){
        $download = $download_url.$download[1][0];
        return $download;
    }else{
        $html = $client->request('get','https://lanzoul.com/tp/'.$file_id,[
            'headers'=>[
                'User-Agent'=>'Mozilla/5.0 (Linux; Android 11; Pixel 5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.91 Mobile Safari/537.36',
                'referer'=>'https://lanzoul.com/',
                'X-FORWARDED-FOR'=>getIp(),
                'CLIENT-IP'=>getIp(),
            ]
        ])->getBody()->getContents();
        $download_pattern = '#var oreferr = \'(.*?)\';#';
        preg_match_all($download_pattern,$html,$download);
        $download = $download_url.$download[1][0];
        return $download;
    }

}

function redirect_lanzouyun_download($url)
{
    $client = new Client();
    $html = $client->request('get',$url,[
        'headers'=>[
            'cookie'=>'down_ip=1; UM_distinctid=17e769a404f4ea-044d510650c8aa-1d326253-1aeaa0-17e769a405088e; CNZZDATA5289258=cnzz_eid%3D977043593-1642666902-null%26ntime%3D1642666902; CNZZDATA1253610887=490263014-1642666299-null%7C1642666299; CNZZDATA5288858=cnzz_eid%3D470380851-1642663723-%26ntime%3D1642663723',
            'User-Agent'=>'Mozilla/5.0 (Linux; Android 11; Pixel 5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.91 Mobile Safari/537.36',
            'referer'=>'https://lanzoul.com/',
            'X-FORWARDED-FOR'=>getIp(),
            'CLIENT-IP'=>getIp(),
        ]
    ])->getBody()->getContents();
    $download_pattern = '#<a href="(.*?)" id="ding" class="ding">立即下载</a>#';
    preg_match_all($download_pattern,$html,$download);
    return $download[1][0];
}

function app_version()
{
    return \App\Models\SystemConfigModel::query()->value('app_version');
}


function getIp(){

    $ip_long = [
        ['607649792', '608174079'], //36.56.0.0-36.63.255.255
        ['1038614528', '1039007743'], //61.232.0.0-61.237.255.255
        ['1783627776', '1784676351'], //106.80.0.0-106.95.255.255
        ['2035023872', '2035154943'], //121.76.0.0-121.77.255.255
        ['2078801920', '2079064063'], //123.232.0.0-123.235.255.255
        ['-1950089216', '-1948778497'], //139.196.0.0-139.215.255.255
        ['-1425539072', '-1425014785'], //171.8.0.0-171.15.255.255
        ['-1236271104', '-1235419137'], //182.80.0.0-182.92.255.255
        ['-770113536', '-768606209'], //210.25.0.0-210.47.255.255
        ['-569376768', '-564133889'] //222.16.0.0-222.95.255.255
    ];

    $rand_key = mt_rand(0, 9);
    $ip = long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));

    return $ip;
}

function mdate($time = NULL) {
    $text = '';
    $time = $time === NULL || $time > time() ? time() : intval($time);
    $t = time() - $time; //时间差 （秒）
    $y = date('Y', $time)-date('Y', time());//是否跨年
    switch($t){
        case $t == 0:
            $text = '刚刚';
            break;
        case $t < 60:
            $text = $t . '秒前'; // 一分钟内
            break;
        case $t < 60 * 60:
            $text = floor($t / 60) . '分钟前'; //一小时内
            break;
        case $t < 60 * 60 * 24:
            $text = floor($t / (60 * 60)) . '小时前'; // 一天内
            break;
        case $t < 60 * 60 * 24 * 3:
            $text = floor($time/(60*60*24)) ==1 ?'昨天 ' . date('H:i', $time) : '前天 ' . date('H:i', $time) ; //昨天和前天
            break;
        case $t < 60 * 60 * 24 * 30:
            $text = date('m月d日 H:i', $time); //一个月内
            break;
        case $t < 60 * 60 * 24 * 365&&$y==0:
            $text = date('m月d日', $time); //一年内
            break;
        default:
            $text = date('Y年m月d日', $time); //一年以前
            break;
    }
    return $text;
}

