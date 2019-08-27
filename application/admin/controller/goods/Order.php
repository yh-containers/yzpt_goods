<?php
namespace app\admin\controller\goods;

use app\admin\controller\Common;

class Order extends Common
{
	public function index(){
        $list = \app\common\model\Order::with('ownAddrs')->order('create_time asc')->paginate();
        foreach ($list as &$v){
            $state = \app\common\model\Order::getPropInfo('fields_mobile_step', $v['step_flow']);
            $v['status'] = is_array($state['name']) ? $state['name'][$v['status']] : $state['name'];
        }
//        print_r($list);
        $page = $list->render();
        return view('index',['list'=>$list,'page'=>$page]);
    }
    //订单详情
    public function orderdetail(){
        $model = new \app\common\model\Order();
        if($this->request->isAjax()){
            $res = ['code'=>0,'msg'=>''];
            $oid = $this->request->param('id');
            $handle = $this->request->param('type');
            $u = $model->field('uid')->get($oid);
            try{
                \think\Db::startTrans();
                if($handle == 'cancel'){//取消订单
                    $model->cancelOrder($u['uid'],$oid);
                    $res['msg'] = '已取消';
                }else if($handle == 'del'){//删除订单
                    $model->actionDel(['id'=>$oid]);
                    $res['msg'] = '已提醒';
                }else if($handle == 'sure-pay'){//确认付款
                    $model->orderPay($u['uid'],$oid);
                    $res['msg'] = '已确认';
                }else if($handle == 'send'){//发货
                    $model->orderSend($u['uid'],$oid);
                    $res['msg'] = '已确认';
                }
                \think\Db::commit();
            }catch (\Exception $e){
                \think\Db::rollback();
                $res['msg'] = $e->getMessage();
                return json($res);
            }
            return json($res);
        }
        $id  = $this->request->param('id');
        $data = $model->with('ownAddrs,ownGoods')->get($id);
        $step = \app\common\model\Order::getPropInfo('fields_step', $data['step_flow']);
        $handle = \app\common\model\Order::getPropInfo($step['prop_func'],$data[$step['field']]);
//        print_r($handle);
//        $state = \app\common\model\Order::getPropInfo('fields_mobile_step', $data['step_flow']);
        $data['status'] = $handle['name'];
//        print_r($data['own_addr']);
        return view('order_detail',['data'=>$data,'handle'=>$handle['m_handle']]);
    }
}