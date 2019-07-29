<?php
namespace app\api\controller;

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
        return $this->_resData(1,'获取成功',[]);
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
}