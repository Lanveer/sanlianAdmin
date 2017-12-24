<?php
namespace Admin\Controller;
use Think\Controller;
class QualifyingController extends CommonController{
    /**
     * 排位赛列表
     */
    public function qualifyinglist(){
        $query = [];
        if(session("admin_key_auth")==3){
            $venue_id = session('admin_key_venue_id');
            $courtIds = M("court")->where("venue_id=%d",$venue_id)->getField("court_id",true);
            if(empty($courtIds)){
                $courtIds = [0];
            }
            $query['court_id'] = array("in",$courtIds);
        }
        $qualifyingMsg=M('qualifying')->where($query)->select();
        foreach($qualifyingMsg as $key=>$val){
            $val['start_time']=date('Y-m-d H:i:s',$val['start_time']);
            $val['order_no']=M('order')->where('order_id='.$val['home_order_id'])->getField('order_no');
            $val['homeTeamMsg']=M('ball_team')->where('ball_team_id='.$val['home_team_id'])->find();
            if($val['guest_team_id']){
                $val['guestTeam'] = M('ball_team')->where('ball_team_id='.$val['guest_team_id'])->find();
            }else{
                $val['guestTeam']['name'] = '-';
            }
            if($val['homeTeamMsg'] != ''){
                $val['homeTeamMsg']['nickname']=$val['homeTeamMsg']['lead_name']?$val['homeTeamMsg']['lead_name']:M('user')->where('user_id='.$val['homeTeamMsg']['uid'])->getField('nickname');
                $val['homeTeamMsg']['phone']=M('user')->where('user_id='.$val['homeTeamMsg']['uid'])->getField('phone');
            }

            $val['courtMsg']=M('court')->where('court_id='.$val['court_id'])->find();
            $qualifyingMsg[$key]=$val;
        }
                     
        $this->assign('countQualifying',count($qualifyingMsg));
        $this->assign('qualifyingMsg',$qualifyingMsg);
        $this->display();
    }
    public function qualify_del(){
        $qualifying_id=I('post.id');
        $result=M('qualifying')->where('qualifying_id='.$qualifying_id)->delete();
        $this->ajaxReturn($result);
    }
   public function qualify_edit(){
       $qualifying_id=I('get.qualifying_id');
       $qualifyingMsg=M('qualifying')->where('qualifying_id='.$qualifying_id)->find();

       $homeTeamMsg=M('ball_team')->where('ball_team_id='.$qualifyingMsg['home_team_id'])->find();
       $homeTeamMsg['build_time']=date('Y-m-d H:i:s',$homeTeamMsg['build_time']);
       //$homeTeamMsg['lead_name']=$homeTeamMsg['lead_name']?$homeTeamMsg['lead_name']:M('user')->where('user_id='.$homeTeamMsg['uid'])->getField('nickname');
       $homeTeamMsg['phone']=M('user')->where('user_id='.$homeTeamMsg['uid'])->getField('phone');
       $homeTeamUser=M('ball_team_member')->where('ball_team_id='.$qualifyingMsg['home_team_id'])->select();
       
       foreach ($homeTeamUser as $key=>$val){
           //if($val['uid'] != ''){
               $val['nickname']=M('user')->where('user_id='.$val['uid'])->getField('nickname');
               $val['phone']=M('user')->where('user_id='.$val['uid'])->getField('phone');
               $val['avatar']=M('user')->where('user_id='.$val['uid'])->getField('avatar');
               $homeTeamUser[$key]=$val;
          // }
           
       }

       if($qualifyingMsg['guest_team_id'] != ''){
           $guestTeamMsg=M('ball_team')->where('ball_team_id='.$qualifyingMsg['guest_team_id'])->find();
           $guestTeamMsg['build_time']=date('Y-m-d H:i:s',$guestTeamMsg['build_time']);
           $guestTeamMsg['lead_name']=$guestTeamMsg['lead_name']?$guestTeamMsg['lead_name']:M('user')->where('user_id='.$guestTeamMsg['uid'])->getField('nickname');
           $guestTeamMsg['phone']=M('user')->where('user_id='.$guestTeamMsg['uid'])->getField('phone');
           $guestTeamUser=M('ball_team_member')->where('ball_team_id='.$qualifyingMsg['guest_team_id'])->select();
           foreach ($guestTeamUser as $key=>$val){
               $val['nickname']=M('user')->where('user_id='.$val['uid'])->getField('nickname');
               $val['phone']=M('user')->where('user_id='.$val['uid'])->getField('phone');
               $val['avatar']=M('user')->where('user_id='.$val['uid'])->getField('avatar');
               $guestTeamUser[$key]=$val;
           }
       
           $this->assign('guestTeamUser',$guestTeamUser);
           $this->assign('guestTeamMsg',$guestTeamMsg);
       }
       
       $this->assign('qualifyingMsg',$qualifyingMsg);
       $this->assign('qualifying_id',$qualifying_id);
       $this->assign('homeTeamMsg',$homeTeamMsg);
       $this->assign('homeTeamUser',$homeTeamUser);
       $this->display();
   }
   
   public function qualifyMsg_edit(){
       $ballTeamModel = M("BallTeam");
       $qualifyingModel = M("Qualifying");
       $qualifying_id=I('post.qualifying_id');//排位赛订单id
       $home_team_id=I('post.home_team_id');//主队球队id
       $guest_team_id=I('post.guest_team_id');//应战队球队id
       $home_hit=I('post.home_hit');//主队是否殴打裁判
       $guest_hit=I('post.guest_hit');//应战队是否殴打裁判
       
       $dataArr['home_score']       =I('post.home_score');
       $dataArr['guest_score']      =I('post.guest_score');
       $dataArr['home_goal']        =I('post.home_goal');
       $dataArr['guest_goal']       =I('post.guest_goal');
       $dataArr['home_yellow_card'] =I('post.home_yellow_card');
       $dataArr['guest_yellow_card']=I('post.guest_yellow_card');
       $dataArr['home_red_card']    =I('post.home_red_card');
       $dataArr['guest_red_card']   =I('post.guest_red_card');
       $dataArr['is_record'] = 1;

       $home_score = 0;
       $guest_score = 0;
       $lastData = M('qualifying')->where('qualifying_id=%d',$qualifying_id)->find();

       $homeTeam=  [];
       $guestTeam = [];

       if(!$lastData['is_record']){
           $homeTeam['game_times'] = array('exp','game_times+1');
           $guestTeam['game_times'] = array('exp','game_times+1');

       }

       if($lastData['home_goal']==$lastData['guest_goal']){
           if($dataArr['home_goal']>$dataArr['guest_goal']){
               $homeTeam['game_win'] = array('exp','game_win+1');
               $guestTeam['game_fail'] = array('exp','game_fail+1');
               if($lastData['is_record']){
                   $homeTeam['game_draw'] = array('exp','game_draw-1');
                   $guestTeam['game_draw'] = array('exp','game_draw-1');
               }

           }elseif($dataArr['home_goal']<$dataArr['guest_goal']){
               $homeTeam['game_fail'] = array('exp','game_fail+1');
               $guestTeam['game_win'] = array('exp','game_win+1');
               if($lastData['is_record']){
                   $homeTeam['game_draw'] = array('exp','game_draw-1');
                   $guestTeam['game_draw'] = array('exp','game_draw-1');
               }
           }else{
               if(!$lastData['is_record']){
                   $homeTeam['game_draw'] = array('exp','game_draw+1');
                   $guestTeam['game_draw'] = array('exp','game_draw+1');
               }
           }
       }

       if($lastData['home_goal']>$lastData['guest_goal']){
           if($dataArr['home_goal']==$dataArr['guest_goal']){
               $homeTeam['game_win'] = array('exp','game_win-1');
               $homeTeam['game_draw'] = array('exp','game_draw+1');
               $guestTeam['game_fail'] = array('exp','game_fail-1');
               $guestTeam['game_draw'] = array('exp','game_draw+1');
           }elseif($dataArr['home_goal']<$dataArr['guest_goal']){
               $homeTeam['game_fail'] = array('exp','game_fail+1');
               $homeTeam['game_win'] = array('exp','game_win-1');
               $guestTeam['game_win'] = array('exp','game_win+1');
               $guestTeam['game_fail'] = array('exp','game_fail-1');
           }else{

           }
       }

       if($lastData['home_goal']<$lastData['guest_goal']){
           if($dataArr['home_goal']==$dataArr['guest_goal']){
               $homeTeam['game_fail'] = array('exp','game_fail-1');
               $homeTeam['game_draw'] = array('exp','game_draw+1');
               $guestTeam['game_win'] = array('exp','game_win-1');
               $guestTeam['game_draw'] = array('exp','game_draw+1');
           }elseif($dataArr['home_goal']>$dataArr['guest_goal']){
               $homeTeam['game_fail'] = array('exp','game_fail-1');
               $homeTeam['game_win'] = array('exp','game_win+1');
               $guestTeam['game_win'] = array('exp','game_win-1');
               $guestTeam['game_fail'] = array('exp','game_fail+1');
           }else{

           }
       }

       if($dataArr['home_goal']==$dataArr['guest_goal']){
           $home_score += 3;
           $guest_score += 3;
       }elseif($dataArr['home_goal']>$dataArr['guest_goal']){
           $home_score += 10;
           $guest_score += 0;
       }else{
           $home_score += 0;
           $guest_score += 10;
       }

       $home_score -= $dataArr['home_red_card']*3;
       $home_score -= $dataArr['home_yellow_card'];

       $guest_score -= $dataArr['guest_red_card']*3;
       $guest_score -= $dataArr['guest_yellow_card'];

       if($home_hit){
           $home_score = 0;
       }
       if($guest_hit){
           $guest_score = 0;
       }


       $dataArr['home_score'] = $home_score;
       $dataArr['guest_score'] = $guest_score;


       try {
           $qualifyingModel->startTrans();
           $qualifyingModel->where('qualifying_id=%d',$qualifying_id)->save($dataArr);
           $ballTeamModel->where('ball_team_id=%d',$home_team_id)->setInc('san_score',$home_score-$lastData['home_score']);
           $ballTeamModel->where('ball_team_id=%d',$guest_team_id)->setInc('san_score',$guest_score-$lastData['guest_score']);
           if(!empty($homeTeam)){
               $ballTeamModel->where('ball_team_id=%d',$home_team_id)->save($homeTeam);
           }
           if(!empty($guestTeam)){
               $ballTeamModel->where('ball_team_id=%d',$guest_team_id)->save($guestTeam);
           }
           if($home_hit){
               $ballTeamModel->where('ball_team_id=%d',$home_team_id)->save(['san_score'=>0]);
           }
           if($guest_hit){
               $ballTeamModel->where('ball_team_id=%d',$guest_team_id)->save(['san_score'=>0]);
           }
           $qualifyingModel->commit();
           $this->ajaxReturn(1);
       } catch (\Exception $e) {
           $qualifyingModel->rollback();
            $this->ajaxReturn(0);
       }

   }

   
}