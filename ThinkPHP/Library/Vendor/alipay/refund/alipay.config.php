<?php
/* *
 * 配置文件
 * 版本：3.4
 * 日期：2016-03-08
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
	
 * 安全校验码查看时，输入支付密码后，页面呈灰色的现象，怎么办？
 * 解决方法：
 * 1、检查浏览器配置，不让浏览器做弹框屏蔽设置
 * 2、更换浏览器或电脑，重新登录查询。
 */
 
//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
// 合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://b.alipay.com/order/pidAndKey.htm
$alipay_config['partner']		= '2088221550177244';

// 卖家支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
$alipay_config['seller_user_id']=$alipay_config['partner'];

// 商户的私钥文件路径,原始格式，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
$alipay_config['private_key_path']	= 'key/rsa_private_key.pem';

// 支付宝公钥（后缀是.pen）文件相对路径，查看地址：https://b.alipay.com/order/pidAndKey.htm 
$alipay_config['ali_public_key_path']= 'key/alipay_public_key.pem';

// 服务器异步通知页面路径，需http://格式的完整路径，不能加?id=123这类自定义参数,必须外网可以正常访问
$alipay_config['notify_url']="http://商户网关网址/refund_fastpay_by_platform_pwd-PHPUTF-8/notify_url.php";

// 签名方式
$alipay_config['sign_type']    = strtoupper('RSA');

// 退款日期 时间格式 yyyy-MM-dd HH:mm:ss
//date_default_timezone_set('PRC');//设置当前系统服务器时间为北京时间，PHP5.1以上可使用。
$alipay_config['refund_date']=date("Y-m-d H:i:s",time());;

// 调用的接口名，无需修改
$alipay_config['service']='refund_fastpay_by_platform_pwd';

//字符编码格式 目前支持 gbk 或 utf-8
$alipay_config['input_charset']= strtolower('utf-8');

//ca证书路径地址，用于curl中ssl校验
//请保证cacert.pem文件在当前文件夹目录中
$alipay_config['cacert']    = getcwd().'\\cacert.pem';

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$alipay_config['transport']    = 'http';

//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
?>