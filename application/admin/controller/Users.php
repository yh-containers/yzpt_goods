<?php
namespace app\admin\controller;



class Users extends Common
{

    public function index()
    {
        $platform_state = input('platform_state','0','intval');
        $keyword = input('keyword','','trim');
        $where=[];
        !empty($keyword) && $where[]=['name|phone','like','%'.$keyword.'%'];
        if($platform_state==1){
            //平台帐号
            $where[]=['is_platform','=',1];
        }
        $list = \app\common\model\Users::where($where)->paginate();
        // 获取分页显示
        $page = $list->render();
        return view('index',[
            'platform_state' => $platform_state,
            'keyword' => $keyword,
            'list' => $list,
            'page'=>$page
        ]);
    }

    public function add()
    {
        $id = input('id',0,'intval');

        if($this->request->isAjax()){
            $php_input = $this->request->param();
            if(empty($php_input['password']) && isset($php_input['password'])) unset($php_input['password']);

            $validate = new \app\common\validate\Users();
            $validate->scene('admin_opt');
            try{
                $model = new \app\common\model\Users();
                $model->actionAdd($php_input,$validate);//调用BaseModel中封装的添加/更新操作
            }catch (\Exception $e){
                return json(['code'=>0,'msg'=>$e->getMessage()]);
            }
            return json(['code'=>1,'msg'=>'操作成功']);
        }

        $model = \app\common\model\Users::get($id);

        return view('add',[
            'model'=>$model,
        ]);
    }

    //用户详情
    public function detail()
    {
        $id = input('id',0,'intval');

        $model = \app\common\model\Users::get($id);
        //获取养分日志
        $raise_logs = \app\common\model\UsersRaiseLogs::where('uid',$model['id'])->order('id desc')->limit(5)->select();
        //余额日志
        $money_logs = \app\common\model\UsersMoneyLog::where('uid',$model['id'])->order('id desc')->limit(5)->select();
        //邀请
        $req_info = \app\common\model\Users::where('r_uid1',$model['id'])->select();
        return view('detail',[
            'model'=>$model,
            'raise_logs'=>$raise_logs,
            'money_logs'=>$money_logs,
            'req_info'=>$req_info,

        ]);
    }

    //用户养分日志
    public function raiseNumLogs()
    {
        $id = input('id',0,'intval');

        $model = \app\common\model\Users::get($id);
        //获取养分日志
        $list = \app\common\model\UsersRaiseLogs::where('uid',$model['id'])->order('id desc')->paginate();
        return view('raiseNumLogs',[
            'model'=>$model,
            'list'=>$list,
            'page'=>$list->render()
        ]);
    }

    //用户余额日志
    public function moneyLogs()
    {
        $id = input('id',0,'intval');

        $model = \app\common\model\Users::get($id);
        //获取养分日志
        $list = \app\common\model\UsersMoneyLog::where('uid',$model['id'])->order('id desc')->paginate();
        return view('moneyLogs',[
            'model'=>$model,
            'list'=>$list,
            'page'=>$list->render()
        ]);
    }
}
