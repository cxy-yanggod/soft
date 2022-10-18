<?php

namespace App\Http\Controllers;






class UsersController extends Controller
{
    const AES_KEY = "zxcasdnasdfdasda";
    const AES_IV  = "zofdgvszdfasdfas";

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

    public  function publicEncrypt($data = '')
    {
        $encrypted_data = openssl_encrypt($data, 'aes-128-cbc', self::AES_KEY, OPENSSL_RAW_DATA, self::AES_IV);
        return base64_encode($encrypted_data);
    }

    public function privateDecrypt($encryptString = '')
    {
        $decrypted = openssl_decrypt(base64_decode($encryptString), 'aes-128-cbc', self::AES_KEY, OPENSSL_RAW_DATA, self::AES_IV);
        return $decrypted;
    }
}
