<?php








use Illuminate\Support\Facades\Route;


use \App\Http\Controllers\Home\v3\AuthController;
use \App\Http\Controllers\Home\v3\UserController;
use \App\Http\Controllers\Home\v3\SoftController;
use \App\Http\Controllers\Home\v3\IndexController;
use \App\Http\Controllers\Home\v3\ArticleController;
use \App\Http\Controllers\Home\v3\ToolsController;

Route::group(['prefix'=>'v3'],function(){
    Route::group(['prefix'=>'auth'],function(){
        Route::post('login',[AuthController::class,'login']);
        Route::post('logout',[AuthController::class,'logout']);
        Route::post('register',[AuthController::class,'register']);
    });
    Route::group(['middleware'=>'jwt.role:app'],function(){
        Route::group(['prefix'=>'user'],function(){
            Route::get('info',[UserController::class,'userInfo']);
            Route::post('use_carmy',[UserController::class,'useCarMy']);
            Route::get('my_comment_list',[UserController::class,'myCommentList']);
            Route::get('my_collection',[UserController::class,'myCollection']);
            Route::post('feedback',[UserController::class,'feedback']);
            Route::get('reply_feedback_list',[UserController::class,'replyFeedbackList']);
        });
        Route::group(['prefix'=>'soft'],function(){
            Route::post('comment',[SoftController::class,'comment']);
            Route::post('reply_comment',[SoftController::class,'replyComment']);
            Route::post('fabulous_comment',[SoftController::class,'fabulousComment']);
            Route::post('collection',[SoftController::class,'collection']);
        });
        Route::group(['prefix'=>'article'],function(){
            Route::post('comment',[ArticleController::class,'comment']);
            Route::post('reply_comment',[ArticleController::class,'replyComment']);
            Route::post('fabulous_comment',[ArticleController::class,'fabulousComment']);
            Route::post('collection',[ArticleController::class,'collection']);
        });
    });
    Route::group(['prefix'=>'soft'],function(){
        Route::get('comment_list/{soft_id}',[SoftController::class,'commentList']);
        Route::get('recommend/{soft_id}',[SoftController::class,'recommend']);
    });
    Route::group(['prefix'=>'article'],function(){
        Route::get('comment_list/{soft_id}',[ArticleController::class,'commentList']);
        Route::get('recommend/{soft_id}',[ArticleController::class,'recommend']);
    });
    Route::group(['prefix'=>'tools'],function(){
        Route::get('tools_plate',[ToolsController::class,'toolsPlateList']);
        Route::get('tools',[ToolsController::class,'toolsList']);
    });
    Route::group(['prefix'=>'home'],function(){
        Route::get('soft_menu',[SoftController::class,'softMenu']);
        Route::get('soft_menu_detail/{menu_id}',[SoftController::class,'softMenuDetail']);
        Route::get('soft_list',[SoftController::class,'softList']);
        Route::get('soft_detail/{soft_id}',[SoftController::class,'softDetail']);
        Route::get('banner',[IndexController::class,'banner']);
        Route::get('article_menu',[ArticleController::class,'articleMenu']);
        Route::get('article_list',[ArticleController::class,'articleList']);
        Route::get('article_detail/{article_id}',[ArticleController::class,'articleDetail']);
        Route::get('app_config',[IndexController::class,'appConfig']);
        Route::get('hots_soft',[IndexController::class,'hotsSoft']);
        Route::post('update',[IndexController::class,'update']);
        Route::get('statistics',[IndexController::class,'statistics']);
        Route::get('check_user_vip',[IndexController::class,'checkUserVip']);
    });
});
