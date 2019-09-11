<?php
namespace app\common\model;

class UsersHealth extends BaseModel
{

    protected $name='users_health';
    /**
     * @var Users
     * */
    protected $user_model;
    public static $fields_type = [
        ['name'=>'步数','month'=>'','list'=>[]],
        ['name'=>'体重','month'=>'','list'=>[]],
        ['name'=>'视力','month'=>'','list'=>[],'mode'=>['type'=>'area','title'=>[
            ['name'=>'左眼','value'=>''],
            ['name'=>'右眼','value'=>'']
        ]],'is_only'=>true], //单条记录
        ['name'=>'血压','month'=>'','list'=>[]],
        ['name'=>'血糖','month'=>'','list'=>[]],
        ['name'=>'血脂','month'=>'','list'=>[]],
        ['name'=>'心率','month'=>'','list'=>[]],
    ];

    public static function init()
    {
        parent::init(); // TODO: Change the autogenerated stub

        self::event('after_insert',function($model){
            //新增数据获得养分
             if(!empty($model->user_model)){
                 //增加邀请人数
                 $setting_content = SysSetting::getContent('normal');
                 $setting_content = json_decode($setting_content,true);
                 $num = isset($setting_content['healthy_raise_num'])?$setting_content['healthy_raise_num']:0;
                 $num>0 && $model->user_model->recordRaise($num, 8,'更新健康信息获得:'.$num.'养分');
             }
        });
    }



    /**
     * 列表数据--
     * @param  $php_input array 数据
     * @param  $user_id int 用户id
     * @throws
     * @return array
     * */
    public static function getTypeList(array $php_input=[],$user_id=0)
    {

        $where = [];
        if($user_id){
            $where[] =['uid','=',$user_id];
        }

        //按月查看
        $month = empty($php_input['month'])?date('Y-m-01'):date('Y-m-01',strtotime($php_input['month']));
        $next_month = date('Y-m-01',strtotime('+1 month',strtotime($month)));
        $where[] = ['date','>=',$month];
        $where[] = ['date','<',$next_month];

        //按类型查看
        if(isset($php_input['type']) && is_numeric($php_input['type'])){
            $where[] = ['type','=',$php_input['type']];
            $type_info = self::getPropInfo('fields_type',$php_input['type']);
            $type_info = empty($type_info)?[]:[$type_info];
        }else{
            //将数据分组
            $type_info = self::getPropInfo('fields_type');
        }

        //清空不要数据
        foreach ($type_info as &$vo){
            $vo['month'] = substr($month,0,-3);
        }

        self::where($where)->order('date desc')->select()->each(function($item,$index)use(&$type_info){
            if(isset($type_info[$item['type']])){
                $is_only = isset($type_info[$item['type']]['is_only']) ? $type_info[$item['type']]['is_only'] : false;
                $mode = isset($type_info[$item['type']]['mode'])?$type_info[$item['type']]['mode']:[];
                $mode_type =isset($mode['type'])?$mode['type']:'';
                $mode_title = isset($mode['title'])?$mode['title']:[];
                $item_data = $item->toArray();
                !empty($item_data['date']) && $item_data['date'] = (int)substr($item_data['date'],-2);
                if($mode_type=='area'){
                    //眼睛
                    $arr = explode(',', $item_data['num']);
                    foreach ($mode_title as $key=>&$vo){
                        $vo['value'] = isset($arr[$key])?$arr[$key]:'';
                    }
                    $item_data['num']=$mode_title;
                }
                $type_info[$item['type']]['list'][] = $item_data;
            }
        });

        return $type_info;
    }



    /**
     * 记录健康数据
     * @param $user_model Users 用户模型
     * @param $num int|string 健康数据
     * @param $type int 数据类型
     * @throws
     * @return self
     * */
    public static function setHealthData(Users $user_model,$num,$type=0)
    {
        $type_info = self::getPropInfo('fields_type',$type);
        empty($type_info) && exception('更新信息异常:type');

        $day = date('Y-m-d');
        $model = self::where(['uid'=>$user_model->id,'type'=>$type])->order('date desc')->find();
        if(empty($model)){
            $model = new self();
        }elseif($model['date']!==$day){
            if(!isset($type_info['is_only'])){
                $model = new self();
            }
        }else{

        }
        $model->user_model = $user_model;
        $model->setAttr('type',$type);
        $model->uid = $user_model->id;
        $model->num = $num;
        $model->date = $day;
        $model->save();
        return $model;
    }

}