<?php
namespace Admin\Controller;
use Common\Model\BallTeamModel;
use Common\Model\CompetitionBallteamModel;
use Common\Model\CompetitionModel;
use Common\Model\CompetitionRaceModel;
use Common\Model\CompetitionRoundModel;
use Think\Controller;

/**
 * Class ArticleController
 * @package Admin\Controller
 * @property CompetitionRoundModel $competitionRoundModel
 * @property CompetitionModel $competitionModel
 * @property CompetitionRaceModel $competitionRaceModel
 * @property BallTeamModel $ballTeamModel
 * @property CompetitionBallteamModel $competitionBallTeamModel
 */
class ArticleController extends CommonController{
	private $competitionRoundModel;
	private $competitionModel;
	private $competitionRaceModel;
	private $ballTeamModel;
	private $competitionBallTeamModel;

	public function _initialize()
	{
		parent::_initialize();
		$this->competitionRoundModel = D("CompetitionRound");
		$this->competitionModel = D("Competition");
		$this->competitionRaceModel = D("CompetitionRace");
		$this->ballTeamModel = D("BallTeam");
		$this->competitionBallTeamModel = D("CompetitionBallteam");
	}

	/**
	 * 赛程列表
	 */
	public function competitionlist(){
	    $competitionMsg=M('competition')->select();
	    foreach($competitionMsg as $key=>$val){
	        $val['start_time']=date('Y-m-d H:i:s',$val['start_time']);
			$val['fee_time']=date('Y-m-d H:i:s',$val['fee_time']);
	        $val['create_time']=$val['create_time']==0?'0':date('Y-m-d H:i:s',$val['create_time']);
	        
	        $competitionMsg[$key]=$val;
	    }
	    
	    $this->assign('countCompetition',count($competitionMsg));
	    $this->assign('competitionMsg',$competitionMsg);
	    $this->display();
	}

	public function competition_add(){
	    if(!empty($_POST)){
	        $dataArr['title']=I('post.title');
	        $dataArr['type']=I('post.type');
	        $dataArr['address']=I('post.address');
	        $dataArr['fee']=I('post.fee');
	        $dataArr['rule']=I('post.rule');
	        $dataArr['start_time']=strtotime(I('post.start_time'));
	        $dataArr['create_time']=time();
			$dataArr['ball_team_num']=I('post.ball_team_num');
			$dataArr['fee_time']=strtotime(I('post.fee_time'));
			$dataArr['award']=I('post.award');
	        $dataArr['is_show']=1;
			$dataArr['explain'] = I('post.explain');
			$dataArr['ticket_card_num'] = I('post.ticket_card_num');
	        if(!empty($_FILES)){
	            $config = array(
	                'maxSize' => 3145728,
	                'rootPath' => './Public/',
	                'savePath' => '/Uploads/competition/',
	                'saveName' => array('uniqid',''),
	                'exts' => array('jpg', 'gif', 'png', 'jpeg'),
	                'autoSub' => true,
	                'subName' => array('date','Y-m-d'),
	            );
	            $upload = new \Think\Upload($config);// 实例化上传类
	            $info = $upload->upload();
	            if($info){
	                $dataArr['img']=C('__UPLOADS_PATH__').$info['file-2']['savepath'].$info['file-2']['savename'];
//	                $dataArr['merchantImg']=C('__UPLOADS_PATH__').$info['file-3']['savepath'].$info['file-3']['savename'];
                    $zan1=C('__UPLOADS_PATH__').$info['zan-1']['savepath'].$info['zan-1']['savename'];
                    $zan2=C('__UPLOADS_PATH__').$info['zan-2']['savepath'].$info['zan-2']['savename'];
                    $zan3=C('__UPLOADS_PATH__').$info['zan-3']['savepath'].$info['zan-3']['savename'];
                    $dataArr['merchantImg']="$zan1,$zan2,$zan3";
	                $result=M('competition')->add($dataArr);
	            }else{
	                $this->error($upload->getError());
	            }
	        }else{
	            $result=M('competition')->add($dataArr);
	        }

	        if($result){
	            echo "<div id='close' style='display:none'>1</div>";
	        }else{
	            echo "<div id='close' style='display:none'>2</div>";
	        }
	    }

	    $this->display();
	}
	public function adver(){
	    $adverMsg=M('competition_banner')->select();
	    foreach($adverMsg as $key=>$val){
	        $val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
	        $adverMsg[$key]=$val;
	    }
	    $this->assign('countAdver',count($adverMsg));
	    $this->assign('adverMsg',$adverMsg);
	    $this->display();
	}
	public function adver_stop(){
	    $id=I('post.id');
	    $result=M('competition_banner')->where('id='.$id)->save(array('is_show'=>0));
	    $this->ajaxReturn($result);
	}
	public function adver_start(){
	    $id=I('post.id');
	    $result=M('competition_banner')->where('id='.$id)->save(array('is_show'=>1));
	    $this->ajaxReturn($result);
	}
	public function adver_del(){
	    $id=I('post.id');
	    $result=M('competition_banner')->where('id='.$id)->delete();
	    $this->ajaxReturn($result);
	}
	public function adver_add(){
	    $competition=M('competition')->select();

	    if(!empty($_POST)){
	        $dataArr['competition_id']=I('post.competition_id');
	        $dataArr['url']=I('post.url');
	        $dataArr['is_show']=I('post.is_show');
	        $dataArr['create_time']=time();

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
	            if(!$info){
	                $this->error($upload->getError());
	            }else{
	                $dataArr['img']=C('__UPLOADS_PATH__').$info['file-2']['savepath'].$info['file-2']['savename'];
	                $result=M('competition_banner')->add($dataArr);
	            }
	        }else{
	            $result=M('competition_banner')->add($dataArr);
	        }

	        if($result){
	            echo "<div id='close' style='display:none'>1</div>";
	        }else{
	            echo "<div id='close' style='display:none'>2</div>";
	        }
	    }

	    $this->assign('competition',$competition);
	    $this->display();
	}

	public function competition_edit(){
	    $competition_id=I('get.competition_id');
	    $competitionMsg=M('competition')->where('competition_id='.$competition_id)->find();
		$competitionMsg['fee_time']=date('Y-m-d H:i:s',$competitionMsg['fee_time']);
		$competitionMsg['start_time']=date('Y-m-d H:i:s',$competitionMsg['start_time']);

	    if(!empty($_POST)){
	        $competition_id=I('post.id');
	        $dataArr['title']=I('post.title');
	        $dataArr['type']=I('post.type');
	        $dataArr['address']=I('post.address');
	        $dataArr['fee']=I('post.fee');
	        $dataArr['rule']=I('post.rule');
			$dataArr['award']=I('post.award');
			$dataArr['ball_team_num']=I('post.ball_team_num');
			$dataArr['fee_time']=strtotime(I('post.fee_time'));
	        $dataArr['start_time']=strtotime(I('post.start_time'));
			$dataArr['explain'] = I('post.explain');
	        if(!empty($_FILES)){
	            $config = array(
	                'maxSize' => 3145728,
	                'rootPath' => './Public/',
	                'savePath' => '/Uploads/competition/',
	                'saveName' => array('uniqid',''),
	                'exts' => array('jpg', 'gif', 'png', 'jpeg'),
	                'autoSub' => true,
	                'subName' => array('date','Y-m-d'),
	            );
	            $upload = new \Think\Upload($config);// 实例化上传类
	            $info = $upload->upload();
	            if($info){
	                $dataArr['img']=C('__UPLOADS_PATH__').$info['file-2']['savepath'].$info['file-2']['savename'];

                    $zan1=C('__UPLOADS_PATH__').$info['zan-1']['savepath'].$info['zan-1']['savename'];
                    $zan2=C('__UPLOADS_PATH__').$info['zan-2']['savepath'].$info['zan-2']['savename'];
                    $zan3=C('__UPLOADS_PATH__').$info['zan-3']['savepath'].$info['zan-3']['savename'];
                    $dataArr['merchantImg']="$zan1,$zan2,$zan3";
//                    var_dump($test);
//                    exit;
                    /*赞助商结束*/
	                $result=M('competition')->where('competition_id='.$competition_id)->save($dataArr);
	            }else{
	                $this->error($upload->getError());
	            }
	        }else{
	            $result=M('competition')->where('competition_id='.$competition_id)->save($dataArr);
	        }

	        if($result){
	            echo "<div id='close' style='display:none'>1</div>";
	        }else{
	            echo "<div id='close' style='display:none'>2</div>";
	        }
	    }

	    $this->assign('competitionMsg',$competitionMsg);
	    $this->display();
	}
	public function saicheng(){
	    $round_id=$_REQUEST['round_id'];
		$competition_id = $_REQUEST['competition_id'];
	    $comRaceMsg=M('competition_race')->order('create_time desc')->where('round_id='.$round_id)->select();
	    $type=M('competition')->where('competition_id='.$competition_id)->getField('type');
	    foreach($comRaceMsg as $key=>$val){
	        $val['start_time'] =date('Y-m-d H:i:s',$val['start_time']);
	        $val['end_time']   =$val['end_time']==0?'0':date('Y-m-d H:i:s' ,$val['end_time']);
	        $val['home_team_name']=M('ball_team')->where('ball_team_id='.$val['home_team_id'])->getField('name');
	        $val['guest_team_name']=M('ball_team')->where('ball_team_id='.$val['guest_team_id'])->getField('name');
	        $val['round']=M('competition_round')->where('round_id='.$val['round_id'])->find();
	        $val['court_address']=M('court')->where('court_id='.$val['court_id'])->getField('address');
	        $comRaceMsg[$key]=$val;
	    }

	    $this->assign('type',$type);
	    $this->assign('competition_id',$competition_id);
		$this->assign('round_id',$round_id);
	    $this->assign('comRaceMsg',$comRaceMsg);
	    $this->assign('countComRa',count($comRaceMsg));
	    $this->display();
	}
	public function schedule_add(){
	    $competition_id=I('competition_id');
		$round_id = I('round_id');
	    $competitionMsg=M('competition')->where('competition_id='.$competition_id)->find();
	    $home_teamMsg=M('competition_ballteam')->where('competition_id='.$competition_id)->select();
	    foreach($home_teamMsg as $key=>$val){
	        $val['home_team_name']=M('ball_team')->where('ball_team_id='.$val['ball_team_id'])->getField('name');
	        $home_teamMsg[$key]=$val;
	    }
	    $courtMsg=M('court')->select();
	    $roundMsg=M('competition_round')->where('round_id='.$round_id)->select();

	    if(!empty($_POST)){
	        $dataArr['competition_id']=I('post.competition_id');
	        $dataArr['home_team_id']=I('post.home_team_id');
	        $dataArr['guest_team_id']=I('post.guest_team_id');
	        $dataArr['court_id']=I('post.court_id');
	        $dataArr['round_id']=I('post.round_id');
	        $dataArr['is_recommend']=I('post.is_recommend');
	        $dataArr['start_time']=strtotime(I('post.start_time'));
	        $dataArr['create_time']=time();
	        if($dataArr['start_time'] == ''){
	            echo "<div id='close' style='display:none'>3</div>";
	        }else{
	            $result=M('competition_race')->add($dataArr);
	            if($result){
	                echo "<div id='close' style='display:none'>1</div>";
	            }else{
	                echo "<div id='close' style='display:none'>2</div>";
	            }
	        }

	    }
	    $this->assign('roundMsg',$roundMsg);
	    $this->assign('courtMsg',$courtMsg);
	    $this->assign('competition_id',$competition_id);
	    $this->assign('home_teamMsg',$home_teamMsg);
	    $this->assign('competitionMsg',$competitionMsg);
	    $this->display();
	}

	public function schedule_edit(){
	    $race_id=I('race_id');
	    $raceMsg=M('competition_race')->where('race_id='.$race_id)->find();
	    $competitionMsg=M('competition')->where('competition_id='.$raceMsg['competition_id'])->find();
	    $home_teamMsg=M('competition_ballteam')->where('competition_id='.$raceMsg['competition_id'])->select();
	    foreach($home_teamMsg as $key=>$val){
	        $val['home_team_name']=M('ball_team')->where('ball_team_id='.$val['ball_team_id'])->getField('name');
	        $home_teamMsg[$key]=$val;
	    }
	    $courtMsg=M('court')->select();
	    $roundMsg=M('competition_round')->where('competition_id='.$raceMsg['competition_id'])->select();

	    if(!empty($_POST)){
	        $race_id=I('post.race_id');
	        $dataArr['competition_id']=I('post.competition_id');
	        $dataArr['home_team_id']=I('post.home_team_id');
	        $dataArr['guest_team_id']=I('post.guest_team_id');
	        $dataArr['court_id']=I('post.court_id');
	        $dataArr['round_id']=I('post.round_id');
	        $dataArr['is_recommend']=I('post.is_recommend');
	        $dataArr['start_time']=strtotime(I('post.start_time'));
	        $dataArr['create_time']=time();
	        if($dataArr['start_time'] == ''){
	            echo "<div id='close' style='display:none'>3</div>";
	        }else{
	            $result=M('competition_race')->where('race_id='.$race_id)->save($dataArr);
	            if($result){
	                echo "<div id='close' style='display:none'>1</div>";
	            }else{
	                echo "<div id='close' style='display:none'>2</div>";
	            }
	        }

	    }
	    $this->assign('race_id',$race_id);
	    $this->assign('courtMsg',$courtMsg);
	    $this->assign('roundMsg',$roundMsg);
	    $this->assign('home_teamMsg',$home_teamMsg);
	    $this->assign('competition_id',$raceMsg['competition_id']);
	    $this->assign('competitionMsg',$competitionMsg);
	    $this->display();
	}

	public function scoreEntry_add(){
	    $race_id=I('get.race_id');
	    $raceMsg=M('competition_race')->where('race_id='.$race_id)->find();
	    /*******************************赛事信息开始**********************************/
	    $competitionMsg=M('competition')->where('competition_id='.$raceMsg['competition_id'])->find();
	    $competitionMsg['roundMsg']=M('competition_round')->where('round_id='.$raceMsg['round_id'])->getField('title');
	    if($competitionMsg['type']==0){
	        $competitionMsg['type']='循环赛';
	    }else if($competitionMsg['type']==1){
	        $competitionMsg['type']='杯赛';
	    }else if($competitionMsg['type']==2){
	        $competitionMsg['type']='淘汰赛';
	    }

	    /*******************************赛事信息结束**********************************/

	    /*******************************双方球队队员信息开始***********************************/
	    $home_team=M('ball_team_member')->where('ball_team_id='.$raceMsg['home_team_id'])->select();
	    $home_teamMsg=M('ball_team')->where('ball_team_id='.$raceMsg['home_team_id'])->find();
	    foreach($home_team as $key=>$val){
	        $val['nickname']=M('user')->where('user_id='.$val['uid'])->getField('nickname');
	        $val['phone']=M('user')->where('user_id='.$val['uid'])->getField('phone');
	        $val['avatar']=M('user')->where('user_id='.$val['uid'])->getField('avatar');
	        $home_team[$key]=$val;
	    }
	    $guest_team=M('ball_team_member')->where('ball_team_id='.$raceMsg['guest_team_id'])->select();
	    $guest_teamMsg=M('ball_team')->where('ball_team_id='.$raceMsg['guest_team_id'])->find();
	    foreach($guest_team as $key=>$val){
	        $val['nickname']=M('user')->where('user_id='.$val['uid'])->getField('nickname');
	        $val['phone']=M('user')->where('user_id='.$val['uid'])->getField('phone');
	        $val['avatar']=M('user')->where('user_id='.$val['uid'])->getField('avatar');
	        $guest_team[$key]=$val;
	    }
	    /*******************************双方球队队员信息结束***********************************/
	    /*******************************赛事战报/集锦开始****************************************/
	    $competition_posts=M('competition_posts')->select();

	    /*******************************赛事战报/集锦结束****************************************/
	    $this->assign('raceMsg',$raceMsg);
	    $this->assign('competition_posts',$competition_posts);
	    $this->assign('home_teamMsg',$home_teamMsg);
	    $this->assign('guest_teamMsg',$guest_teamMsg);
	    $this->assign('competitionMsg',$competitionMsg);
	    $this->assign('guest_team',$guest_team);
	    $this->assign('home_team',$home_team);
	    $this->display();
	}

	public function userScore_add(){
	    //$dataArr['race_id']        =I('post.race_id');
	    $appearances               =I('post.appearances');//是否出场 1:是 0:否
	    $best_num                  =I('post.best_num');//是否最佳1:是 0:否
	    $dataArr['competition_id'] =I('post.competition_id');
	    $dataArr['user_id']        =I('post.uid');
	    $dataArr['ball_team_id']   =I('post.ball_team_id');
	    if($appearances==1){
	        $dataArr['goals_scored']   =I('post.goals_scored');
	        $dataArr['ast']            =I('post.ast');
	        $dataArr['yellow_card']    =I('post.yellow_card');
	        $dataArr['red_card']       =I('post.red_card');
	        $dataArr['penalty_kick']   =I('post.penalty_kick');
	    }else{
	        $dataArr['goals_scored']   =0;
	        $dataArr['ast']            =0;
	        $dataArr['yellow_card']    =0;
	        $dataArr['red_card']       =0;
	        $dataArr['penalty_kick']   =0;
	        $dataArr['best_num']       =0;
	    }


	    $where1="competition_id='".$dataArr['competition_id']."' and ball_team_id='".$dataArr['ball_team_id']."'";
	    $dataArr['competition_ballteam_id']=M('competition_ballteam')->where($where1)->getField('id');

	    $where2="competition_id='".$dataArr['competition_id']."' and ball_team_id='".$dataArr['ball_team_id']."' and user_id='".$dataArr['user_id']."'";
	    $userScoreMsg=M('competition_member')->where($where2)->find();

	    if($userScoreMsg){
	        $saveArr['goals_scored']   =$userScoreMsg['goals_scored']+$dataArr['goals_scored'];
	        $saveArr['ast']            =$userScoreMsg['ast']+$dataArr['ast'];
	        $saveArr['yellow_card']    =$userScoreMsg['yellow_card']+$dataArr['yellow_card'];
	        $saveArr['red_card']       =$userScoreMsg['red_card']+$dataArr['red_card'];
	        $saveArr['penalty_kick']   =$userScoreMsg['penalty_kick']+$dataArr['penalty_kick'];

	        if($best_num==1){
	            $saveArr['best_num']   =$userScoreMsg['best_num']+1;
	        }
	        if($appearances==1){
	            $saveArr['appearances']=$userScoreMsg['appearances']+1;
	        }else{
	            $saveArr['appearances']=$userScoreMsg['appearances'];
	        }
	        $savewhere=array(
	           'competition_id' =>$dataArr['competition_id'],
	           'user_id'        =>$dataArr['user_id'],
	           'ball_team_id'   =>$dataArr['ball_team_id'],
	        );

	        $result=M('competition_member')->where($savewhere)->save($saveArr);
	    }else{
	        if($appearances==1){
	            $dataArr['appearances']=1;
	            if($best_num==1){
	                $dataArr['best_num']=1;
	            }else{
	                $dataArr['best_num']=0;
	            }
	        }else{
	            $dataArr['appearances']=0;
	            $dataArr['best_num']=0;
	        }

	        $dataArr['create_time']    =time();
	        $result=M('competition_member')->add($dataArr);
	    }

	    $this->ajaxReturn($result);
	}

	public function getUserMsg(){
	    $user_id=I('post.user_id');
	    $ball_team_id=I('post.ball_team_id');
	    $data['nickname']=M('user')->where('user_id='.$user_id)->getField('nickname');
	    $data['ball_team_name']=M('ball_team')->where('ball_team_id='.$ball_team_id)->getField('name');

	    $this->ajaxReturn($data);
	}

	/**
	 *	赛程比分录入
     */
	public function score_add(){
	    $race_id            =I('post.race_id');
	    $data['battle_report']      =I('post.battle_report');//战报
		$data['collection']         =I('post.collection');//集锦
		$data['competition_id']     =I('post.competition_id');

		$data['home_score']         =I('post.home_score');
	    $data['home_team_id']       =I('post.home_team_id');
	    $data['home_goal']          =I('post.home_goal');
	    $data['home_yellow_card']   =I('post.home_yellow_card');
	    $data['home_red_card']      =I('post.home_red_card');
	    $data['home_hit']           =I('post.home_hit');		//殴打裁判
	    $data['home_fough']         =I('post.home_fough');		//打架斗殴

	    $data['guest_score']        =I('post.guest_score');
	    $data['guest_team_id']      =I('post.guest_team_id');
	    $data['guest_goal']         =I('post.guest_goal');
	    $data['guest_yellow_card']  =I('post.guest_yellow_card');
	    $data['guest_red_card']     =I('post.guest_red_card');
	    $data['guest_hit']          =I('post.guest_hit');		//殴打裁判
	    $data['guest_fough']        =I('post.guest_fough');	//打架斗殴

		$homeTeamId = intval($data['home_team_id']);				//主队ID
		$guestTeamId = intval($data['guest_team_id']);			//客队ID
		$competitionId = intval($data['competition_id']);			//赛事ID

		//赛程录入
		$race['race_id'] = $race_id;
	    $race['home_goal']   =$data['home_goal'];
		$race['guest_goal']  =$data['guest_goal'];
		$race['home_yellow_card'] = $data['home_yellow_card'];
		$race['guest_yellow_card'] = $data['guest_yellow_card'];
		$race['home_red_card'] = $data['home_red_card'];
		$race['guest_red_card'] = $data['guest_red_card'];
		$race['home_beat_referee'] = intval($data['home_hit']);
		$race['guest_beat_referee'] = intval($data['guest_hit']);
		$race['home_fighting'] = intval($data['home_fough']);
		$race['guest_fighting'] = intval($data['guest_fough']);
		$race['end_time']    =time();
		$race['battle_report']=$data['battle_report']==0?'':C("API_HOST").'/competition/postsInfo?competition_id='.$data['competition_id'].'&posts_id='.$data['battle_report'];
		$race['collection']  =$data['collection']==0?'':C("API_HOST")."/competition/postsInfo?competition_id=".$data['competition_id']."&posts_id=".$data['collection'];

		$oldRace = $this->competitionRaceModel->getById($race_id);
		$homeTeamInfo = $this->ballTeamModel->getById($data['home_team_id']);		//主队详情
		$guestTeamInfo = $this->ballTeamModel->getById($data['guest_team_id']);		//客队详情

		$homeSanscore = 0;			//这次的三联分
		$guestSanscore = 0;

		$oldHomeSanscore = $oldRace['home_score'];
		$oldGuestSanscore = $oldRace['guest_score'];
		$inHomeSanscore = $oldRace['home_san_score'];
		$inGuestSanscore = $oldRace['guest_san_score'];

		if(empty($oldRace['end_time'])){
			$inHomeSanscore = $homeTeamInfo['san_score'];
			$inGuestSanscore = $guestTeamInfo['san_score'];
			$race['home_san_score'] = $inHomeSanscore;
			$race['guest_san_score'] = $inGuestSanscore;
		}


		$hTeam = [];
		$gTeam = [];

		//赛事积分录入
		$homeTeam['goals_scored'] = array('exp','goals_scored+'.($race['home_goal']-$oldRace['home_goal']));
		$homeTeam['goals_against'] = array('exp','goals_against+'.($race['guest_goal']-$oldRace['guest_goal']));
		$homeTeam['yellow_card'] = array('exp','yellow_card+'.($race['home_yellow_card']-$oldRace['home_yellow_card']));
		$homeTeam['red_card'] = array('exp','red_card+'.($race['home_red_card']-$oldRace['home_red_card']));

		$guestTeam['goals_scored'] = array('exp','goals_scored+'.($race['guest_goal']-$oldRace['guest_goal']));
		$guestTeam['goals_against'] = array('exp','goals_against+'.($race['home_goal']-$oldRace['home_goal']));
		$guestTeam['yellow_card'] = array('exp','yellow_card+'.($race['guest_yellow_card']-$oldRace['guest_yellow_card']));
		$guestTeam['red_card'] = array('exp','red_card+'.($race['guest_red_card']-$oldRace['guest_red_card']));

		if(!$oldRace['end_time']){
			$hTeam['game_times'] = array('exp','game_times+1');
			$gTeam['game_times'] = array('exp','game_times+1');
		}

		if($oldRace['home_goal']-$oldRace['guest_goal']==0){
			if($race['home_goal']-$race['guest_goal']>0){
				$homeTeam['win_match'] = array('exp','win_match+1');
				$guestTeam['fail_match'] = array('exp','fail_match+1');

				$hTeam['game_win'] = array('exp','game_win+1');
				$gTeam['game_fail'] = array('exp','game_fail+1');

				$homeTeam['score'] = array('exp','score+3');
				$guestTeam['score'] = array('exp','score+0');
				$homeSanscore += 15;
				if($oldRace['end_time']){
					$homeTeam['flat_match'] = array('exp','flat_match-1');
					$guestTeam['flat_match'] = array('exp','flat_match-1');
					$hTeam['game_draw'] = array('exp','game_draw+1');
					$gTeam['game_draw'] = array('exp','game_draw+1');
					$homeTeam['score'] = array('exp','score+2');
					$guestTeam['score'] = array('exp','score-1');
				}
			}elseif($race['home_goal']-$race['guest_goal']<0){
				$homeTeam['fail_match'] = array('exp','fail_match+1');
				$guestTeam['win_match'] = array('exp','win_match+1');

				$hTeam['game_fail'] = array('exp','game_fail+1');
				$gTeam['game_win'] = array('exp','game_win+1');

				$guestTeam['score'] = array('exp','score+3');
				$homeTeam['score'] = array('exp','score-0');
				$guestSanscore += 15;
				if($oldRace['end_time']){
					$homeTeam['flat_match'] = array('exp','flat_match-1');
					$guestTeam['flat_match'] = array('exp','flat_match-1');

					$hTeam['game_draw'] = array('exp','game_draw-1');
					$gTeam['game_draw'] = array('exp','game_draw-1');

					$homeTeam['score'] = array('exp','score-1');
					$guestTeam['score'] = array('exp','score+2');
				}
			}else{
				if(!$oldRace['end_time']){
					$homeTeam['flat_match'] = array('exp','flat_match+1');
					$guestTeam['flat_match'] = array('exp','flat_match+1');

					$hTeam['game_draw'] = array('exp','game_draw+1');
					$gTeam['game_draw'] = array('exp','game_draw+1');

					$homeTeam['score'] = array('exp','score+1');
					$guestTeam['score'] = array('exp','score+1');
				}
				$homeSanscore += 5;
				$guestSanscore += 5;
			}
		}

		if($oldRace['home_goal']-$oldRace['guest_goal']>0){
			if($race['home_goal']-$race['guest_goal']==0){
				$homeTeam['win_match'] = array('exp','win_match-1');
				$homeTeam['flat_match'] = array('exp','flat_match+1');

				$hTeam['game_win'] = array('exp','game_win-1');
				$hTeam['game_draw'] = array('exp','game_draw+1');
				$gTeam['game_draw'] = array('exp','game_draw+1');
				$gTeam['game_fail'] = array('exp','game_fail-1');

				$guestTeam['flat_match'] = array('exp','flat_match+1');
				$guestTeam['fail_match'] = array('exp','fail_match-1');
				$homeTeam['score'] = array('exp','score-2');
				$guestTeam['score'] = array('exp','score+1');

				$homeSanscore += 5;
				$guestSanscore += 5;
			}elseif($race['home_goal']-$race['guest_goal']<0){
				$homeTeam['win_match'] = array('exp','win_match-1');
				$guestTeam['win_match'] = array('exp','win_match+1');
				$homeTeam['fail_match'] = array('exp','fail_match+1');
				$guestTeam['fail_match'] = array('exp','fail_match-1');

				$hTeam['game_win'] = array('exp','game_win-1');
				$hTeam['game_fail'] = array('exp','game_fail+1');
				$gTeam['game_win'] = array('exp','game_win+1');
				$gTeam['game_fail'] = array('exp','game_fail-1');

				$homeTeam['score'] = array('exp','score-3');
				$guestTeam['score'] = array('exp','score+3');

				$guestSanscore += 15;
			}else{
				$homeSanscore += 15;
			}
		}

		if($oldRace['home_goal']-$oldRace['guest_goal']<0){
			if($race['home_goal']-$race['guest_goal']==0){
				$homeTeam['fail_match'] = array('exp','fail_match-1');
				$guestTeam['win_match'] = array('exp','win_match-1');
				$homeTeam['flat_match'] = array('exp','flat_match+1');
				$guestTeam['flat_match'] = array('exp','flat_match+1');

				$hTeam['game_fail'] = array('exp','game_fail-1');
				$hTeam['game_draw'] = array('exp','game_draw+1');
				$gTeam['game_win'] = array('exp','game_win-1');
				$gTeam['game_draw'] = array('exp','game_draw+1');

				$homeTeam['score'] = array('exp','score+1');
				$guestTeam['score'] = array('exp','score-2');

				$homeSanscore += 5;
				$guestSanscore += 5;
			}elseif($race['home_goal']-$race['guest_goal']>0){
				$homeTeam['win_match'] = array('exp','win_match+1');
				$guestTeam['win_match'] = array('exp','win_match-1');
				$homeTeam['fail_match'] = array('exp','fail_match-1');
				$guestTeam['fail_match'] = array('exp','fail_match+1');

				$hTeam['game_win'] = array('exp','game_win+1');
				$hTeam['game_fail'] = array('exp','game_fail-1');
				$gTeam['game_win'] = array('exp','game_win-1');
				$gTeam['game_fail'] = array('exp','game_fail+1');

				$homeTeam['score'] = array('exp','score+3');
				$guestTeam['score'] = array('exp','score-3');
				$homeSanscore += 15;
			}else{
				$guestSanscore += 15;
			}
		}
		//赛事积分录入完成

		//战点录入
		if($race['home_fighting']){
			$homeSanscore -= 20;
		}
		if($race['guest_fighting']){
			$guestSanscore -= 20;
		}
		$homeSanscore -= intval($data['home_red_card'])*3;
		$guestSanscore -= intval($data['guest_red_card'])*3;
		$homeSanscore -= intval($data['home_yellow_card']);
		$guestSanscore -= intval($data['guest_yellow_card']);
		if($race['home_beat_referee']){
			$homeSanscore = -$inHomeSanscore;
		}

		if($race['guest_beat_referee']){
			$guestSanscore = -$inGuestSanscore;
		}
		//战点录入end

		$race['home_score'] = $homeSanscore;
		$race['guest_score'] = $guestSanscore;

//		var_dump($hTeam);
//		var_dump($gTeam);

		try {
			$this->competitionRaceModel->startTrans();
			$this->competitionRaceModel->updateRace($race);		//更新赛程
			$this->ballTeamModel->where("ball_team_id=%d",$homeTeamId)->setInc("san_score",($homeSanscore-$oldHomeSanscore));
			$this->ballTeamModel->where("ball_team_id=%d",$guestTeamId)->setInc("san_score",($guestSanscore-$oldGuestSanscore));
			if($homeTeamInfo['san_score']+$homeSanscore-$oldHomeSanscore<0){
				$this->ballTeamModel->where("ball_team_id=%d",$homeTeamId)->save(["san_score"=>0]);
			}
			if($guestTeamInfo['san_score']+$guestSanscore-$oldGuestSanscore<0){
				$this->ballTeamModel->where("ball_team_id=%d",$guestTeamId)->save(["san_score"=>0]);
			}
			if(!empty($hTeam)){
				$this->ballTeamModel->where("ball_team_id=%d",$homeTeamId)->save($hTeam);
			}
			if(!empty($gTeam)){
				$this->ballTeamModel->where("ball_team_id=%d",$guestTeamId)->save($gTeam);
			}
			$this->competitionBallTeamModel->where("competition_id=%d AND ball_team_id=%d",$competitionId,$homeTeamId)->save($homeTeam);
			$this->competitionBallTeamModel->where("competition_id=%d AND ball_team_id=%d",$competitionId,$guestTeamId)->save($guestTeam);
			$this->competitionRaceModel->commit();
//			var_dump($race);
//			var_dump($this->competitionBallTeamModel->getError());
//			var_dump($this->ballTeamModel->getError());
//			var_dump($oldHomeSanscore);
			$this->ajaxReturn(1);
		} catch (\Exception $e) {
			if($e->getMessage()) var_dump($e->getMessage());
			$this->competitionRaceModel->rollback();
			$this->ajaxReturn(2);
		}

	}

	public function get_guestTeam(){
	    $home_team_id=I('post.home_team_id');
	    $competition_id=I('post.competition_id');
	    $where=" competition_id = ".$competition_id." and ball_team_id <> ".$home_team_id;
	    $guestTeamMsg=M('competition_ballteam')->where($where)->select();
	    foreach($guestTeamMsg as $key=>$val){
	        $val['guest_team_name']=M('ball_team')->where('ball_team_id='.$val['ball_team_id'])->getField('name');
	        $guestTeamMsg[$key]=$val;
	    }
	    $this->ajaxReturn($guestTeamMsg);
	}

	public function competitionRace_del(){
	    $race_id=I('post.race_id');
	    $result=M('competition_race')->where('race_id='.$race_id)->delete();
	    $this->ajaxReturn($result);
	}
	public function saicheng_stop(){
	    $race_id=I('post.race_id');
	    $result=M('competition_race')->where('race_id='.$race_id)->save(array('is_recommend'=>0));
	    $this->ajaxReturn($result);
	}
	public function saicheng_start(){
	    $race_id=I('post.race_id');
	    $result=M('competition_race')->where('race_id='.$race_id)->save(array('is_recommend'=>1));
	    $this->ajaxReturn($result);
	}

	public function competition_stop(){
	    $competition_id=I('post.competition_id');
	    $result=M('competition')->where('competition_id='.$competition_id)->save(array('is_show'=>0));
	    $this->ajaxReturn($result);
	}
	public function competition_start(){
	    $competition_id=I('post.competition_id');
	    $result=M('competition')->where('competition_id='.$competition_id)->save(array('is_show'=>1));
	    $this->ajaxReturn($result);
	}
	public function competition_del(){
	    $competition_id=I('post.competition_id');
	    $result=M('competition')->where('competition_id='.$competition_id)->delete();
	    $this->ajaxReturn($result);
	}

	/*参数证函数*/

	/*关闭功能*/
    public function close_card(){
        $competition_id=I('post.competition_id');
        $result=M('competition')->where('competition_id='.$competition_id)->setField('switched','0');
        $this->ajaxReturn($result);
    }

    /*开启功能*/
    public function open_card(){
        $competition_id=I('post.competition_id');
        $result=M('competition')->where('competition_id='.$competition_id)->setField('switched','1');
        $this->ajaxReturn($result);
    }

	/**
	 * 赛事动态
	 */
	public function events(){
	    $eventsMsg=M('competition_posts')->select();
	    foreach($eventsMsg as $key=>$val){
	        $val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
	        $val['competition_name']=M('competition')->where('competition_id='.$val['competition_id'])->getField('title');
	        //$val['content']=html_entity_decode($val['content']);
	        $val['content']=strip_tags($val['content']);

	        $eventsMsg[$key]=$val;
	    }

	    $this->assign('countEvent',count($eventsMsg));
	    $this->assign('eventsMsg',$eventsMsg);
	    $this->display();
	}
	public function events_stop(){
	    $id=I('post.id');
	    $result=M('competition_posts')->where('id='.$id)->save(array('status'=>0));
	    $this->ajaxReturn($result);
	}
	public function events_start(){
	    $id=I('post.id');
	    $result=M('competition_posts')->where('id='.$id)->save(array('status'=>1));
	    $this->ajaxReturn($result);
	}
	public function events_del(){
	    $id=I('post.id');
	    $result=M('competition_posts')->where('id='.$id)->delete();
	    $this->ajaxReturn($result);
	}
	public function events_add(){
	    $competitionMsg=M('competition')->select();
		foreach($competitionMsg as $key=>$val){
			$val['content']=html_entity_decode($val['content']);
		}

	    if(!empty($_POST)){
	        $dataArr['competition_id']=I('post.competition_id');
	        $dataArr['create_time']=time();
			$dataArr['create_date'] = strtotime(date("Y-m-d"));
	        $dataArr['title']=I('post.title');
	        $dataArr['content']=I('post.content');
	        $dataArr['status']=1;

	        if(!empty($_FILES)){
	            $config = array(
	                'maxSize' => 3145728,
	                'rootPath' => './Public/',
	                'savePath' => '/Uploads/competition/',
	                'saveName' => array('uniqid',''),
	                'exts' => array('jpg', 'gif', 'png', 'jpeg'),
	                'autoSub' => true,
	                'subName' => array('date','Y-m-d'),
	            );
	            $upload = new \Think\Upload($config);// 实例化上传类
	            $info = $upload->upload();
	            if($info){
                    $dataArr['img']=C('__UPLOADS_PATH__').$info['file-2']['savepath'].$info['file-2']['savename'];
                    $result=M('competition_posts')->add($dataArr);
                }else{
                    $this->error($upload->getError());
                }
	        }else{
	            $result=M('competition_posts')->add($dataArr);
	        }

	        if($result){
	            echo "<div id='close' style='display:none'>1</div>";
	        }else{
	            echo "<div id='close' style='display:none'>2</div>";
	        }
	    }


	    $this->assign('competitionMsg',$competitionMsg);
	    $this->display();
	}
	public function events_edit(){
	    $id=I('id');
	    $competitionMsg=M('competition')->select();
	    $compostsMsg=M('competition_posts')->where('id='.$id)->find();
		$compostsMsg['content']=html_entity_decode($compostsMsg['content']);

	    if(!empty($_POST)){
	        $id=I('post.id');
	        $dataArr['competition_id']=I('post.competition_id');
	        $dataArr['create_time']=time();
	        $dataArr['title']=I('post.title');
	        $dataArr['content']=I('post.content');
	        $dataArr['status']=1;
	        if(!empty($_FILES['file-2']['size'])){
	            $config = array(
	                'maxSize' => 3145728,
	                'rootPath' => './Public/',
	                'savePath' => '/Uploads/competition/',
	                'saveName' => array('uniqid',''),
	                'exts' => array('jpg', 'gif', 'png', 'jpeg'),
	                'autoSub' => true,
	                'subName' => array('date','Y-m-d'),
	            );
	            $upload = new \Think\Upload($config);// 实例化上传类
	            $info = $upload->upload();
	            if($info){
	                $dataArr['img']=C('__UPLOADS_PATH__').$info['file-2']['savepath'].$info['file-2']['savename'];
	                $result=M('competition_posts')->where('id='.$id)->save($dataArr);
	            }else{
	                $this->error($upload->getError());
	            }
	        }else{
	            $result=M('competition_posts')->where('id='.$id)->save($dataArr);
	        }

	        if($result){
	            echo "<div id='close' style='display:none'>1</div>";
	        }else{
	            echo "<div id='close' style='display:none'>2</div>";
	        }
	    }

	    $this->assign('compostsMsg',$compostsMsg);
	    $this->assign('competitionMsg1',$competitionMsg);
	    $this->display();
	}

	public function events_show_views(){
	    $competition_posts_id=I('get.id');
	    $viewMsg=M('competition_posts_comment')->where('competition_posts_id='.$competition_posts_id)->select();
	    foreach($viewMsg as $key=>$val){
	        $val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
	        $viewMsg[$key]=$val;
	    }

	    $this->assign('countViews',count($viewMsg));
	    $this->assign('viewMsg',$viewMsg);
	    $this->display();
	}
	public function views_del(){
	    $id=I('post.id');
	    $result=M('competition_posts_comment')->where('id='.$id)->delete();
	    $this->ajaxReturn($result);
	}

	public function competition_addteam(){
		$competition_id=I('get.competition_id');
		$competition_name=M('competition')->where('competition_id='.$competition_id)->getField('title');
		$competion_ballTeam=M('competition_ballteam')->where('competition_id='.$competition_id)->select();
		foreach($competion_ballTeam as $key=>$val){
			$val['competition_name']=$competition_name;
			$val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
			$val['ball_team_name']=M('ball_team')->where('ball_team_id='.$val['ball_team_id'])->getField('name');
			$competion_ballTeam[$key]=$val;
		}
		$this->assign('competition_id',$competition_id);
		$this->assign('countCompetitionTeam',count($competion_ballTeam));
		$this->assign('competion_ballTeam',$competion_ballTeam);
		$this->display();
	}

	public function competition_ball_team_del(){
		$id=I('post.id');
		$result=M('competition_ballteam')->where('id='.$id)->delete();
		$this->ajaxReturn($result);
	}
	public function competition_team_add(){
		$competition_id=I('competition_id');
		$ball_team_Msg=M('ball_team')->where('is_verify=1')->select();

		if(!empty($_POST)){
			$dataArr['competition_id']=I('post.competition_id');
			$dataArr['ball_team_id']=I('post.ball_team_id');
			$dataArr['user_id']=M('ball_team')->where('ball_team_id='.$dataArr['ball_team_id'])->getField('uid');
			$dataArr['join_time']=time();
			$dataArr['create_time']=time();
			$dataArr['status'] = 1;

			$result=M('competition_ballteam')->add($dataArr);
			if($result){
				echo "<div id='close' style='display:none'>1</div>";
			}else{
				echo "<div id='close' style='display:none'>2</div>";
			}
		}

		$this->assign('competition_id',$competition_id);
		$this->assign('ball_team_Msg',$ball_team_Msg);
		$this->display();
	}

	public function  competition_group(){
		$competition_id=I('get.competition_id');
		$grouplist=M('competition_group')->where('competition_id='.$competition_id)->select();
		foreach($grouplist as $key=>$val){
			$val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
			$val['competition_name']=M('competition')->where('competition_id='.$competition_id)->getField('title');
			$grouplist[$key]=$val;
		}

		$this->assign('countMsg',count($grouplist));
		$this->assign('teamlist',$grouplist);
		$this->assign('competition_id',$competition_id);
		$this->display();
	}
	public function competition_group_add(){
		$competition_id=I('get.competition_id');

		if(!empty($_POST)){
			$data['competition_id']=I('post.competition_id');
			$data['name']=I('post.name');
			$data['create_time']=time();

			$result=M('competition_group')->add($data);

			if($result){
				echo "<div id='close' style='display:none'>1</div>";
			}else{
				echo "<div id='close' style='display:none'>2</div>";
			}
		}

		$this->assign('competition_id',$competition_id);
		$this->display();
	}

	public function competition_ballteam_add(){
		$competition_id=I('competition_id');
		$competition_group_id=I('competition_group_id');
		$groupMsg=M('competition_group')->where('competition_id='.$competition_id)->select();
		$groupBallTeamMsg=M('competition_ballteam')->where('competition_id='.$competition_id)->select();
		foreach($groupBallTeamMsg as $key=>$val){
			$val['team_name']=M('ball_team')->where('ball_team_id='.$val['ball_team_id'])->getField('name');
			$groupBallTeamMsg[$key]=$val;
		}

		if(!empty($_POST)){
			$competition_id=I('post.competition_id');
			$data['competition_group_id']=I('post.competition_group_id');
			$data['ball_team_id']=I('post.ball_team_id');
			$data['competition_ballteam_id']=M('competition_ballteam')->where(array('competition_id'=>$competition_id,'ball_team_id'=>$data['ball_team_id']))->getField('id');
			$data['create_time']=time();

			$result=M('competition_group_member')->add($data);

			if($result){
				echo "<div id='close' style='display:none'>1</div>";
			}else{
				echo "<div id='close' style='display:none'>2</div>";
			}
		}
		$this->assign('competition_group_id',$competition_group_id);
		$this->assign('groupBallTeamMsg',$groupBallTeamMsg);
		$this->assign('groupMsg',$groupMsg);
		$this->assign('competition_id',$competition_id);
		$this->display();
	}
	public function groupTeam_del(){
		$id=I('post.id');
		$result=M('competition_group')->where('id='.$id)->delete();
		$this->ajaxReturn($result);
	}
	public function competition_group_teamlist(){
		$id=I('get.id');
		$competition_id=I('get.competition_id');
		$teamList=M('competition_group_member')->where('competition_group_id='.$id)->select();
		foreach($teamList as $key=>$val){
			$val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
			$val['group_name']=M('competition_group')->where('id='.$val['competition_group_id'])->getField('name');
			$val['ball_team_name']=M('ball_team')->where('ball_team_id='.$val['ball_team_id'])->getField('name');
			$teamList[$key]=$val;
		}

		$this->assign('competition_group_id',$id);
		$this->assign('competition_id',$competition_id);
		$this->assign('teamlist',$teamList);
		$this->assign('countTeamlist',count($teamList));
		$this->display();
	}
	public function group_team_del(){
		$id=I('post.id');
		$result=M('competition_group_member')->where('id='.$id)->delete();
		$this->ajaxReturn($result);
	}
	public function getballTeamBysearch(){
		$search=I('post.search');
		$where="name like '%".$search."%'";
		$result=M('ball_team')->where($where)->select();

		$this->ajaxReturn($result);
	}

	/**
	 *	赛事轮次
     */
	public function round_list(){
		$competition_id = $_REQUEST['competition_id'];
		$this->assign("competition_id",$competition_id);
		$this->display();
	}

	public function getRoundList(){
		$queryParams = [
			"limit" => $_REQUEST['limit'],
			"offset" => $_REQUEST['offset'],
			"sort" => $_REQUEST['sort'],
			"order" => $_REQUEST['order'],
			"start_time" => $_REQUEST['start_time'],
			"end_time" => $_REQUEST['end_time'],
			"competition_id" => $_REQUEST['competition_id'],
			"type" => $_REQUEST['type']
		];

		$option = intval($_REQUEST['option']);
		$search = $_REQUEST['search'];
		if(!empty($search)){
			switch($option){
				case 0:             //标题
					$queryParams['round_id'] = $search;
					break;
			}
		}
		$data = $this->competitionRoundModel->search($queryParams);
		formatRes($data,$_REQUEST['draw']);
//        var_dump($data);
		response($data);
	}

	/**
	 *	删除轮次
     */
	public function delRound(){
		$id = $_REQUEST['id'];
		try {
			$this->competitionRoundModel->delById($id);
			response();
		} catch (\Exception $e) {
			response([],$e->getMessage(),501);
		}
	}

	public function addRound(){
		$competition_id = $_REQUEST['competition_id'];
		$competition_type = $this->competitionModel->where("competition_id=".intval($competition_id))->getField("type");
		$this->assign("competition_id",$competition_id|0);
		$this->assign("competition_type",$competition_type|0);
		$this->display()
;	}

	/**
	 *	添加轮次
     */
	public function doAddRound(){
		$data = [
			"competition_id" => $_REQUEST['competition_id'],
			"title" => trim($_REQUEST['title']),
			"type" => $_REQUEST['type'],
			"round_num" => $_REQUEST['round_num']
		];
		$date = strtotime($_REQUEST['date']);
		$data['date'] = $date;

		try {
//			var_dump($data);
			$this->competitionRoundModel->addRound($data);
			response();
		} catch (\Exception $e) {
			response([],$e->getMessage(),501);
		}
	}

	/**
	 *	修改轮次
     */
	public function editRound(){
		$round_id = $_REQUEST['id'];
		$data = $this->competitionRoundModel->getById($round_id);
		$competition_type = $this->competitionModel->where("competition_id=".intval($data['competition_id']))->getField("type");
		$data['date'] = date("Y-m-d",$data['date']);
		$this->assign("competition_type",$competition_type|0);
		$this->assign("round",$data);
		$this->display();
	}

	public function doEditRound(){
		$data = [
			"round_id" => $_REQUEST['round_id'],
			"title" => trim($_REQUEST['title']),
			"type" => $_REQUEST['type'],
			"round_num" => $_REQUEST['round_num'],
			"date" => strtotime($_REQUEST['date'])
		];

		try {
			$this->competitionRoundModel->updateRound($data);
			response();
		} catch (\Exception $e) {
			response([],$e->getMessage(),501);
		}
	}

}

