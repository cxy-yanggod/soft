<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\UsersController;
use App\Models\AppUsersModel;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Models\Activity;

class UserController extends UsersController
{
    /**
     * 获取用户信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function userInfo()
    {
        $user_info = User::query()->where(['id'=>auth()->id()])->first();
        return $this->success('',$user_info);
    }

    /**
     * 修改用户信息
     * @param Request $request
     * @return array
     */
    public function updateUserInfo(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data,[
            'qq'=>'regex:/^[0-9]{5,11}+$/',
        ],[
            'qq.regex'=>'请填写正确的qq',
        ]);
        if($validator->errors()->first()){
            return $this->error($validator->errors()->first());
        }
        $params = [
            'nickname'=>$data['nickname'],
            'qq'=>$data['qq'],
        ];
        if($request->get('password')){
            $params['password'] = Hash::make($request->get('password'));
        }
        $result = User::query()->where(['id'=>auth()->id()])->update($params);
        if(!$result){
            return $this->error('修改失败');
        }
        return $this->success('修改成功',['user'=>auth()->user()]);
    }

    /**
     * 用户列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userList(Request $request)
    {
        $sort_field = $request->get('sortField') ?? 'id';
        $order = $request->get('sortOrder') == 'ascend' ? 'asc' : 'desc';
        $user_list = AppUsersModel::query()
            ->where(function($q)use($request){
                if($request->get('keywords')){
                    $q->where('qq','like','%'.$request->get('keywords').'%');
                    $q->orWhere('username','like','%'.$request->get('keywords').'%');
                    $q->orWhere('nickname','like','%'.$request->get('keywords').'%');
                }
            })
            ->where(['user_id'=>User::user_id()])
            ->orderBy($sort_field,$order)
            ->paginate($request->get('page_size') ?? 10);
        $user_list->each(function($item){
            $item['is_vip'] = false;
            $item['status'] = $item['status'] == 1 ? true : false;
            if($item['vip_end_time'] != '' && $item['vip_end_time'] > now()){
                $item['is_vip'] = true;
            }
        });
        return $this->success('',$user_list);
    }

    /**
     * 用户状态
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userStatus(Request $request)
    {
        $data = $request->all();
        $status = $data['status'] == true ? -1 : 1;
        $result = AppUsersModel::query()->where(['user_id'=>User::user_id(),'id'=>$data['id']])->update(['status'=>$status]);
        if(!$result){
            return $this->error('修改失败');
        }
        if($status == 1){
            $text = '开启用户';
        }else{
            $text = '禁用用户';
        }
        return $this->success('修改成功');
    }

    /**
     * 用户详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userDetail(Request $request)
    {
        $user = AppUsersModel::query()->where(['user_id'=>User::user_id(),'id'=>$request->get('id')])->first();
        return $this->success('',$user);
    }

    /**
     * 添加APP用户
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createUser(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data,[
            'username'=>'required|min:5|max:15',
            'password'=>'required|same:repassword|min:6|max:20',
            'qq'=>'required|min:5|max:10',
        ],[
            'username.required'=>'请填写账号',
            'username.min'=>'账号格式为5位到15位字符',
            'username.max'=>'账号格式为5位到15位字符',
            'password.required'=>'请填写密码',
            'password.same'=>'两次密码输入不一样',
            'password.min'=>'密码格式为5位到15位字符',
            'password.max'=>'密码格式为5位到15位字符账号',
            'qq.required'=>'请填写qq',
            'qq.min'=>'qq格式不对',
            'qq.max'=>'qq格式不对',
        ]);
        $errors = $validator->errors()->first();
        if($errors){
            return $this->error($errors);
        }
        $user = AppUsersModel::query()->where(['username'=>$data['username']])->first();
        if($user){
            return $this->error('账号已存在');
        }
        $data['avatar'] = 'https://q1.qlogo.cn/g?b=qq&nk='.$data['qq'].'&s=640';
        $data['password'] = Hash::make($data['password']);
        $data['user_id'] = User::user_id();
        $data['nickname'] = $this->getQqInfo((int) $data['qq'])['nickname'] ?? $data['username'];
        $result = AppUsersModel::query()->create($data);
        if(!$result){
            return $this->error('添加失败');
        }
        return $this->success('添加成功');
    }

    /**
     * 获取qq昵称跟头像
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getQqInfo(string $qq):array
    {
        $qq_api = 'https://r.qzone.qq.com/fcg-bin/cgi_get_portrait.fcg?g_tk=1518561325&uins='.$qq;
        $client = new Client();
        $result = $client->get($qq_api)->getBody()->getContents();
        $result = iconv("GBK","UTF-8",$result);
        preg_match_all("/portraitCallBack\((.*)\)/is",$result,$qq_result);
        $qq_result = json_decode($qq_result[1][0],true);
        $data = [
            'avatar' => $qq_result[array_keys($qq_result)[0]][0],
            'nickname' => $qq_result[array_keys($qq_result)[0]][6]
        ];
        return $data;
    }

    /**
     * 修改APP用户
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateUser(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data,[
            'username'=>'required|min:5|max:15',
            'qq'=>'required|min:5|max:10',
        ],[
            'username.required'=>'请填写账号',
            'username.min'=>'账号格式为5位到15位字符',
            'username.max'=>'账号格式为5位到15位字符',
            'qq.required'=>'请填写qq',
            'qq.min'=>'qq格式不对',
            'qq.max'=>'qq格式不对',
        ]);
        $errors = $validator->errors()->first();
        if($errors){
            return $this->error($errors);
        }
        $param = [
            'avatar'=>'https://q1.qlogo.cn/g?b=qq&nk='.$data['qq'].'&s=640',
            'nickname'=>$this->getQqInfo((int) $data['qq'])['nickname'] ?? $data['username'],
            'qq'=>$data['qq'],
            'username'=>$data['username'],
        ];
        if(isset($data['password'])){
            $param['password'] = Hash::make($data['password']);
            $validator = Validator::make($data,[
                'password'=>'required|min:6|max:20',
            ],[
                'password.required'=>'请填写密码',
                'password.min'=>'密码格式为5位到15位字符',
                'password.max'=>'密码格式为5位到15位字符账号',
            ]);
            $errors = $validator->errors()->first();
            if($errors){
                return $this->error($errors);
            }
        }
        $user = AppUsersModel::query()->where(['username'=>$data['username']])->where('id','!=',$data['id'])->first();
        if($user){
            return $this->error('账号已存在');
        }
        $result = AppUsersModel::query()->where(['id'=>$data['id']])->update($param);
        if(!$result){
            return $this->error('修改失败');
        }
        return $this->success('修改成功');
    }

    /**
     * 删除APP用户
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUser(Request $request)
    {
        $data = $request->all();
        $result = AppUsersModel::query()->whereIn('id',$data['ids'])->delete();
        if(!$result){
            return $this->error('删除失败');
        }
        return $this->success('删除成功');
    }

    public function addUsersVip(Request $request)
    {
        $days = $request->get('days');
        $id = $request->get('id');
        $user = AppUsersModel::query()->where(['user_id'=>User::user_id(),'id'=>$id])->first();
        if($user['vip_start_time']){
            if(Carbon::parse($user['vip_end_time'])->getTimestamp() < time()){
                $param = [
                    'vip_end_time'=>Carbon::now()->addDays($days)
                ];
            }else{
                $param = [
                    'vip_end_time'=>Carbon::parse($user['vip_end_time'])->addDays($days)
                ];
            }
        }else{
            $param = [
                'vip_start_time'=>now(),
                'vip_end_time'=>Carbon::now()->addDays($days)
            ];
        }
        $result = AppUsersModel::query()->where(['id'=>$id])->update($param);
        if(!$result){
            return $this->error('操作失败');
        }
        return $this->success('操作成功');
    }
}
