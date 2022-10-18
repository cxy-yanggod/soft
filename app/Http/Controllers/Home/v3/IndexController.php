<?php

namespace App\Http\Controllers\Home\v3;

use App\Http\Controllers\HomeController;
use App\Models\AppConfigModel;
use App\Models\AppStatisticsModel;
use App\Models\BannerModel;
use App\Models\SoftMenuModel;
use App\Models\SoftModel;
use Illuminate\Http\Request;

class IndexController extends HomeController
{
    /**
     * 首页横幅
     * @return \Illuminate\Http\JsonResponse
     */
    public function banner()
    {
        $banner = BannerModel::query()->where(['user_id'=>$this->home_user_id])
            ->select('id','type','path','link')
            ->get();
        $banner->each(function($item){
            $item['img'] = $item['path'];
            $item['opentype'] = 'click';

            return $item;
        });
        return $this->success('',$banner);
    }

    /**
     * 软件配置
     * @return \Illuminate\Http\JsonResponse
     */
    public function appConfig()
    {
        $app_config = AppConfigModel::query()->where(['user_id'=>$this->home_user_id])
            ->select('notice','notice', 'logo', 'alert_notice', 'one_alert_button', 'one_alert_type', 'tow_alert_button', 'tow_alert_type', 'one_alert_content', 'tow_alert_content', 'app_name', 'password_text', 'alert_open', 'password_button', 'password_content', 'password_type', 'three_alert_button', 'three_alert_type', 'three_alert_content','qq_qun','buy_carmy_link','share_soft','share_article','share_link','is_comment','qq','wechat','lanzouyun_detail','lanzouyun_download','lanzouyun_search','lanzouyun_static','lanzouyun_ajax')
            ->first();
        return $this->success('',$app_config);
    }

    /**
     * 热门软件
     * @return \Illuminate\Http\JsonResponse
     */
    public function hotsSoft()
    {
        $menu_id = SoftMenuModel::query()->inRandomOrder()->value('id');
        $soft = SoftModel::query()
            ->where(['user_id'=>$this->home_user_id,'menu_id'=>$menu_id])
            ->select('id','name','menu_id','icon','size')
            ->orderBy('nums','desc')
            ->take(5)
            ->get();
        return $this->success('',$soft);
    }

    /**
     * 软件更新
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update(Request $request)
    {
        try {
            $data = $request->all();
            $app_update = AppConfigModel::query()->where(['user_id'=>$this->home_user_id])->first();
            $versionName = str_replace('.','',$app_update['version']);
            if(!$app_update){
                return $this->success('暂无更新');
            }
            if($versionName <= $data['versionName']){
                return $this->success('暂无更新');
            }
            $download = $app_update['update_link'];
            if(strstr($app_update['update_link'],'lanzou')){
                $link_pattern = '#.com/(.*)#';
                preg_match_all($link_pattern,$app_update['update_link'],$link);
                $file_id = $link[1][0];
                $download = lanzouyun_file_download($file_id);
                $download = redirect_lanzouyun_download($download);
            }
            $updateType = 'solicit';
            if(!$data['setupPage']){
                if($app_update['update_type'] == 1){
                    $updateType = 'solicit';
                }else if($app_update['update_type'] == 2){
                    $updateType = 'silent';
                }else if($app_update['update_type'] == 3){
                    $updateType = 'forcibly';
                }
            }
            $update = [
                'versionCode'=>$versionName,
                'versionName'=>$app_update['version'],
                'versionInfo'=>$app_update['desc'] ?? '',
                'updateType'=>$updateType,
                'downloadUrl'=>$download,
            ];
            return $this->success('',$update);
        }catch(\Exception $e){
        }
    }

    /**
     * App统计
     * @param Request $request
     * @return void
     */
    public function statistics(Request $request)
    {
        $data = $request->all();
        $data['user_id'] = $this->home_user_id;
        $data['ip'] = $request->ip();
        AppStatisticsModel::query()->create($data);
    }
}
