<?php
namespace Admin\Controller;
use Think\Controller;
header('content-type:text/html;charset=utf-8');
class ExportexcelController extends Controller{        
    public function getTeamlistExcel(){
        $headArr=array('球队编号','创建人','球队名称','队徽','类型','球员人数','成立时间','是否审核','联系电话');
        $fileName="球队列表";
        $teamListMsg=M('ball_team')->where('is_verify=1')->select();
        $dataArr=array();
        foreach($teamListMsg as $key=>$val){
            $data['ball_team_id']=$val['ball_team_id'];
            $data['uid']=M('user')->where('user_id='.$val['uid'])->getField('nickname');
            $data['name']=$val['name'];
            $data['logo']=$val['logo'];
            if($val['type']==0){
                $data['type']='俱乐部';
            }elseif($val['type']==1){
                $data['type']='FC';
            }elseif($val['type']==2){
                $data['type']='球队';
            }
            $data['member_num']=$val['member_num'];
            $data['create_time']=date('Y-m-d H:i:s',$val['create_time']);
            $data['is_verify']='已通过';
            $data['phone']=M('user')->where('user_id='.$val['uid'])->getField('phone');

            $dataArr[$key]=$data;
        } 

        Vendor ('PHPExcel.PHPExcel');
        $this->exportExcel($fileName,$headArr,$dataArr);//数据导出
    }
    public function getUserExcel(){
        $headArr=array('球员编号','联系电话','用户昵称','三联币','邮箱','性别','身高','体重','年龄','设备','vip等级','当前版本号','累计充值金额','地址','申请时间','最后登陆时间');
        $fileName='球员信息';

        $userMsg=M('user')->select();
        $dataArr=array();
        foreach($userMsg as $key=>$val){
            $data['user_id']=$val['user_id'];
            $data['phone']=$val['phone'];
            $data['nickname']=$val['nickname'];
            $data['san_money']=$val['san_money'];
            $data['email']=$val['email'];
            if($val['sex']==0){
                $data['sex']='保密';
            }else if($val['sex']==1){
                $data['sex']='男';
            }else if($val['sex']==2){
                $data['sex']='女';
            }
            $data['height']=$val['height'];
            $data['weight']=$val['weight'];
            if($val['birthday']==''){
                $data['age']='暂无';
            }else{
                $data['age']=date('Y',time()-$val['birthday']);
            }
            $data['device']=$val['device'];
            $data['vip']=M('vip')->where('level='.$val['vip_id'])->getField('name');
            $data['ver']=$val['ver'];
            $data['recharge_money']=$val['recharge_money'];
            if($val['province_id']=='' || $val['city_id']=='' || $val['county_id']==''){
                $data['address']='暂无';
            }else{
                $province=M('province')->where('province_id='.$val['province_id'])->getField('province');
                $citys=M('city')->where('city_id='.$val['city_id'])->getField('city');
                $county=M('county')->where('county_id='.$val['county_id'])->getField('county');
                $data['address']=$province.$citys.$county;
            }

            $data['create_time']=date('Y-m-d H:i:s',$val['create_time']);
            $data['last_login_time']=date('Y-m-d H:i:s',$val['last_login_time']);
            $dataArr[$key]=$data;
        }

        Vendor ('PHPExcel.PHPExcel');
        $this->exportExcel($fileName,$headArr,$dataArr);//数据导出
    }
    public function getActivityExcel(){
        $headArr=array('活动编号','活动名称','活动发起人','开始时间','保证金','球队名称','预计人数/最大人数','参加人数','活动类型','当前状态','创建时间');
        $fileName='球队活动信息';

        $dataArr=array();
        $activityMsg=M('ball_team_activity')->select();
        foreach($activityMsg as $key=>$val){
            $data['id']=$val['id'];
            $data['name']=$val['name'];
            $data['userphone']=M('user')->where('user_id='.$val['user_id'])->getField('phone');
            $data['start_time']=date('Y-m-d H:i:s',$val['activity_time']);
            $data['ensure_money']=$val['ensure_money'];
            $data['team_name']=M('ball_team')->where('ball_team_id='.$val['ball_team_id'])->getField('name');
            $data['plan_person_num']=$val['plan_person_num'];
            $data['join_num']=$val['join_num'];
            if($val['type']==0){
                $data['type']='非赛事';
            }elseif($val['type']==1){
                $data['type']='赛事';
            }
            if($val['status']==1){
                $data['status']='正常';
            }elseif($val['status']==2){
                $data['status']='已结束';
            }elseif($val['status']==3){
                $data['status']='已取消';
            }
            $dataArr[$key]=$data;
        }
        Vendor ('PHPExcel.PHPExcel');
        $this->exportExcel($fileName,$headArr,$dataArr);//数据导出
    }
    public function getQualifyingExcel(){
        $headArr=array('排位赛编号','主战队名称','主战队联系电话','主战队球衣颜色','主战队三联币抵扣金额','主战队支付金额','主战队三联分','主战队进球数',
                        '主战队红牌','主战队黄牌','',
                        '应战队名称','应战队联系方式','应战队球衣颜色','应战队三联币抵扣金额','应战队支付金额','应战队三联分','应战队进球数','应战队红牌',
                        '应战队黄牌','',
                        '比赛场地','球场联系电话','比赛开始时间','','裁判组名称','裁判金额','','是否预约视频服务','',
                        '总金额','订单创建时间','是否匹配','是否支付','当前状态');
        $fileName='排位赛信息';

        $Controller=new IndentController();
        $query = [];
        if(session("admin_key_auth")==3){
            $venue_id = session('admin_key_venue_id');
            $courtIds = M("court")->where("venue_id=%d",$venue_id)->getField("court_id",true);
            if(empty($courtIds)){
                $courtIds = [0];
            }
            $query['court_id'] = array("in",$courtIds);
        }
        $dataArr=array();
        $qualifyingMsg=M('qualifying')->where($query)->order('create_time desc')->select();
        foreach($qualifyingMsg as $key=>$val){
            $home_order_Msg=M('order')->where('order_id='.$val['home_order_id'])->find();
            $data['qualifying_id']=$val['qualifying_id'];

            $data['home_team_name']=M('ball_team')->where('ball_team_id='.$val['home_team_id'])->getField('name');
            $data['home_phone']=M('qualifying')->join('t_ball_team as b on b.ball_team_id='.$val['home_team_id'])->join('t_user on b.uid=t_user.user_id')->getField('t_user.phone');
            $data['home_color']=$Controller->returnColor($val['home_color']);
            $data['san_money']=$home_order_Msg['san_money'];
            $data['price']=$home_order_Msg['price'];
            $data['home_score']=$val['home_score'];
            $data['home_goal']=$val['home_goal'];
            $data['home_red_card']=$val['home_red_card'];
            $data['home_yellow_card']=$val['home_yellow_card'];
            $data['space1']='';

            if($val['guest_team_id'] !=''){
                $data['guest_team_name']=M('ball_team')->where('ball_team_id='.$val['guest_team_id'])->getField('name');
                $data['guest_phone']=M('qualifying')->join('t_ball_team as a on a.ball_team_id='.$val['guest_team_id'])->join('t_user on a.uid=t_user.user_id')->getField('t_user.phone');
                $data['guest_color']=$Controller->returnColor($val['guest_color']);
                if($val['guest_order_id'] != ''){
                    $guest_order_Msg=M('order')->where('order_id='.$val['guest_order_id'])->find();
                    $data['guest_san_money']=$guest_order_Msg['san_money'];
                    $data['guest_price']=$guest_order_Msg['price'];
                }else{
                    $data['guest_san_money']='';
                    $data['guest_price']='';
                }

                $data['guest_score']=$val['guest_score'];
                $data['guest_goal']=$val['guest_goal'];
                $data['guest_red_card']=$val['guest_red_card'];
                $data['guest_yellow_card']=$val['guest_yellow_card'];
            }else{
                $data['guest_team_name']='';
                $data['guest_phone']='';
                $data['guest_color']='';
                $data['guest_san_money']='';
                $data['guest_price']='';
                $data['guest_score']='';
                $data['guest_goal']='';
                $data['guest_red_card']='';
                $data['guest_yellow_card']='';
            }

            $data['space2']='';

            $courtMsg=M('court')->where('court_id='.$val['court_id'])->find();
            $data['courtName']=$courtMsg['name'];
            $data['courtPhone']=$courtMsg['phone'];
            $data['start_time']=date('Y-m-d H:i:s',$val['start_time']);

            $data['space3']='';

            $refereeMsg=M('referee')->where('id='.$val['referee_id'])->find();
            $data['referee']=$refereeMsg['name'];
            $data['refereePrice']=$refereeMsg['price'];

            $data['space4']='';

            if($val['cameraman_id']==0){
                $data['cameraman_id']='否';
            }else{
                $data['cameraman_id']='是';
            }
            $data['space5']='';
            $data['totalPrice']=$val['fee'];
            $data['create_time']=date('Y-m-d H:i:s',$val['create_time']);
            if($val['guest_team_id'] !=''){
                $data['is_marry']='否';
            }else{
                $data['is_marry']='是';
            }
            if($val['pay_num']==0){
                $data['pay_num']='都未付款';
            }elseif($val['pay_num']==1){
                $data['pay_num']='主队已付款';
            }elseif($val['pay_num']==2){
                $data['pay_num']='都已付款';
            }
            if($val['status']==0){
                $data['status']='等待应战';
            }elseif($val['status']==1){
                $data['status']='双方已参赛';
            }elseif($val['status']==2){
                $data['status']='主队已取消比赛';
            }

            $dataArr[$key]=$data;
        }

        Vendor ('PHPExcel.PHPExcel');
        $this->exportExcel($fileName,$headArr,$dataArr);//数据导出
    }
    public function getOrderExcel(){
        $where['type']=I('get.type');
        $where['status']=I('get.status');

        if(session('admin_key_auth')==3){
            $venue_id = session('admin_key_venue_id');
            $courtIds = M("court")->where("venue_id=%d",$venue_id)->getField("court_id",true);
        }

        if($where['type']==''){
            if($where['status']==0){
                $fileName='未处理的订单';
            }else if($where['status']==1){
                $fileName='已处理的订单';
            }else if($where['status']==2){
                $fileName='申请退款的订单';
            }else if($where['status']==3){
                $fileName='已退款的订单';
            }

            $headArr=array('订单编号','支付用户','订单类型','支付方式','支付金额','三联币','创建时间','当前状态');
            $dataArr=array();

            $orderMsg=M('order')->where('status='.$where['status'])->order('create_time DESC')->select();

            foreach($orderMsg as $key=>$val){
                $data['id']=$val['order_id'];
                $data['phone']=M('user')->where('user_id='.$val['user_id'])->getField('phone');
                if($val['type']==0){
                    $data['type']='排位赛';
                }else if($val['type']==1){
                    $data['type']='友谊赛';
                }else if($val['type']==2){
                    $data['type']='充值';
                }else if($val['type']==3){
                    $data['type']='应战';
                }elseif($val['type']==4){
                    $data['type'] = '赛事报名费用';
                }else{
                    $data['type']='';
                }
                if($val['pay_type']==1){
                    $data['pay_type']='支付宝';
                }elseif($val['pay_type']==2){
                    $data['pay_type']='微信';
                }else{
                    $data['pay_type']='';
                }
                $data['price']=$val['price'];
                $data['san_money']=$val['san_money'];
                $data['create_time']=date('Y-m-d H:i:s',$val['create_time']);
                if($val['status']==0){
                    $data['status']='待支付';
                }elseif($val['status']==1){
                    $data['status']='已支付';
                }else if($val['status']==2){
                    $data['status']='申请退款中';
                }elseif($val['status']==3){
                    $data['status']='已退款';
                }else{
                    $data['status']='';
                }
                $dataArr[$key]=$data;
            }

            Vendor ('PHPExcel.PHPExcel');
            $this->exportExcel($fileName,$headArr,$dataArr);
        }else{
            if($where['status']==0){
                $name='未处理的订单';
            }else if($where['status']==1){
                $name='已处理的订单';
            }else if($where['status']==2){
                $name='申请退款的订单';
            }else if($where['status']==3){
                $name='已退款的订单';
            }
            if($where['type']==0){
                $fileName='排位赛-'.$name;
            }else if($where['type']==1){
                $fileName='友谊赛-'.$name;
            }else if($where['type']==2){
                $fileName='充值-'.$name;
            }

            $dataArr=array();
            if($where['type']==2){
                $headArr=array('订单编号','支付用户','订单类型','支付方式','支付金额','三联币','创建时间','当前状态');
                $recharge_orderMsg=M('recharge_order')->where($where)->order('create_time DESC')->select();

                foreach($recharge_orderMsg as $key=>$val){
                    $data['id']=$val['id'];
                    $data['userphone']=M('user')->where('user_id='.$val['user_id'])->getField('phone');
                    $data['type']=$fileName;
                    $pay_type=M('order')->where('order_id='.$val['order_id'])->getField('pay_type');
                    if($pay_type==1){
                        $data['pay_type']='支付宝';
                    }elseif($pay_type==2){
                        $data['pay_type']='微信';
                    }
                    $data['money']=$val['money'];
                    $data['san_money']=M('order')->where('order_id='.$val['order_id'])->getField('san_money')?M('order')->where('order_id='.$val['order_id'])->getField('san_money'):'';
                    $data['create_time']=date('Y-m-d H:i:s',$val['create_time']);
                    if($val['status']==0){
                        $data['status']='未成完';
                    }elseif($val['status']==1){
                        $data['status']='已完成';
                    }
                    $dataArr[$key]=$data;
                }

                Vendor ('PHPExcel.PHPExcel');
                $this->exportExcel($fileName,$headArr,$dataArr);
            }else{
                $headArr=array('订单编号','支付用户','订单类型','支付方式','球队名称','裁判类型','球场名称','球场类型','支付金额','三联币','创建时间','状态');
                $orderMsg=M('order')->where($where)->order('create_time DESC')->select();

                foreach($orderMsg as $key=>$val){
                    $data['order_id']=$val['order_id'];
                    $data['userphone']=M('user')->where('user_id='.$val['user_id'])->getField('phone');
                    $data['type']=$fileName;
                    $data['pay_type']=$val['pay_type']==1?'支付宝':'微信';
                    if(session('admin_key_auth')==3){
                        if(empty($courtIds)){
                            $courtIds = [0];
                        }
                        $query['court_id'] = array('in',$courtIds);
                    }else{
                        $query = [];
                    }

                    if($where['type']==0){
                        $isHas = M("qualifying")->where($query)->count();
                        if(!$isHas){
                            break;
                        }
                        $data['ball_team_name']=M('qualifying')->join('t_ball_team on t_ball_team.ball_team_id=t_qualifying.home_team_id')->where("t_qualifying.home_order_id=%d",$val['order_id'])->getField('t_ball_team.name');
                        $data['referee_id']=M('qualifying')->join('t_referee on t_qualifying.referee_id=t_referee.id')->where("t_qualifying.home_order_id=%d",$val['order_id'])->getField('t_referee.name');
                        $courtMsg=M('qualifying')->join('t_court on t_court.court_id=t_qualifying.court_id')->where("t_qualifying.home_order_id=%d",$val['order_id'])->find();
                    }elseif($where['type']==1){
                        $isHas = M("friendly_order")->where($query)->count();
                        if(!$isHas){
                            break;
                        }
                        $data['ball_team_name']=M('friendly_order')->where("t_friendly_order.order_id=%d",$val['order_id'])->getField('t_friendly_order.team_name');
                        $data['referee_id']=M('friendly_order')->join('t_referee on t_friendly_order.referee_id=t_referee.id')->where("t_friendly_order.order_id=%d",$val['order_id'])->getField('t_referee.name');
                        $courtMsg=M('friendly_order')->join('t_court on t_court.court_id=t_friendly_order.court_id')->where("t_friendly_order.order_id=%d",$val['order_id'])->find();

                    }
                    $data['courtName']=$courtMsg['name'];
                    $data['courtType']=$courtMsg['type'];
                    $data['money']=$val['price'];
                    $data['san_money']=$val['san_money'];
                    $data['create_time']=date('Y-m-d H:i:s',$val['create_time']);
                    if($val['status']==0){
                        $data['status']='待支付';
                    }elseif($val['status']==1){
                        $data['status']='已支付';
                    }elseif($val['status']==2){
                        $data['status']='申请退款中';
                    }elseif($data['status']==3){
                        $data['status']='已退款';
                    }else{
                        $data['status']='其他';
                    }

                    $dataArr[$key]=$data;
                }

                Vendor ('PHPExcel.PHPExcel');
                $this->exportExcel($fileName,$headArr,$dataArr);
            }
        }
    }
    public function exportExcel($fileName,$headArr,$data){
        if(empty($data) || !is_array($data)){
            die("data must be a array");
        }
        if(empty($fileName)){
            exit;
        }
        $date = date("Y_m_d",time());
        $fileName .= "_{$date}.csv";

        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        date_default_timezone_set('PRC');

        if (PHP_SAPI == 'cli')
            die('只能通过浏览器运行');

        //创建新的PHPExcel对象
        $objPHPExcel = new \PHPExcel();
        $objProps = $objPHPExcel->getProperties();

        //设置表头
        $key = ord("A");
        $key2 = ord("@");//@--64

        foreach($headArr as $v){
            if($key>ord("Z")){
                $key2 += 1;
                $key = ord("A");
                $colum = chr($key2).chr($key);//超过26个字母时才会启用  dingling 20150626
            }else{
                if($key2>=ord("A")){
                    $colum = chr($key2).chr($key);
                }else{
                    $colum = chr($key);
                }
            }

            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $key += 1;
        }

        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();

        foreach($data as $key => $rows){ //行写入
            $span = ord("A");
            $span2 = ord("@");
            foreach($rows as $v){
                if($span>ord("Z")){
                    $span2 += 1;
                    $span = ord("A");
                    $j = chr($span2).chr($span);//超过26个字母时才会启用  dingling 20150626
                }else{
                    if($span2>=ord("A")){
                        $j = chr($span2).chr($span);
                    }else{
                        $j = chr($span);
                    }
                }
                //$j = chr($span);
                $objActSheet->setCellValue($j.$column,$v);
                $span++;
            }
            $column++;
        }
        $fileName = iconv("UTF-8", "GBK", $fileName);
        //设置活动单指数到第一个表,所以Excel打开这是第一个 表
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
        $objWriter->setUseBOM(true);
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }

}
 