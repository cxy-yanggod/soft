<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\UsersController;
use App\Jobs\BuildAppJobs;
use App\Models\AppBuildModel;
use App\Models\AppConfigModel;
use App\Models\AppDownloadModel;
use App\Models\AppUpdateModel;
use App\Models\LanZouYunModel;
use App\Models\SystemConfigModel;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Spatie\Activitylog\Models\Activity;

class AppConfigController extends UsersController
{
    /**
     * 获取配置
     * @return \Illuminate\Http\JsonResponse
     */
    public function appConfig()
    {
        $config = AppConfigModel::query()->where(['user_id'=>User::user_id()])->first();
        if(!isset($config['alert_open'])){
            $config['alert_open'] = false;
        }else{
            $config['alert_open'] = $config['alert_open'] == true ? true : false;
        }
        if(!isset($config['share_soft'])){
            $config['share_soft'] = false;
        }else{
            $config['share_soft'] = $config['share_soft'] == true ? true : false;
        }
        if(!isset($config['share_article'])){
            $config['share_article'] = false;
        }else{
            $config['share_article'] = $config['share_article'] == true ? true : false;
        }
        if(!isset($config['share_soft'])){
            $config['share_soft'] = false;
        }else{
            $config['share_soft'] = $config['share_soft'] == true ? true : false;
        }
        if(!isset($config['share_article'])){
            $config['share_article'] = false;
        }else{
            $config['share_article'] = $config['share_article'] == true ? true : false;
        }
        if(!isset($config['is_one_day_carmy'])){
            $config['is_one_day_carmy'] = false;
        }else{
            $config['is_one_day_carmy'] = $config['is_one_day_carmy'] == true ? true : false;
        }
        if(!isset($config['is_comment'])){
            $config['is_comment'] = false;
        }else{
            $config['is_comment'] = $config['is_comment'] == true ? true : false;
        }
        if(!isset($config['is_weiguizi'])){
            $config['is_weiguizi'] = false;
        }else{
            $config['is_weiguizi'] = $config['is_weiguizi'] == true ? true : false;
        }
        if(!$config['lanzouyun_detail']){
            $config['lanzouyun_detail'] = SystemConfigModel::query()->value('lanzouyun_detail');
        }
        if(!$config['lanzouyun_download']){
            $config['lanzouyun_download'] = SystemConfigModel::query()->value('lanzouyun_download');
        }
        if(!$config['lanzouyun_search']){
            $config['lanzouyun_search'] = SystemConfigModel::query()->value('lanzouyun_search');
        }
        if(!$config['lanzouyun_static']){
            $config['lanzouyun_static'] = SystemConfigModel::query()->value('lanzouyun_static');
        }
        if(!$config['lanzouyun_ajax']){
            $config['lanzouyun_ajax'] = SystemConfigModel::query()->value('lanzouyun_ajax');
        }
        return $this->success('',$config);
    }

    /**
     * 保存配置
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function appConfigSave(Request $request)
    {
        $data = $request->all();
        $param = [
            'agreement'=>$data['agreement'] ?? '',
            'alert_notice'=>$data['alert_notice'] ?? '',
            'alert_open'=>$data['alert_open'] ?? '',
            'app_name'=>$data['app_name'] ?? '',
            'notice'=>$data['notice'] ?? '',
            'one_alert_button'=>$data['one_alert_button'] ?? '',
            'one_alert_content'=>$data['one_alert_content'] ?? '',
            'one_alert_type'=>$data['one_alert_type'] ?? 1,
            'password_button'=>$data['password_button'] ?? '',
            'password_content'=>$data['password_content'] ?? '',
            'password_text'=>$data['password_text'] ?? '',
            'password_type'=>$data['password_type'] ?? 1,
            'qq_qun'=>$data['qq_qun'] ?? '',
            'tow_alert_button'=>$data['tow_alert_button'] ?? '',
            'tow_alert_content'=>$data['tow_alert_content'] ?? '',
            'tow_alert_type'=>$data['tow_alert_type'] ?? 1,
            'share_link'=>$data['share_link'] ?? '',
            'app_download'=>$data['app_download'] ?? '',
            'share_soft'=>$data['share_soft'] ?? 1,
            'share_article'=>$data['share_article'] ?? 1,
            'is_one_day_carmy'=>$data['is_one_day_carmy'] ?? 1,
            'buy_carmy_link'=>$data['buy_carmy_link'] ?? '',
            'is_comment'=>$data['is_comment'] ?? 1,
            'is_weiguizi'=>$data['is_weiguizi'] ?? 1,
            'weiguizi'=>$data['weiguizi'] ?? '',
            'qq'=>$data['qq'] ?? '',
            'wechat'=>$data['wechat'] ?? '',
            'lanzouyun_detail'=>$data['lanzouyun_detail'] ?? '',
            'lanzouyun_download'=>$data['lanzouyun_download'] ?? '',
            'lanzouyun_search'=>$data['lanzouyun_search'] ?? '',
            'lanzouyun_static'=>$data['lanzouyun_static'] ?? '',
            'lanzouyun_ajax'=>$data['lanzouyun_ajax'] ?? '',
        ];
        $config = AppConfigModel::query()->where(['user_id'=>User::user_id()])->first();
        if(!$config){
            $system_app_version = SystemConfigModel::query()->value('app_version');
            $param['version'] = $system_app_version;
            $param['user_id'] = User::user_id();
            AppConfigModel::query()->create($param);
        }else{
            AppConfigModel::query()->where(['user_id'=>User::user_id()])->update($param);
        }
        activity()
            ->withProperties($param)
            ->tap(function(Activity $activity){
                $activity->ip = \request()->ip();;
                $activity->user_id = User::user_id();
            })
            ->log('保存APP配置');
        return $this->success('保存成功');
    }

    /**
     * 打包app
     * @param Request $request
     */
    public function buildApp(Request $request)
    {
        $name = $request->get('name');
        $icon = $request->file('icon');
        $get_icon = $request->get('icon');
        $build_key = $request->get('build_key');
        if(!$name){
            return $this->error('请输入名字');
        }
        if(!$get_icon){
            if(!$request->hasFile('icon')){
                return $this->error('请上传图标');
            }
        }
        $storage = Storage::disk('public');
        $icon_path = 'app_build/user/'.User::user_id().'/asset/icon.png';
        $app_build = AppBuildModel::query()->where(['user_id'=>User::user_id(),'update'=>0])->orderBy('id','desc')->first();
        //首先判断有没有打包过
        if(!$app_build){
            if($request->hasFile('icon')){
                //防止打包失败 转格式 jpg转png 格式 写入文件 读取文件 转换格式存入png格式
                $storage->put($icon_path,file_get_contents($icon));//写入文件
                $type = file_type_detect($storage->path($icon_path));
                if($type == 'jpg'){
                    $img = imagecreatefromjpeg($storage->path($icon_path));
                    imagepng($img, $storage->path($icon_path));
                }
            }else {
                //防止打包失败 转格式 jpg转png 格式 写入文件 读取文件 转换格式存入png格式
                $storage->put($icon_path,file_get_contents($get_icon));//写入文件
                $manager = new ImageManager('imagick');
                $image = $manager->make($storage->path($icon_path));//读取文件
                $image->toPng()->save($storage->path($icon_path));//存入png格式
            }
            $app_build_param = [
                'user_id'=>User::user_id(),
                'name'=>$name,
                'icon'=>$icon_path,
                'type'=>-1,
                'build_key'=>$build_key
            ];
            if($app_build){
                $app_build->update($app_build_param);
            }else{
                AppBuildModel::query()->create($app_build_param);
            }
            $param = [
                'icon'=>$storage->path($icon_path),
                'name'=>$name,
                'user_id'=>User::user_id()
            ];
            activity()
                ->withProperties($app_build_param)
                ->tap(function(Activity $activity) {
                    $activity->ip = \request()->ip();
                    $activity->user_id = User::user_id();
                })
                ->log('打包app');
            BuildAppJobs::dispatch($param);
            return $this->success('开始打包');
        }else{
            //判断是否 版本一致 更换名字或者更换图标重新打包否则是返回上次打包的下载链接
            if($app_build['version'] < app_version() || $name != $app_build['name'] || $icon == true){
                if($request->hasFile('icon')){
                    //防止打包失败 转格式 jpg转png 格式 写入文件 读取文件 转换格式存入png格式
                    $storage->put($icon_path,file_get_contents($icon));//写入文件
                    $manager = new ImageManager('imagick');
                    $image = $manager->make($storage->path($icon_path));//读取文件
                    $image->toPng()->save($storage->path($icon_path));//存入png格式
                }else {
                    //防止打包失败 转格式 jpg转png 格式 写入文件 读取文件 转换格式存入png格式
                    $storage->put($icon_path,file_get_contents($get_icon));//写入文件
                    $manager = new ImageManager('imagick');
                    $image = $manager->make($storage->path($icon_path));//读取文件
                    $image->toPng()->save($storage->path($icon_path));//存入png格式
                }
                $app_build_param = [
                    'user_id'=>User::user_id(),
                    'name'=>$name,
                    'icon'=>$icon_path,
                    'type'=>-1,
                    'build_key'=>$build_key
                ];
                if($app_build){
                    $app_build->update($app_build_param);
                }else{
                    AppBuildModel::query()->create($app_build_param);
                }
                $param = [
                    'icon'=>$storage->path($icon_path),
                    'name'=>$name,
                    'user_id'=>User::user_id()
                ];
                activity()
                    ->withProperties($app_build_param)
                    ->tap(function(Activity $activity) {
                        $activity->ip = \request()->ip();
                        $activity->user_id = User::user_id();
                    })
                    ->log('打包app');
                BuildAppJobs::dispatch($param);
                return $this->success('开始打包');
            }else{
                //没做任何更改 返回最后一次打包的链接
                $token = auth()->getToken();
                $sign = md5($app_build['apk_path']);
                activity()
                    ->withProperties(['sign'=>$sign])
                    ->tap(function(Activity $activity) {
                        $activity->ip = \request()->ip();
                        $activity->user_id = User::user_id();
                    })
                    ->log('打包完成下载软件');
                $host = env('APP_URL');
                $host = $host.'/api/user/app_download/'.$sign.'?token='.$token;
                $param = [
                    'text'=>'打包完成,<a href="'.$host.'" target="_blank">点我下载</a>'
                ];
                return $this->success('打包成功',$param);
            }
        }
    }

    /**
     * 打包结果
     * @return \Illuminate\Http\JsonResponse
     */
    public function buildAppOutput(Request $request)
    {
        $build_key = $request->get('build_key');
        $user_id = User::user_id();
        $app_build = AppBuildModel::query()->where(['build_key'=>$build_key,'user_id'=>$user_id])->first();
        if($app_build['type'] != 1){
            return $this->success('正在打包',['type'=>$app_build['type']]);
        }
        $token = auth()->getToken();
        $sign = md5($app_build['apk_path']);
        activity()
            ->withProperties($app_build)
            ->tap(function(Activity $activity) {
                $activity->ip = \request()->ip();
                $activity->user_id = User::user_id();
            })
            ->log('打包完成');
        $host = env('APP_URL');
        $host = $host.'/api/user/app_download/'.$sign.'?token='.$token;
        return $this->success('打包完成,<a href="'.$host.'" target="_blank">点我下载</a>',['type'=>$app_build['type']]);
    }

    /**
     * 下载软件
     * @param $sign
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function appDownload($sign)
    {
        try {
            $download = AppDownloadModel::query()->where(['sign'=>$sign])->first();
            $storage = Storage::disk('public');
            $file = File::isFile($storage->path($download['download_link']));
            if(!$file){
                return $this->error('请重新打包');
            }
            if(!$download){
                abort(404);
            }
            activity()
                ->withProperties(['sign'=>$sign])
                ->tap(function(Activity $activity) {
                    $activity->ip = \request()->ip();
                    $activity->user_id = User::user_id();
                })
                ->log('打包完成下载软件');
            return Storage::disk('public')->download($download['download_link']);
        }catch (\Exception $e){
            return $this->error('请重新打包');
        }
    }


    /**
     * 一键配置
     * @return \Illuminate\Http\JsonResponse
     */
    public function easyConfig(Request $request)
    {
        $data = $request->all();
        $app_config = config('easy_config');
        $new_config = [];
        foreach($app_config as $key=>$value){
            if(strstr($value,'{$app_name}')){
                $value = str_replace('{$app_name}',$data['name'],$value);
            }
            if(strstr($value,'{$qq_group}')){
                $value = str_replace('{$qq_group}',$data['qq_group'],$value);
            }
            $new_config[$key] = $value;
        }
        $config = AppConfigModel::query()->where(['user_id'=>User::user_id()])->first()->toArray();
        if(!isset($config['alert_open'])){
            $config['alert_open'] = false;
        }else{
            $config['alert_open'] = $config['alert_open'] == true ? true : false;
        }
        if(!isset($config['share_soft'])){
            $config['share_soft'] = false;
        }else{
            $config['share_soft'] = $config['share_soft'] == true ? true : false;
        }
        if(!isset($config['share_article'])){
            $config['share_article'] = false;
        }else{
            $config['share_article'] = $config['share_article'] == true ? true : false;
        }
        if(!isset($config['share_soft'])){
            $config['share_soft'] = false;
        }else{
            $config['share_soft'] = $config['share_soft'] == true ? true : false;
        }
        if(!isset($config['share_article'])){
            $config['share_article'] = false;
        }else{
            $config['share_article'] = $config['share_article'] == true ? true : false;
        }
        if(!isset($config['is_one_day_carmy'])){
            $config['is_one_day_carmy'] = false;
        }else{
            $config['is_one_day_carmy'] = $config['is_one_day_carmy'] == true ? true : false;
        }
        if(!isset($config['is_comment'])){
            $config['is_comment'] = false;
        }else{
            $config['is_comment'] = $config['is_comment'] == true ? true : false;
        }
        if(!isset($config['is_weiguizi'])){
            $config['is_weiguizi'] = false;
        }else{
            $config['is_weiguizi'] = $config['is_weiguizi'] == true ? true : false;
        }
        if(!$config['lanzouyun_detail']){
            $config['lanzouyun_detail'] = SystemConfigModel::query()->value('lanzouyun_detail');
        }
        if(!$config['lanzouyun_download']){
            $config['lanzouyun_download'] = SystemConfigModel::query()->value('lanzouyun_download');
        }
        if(!$config['lanzouyun_search']){
            $config['lanzouyun_search'] = SystemConfigModel::query()->value('lanzouyun_search');
        }
        if(!$config['lanzouyun_static']){
            $config['lanzouyun_static'] = SystemConfigModel::query()->value('lanzouyun_static');
        }
        if(!$config['lanzouyun_ajax']){
            $config['lanzouyun_ajax'] = SystemConfigModel::query()->value('lanzouyun_ajax');
        }
        $new_config = array_merge($config,$new_config);
        activity()
            ->withProperties($new_config)
            ->tap(function(Activity $activity) {
                $activity->ip = \request()->ip();
                $activity->user_id = User::user_id();
            })
            ->log('一键配置');
        return $this->success('配置成功',$new_config);
    }

    /**
     * 打包资源
     * @return \Illuminate\Http\JsonResponse
     */
    public function appBuildResource()
    {
        $asset = AppBuildModel::query()->where(['user_id'=>User::user_id(),'update'=>0])->orderBy('id','desc')->select('id','icon','name')->first();
        if(isset($asset['icon'])){
            $asset['icon'] = Storage::disk('public')->url($asset['icon']);
        }
        return $this->success('',$asset);
    }

    /**
     * 软件更新
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkUpdate()
    {
        if(!is_lanzou()){
            return $this->error('请先绑定蓝奏云');
        }
        $app_build = AppBuildModel::query()->where(['user_id'=>User::user_id(),'update'=>0])->orderBy('id','desc')->select('icon','name')->first();
        if(!$app_build){
            return $this->error('您还未打包过APP');
        }
        $user_app_version = AppConfigModel::query()->where(['user_id'=>User::user_id()])->value('version');
        if($user_app_version >= app_version()){
            return $this->error('暂无更新');
        }
        $param = [
            'system_app_version'=>app_version(),
            'user_app_version'=>$user_app_version,
        ];
        return $this->success('发现更新',$param);
    }

    /**
     * app更新
     * @return \Illuminate\Http\JsonResponse
     */
    public function appUpdate(Request $request)
    {
        $app_build_id = $request->get('app_build_id');
        if(!$app_build_id){
            return $this->success('请先打包');
        }
        $app_build = AppBuildModel::query()->where(['id'=>$app_build_id])->first();
        $storage = Storage::disk('public');
        $build_key = $request->get('build_key');
        $param = [
            'icon'=>$storage->path($app_build['icon']),
            'name'=>$app_build['name'],
            'user_id'=>User::user_id(),
            'update'=>1,
            'build_key'=>$build_key
        ];
        BuildAppJobs::dispatch($param);
        return $this->success('开始更新');
    }

    /**
     * app更新结果
     * @return \Illuminate\Http\JsonResponse
     */
    public function appUpdateOutput(Request $request)
    {
        $build_key = $request->get('build_key');
        $user_id = User::user_id();
        $app_build_update = AppBuildModel::query()->where(['build_key'=>$build_key,'user_id'=>$user_id,'update'=>1])->first();
        if(!$app_build_update){
            return $this->success('正在更新',['type'=>-1]);
        }
        return $this->success('打包完成上传蓝奏云....',['type'=>1]);
    }

    /**
     * 上传蓝奏云
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function uploadLanzou(Request $request)
    {
        try {
            $data = $request->all();
            sleep(4);
            $apk_path = AppBuildModel::query()->where(['user_id'=>User::user_id(),'version'=>app_version(),'update'=>1])->orderBy('id','desc')->value('apk_path');
            $a = AppBuildModel::query()->where(['user_id'=>User::user_id(),'version'=>app_version(),'update'=>1])->max('id');
            Log::info($a);
            Log::info($apk_path);
            $apk_path = Storage::disk('public')->path($apk_path);
            $cookie = LanZouYunModel::query()->where(['user_id'=>User::user_id()])->value('cookie');
            $folder_id = $this->mkdirLanZou($cookie);
            $headers = [
                'cookie' => $cookie,
                'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36',
                'referer'=>'https://lanzoul.com/',
                'X-FORWARDED-FOR'=>getIp(),
                'CLIENT-IP'=>getIp(),
            ];
            $app_name = date('Y-m-d H:i:s').'更新文件.apk';
            $client = new Client();
            $result = $client->request('post','https://pc.woozooo.com/fileup.php',[
                'multipart' => [
                    [
                        'name'     => 'task',
                        'contents' => 1,
                    ],
                    [
                        'name'     => 've',
                        'contents' => 2
                    ],
                    [
                        'name'     => 'id',
                        'contents' => 'WU_FILE_0',
                    ],
                    [
                        'name'     => 'name',
                        'contents' => $app_name,
                    ],
                    [
                        'name'     => 'type',
                        'contents' => File::mimeType($apk_path),
                    ],
                    [
                        'name'     => 'lastModifiedDate',
                        'contents' => date(DATE_RFC2822).' (中国标准时间)',
                    ],
                    [
                        'name'     => 'size',
                        'contents' => File::size($apk_path),
                    ],
                    [
                        'name'     => 'folder_id_bb_n',
                        'contents' => $folder_id,
                    ],
                    [
                        'name'     => 'upload_file',
                        'contents' => file_get_contents($apk_path),
                        'filename'=> $app_name
                    ],
                ],
                'headers'=>$headers,
            ])->getBody()->getContents();
            $result = json_decode($result,true);
            activity()
                ->tap(function(Activity $activity) {
                    $activity->ip = \request()->ip();
                    $activity->user_id = User::user_id();
                })
                ->log('更新软件上传蓝奏云文件');
            if($result['zt'] != 1){
                return $this->error($result['info']);
            }
            $result = $result['text'][0];
            $param = [
                'download_link'=>$result['is_newd'].'/'.$result['f_id'],
                'desc'=>$data['desc'],
                'update_type'=>$data['update_type'],
            ];
            $this->nowAppUpdate($param);
            return $this->success('上传成功',$result);
        }catch (\Exception $e){
            Log::info($e->getMessage());
            return $this->error('更新失败,请检测蓝奏云是否过期');
        }
    }

    /**
     * 创建文件夹
     * @param $cookie
     * @return false|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function mkdirLanZou($cookie)
    {
        $headers = [
            'cookie' => $cookie,
            'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36',
            'referer'=>'https://lanzoul.com/',
            'X-FORWARDED-FOR'=>getIp(),
            'CLIENT-IP'=>getIp(),
        ];
        $client = new Client();
        //检测是否有这个文件夹 获取蓝奏云文件夹列表
        $folder = $client->request('post','https://pc.woozooo.com/doupload.php',[
            'form_params'=>[
                'task'=>47,
                'folder_id'=>-1,
            ],
            'headers'=>$headers
        ])->getBody()->getContents();
        $folder = json_decode($folder,true);
        if($folder['zt'] != 1){
            return false;
        }
        $appsaas_folder_name = 'appsaas_update';
        $appsaas_folder_desc = '请勿删除,appsaas更新文件夹';
        $appsaas_folder = false;
        foreach($folder['text'] as $key=>$value){
            if($value['name'] == $appsaas_folder_name){
                $appsaas_folder = $value;
            }
        }
        activity()
            ->tap(function(Activity $activity) {
                $activity->ip = \request()->ip();
                $activity->user_id = User::user_id();
            })
            ->log('更新软件创建蓝奏云文件夹');
        if(!$appsaas_folder){
            $result = $client->request('post','https://pc.woozooo.com/doupload.php',[
                'form_params'=>[
                    'task'=>2,
                    'parent_id'=>-1,
                    'folder_name'=>$appsaas_folder_name,
                    'folder_description'=>$appsaas_folder_desc
                ],
                'headers'=>$headers
            ])->getBody()->getContents();
            $result = json_decode($result,true);
            if($result['zt'] != 1){
                return false;
            }
            return $result['text'];
        }
        return $appsaas_folder['fol_id'];
    }

    /**
     * 更新配置
     * @param $data
     */
    protected function nowAppUpdate($data)
    {
        DB::beginTransaction();
        try {
            $param = [
                'user_id'=>User::user_id(),
                'download_link'=>$data['download_link'],
                'desc'=>$data['desc'],
                'version'=>app_version(),
                'update_type'=>$data['update_type'],
            ];
            AppConfigModel::query()->where(['user_id'=>User::user_id()])->update(['version'=>app_version()]);
            AppUpdateModel::query()->create($param);
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
        }
    }

    /**
     * 短网址生成
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function shortLink(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data,[
            'link'=>'required|url',
        ],[
            'link.required'=>'请填写正确的链接',
            'link.url'=>'请填写正确的链接'
        ]);
        if($validator->errors()->first()){
            return $this->error($validator->errors()->first());
        }
        $linkArray = [
            'taobao.com',
            'lanzoul.com',
            'lanzouo.com',
            'baidu.com',
            'meituan.com',
            'pinduoduo.com',
            'youdao.com',
            'lanzouq.com',
            'lanzoui.com',
            'lanzouw.com',
            'lanzour.com',
            'lanzout.com',
            'sourl.co',
            'qq.com',
            'yuque.com',
        ];
        preg_match_all('/[\w][\w-]*\.(?:com\.cn|com|cn|co|net|org|gov|cc|biz|info)(|$)/isU',$data['link'],$link);
        if(!in_array($link[0][0],$linkArray)){
            return $this->error('该域名不合法');
        }
        $client = new Client();
        $result = $client->request('get','https://tool.4rz.cn/dwz/sedwz.php?url='.$data['link'])->getBody()->getContents();
        $result = json_decode($result,true);
        if($result['code'] != 200){
            return $this->error('生成失败');
        }
        $data['shortUrl'] = $result['shortUrl'];
        activity()
            ->withProperties($data)
            ->tap(function(Activity $activity) {
                $activity->ip = \request()->ip();
                $activity->user_id = User::user_id();
            })
            ->log('用户生成短网址');
        return $this->success('',$result['shortUrl']);
    }


    /**
     * 绑定域名
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bindShareLink(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'share_link'=>'url',
        ],[
            'share_link.url'=>'请填写正确的链接'
        ]);
        if($validator->errors()->first()){
            return $this->error($validator->errors()->first());
        }
        $result = User::query()->where(['id'=>User::user_id()])->update(['share_link'=>$request->get('share_link')]);
        if(!$result){
            return $this->error('保存失败');
        }
        return $this->success('保存成功');
    }
}
