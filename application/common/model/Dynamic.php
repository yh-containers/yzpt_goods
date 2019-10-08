<?php
namespace app\common\model;

use think\model\concern\SoftDelete;
use think\Validate;

class Dynamic extends BaseModel
{
    use SoftDelete;
    protected $name='dynamic';

    protected $insert = [];

    public static $link_cond_black_uid=0;

    public static $fields_status =[
        ['name'=>'公开'],
        ['name'=>'私密'],
        ['name'=>'禁用'],
    ];


    public function getStatusIntroAttr()
    {
        if(empty($this->is_auth)){
            return '待审核';
        }elseif($this->is_auth==2){
            return '已被拒';
        }else{
            return self::getPropInfo('fields_status',$this->status,'name');
        }
    }

    public function getStatusClassAttr()
    {
        if(empty($this->is_auth)){
            return 'wait';
        }elseif($this->is_auth==2){
            return 'warring';
        }else{
            return 'normal';
        }
    }


    //发布时间
    protected function getReleaseDateAttr()
    {
        return $this->create_time;
    }
    protected function getFileAttr($value){
        return empty($value)?[]:explode(',',$value);
    }

    //发布的文件组合
    protected function getFileGroupAttr()
    {
        $data = ['type'=>'','data'=>[]];
        $file = $this->file;
//        $file = empty($file)?[]:explode(',',$file);
        $ext = $this->ext;
        $ext = empty($ext)?[]:explode(',',$ext);
        foreach ($file as $key=>$vo){
            empty($data['type']) && $data['type']=isset($ext[$key])?(self::checkImg($ext[$key])?'image':'video'):'image';
            array_push($data['data'],[
                'file' => self::handleFile($vo),
                'ext'  => isset($ext[$key])?$ext[$key]:'image',
            ]);
        }
//        $size = $this->size;
//        $mine_type = $this->mine_type;

        return $data;
    }

    protected function setContentAttr($value)
    {
        return empty($value)?'':strip_tags($value);
    }


    //设置poi信息
    protected function setLocationPoiAttr($value)
    {
        if(!empty($value)){
//            exception($value);
            $poi = json_decode($value,true);

            $this->setAttr('coordinate',(isset($poi['lng']) && isset($poi['lat'])) ? ($poi['lng'].','. $poi['lat'] ):'');
            $this->setAttr('addr',isset($poi['address'])?$poi['address']:'');
        }
        return $value;
    }

    //
    public static function init()
    {
        parent::init(); // TODO: Change the autogenerated stub

        //审核通过
        self::event('auth_success',function($model){
            //增加养分
            $setting_content = SysSetting::getContent('normal');
            $setting_content = json_decode($setting_content,true);

            $num = isset($setting_content['dynamic_raise_num'])?explode(',',$setting_content['dynamic_raise_num']):[];
            $award_num = empty($num[0])?5:$num[0];
            $award_limit = isset($num[1])?$num[1]:3;
            //获取用户奖励次数
            $award_times = UsersRaiseLogs::where([['uid','=',$model['uid']],['type','=',5],['create_time','>=',date('Y-m-d').' 00:00:00']])->count();
            if(empty($award_limit) || empty($award_times) || $award_times<$award_limit){
                $user_model = Users::get($model['uid']);
                if($award_num>0 && !empty($user_model)){
                    $user_model->recordRaise($award_num,5,'发布动态获得:'.$award_num.'养分');
                }
            }

        });

        //审核日志
        self::event('auth_logs',function($model){
            SysOptLogs::record($model,app()->session->get('user_info.user_id'),'审核状态:'.($model->is_auth==1?'通过':'拒绝').';;活动标题'.$model->getData('content'));
        });


    }

    /**
     * 评论
     * @param Users $user_model;
     * @param array $data;
     * @throws
     * @return DyComment
     * */
    public static function addComment(Users $user_model,array $data=[])
    {
        empty($data['content']) && exception('内容不能为空');
        empty($data['id']) && exception('对象异常:id');

        $model = new DyComment();
        $model->uid = $user_model->id;
        $model->dy_id = $data['id'];
        $model->to_uid = empty($data['to_uid'])?0:$data['to_uid'];
        $model->pid = empty($data['pid'])?0:$data['pid'];
        $model->content = $data['content'];
        $model->save();
        return $model;
    }


    /**
     * 评论
     * @param Users $user_model;
     * @param array $data;
     * @throws
     * @return DyPraise
     * */
    public static function praise(Users $user_model,array $data=[])
    {
        empty($data['id']) && exception('对象异常:id');
        $dy_model = self::get($data['id']);
        empty($dy_model) && exception('动态异常');

        $model = DyPraise::where(['uid'=>$user_model->id,'dy_id'=>$data['id']])->find();
        if(empty($model)){
            $model = new DyPraise();
        }

        $model->uid = $user_model->id;
        $model->dy_id = $data['id'];
        $model->praise_date = empty($model->praise_date)?date('Y-m-d H:i:s'):null;
        $model->save();
        //点赞次数增加
        $model->praise_date?$dy_model->setInc('praise_times'):$dy_model->setDec('praise_times');
        //增加用户点赞次数
        try{
            //被点用户
            $opt_user_model = Users::get($dy_model['uid']);
            $model->praise_date?$opt_user_model->setInc('praise_num'):$opt_user_model->setDec('praise_num');
            //触发点赞奖励事件
            if(!empty($opt_user_model)  && empty($opt_user_model->praise_num%Users::PRAISE_TIMES_AWARD)){
                $opt_user_model->trigger('praise_award');
            }

        }catch (\Exception $e){
        }
        return $model;
    }




    /**
     * 动态删除
     * @param Users $user_model|null;
     * @param array $data;
     * @throws
     * */
    public static function Del(Users $user_model=null,array $data=[])
    {
        empty($data['id']) && exception('对象异常:id');
        $model = self::get($data['id']);
        empty($model) && exception('已被删除');
        if(!empty($user_model) && $user_model['id']!=$model['uid']){
            exception('无法进行此操作');
        }
        //直接删除
        $model->delete();
    }





    //动态用户
    public function linkUsers()
    {
        return $this->belongsTo('Users','uid');
    }

    //动态评论
    public function linkCommentCount()
    {
        return $this->hasOne('DyComment','dy_id')->field('dy_id,count(*) as comment_count')->group('dy_id');
    }

    //是否点过赞
    public function linkIsPraise()
    {
        return $this->hasOne('DyPraise','dy_id')->whereNotNull('praise_date');
    }


    //黑名单
    public function linkBlack()
    {
        return $this->hasOne('UsersBlack','b_uid','uid');
    }

    //提供删除评论信息
    public function getDelIntro()
    {
        return '删除动态'.$this->getData('content');
    }
}