<?php
namespace Admin\Controller;
use Common\Model\OrderModel;
use Think\Controller;

/**
 * Class IndentController
 * @package Admin\Controller
 * @property OrderModel $orderModel
 * @property \AliPay $aliPay
 */
class IndentController extends Controller{
    private $orderModel;
    private $aliPay;

    public function _initialize(){
        $this->orderModel = D("Order");
//        Vendor('alipay.aliPayApi');
        $this->aliPay = new \AliPay();
    }
    /**
     * 订单列表
     */
    public function orderlist(){
        $status=I('get.status');
        $this->assign('status',$status);
        $this->display();
    }

    /**
     *  设置订单退款状态
     */
    public function setRefundType(){
        $data = [
            "order_id"=>$_REQUEST['order_id'],
            "refund_type" => $_REQUEST['refund_type']
        ];
        try {
            $this->orderModel->updateOrder($data);
            response();
        } catch (\Exception $e) {
            response([],$e->getMessage(),501);
        }
    }
	
	 /**
     * 申请退款订单列表
     */
    public function tuiorderlist(){
        $status=I('get.status');
        $type=I('get.type')?I('get.type'):'';
		$where="t_apply_refund.status=0";
		$where.=empty($_GET['type'])?"":" and t_order.type=".(INT)($_GET['type']-1);
		$field="t_apply_refund.*,t_order.user_id,t_order.price,t_order.pay_type,t_order.order_no,t_order.trade_no,t_order.san_money,
		t_order.batch_no,t_order.refund_details,t_order.last_update,
		t_order.create_time as order_create_time,t_order.type as order_type,t_order.status as order_status";
		$order='t_apply_refund.create_time desc';
		$join='LEFT JOIN t_order ON t_apply_refund.order_id=t_order.order_id';
		$Modle=M('apply_refund');
        $refundOrder=$Modle->field($field)->order($order)->join($join)->where($where)->select();
		//dump($Modle->getLastSql());
        if(session('admin_key_auth')==3){
            $refundOrder=M('order')->order('create_time desc')->where(array('status'=>$status))->select();
            $courtArr=M('court')->where('venue_id='.session('admin_key_venue_id'))->select();
            $courtArr=array_column($courtArr,'court_id');
        
            foreach($refundOrder as $key=>$val){
                $val['friendOrder']=M('friendly_order')->where('order_id='.$val['order_id'])->select();
                $refundOrder[$key]=$val;
            }
            foreach($refundOrder as $key=>$val){
                if(empty($val['friendOrder'])){
                    array_splice($refundOrder, $key,1);
                }
            }
           
        }else{
            $refundOrder=M('order')->order('create_time desc')->where(array('status'=>$status))->select();
        }
        foreach($refundOrder as $key=>$val){
            $val['username']=M('user')->where('user_id='.$val['user_id'])->find();
            $val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
            $refundOrder[$key]=$val;
        }
	
        $this->assign('status',$status);
        $this->assign('type',$type);
        $this->assign('countRefund',count($refundOrder));
        $this->assign('refundOrder',$refundOrder);
        $this->display();
    }
	
	 /**
     * 已退款订单列表
     */
    /*public function endtuiorderlist(){
        $status=I('get.status');
		$where="t_apply_refund.status=3";
	    $where.=empty($_GET['type'])?"":" and t_order.type=".(INT)($_GET['type']-1);
		$field="t_apply_refund.*,t_order.user_id,t_order.price,t_order.pay_type,t_order.order_no,t_order.trade_no,t_order.san_money,
		t_order.batch_no,t_order.refund_details,t_order.last_update,
		t_order.create_time as order_create_time,t_order.type as order_type,t_order.status as order_status";
		$order='t_apply_refund.create_time desc';
		$join='t_order ON t_apply_refund.order_id=t_order.order_id';
		$Modle=M('apply_refund');
        $refundOrder=$Modle->field($field)->order($order)->join($join)->where($where)->select();
        foreach($refundOrder as $key=>$val){
            $val['username']=M('user')->where('user_id='.$val['user_id'])->find();
            $val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
            $refundOrder[$key]=$val;
        }

        $this->assign('status',$status);
        $this->assign('countRefund',count($refundOrder));
        $this->assign('refundOrder',$refundOrder);
        $this->display();
    }*/
    public function endtuiorderlist(){
        $status=I('get.status');
        $type=I('get.type');

        if($type==''){
            $where['status']=$status;
        }else{
            $where['status']=$status;
            $where['type']=$type;
        }

        $refundOrder=M('order')->where($where)->select();
        foreach($refundOrder as $key=>$val){
            $val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
            $val['username']=M('user')->where('user_id='.$val['user_id'])->find();
            $refundOrder[$key]=$val;
        }

        $this->assign('status',$status);
        $this->assign('type',$type);
        $this->assign('countRefund',count($refundOrder));
        $this->assign('refundOrder',$refundOrder);

        $this->display();
    }
    
    public function refundlist(){
        $type=I('get.type');
        $status=I('get.status')?I('get.status'):'';
        if($type==0){
            $type = array("in",[0,3]);
        }
        $where=array(
            'type'  =>$type,
            'status'=>$status,
        );
        if(session('admin_key_auth')==3){
            $refundMsg=M('order')->where($where)->select();
            foreach($refundMsg as $key=>$val){
                if($type==0){
                    $val['court']=M('qualifying')->join('t_court on t_court.court_id=t_qualifying.court_id')->find();
                    $val['create_time']=M('qualifying')->where('home_order_id='.$val['order_id'])->getField('create_time');
                    $val['referee']=M('referee')->where('id='.$val['court']['referee_id'])->find();
                    $val['ball_team']=M('ball_team')->where('ball_team_id='.$val['court']['home_team_id'])->find();
                    $val['start_time']=M('qualifying')->where('home_order_id='.$val['order_id'])->getField('start_time');
                    $val['end_time']=M('qualifying')->where('home_order_id='.$val['order_id'])->getField('end_time');
                }
                if($type==1){
                    $val['court']=M('friendly_order')->join('t_court on t_court.court_id=t_friendly_order.court_id')->where("order_id=%d",$val['order_id'])->find();
                    $val['create_time']=M('friendly_order')->where('order_id='.$val['order_id'])->getField('create_time');
                    $val['referee']=M('referee')->where('id='.(int)$val['court']['referee_id'])->find();
                    $val['ball_team']['name']=$val['court']['team_name'];
                    $val['start_time']=M('friendly_order')->where('order_id='.$val['order_id'])->getField('start_time');
                    $val['end_time']=M('friendly_order')->where('order_id='.$val['order_id'])->getField('end_time');
                }

                $refundMsg[$key]=$val;
            }
            $courtArr=M('court')->where('venue_id='.session('admin_key_venue_id'))->select();
            $courtArr=array_column($courtArr,'court_id');
            
            foreach($refundMsg as $key=>$val){
                $val['friendOrder']=M('friendly_order')->where('order_id='.$val['order_id'])->select();

                $refundMsg[$key]=$val;
            }
            foreach($refundMsg as $key=>$val){
                if(empty($val['friendOrder'])){
                    array_splice($refundMsg, $key,1);
                }
            }
        }else{
            $refundMsg=M('order')->where($where)->select();
            foreach($refundMsg as $key=>$val){
                if($type==0){
                    $val['court']=M('qualifying')->join('t_court on t_court.court_id=t_qualifying.court_id')->find();
                    $val['referee']=M('referee')->where('id='.$val['court']['referee_id'])->find();
                    $val['ball_team']=M('ball_team')->where('ball_team_id='.$val['court']['home_team_id'])->find();
                    $val['start_time']=M('qualifying')->where('home_order_id='.$val['order_id'])->getField('start_time');
                    $val['end_time']=M('qualifying')->where('home_order_id='.$val['order_id'])->getField('end_time');
                }
                if($type==1){
                    $val['court']=M('friendly_order')->join('t_court on t_court.court_id=t_friendly_order.court_id')->where("order_id=".$val['order_id'])->find();
                    $val['referee']=M('referee')->where('id='.(int)$val['court']['referee_id'])->find();
                    $val['ball_team']['name']=$val['court']['team_name'];
                    $val['start_time']=M('friendly_order')->where('order_id='.$val['order_id'])->getField('start_time');
                    $val['end_time']=M('friendly_order')->where('order_id='.$val['order_id'])->getField('end_time');
                }

                $refundMsg[$key]=$val;
            }

        }

        foreach($refundMsg as $key=>$val){
            $val['username']=M('user')->where('user_id='.$val['user_id'])->find();
            if($type==2){
                $val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
            }else{
                $val['create_time']=date('Y-m-d H:i:s',$val['start_time']).'~'.date('H:i',$val['end_time']);
            }

            $refundMsg[$key]=$val;
        }

        $this->assign('type',$type);
        $this->assign('status',$status);
        $this->assign('refundMsg',$refundMsg);
        $this->assign('countRefund',count($refundMsg));
        $this->display();
    }

    /**
     *  订单列表
     */
    public function order_list(){
        $status = $_REQUEST['status'];
        $type = $_REQUEST['type'];
        $this->assign("status",$status);
        $this->assign("type",$type);
        $this->display();
    }

    /**
     *  退款订单
     */
    public function refund_list(){
        $status = $_REQUEST['status'];
        $type = $_REQUEST['type'];
        $this->assign("status",$status);
        $this->assign("type",$type);
        $this->display();
    }

    public function getOrderList(){
        $queryParams = [
            "limit" => $_REQUEST['limit'],
            "offset" => $_REQUEST['offset'],
            "sort" => $_REQUEST['sort'],
            "order" => $_REQUEST['order'],
            "start_time" => $_REQUEST['start_time'],
            "end_time" => $_REQUEST['end_time'],
            "order_id" => $_REQUEST['order_id'],
            "user_id" => $_REQUEST['user_id'],
            "type" => $_REQUEST['type'],
            "status" => $_REQUEST['status'],
            "refund_type" => $_REQUEST['refund_type'],
            "order_no" => $_REQUEST['order_no'],
            "pay_type" => $_REQUEST['pay_type'],
            "game_time" => $_REQUEST['game_time']
        ];

        if(session('admin_key_auth')==3){
            $venue_id = session('admin_key_venue_id');
            $queryParams['venue_id'] = $venue_id;
        }

        $option = intval($_REQUEST['option']);
        $search = $_REQUEST['search'];
        if(!empty($search)||$search==0){
            switch($option){
                case 0:             //标题
                    $queryParams['phone'] = $search;
                    break;
            }
        }

        $data = $this->orderModel->search($queryParams);
        formatRes($data,$_REQUEST['draw']);
//        var_dump($data);
        response($data);
    }
    
    public function returnColor($color){
        switch ($color != ''){
            case 'red':
              return  $color='红色';
            case 'white':
              return  $color='白色';
            case 'blue':
              return  $color='蓝色';
            case 'yellow':
              return  $color='黄色';
            case 'orange':
              return  $color='橙色';
            case 'pink':
              return  $color='粉色';
            case 'purple':
              return  $color='紫色';
            case 'green':
             return   $color='绿色';
            default:
                return null;
        }        
    }
    
    public function orderMsg(){
        $order_id=I('get.order_id');
        $type=I('get.type');
        
        if($type==0){
            $orderMsg=M('order')->where('order_id='.$order_id)->find();
            $orderMsg['create_time']=date('Y-m-d H:i:s',$orderMsg['create_time']);
            
            $qualifyingMsg=M('qualifying')->where('home_order_id='.$order_id)->find();
            
            $endTime=$qualifyingMsg['end_time'];
            
            $qualifyingMsg['home_color']=$this->returnColor($qualifyingMsg['home_color']);
            $qualifyingMsg['guest_color']=$this->returnColor($qualifyingMsg['guest_color']);
            $qualifyingMsg['start_time']=date('Y-m-d H:i:s',$qualifyingMsg['start_time']);
            $qualifyingMsg['end_time']=date('Y-m-d H:i:s',$qualifyingMsg['end_time']);

            if($qualifyingMsg['court_id'] == ''){
                echo "<script>alert('无效订单...');window.parent.location.reload();</script>";
                exit;
            }
            $courtMsg=M('court')->where('court_id='.$qualifyingMsg['court_id'])->find();

            if($qualifyingMsg['referee_id']==''){
                echo "<script>alert('无效订单...');window.parent.location.reload();</script>";
                exit;
            }
            $refereeMsg=M('referee')->where('id='.$qualifyingMsg['referee_id'])->find();
                       
            $homeTeamMsg=M('ball_team')->where('ball_team_id='.$qualifyingMsg['home_team_id'])->find();
            $homeTeamMsg['build_time']=date('Y-m-d H:i:s',$homeTeamMsg['build_time']);
            $homeTeamMsg['lead_name']=$homeTeamMsg['lead_name']?$homeTeamMsg['lead_name']:M('user')->where('user_id='.$homeTeamMsg['uid'])->getField('nickname');
            $homeTeamMsg['phone']=M('user')->where('user_id='.$homeTeamMsg['uid'])->getField('phone');
            $homeTeamUser=M('ball_team_member')->where('ball_team_id='.$qualifyingMsg['home_team_id'])->select();
            foreach ($homeTeamUser as $key=>$val){
                $val['nickname']=M('user')->where('user_id='.$val['uid'])->getField('nickname');
                $val['phone']=M('user')->where('user_id='.$val['uid'])->getField('phone');
                $val['avatar']=M('user')->where('user_id='.$val['uid'])->getField('avatar');
                $homeTeamUser[$key]=$val;
            }          
            if($qualifyingMsg['guest_team_id'] != ''){
                $guestTeamMsg=M('ball_team')->where('ball_team_id='.$qualifyingMsg['guest_team_id'])->find();
                $guestTeamMsg['build_time']=date('Y-m-d H:i:s',$guestTeamMsg['build_time']);
                $guestTeamMsg['lead_name']=$guestTeamMsg['lead_name']?$guestTeamMsg['lead_name']:M('user')->where('user_id='.$guestTeamMsg['uid'])->getField('nickname');
                $guestTeamMsg['phone']=M('user')->where('user_id='.$guestTeamMsg['uid'])->getField('phone');
                $guestTeamUser=M('ball_team_member')->where('ball_team_id='.$qualifyingMsg['guest_team_id'])->select();
                foreach ($guestTeamUser as $key=>$val){
                    $val['nickname']=M('user')->where('user_id='.$val['uid'])->getField('nickname');
                    $val['phone']=M('user')->where('user_id='.$val['uid'])->getField('phone');
                    $val['avatar']=M('user')->where('user_id='.$val['uid'])->getField('avatar');
                    $guestTeamUser[$key]=$val;
                }
                
                $this->assign('guestTeamUser',$guestTeamUser);
                $this->assign('guestTeamMsg',$guestTeamMsg);
            }
            
            $this->assign('homeTeamUser',$homeTeamUser);
            $this->assign('refereeMsg',$refereeMsg);
            $this->assign('courtMsg',$courtMsg);
            $this->assign('qualifyingMsg',$qualifyingMsg);
            $this->assign('homeTeamMsg',$homeTeamMsg);
            $this->assign('orderMsg',$orderMsg);
            $this->display('qualifying');
        }else if($type==1){            
            $orderMsg=M('order')->where('order_id='.$order_id)->find();
            $orderMsg['create_time']=date('Y-m-d H:i:s',$orderMsg['create_time']);            
            $friendlyMsg=M('friendly_order')->where('order_id='.$order_id)->find();
            $friendlyMsg['start_time']=date('Y-m-d H:i:s',$friendlyMsg['start_time']);
            $friendlyMsg['end_time']=date('Y-m-d H:i:s',$friendlyMsg['end_time']);
            $friendlyMsg['username']=M('user')->where('user_id='.$orderMsg['user_id'])->getField('nickname');
            $ballTeamMsg=M('ball_team')->where('uid='.$orderMsg['user_id'])->find();
            $ballTeamMsg['build_time']=date('Y-m-d H:i:s',$ballTeamMsg['build_time']);
            
            $userMsg=M('user')->where('user_id='.$orderMsg['user_id'])->find();
            if($friendlyMsg['court_id'] != ''){
                $courtMsg=M('court')->where('court_id='.$friendlyMsg['court_id'])->find();
            }
            
            if($friendlyMsg['referee_id'] != ''){
                $refereeMsg=M('referee')->where('id='.$friendlyMsg['referee_id'])->find();
            }
            
            if($ballTeamMsg['ball_team_id'] != ''){
                $teamUserMsg=M('ball_team_member')->where('ball_team_id='.$ballTeamMsg['ball_team_id'])->select();
                foreach($teamUserMsg as $key=>$val){
                    $val['userMsg']=M('user')->where('user_id='.$val['uid'])->find();
                    $teamUserMsg[$key]=$val;
                }
                $this->assign('teamUserMsg',$teamUserMsg);
            }
            
            $user['team_name']=$friendlyMsg['team_name']?$friendlyMsg['team_name']:$ballTeamMsg['name'];
            $user['user_phone']=$friendlyMsg['user_phone']?$friendlyMsg['user_phone']:$ballTeamMsg['phone'];
                        
            $this->assign('user',$user);
            $this->assign('refereeMsg',$refereeMsg);
            $this->assign('userMsg',$userMsg);
            $this->assign('ballTeamMsg',$ballTeamMsg);
            $this->assign('courtMsg',$courtMsg);
            $this->assign('friendlyMsg',$friendlyMsg);
            $this->assign('orderMsg',$orderMsg);
            $this->display('friendorder');
        }else if($type==2){
            $rechargeOrderMsg=M('recharge_order')->where('order_id='.$order_id)->find();
            $orderMsg=M('order')->where('order_id='.$order_id)->find();
            $orderMsg['create_time']=date('Y-m-d H:i:s',$orderMsg['create_time']);
            $ballName=M('ball_team')->where('uid='.$orderMsg['user_id'])->getField('name');
            $userMsg=M('user')->where('user_id='.$orderMsg['user_id'])->find();
            $userMsg['create_time']=date('Y-m-d H:i:s',$userMsg['create_time']);
            $userMsg['birthday']=date('Y-m-d',$userMsg['birthday']);
            $userMsg['province_id']=M('province')->where('province_id='.$userMsg['province_id'])->getField('province');
            $userMsg['city_id']=M('city')->where('city_id='.$userMsg['city_id'])->getField('city');
            $userMsg['county_id']=M('county')->where('county_id='.$userMsg['county_id'])->getField('county');
            if($userMsg['sex']==1){
                $userMsg['sex']='男';
            }elseif ($userMsg['sex']==2){
                $userMsg['sex']='女';
            }else{
                $userMsg['sex']='保密';
            }
            
            $this->assign('ballName',$ballName);
            $this->assign('rechargeOrderMsg',$rechargeOrderMsg);
            $this->assign('orderMsg',$orderMsg);
            $this->assign('userMsg',$userMsg);
            $this->display('userinfo');
        }
        
    }
    public function order_del(){
        $order_id=I('post.id');
        $type=I('post.type');
        if($type==1){
            $result=M('friendly_order')->where('order_id='.$order_id)->delete();
        }else if($type==0){
            $result=M('qualifying')->where('home_order_id='.$order_id)->delete();
        }
        $result=M('order')->where('order_id='.$order_id)->delete();
        $this->ajaxReturn($result);
    }


    /**
     *  立即退款
     */
    public function refundNow(){
        $order_id = $_REQUEST['order_id'];
        $orderInfo = $this->orderModel->getById($order_id);
        if($orderInfo['pay_type']!=1){
            response([],401,"暂不受理除支付宝以外的退款");
        }
        try {
            $this->orderModel->startTrans();
            $url = $this->aliPayRefund($order_id);
            $this->orderModel->commit();
            response(["url"=>$url]);
        } catch (\Exception $e) {
            $this->orderModel->rollback();
            response([],501,$e->getMessage());
        }
    }

    /**
     * 支付宝退款
     * @param int $order_id
     * @return string
     * @throws \Exception
     */
    protected function aliPayRefund($order_id){
        $order = $this->orderModel->getById($order_id);
        if(empty($order)){
            throw new \Exception("订单错误！",555);
        }

        $data = [
            "order_id" => $order_id,
            "batch_no" => build_order_no(),
        ];
        $this->orderModel->updateOrder($data);
        $url = $this->aliPay->refund($data['batch_no'],$order['trade_no'],$order['price']);

        return $url;
    }
}