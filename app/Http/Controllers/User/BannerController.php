<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\UsersController;
use App\Models\BannerModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Models\Activity;

class BannerController extends UsersController
{
    /**
     * 轮播图列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bannerList(Request $request)
    {
        $sort_field = $request->get('sortField') ?? 'id';
        $order = $request->get('sortOrder') == 'ascend' ? 'asc' : 'desc';
        $banner = BannerModel::query()
            ->where(['user_id'=>User::user_id()])
            ->orderBy($sort_field,$order)
            ->paginate($request->get('page_size') ?? 10);
        return $this->success('',$banner);
    }

    /**
     * 添加轮播图
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function createBanner(Request $request)
    {
        $data = $request->all();
        $data['user_id'] = User::user_id();
        if($request->get('link')){
            $validator = Validator::make($data,['link'=>'required|url'],['link.required'=>'请输入链接','link.url'=>'请输入正确的链接']);
            if($validator->errors()->first()){
                return $this->error($validator->errors()->first());
            }
        }
        $result = BannerModel::query()->create($data);
        if(!$result){
            return $this->error('添加失败');
        }
        return $this->success('添加成功');
    }

    /**
     * 修改轮播图
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function updateBanner(Request $request)
    {
        $data = $request->all();
        $param = [
            'path'=>$data['path'],
            'link'=>$data['link'],
            'type'=>$data['type'],
        ];
        $result = BannerModel::query()->where(['id'=>$data['id']])->update($param);
        if(!$result){
            return $this->error('修改失败');
        }
        return $this->success('修改成功');
    }


    /**
     * 删除轮播图
     * @param Request $request
     */
    public function deleteBanner(Request $request)
    {
        $data = $request->all();
        $result = BannerModel::query()->whereIn('id',$data['ids'])->delete();
        if(!$result){
            return $this->error('删除失败');
        }
        return $this->success('删除成功');
    }

    /**
     * 幻灯详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bannerDetail(Request $request)
    {
        $article = BannerModel::query()->where(['user_id'=>User::user_id(),'id'=>$request->get('id')])->first();
        return $this->success('',$article);
    }
}
