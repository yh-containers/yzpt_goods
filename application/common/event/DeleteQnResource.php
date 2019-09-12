<?php
namespace app\common\event;

class DeleteQnResource
{
    //七牛需要删除的字段等配置信息
    const DELETE_TABLE_FIELDS = [
        \app\common\model\Ad::class=>['filed'=>['img'],'delay'=>0],
        \app\common\model\GoodsCategory::class=>['filed'=>['icon','image','icon_img'],'delay'=>0],
        \app\common\model\Comment::class=>['filed'=>['imgs,'],'delay'=>0],
        \app\common\model\Goods::class=>['filed'=>['goods_image','image_arr,','tuijian_img','zd_img'],'delay'=>0],
        \app\common\model\OrderReturn::class=>['filed'=>['image,'],'delay'=>0],
        \app\common\model\Activity::class=>['filed'=>['img'],'delay'=>0],
        \app\common\model\Dynamic::class=>['filed'=>['file,'],'delay'=>0],
        \app\common\model\Feedback::class=>['filed'=>['file,'],'delay'=>0],
        \app\common\model\Music::class=>['filed'=>['img','file'],'delay'=>0],
        \app\common\model\News::class=>['filed'=>['img'],'delay'=>0],
        \app\common\model\Video::class=>['filed'=>['file','img'],'delay'=>0],
        \app\common\model\Welfare::class=>['filed'=>['img,'],'delay'=>0],
        \app\common\model\Merchant::class=>['filed'=>['img','logo'],'delay'=>0],
    ];


    //记录改变了的图片信息
    public function afterUpdate($model)
    {
        $model_class_name = get_class($model);
        if(array_key_exists($model_class_name,self::DELETE_TABLE_FIELDS)){
            //进行字段删除
            $opt_data = [];
            $del_fields = self::DELETE_TABLE_FIELDS[$model_class_name]['filed'];  //删除的图片字段
            $del_delay = self::DELETE_TABLE_FIELDS[$model_class_name]['delay'];  //延迟删除时间
            $time = time();
            //用户调整的字段信息
            $origin_data = $model->getOrigin();
            $change_data = $model->getChangedData();
            foreach ($del_fields as $vo){
                list($field, $image) = $this->_handleImg($vo,$origin_data);

                if(array_key_exists($field,$change_data)){
                    foreach ($image as $img){
                        array_push($opt_data, [
                            'class' => $model_class_name,
                            'field' => $field,
                            'file' => $img,
                            'create_time' => date('Y-m-d H:i:s',$time),
                            'delay_day' => date('Y-m-d',strtotime('+'.$del_delay.' day',$time)),
                        ]);
                    }

                }

            }
            //入库
            count($opt_data)>0 && \app\common\model\QnResourceDel::insertAll($opt_data);
            //执行删除动作
            config('qiniu.is_open_del') && self::runDel();
        }
    }
    public function afterDelete($model)
    {
        $model_class_name = get_class($model);
        if(array_key_exists($model_class_name,self::DELETE_TABLE_FIELDS)){
            //进行字段删除
            $opt_data = [];
            $del_fields = self::DELETE_TABLE_FIELDS[$model_class_name]['filed'];  //删除的图片字段
            $del_delay = self::DELETE_TABLE_FIELDS[$model_class_name]['delay'];  //延迟删除时间
            $time = time();
            $model_data = $model->getData();
            foreach ($del_fields as $vo){
                list($field, $image) = $this->_handleImg($vo,$model_data);
                foreach ($image as $img){
                    array_push($opt_data, [
                        'class' => $model_class_name,
                        'field' => $field,
                        'file' => $img,
                        'create_time' => date('Y-m-d H:i:s',$time),
                        'delay_day' => date('Y-m-d',strtotime('+'.$del_delay.' day',$time)),
                    ]);
                }
            }
            //入库
            count($opt_data)>0 && \app\common\model\QnResourceDel::insertAll($opt_data);
            //执行删除动作
            config('qiniu.is_open_del') && self::runDel();
        }

    }

    //删除七牛文件
    public static function runDel()
    {
        $qiniu_bucket = config('qiniu.bucket');
        $url = 'https://rs.qiniu.com';
        \app\common\model\QnResourceDel::whereNull('opt_time')->where(['delay_day'=>date('Y-m-d')])->select()->each(function($item)use($qiniu_bucket,$url){
            $result = \Qiniu\Storage\BucketManager::buildBatchDelete($qiniu_bucket,[$item->file]);
            foreach ($result as $vo){
                $req_url = $url.$vo;
                $auth = \app\common\service\Upload::authorization($req_url);
                $handle_result=\app\common\HttpCurl::req($req_url,[],'POST',[
                    'Authorization:'.$auth,
                    'Content-Type:  multipart/form-data'
                ],true);
                $item->opt_time = date('Y-m-d H:i:s');
                $item->info = $handle_result;
                $item->save();
            }
        });

    }



    private function _handleImg($field, $data)
    {
        $spe = substr($field,-1);
        if($spe==','){
            //说明该字段使用逗号分割的数据
            $field = substr($field,0,-1);
            $file = empty($data[$field])?[]:explode(',',$data[$field]);
        }else{
            $file = empty($data[$field])?[]:[$data[$field]];
        }
        return [$field,$file];

    }


}