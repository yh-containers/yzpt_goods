<?php
namespace app\common\model;

use think\Model;
use think\route\dispatch\Callback;
use think\Validate;

class BaseModel extends Model
{
    public static $fields_status = ['--','正常','关闭'];

    public static function init()
    {
        parent::init();
        //注册七牛文件删除事件
        self::observe(\app\common\event\DeleteQnResource::class);

        if(app()->request->module()=='admin'){
            //后台模块
            //注册删除事件
            self::event('after_delete',function($model){
                //记录删除日志
                SysOptLogs::record($model,app()->session->get('user_info.user_id'));
            });
        }

    }

    //删除文件



    //状态
    public static function getPropInfo($propOrFunc,$key=null,$field=null)
    {
        $class = get_called_class();
        if(method_exists($class,$propOrFunc)){
            $data = $class::$propOrFunc();
        }elseif(property_exists($class,$propOrFunc)){
            $data = $class::$$propOrFunc;
        }else{
            return false;
        }

        if(is_null($key)){
            return $data;
        }else{
            $info = isset($data[$key])?$data[$key] : [];
            return is_null($field)?$info:(isset($info[$field])?$info[$field]:'');
        }
    }
    /**
     * 数据保存/更新
     * @param array $data 数据
     * @param Validate $validate 验证器
     * @param \Closure $func 闭包
     * @throws
     * */
    public function actionAdd(array $data=[],Validate $validate=null,\Closure $func=null)
    {
        if ($validate && !$validate->check($data)) {
            exception($validate->getError());
        }
        $model = $this;
        $pk = $this->getPk();
        if(!empty($data[$pk])){  //编辑状态
            $model = $this->find($data[$pk]);
        }else{
            //清除主键影响
            unset($data[$pk]);
        }
        try{
            $model && $model->save($data);
            if(!is_null($func)){
                $func();
            }
        }catch (\Exception $e) {
            exception('操作异常'.$e->getMessage());
        }
    }

    /*
     * 数据删除
     * */
    public function actionDel(array $where=[])
    {
        try{
            !count($where) && exception('条件异常');
            $model = $this->where($where)->find();
            $model && $model->delete();
            return ['code'=>1,'msg'=>'删除成功'];
        }catch (\Exception $e) {
            return ['code'=>0,'msg'=>'删除异常:'.$e->getMessage()];
        }
    }


    /**
     * 评论删除
     * @param Users $user_model|null;
     * @param array $data;
     * @throws
     * @return self
     * */
    public static function commentDel(Users $user_model=null,array $data=[])
    {
        empty($data['id']) && exception('参数异常:id');
        $where[] = ['id', '=', $data['id']];
        if(!empty($user_model)){
            $where[] = ['uid','=',$user_model->id];
        }

        $model = self::where($where)->find();
        if(empty($model)){
            exception('评论已删除');
        }
        self::whereOr([['id','=',$model['id']],['pid','=',$model['id']]])->delete();
        return $model;
    }


    //作品审核
    public static function proAuth($id,$state,$content='')
    {
        $class = get_called_class();
        empty($id) && exception('参数异常:id');
        empty($state) && exception('参数异常:state');

        $model = new $class();
        $model = $model->get($id);
        empty($model) && exception('操作对象异常');
        !empty($model['is_auth']) && exception('操作对象未处于待审核状态,无法进行此操作');

        $content = empty($content)? '' :$content;
        if($model instanceof UsersComplaint){
            //用户投诉审核
            $model->status = $state===1?1:2;
            $content = empty($content)?($model->status==1?'已处理该记录':'已忽略该记录'):$content;
        }else{
            $content = empty($content)?($model->is_auth==1?'恭喜您,发布的信息已通过审核':'很遗憾,发布的信息审核被拒'):$content;
        }

        $model->is_auth = $state===1?1:2;
        $model->auth_time = date('Y-m-d H:i:s');
        $model->auth_content = $content;

        $model->save();

        //审核日志
        $model->trigger('auth_logs');

        if($model->is_auth==1){
            //审核通过
            $model->trigger('auth_success');
        }else{
            $model->trigger('auth_fail');
        }

    }


    //验证文件是否是图片
    public static function checkImg($file)
    {
        if(empty($file)){
            return null;
        }
        return preg_match('/.*(\.?png|\.?jpg|\.?jpeg|\.?gif)$/', $file);
    }

    //处理图片路径问题
    public static function handleFile($file='')
    {

        if(empty($file)) return '';
        if(preg_match('/^https?:\/\//',$file)) return $file;
        //当前模块
//        $module = request()->module();
//        if($module=='api'){
            //api模块增加域名前缀
            $qiniu_prefix = str_replace('/','\\/',config('qiniu.file_prefix'));
            if(preg_match('/^'.$qiniu_prefix.'/',$file)){
                //七牛文件前缀
                $file = config('qiniu.preview_domain').$file;
            }else{
                //当前服务器的域名
                $file = request()->domain().$file;
            }
//        }
        return $file;


    }

    // 制作邀请码
    protected function make_coupon_card() {
        $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $rand = $code[rand(0,25)]
            .strtoupper(dechex(date('m')))
            .date('d').substr(time(),-5)
            .substr(microtime(),2,5)
            .sprintf('%02d',rand(0,99));
        for(
            $a = md5( $rand, true ),
            $s = '0123456789ABCDEFGHIJKLMNOPQRSTUV',
            $d = '',
            $f = 0;
            $f < 8;
            $g = ord( $a[ $f ] ),
            $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ],
            $f++
        );
        return  $d;
    }

    //计算时间
    public static function time_tranx($the_time)
    {
        $now_time = time();
        $dur = $now_time - $the_time;
        if ($dur <= 0) {
            $mas =  '刚刚';
        } else {
            if ($dur < 60) {
                $mas =  $dur . '秒前';
            } else {
                if ($dur < 3600) {
                    $mas =  floor($dur / 60) . '分钟前';
                } else {
                    if ($dur < 86400) {
                        $mas =  floor($dur / 3600) . '小时前';
                    } else {
                        if ($dur < 259200) { //3天内
                            $mas =  floor($dur / 86400) . '天前';
                        } else {
                            $mas =  date("Y-m-d H:i:s",$the_time);
                        }
                    }
                }
            }
        }
        return $mas;
    }



}