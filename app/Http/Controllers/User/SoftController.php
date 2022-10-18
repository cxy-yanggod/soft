<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\UsersController;
use App\Models\CommentModel;
use App\Models\SoftModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Models\Activity;

class SoftController extends UsersController
{
    /**
     * 软件列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function softList(Request $request)
    {
        $sort_field = $request->get('sortField') ?? 'id';
        $order = $request->get('sortOrder') == 'ascend' ? 'asc' : 'desc';
        $goods = SoftModel::query()->with('menu')->orderBy($sort_field,$order)
            ->where(function($query)use($request){
                if($request->get('keywords')){
                    $query->where('name','like','%'.$request->get('keywords').'%');
                }
            })->where(['user_id'=>User::user_id()])->paginate($request->get('page_size') ?? 10);
        return $this->success('',$goods);
    }

    /**
     * 添加软件
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function createSoft(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data,['link'=>'url'],['link.url'=>'请输入正确的链接']);
        if($validator->errors()->first()){
            return $this->error($validator->errors()->first());
        }
        $param = [
            'user_id'=>User::user_id(),
            'name'=>$data['name'],
            'desc'=>$data['desc'] ?? '',
            'icon'=>$data['icon'] ?? '',
            'menu_id'=>$data['menu_id'],
            'size'=>$data['size'] ?? 0,
            'link'=>$data['link'],
            'soft_screenshot'=>''
        ];
        if($data['soft_screenshot']){
            $param['soft_screenshot'] = json_encode($data['soft_screenshot']);
        }
        $result = SoftModel::query()->create($param);
        if(!$result){
            return $this->error('添加失败');
        }
        activity()
            ->withProperties($param)
            ->tap(function(Activity $activity) {
                $activity->ip = \request()->ip();
                $activity->user_id = User::user_id();
            })
            ->log('添加软件');
        return $this->success('添加成功');
    }


    /**
     * 修改软件
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function updateSoft(Request $request)
    {
        $data = $request->all();
        $param = [
            'user_id'=>User::user_id(),
            'name'=>$data['name'],
            'desc'=>$data['desc'],
            'icon'=>$data['icon'],
            'menu_id'=>$data['menu_id'],
            'size'=>$data['size'],
            'link'=>$data['link'],
            'soft_screenshot'=>''
        ];
        if($data['soft_screenshot']){
            $param['soft_screenshot'] = json_encode($data['soft_screenshot']);
        }
        $result = SoftModel::query()->where(['id'=>$data['id']])->update($param);
        if(!$result){
            return $this->error('修改失败');
        }
        activity()
            ->withProperties($param)
            ->tap(function(Activity $activity) {
                $activity->ip = \request()->ip();
                $activity->user_id = User::user_id();
            })
            ->log('修改软件');
        return $this->success('修改成功');
    }


    /**
     * 删除软件
     * @param Request $request
     */
    public function deleteSoft(Request $request)
    {
        $data = $request->all();
        $result = SoftModel::query()->whereIn('id',$data['ids'])->delete();
        if(!$result){
            return $this->error('删除失败');
        }
        activity()
            ->withProperties($data)
            ->tap(function(Activity $activity) {
                $activity->ip = \request()->ip();
                $activity->user_id = User::user_id();
            })
            ->log('删除软件');
        return $this->success('删除成功');
    }

    /**
     * 软件详情
     * @param Request $request
     * @return void
     */
    public function softDetail(Request $request)
    {
        $soft = SoftModel::query()->where(['user_id'=>User::user_id(),'id'=>$request->get('id')])->first();
        if($soft['soft_screenshot']){
            $soft['soft_screenshot'] = json_decode($soft['soft_screenshot'],true);
        }else{
            $soft['soft_screenshot'] = [];
        }
        return $this->success('',$soft);
    }

    /**
     * 评论列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function commentList(Request $request)
    {
        $sort_field = $request->get('sortField') ?? 'id';
        $order = $request->get('sortOrder') == 'ascend' ? 'asc' : 'desc';
        $comment = CommentModel::query()
            ->with('app_users')
            ->where(function($query)use($request){
                if($request->get('keywords')){
                    $query->where('content','like','%'.$request->get('keywords').'%');
                }
            })
            ->where(['type'=>1,'user_id'=>User::user_id()])->orderBy($sort_field,$order)->paginate($request->get('page_size') ?? 10);
        return $this->success('',$comment);
    }

    /**
     * 删除评论
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteComment(Request $request)
    {
        $data = $request->all();
        $result = CommentModel::query()->whereIn('id',$data['ids'])->delete();
        if(!$result){
            return $this->error('删除失败');
        }
        activity()
            ->withProperties($data)
            ->tap(function(Activity $activity) {
                $activity->ip = \request()->ip();
                $activity->user_id = User::user_id();
            })
            ->log('删除评论');
        return $this->success('删除成功');
    }
}
