<?php
namespace Admin\Controller;
use Think\Controller;
class ActivityController extends CommonController{
    /**
     * 球队活动列表
     */
    public function activitylist(){
        $activiMsg=M('ball_team_activity')->where('status=1')->select();
        foreach($activiMsg as $key=>$val){
            $val['userMsg']=M('user')->where('user_id='.$val['user_id'])->getField('nickname');
            $val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
            $val['activity_time']=date('Y-m-d H:i:s',$val['activity_time']);
            $activiMsg[$key]=$val;
        }
                
        $this->assign('countActivi',count($activiMsg));
        $this->assign('activiMsg',$activiMsg);
        $this->display();
    }
    /**
     * 删除球队活动
     */
    public function delActivi(){
        $id=I('post.id');
        $result=M('ball_team_activity')->where('id='.$id)->delete();
        
        $this->ajaxReturn($result);
    }
    
    /**
     * 赛事活动列表
     * 
     */
    public function activityteamlist(){
        $activityMsg=M('competition_activity')->order('create_time DESC')->select();
        foreach($activityMsg as $key=>$val){
            $val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
            $val['adduser']=M('admin_user')->where('id='.$val['admin_userid'])->getField('loginname');
            $val['url']=$val['url'].'index.html';
            $activityMsg[$key]=$val;
        }        
        
        $this->assign('activityMsg',$activityMsg);
        $this->assign('countActivityMsg',count($activityMsg));
        $this->display();
    }
    /**
     * 发布赛事活动
     */
    public function addcompetion(){
        if(!empty($_POST) && !empty($_FILES)){
            $title=I('post.title');
            $url=I('post.url');
            $config = array(
                'maxSize' => 2097152000,
                'rootPath' => './Public/',
                'savePath' => '/Uploads/activity/',
                'saveName' => array('uniqid',''),
                'exts' => array('jpg', 'gif', 'png', 'jpeg','zip'),
                'autoSub' => true,
                'subName' => array('date','Y-m-d'),
            );
            $upload = new \Think\Upload($config);// 实例化上传类
            $info = $upload->upload();
            if(!$info) {
                // 上传错误提示错误信息
                $this->error($upload->getError());
            }else{
                // 上传成功 获取上传文件信息  57c3f04513893.png
                if($info['file-2']){
                    $data['logo']=C('__UPLOADS_PATH__').$info['file-2']['savepath'].$info['file-2']['savename'];
                    $data['title']=$title;
                    $data['create_time']=time();                    
                    $data['status']=1;
                }
                if($info['file']){
                    $subFileName=explode('.', $info['file']['name']);
                    $expand_name=explode(".",$info['file']['name']);
                    if($expand_name[1] == 'zip' or $expand_name[1] == 'ZIP'){
                        $Zip=new \ZipArchive();
                        $saveUrl=explode('.', $info['file']['savename']);
                        if($Zip->open($info['file']['savename']) == TRUE){
                            chmod("/Public/Uploads/activity/", 0755);
                            $data['admin_userid']=session('admin_key_id');
                            $data['url']=C('__UPLOADS_PATH__').$info['file']['savepath'].$saveUrl[0].$subFileName[0].'/';
                            $result=get_zip_originalsize(c('__DOCUMENT_UPLOADS_ZIP__').$info['file']['savepath'].$info['file']['savename'],c('__DOCUMENT_UPLOADS_ZIP__').$info['file']['savepath'].$saveUrl[0]);
                            //$result=get_zip_originalsize(c('__DOCUMENT_UPLOADS_ZIP__').$info['file']['savepath'].$info['file']['savename'],c('__DOCUMENT_UPLOADS_ZIP__').$info['file']['savepath'].$info['file']['savename']);
                            unlink(c('__DOCUMENT_UPLOADS_ZIP__').$info['file']['savepath'].$info['file']['savename']);
                            if($result){
                                $res=M('competition_activity')->add($data);
                                if($res){
                                    echo "<div id='close' style='display:none'>1</div>";
                                }else{
                                    echo "<div id='close' style='display:none'>2</div>";
                                }
                            }                           
                                                      
                        }
                    }
                }else{
                    $data['url']=I('post.url');
                    $data['admin_userid']=session('admin_key_id');
                    $res=M('competition_activity')->add($data);
                    if($res){
                        echo "<div id='close' style='display:none'>1</div>";
                    }else{
                        echo "<div id='close' style='display:none'>2</div>";
                    }
                }
         } 
      }
        $this->display();
    }
    
    /**
     * 上架赛事活动
     */
    public function activity_start(){
        $id=I('post.id');
        $result=M('competition_activity')->where('id='.$id)->save(array('status'=>1));
        $this->ajaxReturn($result);
    }
    
    /**
     * 下架赛事活动
     */
    public function activity_stop(){
        $id=I('post.id');
        $result=M('competition_activity')->where('id='.$id)->save(array('status'=>0));
        $this->ajaxReturn($result);
    }
    /**
     * 删除赛事活动
     */
    public function activity_del(){
        $id=I('post.id');
        $delActivityMsg=M('competition_activity')->where('id='.$id)->find();
        $delFileUrl=$_SERVER['DOCUMENT_ROOT'].'/T_ball/Public'.$delActivityMsg['url'];
        $delFileUrl=substr($delFileUrl, 0,strlen($delFileUrl)-1);
        $delImgUrl =$_SERVER['DOCUMENT_ROOT'].'/T_ball/Public'.$delActivityMsg['logo'];
        
        $delMsg=M('competition_activity')->where('id='.$id)->delete();
        $delImg=@unlink($delImgUrl);
        $delFile=delDirAndFile($delFileUrl);
        // && $delImg && $delFile
        if($delMsg){
            $this->ajaxReturn(true);
        }
    }
    
    /**
     * 编辑赛事活动
     */
    public function updatecompetion(){
        $id=I('get.id');
        $updateMsg=M('competition_activity')->where('id='.$id)->find();
        $this->assign('updateMsg',$updateMsg);
        $this->display();
    }
    public function addactivity(){
        if(!empty($_POST)){
            $id=I('post.id');
            $admin_userid=$_SESSION['adminUserMsg']['id'] != null?$_SESSION['adminUserMsg']['id']:1;
            $dataArr=array(
                'title'         =>I('post.title'),
                'create_time'   =>time(),
                'status'        =>1,
                'admin_userid'  =>$admin_userid
            );
            if(!empty($_FILES)){
                $config = array(
                    'maxSize' => 2097152,
                    'rootPath' => './Public/',
                    'savePath' => '/Uploads/activity/',
                    'saveName' => array('uniqid',''),
                    'exts' => array('jpg', 'gif', 'png', 'jpeg','zip'),
                    'autoSub' => true,
                    'subName' => array('date','Y-m-d'),
                );
                $upload = new \Think\Upload($config);// 实例化上传类
                $info = $upload->upload();
                if($info){
                    $delImg=M('competition_activity')->where('id='.$id)->find();
                    $delUrl=$_SERVER['DOCUMENT_ROOT'].'/T_ball/Public'.$delImg['logo'];
                    @unlink($delUrl);
                }
                $dataArr['logo']=C('__UPLOADS_PATH__').$info['file-2']['savepath'].$info['file-2']['savename'];
                $result=M('competition_activity')->where('id='.$id)->save($dataArr);
                if($result){
                    echo "<div id='close'>修改成功</div>";
                }
            }
        }
    }
}