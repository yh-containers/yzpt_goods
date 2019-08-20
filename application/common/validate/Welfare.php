<?php
namespace app\common\validate;

use think\Validate;

class Welfare extends Validate
{
    protected $rule = [
        'title'  => 'max:100',
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


}