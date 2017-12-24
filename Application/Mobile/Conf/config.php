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

    /* 数据库设置 */
//    'DB_TYPE'               =>  'mysql',     // 数据库类型
//    'DB_HOST'               =>  '127.0.0.1', // 服务器地址
//    'DB_NAME'               =>  'qiuchang3',          // 数据库名
//    'DB_USER'               =>  'root',      // 用户名
//    'DB_PWD'                =>  '123456',          // 密码
//    'DB_PORT'               =>  '3306',        // 端口
//    'DB_PREFIX'             =>  't_',    // 数据库表前缀
//    'DB_PARAMS'          	=>  array(), // 数据库连接参数
//    'DB_DEBUG'  			=>  TRUE, // 数据库调试模式 开启后可以记录SQL日志S


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
   // '__UPLOADS_PATH__'      =>  'http://127.0.0.1/T_ball/Public' //图片上传绝对路径
    '__UPLOADS_PATH__'      =>  'http://'.(isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']).__ROOT__."/".'Public', //图片上传绝对路径

);