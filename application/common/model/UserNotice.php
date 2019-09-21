<?php
/**
 * Date: 2019/8/9
 * Time: 17:56
 */
namespace app\common\model;
use think\model\concern\SoftDelete;
class UserNotice extends BaseModel
{
    use SoftDelete;
    protected $name = 'users_notice';

    public static $fields_type = [
        ['name'=>'系统通知'],
        ['name'=>'投诉通知'],
    ];

    /**
     * 发送通知
     * */
    public static function send($title,$content,$uid=0,$type=0,$cond_id=0){
        try{
            $model = new self();
            $model->type = $type;
            $model->uid = $uid;
            $model->cond_id = $cond_id;
            $model->title = $title;
            $model->content = $content;
            $model->save();
        }catch (\Exception $e){

        }
    }

    /**
     * 我的通知
     * @param array $php_input 数据
     * @param int $user_id 用户id
     * @throws
     * @return \think\Paginator
     * */
    public static function getList(array $php_input=[],$user_id=0)
    {

        $where = [];
        $where[] = ['status','=',1];
        if($user_id){
            $where[] =['uid','=',$user_id];
        }
        if(!empty($php_input['type'])){
            $where[] =['type','=',$php_input['type']];
        }
        $list = self::where(function($query)use($where){
            $query->where($where);
        })->whereOr(function($query){
            $query->where([['uid','=',0]]);
        })->order('id desc')->paginate();
        return $list;
    }
}