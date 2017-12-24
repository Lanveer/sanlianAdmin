<?php
namespace Admin\Model;
use Think\Model\MongoModel;
class MessageModel extends MongoModel{
    protected $tablePrefix = "";
    protected $dbName = "qiuchang3";
    protected $connection = "MONGODB_CONNECTION";
    protected $_validate = [
        array('payment_method','require','退款类型必须！',1,'regex',1),    //(支付宝-0 | 微信-1 | 银联-2)
    ];
    protected $_auto = [
    
    ];
}