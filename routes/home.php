<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use \App\Http\Controllers\User\AuthController;
use \App\Http\Controllers\User\UserController;
use \App\Http\Controllers\User\AppConfigController;
use \App\Http\Controllers\User\ArticleController;
use \App\Http\Controllers\User\SoftLinkController;
use \App\Http\Controllers\User\FeedBackController;
use \App\Http\Controllers\User\LanZouYunController;
use \App\Http\Controllers\User\SoftMenuController;
use \App\Http\Controllers\User\SoftController;
use \App\Http\Controllers\User\SystemController;
use \App\Http\Controllers\User\CarMyController;
use \App\Http\Controllers\User\BannerController;
use \App\Http\Controllers\User\ToolsController;

Route::group(['prefix'=>'user'],function(){
    Route::post('login',[AuthController::class,'login']);
    Route::post('logout',[AuthController::class,'logout']);
    Route::post('get_sms_captcha',[AuthController::class,'getSmsCaptcha']);
    Route::post('register',[AuthController::class,'register']);
    Route::get('system_agreement',[AuthController::class,'systemAgreement']);
    Route::get('get_gee_test',[AuthController::class,'getGeeTest']);
});

Route::group(['prefix'=>'workplace'],function(){
    Route::get('/',[SystemController::class,'workplace']);
    Route::get('workplace_data',[SystemController::class,'workplaceData']);
    Route::get('analysis',[SystemController::class,'analysis']);
});
Route::group(['prefix'=>'user'],function(){
    Route::get('info',[UserController::class,'userInfo']);
});
Route::group(['middleware'=>['jwt.auth']],function(){
    Route::group(['prefix'=>'user'],function(){
        Route::post('update_info',[UserController::class,'updateUserInfo']);
        Route::get('user_list',[UserController::class,'userList']);
        Route::post('user_status',[UserController::class,'userStatus']);
        Route::get('user_detail',[UserController::class,'userDetail']);
        Route::post('create_user',[UserController::class,'createUser']);
        Route::post('update_user',[UserController::class,'updateUser']);
        Route::post('delete_user',[UserController::class,'deleteUser']);
        Route::post('add_users_vip',[UserController::class,'addUsersVip']);
    });
    Route::group(['prefix'=>'carmy'],function(){
        Route::get('carmy_list',[CarMyController::class,'carMyList']);
        Route::post('create_carmy',[CarMyController::class,'createCarMy']);
        Route::post('delete_carmy',[CarMyController::class,'deleteCarmy']);
    });
    Route::group(['prefix'=>'tools'],function(){
        Route::get('tools_plate_list',[ToolsController::class,'toolsPlateList']);
        Route::post('create_tools_plate',[ToolsController::class,'createToolsPlate']);
        Route::get('tools_plate_detail',[ToolsController::class,'toolsPlateDetail']);
        Route::post('update_tools_plate',[ToolsController::class,'updateToolsPlate']);
        Route::get('tools_list',[ToolsController::class,'toolsList']);
        Route::post('create_tools',[ToolsController::class,'createTools']);
        Route::get('tools_detail',[ToolsController::class,'toolsDetail']);
        Route::post('update_tools',[ToolsController::class,'updateTools']);
        Route::post('delete_tools',[ToolsController::class,'deleteTools']);
    });
    Route::group([
        'prefix'=>'app_config',
    ],function(){
        Route::get('/',[AppConfigController::class,'appConfig']);
        Route::post('save',[AppConfigController::class,'appConfigSave']);
    });
    Route::group([
        'prefix'=>'banner',
        'middleware'=>'jwt.auth'
    ],function(){
        Route::get('banner_list',[BannerController::class,'bannerList']);
        Route::post('create_banner',[BannerController::class,'createBanner']);
        Route::post('update_banner',[BannerController::class,'updateBanner']);
        Route::post('delete_banner',[BannerController::class,'deleteBanner']);
        Route::get('banner_detail',[BannerController::class,'bannerDetail']);
    });
    Route::post('app_update',[AppConfigController::class,'appUpdate']);
    Route::group([
        'prefix'=>'article',
        'middleware'=>'jwt.auth'
    ],function(){
        Route::get('/',[ArticleController::class,'articleList']);
        Route::post('/',[ArticleController::class,'createArticle']);
        Route::put('/',[ArticleController::class,'updateArticle']);
        Route::delete('/',[ArticleController::class,'deleteArticle']);
        Route::get('detail',[ArticleController::class,'articleDetail']);
        Route::post('zhiding',[ArticleController::class,'zhiding']);
        Route::get('menu_list',[ArticleController::class,'menuSelect']);
        Route::get('article_link',[ArticleController::class,'articleLink']);
        Route::get('comment_list',[ArticleController::class,'commentList']);
        Route::post('delete_comment',[ArticleController::class,'deleteComment']);
        Route::group([
            'prefix'=>'menu',
        ],function(){
            Route::get('/',[ArticleController::class,'menuList']);
            Route::post('/',[ArticleController::class,'createMenu']);
            Route::put('/',[ArticleController::class,'updateMenu']);
            Route::delete('/',[ArticleController::class,'deleteMenu']);
            Route::post('index',[ArticleController::class,'indexMenu']);
            Route::get('detail',[ArticleController::class,'menuDetail']);
        });
    });
    Route::group([
        'prefix'=>'soft',
        'middleware'=>'jwt.auth'
    ],function(){
        Route::get('/',[SoftController::class,'softList']);
        Route::post('/',[SoftController::class,'createSoft']);
        Route::put('/',[SoftController::class,'updateSoft']);
        Route::delete('/',[SoftController::class,'deleteSoft']);
        Route::get('detail',[SoftController::class,'softDetail']);
        Route::get('comment_list',[SoftController::class,'commentList']);
        Route::post('delete_comment',[SoftController::class,'deleteComment']);
        Route::group([
            'prefix'=>'link',
        ],function(){
            Route::get('/',[SoftLinkController::class,'softLinkList']);
            Route::put('/',[SoftLinkController::class,'updateSoftLink']);
            Route::delete('/',[SoftLinkController::class,'deleteSoftLink']);
            Route::get('soft_menu',[SoftLinkController::class,'softMenu']);
        });
        Route::group([
            'prefix'=>'menu',
        ],function(){
            Route::get('/',[SoftMenuController::class,'menuList']);
            Route::post('/',[SoftMenuController::class,'createMenu']);
            Route::put('/',[SoftMenuController::class,'updateMenu']);
            Route::delete('/',[SoftMenuController::class,'deleteMenu']);
            Route::post('index',[SoftMenuController::class,'indexMenu']);
            Route::get('detail',[SoftMenuController::class,'menuDetail']);
        });
    });

    Route::group([
        'prefix'=>'feedback',
        'middleware'=>'jwt.auth'
    ],function(){
        Route::get('/',[FeedBackController::class,'feedbackList']);
        Route::post('/',[FeedBackController::class,'replyFeedback']);
        Route::delete('/',[FeedBackController::class,'deleteFeedback']);
    });

    Route::group([
        'middleware'=>'jwt.auth'
    ],function(){
        Route::any('upload',[\App\Http\Controllers\User\UploadController::class,'upload']);
    });

    Route::group([
        'middleware'=>'jwt.auth'
    ],function(){
        Route::group(['prefix'=>'lanzouyun'],function(){
            Route::post('login',[LanZouYunController::class,'login']);
            Route::get('list',[LanZouYunController::class,'lanZouYunList']);
            Route::delete('remove_bind',[LanZouYunController::class,'removeBind']);
        });
    });
});
