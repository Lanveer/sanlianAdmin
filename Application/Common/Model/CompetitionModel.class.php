<?php
/**
 * Created by PhpStorm.
 * User: walter
 * Date: 2016/7/19
 * Time: 9:37
 */
namespace Common\Model;

use Think\Model;

class CompetitionModel extends Model{
    protected $_validate = [
        array('type','require','赛事类型必须！',1,'regex',1),    //新增时必须字段
        array('title','require','赛事名称必须！',1,'regex',1),    //新增时必须字段
    ];

    //自动完成会把字段填全，建议别用更新
    protected $_auto = [
        array('create_time','time',1,'function')   //新增时执行函数
    ];

    /**
     *  根据条件检索
     * @param $queryParams
     * @return mixed
     */
    public function search($queryParams){
        $condition=[];
        $length = isset($queryParams['limit'])?$queryParams['limit']:10;
        $offset = $queryParams['offset']?$queryParams['offset']:0;
        $sort = $queryParams['sort']?$queryParams['sort']:'round_id';
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


        if(isset($queryParams['type'])&&$queryParams['type']!==''){
            $condition['type'] = intval($queryParams['type']);
        }

        //标签ID检索
        if(isset($queryParams['competition_id'])&&$queryParams['competition_id']!==''){
            $condition['competition_id'] = intval($queryParams['competition_id']);
        }

        if(!empty($queryParams['title'])){
            $condition['title'] = array("like","%".trim($queryParams['title'])."%");
        }



        $total = $this->where($condition)->count();
        $data = $this
            ->where($condition)
            ->order("{$sort} {$order}")
            ->limit($offset,$length)
            ->select();
        $result['total'] = $total;
        $result['data'] = $data;
        return $result;
    }


    /**
     * 根据ID删除
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function delById($id){
        if(empty($id)){
            throw new \Exception("赛事ID必须");
        }
        $result = $this->where("competition_id=%d",$id)->delete();
        return $result;
    }


    /**
     * @param $id
     * @return array|mixed
     */
    public function getById($id){
        $result = $this->where("competition_id=%d",$id)->find();
        if(empty($result)){
            $result = [];
        }
        return $result;
    }

    /**
     * 根据订单ID获取信息
     * @param $id
     * @return array|mixed
     */
    public function getByOrderId($id){
        $result = $this->where("order_id=%d",$id)->find();
        if(empty($result)){
            $result = [];
        }
        return $result;
    }




    /**
     * @param $input
     * @return bool
     * @throws \Exception
     */
    public function updateCompetition($input){
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
     *
     * @param $input
     * @return mixed
     * @throws \Exception
     */
    public function addCompetition($input){
        $data = $this->create($input);
        if(!$data){
            throw new \Exception($this->getError());
        }else{
            $newId = $this->add($data);

            if(empty($newId)){
                throw new \Exception("新增失败");
            }
            return $newId;
        }
    }
}