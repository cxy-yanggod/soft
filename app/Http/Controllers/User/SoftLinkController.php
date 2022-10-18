<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\UsersController;
use App\Models\SoftMenuModel;
use App\Models\SoftModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Models\Activity;

class SoftLinkController extends UsersController
{
    /**
     * 链接列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function softLinkList(Request $request)
    {
        $sort_field = $request->get('sortField') ?? 'id';
        $order = $request->get('sortOrder') == 'ascend' ? 'asc' : 'desc';
        $goods = SoftModel::query()->with('menu')->orderBy($sort_field,$order)
            ->where(function($query)use($request){
                if($request->get('keywords')){
                    $query->where('name','like','%'.$request->get('keywords').'%');
                }
            })->where(['type'=>'1','user_id'=>User::user_id()])->paginate($request->get('page_size') ?? 10);
        return $this->success('',$goods);
    }

    /**
     * 修改链接
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function updateSoftLink(Request $request)
    {
        $data = $request->all();
        $param = [
            'link'=>$data['link'],
            'l_password'=>$data['l_password']
        ];
        $result = SoftMenuModel::query()->where(['id'=>$data['id']])->update($param);
        if(!$result){
            return $this->error('关联失败');
        }
        return $this->success('关联成功');
    }


    /**
     * 删除链接
     * @param Request $request
     */
    public function deleteSoftLink(Request $request)
    {
        $data = $request->all();
        $result = SoftModel::query()->whereIn('id',$data['ids'])->delete();
        if(!$result){
            return $this->error('删除失败');
        }
        return $this->success('删除成功');
    }

    /**
     * 软件菜单
     * @return \Illuminate\Http\JsonResponse
     */
    public function softMenu()
    {
        $menu = SoftMenuModel::query()->where(['user_id'=>User::user_id()])->get();
        return $this->success('',$menu);
    }
}
