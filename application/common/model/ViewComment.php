<?php
namespace app\common\model;

//点赞视图
class ViewComment extends BaseModel
{
    protected $table = 'comment_view';
    public static $fields_type=[
        ['name'=>'--',       'model'=>''],
        ['name'=>'动态评论',  'model'=>''],
        ['name'=>'活动评论',  'model'=>''],
        ['name'=>'视频评论',  'model'=>''],
    ];

    protected function getTraxDateAttr()
    {
        return self::time_tranx($this->getData('create_time'));
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
        $where[]=['status','=',1];
        if($user_id){
            $where[] =['uid','=',$user_id];
        }
        if(!empty($php_input['type'])){
            $where[] =['type','=',$php_input['type']];
        }
        $list = self::with(['linkUsers'])->where($where)->order('create_time desc')->paginate();
        return $list;
    }



    public function linkUsers()
    {
        return $this->belongsTo('Users','uid');
    }
}