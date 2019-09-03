<?php
namespace app\common\model;

//点赞视图
class ViewLove extends BaseModel
{
    protected $table='love_view';

    public static $fields_type=[
        ['name'=>'动态'],
        ['name'=>'视频'],
    ];

    protected function getDateAttr($value)
    {
        return !empty($value)?$value:date('Y-m-d',$this->getData('create_time'));
    }
    protected function getImageAttr($value)
    {
        return !empty($value)?self::handleFile($value):'';
    }


    //发布的文件组合
    protected function getFileGroupAttr()
    {
        $data = ['type'=>'','data'=>[]];
        $file = $this->file;
        $file = empty($file) ? [] : explode(',',$file);
        foreach ($file as $key=>$vo){
            empty($data['type']) && $data['type']=self::checkImg($vo)?'image':'video';
            array_push($data['data'],[
                'file' => self::handleFile($vo),
                'ext'  => self::checkImg($vo)?'image':'video',
            ]);
        }
//        $size = $this->size;
//        $mine_type = $this->mine_type;

        return $data;
    }
    /**
     * 列表数据
     * @param array $php_input 数据
     * @param int $user_id 用户id
     * @param int $praise_uid 点赞用户
     * @throws
     * @return \think\Paginator
     * */
    public static function getList(array $php_input=[],$user_id=0,$praise_uid=0)
    {
        $where = [];
        if($user_id){
            $where[] =['uid','=',$user_id];
        }
        if(!empty($php_input['type'])){
            $where[] =['type','=',$php_input['type']];
        }

        $list = self::with(['linkUsers'])->where($where)->order('praise_date desc')->paginate();
        return $list;
    }



    public function linkUsers()
    {
        return $this->belongsTo('Users','uid');
    }

    //是否给视频点过赞
    public function linkVideoIsPraise()
    {
        return $this->hasOne('DyPraise','dy_id')->whereNotNull('praise_date');
    }

    //是否给动态点过赞
    public function linkDynamicIsPraise()
    {
        return $this->hasOne('VideoPraise','vid')->whereNotNull('praise_date');
    }
}