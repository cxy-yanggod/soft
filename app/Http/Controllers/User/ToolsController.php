<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\UsersController;
use App\Models\ToolsModel;
use App\Models\ToolsPlateModel;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ToolsController extends UsersController
{
    /**
     * 工具板块
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toolsPlateList(Request $request)
    {
        $sort_field = $request->get('sortField') ?? 'id';
        $order = $request->get('sortOrder') == 'ascend' ? 'asc' : 'desc';
        $tools_plate = ToolsPlateModel::query()
            ->where(['user_id'=>User::user_id()])
            ->where(function($query)use($request){
                if($request->get('keywords')){
                    $query->where('title','like','%'.$request->get('keywords').'%');
                }
                if($request->get('keywords')){
                    $query->where('desc','like','%'.$request->get('keywords').'%');
                }
            })->orderBy($sort_field,$order)->paginate($request->get('page_size') ?? 10);
        return $this->success('',$tools_plate);
    }

    /**
     * 添加板块
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createToolsPlate(Request $request)
    {
        $data = $request->all();
        $data['user_id'] = User::user_id();
        if(ToolsPlateModel::query()->where(['user_id'=>User::user_id()])->count() >=4){
            return $this->error('最多4个');
        }
        $result = ToolsPlateModel::query()->create($data);
        if(!$result){
            return $this->error('添加失败');
        }
        return $this->success('添加成功');
    }

    /**
     * 板块详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toolsPlateDetail(Request $request)
    {
        $tools_plate = ToolsPlateModel::query()->where(['user_id'=>User::user_id(),'id'=>$request->get('id')])->first();
        return $this->success('',$tools_plate);
    }

    /**
     * 修改版块
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateToolsPlate(Request $request)
    {
        $data = $request->all();
        $param = [
            'title'=>$data['title'],
            'desc'=>$data['desc'],
            'link'=>$data['link'],
            'type'=>$data['type'],
        ];
        $result = ToolsPlateModel::query()->where(['id'=>$data['id']])->update($param);
        if(!$result){
            return $this->error('添加失败');
        }
        return $this->success('添加成功');
    }



    /**
     * 工具板块
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toolsList(Request $request)
    {
        $sort_field = $request->get('sortField') ?? 'id';
        $order = $request->get('sortOrder') == 'ascend' ? 'asc' : 'desc';
        $tools_plate = ToolsModel::query()
            ->where(['user_id'=>User::user_id()])
            ->where(function($query)use($request){
                if($request->get('keywords')){
                    $query->where('name','like','%'.$request->get('keywords').'%');
                }
            })->orderBy($sort_field,$order)->paginate($request->get('page_size') ?? 10);
        return $this->success('',$tools_plate);
    }

    /**
     * 添加工具
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createTools(Request $request)
    {
        $data = $request->all();
        $data['user_id'] = User::user_id();
        $result = ToolsModel::query()->create($data);
        if(!$result){
            return $this->error('添加失败');
        }
        return $this->success('添加成功');
    }

    /**
     * 板块详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toolsDetail(Request $request)
    {
        $tools_plate = ToolsModel::query()->where(['user_id'=>User::user_id(),'id'=>$request->get('id')])->first();
        return $this->success('',$tools_plate);
    }

    /**
     * 修改工具
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTools(Request $request)
    {
        $data = $request->all();
        $param = [
            'name'=>$data['name'],
            'link'=>$data['link'],
            'type'=>$data['type'],
        ];
        $result = ToolsModel::query()->where(['id'=>$data['id']])->update($param);
        if(!$result){
            return $this->error('添加失败');
        }
        return $this->success('添加成功');
    }

    /**
     * 删除工具
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTools(Request $request)
    {
        $data = $request->all();
        $result = ToolsModel::query()->whereIn('id',$data['ids'])->delete();
        if(!$result){
            return $this->error('删除失败');
        }
        return $this->success('删除成功');
    }
}
