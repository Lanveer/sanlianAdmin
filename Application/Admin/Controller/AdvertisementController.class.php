<?php
namespace Admin\Controller;
use Think\Controller;
header('content-type:text/html;charset=utf-8');
class AdvertisementController extends CommonController{
    /**
     * 幻灯片列表
     */
    public function advertisement(){
        $adverMsg=M('adver')->select();
        foreach($adverMsg as $key=>$val){
            $val['create_time']=date('Y-m-d H:i:s' ,$val['create_time']);
            $adverMsg[$key]=$val;
        }
        $this->assign('countAdver',count($adverMsg));
        $this->assign('adverMsg',$adverMsg);
        $this->display();
    }
    
    /**
     * 编辑图片信息
     */
    public function adveredite(){
        $id=I('post.id');
        $content=I('post.content');
        $location=I('post.location');
        $type=I('post.type');
        $url=I('post.url');
        $show_type = $_REQUEST['show_type'];
        $html = $_REQUEST['html'];
        if($_FILES){
            $config = array(
                'maxSize' => 3145728,
                'rootPath' => './Public/',
                'savePath' => '/Uploads/adver/',
                'saveName' => array('uniqid',''),
                'exts' => array('jpg', 'gif', 'png', 'jpeg'),
                'autoSub' => true,
                'subName' => array('date','Y-m-d'),
            );
            $upload = new \Think\Upload($config);// 实例化上传类
            $info = $upload->upload();
            if(!$info) {
                // 上传错误提示错误信息
                $this->error($upload->getError());
            }else{
                // 上传成功 获取上传文件信息            
                foreach($info as $file){
                    $data['imagepath']=C('__UPLOADS_PATH__').$file['savepath'].$file['savename'];
                    $data['content']=$content;
                    $data['type']=$type;
                    $data['create_time']=time();
                    $data['location']=$location;
                    $data['url']=$url;
                    $data['show_type']=$show_type;
                    $data['html']=$html;
                    $result=M('adver')->where('id='.$id)->save($data);
                } 
               
            }
            if($result){
                echo "<div class='btn btn-success center'>修改成功！</div>";exit;
            }else{
                $this->error('修改失败');
            }
            
        }
        
    }
    /**
     * 编辑图片页面
     */
    public function product_edit(){
        $id=I('get.id');
        $updateMsg=M('adver')->where('id='.$id)->find();
        
        $this->assign('updateMsg',$updateMsg);
        $this->display();
    }
    /**
     * 删除图片
     */
    public function delAdver(){
        $id=I('post.id');
        $picMsg=M('adver')->where('id='.$id)->find();        
        $delUrl=$_SERVER['DOCUMENT_ROOT'].'/T_ball/Public'.$picMsg['imagepath'];
        $delMsg=M('adver')->where('id='.$id)->delete();
        $result=@unlink($delUrl);
        if($delMsg){
            $this->ajaxReturn(true);
        }
        
    }
    
    /**
     * 图片下架
     */
    public function adver_stop(){
        $id=I('post.id');
        $result=M('adver')->where('id='.$id)->save(array('status'=>0));
        $this->ajaxReturn($result);
    }
    /**
     * 图片上架
     */
    public function adver_start(){
        $id=I('post.id');
        $result=M('adver')->where('id='.$id)->save(array('status'=>1));
        $this->ajaxReturn($result);        
    }
    /**
     * banner图列表
     */
    public function bannerlist(){
        
        
        $this->display();
    }
    /**
     * 添加图片
     */
    public function addpic(){
        $this->display();
    }
    /**
     * 发布图片
     */
    public function adveradd(){
        $type=I('post.type');
        $content=I('post.content');
        $location=I('post.location');
        $config = array(
            'maxSize' => 3145728,
            'rootPath' => './Public/',
            'savePath' => '/Uploads/adver/',
            'saveName' => array('uniqid',''),
            'exts' => array('jpg', 'gif', 'png', 'jpeg'),
            'autoSub' => true, 
            'subName' => array('date','Y-m-d'),            
         );
        $upload = new \Think\Upload($config);// 实例化上传类 
        $info = $upload->upload();
        $this->ajaxReturn($info,"json");
         if(!$info) {
             // 上传错误提示错误信息
              $this->error($upload->getError());
          }else{
              // 上传成功 获取上传文件信息
              $this->ajaxReturn($info);
              /*  foreach($info as $file){ 
                   $data['imagepath']=$file['savepath'].$file['savename'];
                   $data['content']=$content;
                   $data['type']=$type;
                   $data['creat_time']=time();
                   $data['location']=$type;
                   $data['status']=1;

                   $result=M('adver')->add($data);
                   if($result){
                       header('location:'.U('Index/index'));
                   }else{
                       $this->error('上传失败');
                   }
                } */
          }
         $this->ajaxReturn('不存在');
    }
    public function adveraddMsg(){        
        $type=I('post.type');
        $content=I('post.content');
        $location=I('post.location');
        $show_type = $_REQUEST['show_type'];
        $html = $_REQUEST['html'];
        $url=I('post.url');
        $config = array( 
            'maxSize' => 3145728, 
            'rootPath' => './Public/', 
            'savePath' => '/Uploads/adver/',
            'saveName' => array('uniqid',''), 
            'exts' => array('jpg', 'gif', 'png', 'jpeg'), 
            'autoSub' => true, 
            'subName' => array('date','Y-m-d'),            
         );
        $upload = new \Think\Upload($config);// 实例化上传类 
        $info = $upload->upload();
         if(!$info) {
             // 上传错误提示错误信息
              $this->error($upload->getError());
          }else{
              // 上传成功 获取上传文件信息
              foreach($info as $file){ 
                   $data['imagepath']=C('__UPLOADS_PATH__').$file['savepath'].$file['savename'];
                   $data['content']=$content;
                   $data['type']=$type;
                   $data['create_time']=time();
                   $data['location']=$location;
                   $data['status']=1;
                   $data['url']=$url;
                  $data['show_type'] = $show_type;
                  $data['html'] = $html;
                   $result=M('adver')->add($data);                   
               }
          }
          if($result){
              echo "<div class='btn btn-success center'>上传成功！</div>";exit;
          }else{
              $this->error('上传失败');
          }
          
     }
    
}