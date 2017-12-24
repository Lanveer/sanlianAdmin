<?php
/**
 * Author: foxyZeng
 * Date: 2016/4/28
 * Time: 9:39
 *
 * 支付类
 */



/**
 * Class UnionPay
 * 银联
 */
class UnionPay{

    const merId = "308510181110006";         //商户号

    public function __construct()
    {
        vendor("unionPay.sdk.acp_service");

    }

    /**
     * 代付交易，付款给用户
     * @param string $orderNo  商户订单流水号
     * @param string $accNo 银行卡号
     * @param string $certifId 身份证号
     * @param string $customerNm 持卡人姓名
     * @param int $txnAmt 交易额，单位分
     * @param string $txnTime date('YmdHis')
     * @return string
     * @throws Exception
     */
    public function payment($orderNo,$accNo,$certifId,$customerNm,$txnAmt,$txnTime){
        $customerInfo = [
            "certifTp" => "01",
            'certifId' => trim($certifId),
		    'customerNm' => trim($customerNm),
        ];
        $params = [
            'version' => '5.0.0',		      //版本号
            'encoding' => 'utf-8',		      //编码方式
            'signMethod' => '01',		      //签名方法
            'txnType' => '12',		          //交易类型
            'txnSubType' => '00',		      //交易子类
            'bizType' => '000401',		      //业务类型
            'accessType' => '0',		      //接入类型
            'channelType' => '08',		      //渠道类型
            'currencyCode' => '156',          //交易币种，境内商户勿改
            'backUrl' => SDK_BACK_NOTIFY_URL_PAYMENT, //后台通知地址
            'encryptCertId' => AcpService::getEncryptCertId(), //验签证书序列号

            //TODO 以下信息需要填写
            'merId' => self::merId,		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
            'orderId' => $orderNo,	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
            'txnTime' => $txnTime,	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
            'txnAmt' => strval($txnAmt),	//交易金额，单位分，此处默认取demo演示页面传递的参数
            'accNo' =>  AcpService::encryptData($accNo),     //卡号，新规范请按此方式填写
            'customerInfo' => AcpService::getCustomerInfoWithEncrypt($customerInfo), //持卡人身份信息，新规范请按此方式填写
        ];
        AcpService::sign($params);
        $url = SDK_BACK_TRANS_URL;
        $result = AcpService::post($params,$url);
        if(count($result)<=0){
            throw new \Exception("与银联服务器连接失败",502);
        }
        if(!AcpService::validate($result)){
            throw new \Exception("应答报文验证失败",502);
        }
        if($result["respCode"] == "00"){
            return AcpService::decryptData($result['accNo']);                           //返回银行卡号
        }elseif(in_array($result['respCode'],["03","04","05","01","12","34","60"])){
            throw new \Exception("处理超时，请稍后查询。",502);
        }else{
            throw new \Exception($result['respMsg']);
        }
    }

    /**
     * 银联支付订单
     * @param string $orderNo
     * @param int $txnAmt
     * @param string $txnTime
     * @return mixed tn
     * @throws Exception
     */
    public function payOrder($orderNo, $txnAmt, $txnTime){
        $params = [
            'version' => '5.0.0',                 //版本号
            'encoding' => 'utf-8',				  //编码方式
            'txnType' => '01',				      //交易类型
            'txnSubType' => '01',				  //交易子类
            'bizType' => '000201',				  //业务类型
            'frontUrl' =>  SDK_FRONT_NOTIFY_URL_PAYORDER,  //前台通知地址
            'backUrl' => SDK_BACK_NOTIFY_URL_PAYORDER,	  //后台通知地址
            'signMethod' => '01',	              //签名方法
            'channelType' => '08',	              //渠道类型，07-PC，08-手机
            'accessType' => '0',		          //接入类型
            'currencyCode' => '156',	          //交易币种，境内商户固定156

            //TODO 以下信息需要填写
            'merId' => self::merId,		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
            'orderId' => $orderNo,	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
            'txnTime' => $txnTime,	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
            'txnAmt' => $txnAmt
        ];
        AcpService::sign($params);
        $url = SDK_App_Request_Url;
        $result = AcpService::post($params,$url);
        if(count($result)<=0){
            throw new \Exception("与银联服务器连接失败",502);
        }
        if(!AcpService::validate($result)){
            throw new \Exception("应答报文验证失败",502);
        }
        if($result["respCode"] == "00"){
            return $result['tn'];                           //返回银行受理订单号
        }else{
            throw new \Exception($result['respMsg']);
        }
    }

    /**
     * 退款
     * @param $order_no
     * @param $queryId
     * @param $txnTime
     * @param $txnAmt
     * @return bool
     * @throws Exception
     */
    public function refund($order_no, $queryId, $txnTime, $txnAmt){
        $params = [
            'version' => '5.0.0',		      //版本号
            'encoding' => 'utf-8',		      //编码方式
            'signMethod' => '01',		      //签名方法
            'txnType' => '04',		          //交易类型
            'txnSubType' => '00',		      //交易子类
            'bizType' => '000201',		      //业务类型
            'accessType' => '0',		      //接入类型
            'channelType' => '07',		      //渠道类型
            'backUrl' => SDK_BACK_NOTIFY_URL_REFUND, //后台通知地址

            //TODO 以下信息需要填写
            'orderId' => $order_no,	    //商户订单号，8-32位数字字母，不能含“-”或“_”，可以自行定制规则，重新产生，不同于原消费，此处默认取demo演示页面传递的参数
            'merId' => self::merId,	        //商户代码，请改成自己的测试商户号，此处默认取demo演示页面传递的参数
            'origQryId' => $queryId, //原消费的queryId，可以从查询接口或者通知接口中获取，此处默认取demo演示页面传递的参数
            'txnTime' => $txnTime,	    //订单发送时间，格式为YYYYMMDDhhmmss，重新产生，不同于原消费，此处默认取demo演示页面传递的参数
            'txnAmt' => $txnAmt,       //交易金额，退货总金额需要小于等于原消费
        ];
        AcpService::sign($params);
//        var_dump($params);
        $url = SDK_BACK_TRANS_URL;
        $result = AcpService::post($params,$url);
        if(count($result)<=0){
            throw new \Exception("与银联服务器连接失败",502);
        }
        if(!AcpService::validate($result)){
            throw new \Exception("应答报文验证失败",502);
        }
        if($result["respCode"] == "00"){
            return true;
        }else{
            throw new \Exception($result['respMsg']);
        }
    }

    /**
     * 交易查询
     * @param $orderNo
     * @param $txnTime
     * @return string
     * @throws Exception
     */
    public function queryOrder($orderNo, $txnTime){
        $params = [
            'version' => '5.0.0',		  //版本号
            'encoding' => 'utf-8',		  //编码方式
            'signMethod' => '01',		  //签名方法
            'txnType' => '00',		      //交易类型
            'txnSubType' => '00',		  //交易子类
            'bizType' => '000000',		  //业务类型
            'accessType' => '0',		  //接入类型
            'channelType' => '07',		  //渠道类型

            //TODO 以下信息需要填写
            'merId' => self::merId,	    //商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
            'orderId' => $orderNo,	//请修改被查询的交易的订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数
            'txnTime' => $txnTime,
        ];
        AcpService::sign($params);
        $url = SDK_SINGLE_QUERY_URL;

        $result = AcpService::post ( $params, $url);
        if(count($result)<=0){
            throw new \Exception("与银联服务器连接失败",502);
        }
        if(!AcpService::validate($result)){
            throw new \Exception("应答报文验证失败",502);
        }
        if($result['respCode'] == '00'){
            if($result['origRespCode'] == '00'){
                return $result["respMsg"];
            }elseif(in_array($result['origRespCode'],["03","04","05","01","12","34","60"])){
                return "交易处理中，请稍后查询";
            }else{
                return "交易失败：".$result['origRespMsg'];
            }
        }elseif(in_array($result['respCode'],["03","04","05"])){
            return "处理超时，请稍后查询";
        }else{
            return "失败：".$result['origRespMsg'];
        }
    }
}



/**
 * Class WxPay
 * 微信支付
 *
 */
class WxPay{
    static $NOTIFY_URL;         //支付回调地址
    static $TRADE_STATE = [
        "SUCCESS" => "支付成功",
        "REFUND" => "转入退款",
        "NOTPAY" => "未支付",
        "CLOSED" => "已关闭",
        "REVOKED" => "已撤销（刷卡支付）",
        "USERPAYING" => "用户支付中",
        "PAYERROR" => "支付失败"
    ];

    public function __construct()
    {
        vendor("wxpay.lib.WxPay#Api");
        self::$NOTIFY_URL = C("HOST")."index.php/Admin/Pay/wxPayCallBack";
    }

    /**
     * 微信下单
     * @param string $order_no 商户订单号
     * @param string $txnTime 交易时间 date("YmdHis")
     * @param int $txnAmt 交易金额 单位为分
     * @return array 成功时返回
     * @throws Exception
     * @throws WxPayException
     */
    public function payOrder($order_no, $txnTime, $txnAmt){
        $input = new WxPayUnifiedOrder();
        $input->SetBody($order_no);
        $input->SetAttach("");
        $input->SetOut_trade_no($order_no);
        $input->SetTotal_fee($txnAmt);
        $input->SetTime_start($txnTime);
        $input->SetTime_expire(date("YmdHis", strtotime($txnTime) + 600));
        $input->SetNotify_url(self::$NOTIFY_URL);
        $input->SetTrade_type("APP");

        $result = WxPayApi::unifiedOrder($input);
        if($result['return_code']!="SUCCESS"){
            throw new \Exception($result['return_msg']);
        }
        if($result['result_code']!="SUCCESS"){
            throw new \Exception($result['err_code_des']);
        }

        return $result;
    }

    /**
     * 获取APP端的请求参数
     * @param $prepayId
     * @return array
     */
    public function getAppRequest($prepayId){
        $input = new WxPayRequest();
        $input->setPrepayid($prepayId);
        $result = WxPayApi::getSignRequest($input);
        return $result;
    }

    /**
     * 微信支付退款
     * @param string $transaction_id 微信订单号
     * @param int $money 交易金额 单位为分
     * @return bool 成功时返回
     * @throws Exception
     * @throws WxPayException
     */
    public function refund($transaction_id, $money){
        $input = new WxPayRefund();
        $input->SetTransaction_id($transaction_id);
        $input->SetTotal_fee($money|0);
        $input->SetRefund_fee($money|0);
        $input->SetOut_refund_no(date("YmdHis"));
        $input->SetOp_user_id(WxPayConfig::MCHID);

        $result = WxPayApi::refund($input);
//        var_dump($result);
        if($result['result_code']=='SUCCESS'){
            $result = true;
        }elseif(!empty($result['err_code_des'])){
            throw new \Exception($result['err_code_des']);
        }else{
            throw new \Exception($result['return_msg']);
        }
        return $result;
    }

    /**
     * 微信订单查询
     * @param string $order_no 商户订单号
     * @param string $transaction_id 微信订单号
     * @return array 成功时返回
     * @throws Exception
     * @throws WxPayException
     */
    public function orderQuery($order_no="", $transaction_id=""){
        $input = new WxPayOrderQuery();
        if(!empty($order_no)){
            $input->SetOut_trade_no($order_no);
        }elseif(!empty($transaction_id)){
            $input->SetTransaction_id($transaction_id);
        }else{
            throw new \Exception("商户订单号或微信订单号必须！");
        }
        $response = WxPayApi::orderQuery($input);
        $result = [];
        if($response['return_code']!='SUCCESS'){
            if(!empty($response['return_msg'])){
                $result['msg'] = $response['return_msg'];
            }else{
                $result['msg'] = "查询失败，请稍后再试";
            }
        }elseif($response['result_code']!='SUCCESS'){
            $result['msg'] = !empty($response['err_code_des'])?$response['err_code_des']:"请稍后再试";
        }else{
            $result['msg'] = self::$TRADE_STATE[$response['trade_state']];
        }

        return $result;
    }
}


/**
 * Class AliPay
 * 支付宝支付
 */
class AliPay{
    static $NOTIFY_URL;
    static $REFUND_URL;
    private $aliPayApi;

    public function __construct(){
        vendor("alipay.aliPayApi");
        self::$NOTIFY_URL = C("HOST")."index.php/Admin/Pay/aliPayCallBack";
        self::$REFUND_URL = C("HOST")."index.php/Admin/Pay/aliPayRefundCallBack";
        $this->aliPayApi = new AliPayApi();
    }

    /**
     * 验证支付宝支付回调消息是否合法
     * @param $data
     * @return bool
     */
    public function verifyCallBack($data){
        if(empty($data)) {
            return false;
        }
        else {
            //生成签名结果
            $isSign = $this->aliPayApi->getSignVeryfy($data, $data["sign"]);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'false';
            if (!empty($data["notify_id"])){
                $responseTxt = $this->aliPayApi->getResponse($data["notify_id"]);
            }
            //验证
            //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
            if (preg_match("/true$/i",$responseTxt) && $isSign) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     *  获取带签名的请求体
     * @param string $order_no  订单号
     * @param float $money   交易金额 单位：元
     * @return string
     */
    public function getRequestParam($order_no, $money){
        $request = [
            "partner"=>PARTNER,
            "seller_id"=>SELLER_ID,
            "out_trade_no"=> $order_no,
            "subject" => "调查取证",
            "body" => "调查取证",
            "total_fee" => $money,
            "notify_url" => self::$NOTIFY_URL,
            "service" => "mobile.securitypay.pay",
            "payment_type" => "1",
            "_input_charset" => "utf-8",
            "it_b_pay" => "30m",
//            "return_url" => "m.alipay.com"
        ];
        $request_str = $this->aliPayApi->getRequestStr(($request));
        $sign = $this->aliPayApi->rsaSign($request_str);
        $request['sign'] = urlencode($sign);
        $request['sign_type'] = "RSA";
        $result = $this->aliPayApi->getRequestStr($request);
        return $result;
    }

    /**
     * 支付宝退款
     * @param string $batch_no 退款批次号
     * @param $trade_no
     * @param $money
     * @return string 支付宝退款URL
     */
    public function refund($batch_no,$trade_no, $money){
        $result = $this->aliPayApi->refund($batch_no,$trade_no,$money,self::$REFUND_URL);
        return $result;
    }

}