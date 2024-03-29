<?php
namespace app\api\controller;

use app\common\model\UsersMoneyLog;

class Mine extends Common
{
    protected $need_login=true;

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

    //个人基本信息
    public function info()
    {
        $on_praise_num = input('on_praise_num',0,'intval');
        $on_user_body = input('on_user_body',0,'intval');
        $on_user_money = input('on_user_money',0,'intval');
        $on_user_label = input('on_user_label',0,'intval');
        $on_qr_code = input('on_qr_code',0,'intval'); //二维码
        $on_req_info = input('on_req_info',0,'intval'); //邀请信息
        $on_req_info = input('on_req_info',0,'intval'); //邀请信息
        $off_user_info = input('off_user_info',0,'intval');//关闭用户基本信息

        $data=[];
        if(empty($off_user_info)){
            //用户资料
            $data['info']=[
                'id' => $this->user_model['id'],
                'num' => $this->user_model['qr_code'],
                'real_name' => $this->user_model['real_name'],
                'name' => $this->user_model['name'],
                'face' => $this->user_model['face'],
                'sex' => (int)$this->user_model['sex'],
                'sex_name' => \app\common\model\Users::getPropInfo('fields_sex',$this->user_model['sex']),
                'intro' => $this->user_model['intro'],
                'age' => $this->user_model['age'],
                'height' => $this->user_model['height'],
                'address' => $this->user_model['address'],
                'birthday' => $this->user_model->birthday,
                'flag' => empty($this->user_model->py)?'':$this->user_model->py[0],
            ];
        }

        //用户余额
        if($on_user_money){
            $data['money']=[
                'raise_num'=>empty($this->user_model['raise_num'])?0:$this->user_model['raise_num'],
                'money'=>0
            ];
        }
        //用户点赞
        if($on_praise_num){
            //粉丝
            $fans_num = (int)\app\common\model\UsersFollow::whereNotNull('follow_time')->where(['f_uid'=>$this->user_id])->count();
            //关注对象
            $follow_num = (int)\app\common\model\UsersFollow::whereNotNull('follow_time')->where(['uid'=>$this->user_id])->count();
            $data['praise']=['num'=>(int)$this->user_model['praise_num'],'follow'=>$follow_num,'fans'=>$fans_num];
        }
        //身体状态
        if($on_user_body){
            $data['body']=['star'=>0];
        }
        //身体状态
        if($on_user_label){
            $label = $this->user_model['label'];
            $label_data = [];
            foreach ($label as $key=>$vo){
                $label_data[] = [
                    'index' => $key,
                    'name' => $vo,
                ];
            }
            $data['label']=$label_data;
        }
        //二维码
        if($on_qr_code){
            $data['qr_code']=[
                'code'=>$this->user_model->qr_code_info,
                'code_url'=>url('index/qrcode',['code'=>base64_encode($this->user_model->qr_code_info)],false,true)
            ];
        }
        //邀请信息
        if($on_req_info){
            $data['req_info']=[
                'num'=>$this->user_model->req_num,
                'req_raise_num'=>$this->user_model->req_raise_num,
            ];
        }
        return $this->_resData(1,'获取成功',$data);
    }


    //关注用户
    public function followUser()
    {
        $f_uid = input('f_uid',0,'intval');
        try{
            $model = $this->user_model->followUser($f_uid);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }
        $state = 0;
        if($model['follow_time']){
            $state =1;
            //验证对方有没有关注我
            $obj_follow_me = \app\common\model\UsersFollow::where(['uid'=>$model['f_uid'],'f_uid'=>$this->user_id])->whereNotNull('follow_time')->find();
            if($obj_follow_me){
                $state=2;
            }
        }
        return $this->_resData(1,$model->follow_time?'关注成功':'已取消关注',['state'=>$state]);
    }

    //好友申请
    public function friendReq()
    {
        $f_uid = input('f_uid',0,'intval');
        try{
            $this->user_model->friendReq($f_uid);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }
        return $this->_resData(1,'申请成功,等待对方审核');
    }

    //处理好友申请
    public function friendReqPassOrRefuse()
    {
        $id = input('id',0,'intval');
        $state = input('state',1,'intval'); //通过状态
        try{
            $this->user_model->friendReqPassOrRefuse($id,$state);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }
        return $this->_resData(1,'处理成功');
    }

    //修改用户信息
    public function modInfo()
    {
        $php_input = input();
        try{
            $this->user_model->modInfo($php_input);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }
        return $this->_resData(1,'修改成功');
    }

    //消费日志
    public function moneyLog()
    {
        //用户消费日志
        $info = \app\common\model\UsersRaiseLogs::where(['uid'=>$this->user_id])->order('id desc')->paginate();
        $list = [];
        foreach ($info as $vo){
            $list[] =[
                'type' => $vo['type'],
                'intro' => $vo['intro'],
                'money' => $vo['num'],
                'date_time' => $vo['create_time'],
            ];
        }
        $data=['list'=>$list,'total_page'=>$info->lastPage()];
        return $this->_resData(1,'获取成功',$data);
    }

    //删除标签
    public function labelDel()
    {
        $index = input('index');
        try{
            $this->user_model->labelDel($index);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }
        return $this->_resData(1,'操作成功');
    }

    //标签新增
    public function labelAdd()
    {
        $name = input('name','','trim');
        try{
            $this->user_model->labelAdd($name);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }
        return $this->_resData(1,'操作成功');
    }

    //我的好友
    public function friends()
    {
        $data = \app\common\model\Users::friends($this->user_id);
        return $this->_resData(1,'操作成功',$data);
    }

    //签到信息
    public function signInfo()
    {
        $month = input('month','','trim');
        $month = empty($month)?date('Y-m'):date('Y-m',strtotime($month));
        $last_month = date('Y-m-01',strtotime('+1 month',strtotime($month)));
        $sign_day = [];
        \app\common\model\UsersSign::where([
            ['uid','=',$this->user_id],
            ['date','>=',$month],
            ['date','<',$last_month]
        ])->order('id asc')->select()->each(function($item,$index)use(&$sign_day,&$last_times){
            array_push($sign_day,$item['date']);
            $last_times = $item['lx_times'];
        });
        //签到数据
        $sing_model = \app\common\model\UsersSign::order('id desc')->where(['uid'=>$this->user_id])->find();
        $last_times = empty($sing_model)?0:$sing_model['lx_times']; //连续签到次数
        $today_sign_status = $sing_model['date']==date('Y-m-d')?1:0; //今天是否签到
        return $this->_resData(1,'操作成功',[
            'sign_day'=>$sign_day,
            'last_times'=>$last_times,
            'today_sign_status'=>$today_sign_status,
        ]);
    }

    //用户签到
    public function sign()
    {
        try{
            list($last_times,$num )= \app\common\model\UsersSign::sign($this->user_model);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }
        return $this->_resData(1,'签到成功',['last_times'=>$last_times,'num'=>$num]);
    }

    //我的通知
    public function notice()
    {
        $php_input = input();
        $list = [];
        $info = \app\common\model\UserNotice::getList($php_input,$this->user_id)->each(function($item,$index)use(&$list){
            array_push($list,[
                'id' => $item['id'],
                'title' => $item['title'],
                'content' => $item['content'],
                'date' => $item['create_time'],
            ]);
        });

        $data = ['list'=>$list,'total_page'=>$info->lastPage()];
        return $this->_resData(1,'获取成功',$data);
    }

    //给我点赞
    public function praiseInfo()
    {
        $php_input = input();
        $list = [];
        $info = \app\common\model\ViewPraise::getList($php_input,$this->user_id)->each(function($item,$index)use(&$list){
            array_push($list,[
                'uid' => $item['uid'],
                'user_name' => $item['link_love_uid']['name'],
                'user_face' => $item['link_love_uid']['face'],
                'cond_id' => $item['cond_id'],
                'title' => \app\common\model\ViewPraise::getPropInfo('fields_type',$item['type'],'name'),
                'content' => '',
                'date' => $item['trax_date'],
            ]);
        });

        $data = ['list'=>$list,'total_page'=>$info->lastPage()];
        return $this->_resData(1,'获取成功',$data);
    }

    //评论
    public function commentInfo()
    {
        $php_input = input();
        $list = [];
        $info = \app\common\model\ViewComment::getList($php_input,$this->user_id)->each(function($item,$index)use(&$list){
            array_push($list,[
                'uid' => $item['uid'],
                'user_name' => $item['link_to_users']['name'],
                'user_face' => $item['link_to_users']['face'],
                'cond_id' => $item['cond_id'],
                'title' => \app\common\model\ViewComment::getPropInfo('fields_type',$item['type'],'name'),
                'content' => $item['content'],
                'date' => $item['trax_date'],
            ]);
        });

        $data = ['list'=>$list,'total_page'=>$info->lastPage()];
        return $this->_resData(1,'获取成功',$data);
    }

    //关注列表--已弃用 请使用index/userfollow
    public function follow()
    {
        return $this->_resData(0,'接口已停用');
//        $php_input = input();
//        $list = [];
//        $user_id = $this->user_id;
//        list($paginator,$user_key,$type) = \app\common\model\UsersFollow::getList($user_id, $php_input);
//        $paginator->each(function($item,$index)use(&$list,$user_key,$user_id,$type){
//
//            if($type=='fans'){
//                //自己有没有关注对方
//                $is_ftf_info = \app\common\model\UsersFollow::where(['uid'=>$user_id,'f_uid'=>$item['uid']])->whereNotNull('follow_time')->find();
//                $is_ftf_str = '未关注';
//                $is_follow = 0;
//                if($is_ftf_info){
//                    $is_ftf_str = '互相关注';
//                    $is_follow = 2;
//                }
//            }else{
//                $is_ftf_info_other = \app\common\model\UsersFollow::where(['uid'=>$item['uid'],'f_uid'=>$user_id])->whereNotNull('follow_time')->find();
//                if($is_ftf_info_other){
//                    $is_ftf_str = '互相关注';
//                    $is_follow = 2;
//                }else{
//                    $is_ftf_str = '已关注';
//                    $is_follow = 1;
//                }
//            }
//            array_push($list,[
//                'uid' => $item['uid'],
//                'f_uid' => $item['f_uid'],
//                'user_name' => $item[$user_key]['name'],
//                'user_face' => $item[$user_key]['face'],
//                'user_intro' => $item[$user_key]['intro'],
//                'is_follow' => $is_follow,
//                'is_ftf_str' => $is_ftf_str,
//                'date' => $item['follow_time'],
//            ]);
//        });
//
//        $data = ['list'=>$list,'total_page'=>$paginator->lastPage()];
//        return $this->_resData(1,'获取成功',$data);
    }


    //我邀请的用户
    public function reqUsers()
    {
        $user_id = input('user_id',$this->user_id,'intval');
        $php_input = input();
        $list = [];
        $info = \app\common\model\Users::getReqList($user_id, $php_input)->each(function($item,$index)use(&$list){
            array_push($list,[
                'id' => $item['id'],
                'name' => $item['name'],
                'face' => $item['face'],
                'intro' => $item['intro'],
                'date' => $item['create_time'],
            ]);
        });

        $data = ['list'=>$list,'total_page'=>$info->lastPage()];
        return $this->_resData(1,'获取成功',$data);
    }

    //健康数据
    public function health()
    {
        $deep = input('deep',0,'intval');
        $user_id = input('user_id',$this->user_id,'intval');
        $php_input = input();
        $list = \app\common\model\UsersHealth::getTypeList( $php_input,$user_id,$deep);
        $data = ['list'=>$list];
        return $this->_resData(1,'获取成功',$data);
    }

    
    //增加数据导入
    public function healthUpdate()
    {
        try{
            $num = input('num');
            $type = input('type',0,'intval');
            \app\common\model\UsersHealth::setHealthData($this->user_model,$num,$type);
        }catch (\Exception $e){
            $this->_resData(0,$e->getMessage());
        }
        return $this->_resData(1,'更新成功');
    }

    //健康报表
    public function healthResult()
    {
        $data=[
            'img'=>$this->user_model->face,
            'date'=>date('m.d'),
            'number'=>99,
            'list'=>[
                ['intro'=>'累计步数为93步','color'=>''],
                ['intro'=>'体重80kg','color'=>''],
                ['intro'=>'左眼视力正常','color'=>'red'],
                ['intro'=>'血压正常','color'=>''],
                ['intro'=>'血脂正常','color'=>''],
                ['intro'=>'血糖正常','color'=>''],
                ['intro'=>'心率正常','color'=>''],
            ],
            'suggest'=>'建议注意调整日常生活饮食，遵循良好作息习惯，增加日常活动量，保持良好的身体状态。',
        ];
        //触发奖项
        //增加养分
        $setting_content = \app\common\model\SysSetting::getContent('normal');
        $setting_content = json_decode($setting_content,true);

        $num = isset($setting_content['healthy_raise_num'])?explode(',',$setting_content['healthy_raise_num']):[];
        $award_num = empty($num[0])?2:$num[0];
        $award_limit = isset($num[1])?$num[1]:1;
        //获取用户奖励次数
            $award_times = \app\common\model\UsersRaiseLogs::where([['uid','=',$this->user_id],['type','=',8],['create_time','>=',date('Y-m-d').' 00:00:00']])->count();
            if($award_num>0 && (empty($award_limit) || empty($award_times) || $award_times<$award_limit)){
                //验证用户是否有更新记录
                $update_record_model = \app\common\model\UsersHealth::where(['date'=>date('Y-m-d'),'uid'=>$this->user_id])->find();
                if($update_record_model) {
                    //增加养分
                    $this->user_model->recordRaise($award_num, 8, '更新健康信息获得:' . $award_num . '养分');

                }
        }
        return $this->_resData(1,'更新成功',$data);
    }

    //黑名单列表
    public function blackList()
    {
        $list = [];
        $info = \app\common\model\UsersBlack::with(['linkUsers'])->where(['uid'=>$this->user_id])->paginate()->each(function($item)use(&$list){
            array_push($list,[
                'uid'=>$item['b_uid'],
                'name' => $item['link_users']['name'],
                'face' => $item['link_users']['face'],
            ]);
        });
        $data = ['list'=>$list,'total_page'=>$info->lastPage()];
        return $this->_resData(1,'获取成功',$data);
    }

    //黑名单列表
    public function blackOpt()
    {
        $uid = input('user_id',0,'intval');
        try{
            $state = \app\common\model\UsersBlack::JoinOrDel($this->user_model,$uid);
        }catch (\Exception $e){
            return $this->_resData(0, $e->getMessage());
        }
        return $this->_resData(1,'已加入到黑名单，你可以在设置中移出',['state'=>$state]);
    }
    
}