<?php
namespace Admin\Controller;
use Think\Controller;

class AdminController extends CommonController{
	//管理员列表
	public function adminlist(){
		$adminUserMsg=M('admin_user')->where('is_delete=0')->select();
		foreach($adminUserMsg as $key=>$val){
		    $val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
		    $val['p_id']=$val['p_id']?$val['p_id']:'';
		    if($val['p_id']!=''){
		        $val['leadername']=M('admin_user')->where('p_id='.$val['p_id'])->getField('name');
		    }else{
		        $val['leadername']='无';
		    }
		    
		    $adminUserMsg[$key]=$val;
		}
	    
		$this->assign('adminUserMsg',$adminUserMsg);
		$this->assign('countAdminUser',count($adminUserMsg));
		$this->display();
	}

	public function admin_stop(){
	    $id=I('post.id');
	    $result=M('admin_user')->where('id='.$id)->save(array('is_freeze'=>1));
	    $this->ajaxReturn($result);
	}
	public function admin_start(){
	    $id=I('post.id');
	    $result=M('admin_user')->where('id='.$id)->save(array('is_freeze'=>0));
	    $this->ajaxReturn($result);
	}
	public function admin_del(){
	    $id=I('post.id');
	    $result=M('admin_user')->where('id='.$id)->delete();
	    $this->ajaxReturn($result);
	}
	
	//管理员添加
	public function adminadd(){
	    $leavalAdmin=M('admin_user')->where('auth < 3')->select();
	    $venueMsg=M('venue')->select();
	    $adminuserMsg=M('admin_user')->field('id,venue_id')->select();
	    
//	    foreach($venueMsg as $key=>$val){
//	        foreach($adminuserMsg as $k=>$v){
//	            if($val['venue_id']==$v['venue_id']){
//	                array_splice($venueMsg,$key,1);
//	            }
//	        }
//	    }
	    
	    
	    if(!empty($_POST)){
	        $adminArr['loginname']=I('post.loginname');
	        $adminArr['password'] =md5(md5(I('post.password')).C('__ENCRYPT__'));
	        $adminArr['name']     =I('post.name');
	        $adminArr['auth']     =I('post.auth');
	        $adminArr['phone']    =I('post.phone');
	        $adminArr['p_id']     =I('post.p_id')?I('post.p_id'):0;
	        $adminArr['create_time']=time();
	        $adminArr['remark']   =I('post.remark');
	        $adminArr['venue_id']   =I('post.venue_id');
	        if(!empty($_FILES)){
	            $config = array(
	                'maxSize' => 2097152,
	                'rootPath' => './Public/',
	                'savePath' => '/Uploads/admin/',
	                'saveName' => array('uniqid',''),
	                'exts' => array('jpg', 'gif', 'png', 'jpeg'),
	                'autoSub' => true,
	                'subName' => array('date','Y-m-d'),
	            );
	            $upload = new \Think\Upload($config);// 实例化上传类
	            $info = $upload->upload();
	            if($info){
	                $adminArr['pic']=C('__UPLOADS_PATH__').$info['file-2']['savepath'].$info['file-2']['savename'];
	            }else{
                    $this->error($upload->getError());
                }
	        }
	        $result=M('admin_user')->add($adminArr);
	        if($result){
	            echo "<div id='close' style='display:none'>1</div>";
	        }
	    }
	    $this->assign('venueMsg',$venueMsg);
		$this->assign('leavalAdmin',$leavalAdmin);
		$this->display();
	}
	//管理员编辑
	public function updateadmin(){
	    $id=I('get.id');
	    $leavalAdmin=M('admin_user')->where('auth < 3')->select();
	    $userMsg=M('admin_user')->where('id='.$id)->find();
	    
	    $venueMsg=M('venue')->select();
	    $adminuserMsg=M('admin_user')->field('id,venue_id')->select();
	     
//	    foreach($venueMsg as $key=>$val){
//	        foreach($adminuserMsg as $k=>$v){
//	            if($val['venue_id']==$v['venue_id']){
//	                array_splice($venueMsg,$key,1);
//	            }
//	        }
//	    }
	    
	    $this->assign('venueMsg1',$venueMsg);
	    $this->assign('userMsg',$userMsg);
	    $this->assign('leavalAdmin',$leavalAdmin);
		$this->display();
	}
	public function admin_edit(){
	    if(!empty($_POST)){
	        $id=I('post.id');
	        $adminArr['loginname']=I('post.loginname');
	        $adminArr['name']     =I('post.name');
	        $adminArr['auth']     =I('post.auth');
	        $adminArr['phone']    =I('post.phone');
	        $adminArr['p_id']     =I('post.p_id')?I('post.p_id'):0;
	        $adminArr['create_time']=time();
	        $adminArr['remark']   =I('post.remark');
	        $adminArr['venue_id']   =I('post.venue_id');
			$password = $_REQUEST['password'];
			$repassword = $_REQUEST['repassword'];
	        if(!empty($_FILES['file-2']['size'])){
	            $config = array(
	                'maxSize' => 2097152,
	                'rootPath' => './Public/',
	                'savePath' => '/Uploads/admin/',
	                'saveName' => array('uniqid',''),
	                'exts' => array('jpg', 'gif', 'png', 'jpeg'),
	                'autoSub' => true,
	                'subName' => array('date','Y-m-d'),
	            );
	            $upload = new \Think\Upload($config);// 实例化上传类
	            $info = $upload->upload();
	            if($info){
	                $adminArr['pic']=C('__UPLOADS_PATH__').$info['file-2']['savepath'].$info['file-2']['savename'];
	            }else{
	                $this->error($upload->getError());
	            }
	        }
			if(!empty($password)){
				if($password!=$repassword){
					$this->error("两次输入的密码不一致");
				}else{
					$adminArr['password'] = md5(md5($password).C("__ENCRYPT__"));
				}
			}
	        $result=M('admin_user')->where('id='.$id)->save($adminArr);
	        if($result){
	            echo "<div id='close' style='display:none'>1</div>";
	            $this->display('Admin/updateadmin');
	        }
	    }
	}
	public function teamlist(){
	    $id=I('get.id');
	    $venue_id=M('admin_user')->where('id='.$id)->getField('venue_id');
	    if(!empty($venue_id)){
	        $courtMsg=M('court')->where('venue_id='.$venue_id)->select();
	        foreach($courtMsg as $key=>$val){
	            $val['ball_park_name']=M('venue')->where('venue_id='.$venue_id)->getField('name');
	            $courtMsg[$key]=$val;
	        }
	        
	        $this->assign('countCourt',count($courtMsg));
	        $this->assign('courtMsg',$courtMsg);
	    }else{
	        echo "<div id='close' style='display:none'>1</div>";
	    }
	    $this->assign('id',$id);
	    $this->display();
	}
	public function ball_park_stop(){
	    $id=I('post.id');
	    $result=M('court')->where('court_id='.$id)->save(array('is_freeze'=>1));
	    $this->ajaxReturn($result);
	}
	public function ball_park_start(){
	    $id=I('post.id');
	    $result=M('court')->where('court_id='.$id)->save(array('is_freeze'=>0));
	    $this->ajaxReturn($result);
	}
	public function addteam(){
	    $id=I('get.id');
	    $vuenueMsg=M('venue')->where('is_freeze=0')->select();
	    
	    $this->assign('id',$id);
	    $this->assign('vuenueMsg',$vuenueMsg);
	    $this->display();
	}
	public function getCourtMsg(){
	    $venue_id=I('post.venue_id');
	    $result=M('court')->where('venue_id='.$venue_id)->select();
	    
	    $this->ajaxReturn($result);
	}
	public function venueadd(){
	    $userid=I('post.id');
	    $venue_id=I('post.venue_id');
	    
	    $res=M('admin_user')->where('id='.$userid)->save(array('venue_id'=>$venue_id));
	    if($res){
	        echo "<div id='close' style='display:none'>1</div>";
	        $this->display('Admin/addteam');
	    }
	}
}