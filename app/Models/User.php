<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'username',
        'password',
        'qq',
        'mail',
        'nickname',
        'phone',
        'vip',
        'vip_start_time',
        'vip_end_time',
        'avatar',
        'ip',
        'last_login_time',
        'last_login_ip',
        'short_url_nums',
        'admin'
    ];

    use HasFactory, Notifiable;

    protected $hidden = [
        'admin',
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
        return ['role' => 'user'];
    }

    static public function user_vip()
    {
        $user_id = auth()->id();
        $user = User::query()->where(['id'=>$user_id])->first();
        if($user['vip'] != 1 || strtotime($user['vip_end_time']) < time()){
            return false;
        }
        return true;
    }

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(\DateTimeInterface $date) : string
    {
        return $date->format('Y-m-d H:i:s');
    }

    static function user_id()
    {
        return auth()->id();
    }

    static function addVipDay(int $days)
    {
        $user = User::query()->where(['id'=>User::user_id()])->first();
        if($user['vip_start_time']){
            if(Carbon::parse($user['vip_end_time'])->getTimestamp() < time()){
                $param = [
                    'vip_start_time'=>now(),
                    'vip_end_time'=>Carbon::now()->addDays($days)
                ];
            }else{
                $param = [
                    'vip_start_time'=>now(),
                    'vip_end_time'=>Carbon::parse($user['vip_end_time'])->addDays($days)
                ];
            }
        }else{
            $param = [
                'vip_start_time'=>now(),
                'vip_end_time'=>Carbon::now()->addDays($days)
            ];
        }
        User::query()->where(['id'=>User::user_id()])->update($param);
    }
}
