<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\UsersController;
use App\Models\AppCarMyModel;
use App\Models\AppStatisticsModel;
use App\Models\AppUsersModel;
use App\Models\ArticleModel;
use App\Models\CommentModel;
use App\Models\SoftModel;
use App\Models\SystemConfigModel;
use App\Models\SystemNoticeModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SystemController extends UsersController
{

    /**
     * 工作台数据
     * @return \Illuminate\Http\JsonResponse
     */
    public function workplace()
    {
        $user_info = User::query()->where(['id'=>User::user_id()])->select('nickname')->first();
        $user_count = AppUsersModel::query()->where(['user_id'=>User::user_id()])->count();
        $vip_user_count = AppUsersModel::query()->where(['user_id'=>User::user_id()])->where('vip_start_time','!=','')->count();
        $cay_my_count = AppCarMyModel::query()->where(['user_id'=>User::user_id()])->count();
        $use_cay_my_count = AppCarMyModel::query()->where(['user_id'=>User::user_id(),'status'=>-1])->count();
        $start_week_time =  Carbon::now()->startOfWeek();
        $end_week_time =  Carbon::now()->endOfWeek();
        $start_week_day = $start_week_time->day;
        $end_week_day = $end_week_time->day;
        $user_register_data = [];
        $week = [];
        for ($start_week_day;$start_week_day<=$end_week_day;$start_week_day++){
            $year = Carbon::now()->year;
            $month = Carbon::now()->month;
            $day = $start_week_day + 1;
            $week[] = $month.'-'.$start_week_day;
            $start = Carbon::create($year,$month,$start_week_day);
            $end = Carbon::create($year,$month,$day);
            $user_register_data[] = AppUsersModel::query()
                ->where(['user_id'=>User::user_id()])
                ->whereBetween('created_at',[$start,$end])
                ->groupBy('date')
                ->selectRaw('DATE_FORMAT(created_at,"%d") as date,count(id) as count')
                ->first();
        }
        $week_data = [];
        foreach($user_register_data as $key=>$value){
            $week_data[$key] = $value['count'] ?? 0;
        }
        $user_info['vip'] = false;
        if(strtotime($user_info['vip_end_time']) > time()){
            $user_info['vip'] = true;
            $vip_count_day = Carbon::parse($user_info['vip_start_time'])->diffInDays($user_info['vip_end_time']);
            $vip_end_day = Carbon::parse($user_info['vip_end_time'])->diffInDays(Carbon::now());
            $user_info['jindu'] = round( $vip_end_day/$vip_count_day * 100 , 2);
            $user_info['vip_end_day'] = $vip_end_day;
        }
        $param = [
            'user_info'=>$user_info,
            'data'=>[
                'user_count'=>$user_count,
                'vip_user_count'=>$vip_user_count,
                'cay_my_count'=>$cay_my_count,
                'use_cay_my_count'=>$use_cay_my_count,
            ],
            'week'=>[
                'week'=>$week,
                'week_data'=>$week_data
            ],
        ];
        return $this->success('',$param);
    }

    /**
     * 工作台数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function workplaceData(Request $request)
    {
        $type = $request->get('type');
        if($type == 1){
            $data = SoftModel::query()->where(['user_id'=>User::user_id()])->orderBy('nums','desc')->take(5)->get();
        }else{
            $data = ArticleModel::query()->where(['user_id'=>User::user_id()])->orderBy('nums','desc')->take(5)->get();
        }
        $soft_count = SoftModel::query()->where(['user_id'=>User::user_id()])->count();
        $article_count = ArticleModel::query()->where(['user_id'=>User::user_id()])->count();
        $share_count = AppStatisticsModel::query()->where(['user_id'=>User::user_id(),'type'=>1])->count();
        $download_count = AppStatisticsModel::query()->where(['user_id'=>User::user_id(),'type'=>2])->count();
        $add_group_count = AppStatisticsModel::query()->where(['user_id'=>User::user_id(),'type'=>3])->count();
        $qidong_count = AppStatisticsModel::query()->where(['user_id'=>User::user_id(),'type'=>4])->count();
        $count = [$soft_count,$article_count,$share_count,$download_count,$add_group_count,$qidong_count];
        $zhanbi = [];
        $zhanbi_name = ['软件','文章','评论','分享','下载','加群','启动'];
        foreach($count as $key=>$value){
            $zhanbi[] = [
                'value'=>$value,
                'name'=>$zhanbi_name[$key],
            ];
        }
        $data->each(function($item,$key){
           $item['paiming'] = $key+1;
        });
        $param = [
            'data'=>$data,
            'zhanbi'=>$zhanbi
        ];
        return $this->success('',$param);
    }

    /**
     * 多维数据
     * @return \Illuminate\Http\JsonResponse
     */
    public function analysis()
    {
        $fangwen_count = AppStatisticsModel::query()->where(['user_id'=>User::user_id(),'type'=>3])->count();
        $soft_count = SoftModel::query()->where(['user_id'=>User::user_id()])->count();
        $article_count = ArticleModel::query()->where(['user_id'=>User::user_id()])->count();
        $comment_count = CommentModel::query()->where(['user_id'=>User::user_id()])->count();
        $share_count = AppStatisticsModel::query()->where(['user_id'=>User::user_id(),'type'=>1])->count();
        $data_count = $soft_count + $article_count;

        $start_week_time =  Carbon::now()->startOfWeek();
        $end_week_time =  Carbon::now()->endOfWeek();
        $start_week_day = $start_week_time->day;
        $end_week_day = $end_week_time->day;
        $soft = [];
        $article = [];
        $week = [];
        for ($start_week_day;$start_week_day<=$end_week_day;$start_week_day++){
            $year = Carbon::now()->year;
            $month = Carbon::now()->month;
            $day = $start_week_day + 1;
            $week[] = $month.'-'.$start_week_day;
            $start = Carbon::create($year,$month,$start_week_day);
            $end = Carbon::create($year,$month,$day);
            $soft[] = SoftModel::query()
                ->where(['user_id'=>User::user_id()])
                ->whereBetween('created_at',[$start,$end])
                ->groupBy('date')
                ->selectRaw('DATE_FORMAT(created_at,"%d") as date,count(id) as count')
                ->first();
            $article[] = ArticleModel::query()
                ->where(['user_id'=>User::user_id()])
                ->whereBetween('created_at',[$start,$end])
                ->groupBy('date')
                ->selectRaw('DATE_FORMAT(created_at,"%d") as date,count(id) as count')
                ->first();
        }
        $week_data = [];
        foreach($soft as $key=>$value){
            $week_data['soft'][$key] = $value['count'] ?? 0;
        }
        foreach($article as $key=>$value){
            $week_data['article'][$key] = $value['count'] ?? 0;
        }
        $user = [];
        $huoyue_user = AppStatisticsModel::query()->where(['user_id'=>User::user_id(),'type'=>3])->where('app_users_id','!=','')->distinct('user_id')->orderBy('created_at','desc')->take(10)->get();
        if($huoyue_user->isNotEmpty()){
            $app_users = [];
            foreach($huoyue_user as $key=>$value){
                $app_users['id'][] = $value['app_users_id'];
                $app_users['created_at'][] = $value['created_at'];
            }
            $user = AppUsersModel::query()->whereIn('id',$app_users['id'])->get();
            $user->each(function($item,$index)use($app_users){
                $item['huoyue_time'] = $app_users['created_at'][$index]->toDateTimeString();
            });
        }
        $htime = [];
        for($h = 0;$h<=23;$h++){
            $year = Carbon::now()->year;
            $month = Carbon::now()->month;
            $day = Carbon::now()->day;
            $htime['start'][] = Carbon::create($year,$month,$day,$h,'0','0');
            $htime['end'][] = Carbon::create($year,$month,$day,$h,'59','59');
        }
        $qidong = [];
        foreach($htime['start'] as $key=>$value){
             $data = AppStatisticsModel::query()
                ->where(['user_id'=>User::user_id()])
                ->whereBetween('created_at',[$value,$htime['end'][$key]])
                ->groupBy('date')
                ->selectRaw('DATE_FORMAT(created_at,"%H") as date,count(id) as count')
                ->first();
            $qidong[] = [
                'date'=>$value->hour.':00',
                'value'=>$data['count'] ?? 0,
            ];
        }
        $qidong_date = [];
        $qidong_count = [];
        foreach($qidong as $key=>$value){
            $qidong_date[] = $value['date'];
            $qidong_count[] = $value['value'];
        }
        $param = [
            'data'=>[
                'fangwen_count'=>$fangwen_count,
                'comment_count'=>$comment_count,
                'share_count'=>$share_count,
                'data_count'=>$data_count,
            ],
            'huoyue_user'=>$user,
            'week'=>[
                'week'=>$week,
                'week_data'=>$week_data
            ],
            'qidong'=>[
                'date'=>$qidong_date,
                'count'=>$qidong_count
            ]
        ];
        return $this->success('',$param);
    }

    /**
     * 系统公告
     * @param Request $request
     * @return void
     */
    public function notice(Request $request)
    {
        $notice = SystemNoticeModel::query()->where(['id'=>$request->get('id')])->first();
        return $this->success('',$notice);
    }
}
