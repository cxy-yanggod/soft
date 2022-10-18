<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\UsersController;
use App\Models\SoftMenuModel;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class SoftMenuController extends UsersController
{
    /**
     * 菜单列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function menuList(Request $request)
    {
        $sort_field = $request->get('sortField') ?? 'id';
        $order = $request->get('sortOrder') == 'ascend' ? 'asc' : 'desc';
        $menu = SoftMenuModel::query()
            ->where(function($query)use($request){
                if($request->get('name')){
                    $query->where('name','like','%'.$request->get('name').'%');
                }
            })->where(['user_id'=>User::user_id()])->orderBy($sort_field,$order)
            ->paginate($request->get('page_size') ?? 10);
        $menu->each(function($item){
            $item['index'] = $item['index'] == 1 ? true : false;
        });
        return $this->success('',$menu);
    }

    /**
     * 添加菜单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function createMenu(Request $request)
    {
        $data = $request->all();
        $param = [
            'user_id'=>User::user_id(),
            'name'=>$data['name'],
            'password'=>$data['password'] ?? '',
            'sort'=>$data['sort'],
            'is_vip'=>$data['is_vip'],
        ];
        $result = SoftMenuModel::query()->create($param);
        if(!$result){
            return $this->error('添加失败');
        }
        return $this->success('添加成功');
    }


    /**
     * 修改菜单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function updateMenu(Request $request)
    {
        $data = $request->all();
        $param = [
            'name'=>$data['name'],
            'password'=>$data['password'] ?? '',
            'sort'=>$data['sort'],
            'is_vip'=>$data['is_vip'],
        ];
        $result = SoftMenuModel::query()->where(['id'=>$data['id'],'user_id'=>User::user_id()])->update($param);
        if(!$result){
            return $this->error('修改失败');
        }
        return $this->success('修改成功');
    }


    /**
     * 删除菜单
     * @param Request $request
     */
    public function deleteMenu(Request $request)
    {
        $data = $request->all();
        $result = SoftMenuModel::query()->whereIn('id',$data['ids'])->where(['user_id'=>User::user_id()])->delete();
        if(!$result){
            return $this->error('删除失败');
        }
        return $this->success('删除成功');
    }

    /**
     * 首页菜单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexMenu(Request $request)
    {
        $data = $request->all();
        $index = $data['index'] == true ? -1 : 1;
        $result = SoftMenuModel::query()->where(['user_id'=>User::user_id(),'id'=>$data['id']])->update(['index'=>$index]);
        if(!$result){
            return $this->error('修改失败');
        }
        return $this->success('修改成功');
    }

    /**
     * 菜单详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function menuDetail(Request $request)
    {
        $menu = SoftMenuModel::query()->where(['user_id'=>User::user_id(),'id'=>$request->get('id')])->first();
        return $this->success('',$menu);
    }
}
