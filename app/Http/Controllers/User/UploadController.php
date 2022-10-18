<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\UsersController;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;

class UploadController extends UsersController
{
    /**
     * 上传文件
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $file = $request->file('file');
        $array = [
            'png',
            'jpg',
            'jpeg'
        ];
        if(!in_array($file->extension(),$array)){
            return $this->error('上传失败');
        }
        $path = '/upload/'.md5(microtime()).'.'.$file->getClientOriginalExtension();
        Storage::disk('public')->put($path,$file->getContent());
        $link = Storage::disk('public')->url($path);
        return $this->success('上传成功',['link'=>$link]);
    }
}
