<?php
namespace Admin\Controller;
use Admin\Model\JpushModel;
use Think\Controller;
use Admin\Model\MessageModel;
use Think\Model;

class SystemController extends CommonController{
    
    public function systembase(){
        
        
        
        $this->display();
    }
    
	//系统设置
	public function sendSystemMsg(){
        $title=I('post.title');
        $sendContent=I('post.content');

		/*************************mongo数据**************************************/
		$sendMsgArr['title']=$title;
		$sendMsgArr['content']=$sendContent;
		$sendMsgArr['type']=4;//0订场 1排位赛 2活动 3球队管理 4系统消息
		$sendMsgArr['create_time']=time();
		$sendMsgArr['ext']=array(
			'msg_type'		=>4,
			'type'			=>0,
			'ball_team_id'	=>'',
			'ball_team_name'=>'',
		);
		$sendMsgArr['target_user']='all';
		$sendMsgArr['__v']=0;
		/*************************jpush数据**************************************/
		$data['sendno']			=time();
		$data['receiver_type']	=4;
		$data['receiver_value']	='';
		$data['msg_type']		=1;

		$content['n_builder_id']=1;
		$content['n_title']		='【三联球战】';
		$content['n_content']	=I('post.title');

		/*************************************************************************/

		$Model=new MessageModel();
		$result=$Model->add($sendMsgArr);

        if($result){
			$id=json_decode(json_encode($result),1)['$id'];
			/**********************Jpush扩展**************************************/
			$content['n_extras']	=array(
				'msg_type'	=>4,
				'ball_team_id'	=>'',
				//'ball_team_name'=>'',
				'_id'			=>$id,
				'content'		=>$sendContent,
			);
			$JpushModel=new JpushModel();
			$res=$JpushModel->push($data,$content);//推送消息

            if($res){
				$re=M('user')->where('user_id <> 0')->setInc('system_point');
				if($re){
					echo "<div id='close' style='display:none'>1</div>";
					$this->display('System/systembase');
				}
            }
        }
		
	}
	/**
	 * 版本说明
	 */
	public function imprint(){
	    
	    $this->display();
	}
	public function imprintlist(){
	    $imprintMsg=M('ver')->select();	    
	    
	    $this->assign('imprintMsg',$imprintMsg);
	    $this->assign('countImprint',count($imprintMsg));
	    $this->display();
	}
	public function imprint_del(){
	    $id=I('post.id');
	    $result=M('ver')->where('ver_id='.$id)->delete();
	    $this->ajaxReturn($result);
	}
	public function addimprint(){
	    if(!empty($_POST)){
			if(empty($_FILES['file'])){
				$this->error('更新文件必须！');
			}else{
				try {
					$file = uploadFile('update');
					$_POST['url'] = $file['file']['src'];
				} catch (\Exception $e) {
					$this->error($e->getMessage());
				}
			}
	        $result=M('ver')->add($_POST);
	        if($result){
	            echo "<div id='close' style='display:none'>1</div>";
	            $this->display('System/imprint');
	        }
	    }
	}
	/**
	 * 晋级设置
	 */
	public function promoted(){
	    
	    $this->display();
	}
	public function addpromoted(){
	    if(!empty($_POST)){
	        $result=M('promoted')->add($_POST);
	        if($result){
	            echo "<div id='close' style='display:none'>1</div>";
	            $this->display('System/promoted');
	        }
	    }
	}
	public function promotedlist(){
	    $promotedMsg=M('promoted')->select();
	    foreach($promotedMsg as $key=>$val){
	        if($val['end'] != 0){
	            $val['shuttle']=$val['start'].'--------'.$val['end'];
	        }else{
	            $val['shuttle']=$val['start'].'--------';
	        }
	        $val['content']=html_entity_decode($val['content']);
	        $promotedMsg[$key]=$val;
	    }
	    
	    $this->assign('countPromoted',count($promotedMsg));
	    $this->assign('promotedMsg',$promotedMsg);
	    $this->display();
	}
	public function promoted_del(){
	    $id=I('post.id');
	    $result=M('promoted')->where('id='.$id)->delete();
	    $this->ajaxReturn($result);
	}
	public function promoted_edit(){
	    $id=I('get.id');
	    $promotMsg=M('promoted')->where('id='.$id)->find();
	    
	    $this->assign('promotMsg',$promotMsg); 
	    $this->display();
	}
	public function promoted_update(){
	    if(!empty($_POST)){
	        $id=I('post.id');;
	        $dataArr['name']=I('post.name');
	        $dataArr['start']=I('post.start');
	        $dataArr['end']=I('post.end');
	        $dataArr['content']=I('post.content');
	        $result=M('promoted')->where('id='.$id)->save($dataArr);
	        if($result){
	            echo "<div id='close' style='display:none'>1</div>";
	            $this->display('System/promoted_edit');
	        }
	    }
	}
	
	/**
	 * 系统协议列表
	 */
	public function agreementlist(){
	    $agreementMsg=M('agreement')->select();
	    foreach ($agreementMsg as $key=>$val){
	        $val['content']=html_entity_decode($val['content']);
	        $val['create_time']=date('Y-m-d H:i:s' , $val['create_time']);
	        $agreementMsg[$key]=$val;
	    }
	    
	    $this->assign('countAgreement',count($agreementMsg));
	    $this->assign('agreementMsg',$agreementMsg);
	    $this->display();
	}
	
	public function agreement(){    
	    if(!empty($_POST)){
	        $data['title']=I('post.title');
	        $data['content']=I('post.content');
	        $data['create_time']=time();
	        $result=M('agreement')->add($data);
	        if($result){
	            echo "<div id='close' style='display:none'>1</div>";
	        }
	    }
	    
	    $this->display();
	}
	public function agreement_del(){
	    $id=I('post.id');
	    $result=M('agreement')->where('id='.$id)->delete();
	    $this->ajaxReturn($result);
	}
	public function agreement_edit(){
	    $id=I('get.id');
	    $agreementMsg=M('agreement')->where('id='.$id)->find();
		$agreementMsg['content']=html_entity_decode($agreementMsg['content']);

	    $this->assign('agreementMsg',$agreementMsg);
	    $this->display();
	}
	public function agreement_update(){	    
	    if(!empty($_POST)){
	        $id=I('post.id');
	        $data['title']=I('post.title');
	        $data['content']=I('post.content');
	        $data['create_time']=time();
	        $result=M('agreement')->where('id='.$id)->save($data);
	        if($result){
	            echo "<div id='close' style='display:none'>1</div>";
	            $this->display('System/agreement_edit');
	        }
	    }
	    
	}
}