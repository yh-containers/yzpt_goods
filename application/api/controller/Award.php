<?php
namespace app\api\controller;

//奖励
class Award extends Common
{

    protected $need_login=true;
    protected $ignore_action = ['getlist'];
    /**
     * @var \app\common\model\Users
     *
     * */
    protected $user_model;

    public function __construct()
    {
        parent::__construct();
        $this->user_id>0 &&$this->user_model = \app\common\model\Users::get($this->user_id);
    }

    //获取奖项
//    public function info()
//    {
//
//
//        return $this->_resData(1,'获取成功',);
//    }


    public function getList()
    {
        $award_info = \app\common\model\AwResult::getPropInfo('award_info');
        $list = array_column($award_info,'name');
        return $this->_resData(1,'获取成功',['list'=>$list]);
    }


    public function draw()
    {
        try{
            $model = \app\common\model\AwResult::drawAward($this->user_model);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }
        return $this->_resData(1,'获取成功',['index'=>$model['aid'],'intro'=>$model['info']]);
    }
}