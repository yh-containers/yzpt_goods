<?php
namespace app\admin\widget;


class Components
{
    //导航
    public function menu($current_index=[])
    {

        $menu = \app\common\model\SysNavigation::with(['linkChild.linkChild'])->where(['pid'=>0,'status'=>1])->order('sort asc')->select();
//        dump($menu);exit;
        //当前点击的url
        $current_url = strtolower(request()->controller().'/'.request()->action());
        $parent_info = \app\common\model\SysNavigation::with(['linkParent.linkParent.linkParent'])->where(['url'=>$current_url])->order('sort asc')->find();
        !empty($parent_info) && array_unshift($current_index,$parent_info['id']);
        if(!empty($parent_info['link_parent'])){
            array_unshift($current_index,$parent_info['link_parent']['id']);
            if(!empty($parent_info['link_parent']['link_parent'])){
                array_unshift($current_index,$parent_info['link_parent']['link_parent']['id']);
            }
            if(!empty($parent_info['link_parent']['link_parent']['link_parent'])){
                array_unshift($current_index,$parent_info['link_parent']['link_parent']['link_parent']['id']);
            }
        }
//        dump($current_index);exit;
        return view('/common/menu',[
            'menu'=>$menu,
            'current_index'=>$current_index
        ])->getContent();
    }

    //平台账号
    public function platformUserSelect($release_uid=0)
    {
        $users = \app\common\model\Users::where(function($query){
            $query->where(['is_platform'=>1,'status'=>1]);
        })->whereOr(['id'=>$release_uid])->select();
        return view('/common/platformUserSelect',[
            'users'=>$users,
            'release_uid'=>$release_uid
        ])->getContent();
    }
}