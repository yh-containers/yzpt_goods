<?php
namespace app\admin\middleware;

class CheckAuth
{
    //忽略权限认证的动作
    protected $ignore_action=['index/index','index/login','index/logout','admin/captcha','system/settingsave','article/showcomments'];
    protected $is_spe = 0; //特殊权限特殊处理
    protected $rid = 0; //角色id
    protected $roles_action = [];
    /**
     * @var $request \think\Request
     * @var $next \Closure
     * @throws
     * @return \Closure
     * */
    public function handle($request, \Closure $next)
    {
        if(session('?user_info')){
            $user_info = session('user_info');
            $this->is_spe = isset($user_info['is_spe'])?$user_info['is_spe']:0;
            $this->rid = isset($user_info['rid'])?$user_info['rid']:0;
            //检测是否需要验证权限
            if(!$this->_check_action($request)){
                if($request->isAjax()){
                    return json(['code'=>0,'msg'=>'您没有权限操作']);
                }else{
                    return view('index/noauth',['msg'=>'您没有权限访问']);
                }
            }
        }

        return $next($request);
    }
    /**
     * @var $request \think\Request
     * @return bool
     * */
    private function _check_action($request)
    {
        $current_action = $request->controller(true).'/'.$request->action(true);

        if(!$this->is_spe && !in_array($current_action,$this->ignore_action)){
            $node = \app\common\model\SysRole::where(['id'=>$this->rid,'status'=>1])->value('node');
            $node = strtolower($node);
            $node_arr = explode(',',$node);

            //获取用户角色权限
            if(empty($node) || !in_array($current_action,$node_arr)){
                return false;
            }

        }
        return true;
    }
}