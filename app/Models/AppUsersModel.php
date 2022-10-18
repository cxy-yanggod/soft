<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class AppUsersModel extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;


    public $table = 'app_users';

    public $fillable = [
        'user_id',
        'username',
        'password',
        'qq',
        'nickname',
        'avatar',
        'status',
        'vip_start_time',
        'vip_end_time',
        'last_login_time',
        'last_login_ip'
    ];

    protected $hidden = [
        'password'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return ['role'=>'app'];
    }

    protected function serializeDate(\DateTimeInterface $date) : string
    {
        return $date->format('Y-m-d H:i:s');
    }

    static function addVipDay(int $days)
    {
        $user = AppUsersModel::query()->where(['id'=>auth('app')->id()])->first();
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
        AppUsersModel::query()->where(['id'=>auth('app')->id()])->update($param);
    }
}
