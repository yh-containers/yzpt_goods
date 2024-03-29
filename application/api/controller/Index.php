<?php
namespace app\api\controller;



use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;

class Index extends Common
{
    public function index()
    {
        abort(1,'abc');
        dump(config(''));
        echo 123;
    }

    //用户登录
    public function login()
    {
        $account = input('account');
        $mode_str = input('mode_str');
        $mode = input('mode',0,'intval');
        $data = input();
        try{
            $model = \app\common\model\Users::handleLogin($account,$mode_str,$mode,$data);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }

        return $this->_resData(1,'登录成功',[
            'user_token' => $model->generateUserToken(),
        ]);
    }

    //第三方登录流程
    public function loginThird()
    {
        $mode = input('mode');
        $php_input = input();
        try{
            $model = \app\common\model\Users::handleThirdLogin($mode,$php_input);
        }catch (\Exception $e){
            return $this->_resData($e->getCode(),$e->getMessage());
        }

        return $this->_resData(1,'登录成功',[
            'user_token' => $model->generateUserToken(),
        ]);
    }


    //用户注册
    public function reg()
    {

        try{
            $input_data = input();
            $validate =new \app\common\validate\Users();
            $validate->scene('api_reg');
            \app\common\model\Users::handleReg($input_data,$validate);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }

        return $this->_resData(1,'注册成功');
    }

    //发送短信
    public function sendSms()
    {
        $type = input('type');
        $phone = input('phone');
        try{
            \app\common\model\Sms::send($type,$phone);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }
        return $this->_resData(1,'发送成功');
    }

    //忘记密码
    public function forget()
    {
        try{
            $input_data = input();
            $validate =new \app\common\validate\Users();
            $validate->scene('api_forget');
            \app\common\model\Users::handleForget($input_data,$validate);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }

        return $this->_resData(1,'找回成功');
    }

    //系统默认标签
    public function sysLabel()
    {
        $sys_label = \app\common\model\SysSetting::getContent('sys_label');
        $sys_label = empty($sys_label)?[]:explode(',',$sys_label);
        $data = [];
        foreach ($sys_label as $vo){
            $data[] =[
                'name' => $vo,
                'is_active' => 0
            ];
        }
        return $this->_resData(1,'获取成功',$data);
    }

    //用户
    public function searchUsers()
    {
        $keyword = input('keyword','','trim');
        empty($keyword) && abort(0,'请输入关键字');

        $info = \app\common\model\Users::where([
                    ['name','like','%'.$keyword.'%'],
                    ['status','=',1]
                ])
                ->paginate();
        $list = [];
        foreach ($info as $vo){
            $list[] =[
                'id' => $vo['id'],
                'face' => $vo['face'],
                'name' => $vo['name'],
                'py' => $vo['py'],
            ];
        }
        $data=['list'=>$list,'total_page'=>$info->lastPage()];
        return $this->_resData(1,'获取成功',$data);
    }

    //反馈
    public function feedback()
    {
        try{
            $input_data = input();
            $input_data['uid'] = $this->user_id;
            $validate =new \app\common\validate\Feedback();
            $validate->scene('api_release');
            $model = new \app\common\model\Feedback();
            $model->actionAdd($input_data,$validate);
        }catch (\Exception $e){
            return $this->_resData(0,$e->getMessage());
        }
        return $this->_resData(1,'感谢您的反馈，我们会尽快处理');
    }
    
    //系统乐库
    public function music()
    {
        $where = [];
        $where[] = ['status','=',1];
        $list =[];
        $info=\app\common\model\Music::where($where)
            ->order('sort asc')->paginate(100)
            ->each(function($item,$index)use(&$list){
                array_push($list,[
                    'id'=>$item['id'],
                    'name'=>$item['name'],
                    'author'=>$item['author'],
                    'file'=>$item['file'],
                    'size'=>$item['size'],
                    'ext'=>$item['ext'],
                    'duration'=>$item['duration'],
                ]);
            });
        $data = ['list'=>$list,'total'=>$info->total()];
        return $this->_resData(1,'获取成功',$data);
    }

    //查看用信息
    public function userInfo()
    {
        $id = input('id',0,'intval');
        $on_praise_num = input('on_praise_num',0,'intval');
        $on_user_body = input('on_user_body',0,'intval');
        $on_user_label = input('on_user_label',0,'intval');
        $on_qr_code = input('on_qr_code',0,'intval'); //二维码
        $on_req_info = input('on_req_info',0,'intval'); //邀请信息
        $on_black = input('on_black',0,'intval'); //邀请信息

        //用户信息
        $user_model = \app\common\model\Users::get($id);

        $data=[];
        //用户资料
        $data['info']=[
            'id' => $user_model['id'],
            'num' => (string)$user_model['qr_code'],
            'name' => (string)$user_model['name'],
            'real_name' => (string)$user_model['real_name'],
            'face' => (string)$user_model['face'],
            'sex' => (int)$user_model['sex'],
            'sex_name' => empty($user_model)?'':\app\common\model\Users::getPropInfo('fields_sex',$user_model['sex']),
            'age' => (string)$user_model['age'],
            'height' => (string)$user_model['height'],
            'intro' => (string)$user_model['intro'],
            'address' => (string)$user_model['address'],
            'birthday' => empty($user_model)?'':$user_model->birthday,
            'flag' => empty($user_model->py)?'':$user_model->py[0],
        ];
        //黑名单
        if($on_black){
            //验证用户是否在我的黑名单
            $is_black = \app\common\model\UsersBlack::where(['uid'=>$this->user_id,'b_uid'=>$id])->find();
            //验证用户我是否在对方黑名单
            $other_black = \app\common\model\UsersBlack::where(['uid'=>$id,'b_uid'=>$this->user_id])->find();
            $data['black'] = ['in_mine'=>empty($is_black)?0:1,'in_other'=>empty($other_black)?0:1];
        }

        //用户点赞
        if($on_praise_num){
            //粉丝
            $fans_num = (int)\app\common\model\UsersFollow::whereNotNull('follow_time')->where(['f_uid'=>$id])->count();
            //关注对象
            $follow_num = (int)\app\common\model\UsersFollow::whereNotNull('follow_time')->where(['uid'=>$id])->count();
            //是否关注
            $is_follow = \app\common\model\UsersFollow::where(['uid'=>$this->user_id,'f_uid'=>$id])->whereNotNull('follow_time')->find();
            $is_follow_state = empty($is_follow)?0:1;
            if($is_follow_state && $this->user_id){
                //验证对方是否关注了我
                $is_ftf_me = \app\common\model\UsersFollow::where(['uid'=>$id,'f_uid'=>$this->user_id])->whereNotNull('follow_time')->find();
                if($is_ftf_me) {
                    $is_follow_state = 2;
                }
            }

            $data['praise']=['num'=>(int)$user_model['praise_num'],'follow'=>$follow_num,'fans'=>$fans_num,'is_follow'=>$is_follow_state];
        }
        //身体状态
        if($on_user_body){
            $data['body']=['star'=>0];
        }
        //身体状态
        if($on_user_label){
            $label = empty($user_model['label'])?[]:$user_model['label'];
            $label_data = [];
            foreach ($label as $key=>$vo){
                $label_data[] = [
                    'index' => $key,
                    'name' => $vo,
                ];
            }
            $data['label']=$label_data;
        }
        //二维码
        if($on_qr_code){
            $data['qr_code']=[
                'code'=>$user_model->qr_code_info,
                'code_url'=>url('index/qrcode',['code'=>base64_encode($user_model->qr_code_info)],false,true)
            ];
        }
        //邀请信息
        if($on_req_info){
            $data['req_info']=[
                'num'=>$user_model->req_num,
                'req_raise_num'=>$user_model->req_raise_num,
            ];
        }

        return $this->_resData(1,'获取成功',$data);
    }

    public function video()
    {
        return action('info/video');
    }

    //广告图
    public function ad()
    {
        $type = input('type',0,'intval');

        $list = [];
        \app\common\model\Ad::where(['status'=>1,'type'=>$type])->order('sort asc')->select()->each(function($model,$index)use(&$list){
            array_push($list,[
                'title'=>$model['title'],
                'img'=>$model['img'],
                'url'=>$model['url'],
            ]);
        });



        return $this->_resData(1,'获取成功',['list'=>$list]);
    }

    //用户列表
    public function userFollow()
    {
        $php_input = input();
        $user_id = input('user_id',0,'intval');
        $mine_user_id = $this->user_id;//我自己
        $list = [];
        list($paginator,$user_key,$type) = \app\common\model\UsersFollow::getList($user_id, $php_input);
        $paginator->each(function($item,$index)use(&$list,$user_key,$user_id,$type,$mine_user_id){
            if($mine_user_id!=$user_id){
                //我看别人的列表
                $is_ftf_str = '未关注';
                $is_follow = 0;
                //自己有没有关注对方
                $is_ftf_info = \app\common\model\UsersFollow::where(['uid'=>$mine_user_id,'f_uid'=>$item[$user_key]['id']])->whereNotNull('follow_time')->find();
                if(!empty($is_ftf_info)){
                    $is_ftf_str = '已关注';
                    $is_follow = 1;
                    //对方是否关注了我
                    $is_ftf_info_other = \app\common\model\UsersFollow::where(['uid'=>$item[$user_key]['id'],'f_uid'=>$mine_user_id])->whereNotNull('follow_time')->find();
                    if(!empty($is_ftf_info_other)){
                        $is_ftf_str = '互相关注';
                        $is_follow = 2;
                    }
                }
            }else{
                //自己看自己的列表
                if($type=='fans'){
                    //自己有没有关注对方
                    $is_ftf_info = \app\common\model\UsersFollow::where(['uid'=>$user_id,'f_uid'=>$item[$user_key]['id']])->whereNotNull('follow_time')->find();
                    $is_ftf_str = '未关注';
                    $is_follow = 0;
                    if($is_ftf_info){
                        $is_ftf_str = '互相关注';
                        $is_follow = 2;
                    }
                }else{
                    $is_ftf_info_other = \app\common\model\UsersFollow::where(['uid'=>$item[$user_key]['id'],'f_uid'=>$user_id])->whereNotNull('follow_time')->find();
                    if($is_ftf_info_other){
                        $is_ftf_str = '互相关注';
                        $is_follow = 2;
                    }else{
                        $is_ftf_str = '已关注';
                        $is_follow = 1;
                    }
                }
            }

            array_push($list,[
                'uid' => $item[$user_key]['id'],
                'user_name' => $item[$user_key]['name'],
                'user_face' => $item[$user_key]['face'],
                'user_intro' => $item[$user_key]['intro'],
                'is_follow' => $is_follow,
                'is_ftf_str' => $is_ftf_str,
                'date' => $item['follow_time'],
            ]);
        });

        $data = ['list'=>$list,'total_page'=>$paginator->lastPage()];
        return $this->_resData(1,'获取成功',$data);
    }

    //分享信息
    public function shareInfo(){
        $redirect_url =url('index/share',[],false,true);
        if(!empty($this->user_id)){
            $model = \app\common\model\Users::get($this->user_id);
            $redirect_url = empty($model)?$redirect_url:$model->qr_code_info;
        }

        $share_content = \app\common\model\SysSetting::getContent('share');
        $share_content = empty($share_content)?[]:json_decode($share_content,true);

        return $this->_resData(1,'获取成功',[
            'type'=>'page',
            'title'=>empty($share_content['title'])?'养众天使,关注你的身体健康':$share_content['title'],
            'image'=>empty($share_content['img'])?'http://chinacarechain.com/assets/images/share_img.png':\app\common\model\BaseModel::handleFile($share_content['img']),
            'desc'=>empty($share_content['content'])?'快加入我！让我们尽早预防各种疾病':$share_content['content'],
            'url'=>$redirect_url,
        ]);
    }

    //系统通知
    public function notice(){

        $follow_msg_content = \app\common\model\SysSetting::getContent('follow_msg');
        $follow_msg_content = json_decode($follow_msg_content,true);
        $msg_content = empty($follow_msg_content['content'])?[]:explode("\r\n",$follow_msg_content['content']);
        $list = [];
        foreach ($msg_content as $vo){
            array_push($list,[
                'content'=>$vo
            ]);
        }

        return $this->_resData(1,'获取成功',[
            'list' => $list
        ]);
    }

    //生成二维码
    public function qrcode()
    {
        $content = base64_decode(input('code'));
        //资源路径
//        $resource_path = str_replace('\\','/',\Env::get('vendor_path').'\\endroid\\qr-code');
        // Create a basic QR code
        $qrCode = new QrCode($content);
        $qrCode->setSize(300);

// Set advanced options
        $qrCode
            ->setWriterByName('png')
            ->setMargin(10)
            ->setEncoding('UTF-8')
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH)
            ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0])
            ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255])
//            ->setLabel('Scan the code', 16, $resource_path.'/assets/noto_sans.otf', LabelAlignment::CENTER)
//            ->setLogoPath($resource_path.'/assets/symfony.png')
            ->setLogoWidth(100)
            ->setValidateResult(false)
        ;

        // Directly output the QR code
        return response($qrCode->writeString())->header('Content-Type',$qrCode->getContentType());
    }


    //分享页面
    public function share()
    {
        $req_code = input('req_code','','trim');
        return view('share',[
            'req_code'=>$req_code,
        ]);
    }
    public function shareAction()
    {
        $phone = input('phone','');
        $req_code = input('req_code','');
//        $php_input = input();
        !validPhone($phone) && exception('请输入正确的手机号码');
//        try{
//            \app\common\model\Users::handleLogin($phone,'1234',1,$php_input);
//        }catch (\Exception $e){
//            return $this->_resData(0,$e->getMessage());
//        }
        //查找用户
        $req_user_model = \app\common\model\Users::where(['qr_code'=>$req_code])->find();
        $req_uid = empty($req_user_model)?0:$req_user_model['id'];
        \app\common\model\UsersReqRecord::insert([
            'phone'=>$phone,
            'req_uid'=>$req_uid,
            'req_code'=>$req_code,
        ]);

        return $this->_resData(1,'绑定成功');



    }

    //分享页面
    public function download()
    {
        return view('download');
    }


    public function sh()
    {
        $shell = "ffmpeg -version";
        echo "<pre>";
        system($shell, $status);
        echo "</pre>";
        //注意shell命令的执行结果和执行返回的状态值的对应关系
        $shell = "<font color='red'>$shell</font>";
        if( $status ){
            echo "shell命令{$shell}执行失败";
        } else {
            echo "shell命令{$shell}成功执行";
        }
    }

    //分享
    public function shareHandle()
    {
        $mode = input('mode');
        $id = input('id',0,'intval');
        $share_way = input('share_way');//分享方式 qq weixin weibo
        $cond_mode = input('cond_mode');//分享方式 采用模式
        try{
            if($mode=='video' && $id){
                //视频分享
                \app\common\model\Video::where('id',$id)->setInc('share_times');
            }elseif ($mode=='dynamic' && $id){
                //动态
                \app\common\model\Dynamic::where('id',$id)->setInc('share_times');
            }
        }catch (\Exception $e){

        }
        return $this->_resData(1,'分享成功');

    }

    //积分商品
    public function goods()
    {
        $list = [];
        $info=\app\common\model\Goods::where([['status','=',1],['integral','>',0]])->paginate()->each(function($item,$index)use(&$list){
            array_push($list,[
                'id'=>$item['id'],
                'goods_name'=>$item['goods_name'],
                'integral'=>$item['integral'],
                'goods_image'=>$item['goods_image'],
                'intro'=>'已有'.$item['sales'].'人兑换',
            ]);
        });
        $data = ['list'=>$list,'total_page'=>$info->lastPage()];
        return $this->_resData(1,'获取成功',$data);
    }

    public function optInfo()
    {
        return $this->_resData(1,'操作成功',[
            'lc'=>0,//理财状态
        ]);
    }

    //热门关键字
    public function hotKey()
    {
        $type = input('type','video');

        $content  = \app\common\model\SysSetting::getContent('other');
        $content = json_decode($content,true);
        $hot_key = isset($content['video_hot_key'])?explode("\r\n",$content['video_hot_key']):[];
        return $this->_resData(1,'操作成功',[
            'hot_key'=>$hot_key,
        ]);
    }

    //服务商家
    public function mchService()
    {
        $list = [];
        $info=\app\common\model\MchService::where([['status','=',1]])->order('sort asc')->paginate()->each(function($item,$index)use(&$list){
            array_push($list,[
                'id'=>$item['id'],
                'name'=>$item['name'],
                'img'=>$item['img'],
                'view'=>$item['view'],
                'intro'=>'已有'.$item['consume_num'].'人参与',
            ]);
        });
        $data = ['list'=>$list,'total_page'=>$info->lastPage()];
        return $this->_resData(1,'获取成功',$data);
    }

    //服务商家门店
    public function mchStore()
    {
        $ser_id = input('ser_id',0,'intval');
        $list = [];
        $where[] = ['status','=',1];
        $ser_id && $where[]=['ser_id','=',$ser_id];

        $info=\app\common\model\MchStore::with(['linkMchService'])->where($where)->order('sort asc')->paginate()->each(function($item,$index)use(&$list){
            array_push($list,[
                'id'=>$item['id'],
                'ser_name'=>$item['link_mch_service']['name'],
                'name'=>$item['name'],
                'img'=>$item['img'],
                'star'=>$item['star'],
                'service_time'=>$item['service_time'],
                'intro'=>'已有'.$item['consume_num'].'人参与',
            ]);
        });
        $data = ['list'=>$list,'total_page'=>$info->lastPage()];
        return $this->_resData(1,'获取成功',$data);
    }

    //服务商家门店
    public function mchStoreDetail()
    {
        $id = input('id',0,'intval');
        $model=\app\common\model\MchStore::get($id);
        $detail=[
            'id'=>(int)$id,
            'name'=>(string)$model['name'],
            'img'=>(string)$model['img'],
            'star'=>(int)$model['star'],
            'service_time'=>(string)$model['service_time'],
            'content'=>(string)$model['content'],
            'intro'=>'已有'.(string)$model['consume_num'].'人参与',
        ];
        return $this->_resData(1,'获取成功',['detail'=>$detail]);
    }

    public function welcome()
    {
        return $this->_resData(1,'获取成功',[
            'show_url'=>url('index/warring',[],false,true),
            'img_group'=>[]
        ]);
    }

    public function warring()
    {
        return view('warring');
    }

    //获取投诉选项
    public function complaintType()
    {
        $info = \app\common\model\UsersComplaint::getPropInfo('fields_report');
        return $this->_resData(1,'获取成功',[
            'info'=>$info
        ]);
    }

    //不感兴趣
    public function noInterest()
    {
        $model = new \app\common\model\UsersNoInterest();
        $model->type = input('type',0,'intval');//投诉类型
        $model->cond_id = input('id',0,'intval');//作品id
        $model->uid = $this->user_id;
        $model->content = input('content','','trim');
        $model->save();
        return $this->_resData(1,'操作成功,将减少此类作品推荐');
    }

    //投诉
    public function complaint()
    {
        $type = input('type',0,'intval');
        $cond_id = input('id',0,'intval');;

        $type_info = \app\common\model\UsersComplaint::getPropInfo('fields_type',$type);
        //
        $info = '';
        $info_key = 0;
        if(!empty($type_info['is_record_content_field']) && isset($type_info['class'])){
            $class = $type_info['class'];
            $complaint_model = $class::where(['id'=>$cond_id])->find();
            $info= empty($complaint_model[$type_info['is_record_content_field']])?'':$complaint_model[$type_info['is_record_content_field']];
            $info_key= empty($complaint_model[$type_info['is_record_content_field_key']])?0:$complaint_model[$type_info['is_record_content_field_key']];

        }

        $model = new \app\common\model\UsersComplaint();
        $model->type = $type;//投诉类型
        $model->cond_id = $cond_id;//作品id
        $model->cd_id = input('cd_id',0,'intval'); //反馈类型
        $model->uid = $this->user_id;
        $model->content = input('content','','trim');
        $model->info = $info;
        $model->info_key = $info_key;
        $model->save();
        return $this->_resData(1,'举报提示：感谢您的举报，我们将通过站内信通知您举报的内容是否属实。谢谢！');

    }


    public function platform()
    {
        $content = \app\common\model\SysSetting::getContent('normal');
        $content = empty($content)?[]:json_decode($content,true);
        return $this->_resData(1,'获取成功',[
            'tel'=>isset($content['tel'])?$content['tel']:'',
            'qq'=>isset($content['qq'])?$content['qq']:'',
            'email'=>isset($content['email'])?$content['email']:'',
//            'cp'=>'Copyright  2019 深圳市深正互联网络有限公司版权所有.',
        ]);
    }
}