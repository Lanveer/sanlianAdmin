<?php
namespace Mobile\Controller;
use Common\Model\OrderModel;
use Think\Controller;

/**
 * Class IndexController
 * @package Mobile\Controller
 * @property OrderModel $orderModel
 */
class IndexController extends Controller {
    private $orderModel;

    public function _initialize(){
        $this->orderModel = D("Order");
    }

    public function index(){
        $this->display();
    }

    public function venuetlist(){
        $pageNum=I('post.pageNum')?I('post.pageNum'):15;
        $page=I('post.page')?I('post.page'):1;

        $condition = [];

        $auth = session("admin_key_auth");
        if($auth==3){
            $condition['venue_id'] = session('admin_key_venue_id');
        }elseif($auth==2){

        }

        $venueMsg=M('venue')->where($condition)->page($page,$pageNum)->select();
        
        
        $this->assign('venueMsg',$venueMsg);
        $this->display();
    }
    public function getVenueMore(){
        $totalNum=count(M('venue')->select());
        $pageNum=I('post.pageNum')?I('post.pageNum'):15;
        $totalpage=ceil($totalNum/$pageNum);
        $page=I('post.page')?I('post.page'):1;
        $offeset=($page-1)*$pageNum;
        $venueMsg=M('venue')->limit($offeset,$pageNum)->select();
        $this->ajaxReturn($venueMsg);
    }
    public function venue_stop(){
        $venue_id=I('post.venue_id');
        $result=M('venue')->where('venue_id='.$venue_id)->save(array('is_freeze'=>1));
        $this->ajaxReturn($result);
    }
    public function venue_start(){
        $venue_id=I('post.venue_id');
        $result=M('venue')->where('venue_id='.$venue_id)->save(array('is_freeze'=>0));
        $this->ajaxReturn($result);
    }
    public function courtlist(){
        $venue_id=I('get.venue_id');
        $totalNum=count(M('venue')->select());
        $pageNum=I('post.pageNum')?I('post.pageNum'):15;
        $totalpage=ceil($totalNum/$pageNum);
        $page=I('post.page')?I('post.page'):1;
        $offeset=($page-1)*$pageNum;
        $courtMsg=M('court')->where('venue_id='.$venue_id)->limit($offeset,$pageNum)->select();
        
        $this->assign('venue_id',$venue_id);
        $this->assign('courtMsg',$courtMsg);
        $this->display();
    }
    public function getCourtMore(){
        $venue_id=I('post.venue_id');
        $totalNum=count(M('venue')->select());
        $pageNum=I('post.pageNum');
        $totalpage=ceil($totalNum/$pageNum);
        $page=I('post.page');
        $offeset=($page-1)*$pageNum;
        $courtMsg=M('court')->where('venue_id='.$venue_id)->limit($offeset,$pageNum)->select();
                
        $this->ajaxReturn($courtMsg);
    }
    public function court_stop(){
        $court_id=I('post.court_id');
        $result=M('court')->where('court_id='.$court_id)->save(array('is_freeze'=>1));
        $this->ajaxReturn($result);
    }
    public function court_start(){
        $court_id=I('post.court_id');
        $result=M('court')->where('court_id='.$court_id)->save(array('is_freeze'=>0));
        $this->ajaxReturn($result);
    }
    public function timelist(){
        $court_id=I('get.court_id');
        $onceMsg=M('once')->where('court_id='.$court_id)->select();
        foreach($onceMsg as $key=>$val){
            $onceMsg[$key]['start_time']=date('H:i:s',$val['start_time']);
            $onceMsg[$key]['end_time']=date('H:i:s',$val['end_time']);
            $val['datestr']='';
            $onceMsg[$key]['datestr'] = '';
            $val['close_once']=M('close_once')->field('once_id,time')->where('once_id='.$val['once_id'])->group('time')->select();
            if($val['close_once']==''){
                $val['close_once']=date('Y-m-d',time()+3600*24);
            }else{
                //$val['close_once']=date('Y-m-d',$val['close_once']);
                foreach($val['close_once'] as $k=>$v){
                    $v['time']=date('Y-m-d',$v['time']);
                    $val['datestr'] .=$v['time'].',';
                    $val['close_once'][$k]=$v;
                }
            }
            $onceMsg[$key]['datestr']=rtrim($val['datestr'],',');
        }
//        var_dump($onceMsg);
        $this->assign('onceMsg',$onceMsg);
        $this->display();
    }

    public function breakpoint(){
        header("Access-Control-Allow-Origin:*");
        $once_id=I('post.once_id');
        $dateValue=I('post.dateValue');
        $dateArr=explode(',',$dateValue);

        $result=M('close_once')->where('once_id='.$once_id)->delete();

        foreach($dateArr as $key=>$val){
            $val=strtotime($val);
            $res=M('close_once')->add(array('once_id'=>$once_id,'time'=>$val,'create_time'=>time()));
        }
        if($res){
            $this->ajaxReturn(1);
        }else{
            $this->ajaxReturn(2);
        }
    }

    public function emptytimes(){
        header("Access-Control-Allow-Origin:*");
        $once_id=$_REQUEST['once_id'];
        $result=M('close_once')->where('once_id='.$once_id)->delete();

        $result = boolval($result)|0;
        $this->ajaxReturn($result);
    }

    public function once_stop(){
        $once_id=I('post.once_id');
        $result=M('once')->where('once_id='.$once_id)->save(array('is_open'=>0));
        $this->ajaxReturn($result);
    }
    public function once_start(){
        $once_id=I('post.once_id');
        $result=M('once')->where('once_id='.$once_id)->save(array('is_open'=>1));
        $this->ajaxReturn($result);
    }
    public function changeCourtPrice(){
        $once_id=I('post.once_id');
        $price=I('post.price');
        $result=M('once')->where('once_id='.$once_id)->save(array('price'=>$price));
        
        $this->ajaxReturn($result);
    }

    public function orderlist(){
        $where['_string']="o.status = 1 AND (o.type=0 OR o.type=1 OR o.type =3)";
        $order='create_time';
        $orderMsg=$this->makepage($where,'','',$order);
        
//        var_dump($orderMsg);
        $this->assign('orderMsg',$orderMsg);
        $this->display();
    }
    public function getOrderMore(){
        $where['_string']="o.status = 1 AND (o.type=0 OR o.type=1 OR o.type =3)";
        $pageNum=I('post.pageNum');
        $pageSize=I('post.pageSize');
        $order=I('post.order')?I('post.order'):'';
        $phone=I('post.phone');
        if(!empty($phone)){
            $where['phone'] = trim($phone);
        }
        $searchTime=I('post.searchTime');
        if(!empty($searchTime)){
            $time = strtotime($searchTime);
            $where['_string'] .= " AND (f.start_time={$time} OR q.start_time={$time})";
            $order = 'game_time';
        }
        if($order=='money'){
            $order = '(o.price+o.san_money)';
        }
        $result=$this->makepage($where,$pageNum,$pageSize,$order);
        
        $this->ajaxReturn($result);
    }
   public function search(){
       $where['_string']="o.status = 1 AND (o.type=0 OR o.type=1 OR o.type =3)";
        $phone=I('post.phone');
       $order = '';
       if(!empty($phone)){
           $where['phone'] = trim($phone);
       }
        $searchTime=I('post.searchTime');
       if(!empty($searchTime)){
           $time = strtotime($searchTime);
           $where['_string'] .= " AND (f.start_time={$time} OR q.start_time={$time})";
           $order = 'game_time';
       }
        $dataArr=$this->makepage($where,'','',$order);



        $this->ajaxReturn($dataArr);
    }  
    
    public function makepage($where,$pageNum='',$pageSize='',$order=''){
        $pageNum=$pageNum?$pageNum:1;//页码，初始化为第一页
        $pageSize=$pageSize?$pageSize:10;//每页显示数量，默认10条记录
        $offset=($pageNum-1)*$pageSize;//分页偏移量

        $queryParam['offset'] = intval($offset);
        $queryParam['limit'] = intval($pageSize);
        $queryParam['sort'] = trim($order);

        $auth = session("admin_key_auth");
        if($auth==3){
            $queryParam['venue_id'] = session('admin_key_venue_id');
        }elseif($auth==2){

        }

        $result = $this->orderModel->search($queryParam,$where);
        $totalNum=$result['total'];//总记录数
        $totalPage=ceil($totalNum/$pageSize);//总页码数

        $_returnArr = $result['data'];

//        var_dump($_returnArr);
        
        return $_returnArr;
    }    
    //获取排位赛，充值，友谊赛，应战的所有订单，拼装成数组
    public function  getOrderMsgArr($where,$order='create_time',$pageNum,$pageSize){
        $orderMsg=M('order')->where($where)->page($pageNum,$pageSize)->order("{$order} desc")->select();

        $orderArr=array();
//        $rechargeArr=M('recharge_order')->Field('order_id')->select();
//        $friendlyArr=M('friendly_order')->Field('order_id')->select();
        
        foreach($orderMsg as $key=>$val){
//            $result=$this->deep_in_array($val['order_id'], $rechargeArr);
//            $res   =$this->deep_in_array($val['order_id'], $friendlyArr);
            $result = M('recharge_order')->where('order_id='.$val['order_id'])->count();
            $res = M('friendly_order')->where('order_id='.$val['order_id'])->count();
        
            if($result){
                $rechargeMsg=M('recharge_order')->where('order_id='.$val['order_id']|0)->find();
        
                $arr['order_id']=$val['order_id'];
                $arr['days']=date('m-d',$rechargeMsg['create_time']);
                $arr['hours']=date('H:i:s',$rechargeMsg['create_time']);
                $arr['searchTime']=$rechargeMsg['create_time'];
                $arr['phone']=M('user')->where('user_id='.$rechargeMsg['user_id'])->getField('phone');
                $arr['money']=$rechargeMsg['money'];
                $arr['type']=$val['type'];
                array_push($orderArr, $arr);
        
            }else if($res){
                $friendlyMsg=M('friendly_order')->where('order_id='.$val['order_id']|0)->find();
        
                $arr['order_id']=$val['order_id'];
                $arr['days']=date('m-d',$friendlyMsg['start_time']);
                $arr['startHours']=date('H:i',$friendlyMsg['start_time']);
                $arr['searchTime']=$friendlyMsg['start_time'];
                $arr['endHours']=date('H:i',$friendlyMsg['end_time']);
                $arr['phone']=M('user')->where('user_id='.$friendlyMsg['user_id'])->getField('phone');
                $arr['money']=$friendlyMsg['price'];
                $arr['courtName']=M('court')->where('court_id='.$friendlyMsg['court_id']|0)->getField('name');
                $arr['courtId'] = $friendlyMsg['court_id'];
                $arr['type']=$val['type'];
                array_push($orderArr, $arr);
        
            }else{
                if($val['type']==0){
                    $qualifyMsg=M('qualifying')->where('home_order_id='.$val['order_id']|0)->find();
                }else if($val['type']==3){
                    $qualifyMsg=M('qualifying')->where('guest_order_id='.$val['order_id']|0)->find();
                }
                $arr['order_id']=$val['order_id'];
                $arr['days']=date('m-d',$qualifyMsg['start_time']);
                $arr['startHours']=date('H:i',$qualifyMsg['start_time']);
                $arr['searchTime']=$qualifyMsg['start_time'];
                $arr['endHours']=date('H:i',$qualifyMsg['end_time']);
                $arr['courtName']=M('court')->where('court_id='.$qualifyMsg['court_id']|0)->getField('name');
                $arr['courtId'] = $qualifyMsg['court_id'];
                $arr['money']=$val['price'];
                $arr['type']=$val['type'];
                $arr['phone']=M('user')->where('user_id='.$val['user_id'])->getField('phone');
                array_push($orderArr, $arr);
            }
        
        
        }
        
        
        return $orderArr;
    }
    /*二维数组按指定的键值排序*/
    function array_sort($array,$keys,$type='asc'){
        if(!isset($array) || !is_array($array) || empty($array)){
            return '';
        }
        //排序字段名，如：id
        if(!isset($keys) || trim($keys)==''){
            return '';
        }
        //排序方式，如：desc、asc
        if(!isset($type) || $type=='' || !in_array(strtolower($type),array('asc','desc'))){
            return '';
        }
        //定义一个数组
        $keysvalue=array();
        foreach($array as $key=>$val){
            //对排序字段值进行过滤
            $val[$keys] = str_replace('-','',$val[$keys]);
            $val[$keys] = str_replace(' ','',$val[$keys]);
            $val[$keys] = str_replace(':','',$val[$keys]);
    
            //将记录中指定的键名放入数组中，如：[0]=>5,[1]=>3,[2]=>6
            $keysvalue[] =$val[$keys];//排序字段，如：id         索引=》排序键名
        }
        asort($keysvalue); //按值升序排序，且保持键名与键值之间的索引关系,如：[1]=>3,[0]=>5,[2]=>6
        reset($keysvalue); //指针重新指向数组第一个
        foreach($keysvalue as $key=>$vals) {
            $keysort[] = $key;//0=>[1],1=>[0],2=>[2]
        }
        $keysvalue = array();
        $count=count($keysort);//排序记录数
        if(strtolower($type) != 'asc'){//降序
            for($i=$count-1; $i>=0; $i--) {
                $keysvalue[] = $array[$keysort[$i]];
            }
        }else{//升序
            for($i=0; $i<$count; $i++){
                $keysvalue[] = $array[$keysort[$i]];
            }
        }
        return $keysvalue;
    }
    //验证数值是否存在一个数组(含二维数组)
    public function deep_in_array($value, $array) {
        foreach($array as $item) {
            if(!is_array($item)) {
                if ($item == $value) {
                    return true;
                } else {
                    continue;
                }
            }
    
            if(in_array($value, $item)) {
                return true;
            } else if($this->deep_in_array($value, $item)) {
                return true;
            }
        }
        return false;
    }
    public function logout(){
        session_destroy();
        session_unset();
        header('location:'.U('Index/login'));
    }
    public function login(){        
        
        $this->display();
    }
    public function checklogin(){
        $username=I('post.username');
        $password=md5(md5(I('post.password')).C('__ENCRYPT__'));        
        if(!empty($username)){            
            $userinfo=M('admin_user')->where(array('loginname'=>$username))->find();
            if($userinfo){                
                if($userinfo['password'] == $password){
                    //session('[start]');
                    session('admin_key_id',$userinfo["id"]);  //设置session
                    session('admin_key_loginname',$userinfo["loginname"]);  //设置session
                    session('admin_key_name',$userinfo["name"]);  //设置session
                    session('admin_key_auth',$userinfo["auth"]);  //设置session
                    session('admin_key_venue_id',$userinfo["venue_id"]);  //设置session
                    session('admin_key_phone',$userinfo["phone"]);  //设置session
                    
                    $this->ajaxReturn(1);
                }else{
                    $this->ajaxReturn(3);
                }
            }else{
                $this->ajaxReturn(2);
            }
        }
        
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

}