<?php








use \Illuminate\Support\Facades\Route;
if(!strstr(request()->getPathInfo(),'/api') && !strstr(request()->getPathInfo(),'/storage') && !strstr(request()->getPathInfo(),'/tabbar')){
    Route::get('/{any}',function(){
        return view('index');
    })->where('any', '.*');
}
