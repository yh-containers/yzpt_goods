<?php
namespace app\common\model;

class UsersFollow extends BaseModel
{

    protected $name='users_follow';

    /**
     * 列表数据
     * @param array $php_input 数据
     * @param int $user_id 用户id
     * @throws
     * @return \think\Paginator
     * */
    public static function getList($user_id,array $php_input=[])
    {
        $type = empty($php_input['type'])?'follow':$php_input['type'];
        $where = [];


        if($type==='follow'){
            //关注
            $where[] =['uid','=',$user_id];
        }else{
            //粉丝
            $where[]=['f_uid','=',$user_id];
        }
        $list = self::with(['linkUsers'])->whereNotNull('follow_time')->where($where)->order('follow_time desc')->paginate();
        return $list;
    }


    public function linkUsers()
    {
        return $this->belongsTo('Users','uid');
    }

}