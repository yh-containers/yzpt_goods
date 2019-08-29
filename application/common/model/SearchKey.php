<?php
/**
 * Date: 2019/8/29
 * Time: 14:49
 */
namespace app\common\model;
use think\model\concern\SoftDelete;
class SearchKey extends BaseModel
{
    use SoftDelete;
    protected $table = 'gd_search_key';
}