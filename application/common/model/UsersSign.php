<?php
namespace app\common\model;

use think\Validate;

class UsersSign extends BaseModel
{

    protected $name='users_sign';
    protected $autoWriteTimestamp = 'date';


    /**
     * 用户签到
     * @param Users $user_model 用户对象
     * @throws
     * @return
     * */
    public static function sign(Users $user_model)
    {
        $yesterday = date('Y-m-d',strtotime('-1 day'));
        $today = date('Y-m-d');
        $model = self::where([
            ['uid','=',$user_model->id],
        ])->order('id desc')->find();
        if(!empty($model) && $model['date']==$today){
            exception('您今天已签到过');
        }
        //活动养分数量
        $num = 10;
        try{
            \think\Db::startTrans();
            //增加签到
            $new_model = new self();
            $new_model->uid = $user_model->id;
            $new_model->date = $today;//签到日期
            $new_model->times = empty($model->times)?1:$model->times+1;//总签到次数
            $new_model->lx_times = empty($model->lx_times)?1:($model->date!=$yesterday?1:$model->lx_times+1);//连续签到次数
            $new_model->save();
            //增加养分
            $user_model->recordRaise($num,0,'签到养分增加:'.$num);
            \think\Db::commit();
        }catch (\Exception $e){
            \think\Db::rollback();
            exception($e->getMessage());
        }

        return $num;
    }


}