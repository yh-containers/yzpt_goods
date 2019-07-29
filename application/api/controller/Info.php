<?php
namespace app\api\controller;

class Info extends Common
{
    protected $need_login=true;

    //动态
    public function dynamic()
    {


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

    //发布视频
    public function dyVideo()
    {
        try{
            $input_data = input();
            $input_data['uid'] = $this->user_id;

            $validate =new \app\common\validate\DyVideo();
            $validate->scene('api_release');
            $model = new \app\common\model\DyVideo();
            $model->actionAdd($input_data,$validate);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }

        return $this->_resData(1,'发布成功');
    }

    //好友列表
    public function friend()
    {

    }
}