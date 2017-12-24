<?php
namespace Admin\Controller;
use Think\Controller;
class RefereeController extends CommonController{
    
    /**
     * 裁判分组
     */
    public function refereegroup(){
        $referMsg=M('referee')->select();
        foreach($referMsg as $key=>$val){
            $val['content']=html_entity_decode($val['content']);
            $referMsg[$key]=$val;
        }

        $this->assign('countreferMsg',count($referMsg));
        $this->assign('referMsg',$referMsg);
        $this->display();
    }
    public function refer_stop(){
        $id=I('post.id');
        $result=M('referee')->where('id='.$id)->save(array('is_show'=>0));
        $this->ajaxReturn($result);
    }
    public function refer_start(){
        $id=I('post.id');
        $result=M('referee')->where('id='.$id)->save(array('is_show'=>1));
        $this->ajaxReturn($result);
    }
    public function refer_del(){
        $id=I('post.id');
        $result=M('referee')->where('id='.$id)->delete();
        $this->ajaxReturn($result);
    }
    public function addreferee(){
        if(!empty($_POST)){
            $referArr['name']=I('post.name');
            $referArr['content']=I('post.content');
            $referArr['price']=I('post.price');
            $referArr['is_show']=1;
            $result=M('referee')->add($referArr);
            if($result){
                echo "<div id='close'>添加成功</div>";
            }
        }
        
        $this->display();
    }

    public function referee_edit(){
        $id=I('get.id');
        $refereeMsg=M('referee')->where('id='.$id)->find();
        $refereeMsg['content']=html_entity_decode($refereeMsg['content']);


        if(!empty($_POST)){
            $id=I('post.id');
            $referArr['name']=I('post.name');
            $referArr['content']=I('post.content');
            $referArr['price']=I('post.price');

            $result=M('referee')->where('id='.$id)->save($referArr);
            if($result){
                echo "<div id='close' style='display: none'>1</div>";
            }else{
                echo "<div id='close' style='display: none'>2</div>";
            }
        }

        $this->assign('refereeMsg',$refereeMsg);
        $this->assign('id',$id);
        $this->display();
    }
}