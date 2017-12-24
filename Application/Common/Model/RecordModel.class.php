<?php
/**
 * Created by PhpStorm.
 * User: walter
 * Date: 2016/7/19
 * Time: 15:43
 */
namespace Common\Model;

use Think\Model;

class RecordModel extends Model{
    protected $_validate = [
        array('type','require','记录类型必须！',1,'regex',1),    //新增时必须字段
        array('uid','require','用户必须！',1,'regex',1),    //新增时必须字段
        array('money','require','金额必须！',1,'regex',1),
    ];
    //自动完成会把字段填全，建议别用更新
    protected $_auto = [
        array('create_time','time',1,'function')   //新增时执行函数
    ];



    /**
     * 根据条件获取标签
     * @param $queryParams
     * @return mixed
     */
    public function search($queryParams){
        $condition=[];
        $length = $queryParams['limit']?$queryParams['limit']:10;
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

        if(!empty($queryParams['data'])){
            $condition['data'] = trim($queryParams['data']);
        }

        if(isset($queryParams['uid'])&&$queryParams['uid']!==''){
            $condition['uid'] = intval($queryParams['uid']);
        }


        if(!empty($queryParams['remark'])){
            $condition['remark'] = array("like","%".trim($queryParams['remark']."%"));
        }

        $total = $this->where($condition)->count();
        $data = $this
            ->where($condition)
            ->order("{$sort} {$order}")
            ->limit($offset,$length)
            ->select();
        if(empty($data)){
            $data = [];
        }else{
            foreach($data as $index => $value){

            }
        }
        $result['total'] = $total;
        $result['data'] = $data;
        return $result;
    }

    /**
     * 根据标签ID获取标签信息
     * @param $id
     * @return array|mixed
     */
    public function getById($id){
        $result = $this->where("id=%d",$id)->find();
        if(empty($result)){
            $result = [];
        }
        return $result;
    }

    public function updateConfig($input){
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
     * 根据ID删除
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
     * 新增
     * @param $input
     * @return mixed
     * @throws \Exception
     */
    public function addRecord($input){
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

