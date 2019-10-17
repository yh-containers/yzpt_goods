<?php
namespace app\index\widget;


class Components
{
    public function headerTop()
    {
        $cartCount = 0;
        if(session('uid')){
            $cartCount = \app\common\model\Cart::where(['uid'=>session('uid'),'type'=>0])->sum('num');
        }
        return view('common/header_top',['cart_count'=>$cartCount])->getContent();
    }

    public function headerCenter($show_search=1,$step)
    {
        $cartCount = 0;
        if(session('uid')){
            $cartCount = \app\common\model\Cart::where(['uid'=>session('uid'),'type'=>0])->sum('num');
        }
        return view('common/header_center',['show_search'=>$show_search,'step'=>$step,'cart_count'=>$cartCount])->getContent();
    }

    public function headerCate($is_show=false)
    {
        $list = \app\common\model\GoodsCategory::with(['linkChildCate'=>function($query){
            return $query->where(['status'=>1])->field('id,cate_name,pid')->with('linkChildCate');
        }])->where(['status'=>1,'pid'=>0])->field('id,cate_name,pid,icon')->order('sort asc')->select();
        return view('common/header_cate',[
            'is_show'=>$is_show,
            'cate_list'=>$list
        ])->getContent();
    }

    public function footer()
    {
        $list = \app\common\model\BottomColumn::with(['ownColumns'=>function($query){
            return $query->where('status=1');
        }])->where('status=1 and pid=0')->select();
        return view('common/footer',['article'=>$list])->getContent();
    }
    public function mobile_footer($is_active = 1)
    {
        return view('common/footer',['is_active'=>$is_active])->getContent();
    }
}