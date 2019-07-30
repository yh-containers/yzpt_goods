<?php
namespace app\common\model;

use think\Model;
use think\route\dispatch\Callback;
use think\Validate;

class BaseModel extends Model
{
    public static $fields_status = ['--','正常','关闭'];
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

    //验证文件是否是图片
    public static function checkImg($file)
    {
        if(empty($file)){
            return null;
        }
        return preg_match('/.*(\.png|\.jpg|\.jpeg|\.gif)$/', $file);
    }

    //处理图片路径问题
    public static function handleFile($file='')
    {

        if(empty($file)) return $file;
        //当前模块
        $module = request()->module();
        if($module=='api'){
            //api模块增加域名前缀
            $qiniu_prefix = str_replace('/','\\/',config('qiniu.file_prefix'));
            if(preg_match('/^'.$qiniu_prefix.'/',$file)){
                //七牛文件前缀
                $file = config('qiniu.preview_domain').$file;
            }else{
                //当前服务器的域名
                $file = request()->domain().$file;
            }
        }
        return $file;


    }

}