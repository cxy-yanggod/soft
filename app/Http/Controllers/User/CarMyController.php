<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\UsersController;
use App\Models\AppCarMyModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class CarMyController extends UsersController
{
    /**
     * APP卡密列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function carMyList(Request $request)
    {
        $sort_field = $request->get('sortField') ?? 'id';
        $order = $request->get('sortOrder') == 'ascend' ? 'asc' : 'desc';
        $carmy = AppCarMyModel::query()
            ->with('app_users_info')
            ->where(['user_id'=>User::user_id()])
            ->where(function($query)use($request){
            if($request->get('carmy')){
                $query->where('carmy','like','%'.$request->get('carmy').'%');
            }
        })->orderBy($sort_field,$order)->paginate($request->get('page_size') ?? 10);
        return $this->success('',$carmy);
    }

    /**
     * 生成卡密
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function createCarMy(Request $request)
    {
        $data = $request->all();
        $prefix = $data['prefix'] ?? '';
        if($data['nums'] > 100){
            return $this->error('一次最多生成100个');
        }
        $params = [];
        $type = -1;
        if($data['day'] >= 365){
            $type = 1;
        }
        for($i = 1;$i<=$data['nums'];$i++){
            $params[$i]['type'] = $type;
            $params[$i]['day'] = $data['day'];
            $params[$i]['carmy'] = $prefix.Str::random(15);
            $params[$i]['user_id'] = User::user_id();
        }
        foreach($params as $key=>$value){
            AppCarMyModel::query()->create($value);
        }
        return $this->success('添加成功');
    }

    /**
     * 删除卡密
     * @param Request $request
     */
    public function deleteCarMy(Request $request)
    {
        $data = $request->all();
        $result = AppCarMyModel::query()->whereIn('id',$data['ids'])->delete();
        if(!$result){
            return $this->error('删除失败');
        }
        return $this->success('删除成功');
    }
}
