<?php
namespace app\api\controller;

class Info extends Common
{
    protected $need_login=true;
    protected $ignore_action = ['dynamic','dydetail','dycomlist','activity','video','videodetail','videocomlist','welfare','welfareDetail'];

    /**
     * @var \app\common\model\Users
     *
     * */
    protected $user_model;

    public function __construct()
    {
        parent::__construct();
        $this->user_model = \app\common\model\Users::get($this->user_id);
    }

    //动态
    public function dynamic()
    {
        //按用户查看
        $uid = input('user_id',0,'intval');
        $user_id = $this->user_id;
        $where = [];
        !empty($uid) && $where[] = ['uid','=',$uid];
        $list =[];
        $info=\app\common\model\Dynamic::with(['linkCommentCount'
            //点赞
            ,'linkIsPraise'=>function($query)use($user_id){
                $query->where('uid','=',$user_id);
            }
            //用户
            ,'linkUsers'=>function($query)use($user_id){
            $query->with(['linkHasFollow'=>function($query)use($user_id){
                $query->where('uid','=',$user_id);
            }]);
        }])->where($where)
            ->order('id desc')->paginate()
            ->each(function($item,$index)use(&$list){
                array_push($list,[
                    'id'=>$item['id'],
                    'uid'=>$item['uid'],
                    'face'=>$item['link_users']['face'],
                    'user_name'=>$item['link_users']['name'],
                    'release_date'=>$item['releaseDate'],
                    'content'=>$item['content'],
                    'file'=>$item['fileGroup'],
                    'share_times'=>$item['share_times'],
                    'praise_times'=>$item['praise_times'],
                    'views'=>$item['views'],
                    'comment_times'=> isset($item['link_comment_count']['comment_count'])?$item['link_comment_count']['comment_count']:0,
                    'is_follow'=>empty($item['link_users']['link_has_follow'])?0:1,
                    'is_praise'=>empty($item['link_is_praise'])?0:1,
                ]);
            });
        $data = ['list'=>$list,'total_page'=>$info->lastPage()];
        return $this->_resData(1,'获取成功',$data);
    }

    //动态-发布
    public function dynamicRelease()
    {
        try{
            $input_data = input();
            $input_data['uid'] = $this->user_id;
            $validate =new \app\common\validate\Dynamic();
            $validate->scene('api_release');
            $model = new \app\common\model\Dynamic();
            $model->actionAdd($input_data,$validate);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }

        return $this->_resData(1,'发布成功');
    }

    //评论--
    public function dyComment()
    {
        $php_input = input();
        try{
            $model = \app\common\model\Dynamic::addComment($this->user_model,$php_input);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }

        return $this->_resData(1,'评论成功',['comment'=>$model->structInfo()]);
    }


    //评论列表
    public function dyComList()
    {
        $id = input('id',0,'intval');
        $where = [];
        $where[]= ['dy_id','=',$id];
        $where[]= ['pid','=',0];
        $where[]= ['status','=',1];
        $list =[];
        $user_id = $this->user_id;
        $info = \app\common\model\DyComment::with(['linkUsers','linkToUsers','linkPraise'
            ,'linkChild'=>function($query)use($user_id){
                $query->with(['linkUsers','linkToUsers','linkPraise','linkIsPraise'=>function($query)use($user_id){
                    $query->where('uid','=',$user_id);
                }])->order('id desc');
            }
            ,'linkIsPraise'=>function($query)use($user_id){
                $query->where('uid','=',$user_id);
            }
        ])->where($where)->order('id desc')->paginate()->each(function($item,$index)use(&$list){
                $child_comment = [];
                foreach ($item['link_child'] as $vo){
                    array_push($child_comment, $vo->structInfo());
                }

                array_push($list,array_merge($item->structInfo($child_comment),['child'=>$child_comment]));
            });

        $data = ['list'=>$list,'total_page'=>$info->lastPage()];
        return $this->_resData(1,'获取成功',$data);
    }

    //评论点赞
    public function dyComPraise()
    {
        $php_input = input();
        try{
            $model = \app\common\model\DyComment::praise($this->user_model,$php_input);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }

        return $this->_resData(1,$model->praise_date?'点赞成功':'已取消点赞',['state'=>$model->praise_date?1:0]);
    }


    //-点赞
    public function dyPraise()
    {
        $php_input = input();
        try{
            $model = \app\common\model\Dynamic::praise($this->user_model,$php_input);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }

        return $this->_resData(1,$model->praise_date?'点赞成功':'已取消点赞',['state'=>$model->praise_date?1:0]);
    }

    //动态详情
    public function dyDetail()
    {
        $id = input('id',0,'intval');
        $user_id = $this->user_id;
        $model = \app\common\model\Dynamic::with(['linkCommentCount'
            //点赞
            ,'linkIsPraise'=>function($query)use($user_id){
                $query->where('uid','=',$user_id);
            }
            ])->get($id);
        //数据
        $data = [];
        if(!empty($model)){
            $data=[
                    'id'=>$model['id'],
                    'uid'=>$model['uid'],
                    'face'=>$model['link_users']['face'],
                    'user_name'=>$model['link_users']['name'],
                    'release_date'=>$model['releaseDate'],
                    'content'=>$model['content'],
                    'file'=>$model['fileGroup'],
                    'share_times'=>$model['share_times'],
                    'praise_times'=>$model['praise_times'],
                    'views'=>$model['views'],
                    'comment_times'=> isset($model['link_comment_count']['comment_count'])?$model['link_comment_count']['comment_count']:0,
                    'is_follow'=>empty($model['link_users']['link_has_follow'])?0:1,
                    'is_praise'=>empty($model['link_is_praise'])?0:1,
                ];
        }
        return $this->_resData(1,'获取成功',$data);
    }
    //视频列表
    public function video()
    {
        //按用户查看
        $uid = input('user_id',0,'intval');
        $user_id = $this->user_id;
        $where = [];
        !empty($uid) && $where[] = ['uid','=',$uid];
        $list =[];
        $info=\app\common\model\Video::with(['linkCommentCount'
            //点赞
            ,'linkIsPraise'=>function($query)use($user_id){
                $query->where('uid','=',$user_id);
            }
            //用户
            ,'linkUsers'=>function($query)use($user_id){
                $query->with(['linkHasFollow'=>function($query)use($user_id){
                    $query->where('uid','=',$user_id);
                }]);
            }])->where($where)
            ->order('id desc')->paginate()
            ->each(function($item,$index)use(&$list){
                array_push($list,[
                    'id'=>$item['id'],
                    'uid'=>$item['uid'],
                    'face'=>$item['link_users']['face'],
                    'user_name'=>$item['link_users']['name'],
                    'release_date'=>$item['releaseDate'],
                    'title'=>$item['title'],
                    'addr'=>'',
                    'file'=>$item['fileGroup'],
                    'share_times'=>$item['share_times'],
                    'praise_times'=>$item['praise_times'],
                    'views'=>$item['views'],
                    'comment_times'=> isset($item['link_comment_count']['comment_count'])?$item['link_comment_count']['comment_count']:0,
                    'is_follow'=>empty($item['link_users']['link_has_follow'])?0:1,
                    'is_praise'=>empty($item['link_is_praise'])?0:1,
                ]);
            });
        $data = ['list'=>$list,'total_page'=>$info->lastPage()];
        return $this->_resData(1,'获取成功',$data);
    }


    //视频详情
    public function videoDetail()
    {
        $id = input('id',0,'intval');
        $user_id = $this->user_id;

        $where[] = ['id','=',$id];
        $model=\app\common\model\Video::with(['linkCommentCount'
            //点赞
            ,'linkIsPraise'=>function($query)use($user_id){
                $query->where('uid','=',$user_id);
            }
            //用户
            ,'linkUsers'=>function($query)use($user_id){
                $query->with(['linkHasFollow'=>function($query)use($user_id){
                    $query->where('uid','=',$user_id);
                }]);
            }])->where($where)
            ->order('id desc')->find();
            $info = [
                    'id'=>$model['id'],
                    'uid'=>$model['uid'],
                    'face'=>$model['link_users']['face'],
                    'user_name'=>$model['link_users']['name'],
                    'release_date'=>$model['releaseDate'],
                    'title'=>$model['title'],
                    'addr'=>'',
                    'file'=>$model['fileGroup'],
                    'share_times'=>$model['share_times'],
                    'praise_times'=>$model['praise_times'],
                    'views'=>$model['views'],
                    'comment_times'=> isset($model['link_comment_count']['comment_count'])?$model['link_comment_count']['comment_count']:0,
                    'is_follow'=>empty($model['link_users']['link_has_follow'])?0:1,
                    'is_praise'=>empty($model['link_is_praise'])?0:1,
                ];
        $data = ['info'=>$info];
        return $this->_resData(1,'获取成功',$data);
    }


    //发布视频
    public function videoRelease()
    {
        try{
            $input_data = input();
            $input_data['uid'] = $this->user_id;

            $validate =new \app\common\validate\Video();
            $validate->scene('api_release');
            $model = new \app\common\model\Video();
            $model->actionAdd($input_data,$validate);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }

        return $this->_resData(1,'发布成功');
    }

    //-点赞
    public function videoPraise()
    {
        $php_input = input();
        try{
            $model = \app\common\model\Video::praise($this->user_model,$php_input);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }

        return $this->_resData(1,$model->praise_date?'点赞成功':'已取消点赞',['state'=>$model->praise_date?1:0]);
    }

    //评论--
    public function videoComment()
    {
        $php_input = input();
        try{
            $model = \app\common\model\Video::addComment($this->user_model,$php_input);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }

        return $this->_resData(1,'评论成功',['comment'=>$model->structInfo()]);
    }


    //评论点赞
    public function videoComPraise()
    {
        $php_input = input();
        try{
            $model = \app\common\model\VideoComment::praise($this->user_model,$php_input);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }

        return $this->_resData(1,$model->praise_date?'点赞成功':'已取消点赞',['state'=>$model->praise_date?1:0]);
    }

    //评论列表
    public function videoComList()
    {
        $id = input('id',0,'intval');
        $where = [];
        $where[]= ['vid','=',$id];
        $where[]= ['pid','=',0];
        $where[]= ['status','=',1];
        $list =[];
        $user_id = $this->user_id;
        $info = \app\common\model\VideoComment::with(['linkUsers','linkToUsers','linkPraise'
            ,'linkChild'=>function($query)use($user_id){
                $query->with(['linkUsers','linkToUsers','linkPraise','linkIsPraise'=>function($query)use($user_id){
                    $query->where('uid','=',$user_id);
                }])->order('id desc');
            }
            ,'linkIsPraise'=>function($query)use($user_id){
                $query->where('uid','=',$user_id);
            }
        ])->where($where)->order('id desc')->paginate()->each(function($item,$index)use(&$list){
            $child_comment = [];
            foreach ($item['link_child'] as $vo){
                array_push($child_comment, $vo->structInfo());
            }

            array_push($list,array_merge($item->structInfo($child_comment),['child'=>$child_comment]));
        });

        $data = ['list'=>$list,'total_page'=>$info->lastPage()];
        return $this->_resData(1,'获取成功',$data);
    }




    //活动
    public function activity()
    {
        //查询我的活动
        $is_mine = input('is_mine',0,'intval');
        //我参加的活动
        $is_join = input('is_join',0,'intval');

        $user_id = $this->user_id;
        $where = [];

        //默认查询方式
        $model=\app\common\model\Activity::with(['linkIsJoin'=>function($query)use($user_id){
                $query->where('uid','=',$user_id);
            } ]);

        if($is_mine){
            $where[] =['uid','=',$this->user_id];
        }

        if($is_join){
            //我参加的活动
            $model=\app\common\model\Activity::withJoin(['linkIsJoin'=>function($query)use($user_id){
                $query->where('linkIsJoin.uid','=',$user_id);
            }],'left');
        }


        $list =[];
        $info=$model->with(['linkJoinCount','linkUsers'])->where($where)
            ->order('id desc')->paginate()
            ->each(function($item,$index)use(&$list){
//                dump($item);exit;
                array_push($list,[
                    'id'=>$item['id'],
                    'uid'=>$item['uid'],
                    'face'=>$item['link_users']['face'],
                    'user_name'=>$item['link_users']['name'],
                    'title'=>$item['title'],
                    'img'=>$item['img'],
                    'content'=>$item['content'],
                    'share_times'=>$item['share_times'],
                    'praise_times'=>$item['praise_times'],
                    'join_num' => empty($item['link_join_count']) ? 0 : $item['link_join_count']['join_count'],
                    'views'=>$item['views'],
                    'comment_times'=> 0,
                    'start_date'=> $item['start_date'],
                    'end_date'=> $item['end_date'],
                    'date_str'=> $item['end_date'].'至'.$item['end_date'],
                    'addr'=> $item['addr'],
                    'addr_extra'=> $item['addr_extra'],
                    'is_join'=>empty($item['link_is_join'])?0:1,
                ]);
            });
        $data = ['list'=>$list,'total_page'=>$info->lastPage()];
        return $this->_resData(1,'获取成功',$data);
    }

    //活动-发布
    public function activityRelease()
    {
        try{
            $input_data = input();
            $input_data['uid'] = $this->user_id;
            $validate =new \app\common\validate\Activity();
            $validate->scene('api_release');
            $model = new \app\common\model\Activity();
            $model->actionAdd($input_data,$validate);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }

        return $this->_resData(1,'发布成功');
    }

    //活动详情
    public function actDetail()
    {
        $id = input('id',0,'intval');
        $user_id = $this->user_id;

        $model=\app\common\model\Activity::with(['linkJoinCount','linkUsers'
            ,'linkIsJoin'=>function($query)use($user_id){
                $query->where('uid','=',$user_id);
            }
        ])->where(['status'=>1,'id'=>$id])
            ->order('id desc')->find();
        empty($model) && exception('活动不存在');


        $info=[
            'id'=>$model['id'],
            'uid'=>$model['uid'],
            'face'=>$model['link_users']['face'],
            'user_name'=>$model['link_users']['name'],
            'title'=>$model['title'],
            'online'=>$model['online'],
            'user_num'=>$model['user_num'],
            'user_num_str'=>$model['user_num_str'],
            'img'=>$model['img'],
            'content'=>$model['content'],
            'share_times'=>$model['share_times'],
            'praise_times'=>$model['praise_times'],
            'join_num' => empty($model['link_join_count']) ? 0 : $model['link_join_count']['join_count'],
            'views'=>$model['views'],
            'comment_times'=> 0,
            'start_date'=> (string)$model['start_date'],
            'end_date'=> (string)$model['end_date'],
            'date_str'=> $model['start_date'].'至'.$model['end_date'],
            'addr'=> $model['addr'],
            'addr_extra'=> $model['addr_extra'],
            'is_join'=>empty($model['link_is_join'])?0:1,
        ];
        return $this->_resData(1,'获取成功',$info);
    }



    //评论--
    public function actComment()
    {
        $php_input = input();
        try{
            \app\common\model\Activity::addComment($this->user_model,$php_input);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }

        return $this->_resData(1,'评论成功');
    }

    //报名活动
    public function actJoin()
    {
        $php_input = input();
        try{
            \app\common\model\Activity::joinUs($this->user_model,$php_input);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }

        return $this->_resData(1,'加入成功');
    }


    //-点赞
    public function actPraise()
    {
        $php_input = input();
        try{
            $model = \app\common\model\Activity::praise($this->user_model,$php_input);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }

        return $this->_resData(1,$model->praise_date?'点赞成功':'已取消点赞');
    }

    //喜欢
    public function love()
    {
        $uid = input('user_id',0,'intval');
        $user_id = $this->user_id;
        $where = [];
        !empty($uid) && $where[] = ['uid','=',$uid];
        $list =[];

        $data = ['list'=>$list,'total_page'=>0];
        return $this->_resData(1,'获取成功',$data);
    }


    //福利列表
    public function welfare()
    {
        $list = [];
        $info=\app\common\model\Welfare::where(['status'=>1])
            ->order('sort desc')->paginate()
            ->each(function($item,$index)use(&$list){
//                dump($item);exit;
                array_push($list,[
                    'id'=>$item['id'],
                    'title'=>$item['title'],
                    'img'=>$item['img'],
                    'times'=>$item['times'],
                    'num_intro'=>'已有'.$item['times'].'人体验过',
                ]);
            });
        $data = ['list'=>$list,'total_page'=>$info->lastPage()];
        return $this->_resData(1,'获取成功',$data);
    }

    //福利列表
    public function welfareDetail()
    {
        $id = input('id',0,'intval');

        $model=\app\common\model\Welfare::where(['status'=>1])
            ->order('sort desc')->get($id);
        empty($model) && exception('福利已丢失');

        $info=[
            'id'=>$model['id'],
            'title'=>$model['title'],
            'img'=>$model['img'],
            'times'=>$model['times'],
            'num_intro'=>'已有'.$model['times'].'人体验过',
            'content'=>$model['content'],
        ];

        return $this->_resData(1,'获取成功',$info);
    }


}