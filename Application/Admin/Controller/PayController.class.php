<?php
/**
 * Author: foxyZeng
 * Date: 2016/4/28
 * Time: 9:18
 */
namespace Admin\Controller;

use Common\Model\OrderModel;
use Think\Controller;

/**
 * Class PayController
 * @package Admin\Controller
 * @property \AliPay $aliPay
 * @property OrderModel $orderModel
 */
class PayController extends Controller{
    private $orderModel;
    private $aliPay;

    public function _initialize(){
        ignore_user_abort(true);
        header("Access-Control-Allow-Origin:*");
        vendor("wxpay.lib.WxPay#Api");
        $this->aliPay = new \AliPay();
        $this->orderModel = D("Order");
    }


    /**
     *  阿里支付回调接口
     */
    public function aliPayCallBack(){
        S("aliPay".date("m-d H:i:s"),json_encode($_POST));
        $response = $_POST;
        if(empty($response)){
            return false;
        }
        $isVerify = $this->aliPay->verifyCallBack($response);
        if(!$isVerify){
            return false;
        }else{
            try {                                                                           //记录参数
                $callback = $_POST;
                $callback['payment_method'] = 0;
                $this->orderModel->addRecord($callback);
            } catch (\Exception $e) {

            }
        }
        $order_no = $response['out_trade_no'];
        if(empty($order_no)){
            return false;
        }
        if($response['trade_status']=='WAIT_BUYER_PAY'){
            echo "success";
            exit();
        }
        $orderInfo = $this->orderModel->getByOrderNo($order_no);
        if(empty($orderInfo)){
            return false;
        }
        //交易成功
        if($response['trade_status']=='TRADE_SUCCESS'||$response['trade_status']=='TRADE_FINISHED'){
            if(!empty($orderInfo['trade_no'])){
                exit("success");
            }
            $data = [
                "order_id" => $orderInfo['order_id'],
                "order_no" => $order_no,
                "notify_time" => $response['notify_time'],
                "trade_no" => $response['trade_no'],
                "buyer_email" => $response['buyer_email'],
                "buyer_id" => $response['buyer_id'],
                "notify_id" => $response['notify_id'],
                "status" => 2
            ];

            try {
                echo "hi";
            } catch (\Exception $e) {
                S("aliERR_".time(),$e->getMessage());
                return false;
            }
            echo "success";
        }

    }

    /**
     *  支付宝退款回调接口
     */
    public function aliPayRefundCallBack(){
        S("aliPayRefund".date("m-d H:i:s"),json_encode($_POST));
        $result_code = explode("^",$_POST['result_details'])[2];
        if($result_code!="SUCCESS"||empty($_POST['success_num'])||empty($_POST['batch_no'])){
            return false;
        }
        $batch_no = $_POST['batch_no'];
        $orderInfo = $this->orderModel->getByBatchNo($batch_no);
        $order_id = intval($orderInfo['order_id']);
        $order_type = intval($orderInfo['type']);
        $san_money = floatval($orderInfo['san_money']);
        $user_id = intval($orderInfo['user_id']);
        if(empty($orderInfo)){
            return false;
        }else{
            if($orderInfo['status']==3){
                return false;
            }
            try {
                $qualifyingModel = D("Qualifying");
                $friendlyModel = D("FriendlyOrder");
                $userModel = D("User");
                $recordModel = D("Record");
                $orderChange = [
                    "order_id" => $order_id,
                    "status" => 3
                ];
                $friendlyChange = [
                    "status" => 4
                ];
                $qualifying_h_change = [
                    "status" => 2,
                    "pay_num" => 0,
                    "refund_num" => 1
                ];
                $qualifying_g_change = [
                    "refund_num" => 2
                ];

                $recordData = [
                    "uid" => $user_id,
                    "type" => 4,
                    "remark" => "退款",
                    "data" => $order_id,
                    "money" => $san_money
                ];
                $this->orderModel->startTrans();
                $this->orderModel->updateOrder($orderChange);
                if($order_type==0){
                    $qualifyingModel->where("home_order_id=%d",$order_id)->save($qualifying_h_change);
                }
                if($order_type==1){
                    $friendlyModel->where("order_id=%d",$order_id)->save($friendlyChange);
                }
                if($order_type==3){
                    $qualifyingModel->where("guest_order_id=%d",$order_id)->setDec("pay_num",1);
                    $qualifyingModel->where("guest_order_id=%d",$order_id)->save($qualifying_g_change);
                }
                if($san_money){
                    $userModel->where("user_id=%d",$user_id)->setInc("san_money",$san_money);
                    $recordModel->addRecord($recordData);
                }
                $this->orderModel->commit();
                response();
            } catch (\Exception $e) {
                $this->orderModel->rollback();
                response([],501,$e->getMessage());
            }
        }

    }


}