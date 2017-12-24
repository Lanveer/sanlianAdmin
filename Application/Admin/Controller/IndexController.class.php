<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends CommonController {
	
    public function index(){
    	$this->display();
    }
    
    public function welcome(){
    	$time=date("Y-m-d H:i:s",time());
        
    	$this->assign("time",$time);
    	$this->display();
    }
}