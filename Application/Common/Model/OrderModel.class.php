<?php
/**
 * Created by PhpStorm.
 * User: walter
 * Date: 2016/8/13
 * Time: 18:15
 */
namespace Common\Model;

use Think\Model;

class OrderModel extends Model{


    /**
     *  根据条件检索
     * @param $queryParams
     * @param array $ext
     * @return mixed
     */
    public function search($queryParams,$ext=[]){
        $condition=[];
        $having = "";
        $length = isset($queryParams['limit'])?$queryParams['limit']:10;
        $offset = $queryParams['offset']?$queryParams['offset']:0;
        $sort = $queryParams['sort']?$queryParams['sort']:'o.order_id';
        $order = $queryParams['order']?$queryParams['order']:'desc';

        //创建时间检索
        if(!empty($queryParams['start_time'])&&!empty($queryParams['end_time'])){
            if(!is_numeric($queryParams['start_time'])){
                $queryParams['start_time'] = strtotime($queryParams['start_time']);
                $queryParams['end_time'] = strtotime($queryParams['end_time']);
            }
            $start_time = intval($queryParams['start_time'])-1;
            $end_time = intval($queryParams['end_time'])+1;
            $condition['o.create_time'] = array("between",array($start_time,$end_time));
        }

        if(isset($queryParams['order_id'])&&$queryParams['order_id']!==''){
            $condition['o.order_id'] = intval($queryParams['order_id']);
        }

        if(isset($queryParams['type'])&&$queryParams['type']!==''){
            if($queryParams['type']==0){
                $condition['o.type'] = array("in",[0,3]);
            }else{
                $condition['o.type'] = intval($queryParams['type']);
            }
//            if(!empty($condition['o.create_time'])&&($queryParams['type']==0||$queryParams['type']==1)){
//                $time = $condition['o.create_time'][1];
//                $having = "start_time between {$time[0]} and {$time[1]}";
//                unset($condition['o.create_time']);
//            }
        }else{
            if(session('admin_key_auth')==3){
                $condition['o.type'] = -1;
            }
        }

        if(isset($queryParams['status'])&&$queryParams['status']!==''){
            $condition['o.status'] = intval($queryParams['status']);
        }

        if(isset($queryParams['refund_type'])&&$queryParams['refund_type']!==''){
            $condition['o.refund_type'] = intval($queryParams['refund_type']);
        }

        if(isset($queryParams['user_id'])&&$queryParams['user_id']!==''){
            $condition['o.user_id'] = intval($queryParams['user_id']);
        }


        if(isset($queryParams['order_no'])&&$queryParams['order_no']!==''){
            $condition['o.order_no'] = trim($queryParams['order_no']);
        }

        if(!empty($queryParams['phone'])){
            $condition['u.phone'] = array("like","%".trim($queryParams['phone'])."%");
        }

        if(isset($queryParams['venue_id'])&&$queryParams['venue_id']!==''){
            $venue_id = intval($queryParams['venue_id']);
            $courtIds = M("court")->where("venue_id=%d",$venue_id)->getField("court_id",true);
//            var_dump($courtIds);
            if(empty($courtIds)){
                $courtIds = [0];
            }
            $where['q.court_id'] = array("in",$courtIds);
            $where['f.court_id'] = array("in",$courtIds);
            $where['_logic'] = 'or';
            $condition['_complex'] = $where;
        }

        if($sort=='game_time'){
            if($queryParams['type']===''||$queryParams['type']==null){
                $sort = "start_time";
            }elseif($queryParams['type']==0||$queryParams['type']==3){
                $sort = "q_start_time";
            }elseif($queryParams['type']==1){
                $sort = "f_start_time";
            }else{
                $sort = "start_time";
            }
        }

        $condition = array_merge($condition,$ext);

//        var_dump($condition);

        $total = $this->alias("o")
            ->join("left join t_user AS u on o.user_id = u.user_id")
            ->join("left join t_qualifying AS q on q.home_order_id=o.order_id OR q.guest_order_id=o.order_id")
            ->join("left join t_friendly_order AS f ON f.order_id=o.order_id")
            ->where($condition)->having($having)->count();
        $data = $this
            ->alias("o")
            ->field("o.*,u.phone,q.court_id AS q_court_id,f.court_id AS f_court_id,q.home_team_id,q.guest_team_id,f.team_name,q.referee_id AS q_referee_id,f.referee_id AS f_referee_id,q.start_time AS q_start_time,q.end_time AS q_end_time,f.start_time AS f_start_time,f.end_time AS f_end_time,
            f.price AS f_price,f.san_money AS f_san_money,(IFNULL(q.start_time,0)+IFNULL(f.start_time,0)) as start_time")
            ->join("left join t_user AS u on o.user_id = u.user_id")
            ->join("left join t_qualifying AS q on q.home_order_id=o.order_id OR q.guest_order_id=o.order_id")
            ->join("left join t_friendly_order AS f ON f.order_id=o.order_id")
//            ->join("left join t_court AS c on q.court_id=c.court_id OR f.court_id=c.court_id")
            ->where($condition)
            ->having($having)
            ->order("{$sort} {$order}")
            ->limit($offset,$length)
            ->select();

        $courtModel = D("Court");
        $ballTeamModel = D("BallTeam");
        $refereeModel = D("Referee");
        $competitionBallteamModel = D("CompetitionBallteam");
        foreach($data as $index => $value){
            if($value['type']==0){      //排位赛主队
                $court_id = $value['q_court_id'];
                $data[$index]['court_id'] = $court_id;
                $data[$index]['court'] = $courtModel->getById($court_id);
                $data[$index]['ball_team'] = $ballTeamModel->getById($value['home_team_id']|0);
                $data[$index]['team_name'] = $data[$index]['ball_team']['name'];
                $data[$index]['referee'] = $refereeModel->getById($value['q_referee_id']);
                $data[$index]['game_time'] = date("Y-m-d H:i",$value['q_start_time'])."~".date("H:i",$value['q_end_time']);
            }elseif($value['type']==1){     //友谊赛
                $court_id = $value['f_court_id'];
                $data[$index]['court_id'] = $court_id;
                $data[$index]['court'] = $courtModel->getById($court_id);
                $data[$index]['referee'] = $refereeModel->getById($value['f_referee_id']);
                $data[$index]['game_time'] = date("Y-m-d H:i",$value['f_start_time'])."~".date("H:i",$value['f_end_time']);
            }elseif($value['type']==2){

            }elseif($value['type']==3){    //排位赛客队
                $court_id = $value['q_court_id'];
                $data[$index]['court_id'] = $court_id;
                $data[$index]['court'] = $courtModel->getById($court_id);
                $data[$index]['ball_team'] = $ballTeamModel->getById($value['guest_team_id']);
                $data[$index]['team_name'] = $data[$index]['ball_team']['name'];
                $data[$index]['referee'] = $refereeModel->getById($value['q_referee_id']);
                $data[$index]['game_time'] = date("Y-m-d H:i",$value['q_start_time'])."~".date("H:i",$value['q_end_time']);
            }elseif($value['type']==4){     //赛事
                $data[$index]['competition'] = $competitionBallteamModel->getByOrderId($value['order_id']);
            }

        }
        $result['total'] = $total;
        $result['data'] = $data;
        return $result;
    }

    /**
     * 根据ID获取详情
     * @param int $id
     * @return array|mixed
     */
    public function getById($id){
        if(empty($id)){
            return [];
        }
        $order = $this->where("order_id=%d",$id)->find();
        if(empty($order)){
            $order = [];
        }
        return $order;
    }

    /**
     * 根绝退款批次号获取详情
     * @param $batch_no
     * @return array|mixed
     */
    public function getByBatchNo($batch_no){
        $order = $this->where("batch_no='%s'",$batch_no)->find();
        if(empty($order)){
            $order = [];
        }
        return $order;
    }

    /**
     * 根据订单号查询订单
     * @param string $orderNo
     * @return array|mixed
     */
    public function getByOrderNo($orderNo){
        if(empty($orderNo)){
            return [];
        }
        $order = $this->where("order_no='%s'",$orderNo)->find();
        if(empty($order)){
            $order = [];
        }
        return $order;
    }

    /**
     * 修改订单状态
     * @param $order_id
     * @param $status
     * @return bool
     * @throws \Exception
     */
    public function changeStatus($order_id, $status){
        if(empty($order_id)){
            throw new \Exception("order_id必须！");
        }
        $result = $this->where("order_id=%d",$order_id)->save(["status"=>intval($status)]);
        if($result===false){
            throw new \Exception("更新失败");
        }
        return $result;
    }

    /**
     * 根据订单ID删除订单
     * @param $order_id
     * @return mixed
     * @throws \Exception
     */
    public function delById($order_id){
        if(empty($order_id)){
            throw new \Exception("订单ID必须");
        }
        $result = $this->where("order_id=%d",$order_id)->delete();
        return $result;
    }

    /**
     * 更新订单
     * @param array $input
     * @return bool
     * @throws \Exception
     */
    public function updateOrder($input){
        $data = $this->create($input);
        if(!$data){
            throw new \Exception($this->getError());
        }else{
            $result = $this->save($data);
            if($result===false){
                throw new \Exception("更新失败");
            }
            return $result;
        }
    }
}