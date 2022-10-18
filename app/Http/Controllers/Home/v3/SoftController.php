<?php

namespace App\Http\Controllers\Home\v3;

use App\Http\Controllers\HomeController;
use App\Models\AppConfigModel;
use App\Models\CollectionModel;
use App\Models\CommentFabulousModel;
use App\Models\CommentModel;
use App\Models\SoftMenuModel;
use App\Models\SoftModel;
use DfaFilter\SensitiveHelper;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class SoftController extends HomeController
{
    /**
     * 软件菜单
     * @return \Illuminate\Http\JsonResponse
     */
    public function softMenu()
    {
        $menu = SoftMenuModel::query()->where(['user_id'=>$this->home_user_id])
            ->orderBy('sort','desc')
            ->select('id', 'name', 'password','link','l_password','is_vip')
            ->get();
        return $this->success('',$menu);
    }

    /**
     * 软件列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function softList(Request $request)
    {
        $data = $request->all();
        $menu_id = $request->get('menu_id');
        $keywords = $request->get('keywords');
        $lanzouyun_static = AppConfigModel::query()->where(['user_id'=>$this->home_user_id])->value('lanzouyun_static');
        $menu = SoftMenuModel::query()->where(['id'=>$menu_id,'user_id'=>$this->home_user_id])->first();
        $soft = SoftModel::query()->with(['menu'=>function($q){
            $q->select('id','name');
        }])
            ->where(function($q)use($keywords){
                if($keywords){
                    $q->where('name','like','%'.$keywords.'%');
                }
            })
            ->where(['user_id'=>$this->home_user_id,'menu_id'=>$menu_id])
            ->select('id','name','menu_id','icon','size','created_at')
            ->orderBy('id','desc')
            ->paginate(10);
        if($soft->isEmpty()){
            $soft = [];
        }else{
            $soft->each(function($item)use($menu){
                $item['time'] = mdate($item['created_at']->timestamp);
                $item['size'] = $item['size'].'M';
                $item['is_vip'] = $menu['is_vip'];
                return $item;
            });
            $soft = $soft->toArray()['data'];
        }
        if(!$menu['link']){
            return $this->success('',$soft);
        }
        $client = new Client();
        $html = Redis::get($menu['id'].$menu['user_id'].'_html');
        if(!$html){
            $html = $client->request('get',$menu['link'],[
                'headers'=>[
                    'referer'=>$menu['link'],
                    'X-FORWARDED-FOR'=>getIp(),
                    'CLIENT-IP'=>getIp(),
                ],
            ])->getBody()->getContents();
            Redis::setex($menu['id'].$menu['user_id'].'_html',100,$html);
        }
        $pattern = "#var pwd;
\tvar pgs;
\tvar (.*?) = '(.*?)';
\tvar (.*?) = '(.*?)';#";
        preg_match_all($pattern,$html,$sign);
        preg_match_all('/\'fid\':(.*?),/',$html,$fid);
        preg_match_all('/\'uid\':\'(.*?)\'/',$html,$uid);
        preg_match_all('/\'vip\':\'(.*?)\'/',$html,$vip);
        $result = $client->request('post','https://lanzoul.com/filemoreajax.php',[
            'form_params'=>[
                'lx'=>2,
                'fid'=>$fid[1][0] ?? '',
                'uid'=>$uid[1][0] ?? '',
                'pg'=>$data['page'] ?? 1,
                'rep'=>0,
                't'=>$sign[2][0] ?? '',
                'k'=>$sign[4][0] ?? '',
                'up'=>1,
                'vip'=>$vip[1][0] ?? 0,
                'webfoldersign'=>'',
                'pwd'=>$menu['l_password'],
            ],
            'headers'=>[
                'referer'=>$menu['link'],
                'X-FORWARDED-FOR'=>getIp(),
                'CLIENT-IP'=>getIp(),
            ],
        ])->getBody()->getContents();
        $result = json_decode($result,true);
        if($result['zt'] != 1){
            return $this->error('暂无更多');
        }
        foreach($result['text'] as $key=>$value){
            $value['ico'] = isset($value['ico']) ? $value['ico'] : '';
            $result['text'][$key]['icon'] =  $lanzouyun_static.$value['ico'];
            $result['text'][$key]['name'] =  $value['name_all'];
            $result['text'][$key]['menu'] =  [
                'id'=>$menu['id'],
                'name'=>$menu['name'],
            ];
            $result['text'][$key]['is_vip'] = $menu['is_vip'];
        }
        $result['data'] = array_merge($soft,$result['text']);
        return $this->success('',$result);
    }

    /**
     * 软件详情
     * @param $soft_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function softDetail($soft_id)
    {
        $soft = SoftModel::query()->with(['menu'=>function($q){
            $q->select('id','name');
        }])->where(['id'=>$soft_id])->first();
        $soft['soft_screenshot'] = json_decode($soft['soft_screenshot']);
        if(!$soft){
            return $this->error('暂无软件');
        }
        $soft->increment('nums');
        $soft['time'] = mdate($soft['created_at']->timestamp);
        return $this->success('',$soft);
    }

    /**
     * 评论列表
     * @param $soft_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function commentList($soft_id)
    {
        $result = CommentModel::query()->with(['app_users'=>function($query){
            $query->select('avatar','id','nickname');
        },'reply.app_users'])
            ->where(['user_id'=>$this->home_user_id,'content_id'=>$soft_id,'type'=>1,'parent_id'=>0])
            ->select('id','user_id', 'app_users_id', 'type', 'parent_id', 'fabulous', 'content', 'created_at')
            ->orderBy('id','desc')
            ->paginate(15);
        $result->each(function($item)use($soft_id){
            $item['time'] = mdate($item['created_at']->timestamp);
            $item['is_fabulous'] = false;
            $item['is_collection'] = false;
            if(auth('app')->id()){
                $fabulous = CommentFabulousModel::query()->where(['comment_id'=>$item['id'],'app_users_id'=>auth('app')->id()])->first();
                $item['is_fabulous'] = $fabulous ? true : false;
                $collection = CollectionModel::query()->where(['content_id'=>$soft_id,'app_users_id'=>auth('app')->id()])->first();
                $item['is_collection'] = $collection ? true : false;
            }
        });
        return $this->success('',$result);
    }

    /**
     * 软件评论
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function comment(Request $request)
    {
        $data = $request->all();
        $data['app_users_id'] = auth('app')->id();
        $data['user_id'] = $this->home_user_id;
        $data['type'] = 1;
        $validator = Validator::make($data,[
            'content'=>'required|min:5|max:400',
        ],[
            'content.required'=>'请填写内容',
            'content.min'=>'内容太少了',
            'content.max'=>'内容太多了',
        ]);
        $errors = $validator->errors()->first();
        if($errors){
            return $this->error($errors);
        }
        $weiguizi = AppConfigModel::query()->where(['user_id'=>$this->home_user_id])->select('is_weiguizi','weiguizi')->first();
        if($weiguizi['is_weiguizi'] == 1){
            $handle = SensitiveHelper::init()->setTree(explode(',',$weiguizi['weiguizi']));
            $data['content'] = $handle->replace($data['content'], '*', true);
        }
        $result = CommentModel::query()->create($data);
        if(!$result){
            return $this->error('评论失败');
        }
        return $this->success('评论成功');
    }

    /**
     * 回复评论
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function replyComment(Request $request)
    {
        $data = $request->all();
        $data['app_users_id'] = auth('app')->id();
        $data['user_id'] = $this->home_user_id;
        $data['type'] = 1;
        $data['parent_id'] = $data['comment_id'];
        $validator = Validator::make($data,[
            'content'=>'required|min:5|max:400',
        ],[
            'content.required'=>'请填写内容',
            'content.min'=>'内容太少了',
            'content.max'=>'内容太多了',
        ]);
        $errors = $validator->errors()->first();
        if($errors){
            return $this->error($errors);
        }
        $result = CommentModel::query()->create($data);
        if(!$result){
            return $this->error('回复失败');
        }
        return $this->success('回复成功');
    }

    /**
     * 点赞评论
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fabulousComment(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['user_id'] = $this->home_user_id;
            $data['app_users_id'] = auth('app')->id();
            $data['type'] = 1;
            $fabulous = CommentFabulousModel::query()->where(['type'=>1,'user_id'=>$data['user_id'],'app_users_id'=>$data['app_users_id'],'comment_id'=>$data['comment_id']])->first();
            if($fabulous){
                CommentModel::query()->where(['id'=>$data['comment_id']])->decrement('fabulous');
                CommentFabulousModel::query()->where(['id'=>$fabulous['id']])->delete();
            }else{
                CommentModel::query()->where(['id'=>$data['comment_id']])->increment('fabulous');
                CommentFabulousModel::query()->create($data);
            }
            DB::commit();
            return $this->success('操作成功');
        }catch (\Exception $e){
            DB::rollBack();
            return $this->error('操作失败'.$e->getMessage());
        }
    }

    /**
     * 收藏软件
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function collection(Request $request)
    {
        $data = $request->all();
        $data['user_id'] = $this->home_user_id;
        $data['app_users_id'] = auth('app')->id();
        $data['type'] = 1;
        $collection = CollectionModel::query()->where(['type'=>1,'user_id'=>$data['user_id'],'app_users_id'=>$data['app_users_id'],'content_id'=>$data['content_id']])->first();
        if($collection){
            CollectionModel::query()->where(['id'=>$collection['id']])->delete();
            return $this->success('操作成功',['collection'=>false]);
        }else{
            CollectionModel::query()->create($data);
            return $this->success('操作成功',['collection'=>true]);
        }
    }

    /**
     * 软件推荐
     * @param $soft_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function recommend($soft_id)
    {
        $menu_id = SoftModel::query()->where(['id'=>$soft_id])->value('menu_id');
        $soft = SoftModel::query()->where(['menu_id'=>$menu_id])->inRandomOrder()->take(10)->get();
        return $this->success('',$soft);
    }

    /**
     * 菜单详情
     * @param $menu_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function softMenuDetail($menu_id)
    {
        $menu = SoftMenuModel::query()->where(['user_id'=>$this->home_user_id,'id'=>$menu_id])
            ->select('id', 'name', 'password','link','l_password','is_vip')
            ->first();
        return $this->success('',$menu);
    }
}
