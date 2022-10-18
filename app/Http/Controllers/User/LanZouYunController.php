<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\UsersController;
use App\Models\LanZouYunModel;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class LanZouYunController extends UsersController
{
    protected $api = 'https://up.woozooo.com/';

    protected $client;

    protected $user_id;

    public function __construct()
    {
        $this->client = new Client(['base_uri'=>$this->api,'timeout'=>100]);
        $this->user_id = auth()->id();
    }

    /**
     * 登录蓝奏云
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function login(Request $request)
    {
        $data = $request->all();
        $param = [
            'task'=>'3',
            'uid'=>$data['username'],
            'pwd'=>$data['password'],
            'setSessionId'=>'',
            'setSig'=>'',
            'setScene'=>'',
            'setToken'=>'',
            'formhash'=>'82cde7e5'
        ];
        $response = $this->client->request('post','mlogin.php',['form_params'=>$param,
            'headers'=>[
                'User-Agent'=>'Mozilla/5.0 (Linux; Android 11; Pixel 5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.91 Mobile Safari/537.36',
                'X-FORWARDED-FOR'=>getIp(),
                'CLIENT-IP'=>getIp(),
            ]
        ]);
        $result = $response->getBody()->getContents();
        $result = json_decode($result,true);
        if($result['zt'] != 1){
            return $this->error($result['info']);
        }
        $param = [
            'user_id'=>$this->user_id,
            'username'=>$data['username'],
            'password'=>$data['password'],
            'cookie'=>str_replace(' path=/; HttpOnly','',$response->getHeader('Set-Cookie')[0]).$response->getHeader('Set-Cookie')[1]
        ];
        $lanzouyun = LanZouYunModel::query()->where(['user_id'=>$this->user_id,'username'=>$data['username']])->first();
        if(!$lanzouyun){
            $many = LanZouYunModel::query()->where(['user_id'=>$this->user_id])->first();
            if($many){
                return $this->error('暂未开放多个账号');
            }
            $result = LanZouYunModel::query()->create($param);
        }else{
            $result = LanZouYunModel::query()->where(['id'=>$lanzouyun['id']])->update($param);
        }
        if(!$result){
            return $this->error('请稍等登录');
        }
        return $this->success('登陆成功');
    }

    /**
     * 蓝奏云账号
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function lanZouYunList(Request $request)
    {
        $sort_field = $request->get('sortField') ?? 'id';
        $order = $request->get('sortOrder') == 'ascend' ? 'asc' : 'desc';
        $list = LanZouYunModel::query()->where(['user_id'=>User::user_id()])->orderBy($sort_field,$order)->paginate($request->get('page_size') ?? 10);
        $list->each(function($item){
            $item['overdue'] = $item['is_vip'] == -1 ? '是' :'否';
            return $item;
        });
        return $this->success('',$list);
    }


    /**
     * 解除绑定
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeBind(Request $request)
    {
        $data = $request->all();
        $result = LanZouYunModel::query()->whereIn('id',$data['ids'])->delete();
        if(!$result){
            return $this->error('解绑失败');
        }
        return $this->success('解绑成功');
    }
}
