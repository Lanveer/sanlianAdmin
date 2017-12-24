<?php
/**
 * Created by PhpStorm.
 * User: walter
 * Date: 2016/10/31
 * Time: 14:51
 */
namespace Admin\Controller;

use Common\Model\OrderModel;

/**
 * Class OrderController
 * @package Admin\Controller
 * @property OrderModel $orderModel
 */
class OrderController extends CommonController{
    private $orderModel;

    public function _initialize(){
        parent::_initialize();
        $this->orderModel = D("Order");
    }

    /**
     *  赛事订单
     */
    public function competitionList(){
        $this->display();
    }

    public function getCompetitionList(){
        $queryParams = [
            "limit" => $_REQUEST['limit'],
            "offset" => $_REQUEST['offset'],
            "sort" => $_REQUEST['sort'],
            "order" => $_REQUEST['order'],
            "start_time" => $_REQUEST['start_time'],
            "end_time" => $_REQUEST['end_time'],
            "order_id" => $_REQUEST['order_id'],
            "user_id" => $_REQUEST['user_id'],
            "type" => 4,
            "status" => $_REQUEST['status'],
            "refund_type" => $_REQUEST['refund_type'],
            "order_no" => $_REQUEST['order_no'],
            "pay_type" => $_REQUEST['pay_type'],
            "game_time" => $_REQUEST['game_time']
        ];


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
}