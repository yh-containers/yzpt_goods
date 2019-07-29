<?php
namespace app\admin\controller;

use \app\common\model\SysManager;
use think\Request;

class Ad extends Common
{


    //轮播图设置
    public function image()
    {
        $t_id = input('t_id','');
        $where=[];
        is_numeric($t_id) && $where[]=['type','=',$t_id];
        $list = \app\common\model\Ad::where($where)->order('sort asc')->paginate();
        // 获取分页显示
        $page = $list->render();
        return view('image',[
            't_id' => $t_id,
            'list' => $list,
            'page'=>$page
        ]);
    }

    //轮播图新增/编辑
    public function imageAdd()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\Ad();

        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            $validate = new \app\common\validate\Ad();
            return $model->actionAdd($php_input,$validate);
        }
        $model = $model->get($id);

        return view('imageAdd',[
            'model' => $model,

        ]);

    }

    //删除数据
    public function imageDel()
    {
        $id = $this->request->param('id',0,'int');
        $model = new \app\common\model\Ad();
        return $model->actionDel(['id'=>$id]);
    }


    public function message()
    {
        return view('message',[

        ]);
    }


}
