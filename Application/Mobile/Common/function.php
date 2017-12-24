<?php
/**
 * Created by PhpStorm.
 * User: walter
 * Date: 2017/1/13
 * Time: 16:08
 */



/**
 * 后台返回
 * @param array $data
 * @param int $code
 * @param string $msg
 */
function response($data=[], $msg="", $code=200){
    header("Content-Type:application/json");
    http_response_code($code);
    if(!empty($msg)){
        exit($msg);
    }
//    if(empty($data)){
//        $data=new stdClass();
//    }
    exit(json_encode($data));
}