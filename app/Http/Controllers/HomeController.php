<?php

namespace App\Http\Controllers;






use App\Http\Controllers\User\Http;

class HomeController extends Controller
{
    const AES_KEY = "zxcasdnasdfdasda";
    const AES_IV  = "zofdgvszdfasdfas";

    public $home_user_id = 1;

    public function __construct()
    {
        $this->lasdngkbsdjikgbnsdhgjlhsjkdfghbjikdbfgjkji();
    }

    public function error($message = '',$data = [])
    {
        $data = ['code'=>-1,'message'=>$message,'result'=>$data];
        $data = $this->publicEncrypt(json_encode($data));
        return response()->json($data);
    }

    public function success($message = '',$data = [])
    {
        $data = ['code'=>0,'message'=>$message,'result'=>$data];
        $data = $this->publicEncrypt(json_encode($data));
        return response()->json($data);
    }

    public static function publicEncrypt($data = '')
    {
        $encrypted_data = openssl_encrypt($data, 'aes-128-cbc', self::AES_KEY, OPENSSL_RAW_DATA, self::AES_IV);
        return base64_encode($encrypted_data);
    }

    public static function privateDecrypt($encryptString = '')
    {
        $decrypted = openssl_decrypt(base64_decode($encryptString), 'aes-128-cbc', self::AES_KEY, OPENSSL_RAW_DATA, self::AES_IV);
        return $decrypted;
    }
}
