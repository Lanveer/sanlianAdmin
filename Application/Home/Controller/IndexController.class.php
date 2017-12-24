<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
    
    
    	$this->display();
    }
    //登陆
    public function login(){
    
    	
    	
    	header("location:".__APP__."/Admin/Index/index");
    	$this->display();
    }
}