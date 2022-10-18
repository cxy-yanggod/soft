<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\UsersController;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends UsersController
{
    /**
     * 用户登录
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $data = $request->all();
        $ip = $request->ip();
        $user = User::query()->where(['username'=>$data['username']])->first();
        if(!$user){
            return $this->error('账号密码错误');
        }
        if(!Hash::check($data['password'],$user['password'])){
            return $this->error('密码错误');
        }
        if (!$token = JWTAuth::fromUser($user)) {
            return $this->error('账号密码错误');
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
        $ip = \request()->ip();
        activity()
            ->tap(function(Activity $activity) use($ip) {
                $activity->ip = $ip;
                $activity->user_id = User::user_id();
            })
            ->log('退出登录');
        auth('api')->logout();
        return $this->success('logout Successfully');
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
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ];
        return $this->success('',$data);
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

