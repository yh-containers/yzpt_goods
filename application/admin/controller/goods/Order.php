<?php
namespace app\admin\controller\goods;

use app\admin\controller\Common;

class Order extends Common
{
	public function index(){
        $where = [];
	    $t_id = $this->request->param('t_id');
	    if(is_numeric($t_id)){
            $where['step_flow'] = $t_id;
        }
        $list = \app\common\model\Order::with('ownAddrs')->where('status!=5')->where($where)->order('create_time desc')->paginate();
        foreach ($list as &$v){
            $state = \app\common\model\Order::getPropInfo('fields_mobile_step', $v['step_flow']);
            $v['status'] = is_array($state['name']) ? $state['name'][$v['status']] : $state['name'];
        }
//        print_r($list);
        $page = $list->render();
        return view('index',['list'=>$list,'page'=>$page,'t_id'=>$t_id]);
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
                    $res['msg'] = '已删除';
                }else if($handle == 'sure-pay'){//确认付款
                    $model->orderPay($u['uid'],$oid);
                    $res['msg'] = '已确认';
                }else if($handle == 'send'){//发货
                    $model->orderSend($u['uid'],$oid);
                    $addinfo = array();
                    $addinfo['oid'] = $oid;
                    $addinfo['no'] = $this->request->param('no');
                    $addinfo['company'] = $this->request->param('company');
                    $addinfo['money'] = $this->request->param('money');
                    $wlModel = new \app\common\model\OrderLogistics();
                    $wlModel->actionAdd($addinfo);
                    $res['msg'] = '已确认';
                    $this->addToMsg($u['uid'],$oid,'您的订单已发货，快递员正在派送中~');
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

    public function return_order(){
        //$list = \app\common\model\OrderReturn::with('ownUser,ownOrder')->order('create_time desc')
        $list = \app\common\model\Order::with('ownAddrs,ownReturn')->where('status=5')->order('update_time desc')->paginate();
        $page = $list->render();
        return view('return_order',['list'=>$list,'page'=>$page]);
    }
    //退款单详情
    public function returndetail(){
        $model = new \app\common\model\Order();
        if($this->request->isAjax()){
            $res = ['code'=>0,'msg'=>''];
            $returnOrderModel = new \app\common\model\OrderReturn();
            $rid  = $this->request->param('rid');
            $state  = $this->request->param('state');
            try{
                \think\Db::startTrans();
                $arr = $returnOrderModel->find($rid);
                $returnOrderModel->where(['id'=>$rid])->update(['state'=>$state]);
                if($state==1){
                    $this->addToMsg($arr['uid'],$arr['oid'],'您的退款单商家已同意');
                }
                \think\Db::commit();
            }catch (\Exception $e){
                \think\Db::rollback();
                $res['msg'] = $e->getMessage();
                return json($res);
            }
            $res['code'] = 1;
            $res['msg'] = '操作成功';
            return json($res);
        }
        $id  = $this->request->param('id');
        $data = $model->with('ownGoods,ownReturn,ownAddrs')->get($id);
        if(empty($data['own_return'])){
            $this->error('该订单未填写退款单');
        }
        return view('return_detail',['data'=>$data]);
    }
    protected function  addToMsg($uid,$oid,$content){
	    $addinfo = array();
	    $addinfo['uid'] = $uid;
	    $addinfo['oid'] = $oid;
	    $addinfo['content'] = $content;
	    $model = new \app\common\model\OrderMsg();
        $model->actionAdd($addinfo);
    }
}