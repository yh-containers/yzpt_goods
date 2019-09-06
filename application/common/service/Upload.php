<?php
namespace app\common\service;
use app\common\HttpCurl;
use Qiniu\Auth;
use function Qiniu\base64_urlSafeEncode;

class Upload
{
    private $user_id;
    protected $fsizeLimit=0;//文件上传大小

    protected $need_login=false;

    private $root_path;
    public function __construct($user_id=0)
    {
        $this->user_id = $user_id;
        $this->root_path = \think\facade\Env::get('root_path');
        $this->fsizeLimit = empty(config('qiniu.fsizeLimit'))?0:config('qiniu.fsizeLimit');
    }

    //获取上传凭证
    public function info($type='image')
    {
        $data = [
            'token' => '',
            'preview_domain' => request()->domain(),
            'url' => url('upload/upload',[],false,true).'?type='.$type,
        ];
        // 初始化签权对象
        if(!empty(config('qiniu.is_use'))){
            //预览地址
            $data['preview_domain'] = config('qiniu.preview_domain');

            $auth = new Auth(config('qiniu.ak'), config('qiniu.sk'));


            $save_key = config('qiniu.file_prefix').$type.'/'.date('Ymd').'/'.uniqid($this->user_id.'_'.'$(sec)_$(x:up_index)'.'_').'$(ext)';
            $police = [
                'saveKey' => $save_key,
                'forceSaveKey' => true,
                'fsizeLimit' => $this->fsizeLimit,
                'callbackBody'=>json_encode([
                    'up_index'=>'$(x:up_index)'
                ]),
                'returnBody' => json_encode(['code'=>1,'msg'=>'上传成功','data'=>[
                    'avinfo' => ['duration'=>'$(avinfo.video.duration)'],
                    'key' => '$(key)',
                    'hash' => '$(etag)',
                    'w' => '$(imageInfo.width)',
                    'h' => '$(imageInfo.height)',
                    'fsize' => '$(fsize)',
                    'ext' => '$(ext)',//上传资源的后缀名，通过自动检测的 mimeType 或者$(fname)的后缀来获取。
                    'mime_type' => '$(mimeType)',
                    'preview_domain' => config('qiniu.preview_domain'),
                ]])
            ];

            //视频获取封面图
            if($type=='video'){
                $police['persistentOps'] = 'vframe/jpg/offset/0.2/w/750/h/1334|saveas/'.\Qiniu\base64_urlSafeEncode(config('qiniu.bucket').':'.$save_key.'.jpg');
                $police['persistentNotifyUrl'] = request()->domain().'/api/upload/notify';
            }
            $upload_token = $auth->uploadToken(config('qiniu.bucket'),null,config('qiniu.expires'),$police);

            $data['token'] =$upload_token;
            $data['url'] =config('qiniu.url');
        }
        return $data;

    }

    /**
     * 处理视频封面图
     * @param string $path 路径
     * @throws
     * @return
     * */
    public function handleVideoImg($path)
    {
        empty($path) && exception('路径异常');
        $auth = new Auth(config('qiniu.ak'), config('qiniu.sk'));
        $url = 'http://api.qiniu.com/pfop/';
        $saveas = config('qiniu.bucket').':'.self::_handleVideoCoverImgPath($path);
        $body = 'bucket='.config('qiniu.bucket').'&key='.$path.'&fops=vframe%2Fjpg%2Foffset%2F1%2Fw%2F480%2Fh%2F360';
        $body .= '|saveas/'.base64_urlSafeEncode($saveas);
        $auth_header = $auth->authorization($url, $body, 'application/x-www-form-urlencoded');
        $header = ['Content-Type:application/x-www-form-urlencoded'];
        foreach ($auth_header as $key=>$vo){
            $header = array_merge($header,[$key.':'.$vo]);
        }
        //直接处理
        $result = HttpCurl::req($url,$body,'post',$header,true);
        $result = json_decode($result,true);
        if(isset($result['error'])){
            exception($result['error']);
        }

        $persistentId = $result['persistentId'];

        //查询任务
        //执行几次
        $times=6;
        $wait_result = ['code'=>0,'path'=>$saveas,'persistent_id'=>$persistentId];
        do{
            $handle_result = file_get_contents('http://api.qiniu.com/status/get/prefop?id='.$persistentId);
            $handle_result = json_decode($handle_result,true);
            if(isset($handle_result['error'])){
                exception($handle_result['error']);
                break;
            }
            $wait_result['code'] = $handle_result['code'];
            if($handle_result['code']==1 ||$handle_result['code']==2){
                //等待处理

            }elseif ($handle_result['code']==3 || $handle_result['code']==4){
                exception($handle_result['desc']);
                break;
            }elseif(empty($handle_result['code'])){
                //处理成功
                $wait_result['persistent_id'] = '';
                break;
            }

            $times--;
            sleep(1);
        }while($times>0);
        return $wait_result;
    }

    //处理视频封面图路径
    public static function _handleVideoCoverImgPath($path)
    {
        return str_replace('.','_',$path).'/cover_img.png'; //保存数据;
    }

    //上传
    public function upload($type='image')
    {

//        return $this->_resData(0,json_encode($_FILES));exit;
        $upload_file_key=key($_FILES);
        // 获取表单上传文件 例如上传了001.jpg
        $files = request()->file($upload_file_key);
        empty($files) && abort(0,'请选择上传文件');
        //上传路径
        $upload_data = [];
        if(is_array($files)){
            foreach($files as $file){
                $this->_uploadFile($upload_data,$type,$file);
                return $upload_data;
            }
        }else{
            $this->_uploadFile($upload_data,$type,$files);
            return $upload_data[0];
        }
    }

    private function _uploadFile(array &$upload_data,$type,$file)
    {
        $save_path = '/uploads/'.$type.'/'.date('Ymd').'/';
//        !$open_dir_month && $save_path = $save_path.date('Yhm');
        // 移动到框架应用根目录/uploads/ 目录下
        $user_id = $this->user_id;
        $mine_type = 'file';

        $info = $file
            ->validate(['size'=>$this->fsizeLimit])
            ->rule(function($obj)use(&$mine_type,$user_id){
                $file_info = $obj->getInfo();
                isset($file_info['type']) && $mine_type = $file_info['type'];
                return (empty($user_id)?'':$user_id).'_'.md5(json_encode($file_info));
            })
            ->move( $this->root_path.$save_path);
        $data = [
            'key'=>'',
            'fsize' => 0,
            'ext' => '',
            'mime_type' => $mine_type,
            'preview_domain' => request()->domain(),
        ];
        if($info){
            // 成功上传后 获取上传信息
            $data['key'] =str_replace('\\','/',$save_path.$info->getSaveName());
            $data['fsize'] =$info->getSize();
            $data['ext'] =$info->getExtension();
        }else{
            $data['error_msg']=$info->getError();
        }
        array_push($upload_data,$data);
    }
}