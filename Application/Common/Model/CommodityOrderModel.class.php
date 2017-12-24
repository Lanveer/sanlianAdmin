<?php
/**
 * Created by PhpStorm.
 * User: walter
 * Date: 2016/8/13
 * Time: 18:15
 */
namespace Common\Model;

use Think\Model;

class CommodityOrderModel extends Model{

    protected $_validate = [
        array('order_no','require','订单号必须！',1,'regex',1),    //新增时必须字段
        array('user_id','require','用户ID必须！',1,'regex',1),    //新增时必须字段
    ];
    //自动完成会把字段填全，建议别用更新
    protected $_auto = [
        array('create_time','time',1,'function')   //新增时执行函数
    ];


    /**
     *  根据条件检索
     * @param $queryParams
     * @param array $ext
     * @return mixed
     */
    public function search($queryParams,$ext=[]){
        $condition=[];
        $length = isset($queryParams['limit'])?$queryParams['limit']:10;
        $offset = $queryParams['offset']?$queryParams['offset']:0;
        $sort = $queryParams['sort']?$queryParams['sort']:'id';
        $order = $queryParams['order']?$queryParams['order']:'desc';

        //创建时间检索
        if(!empty($queryParams['start_time'])&&!empty($queryParams['end_time'])){
            if(!is_numeric($queryParams['start_time'])){
                $queryParams['start_time'] = strtotime($queryParams['start_time']);
                $queryParams['end_time'] = strtotime($queryParams['end_time']);
            }
            $start_time = intval($queryParams['start_time'])-1;
            $end_time = intval($queryParams['end_time'])+1;
            $condition['create_time'] = array("between",array($start_time,$end_time));
        }

        if(isset($queryParams['order_id'])&&$queryParams['order_id']!==''){
            $condition['id'] = intval($queryParams['order_id']);
        }

        if(isset($queryParams['commodity_id'])&&$queryParams['commodity_id']!==''){
            $condition['commodity_id'] = intval($queryParams['commodity_id']);
        }

        if(isset($queryParams['ball_team_id'])&&$queryParams['ball_team_id']!==''){
            $condition['ball_team_id'] = intval($queryParams['ball_team_id']);
        }

        if(isset($queryParams['status'])&&$queryParams['status']!==''){
            $condition['status'] = intval($queryParams['status']);
        }


        if(!empty($queryParams['order_no'])){
            $condition['order_no'] = array("like","%".trim($queryParams['order_no'])."%");
        }


        $condition = array_merge($condition,$ext);

//        var_dump($condition);

        $total = $this->where($condition)->count();
        $data = $this->where($condition)->order("{$sort} {$order}")->limit($offset,$length)->select();

        $userModel = D("User");
        $commodityModel = D("Commodity");
        $ballTeamModel = D("BallTeam");
        foreach($data as $index => $value){
            if(!empty($value['user_id'])){
                $data[$index]['user'] = $userModel->where(['user_id'=>$value['user_id']])->find();
            }
            if(!empty($value['commodity_id'])){
                $data[$index]['commodity'] = $commodityModel->where(['id'=>$value['commodity_id']])->find();
            }
            if(!empty($value['ball_team_id'])){
                $data[$index]['ball_team'] = $ballTeamModel->where(['ball_team_id'=>$value['ball_team_id']])->find();
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
        $data = $this->where("id=%d",$id)->find();
        if(empty($data)){
            $data = [];
        }else{
            $data['user'] = D("User")->where(['user_id'=>$data['user_id']])->find();
            $data['commodity'] = D("Commodity")->where(['id'=>$data['commodity_id']])->find();
            $data['ball_team'] = D("BallTeam")->where(['ball_team_id'=>$data['ball_team_id']])->find();
        }
        return $data;
    }


    /**
     * 修改订单状态
     * @param $id
     * @param $status
     * @return bool
     * @throws \Exception
     */
    public function changeStatus($id, $status){
        if(empty($id)){
            throw new \Exception("id必须！");
        }
        $result = $this->where("id=%d",$id)->save(["status"=>intval($status)]);
        if($result===false){
            throw new \Exception("更新失败");
        }
        return $result;
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
     * 根据订单ID删除订单
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function delById($id){
        if(empty($id)){
            throw new \Exception("ID必须");
        }
        $result = $this->where("id=%d",$id)->delete();
        return $result;
    }

    /**
     * 更新订单
     * @param array $input
     * @return bool
     * @throws \Exception
     */
    public function updateCommodityOrder($input){
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

    /**
     * 新增商品
     * @param $input
     * @return mixed
     * @throws \Exception
     */
    public function addCommodityOrder($input){
        $data = $this->create($input);
        if(!$data){
            throw new \Exception($this->getError());
        }else{
            try {
                $newId = $this->add($data);
                if (empty($newId)) {
                    throw new \Exception("新增失败");
                }
                return $newId;
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
    }
}