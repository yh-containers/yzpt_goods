<?php
/**
 * Date: 2019/8/19
 * Time: 14:32
 */
namespace app\common\model;
use think\model\concern\SoftDelete;

class Comment extends BaseModel
{
    use SoftDelete;
    protected $table = 'gd_comment';
    public function getImgsAttr($value){
        $list = empty($value)?[]:explode(',',$value);
        foreach ($list as &$vo){
            $vo = self::handleFile($vo);
        }
        return $list;
    }
}