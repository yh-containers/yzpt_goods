<?php
namespace app\admin\controller\goods;

use app\admin\controller\Common;

class Index extends Common
{
    public function index()
    {
        return view('index');
    }
    public function test()
    {
        return 'goods/test';
    }
}