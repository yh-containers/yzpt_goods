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
        $off_user_info = input('off_user_info',0,'intval');//关闭用户基本信息
        $data=[];
        if(empty($off_user_info)){
            //用户资料
            $data['info']=[
                'id' => $this->user_model['id'],
                'num' => $this->user_model['num'],
                'name' => $this->user_model['name'],
                'face' => $this->user_model['face'],
                'sex' => $this->user_model['sex'],
                'sex_name' => \app\common\model\Users::getPropInfo('fields_sex',$this->user_model['sex']),
                'intro' => $this->user_model['intro'],
                'address' => $this->user_model['address'],
                'birthday' => $this->user_model->birthday,
            ];
        }

        //用户余额
        if($on_user_money){
            $data['money']=['raise_num'=>0,'money'=>0];
        }
        //用户点赞
        if($on_praise_num){
            //粉丝
            $fans_num = (int)\app\common\model\UsersFollow::where(['f_uid'=>$this->user_id])->count();
            //关注对象
            $follow_num = (int)\app\common\model\UsersFollow::where(['uid'=>$this->user_id])->count();
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
        return $this->_resData(1,$model->follow_time?'关注成功':'已取消关注');
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
        $info = UsersMoneyLog::moneyLogs($this->user_id);
        $list = [];
        foreach ($info as $vo){
            $list[] =[
                'intro' => $vo['intro'],
                'money' => $vo['money'],
                'date_time' => date('Y-m-d H:i',$vo['create_time']),
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
        $sing_model = \app\common\model\UsersSign::order('id desc')->find();
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
}