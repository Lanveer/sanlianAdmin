<?php
/**
 * 获取请求数据
 * @return array|mixed
 */
function getRequest(){
    $data = [];
    $content_type = $_SERVER['CONTENT_TYPE'];
    $type = explode(";",$content_type)[0];
    if($type=='application/json'){
        $post_body = file_get_contents("php://input");
        $data =array_merge($data,json_decode($post_body,true));
    }
    if(!empty($_POST)){
        $data = array_merge($data,$_POST);
    }
    if(!empty($_GET)){
        $data = array_merge($data,$_GET);
    }
    return $data;
}

/**
 *  验证是否为正确手机号
 * @param string $mobile
 * @return bool
 */
function isMobile($mobile){
    if (!is_numeric($mobile)) {
        return false;
    }
    return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
}

/**
 *  验证是否为URL地址
 * @param string $url
 * @return bool
 */
function isURL($url){
    if(empty($url)){
        return false;
    }
    return preg_match('/\b(([\w-]+:\/\/?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))/',$url) ? true : false;
}

/**
 *  上传图片
 * @param string $savePath  图片相对上传地址
 * @return array    上传成功后的地址
 * @throws Exception    上传出错抛出
 */
function uploadImg($savePath){
    $files = [];
    $savePath = trim($savePath,'/').'/';
    $config = [
        'maxSize' => 1048576*10,   //10M
        'rootPath' => 'Public/img/',
        'savePath' => trim($savePath),
        'saveName' => array('uniqid',''),
        'subName'  => array('date', 'Ymd'),
        'exts' => array('jpg','gif','png','jpeg'),
    ];
    if(!file_exists($config['rootPath'].$savePath)){
        mkdir($config['rootPath'].$savePath,0777,true);
    }

    $uploadModel = new \Think\Upload($config);
    $info = $uploadModel->upload();
    if(!$info){
        throw new \Exception($uploadModel->getError());
    }else{
        foreach($info as $index => $file){
            $path =  $uploadModel->rootPath.$file['savepath'].$file['savename'];
            $info[$index]['path'] = $path;
            $img_info = getimagesize($path);
            $width = $img_info[0];
            $height = $img_info[1];
            $info[$index]['src'] = C("HOST").$info[$index]['path']."?width={$width}&height={$height}";
        }
    }
    return $info;
}

/**
 *  获取url的相对地址
 * @param string $url  绝对地址
 * @return string|bool 相对地址
 */
function getRelativePath($url){
    if(!is_string($url)){
        return false;
    }
    $result = explode(__ROOT__."/",$url);
    $relativePath = $result[1];
    return $relativePath;
}

/**
 * 生成订单号
 * @return string
 */
function build_order_no(){
    return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}

/**
 * 根据时间戳获取简单的时间描述
 * @param int $timestamp
 * @return bool|string
 */
function getSimpleTime($timestamp){
    $timestamp|=0;
    $rtime = date("m-d H:i",$timestamp);
    $htime = date("H:i",$timestamp);
    $time = time() - $timestamp;
    if ($time < 60) {
        $str = '刚刚';
    }
    elseif ($time < 60 * 60) {
        $min = floor($time/60);
        $str = $min.'分钟前';
    }
    elseif ($time < 60 * 60 * 24) {
        $h = floor($time/(60*60));
        $str = $h.'小时前 '.$htime;
    }
    elseif ($time < 60 * 60 * 24 * 3) {
        $d = floor($time/(60*60*24));
        if($d==1)
            $str = '昨天 '.$rtime;
        else
            $str = '前天 '.$rtime;
    }
    else {
        $str = $rtime;
    }
    return $str;
}

/**
 * 中文拼音排序
 * @param $arr
 * @return array
 */
function utf8_sort(&$arr){
    $newArry = [];
    $result = [];
    foreach($arr as $value){
        $newArry[] = iconv("UTF-8","GB2312",$value);
    }
    asort($newArry);
    foreach($newArry as $value){
        $result[] = iconv("GB2312","UTF-8",$value);
    }
    $arr = $result;
}

/**
 * 上传文件
 * @param $savePath
 * @return array|bool
 * @throws Exception
 */
function uploadFile($savePath){
    $files = [];
    $savePath = trim($savePath,'/').'/';
    $config = [
        'maxSize' => 1048576*100,   //10M
        'rootPath' => 'Public/',
        'savePath' => trim($savePath),
        'saveName' => array('uniqid',''),
        'subName'  => array('date', 'Ymd'),
        'exts' => array('jpg','gif','png','jpeg','apk'),
    ];
    if(!file_exists($config['rootPath'].$savePath)){
        mkdir($config['rootPath'].$savePath,0777,true);
    }

    $uploadModel = new \Think\Upload($config);
    $info = $uploadModel->upload();
    if(!$info){
        throw new \Exception($uploadModel->getError());
    }else{
        foreach($info as $index => $file){
            $path =  $uploadModel->rootPath.$file['savepath'].$file['savename'];
            $info[$index]['path'] = $path;

            $info[$index]['src'] = C("HOST").$info[$index]['path'];
        }
    }
    return $info;
}
