<?php
/**
 * Author: foxyZeng
 * Date: 2016/5/4
 * Time: 9:51
 */
namespace Admin\Model;

use Think\Model\MongoModel;

class DrawingsCallBackRecordModel extends MongoModel{
    protected $connection = "MONGODB_CONNECTION";
    protected $_validate = [
        array('payment_method','require','退款类型必须！',1,'regex',1),    //(支付宝-0 | 微信-1 | 银联-2)
    ];
    protected $_auto = [

    ];

    /**
     * 新增银联退款记录
     * @param $input
     * @return mixed
     * @throws \Exception
     */
    public function addRecord($input){
        $input['createdAt'] = date("Y-m-d H:i:s");
        $data = $this->create($input);
        if(!$data){
            throw new \Exception($this->getError());
        }else{
            try {
                $newId = $this->add($data);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage(),555);
            }
            if (empty($newId)) {
                throw new \Exception("新增提现回调记录失败");
            }
            return $newId;
        }

    }
    
}