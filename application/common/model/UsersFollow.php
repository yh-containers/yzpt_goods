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
     * @return array(\think\Paginator,$user_key)
     * */
    public static function getList($user_id,array $php_input=[])
    {
        $type = empty($php_input['type'])?'follow':$php_input['type'];
        $where = [];


        if($type==='follow'){
            //关注
            $where[] =['uid','=',$user_id];
            $with = 'linkFollowUsers';
            $return_key = 'link_follow_users';
        }else{
            //粉丝
            $where[]=['f_uid','=',$user_id];
            $with = 'linkFansUsers';
            $return_key = 'link_fans_users';
        }

        $list = self::with([$with])->whereNotNull('follow_time')->where($where)->order('follow_time desc')->paginate();
        return [$list,$return_key,$type];
    }


    public function linkFollowUsers()
    {
        return $this->belongsTo('Users','f_uid');
    }

    public function linkFansUsers()
    {
        return $this->belongsTo('Users','uid');
    }

}