<?php
/**
 * Created by PhpStorm.
 * User: walter
 * Date: 2016/7/19
 * Time: 15:43
 */
namespace Common\Model;

use Think\Model;

class UserModel extends Model{
    protected $_validate = [

    ];
    //自动完成会把字段填全，建议别用更新
    protected $_auto = [
        array('create_time','time',1,'function')   //新增时执行函数
    ];


    /**
     * 根据条件获取标签
     * @param $queryParams
     * @param array $ext
     * @return mixed
     */
    public function search($queryParams,$ext=[]){
        $condition=[];
        $length = $queryParams['limit']?$queryParams['limit']:10;
        $offset = $queryParams['offset']?$queryParams['offset']:0;
        $sort = $queryParams['sort']?$queryParams['sort']:'user_id';
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


        //ID检索
        if(isset($queryParams['user_id'])&&$queryParams['user_id']!==''){
            $condition['user_id'] = intval($queryParams['user_id']);
        }

        if(isset($queryParams['vip_id'])&&$queryParams['vip_id']!==''){
            $condition['vip_id'] = intval($queryParams['vip_id']);
        }

        //ID检索
        if(isset($queryParams['court_id'])&&$queryParams['court_id']!==''){
            $condition['court_id'] = intval($queryParams['court_id']);
        }

        if(isset($queryParams['province_id'])&&$queryParams['province_id']!==''){
            $condition['province_id'] = intval($queryParams['province_id']);
        }

        if(isset($queryParams['city_id'])&&$queryParams['city_id']!==''){
            $condition['city_id'] = intval($queryParams['city_id']);
        }

        if(isset($queryParams['county_id'])&&$queryParams['county_id']!==''){
            $condition['county_id'] = intval($queryParams['county_id']);
        }

        if(!empty($queryParams['phone'])){
            $condition['phone'] = trim($queryParams['phone']);
        }

        if(!empty($queryParams['nickname'])){
            $condition['nickname'] = array("like","%".trim($queryParams['nickname']."%"));
        }

        if(!empty($queryParams['remark'])){
            $condition['remark'] = array("like","%".trim($queryParams['remark']."%"));
        }

        if(!empty($queryParams['province'])){
            $condition['province'] = array("like","%".trim($queryParams['province']."%"));
        }

        if(!empty($queryParams['city'])){
            $condition['city'] = array("like","%".trim($queryParams['city']."%"));
        }

        if(!empty($queryParams['county'])){
            $condition['county'] = array("like","%".trim($queryParams['county']."%"));
        }
//        var_dump($queryParams);
//        var_dump($condition);

        $condition = array_merge($condition,$ext);
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
        $result = $this->where("user_id=%d",$id)->find();
        if(empty($result)){
            $result = [];
        }
        return $result;
    }

    public function updateUser($input){
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
        $result = $this->where("user_id=%d",$id)->delete();
        return $result;
    }

    /**
     * 新增
     * @param $input
     * @return mixed
     * @throws \Exception
     */
    public function addUser($input){
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

