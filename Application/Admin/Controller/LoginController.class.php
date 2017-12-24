<?php
namespace Admin\Controller;
use Think\Controller;
class LoginController extends Controller {
	 //登陆
    public function index(){

    	$this->display();
    }

    //登陆验证
    public function check(){
		$account=$_POST["account"];
// var_dump(C('__ENCRYPT__'));
// exit;
		$password=md5(md5(I('post.password')).C('__ENCRYPT__'));
		$code=$_POST["code"];
		if(!$this->verifyCheck($code, $id = '')){
               $this->error("亲，验证码输错了哦！");
			}
		$Modle=M("admin_user");
		$where1="loginname='".$account."' and is_delete=0";
		$ischeckaccount=$Modle->where($where1)->find();
		if(!$ischeckaccount){
			 $this->error("账号不存在！");
			}

		$where="loginname='".$account."' and password='".$password."' and is_delete=0";
		$ischeck=$Modle->where($where)->find();
		if(!$ischeck){
			 $this->error("密码输入错误！");
			}

		if($ischeck["is_freeze"]==1){
			 $this->error("账号被冻结，请联系管理员！");
			}

		session('admin_key_id',$ischeck["id"]);  //设置session
		session('admin_key_loginname',$ischeck["loginname"]);  //设置session
		session('admin_key_name',$ischeck["name"]);  //设置session
		session('admin_key_auth',$ischeck["auth"]);  //设置session
		session('admin_key_venue_id',$ischeck["venue_id"]);  //设置session
		session('admin_key_phone',$ischeck["phone"]);  //设置session

		header('location:'.U('Index/index'));
		//$this->redirect('Index/index',array(), 3, '登陆成功，页面跳转中...');
    }


	//退出登录
	 public function loginout(){
	    session('[destroy]'); // 销毁session
	    header('location:'.U('Login/index'));
		//$this->redirect('Login/index',array(), 5, '登出成功，页面跳转中...');
	 }

	// 生成验证码
 	public function verifycode()//这是一个固定的格式
 	{
  		$Verify = new \Think\Verify();
		$Verify->fontSize = 30;
		$Verify->length   = 4;
		$Verify->useNoise = false;
        $Verify->entry();
 	}

 	//检验验证码是否正确
 	public function verifyCheck($code, $id = ''){
  		 $verify = new \Think\Verify();
          return $verify->check($code, $id);
 	}
}