<?php
namespace app\common\validate;

use think\Validate;

class Activity extends Validate
{
    protected $rule = [
        'title'  => 'max:100',
        'phone'  => 'validPhone',
        'start_date'  => 'date|checkDate',
        'end_date'  => 'checkEndDate',
    ];

    protected $message  =   [
        'title.max' => '活动的标题不长度不能超过:rule个字符',
        'title.require' => '请输入标题',
        'content.require' => '活动详情必须输入',
        'date.require' => '活动日期必须传入',
        'start_date.date' => '活动时间格式异常:',

        'user_num.require' => '请输入活动人数',
        'addr.require' => '请选择活动地址',
        'addr_extra.require' => '请输入活动详细地址',
        'phone.require' => '手机号码必须输入',

    ];

    //api忘记密码
    public function sceneApi_release()
    {
        return $this->only(['title','content','start_date','end_date','user_num','addr','addr_extra','phone'])
            ->append('title','require')
            ->append('content','require')
            ->append('start_date','require')
            ->append('user_num','require')
            ->append('addr','require')
            ->append('addr_extra','require')
            ->append('phone','require')
            ;
    }
    
    protected function validPhone($value,$rule,$data=[])
    {
        if(!validPhone($value)){
            return '请输入正确的号码';
        }
        return true;
    }

    protected function checkDate($value,$rule,$data=[])
    {
        if($value<date('Y-m-d')){
            return '活动不能低于当前时间';
        }
        return true;
    }
    protected function checkEndDate($value,$rule,$data=[])
    {
        if(isset($data['start_date']) && $value<$data['start_date']){
            return '活动结束时间不得低于开始时间';
        }
        return true;
    }
}