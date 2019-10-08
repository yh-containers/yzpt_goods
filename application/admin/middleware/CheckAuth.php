<?php
namespace app\admin\middleware;

class CheckAuth
{
    /**
     * @var $request \think\Request
     * @var $next \Closure
     * @return \Closure
     * */
    public function handle($request, \Closure $next)
    {
        $current_action = $request->controller(true).'/'.$request->action(true);



        return $next($request);
    }
}