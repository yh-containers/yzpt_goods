<?php
namespace app\common\model;

//
use think\model\concern\SoftDelete;
use think\Validate;

class Users extends BaseModel
{
    use SoftDelete;
    //性别
    public static $fields_sex = ['','男','女','未知'];
    //数据库表名
    protected $name = 'users';
    //自动完成
    protected $insert=['face','name','status'=>1,'qr_code'];

    //养分变更自从
    protected $c_raise_type=0;//类型
    protected $c_raise_num=0;//数量
    protected $c_raise_intro='';//介绍

    /**
     * @var self
     * */
    public static $req_user_model;//邀请者用户对象

    protected function  getIntroAttr($value)
    {
        return empty($value)?'':$value;
    }

    protected function  getAddressAttr($value)
    {
        return empty($value)?'':$value;
    }

    //拼音
    protected function getPyAttr($value)
    {
        return empty($value)?'':$value;
    }


    //用户编号
    protected function getNumAttr($value)
    {
        return sprintf('%06s', $this->id);
    }
    //用户头像获取
    public function getFaceAttr($value)
    {
        return self::handleFile($value);
    }

    protected function setQrCodeAttr()
    {
        return create_invite_code();
    }

    //自动完成头像
    protected function setFaceAttr($value)
    {
        return !empty($value)?$value:'/assets/images/avatar0'.rand(1,7).'.png';
    }
    //获取用户生日
    protected function getBirthdayAttr()
    {
        $birthday = [];
        !empty($this->birth_y) && array_push($birthday,$this->birth_y);
        !empty($this->birth_m) && array_push($birthday,$this->birth_m);
        !empty($this->birth_d) && array_push($birthday,$this->birth_d);
        return empty($birthday)?'':implode('-',$birthday);
    }
    protected function setBirthdayAttr($value)
    {
        $birthday = empty($value)?[]:explode('-',$value);

        if(count($birthday)==3){
            $this->setAttr('birth_y',$birthday[0]);
            $this->setAttr('birth_m',$birthday[1]);
            $this->setAttr('birth_d',$birthday[2]);
            //年龄
            $this->setAttr('age',date('Y')-$birthday[0]);
        }
        return $value;
    }


    //自动完成头像
    protected function setNameAttr($value,$data)
    {
        if($value){
            $name =  $value;
        }else{
            $phone = empty($data['phone'])?'':$data['phone'];

            if(!empty($phone)){
                $name = substr($phone,-4).'用户';
            } else{
                $name = '用户昵称';
            }
        }



        if(!empty($name)){
            $pinyin =new \Overtrue\Pinyin\Pinyin();
            $py = $pinyin->permalink($name,'-',PINYIN_NAME);
            $this->setAttr('py',strtoupper($py));
        }
        return $name;
    }

    //用户邀请码
    protected function setReqCodeAttr($value)
    {
        if(empty($value)){
            return $value;
        }

        self::$req_user_model = self::where(['qr_code'=>$value])->find();
        if(!empty(self::$req_user_model)){
            $this->setAttr('r_uid1',self::$req_user_model['id']);
            !empty(self::$req_user_model['r_uid1']) && $this->setAttr('r_uid2',self::$req_user_model['r_uid1']);
            !empty(self::$req_user_model['r_uid2']) && $this->setAttr('r_uid3',self::$req_user_model['r_uid2']);
        }


        return $value;

    }


    //用户密码
    protected function setPasswordAttr($value)
    {
        empty($value) && exception('密码不能为空');
        $salt = rand(10000,99999);
        $this->setAttr('salt',$salt);
        return self::generatePwd($value,$salt);
    }

    //用户label
    protected function getLabelAttr($value)
    {
        return empty($value)?[]:explode("\r\n",$value);
    }
    //用户label
    protected function setLabelAttr($value)
    {
        $value = is_array($value)?$value:[$value];
        return implode("\r\n",$value);
    }

    protected function getQrCodeInfoAttr()
    {

        return url('index/share',['req_code'=>$this->qr_code],false,true);
    }


    public static function init()
    {

        parent::init();

        //更新数据
        self::event('after_update',function($model){
            $change_data = $model->getChangedData();
            $update_chat_condition_field = ['phone','name','face'];
            foreach ($update_chat_condition_field as $filed){
                if(array_key_exists($filed,$change_data)){
                    $model->regUpdateChatUser();
                    break;
                }
            }
        });

        //用户新增事件
        self::event('after_insert', function ($model) {
            //绑定用户
            $model->regUpdateChatUser();

            //验证是邀请用户
            if(!empty(self::$req_user_model)){
                //增加邀请人数
                self::$req_user_model->setInc('req_num');
                $setting_content = SysSetting::getContent('normal');
                $setting_content = json_decode($setting_content,true);
                $num = isset($setting_content['req_raise_num'])?$setting_content['req_raise_num']:0;
                $num>0 && self::$req_user_model->recordRaise($num,2,'邀请用户获得:'.$num.'养分');
                //第二级增加养分

                //注册奖励养分

            }
        });
        //养分日志记录
        self::event('raise_logs', function ($model) {
            UsersRaiseLogs::recordLog($model['id'],$model->c_raise_num,$model->c_raise_type,$model->c_raise_intro);
        });
    }

    /**
     * 注册或更新用户聊天资料
     * */
    public function regUpdateChatUser()
    {

        $data = $this->getData();
        $config_chat_url = config('chat.url');
        $config_chat_route = config('chat.route');
        if(!empty($config_chat_url)){
            //注册聊天用户
            if(isset($config_chat_route['reg'])){
                $user_py_first = empty($data['py'])?'':$data['py'][0];
                $reg_update_data = [
                    'user_id' => $data['id'],
                    'phone' => empty($data['phone'])?'':$data['phone'],
                    'user_name' => empty($data['name'])?'':$data['name'],
                    'user_flag' => $user_py_first,
                    'user_face' => empty($data['face'])?'':self::handleFile($data['face']),
                ];
                $query ='';
                foreach ($reg_update_data as $key=>$vo){
                    $query .= $key.'='.$vo.'&';
                }
                $url = $config_chat_url.$config_chat_route['reg'].'?'.$query;
                try{ file_get_contents($url);  }catch (\Exception $e){}
            }
        }
    }


    /**
     * 用户第三方登录
     * @param string $mode 登录方式
     * @param array $data 模式密码/验证码
     * @param int $mode 登录模式 0密码  1验证码
     * @throws
     * @return self
     * */
    public static function handleThirdLogin($mode,array $data=[])
    {
        $limit_mode = ['qq','weibo','wechat'];
        empty($mode) && exception('登录方式异常');
        empty(in_array($mode,$limit_mode)) && exception('不支持该登录方式');

        empty($data['open_id']) && exception('参数异常:open_id');
        empty($data['access_token']) && exception('参数异常:access_token');

        if($mode=='qq'){
            $where['qq_openid'] = $data['open_id'];
        }elseif ($mode=='wechat'){
            $where['wx_openid'] = $data['open_id'];
        }elseif ($mode=='weibo'){
            $where['wb_openid'] = $data['open_id'];
        }

        empty($where) && empty('条件异常:');
        $model = self::where($where)->find();
        empty($model) && exception('用户未注册',-2);

        return $model;

    }


    /**
     * 用户登录
     * @param string $account 登录帐号
     * @param string $mode_str 模式密码/验证码
     * @param int $mode 登录模式 0密码  1验证码
     * @param array $php_input 提交数据
     * @throws
     * @return self
     * */
    public static function handleLogin($account,$mode_str,$mode=0,array $php_input=[])
    {
        empty($account) && exception('请输入手机号');
        empty($mode_str) && exception('请输入'.($mode==1?'验证码':'密码'));
        $auth_info = empty($php_input['auth_info'])?'':json_decode($php_input['auth_info'],true);

        $model = self::where(['phone'=>$account])->find();

        $data = [];
        if(!empty($auth_info) && isset($auth_info['mode']) && isset($auth_info['open_id']) && isset($auth_info['access_token'])){
            //绑定第三方信息
            if($auth_info['mode']=='wechat'){
                $data['wx_openid'] = $auth_info['open_id'];
                $third_update['wx_openid']='';
            }elseif ($auth_info['mode']=='qq'){
                $data['qq_openid'] = $auth_info['open_id'];
                $third_update['qq_openid']='';
            }elseif ($auth_info['mode']=='weibo'){
                $data['wb_openid'] = $auth_info['open_id'];
                $third_update['wb_openid']='';
            }
        }

        if($mode==1){
            //验证码登录
            Sms::validVerify(0,$account,$mode_str);
            if(empty($model)){
                //创建用户
                $model = new self();
                //绑定手机号码
                $data['phone'] = $account;
                //邀请码
                !empty($php_input['req_code']) && $data['req_code'] = $php_input['req_code'];

                if(!empty($auth_info) && isset($auth_info['mode']) && isset($auth_info['open_id']) && isset($auth_info['access_token'])){
                    //新注册使用第三方基本信息
                    try{
                        if($auth_info['mode']=='wechat'){
                            $auth_user_info = \app\common\service\third\OpenWx::actToUserInfo($auth_info['access_token'],$auth_info['open_id']);
                            $data['name'] = $auth_user_info['nickname'];
                            $data['face'] = $auth_user_info['headimgurl'];
                            $data['sex'] = $auth_user_info['sex']==1?1:($auth_user_info['sex']==2?2:0); //普通用户性别，1为男性，2为女性
                        }elseif ($auth_info['mode']=='qq'){
//                            \app\common\service\third\QQ::actToUserInfo($auth_info['access_token'],$auth_info['open_id']);
                        }elseif ($auth_info['mode']=='weibo'){
                            $auth_user_info = \app\common\service\third\Weibo::actToUserInfo($auth_info['access_token'],$auth_info['open_id']);

                            $data['name'] = $auth_user_info['name'];
                            $data['face'] = $auth_user_info['avatar_large'];
                            $data['sex'] = $auth_user_info['gender']=='m'?1:($auth_user_info['gender']=='f'?2:0); //性别，m：男、f：女、n：未知
                        }
                        trace('auth_user_info:'.json_encode($auth_user_info));
                    }catch (\Exception $e){
                        trace('auth_user_info_error:'.$e->getMessage());
                    }

                }
            }

        }else{
            //帐号密码
            empty($model) && exception('用户名或密码错误');
            $model['password'] != self::generatePwd($mode_str,$model['salt']) && exception('用户名或密码错误.');
        }
        if(!empty($data)){
            //赋值数据
            foreach ($data as $key=>$vo){
                $model->setAttr($key,$vo);
            }
            $bool = $model->save();
            empty($bool) && exception('更新失败');


        }
        //模型所有数据
        $model_data = $model->getData();
        isset($model_data['status']) && $model->getAttr('status') !=1 && exception('非正常状态,无法进行登录');

        //移除其它用户绑定的第三方信息
        !empty($third_update) && self::update($third_update,[['id','<>',$model->id]]);



        //验证成功
        return $model;
    }
    /**
     * 用户密码
     * @param string $password 输入密码
     * @param string $salt 密码盐
     * @return string
     * */
    public static function generatePwd($password,$salt)
    {
        return md5($password.md5($password.$salt).$salt);
    }
    /**
     * 生成user_token
     * @return string
     * */
    public function generateUserToken()
    {
        $time = time();
        $access_token = $this->id.'.'.$time.'.'.rand(10000,9999).'.'.self::generateSign($this->id,$time);
        return $access_token;
    }
    /**
     * 验证user_token
     * @param string $user_token
     * @return bool|array
     * */
    public static function validUserToken($user_token)
    {
        if(empty($user_token)){
            return false;
        }
        $arr = explode('.',$user_token);
        $arr_len = count($arr);
        if($arr_len!=4){
            return false;
        }
        if($arr[$arr_len-1] !=self::generateSign($arr[0],$arr[1])){
            return false;
        }
        return $arr;

    }


    //生成token签名
    protected static function generateSign($user_id,$time)
    {
        return md5($user_id.md5($time));
    }

    /**
     * 用户注册
     * @param array $data  帐号
     * @param Validate $validate
     * @throws
     * @return self
     * */
    public static function handleReg(array $data=[],Validate $validate=null)
    {
        //场景验证
        if ($validate && !$validate->check($data)) {
            exception($validate->getError());
        }
        //清空数据
        unset($data['money'], $data['raise_num']);
        $model = new self();
        $model->data($data);
        if($data['phone']){
            if($model->where(['phone'=>$data['phone']])->count()) exception('手机号已被注册！');
        }
        !empty($data['password']) && $model->password = $data['password'];
        $bool = $model->save();
        !$bool && exception('注册异常');
        return $model;


    }

    /**
     * 记录用户积分
     * @param int $num 用户积分
     * @param int $type 交易类型
     * @param string $intro 获得说明
     * */
    public function recordRaise($num,$type=0,$intro='')
    {
        $bool = $num>0?$this->setInc('raise_num',$num):$this->setDec('raise_num',$num);
        //绑定日志事件
        $this->c_raise_type=$type;
        $this->c_raise_num=$num;
        $this->c_raise_intro=$intro;
        $this->trigger('raise_logs');
    }

    /**
     * 忘记密码
     * @param array $data  数据
     * @param Validate $validate 验证
     * @throws
     * @return self
     * */
    public static function handleForget(array $data=[],Validate $validate=null)
    {
        //场景验证
        if ($validate && !$validate->check($data)) {
            exception($validate->getError());
        }
        $phone = empty($data['phone'])?'':$data['phone'];
        $model = self::where(['phone'=>$phone])->find();
        empty($model) && exception('手机号未注册');
        $model->password = $data['password'];
        $bool = $model->save();
        !$bool && exception('保存异常');
        return $model;
    }

    /**
     * 用户关注
     * @param int $follow_user_id
     * @throws
     * @return self
     * */
    public function followUser($follow_user_id)
    {
        empty($follow_user_id) && exception('请求信息错误');
        //验证用户是否关注
        $follow_model = self::get($follow_user_id);
        empty($follow_model) && exception('用户不存在');

        $model = UsersFollow::where(['uid'=>$this->id,'f_uid'=>$follow_user_id])->find();
        if(empty($model)){
            $model = new UsersFollow();
            //未关注过
            $model->data([
                'follow_time' => date('Y-m-d H:i:s'),
                'uid' => $this->id,
                'f_uid' => $follow_user_id,
            ]);
        }else{
            //已关注过
            $model->follow_time = empty($model->follow_time)?date('Y-m-d H:i:s'):null;
        }
        $model->save();
        return $model;
    }

    /**
     * 好友申请
     * @param int $follow_user_id
     * @throws
     * @return self
     * */
    public function friendReq($follow_user_id)
    {
        empty($follow_user_id) && exception('请求信息错误');
        $follow_user_id==$this->id && exception('申请对象不能是自己');
        //验证用户是否关注
        $follow_model = self::get($follow_user_id);
        empty($follow_model) && exception('用户不存在');
        //申请数据
        $model = UsersFriend::withTrashed()->whereOr([
            [['uid','=',$this->id],['f_uid','=',$follow_user_id]],
            [['f_uid','=',$this->id],['uid','=',$follow_user_id]],
        ])->find();

        if(empty($model)){
            $model = new UsersFriend();
            $model->data([
                'follow_time' => date('Y-m-d H:i:s'),
                'uid' => $this->id,
                'f_uid' => $follow_user_id,
                'status'=>0,//申请状态
            ]);
        } else {
            if($model['status']) exception('对方已是你的好友');
            if(empty($model['delete_time'])) exception('已申请，等待对方通过');

            $model->status = 0;
            $model->restore();
        }
        $model->save();


        return $model;
    }

    /**
     * 通过申请或拒绝
     * @param int $id 好友用户id
     * @param int $state 通过状态
     * @throws
     * @return self
     * */
    public function friendReqPassOrRefuse($id,$state=1)
    {
        empty($id) && exception('请求参数异常');
        //验证用户是否关注
        $model = UsersFriend::where(['id'=>$id,'status'=>0])->find();
        empty($model) && exception('申请记录不存在');
        !empty($model['status']) && exception('对方已是你的好友');
        //好友状态通过
        if($state==1){
            //通过
            $model->status = 1;
            $model->save();
        }else{
            //拒绝
            $model->delete();
        }

        return $model;
    }

    /**
     * 修改用户信息
     * @param array $data
     * @throws
     * */
    public function modInfo(array $data = [])
    {
        $data = array_filter($data);
        //无修改内容直接返回
        if(empty($data)){ return;}

        $this->data($data,true);
        //限制某些字段无法修改
//        isset($data['birthday']) && $this->birthday =$data['birthday'];
        $this->readonly(['raise_num','money','phone'])->save();
    }

    /**
     * 删除用户标签
     * @param int index
     * @throws
     **/
    public function labelDel($index)
    {
        !is_numeric($index) && exception('参数异常:index');
        $label = $this->label;
        unset($label[$index]);
        $this->label = $label;
        $this->save();
    }
    /**
     * 新增用户标签
     * @param int index
     * @throws
     **/
    public function labelAdd($name)
    {
        empty($name) && exception('标签名不能为空');
        if(strpos($name,',')!==false){
            $name = explode(',',$name);
        }elseif(!is_array($name)){
            $name = [$name];
        }
        $name = array_filter($name);
        $label = $this->label;
        foreach ($name as $vo){
            array_push($label,$vo);
        }
        $this->label = $label;
        $this->save();
    }

    /**
     * 我的好友
     * */
    public static function friends($user_id)
    {
        $friend_field = 'id,name,face,left(py,1) as flag';
        $data = [];
        UsersFriend::whereRaw('status = 1 and (`uid`=:uid or `f_uid`=:f_uid)',['uid'=>$user_id,'f_uid'=>$user_id])
            ->chunk(100,function($user_friends)use($user_id,$friend_field , &$data){
            $friend_ids = [];
            foreach ($user_friends as $vo){
                $f_uid = $user_id!=$vo['f_uid']?$vo['f_uid']:$vo['uid'];
                array_push($friend_ids,$f_uid);
            }

            $users = Users::field($friend_field)->where([['id','in',$friend_ids]])->select()->toArray();

            $data = array_merge($data,$users);
        });
        return $data;
    }

    /**
     * 列表数据
     * @param array $php_input 数据
     * @param int $user_id 用户id
     * @throws
     * @return \think\Paginator
     * */
    public static function getReqList($user_id,array $php_input=[])
    {
        $where = [];

        $where[]=['r_uid1','=',$user_id];
        $list = self::where($where)->order('id desc')->paginate();
        return $list;
    }


    //我是否关注
    public function linkHasFollow()
    {
        return $this->hasOne('UsersFollow','f_uid')->whereNotNull('follow_time');
    }

    //我邀请的用户
    public function linkReqUser()
    {
        return $this->hasMany('Users','r_uid1');
    }
}