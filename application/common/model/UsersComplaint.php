<?php
namespace app\common\model;

class UsersComplaint extends BaseModel
{

    protected $name='users_complaint';
    protected $insert=['status'=>0];
    public static $fields_status =[
        ['name'=>'待处理'],
        ['name'=>'已处理'],
        ['name'=>'忽略'],
    ];

    public static $fields_type = [
        ['name'=>'视频','m_url'=>'article/videoDetail'],
        ['name'=>'动态','m_url'=>'article/dynamicDetail'],
        ['name'=>'活动','m_url'=>''],
        ['name'=>'用户','m_url'=>''],
        ['name'=>'视频评论','m_url'=>'article/videoDetail','is_record_content_field'=>'content','is_record_content_field_key'=>'vid','class'=>VideoComment::class],
        ['name'=>'动态评论','m_url'=>'article/dynamicDetail','is_record_content_field'=>'content','is_record_content_field_key'=>'dy_id','class'=>DyComment::class],
    ];

    public static $fields_report = [
        ['name'=>'违法违规'],
        ['name'=>'色情低俗'],
        ['name'=>'侮辱谩骂'],
        ['name'=>'垃圾广告、卖假冒伪劣产品'],
        ['name'=>'内容不适合孩子观看'],
        ['name'=>'未成年不适当行为'],
        ['name'=>'标题党、封面党、骗点击'],
        ['name'=>'作品令人反感、不喜欢'],
        ['name'=>'盗用作品'],
        ['name'=>'侵犯隐私'],
        ['name'=>'其他'],
    ];

    public static function init()
    {
        self::event('after_insert',function($model){
            $title = '举报通知';
            $content = '已收到举报信息,非常感谢您的举报,我们会立即进行相关信息处理.';
            $model['uid'] > 0 && UserNotice::send($title,$content,$model['uid'],0,$model['id']);
        });
    }


    public function linkUsers()
    {
        return $this->belongsTo('Users','uid');
    }
}