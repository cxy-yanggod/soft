<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\UsersController;
use App\Models\FeedBackModel;
use App\Models\User;
use Illuminate\Http\Request;

class FeedBackController extends UsersController
{
    /**
     * 反馈列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function feedbackList(Request $request)
    {
        $sort_field = $request->get('sortField') ?? 'id';
        $order = $request->get('sortOrder') == 'ascend' ? 'asc' : 'desc';
        $goods = FeedBackModel::query()->with('reply')->where(['user_id'=>User::user_id(),'parent_id'=>0])->orderBy($sort_field,$order)->paginate($request->get('page_size') ?? 10);
        return $this->success('',$goods);
    }

    /**
     * 添加反馈
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function replyFeedback(Request $request)
    {
        $data = $request->all();
        $feedback = FeedBackModel::query()->where(['id'=>$data['id']])->first();
        $reply = FeedBackModel::query()->where(['parent_id'=>$data['id']])->first();
        $param = [
            'app_users_id'=>$feedback['app_users_id'],
            'content'=>$data['content'],
            'qq'=>$feedback['qq'],
            'ip'=>$feedback['ip'],
            'parent_id'=>$feedback['id'],
            'user_id'=>User::user_id()
        ];
        if($reply){
            $result = FeedBackModel::query()->where(['id'=>$reply['id']])->update($param);
        }else{
            $result = FeedBackModel::query()->create($param);
        }
        if(!$result){
            return $this->error('回复失败');
        }
        return $this->success('回复成功');
    }

    /**
     * 删除反馈
     * @param Request $request
     */
    public function deleteFeedback(Request $request)
    {
        $data = $request->all();
        $result = FeedBackModel::query()->whereIn('id',$data['ids'])->delete();
        if(!$result){
            return $this->error('删除失败');
        }
        return $this->success('删除成功');
    }
}
