<?php
/**
 * Created by PhpStorm.
 * User: walter
 * Date: 2016/9/19
 * Time: 15:56
 */
namespace Admin\Controller;

use Think\Controller;

class FixController extends Controller{

    public function addMember(){
        $ballTeam = M("BallTeam")->select();
        foreach($ballTeam as $value){
            $num = M("BallTeamMember")->where("ball_team_id=%d",$value['ball_team_id'])->count();
            $data = [
                "ball_team_id" => $value['ball_team_id'],
                "member_num" => $num
            ];
            $result = M("BallTeam")->save($data);
        }
    }

    public function ballCaptor(){
        $ballTeam = M("BallTeam")->select();
        foreach($ballTeam as $value){
            $num = M("BallTeamMember")->where("ball_team_id=%d AND uid=%d",$value['ball_team_id'],$value['uid'])->count();
            $data = [
                "ball_team_id" => $value['ball_team_id'],
                "uid" => $value['uid'],
                "type" => "队长"
            ];
            if(empty($num)){
                $result = M("BallTeamMember")->add($data);
//                var_dump($value);
            }
        }
    }

    public function ballteamTime(){
        $ballTeam = M("BallTeam")->select();
        foreach($ballTeam as $value){
            $data = [
              "create_time" => $value['create_time']
            ];
            $num = M("BallTeamMember")->where("ball_team_id=%d AND uid=%d",$value['ball_team_id'],$value['uid'])->save($data);

        }
    }
}