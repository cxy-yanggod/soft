<?php

namespace App\Http\Controllers\Home\v3;

use App\Http\Controllers\HomeController;
use App\Models\AppUsersModel;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends HomeController
{
    /**
     * 用户登录
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $data = $request->all();
        $ip = $request->ip();
        $user = AppUsersModel::query()->where(['username'=>$data['username'],'user_id'=>$this->home_user_id])->first();
        if(!$user){
            return $this->error('账号密码错误');
        }
        if (!$token = JWTAuth::fromUser($user)) {
            return $this->error('账号密码错误');
        }
        if($user['status'] != 1){
            return $this->error('用户被禁用');
        }
        $data = [
            'last_login_time'=>now(),
            'last_login_ip'=>$ip,
        ];
        $user->update($data);
        return $this->respondWithToken($token);
    }

    /**
     * 退出成功
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('app')->logout();
        return $this->success('退出成功');
    }

    /**
     * 返回用户token
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $data = [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('app')->factory()->getTTL() * 60,
        ];
        return $this->success('登陆成功',$data);
    }

    /**
     * 用户注册
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function register(Request $request)
    {
       $data = $request->all();
       $validator = Validator::make($data,[
           'username'=>'required|min:5|max:15',
           'password'=>'required|same:re_password|min:6|max:20',
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
       $user = AppUsersModel::query()->where(['user_id'=>$this->home_user_id,'username'=>$data['username']])->first();
       if($user){
           return $this->error('账号已存在');
       }
       $data['avatar'] = 'https://q1.qlogo.cn/g?b=qq&nk='.$data['qq'].'&s=640';
       $data['password'] = Hash::make($data['password']);
       $data['user_id'] = $this->home_user_id;
       $data['nickname'] = $this->getQqInfo((int) $data['qq'])['nickname'] ?? $data['username'];
       $result = AppUsersModel::query()->create($data);
       if(!$result){
           return $this->error('注册失败');
       }
       return $this->success('注册成功');
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
}
