<?php
namespace Admin\Controller;
use Think\Controller;

class CardController extends CommonController{
	//个人球卡列表
     public  function  person_card(){
         $personList=M('ticket_card')->where('type=0')->select();
         foreach($personList as $key=>$val){
             $val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
             $val['start_time']=date('Y-m-d H:i:s',$val['start_time']);
             $personList[$key]=$val;
         }
         $this->assign('personList',$personList);
         $this->assign('countPersonList',count($personList));
         $this->display();
     }
     /*取消个人卡片*/
     public function person_cancel(){
         $id=I('post.id');
         $result=M('ticket_card')->where('id='.$id)->setField('is_cancel','1');
         $this->ajaxReturn($result);
     }
     //重新打开个人名片*/
      public function person_open(){
          $id=I('post.id');
          $result= M('ticket_card')->where('id='.$id)->setField('is_cancel','0');
          $this->ajaxReturn($result);
      }


//      删除个人卡片
 public function person_del(){
          $id=I('post.id');
          $result=M('ticket_card')->where('id='.$id)->delete();
          $this->ajaxReturn($result);
 }




//      球队求卡列表
  public function team_card(){
          $teamList=M('ticket_card')-> where('type=1')->select();
          foreach ($teamList as $key=>$val){
              $val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
              $val['start_time']=date('Y-m-d H:i:s',$val['start_time']);
              $teamList[$key]=$val;
          }
          $this->assign('teamList',$teamList);
          $this->assign('countTeamList',count($teamList));
          $this->display();
  }

    
}

