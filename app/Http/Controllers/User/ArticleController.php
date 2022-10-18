<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\UsersController;
use App\Models\AppConfigModel;
use App\Models\ArticleMenuModel;
use App\Models\ArticleModel;
use App\Models\CommentModel;
use App\Models\SystemConfigModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Models\Activity;

class   ArticleController extends UsersController
{
    /**
     * 文章列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function articleList(Request $request)
    {
        $sort_field = $request->get('sortField') ?? 'id';
        $order = $request->get('sortOrder') == 'ascend' ? 'asc' : 'desc';
        $article = ArticleModel::query()
            ->where(['user_id'=>User::user_id()])
            ->where(function($query)use($request){
                if($request->get('keywords')){
                    $query->where('title','like','%'.$request->get('keywords').'%');
                }
            })
            ->orderBy($sort_field,$order)
            ->paginate($request->get('page_size') ?? 10);
        $article->each(function($item){
            $item['zhiding'] = $item['zhiding'] == 1 ? true : false;
        });
        return $this->success('',$article);
    }

    /**
     * 添加文章
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function createArticle(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data,
            [
                'content'=>'required',
            ],
            [
                'content.require'=>'请填写内容',
            ]);
        $author = User::query()->where(['id'=>User::user_id()])->value('nickname');
        if($data['type'] == 1){
            if($validator->errors()->first()){
                return $this->error($validator->errors()->first());
            }
            $param = [
                'user_id'=>User::user_id(),
                'author'=>$author,
                'title'=>$data['title'],
                'content'=>$data['content'],
                'cover'=>$data['cover'],
                'type'=>$data['type'],
                'open_type'=>1,
                'menu_id'=>$data['menu_id']
            ];
        }else{
            $param = [
                'user_id'=>User::user_id(),
                'author'=>$author,
                'link'=>$data['link'],
                'type'=>$data['type'],
                'open_type'=>$data['open_type'],
                'title'=>$data['title'],
                'cover'=>$data['cover'],
                'menu_id'=>$data['menu_id']
            ];
        }
        $result = ArticleModel::query()->create($param);
        if(!$result){
            return $this->error('添加失败');
        }
        activity()
            ->withProperties($param)
            ->tap(function(Activity $activity) {
                $activity->ip = \request()->ip();
                $activity->user_id = User::user_id();
            })
            ->log('添加文章');
        return $this->success('添加成功');
    }

    /**
     * 修改文章
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function updateArticle(Request $request)
    {
        $data = $request->all();
        $author = User::query()->where(['id'=>User::user_id()])->value('nickname');
        if($data['type'] == 1){
            $param = [
                'user_id'=>User::user_id(),
                'author'=>$author,
                'title'=>$data['title'],
                'html'=>$data['html'],
                'content'=>$data['content'],
                'cover'=>$data['cover'],
                'type'=>$data['type'],
                'open_type'=>1,
                'menu_id'=>$data['menu_id']
            ];
        }else{
            $param = [
                'user_id'=>User::user_id(),
                'author'=>$author,
                'link'=>$data['link'],
                'type'=>$data['type'],
                'open_type'=>$data['open_type'],
                'menu_id'=>$data['menu_id'],
                'title'=>$data['title'],
                'cover'=>$data['cover'],
            ];
        }
        $result = ArticleModel::query()->where(['id'=>$data['id']])->update($param);
        if(!$result){
            return $this->error('修改失败');
        }
        activity()
            ->withProperties($param)
            ->tap(function(Activity $activity) {
                $activity->ip = \request()->ip();
                $activity->user_id = User::user_id();
            })
            ->log('修改文章');
        return $this->success('修改成功');
    }

    /**
     * 删除文章
     * @param Request $request
     */
    public function deleteArticle(Request $request)
    {
        $data = $request->all();
        $result = ArticleModel::query()->whereIn('id',$data['ids'])->delete();
        if(!$result){
            return $this->error('删除失败');
        }
        activity()
            ->withProperties($data)
            ->tap(function(Activity $activity) {
                $activity->ip = \request()->ip();
                $activity->user_id = User::user_id();
            })
            ->log('删除文章');
        return $this->success('删除成功');
    }

    /**
     * 置顶文章
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function zhiding(Request $request)
    {
        $data = $request->all();
        $zhiding = $data['zhiding'] == true ? -1 : 1;
        $result = ArticleModel::query()->where(['user_id'=>User::user_id(),'id'=>$data['id']])->update(['zhiding'=>$zhiding]);
        if(!$result){
            return $this->error('修改失败');
        }
        activity()
            ->withProperties($data)
            ->tap(function(Activity $activity) {
                $activity->ip = \request()->ip();
                $activity->user_id = User::user_id();
            })
            ->log('置顶文章');
        return $this->success('修改成功');
    }

    /**
     * 菜单列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function menuList(Request $request)
    {
        $sort_field = $request->get('sortField') ?? 'id';
        $order = $request->get('sortOrder') == 'ascend' ? 'asc' : 'desc';
        $article = ArticleMenuModel::query()
            ->where(['user_id'=>User::user_id()])
            ->where(function($query)use($request){
                if($request->get('keywords')){
                    $query->where('name','like','%'.$request->get('keywords').'%');
                }
            })
            ->orderBy($sort_field,$order)
            ->paginate($request->get('page_size') ?? 10);
        $article->each(function($item){
            $item['index'] = $item['index'] == 1 ? true : false;
        });
        return $this->success('',$article);
    }

    /**
     * 添加菜单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createMenu(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'name'=>'required',
        ], [
            'name.require'=>'请填写菜单'
        ]);
        if($validator->errors()->first()){
            return $this->error($validator->errors()->first());
        }
        $param = [
            'user_id'=>User::user_id(),
            'name'=>$data['name'],
            'sort'=>$data['sort'],
            'is_vip'=>$data['is_vip'],
        ];
        $result = ArticleMenuModel::query()->create($param);
        if(!$result){
            return $this->error('添加失败');
        }
        activity()
            ->withProperties($param)
            ->tap(function(Activity $activity) {
                $activity->ip = \request()->ip();
                $activity->user_id = User::user_id();
            })
            ->log('添加文章菜单');
        return $this->success('添加成功');
    }

    /**
     * 修改菜单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMenu(Request $request)
    {
        $data = $request->all();
        $param = [
            'user_id'=>User::user_id(),
            'name'=>$data['name'],
            'sort'=>$data['sort'],
            'is_vip'=>$data['is_vip'],
        ];
        $result = ArticleMenuModel::query()->where(['id'=>$data['id']])->update($param);
        if(!$result){
            return $this->error('修改失败');
        }
        activity()
            ->withProperties($param)
            ->tap(function(Activity $activity) {
                $activity->ip = \request()->ip();
                $activity->user_id = User::user_id();
            })
            ->log('修改文章菜单');
        return $this->success('修改成功');
    }

    /**
     * 删除菜单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteMenu(Request $request)
    {
        $data = $request->all();
        $result = ArticleMenuModel::query()->whereIn('id',$data['ids'])->delete();
        if(!$result){
            return $this->error('删除失败');
        }
        activity()
            ->withProperties($data)
            ->tap(function(Activity $activity) {
                $activity->ip = \request()->ip();
                $activity->user_id = User::user_id();
            })
            ->log('删除文章菜单');
        return $this->success('删除成功');
    }

    /**
     * 首页菜单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexMenu(Request $request)
    {
        $data = $request->all();
        $index = $data['index'] == true ? -1 : 1;
        $result = ArticleMenuModel::query()->where(['user_id'=>User::user_id(),'id'=>$data['id']])->update(['index'=>$index]);
        if(!$result){
            return $this->error('修改失败');
        }
        activity()
            ->withProperties($data)
            ->tap(function(Activity $activity) {
                $activity->ip = \request()->ip();
                $activity->user_id = User::user_id();
            })
            ->log('首页文章菜单');
        return $this->success('修改成功');
    }

    /**
     * 选择菜单
     * @return \Illuminate\Http\JsonResponse
     */
    public function menuSelect()
    {
        $menu = ArticleMenuModel::query()->where(['user_id'=>User::user_id()])->get();
        return $this->success('',$menu);
    }

    /**
     * 文章链接
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function articleLink(Request $request)
    {
        $data = $request->all();
        $link = '/web/share/article/'.$data['id'];
        $share_link = AppConfigModel::query()->where(['user_id'=>User::user_id()])->value('share_link');
        if(!$share_link){
            $share_link = SystemConfigModel::query()->value('share_link');
        }
        $param = [
            'share_link'=>$share_link.$link
        ];
        return $this->success('',$param);
    }

    /**
     * 文章详情
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function articleDetail(Request $request)
    {
        $article = ArticleModel::query()->where(['user_id'=>User::user_id(),'id'=>$request->get('id')])->first();
        if($article['html']){
            $article['content'] = $article['html'];
        }
        return $this->success('',$article);
    }

    /**
     * 菜单详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function menuDetail(Request $request)
    {
        $menu = ArticleMenuModel::query()->where(['user_id'=>User::user_id(),'id'=>$request->get('id')])->first();
        return $this->success('',$menu);
    }

    /**
     * 评论列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function commentList(Request $request)
    {
        $sort_field = $request->get('sortField') ?? 'id';
        $order = $request->get('sortOrder') == 'ascend' ? 'asc' : 'desc';
        $comment = CommentModel::query()
            ->with('app_users')
            ->where(function($query)use($request){
                if($request->get('keywords')){
                    $query->where('content','like','%'.$request->get('keywords').'%');
                }
            })
            ->where(['type'=>2,'user_id'=>User::user_id()])->orderBy($sort_field,$order)->paginate($request->get('page_size') ?? 10);
        return $this->success('',$comment);
    }

    /**
     * 删除评论
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteComment(Request $request)
    {
        $data = $request->all();
        $result = CommentModel::query()->whereIn('id',$data['ids'])->delete();
        if(!$result){
            return $this->error('删除失败');
        }
        activity()
            ->withProperties($data)
            ->tap(function(Activity $activity) {
                $activity->ip = \request()->ip();
                $activity->user_id = User::user_id();
            })
            ->log('删除评论');
        return $this->success('删除成功');
    }
}
