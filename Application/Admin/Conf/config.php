<?php
return array(
	//'配置项'=>'配置值'
	'SHOW_PAGE_TRACE'     =>false,
     
    /* 默认设定 */
    'DEFAULT_MODULE'        =>  'Admin',  // 默认模块
    'DEFAULT_CONTROLLER'    =>  'Index', // 默认控制器名称
    'DEFAULT_ACTION'        =>  'index', // 默认操作名称
    //'__DOCUMENT_UPLOADS_ZIP__'=>'D:/xampp/www/T_ball/Public',
    '__DOCUMENT_UPLOADS_ZIP__'=>'/var/www/admin/Public',
    //'__DOCUMENT_UPLOADS_ZIP__'=>'http://139.196.230.218/admin/Public',

    'SESSION_AUTO_START' => true, //是否开启session

    'SESSION_OPTIONS'         =>  array(
        'name'                =>  'session_id',                    //设置session名
        'expire'              =>  24*3600*15,                      //SESSION保存15天
        'use_trans_sid'       =>  1,                               //跨页传递
        'use_only_cookies'    =>  0,                               //是否只开启基于cookies的session的会话方式
    ),

    
    
    //mongoDB数据库配置
    'MONGODB_CONNECTION' => [
                'DB_TYPE'   =>  'mongo',
                'DB_HOST'   =>  '139.196.230.218',
                'DB_NAME'   =>  'qiuchang3',
                'DB_USER'   =>  'sanlian',
                'DB_PWD'    =>  'sl123456',
                'DB_PORT'   =>  '27017',
                'DB_PREFIX' =>  '',
                'DB_CHARSET'=>  'utf8',
    ],

    'DB_FIELDS_CACHE'       =>  true,        // 启用字段缓存
    'DB_CHARSET'            =>  'utf8',      // 数据库编码默认采用utf8
    'DB_DEPLOY_TYPE'        =>  0, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'DB_RW_SEPARATE'        =>  false,       // 数据库读写是否分离 主从式有效
    'DB_MASTER_NUM'         =>  1, // 读写分离后 主服务器数量
    'DB_SLAVE_NO'           =>  '', // 指定从服务器序号
    //'APP_KEY'               =>  '3308023a020ec6d8214b9f5f', //jpush appkey
    //'MASTERSECRET'          =>  '62ebf9f2363be49f758b67f6',  //jpush 密码
    //'__JPUSH_PATH__'        =>  '/JPush',
    
    /* 模板引擎设置 */
    'TMPL_ACTION_ERROR'     =>  THINK_PATH.'Tpl/dispatch_jump.tpl', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   =>  THINK_PATH.'Tpl/dispatch_jump.tpl', // 默认成功跳转对应的模板文件
//    '__UPLOADS_PATH__'      =>  'http://inner.cdnhxx.com/admin/Public', //图片上传绝对路径
    '__UPLOADS_PATH__'      =>  'http://'.(isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']).__ROOT__."/".'Public', //图片上传绝对路径
    'API_HOST' => 'http://139.196.230.218',
);