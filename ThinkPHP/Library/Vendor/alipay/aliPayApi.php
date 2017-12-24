<?php
require_once "alipay.config.php";
/**
 * Author: foxyZeng
 * Date: 2016/5/5
 * Time: 15:09
 */

class AliPayApi{
    const HTTPS_VERIFY_URL = "https://mapi.alipay.com/gateway.do?service=notify_verify&";
    const HTTP_VERIFY_URL = 'http://notify.alipay.com/trade/notify_query.do?';
    const TRADE_URL = 'https://mapi.alipay.com/gateway.do?';

    public function __construct(){

    }

    /**
     * 退款
     * @param $batch_no
     * @param string $trade_no 支付宝交易号
     * @param float $money 交易金额 单位元
     * @param string $notify_url 回调接口URL
     * @return string 请求URL
     */
    public function refund($batch_no,$trade_no, $money, $notify_url){
        $request = [
            "service" => "refund_fastpay_by_platform_pwd",
            "partner"=>PARTNER,
            "seller_email"=>SELLER_ID,
            "seller_user_id" => PARTNER,
            "_input_charset" => "utf-8",
            "notify_url"=> $notify_url,
            "refund_date" => date("Y-m-d H:i:s"),
            "batch_num" => "1",
            "batch_no" => $batch_no,
            "detail_data" => $trade_no."^".$money."^协商退款"
        ];

        ksort($request);
        $request_str = $this->createLinkstring($request);
        $sign = $this->rsaSign($request_str);
        $request['sign'] = ($sign);
        $request['sign_type'] = "RSA";
//        var_dump($request);
        $request_data = $this->createLinkstringUrlencode($request);
        $request_url = self::TRADE_URL.$request_data;
        return $request_url;
    }

    /**
     * 生产请求字符串格式
     * @param $request
     * @return bool|string
     */
    public function getRequestStr($request){
        if(!is_array($request)){
            return false;
        }
        $str = "";
        foreach($request as $index=>$value){
            $str.=($index."="."\"".$value."\"&");
        }
        $str = rtrim($str,"&");
        return $str;
    }

    /**
     * 获取返回时的签名验证结果
     * @param array $para_temp 通知返回来的参数数组
     * @param string $sign 返回的签名结果
     * @return bool 签名验证结果
     */
    public function getSignVeryfy($para_temp, $sign) {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);
        $isSgin = false;
        switch (SIGN_TYPE) {
            case "RSA" :
                $isSgin = $this->rsaVerify($prestr, $sign);
                break;
            default :
                $isSgin = false;
        }

        return $isSgin;
    }

    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id 通知校验ID
     * @return 服务器ATN结果
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
    public function getResponse($notify_id) {
        $transport = strtolower(TRANSPORT);
        $partner = PARTNER;
        $veryfy_url = '';
        if($transport == 'https') {
            $veryfy_url = self::HTTPS_VERIFY_URL;
        }
        else {
            $veryfy_url = self::HTTP_VERIFY_URL;
        }
        $veryfy_url = $veryfy_url."partner=" . $partner . "&notify_id=" . $notify_id;
        $responseTxt = $this->getHttpResponseGET($veryfy_url);

        return $responseTxt;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param array $para 需要拼接的数组
     * @return string 拼接完成以后的字符串
     */
    public function createLinkstring($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=".$val."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);

        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

        return $arg;
    }
    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
     * @param array $para 需要拼接的数组
     * @return string 拼接完成以后的字符串
     */
    public function createLinkstringUrlencode($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=".urlencode($val)."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);

        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

        return $arg;
    }
    /**
     * 除去数组中的空值和签名参数
     * @param array $para 签名参数组
     * @return array 去掉空值与签名参数后的新签名参数组
     */
    public function paraFilter($para) {
        $para_filter = array();
        while (list ($key, $val) = each ($para)) {
            if($key == "sign" || $key == "sign_type" || $val == "")continue;
            else	$para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }
    /**
     * 对数组排序
     * @param array $para 排序前的数组
     * @return array 排序后的数组
     */
    public function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }
    /**
     * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
     * 注意：服务器需要开通fopen配置
     * @param string $word 要写入日志里的文本内容 默认值：空值
     */
    public function logResult($word='') {
        $fp = fopen("log.txt","a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,"执行日期：".strftime("%Y%m%d%H%M%S",time())."\n".$word."\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * 远程获取数据，POST模式
     * 注意：
     * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
     * @param string $url 指定URL完整路径地址
     * @param array $para 请求的数据
     * @param string $input_charset 编码格式。默认值：空值
     * @return array 远程输出的数据
     */
    public function getHttpResponsePOST($url, $para, $input_charset = '') {

        if (trim($input_charset) != '') {
            $url = $url."_input_charset=".$input_charset;
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
        curl_setopt($curl, CURLOPT_CAINFO,CACERT_PATH);//证书地址
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl,CURLOPT_POST,true); // post传输数据
        curl_setopt($curl,CURLOPT_POSTFIELDS,$para);// post传输数据
        $responseText = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);

        return $responseText;
    }

    /**
     * 远程获取数据，GET模式
     * 注意：
     * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
     * @param string $url 指定URL完整路径地址
     * @return array 远程输出的数据
     */
    public function getHttpResponseGET($url) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
        curl_setopt($curl, CURLOPT_CAINFO,CACERT_PATH);//证书地址
        $responseText = curl_exec($curl);
//        var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);

        return $responseText;
    }

    /**
     * 实现多种字符编码方式
     * @param string $input 需要编码的字符串
     * @param string $_output_charset 输出的编码格式
     * @param string $_input_charset 输入的编码格式
     * @return string 编码后的字符串
     */
    public function charsetEncode($input,$_output_charset ,$_input_charset) {
        $output = "";
        if(!isset($_output_charset) )$_output_charset  = $_input_charset;
        if($_input_charset == $_output_charset || $input ==null ) {
            $output = $input;
        } elseif (function_exists("mb_convert_encoding")) {
            $output = mb_convert_encoding($input,$_output_charset,$_input_charset);
        } elseif(function_exists("iconv")) {
            $output = iconv($_input_charset,$_output_charset,$input);
        } else die("sorry, you have no libs support for charset change.");
        return $output;
    }

    /**
     * 实现多种字符解码方式
     * @param string $input 需要解码的字符串
     * @param string $_output_charset 输出的解码格式
     * @param string $_input_charset 输入的解码格式
     * @return string 需要解码的字符串
     */
    public function charsetDecode($input,$_input_charset ,$_output_charset) {
        $output = "";
        if(!isset($_input_charset) )$_input_charset  = $_input_charset ;
        if($_input_charset == $_output_charset || $input ==null ) {
            $output = $input;
        } elseif (function_exists("mb_convert_encoding")) {
            $output = mb_convert_encoding($input,$_output_charset,$_input_charset);
        } elseif(function_exists("iconv")) {
            $output = iconv($_input_charset,$_output_charset,$input);
        } else die("sorry, you have no libs support for charset changes.");
        return $output;
    }

    /**
     * 获取签名
     * @param $data
     * @return string 签名结果
     */
    public function getSign($data){
        $sign = rsaSign($data);
        $sign = urlencode($data);
        return $sign;
    }

    /**
     * RSA签名
     * @param string $data 待签名数据
     * @return string 签名结果
     */
    public function rsaSign($data) {
        $priKey = file_get_contents(PRIVATE_KEY_PATH);
        $res = openssl_get_privatekey($priKey);
//        var_dump($priKey,$res);
        openssl_sign($data, $sign, $res);
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * RSA验签
     * @param $data 待签名数据
     * @param $sign 要校对的的签名结果
     * @return 验证结果
     */
    public function rsaVerify($data, $sign)  {
        $pubKey = file_get_contents(ALI_PUBLIC_KEY_PATH);
        $res = openssl_get_publickey($pubKey);
        $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        openssl_free_key($res);
        return $result;
    }

    /**
     * RSA解密
     * @param $content 需要解密的内容，密文
     * @param $private_key_path 商户私钥文件路径
     * @return 解密后内容，明文
     */
    public function rsaDecrypt($content, $private_key_path) {
        $priKey = file_get_contents($private_key_path);
        $res = openssl_get_privatekey($priKey);
        //用base64将内容还原成二进制
        $content = base64_decode($content);
        //把需要解密的内容，按128位拆开解密
        $result  = '';
        for($i = 0; $i < strlen($content)/128; $i++  ) {
            $data = substr($content, $i * 128, 128);
            openssl_private_decrypt($data, $decrypt, $res);
            $result .= $decrypt;
        }
        openssl_free_key($res);
        return $result;
    }


}