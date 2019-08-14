<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

// 加载基础文件
require __DIR__ . '/thinkphp/base.php';

// 支持事先使用静态方法设置Request对象和Config对象

if(isMobile()){
	define('VIEW_PATH', './application/index/view/mobile/');
}else{
	define('VIEW_PATH', './application/index/view/pc/');
}

// 执行应用并响应
Container::get('app')->bind('index')->run()->send();


function isMobile()
{
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $is_pc = (strpos($agent, 'windows nt')) ? true : false;
    $is_mac = (strpos($agent, 'mac os')) ? true : false;
    $is_iphone = (strpos($agent, 'iphone')) ? true : false;
    $is_android = (strpos($agent, 'android')) ? true : false;
    $is_ipad = (strpos($agent, 'ipad')) ? true : false;
    if($is_pc){
        return  false;
    }
    if($is_mac){
        return  true;
    }
    if($is_iphone){
        return  true;
    }
    if($is_android){
        return  true;
    }
    if($is_ipad){
        return  true;
    }
}