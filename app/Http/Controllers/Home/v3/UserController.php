<?php

namespace App\Http\Controllers\Home\v3;

use App\Http\Controllers\HomeController;
use App\Models\AppCarMyModel;
use App\Models\AppConfigModel;
use App\Models\AppUsersModel;
use App\Models\ArticleModel;
use App\Models\CollectionModel;
use App\Models\CommentFabulousModel;
use App\Models\CommentModel;
use App\Models\FeedBackModel;
use App\Models\SoftModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends HomeController
{
    /**
     * 用户信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function userInfo()
    {
        $user_info = AppUsersModel::query()->where(['id'=>auth('app')->id(),'user_id'=>$this->home_user_id])->first();
        $user_info['is_vip'] = false;
        if($user_info['vip_end_time'] < now()){
            $user_info['vip_time_desc'] = '已过期:'.$user_info['vip_end_time'];
            $user_info['vip_name'] = '普通用户';
            if($user_info['vip_end_time'] == ''){
                $user_info['vip_time_desc'] = '';
            }
        }
        if($user_info['vip_end_time'] != '' && $user_info['vip_end_time'] > now()){
            $user_info['is_vip'] = true;
            $user_info['vip_name'] = '至尊会员';
            $user_info['vip_time_desc'] = '有效期至:'.Carbon::parse($user_info['vip_end_time'])->toDateString();
        }
        return $this->success('',$user_info);
    }

    /**
     * 使用卡密
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function useCarMy(Request $request)
    {
        DB::beginTransaction();
        try {
            $carmy = $request->get('carmy');
            if(!$carmy){
                return $this->error('请输入卡密');
            }
            $carmy = AppCarMyModel::query()->where(['carmy'=>$carmy,'user_id'=>$this->home_user_id])->first();
            if(!$carmy){
                return $this->error('卡密错误');
            }
            if($carmy['status'] != 1){
                return $this->error('卡密已被使用');
            }
            $is_one_day_carmy = AppConfigModel::query()->where(['user_id'=>$this->home_user_id])->value('is_one_day_carmy');
            if($is_one_day_carmy == 1 && $carmy['day'] == 1){
                $user_carmy = AppCarMyModel::query()->where(['use_app_users_id'=>auth('app')->id(),'day'=>1,'status'=>-1,'user_id'=>$this->home_user_id])->first();
                if($user_carmy){
                    return $this->error('只能使用一次体验卡');
                }
            }
            $carmy->update([
                'use_time'=>now(),
                'status'=>-1,
                'use_app_users_id'=>auth('app')->id(),
            ]);
            //给用户增加天数
            AppUsersModel::addVipDay((int) $carmy['day']);
            DB::commit();
            return $this->success('开通成功');
        }catch (\Exception $e){
            DB::rollBack();
            return $this->error('开通失败');
        }
    }

    /**
     * 我的评论
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function myCommentList(Request $request)
    {
        $type = $request->get('type');
        $comment = CommentModel::query()->with(['app_users'=>function($query){
            $query->select('avatar','id','nickname');
        }]);
        if($type == 1){
            $comment = $comment->where(['parent_id'=>0]);
        }else{
            $comment = $comment->where('parent_id','!=',0);
        }
        $comment = $comment->where(['user_id'=>$this->home_user_id,'app_users_id'=>auth('app')->id()])
            ->select('id','user_id', 'app_users_id', 'type', 'parent_id', 'fabulous', 'content', 'content_id', 'created_at')
            ->orderBy('id','desc')
            ->paginate(15);
        $comment->each(function($item){
           if($item['type'] == 1){
               $content = SoftModel::query()->where(['id'=>$item['content_id']])->select('id','name')->first();
               $content['type'] = 1;
           } else{
               $content = ArticleModel::query()->where(['id'=>$item['content_id']])->select('id','title')->first();
               $content['type'] = 2;
           }
            $item['article'] = $content;
            $item['time'] = mdate($item['created_at']->timestamp);
            $item['is_fabulous'] = false;
            $item['is_collection'] = false;
            if(auth('app')->id()){
                $fabulous = CommentFabulousModel::query()->where(['comment_id'=>$item['id'],'app_users_id'=>auth('app')->id()])->first();
                $item['is_fabulous'] = $fabulous ? true : false;
            }
        });
        return $this->success('',$comment);
    }

    /**
     * 我的收藏
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function myCollection(Request $request)
    {
        $type = $request->get('type');
        $collection = CollectionModel::query();
        if($type == 1){
            $collection = $collection->where(['type'=>1]);
        }else{
            $collection = $collection->where(['type'=>2]);
        }
        $collection = $collection->where(['user_id'=>$this->home_user_id,'app_users_id'=>auth('app')->id()])
            ->select('user_id', 'app_users_id', 'content_id', 'type','created_at')
            ->orderBy('id','desc')
            ->paginate(15);
        $collection->each(function($item){
            if($item['type'] == 1){
                $content = SoftModel::query()->where(['id'=>$item['content_id']])->select('id','name','created_at','icon','size')->first();
                $content['type'] = 1;
            } else{
                $content = ArticleModel::query()->where(['id'=>$item['content_id']])->select('id','title','created_at','cover','fabulous')->first();
                $content['type'] = 2;
            }
            $content['time'] = mdate($content['created_at']->timestamp);
            $item['article'] = $content;
        });
        return $this->success('',$collection);
    }

    /**
     * 我的反馈
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function feedback(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data,[
            'content'=>'required|min:10|max:400',
            'qq'=>'required|max:100'
        ],[
            'content.required'=>'请填写内容',
            'content.min'=>'内容最少10字符',
            'content.max'=>'内容最多400字符',
            'qq.required'=>'请填写联系方式',
            'qq.max'=>'联系方式最多100字符',
        ]);
        $errors = $validator->errors()->first();
        if($errors){
            return $this->error($errors);
        }
        $data['user_id'] = $this->home_user_id;
        $data['app_users_id'] = auth('app')->id();
        $data['ip'] = $request->ip();
        $result = FeedBackModel::query()->create($data);
        if(!$result){
            return $this->error('反馈失败');
        }
        return $this->success('反馈成功');
    }

    /**
     * 反馈记录
     * @return \Illuminate\Http\JsonResponse
     */
    public function replyFeedbackList()
    {
        $feedback = FeedBackModel::query()
            ->with(['app_users'=>function($query){
                $query->select('avatar','id','nickname');
            },'reply'])
            ->where(['user_id'=>$this->home_user_id,'app_users_id'=>auth('app')->id(),'parent_id'=>0])
            ->orderBy('id','desc')
            ->paginate(15);
        return $this->success('',$feedback);
    }
}
