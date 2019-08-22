<?php
namespace app\api\controller;



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
            ->order('sort asc')->paginate()
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
        //用户信息
        $user_model = \app\common\model\Users::get($id);

        $data=[];
        //用户资料
        $data['info']=[
            'id' => $user_model['id'],
            'num' => (string)$user_model['num'],
            'name' => (string)$user_model['name'],
            'face' => (string)$user_model['face'],
            'sex' => (string)$user_model['sex'],
            'sex_name' => empty($user_model)?'':\app\common\model\Users::getPropInfo('fields_sex',$user_model['sex']),
            'intro' => (string)$user_model['intro'],
            'address' => (string)$user_model['address'],
            'birthday' => empty($user_model)?'':$user_model->birthday,
        ];

        //用户点赞
        if($on_praise_num){
            //粉丝
            $fans_num = (int)\app\common\model\UsersFollow::where(['f_uid'=>$this->user_id])->count();
            //关注对象
            $follow_num = (int)\app\common\model\UsersFollow::where(['uid'=>$this->user_id])->count();
            $data['praise']=['num'=>(int)$user_model['praise_num'],'follow'=>$follow_num,'fans'=>$fans_num];
        }
        //身体状态
        if($on_user_body){
            $data['body']=['star'=>0];
        }
        //身体状态
        if($on_user_label){
            $label = $user_model['label'];
            $label_data = [];
            foreach ($label as $key=>$vo){
                $label_data[] = [
                    'index' => $key,
                    'name' => $vo,
                ];
            }
            $data['label']=$label_data;
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

}