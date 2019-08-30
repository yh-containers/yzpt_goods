<?php
namespace app\common\model;
use think\model\concern\SoftDelete;

class AwResult extends BaseModel
{
    use SoftDelete;
    protected $table = 'aw_result';
    public static $award_info = [
        ['name'=>'未中奖','num'=>0,'handle'=>''],
        ['name'=>'获得1个养分','num'=>1,'handle'=>''],
        ['name'=>'获得2个养分','num'=>2,'handle'=>''],
        ['name'=>'获得3个养分','num'=>3,'handle'=>''],
        ['name'=>'获得4个养分','num'=>4,'handle'=>''],
        ['name'=>'获得5个养分','num'=>5,'handle'=>''],
    ];

    /**
     * 用户抽奖
     * @param Users $users 用户模型
     * @throws
     * @return self
     * */
    public static function drawAward(Users $users)
    {
        //今天时间
        $today  = date('Y-m-d');
        $model = self::where(['uid'=>$users->id])->order('id desc')->find();
        if($model['draw_date']>=$today){
            exception('今天抽过奖，请明天再来');
        }
        //创建抽奖记录
        $award_key = mt_rand(0,5);
        $award_info = self::getPropInfo('award_info',$award_key);
        if(empty($award_info)){
            exception('奖励信息异常');
        }
        //添加奖励
        try{
            \think\Db::startTrans();
            //获取抽奖结果
            $model = new self();
            $model->data([
                'uid' => $users->id,
                'aid' => $award_key,
                'info' => $award_info['name'],
                'draw_date' => date('Y-m-d'),
                'status' => 1,
            ],true);
            $model->save();
            //增加养分
            $award_info['num']>0 && $users->recordRaise($award_info['num'],4,'每日抽奖获得:'.$award_info['num'].'养分');

            \think\Db::commit();
        }catch (\Exception $e){
            \think\Db::rollback();
        }

        return $model;


    }



}