<?php
namespace app\common\model;

//养分日志
class UsersRaiseLogs extends BaseModel
{

    protected $name='users_raise_logs';
    protected $autoWriteTimestamp = 'datetime';

    public static $fields_type = [
        ['name'=>'签到'],
        ['name'=>'抽奖'],
        ['name'=>'邀请用户'],
        ['name'=>'用户注册'],
        ['name'=>'邀请用户'],
        ['name'=>'发布动态'],
        ['name'=>'发布视频'],
        ['name'=>'参加活动'],
        ['name'=>'更新健康数据'],
        ['name'=>'举办活动'],
        ['name'=>'视频/动态点赞'],
    ];

    /**
     * 记录养分日志
     * @param int $user_id 用户id
     * @param int $num 数量
     * @param int $type 类型
     * @param string $intro 说明
     * @param $cond_info mixed 奖励信息
     * */
    public static function recordLog($user_id,$num,$type=0,$intro='',$cond_info=null)
    {
        $model = new self();
        $model->data([
            'uid'=> $user_id,
            'num'=> $num,
            'type'=> $type,
            'intro'=> $intro,
            'cond_info'=> $cond_info,
        ],true);
        $model->save();

    }

}