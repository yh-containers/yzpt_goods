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
    protected $insert=['face','name','status'=>1];

    //养分变更自从
    protected $c_raise_type=0;//类型
    protected $c_raise_num=0;//数量
    protected $c_raise_intro='';//介绍

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
    protected function getFaceAttr($value)
    {
        return self::handleFile($value);
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
        }
        return $value;
    }


    //自动完成头像
    protected function setNameAttr($value,$data)
    {
//        if($value){
//            return $value;
//        }
        $name = $value?$value:'用户昵称';
        $phone = empty($data['phone'])?'':$data['phone'];
        !empty($name) && !empty($phone) && $name = substr($phone,-4).'用户';

        if(!empty($name)){
            $pinyin =new \Overtrue\Pinyin\Pinyin();
            $py = $pinyin->permalink($name,'-',PINYIN_NAME);
            $this->setAttr('py',strtoupper($py));
        }
        return $name;
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


    public static function init()
    {
        //养分日志记录
        self::event('raise_logs', function ($model) {
            UsersRaiseLogs::recordLog($model['id'],$model->c_raise_num,$model->c_raise_type,$model->c_raise_intro);
        });
    }


    /**
     * 用户登录
     * @param string $account 登录帐号
     * @param string $mode_str 模式密码/验证码
     * @param int $mode 登录模式 0密码  1验证码
     * @throws
     * @return self
     * */
    public static function handleLogin($account,$mode_str,$mode=0)
    {
        empty($account) && exception('请输入手机号');
        empty($mode_str) && exception('请输入'.($mode==1?'验证码':'密码'));
        $model = self::where(['phone'=>$account])->find();
        if($mode==1){
            //验证码登录
            //验证验证码
            Sms::validVerify(0,$account,$mode_str);

            if(empty($model)){
                //创建用户
                $model = new self();
                $model->data([
                    'phone' => $account,
                ]);
                $bool = $model->save();
                empty($bool) && exception('用户创建失败');
            }

        }else{
            //帐号密码
            empty($model) && exception('用户名或密码错误');
            $model['password'] != self::generatePwd($mode_str,$model['salt']) && exception('用户名或密码错误.');
        }

        $model->getAttr('status') !=1 && exception('非正常状态,无法进行登录');

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

    //我是否关注
    public function linkHasFollow()
    {
        return $this->hasOne('UsersFollow','f_uid')->whereNotNull('follow_time');
    }
}