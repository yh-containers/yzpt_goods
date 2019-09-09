<?php
namespace app\common\model;

class UsersMoneyLog extends BaseModel
{

    protected $name='users_money_log';

    public static $fields_type = [
        ['name'=>'余额消费'],
    ];

    /**
     * 用户消费记录
     * @param int $user_id
     * @throws
     * @return \think\Paginator
     * */
    public static function moneyLogs($user_id)
    {
        $list = self::where(['uid'=>$user_id])->order('id desc')->paginate();

        return $list;
    }

}