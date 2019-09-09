<?php
namespace app\common\model;

//点赞视图
class ViewPraise extends BaseModel
{
    protected $table='praise_view';

    public static $fields_type=[
        ['name'=>'--',                'model'=>''],
        ['name'=>'动态点赞',           'model'=>''],
        ['name'=>'动态评论点赞',       'model'=>''],
        ['name'=>'视频点赞',           'model'=>''],
        ['name'=>'视频评论点赞',       'model'=>''],
    ];


    protected function getTraxDateAttr()
    {
        return self::time_tranx(strtotime($this->praise_date));
    }


    /**
     * 列表数据
     * @param array $php_input 数据
     * @param int $user_id 用户id
     * @throws
     * @return \think\Paginator
     * */
    public static function getList(array $php_input=[],$user_id=0)
    {

        $where = [];
        if($user_id){
            $where[] =['love_uid','=',$user_id];
        }
        if(!empty($php_input['type'])){
            $where[] =['type','=',$php_input['type']];
        }
        $list = self::with(['linkLoveUid'])->whereNotNull('praise_date')->where($where)->order('praise_date desc')->paginate();
        return $list;
    }



    public function linkUsers()
    {
        return $this->belongsTo('Users','uid');
    }


    public function linkLoveUid()
    {
        return $this->belongsTo('Users','uid');
    }
}