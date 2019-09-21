<?php
namespace app\admin\controller;

class Article extends Common
{
    public function news()
    {
        $keyword = input('keyword','','trim');
        $where=[];
        !empty($keyword) && $where[]=['title','like','%'.$keyword.'%'];
        $list = \app\common\model\News::where($where)->with('linkCate')->paginate();
        //dump($list);die;
        // 获取分页显示
        $page = $list->render();
        return view('news',[
            'keyword' => $keyword,
            'list' => $list,
            'page'=>$page
        ]);
    }

    //添加文章
    public function newsAdd()
    {

        $id  = $this->request->param('id');
        $model = new \app\common\model\News();

        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();//获取当前请求的参数
            $validate = new \app\common\validate\News();
            try{

                $model->actionAdd($php_input,$validate);//调用BaseModel中封装的添加/更新操作
            }catch (\Exception $e){
                return json(['code'=>0,'msg'=>$e->getMessage()]);
            }
            return json(['code'=>1,'msg'=>'操作成功']);
        }
        $model = $model->get($id);
        //获取分类
        $nav = \app\common\model\NewsCate::with(['linkChild'=>function($query){
            return $query->where(['status'=>1])->order('sort asc');
        }])->where(['status'=>1,'pid'=>0])->order('sort asc')->select();
        return view('newsAdd',[
            'model'=>$model,
            'nav'=>$nav,
        ]);
    }

    //删除文章
    public function newsDel()
    {
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\News();
        return $model->actionDel(['id'=>$id]);
    }

    public function newsCate()
    {
        $list = \app\common\model\NewsCate::with(['linkChild'=>function($query){
            return $query->order('sort asc');
        }])->where(['pid'=>0])->paginate();
        // 获取分页显示
        $page = $list->render();
        return view('newsCate',[
            'list' => $list,'page'=>$page
        ]);
    }

    //
    public function newsCateAdd()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\NewsCate();

        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();

            $validate = new \app\common\validate\NewsCate();
            try{

                $model->actionAdd($php_input,$validate);//调用BaseModel中封装的添加/更新操作
            }catch (\Exception $e){
                return json(['code'=>0,'msg'=>$e->getMessage()]);
            }
            return json(['code'=>1,'msg'=>'操作成功']);
        }

        $model = $model->get($id);
        $roles = \app\common\model\NewsCate::where(['status'=>1,'pid'=>0])->select();
        return view('newsCateAdd',[
            'model' => $model,
            'roles' => $roles,
        ]);

    }

    //删除数据
    public function newsCateDel()
    {
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\NewsCate();
        return $model->actionDel(['id'=>$id]);
    }

    //动态列表
    public function dynamic()
    {
        $keyword = input('keyword','','trim');
        $auth_state = input('auth_state',0,'intval');
        $where=[];
        if($auth_state==1){
            $where[] = ['is_auth','=',0];
        }elseif($auth_state==2){
            $where[] = ['is_auth','=',2];
        }
        !empty($keyword) && $where[]=['content','like','%'.$keyword.'%'];
        $list = \app\common\model\Dynamic::with(['linkUsers'])->where($where)->order('update_time desc')->paginate();
        //dump($list);die;
        // 获取分页显示
        $page = $list->render();
        return view('dynamic',[
            'auth_state' => $auth_state,
            'keyword' => $keyword,
            'list' => $list,
            'page'=>$page
        ]);
    }


    //
    public function dynamicAdd()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\Dynamic();

        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            if(!$id){
                $pointer_user_id = [1,2,3,6,8,9];
                shuffle($pointer_user_id);
                $php_input['uid'] = $pointer_user_id[0];
            }

            $php_input['file'] = empty($php_input['file'])?'':implode(',',$php_input['file']);
//            dump($php_input);exit;
            $validate = new \app\common\validate\Dynamic();
            try{
                $model->actionAdd($php_input,$validate);//调用BaseModel中封装的添加/更新操作
            }catch (\Exception $e){
                return json(['code'=>0,'msg'=>$e->getMessage()]);
            }
            return json(['code'=>0,'msg'=>'操作成功']);
        }

        $model = $model->get($id);
        return view('dynamicAdd',[
            'model' => $model,
        ]);

    }


    //删除数据
    public function dynamicDel()
    {
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\Dynamic();
        return $model->actionDel(['id'=>$id]);
    }


    public function dynamicDetail()
    {
        $id  = $this->request->param('id');
        $model = \app\common\model\Dynamic::with(['link_users','linkCommentCount'])->get($id);

        return view('dynamicDetail',[
            'model'=>$model,
        ]);
    }

    //活动列表
    public function activity()
    {
        $keyword = input('keyword','','trim');
        $auth_state = input('auth_state',0,'intval');
        $where=[];
        if($auth_state==1){
            $where[] = ['is_auth','=',0];
        }elseif($auth_state==2){
            $where[] = ['is_auth','=',2];
        }
        !empty($keyword) && $where[]=['content','like','%'.$keyword.'%'];
        $list = \app\common\model\Activity::with(['linkUsers'])->where($where)->order('id desc')->paginate();
        //dump($list);die;
        // 获取分页显示
        $page = $list->render();
        return view('activity',[
            'auth_state' => $auth_state,
            'keyword' => $keyword,
            'list' => $list,
            'page'=>$page
        ]);
    }


    //添加活动
    public function activityAdd()
    {
        $id  = $this->request->param('id');
        $model = new \app\common\model\Activity();
        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();//获取当前请求的参数
            if(!$id){
                $pointer_user_id = [1,2,3,6,8,9];
                shuffle($pointer_user_id);
                $php_input['uid'] = $pointer_user_id[0];
            }

            !empty($php_input['user_num']) && $php_input['user_num'] = implode(',',$php_input['user_num']);
            $validate = new \app\common\validate\Activity();
            $validate->scene('api_release');
            try{
                $model->actionAdd($php_input,$validate);//调用BaseModel中封装的添加/更新操作
            }catch (\Exception $e){
                return json(['code'=>0,'msg'=>$e->getMessage()]);
            }
            return json(['code'=>1,'msg'=>'操作成功']);
        }
        $model = $model->get($id);
        return view('activityAdd',[
            'model'=>$model,
        ]);
    }


    //删除数据
    public function activityDel()
    {
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\Activity();
        return $model->actionDel(['id'=>$id]);
    }


    //乐库
    public function music()
    {
        $keyword = input('keyword','','trim');
        $where=[];
        !empty($keyword) && $where[]=['name','like','%'.$keyword.'%'];
        $list = \app\common\model\Music::where($where)->paginate();
        //dump($list);die;
        // 获取分页显示
        $page = $list->render();
        return view('music',[
            'keyword' => $keyword,
            'list' => $list,
            'page'=>$page
        ]);
    }

    //添加乐库
    public function musicAdd()
    {
        $id  = $this->request->param('id');
        $model = new \app\common\model\Music();
        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();//获取当前请求的参数
            $validate = new \app\common\validate\Music();
            try{

                $model->actionAdd($php_input,$validate);//调用BaseModel中封装的添加/更新操作
            }catch (\Exception $e){
                return json(['code'=>0,'msg'=>$e->getMessage()]);
            }
            return json(['code'=>1,'msg'=>'操作成功']);
        }
        $model = $model->get($id);

        return view('musicAdd',[
            'model'=>$model,
        ]);
    }


    //删除数据
    public function musicDel()
    {
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\Music();
        return $model->actionDel(['id'=>$id]);
    }

    public function video()
    {
        $keyword = input('keyword','','trim');
        $auth_state = input('auth_state',0,'intval');
        $where=[];
        if($auth_state==1){
            $where[] = ['is_auth','=',0];
        }elseif($auth_state==2){
            $where[] = ['is_auth','=',2];
        }
        !empty($keyword) && $where[]=['title','like','%'.$keyword.'%'];
        $list = \app\common\model\Video::where($where)->order('status asc,update_time desc')->paginate();
        //dump($list);die;
        // 获取分页显示
        $page = $list->render();
        return view('video',[
            'auth_state' => $auth_state,
            'keyword' => $keyword,
            'list' => $list,
            'page'=>$page
        ]);
    }

    public function videoAdd()
    {
        $id  = $this->request->param('id');
        $model = new \app\common\model\Video();
        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();//获取当前请求的参数
            $validate = new \app\common\validate\Video();
            try{

                $model->actionAdd($php_input,$validate);//调用BaseModel中封装的添加/更新操作
            }catch (\Exception $e){
                return json(['code'=>0,'msg'=>$e->getMessage()]);
            }
            return json(['code'=>1,'msg'=>'操作成功']);
        }
        $model = $model->get($id);

        return view('videoAdd',[
            'model'=>$model,
        ]);
    }

    public function videoDetail()
    {
        $id  = $this->request->param('id');
        $model = \app\common\model\Video::with(['link_users','linkCommentCount'])->get($id);


        return view('videoDetail',[
            'model'=>$model,
        ]);
    }

    //删除数据
    public function videoDel()
    {
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\Video();
        return $model->actionDel(['id'=>$id]);
    }

    public function welfare()
    {
        $keyword = input('keyword','','trim');
        $where=[];
        !empty($keyword) && $where[]=['title','like','%'.$keyword.'%'];
        $list = \app\common\model\Welfare::where($where)->order('update_time desc')->paginate();
        //dump($list);die;
        // 获取分页显示
        $page = $list->render();
        return view('welfare',[
            'keyword' => $keyword,
            'list' => $list,
            'page'=>$page
        ]);
    }

    public function welfareAdd()
    {
        $id  = $this->request->param('id');
        $model = new \app\common\model\Welfare();
        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();//获取当前请求的参数
            $validate = new \app\common\validate\Welfare();
            try{

                $model->actionAdd($php_input,$validate);//调用BaseModel中封装的添加/更新操作
            }catch (\Exception $e){
                return json(['code'=>0,'msg'=>$e->getMessage()]);
            }
            return json(['code'=>1,'msg'=>'操作成功']);
        }
        $model = $model->get($id);

        return view('welfareAdd',[
            'model'=>$model,
        ]);
    }

    //删除数据
    public function welfareDel()
    {
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\Welfare();
        return $model->actionDel(['id'=>$id]);
    }

    //查看评论
    public function showComments()
    {
        $id = input('id',0,'intval');
        $type = input('type');
        if($type=='video'){
            //视频
            $class = \app\common\model\VideoComment::class;
            $where[] =['vid','=',$id];

        }elseif($type=='dynamic'){
            //动态
            $class = \app\common\model\DyComment::class;
            $where[] =['dy_id','=',$id];

        }else{
            return $this->_resData(1,'获取成功',['list'=>[],'total_page'=>0]);
        }
        //评论信息
        $list = [];
        $info = $class::with(['linkUsers','linkToUsers'])->where($where)->order('id desc')->paginate()->each(function($item,$index)use(&$list){
            $child_comment = [];
            foreach ($item['link_child'] as $vo){
                array_push($child_comment, $vo->structInfo());
            }
            array_push($list,$item->structInfo($child_comment));
        });

        return $this->_resData(1,'获取成功',['list'=>$list,'total_page'=>$info->lastPage()]);
    }
    //删除评论
    public function delComments()
    {
        $id = input('id',0,'intval');//对应信息id
        $cid = input('cid',0,'intval');//评论id
        $type = input('type');
        if($type=='video'){
            //视频
            $class = \app\common\model\VideoComment::class;
        }elseif($type=='dynamic'){
            //动态
            $class = \app\common\model\DyComment::class;
        }else{
            return $this->_resData(0,'类型异常');
        }
//        \app\common\model\Dynamic::commentDel()
        try{
            $class::commentDel(null,['id'=>$cid]);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }
        return $this->_resData(1,'删除成功');
    }


    //审核动作
    public function auth()
    {
        $type = input('type');
        $id = input('id',0,'intval');
        $state = input('state',2,'intval');
        $content = input('content','','trim');
        if($type == 'video'){
            $class = \app\common\model\Video::class;
        }elseif($type == 'dynamic'){
            $class = \app\common\model\Dynamic::class;
        }elseif($type == 'activity'){
            $class = \app\common\model\Activity::class;
        }else{
            return $this->_resData(0,'审核类型异常');
        }
        try{
            $class::proAuth($id,$state,$content);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }
        return $this->_resData(1,'操作成功');
    }
}
