<?php
namespace app\admin\controller;

class System extends Common
{
    public function roles()
    {
        $list = \app\common\model\SysRole::paginate();
        // 获取分页显示
        $page = $list->render();
        return view('roles',[
            'list' => $list,'page'=>$page
        ]);
    }

    //
    public function rolesAdd()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\SysRole();

        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            if(empty($php_input['password']) && isset($php_input['password'])) unset($php_input['password']);

            $validate = new \app\common\validate\SysRole();
            return $model->actionAdd($php_input,$validate);
        }

        $model = $model->get($id);

        return view('rolesAdd',[
            'model' => $model
        ]);

    }

    //删除数据
    public function rolesDel()
    {
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\SysRole();
        return $model->actionDel(['id'=>$id]);
    }

    public function manager()
    {
        $list = \app\common\model\SysManager::with('linkRole')->paginate();
        // 获取分页显示
        $page = $list->render();
        return view('manager',[
            'list' => $list,'page'=>$page
        ]);
    }

    //
    public function managerAdd()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\SysManager();

        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            if(empty($php_input['password']) && isset($php_input['password'])) unset($php_input['password']);

            $validate = new \app\common\validate\SysManager();
            return $model->actionAdd($php_input,$validate);
        }
        $model = $model->get($id);
        //查询角色
        $role_list = \app\common\model\SysRole::where(['status'=>1])->select();
        return view('managerAdd',[
            'model' => $model,
            'role_list' => $role_list,
        ]);

    }

    //删除数据
    public function managerDel()
    {
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\SysManager();
        return $model->actionDel(['id'=>$id]);
    }


    //系统设置
    public function setting()
    {
        $normal_content = \app\common\model\SysSetting::getContent('normal');
        $normal_content = empty($normal_content)?[]:json_decode($normal_content,true);
        $other_content = \app\common\model\SysSetting::getContent('other');
        $other_content = empty($other_content)?[]:json_decode($other_content,true);
        return view('setting',[
            'normal_content' => $normal_content,
            'other_content' => $other_content,
        ]);
    }

    //系统设置
    public function settingSave()
    {
        $type = $this->request->param('type');
        $content = $this->request->param('content');
        if(is_array($content)){
            if(key($content)===0){
                //数组
                $content = implode(',',$content);
            }else{
                //键值对
                $content = json_encode($content);
            }
        }
        $bool = \app\common\model\SysSetting::setContent($type, $content);
        return json(['code'=>(int)$bool,'msg'=>$bool?'操作成功':'操作失败']);
    }


    //注册协议
    public function regProtocol()
    {
        return view('regProtocol',[
            'content' =>  $this->_allProtocol('reg_protocol'),
        ]);
    }

    //查询协议
    private function _allProtocol($type)
    {
        return $content = \app\common\model\SysSetting::getContent($type);
    }

}