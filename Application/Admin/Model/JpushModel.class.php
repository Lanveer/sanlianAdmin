<?php

namespace Admin\Model;
class JpushModel {
    private $_appkeys = '9b6613473f6c2aefdcebe04d';
    private $_masterSecret = '282d9c1defe3017ed4ff8a46';
    //private $url = "https://api.jpush.cn/v3/push";      //推送的地址

    public function __construct($app_key=null, $master_secret=null,$url=null) {
        if ($app_key) $this->app_key = $app_key;
        if ($master_secret) $this->master_secret = $master_secret;
        if ($url) $this->url = $url;
    }


    public function push($data,$contentArr){
        $url = 'http://api.jpush.cn:8800/sendmsg/v2/sendmsg';//(必填)调用地址
        $receiver_type = $data['receiver_type'];//(必填)接收者类型。1、指定的 IMEI。此时必须指定 appKeys。2、指定的 tag。3、指定的 alias。4、 对指定 appkey 的所有用户推送消息。
        $receiver_value = $data['receiver_value'];//(选填)发送范围值，与 receiver_type相对应。 1、IMEI只支持一个 2、tag 支持多个，使用 "," 间隔。 3、alias 支持多个，使用 "," 间隔。 4、不需要填
        $msg_type = $data['msg_type'];//(必填)发送消息的类型，1、通知，2、自定义消息（只支持android）
        $msg_content = '';//(必填)发送消息的内容。
        $platform = 'android,ios';//(必填)目标用户终端手机的平台类型，如： android, ios 多个请使用逗号分隔。
        $time_to_live = 86400;//(选填)从消息推送时起，保存离线的时长。秒为单位。最多支持10天（864000秒）。0 表示该消息不保存离线。即：用户在线马上发出，当前不在线用户将不会收到此消息。

        //消息内容格式化

        $content['n_builder_id'] = $contentArr['n_builder_id']; //（可选）1-1000的数值，不填则默认为 0，使用 极光Push SDK 的默认通知样式。只有 Android 支持这个参数
        $content['n_title']      = $contentArr['n_title']; //（可选）通知标题。不填则默认使用该应用的名称。只有 Android支持这个参考。
        $content['n_content']    = $contentArr['n_content']; //（必填）通知内容。
        $content['n_extras']     = $contentArr['n_extras']; //（可选）通知附加参数。客户端可取得全部内容。


        $msg_content = json_encode($content);


        //组装请求参数
        $param = '';
        $param .= '&sendno='.$data['sendno'];
        $appkeys = $this->_appkeys;
        $param .= '&app_key='.$appkeys;
        $param .= '&receiver_type='.$receiver_type;
        $param .= '&receiver_value='.$receiver_value;
        $masterSecret = $this->_masterSecret;
        $verification_code = md5($data['sendno'].$receiver_type.$receiver_value.$masterSecret);//生成验证串
        $param .= '&verification_code='.$verification_code;
        $param .= '&msg_type='.$msg_type;
        $param .= '&msg_content='.$msg_content;
        $param .= '&platform='.$platform;


        $pushResult = $this->request_post($url, $param);
        $res_arr = json_decode($pushResult, true);
        if (intval($res_arr['errcode'])!=0){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 模拟post进行url请求
     * @param string $url
     * @param string $param
     * @return bool|mixed
     */
    function request_post($url = '', $param = '')
    {
        if(empty($url) || empty($param))
        {
            return false;
        }

        $postUrl  = $url;
        $curlPost = $param;
        $ch       = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch); //运行curl
        curl_close($ch);

        return $data;
    }

}