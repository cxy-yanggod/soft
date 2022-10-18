<?php

namespace App\Http\Controllers\Home\v3;

use App\Http\Controllers\HomeController;
use App\Models\AppConfigModel;
use App\Models\ArticleMenuModel;
use App\Models\ArticleModel;
use App\Models\CollectionModel;
use App\Models\CommentFabulousModel;
use App\Models\CommentModel;
use DfaFilter\SensitiveHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ArticleController extends HomeController
{
    /**
     * 文章菜单
     * @return \Illuminate\Http\JsonResponse
     */
    public function articleMenu()
    {
        $menu = ArticleMenuModel::query()->where(['user_id'=>$this->home_user_id])
            ->orderBy('sort','desc')
            ->select('id', 'name','is_vip','password')
            ->get();
        return $this->success('',$menu);
    }

    /**
     * 文章列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function articleList(Request $request)
    {
        $menu_id = $request->get('menu_id');
        $keywords = $request->get('keywords');
        $article = ArticleModel::query()->with(['article_menu'=>function($q){
            $q->select('id','name');
        }]);
        if($keywords){
            $article = $article->where(['user_id'=>$this->home_user_id])->where('title','like','%'.$keywords.'%');
        }else{
            $article = $article->where(['user_id'=>$this->home_user_id,'menu_id'=>$menu_id]);
        }
        $article = $article->select('id','title','menu_id','type','open_type','cover','zhiding','link','created_at')->orderByRaw(DB::raw("CASE WHEN zhiding = 1 then 1 else 2 end"))->orderBy('id','desc')->paginate(15);
        $article->each(function($item){
            $item['time'] = mdate($item['created_at']->timestamp);
        });
        return $this->success('',$article);
    }

    /**
     * 文章详情
     * @param $soft_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function articleDetail($article_id)
    {
        $soft = ArticleModel::query()->where(['id'=>$article_id])->first();
        $soft->increment('nums');
        if(!$soft){
            return $this->error('暂无文章');
        }
        if($soft['html']){
            $soft['content'] = $this->htmlToArray($soft['html']);
        }else{
            $soft['content'] = $this->htmlToArray($soft['content']);
        }
        $soft['time'] = mdate($soft['created_at']->timestamp);
        return $this->success('',$soft);
    }

    /**
     * 评论列表
     * @param $soft_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function commentList($soft_id)
    {
        $result = CommentModel::query()->with(['app_users'=>function($query){
            $query->select('avatar','id','nickname');
        },'reply.app_users'])
            ->where(['user_id'=>$this->home_user_id,'content_id'=>$soft_id,'type'=>2,'parent_id'=>0])
            ->select('id','user_id', 'app_users_id', 'type', 'parent_id', 'fabulous', 'content', 'created_at')
            ->orderBy('id','desc')
            ->paginate(15);
        $result->each(function($item)use($soft_id){
            $item['time'] = mdate($item['created_at']->timestamp);
            $item['is_fabulous'] = false;
            $item['is_collection'] = false;
            if(auth('app')->id()){
                $fabulous = CommentFabulousModel::query()->where(['comment_id'=>$item['id'],'app_users_id'=>auth('app')->id()])->first();
                $item['is_fabulous'] = $fabulous ? true : false;
                $collection = CollectionModel::query()->where(['content_id'=>$soft_id,'app_users_id'=>auth('app')->id()])->first();
                $item['is_collection'] = $collection ? true : false;
            }
        });
        return $this->success('',$result);
    }

    /**
     * 文章评论
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function comment(Request $request)
    {
        $data = $request->all();
        $data['app_users_id'] = auth('app')->id();
        $data['user_id'] = $this->home_user_id;
        $data['type'] = 2;
        $validator = Validator::make($data,[
            'content'=>'required|min:5|max:400',
        ],[
            'content.required'=>'请填写内容',
            'content.min'=>'内容太少了',
            'content.max'=>'内容太多了',
        ]);
        $errors = $validator->errors()->first();
        if($errors){
            return $this->error($errors);
        }
        $result = CommentModel::query()->create($data);
        if(!$result){
            return $this->error('评论失败');
        }
        return $this->success('评论成功');
    }

    /**
     * 回复评论
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function replyComment(Request $request)
    {
        $data = $request->all();
        $data['app_users_id'] = auth('app')->id();
        $data['user_id'] = $this->home_user_id;
        $data['type'] = 2;
        $data['parent_id'] = $data['comment_id'];
        $validator = Validator::make($data,[
            'content'=>'required|min:5|max:400',
        ],[
            'content.required'=>'请填写内容',
            'content.min'=>'内容太少了',
            'content.max'=>'内容太多了',
        ]);
        $errors = $validator->errors()->first();
        if($errors){
            return $this->error($errors);
        }
        $result = CommentModel::query()->create($data);
        if(!$result){
            return $this->error('回复失败');
        }
        return $this->success('回复成功');
    }

    /**
     * 点赞评论
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fabulousComment(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['user_id'] = $this->home_user_id;
            $data['app_users_id'] = auth('app')->id();
            $data['type'] = 2;
            $fabulous = CommentFabulousModel::query()->where(['type'=>2,'user_id'=>$data['user_id'],'app_users_id'=>$data['app_users_id'],'comment_id'=>$data['comment_id']])->first();
            if($fabulous){
                CommentModel::query()->where(['id'=>$data['comment_id']])->decrement('fabulous');
                CommentFabulousModel::query()->where(['id'=>$fabulous['id']])->delete();
            }else{
                CommentModel::query()->where(['id'=>$data['comment_id']])->increment('fabulous');
                CommentFabulousModel::query()->create($data);
            }
            DB::commit();
            return $this->success('操作成功');
        }catch (\Exception $e){
            DB::rollBack();
            return $this->error('操作失败');
        }
    }

    /**
     * 收藏软件
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function collection(Request $request)
    {
        $data = $request->all();
        $data['user_id'] = $this->home_user_id;
        $data['app_users_id'] = auth('app')->id();
        $data['type'] = 2;
        $collection = CollectionModel::query()->where(['type'=>2,'user_id'=>$data['user_id'],'app_users_id'=>$data['app_users_id'],'content_id'=>$data['content_id']])->first();
        if($collection){
            CollectionModel::query()->where(['id'=>$collection['id']])->delete();
            return $this->success('操作成功',['collection'=>false]);
        }else{
            CollectionModel::query()->create($data);
            return $this->success('操作成功',['collection'=>true]);
        }
    }

    /**
     * 转换文章
     * @param $content
     * @return array
     */
    private function htmlToArray($content){
        // 处理链接接
        $content = preg_replace('/<a href="(.*)" .*>.*<\/a>/Uis', '--pgSuperSpliteGraceUI--link::pgSuperSplite::$1--pgSuperSpliteGraceUI--', $content);
        // 处理图片
        $content = preg_replace('/<img src="(.*)".*\/>/Uis', '--pgSuperSpliteGraceUI--img::pgSuperSplite::$1--pgSuperSpliteGraceUI--', $content);
        // 处理 pre
        $content = preg_replace('/<pre.*>(.*)<\/pre>/Uis', '--pgSuperSpliteGraceUI--pre::pgSuperSplite::$1--pgSuperSpliteGraceUI--', $content);
        // 处理 strong
        $content = preg_replace('/<strong.*>(.*)<\/strong>/Uis', '--pgSuperSpliteGraceUI--strong::pgSuperSplite::$1--pgSuperSpliteGraceUI--', $content);
        // 处理 strong
        $content = preg_replace('/<br.*\/>/Uis', PHP_EOL, $content);
        // 处理引用
        $content = preg_replace('/<blockquote>(.*)<\/blockquote>/Uis', '--pgSuperSpliteGraceUI--quote::pgSuperSplite::$1--pgSuperSpliteGraceUI--', $content);
        // 处理hr
        $content = preg_replace('/<hr(.*)\/>/Uis', '--pgSuperSpliteGraceUI--spline::pgSuperSplite::...--pgSuperSpliteGraceUI--', $content);
        // 处理 video
        $content = preg_replace('/<video src="(.*)".*\/video>/Uis', '--pgSuperSpliteGraceUI--video::pgSuperSplite::$1--pgSuperSpliteGraceUI--', $content);
        // 去除标签
        $content = strip_tags($content);
        // 处理 空白
        $content = preg_replace('/(\t)/Uis', '', $content);
        // 处理空格
        $content = preg_replace('/&nbsp;/Uis', ' ', $content);
        // 处理特殊字符
        $content = preg_replace('/&.*;/Uis', '', $content);

        // 拆分数组
        $content = explode('--pgSuperSpliteGraceUI--', $content);
        // 检查数组并处理空白
        // 记录需要删除的项目
        $contentArray = array();
        foreach($content as $k => $item){
            // 如果是空白内容删除它
            $res = str_replace(array("\r\n", "\r", "\n", ''), '', $item);
            if($res != ''){
                // 拆分子项目
                $itemArr = explode('::pgSuperSplite::', $item);
                // 文本
                if(count($itemArr) < 2){
                    $contentArray[] = array('type' => 'txt', 'content' => trim($itemArr[0]));
                }else{
                    $contentArray[] = array('type' => $itemArr[0], 'content' => trim($itemArr[1]));
                }
            }
        }
        return $contentArray;
    }
}
