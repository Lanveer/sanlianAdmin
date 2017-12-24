<?php
namespace Admin\Controller;
use Admin\Model\JpushModel;
use Think\Controller;
use Admin\Model\MessageModel;
class ActivityTeamController extends CommonController{

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 球队列表
     */
    public function teamlist(){
        $teamlist=M('ball_team')->select();
        foreach($teamlist as $key=>$val){
            $val['build_time']=date('Y-m-d H:i:s',$val['build_time']);
            $userMsg=M('user')->where('user_id='.$val['uid'])->find();
            $val['username']=$userMsg['nickname'];
            $teamlist[$key]=$val;
        }
        
        $this->assign('countTeam',count($teamlist));
        $this->assign('teamlist',$teamlist);
        $this->display();
    }
    
    public function addTeam(){
        $provinceMsg=M('province')->select();
        $userMsg=M('user')->select();
        foreach($userMsg as $key=>$val){
            $teamMsg=M('ball_team')->where('uid='.$val['user_id'])->find();
            if($teamMsg){
                array_splice($userMsg, $key, 1);
            }
        }        
        
        if(!empty($_POST)){
            
            $res=M('ball_team')->where(array('name'=>I('post.name')))->find();
            if($res){
                echo '该名称已被创建，请更换....';
                die();               
            }
            
            $dataArr=array(
                'name'              =>trim(I('post.name')),
                'uid'               =>I('post.uid'),
                'short_name'        =>trim(I('post.short_name')),
                'lead_name'         =>trim(I('post.lead_name'))?trim(I('post.lead_name')):$_SESSION['AdminUserMsg']['loginname'],
                'build_time'        =>strtotime(I('post.build_time')),
                'province_id'       =>I('post.province_id'),
                'city_id'           =>I('post.city_id'),
                'county_id'         =>I('post.county_id'),
                'intro'             =>I('post.intro'),
                'is_verify'         =>1,
                'member_num'        =>1
            );
            if(!empty($_FILES)){
                $config = array(
                    'maxSize' => 2097152,
                    'rootPath' => './Public/',
                    'savePath' => '/Uploads/emblem/',
                    'saveName' => array('uniqid',''),
                    'exts' => array('jpg', 'gif', 'png', 'jpeg'),
                    'autoSub' => true,
                    'subName' => array('date','Y-m-d'),
                );
                $upload = new \Think\Upload($config);// 实例化上传类
                $info = $upload->upload();
                if($info){
                    $dataArr['logo']=C('__UPLOADS_PATH__').$info['file-2']['savepath'].$info['file-2']['savename'];
                    $result=M('ball_team')->add($dataArr);                    
                }else{
                    $this->error($upload->getError());
                }
            }else{
                $result=M('ball_team')->add($dataArr);
            }
            
            if($result){
                
                $lastid=$result;
                $teamArr=array(
                    'ball_team_id'      =>$lastid,
                    'uid'               =>I('post.uid'),
                    'create_time'       =>time()
                );
                
                $res=M('ball_team_member')->add($teamArr);
                if($res){
                    echo "<div id='close' style='display:none'>1</div>";
                }
            }
                       
        }
        
        $this->assign('userMsg',$userMsg);
        $this->assign('province',$provinceMsg);
        $this->display();
    }
    
    public function activityTema_del(){
        $id=I('post.id');
        /* $delMsg=M('ball_team')->where('ball_team_id='.$id)->find();
        $res=@unlink($delMsg['logo']); */
        $result=M('ball_team')->where('ball_team_id='.$id)->delete();
        $this->ajaxReturn($result);
    }
    public function teamShow(){
        $ball_team_id=I('get.ball_team_id');
        $teamMsg=M('ball_team_member')->where('ball_team_id='.$ball_team_id)->select();
        foreach($teamMsg as $key=>$val){
            $val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
            $val['userMsg']=M('user')->where('user_id='.$val['uid'])->find();
            $teamMsg[$key]=$val;
        }
        
        $this->assign('ball_team_id',$ball_team_id);
        $this->assign('countTeam',count($teamMsg));
        $this->assign('teamMsg',$teamMsg);
        $this->display();
    }
    public function TeamPerson_del(){
        $id=I('post.id');
        $result=M('ball_team_member')->where('id='.$id)->delete();
        
        $this->ajaxReturn($result);
    }
    
    public function getsearch(){
        $search=I('post.search');
        $where="phone like '%".$search."%' or nickname like '%".$search."%'";
        $result=M('user')->where($where)->select();
        
        $this->ajaxReturn($result);
    }
    
    /**
     * 球员列表
     */
    public function personlist(){
        $personMsg=M('user')->select();
        foreach($personMsg as $key=>$val){
            $val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
            $personMsg[$key]=$val;
        }
        
        $this->assign('countPerson',count($personMsg));
        $this->assign('personMsg',$personMsg);
        $this->display();
    }

    public function person_list(){
        $this->display();
    }

    public function getPersonList(){
        $userModel = D("User");
        $queryParams = [
            "limit" => $_REQUEST['limit'],
            "offset" => $_REQUEST['offset'],
            "sort" => $_REQUEST['sort'],
            "order" => $_REQUEST['order'],
            "start_time" => $_REQUEST['start_time'],
            "end_time" => $_REQUEST['end_time'],
            "status" => $_REQUEST['status']
        ];

        $option = intval($_REQUEST['option']);
        $search = $_REQUEST['search'];
        if(!empty($search)||$search==0){
            switch($option){
                case 0:             //标题
                    $queryParams['user_id'] = $search;
                    break;
                case 1:             //标题
                    $queryParams['nickname'] = $search;
                    break;
                case 2:             //标题
                    $queryParams['phone'] = $search;
                    break;
                case 3:
                    $queryParams['vip_id'] = $search;
                    break;
            }
        }
        $data = $userModel->search($queryParams);
        formatRes($data,$_REQUEST['draw']);
//        var_dump($data);
        response($data);
    }

    public function sanlianMoney_edit(){
        $user_id=I('user_id');
        $vipMsg=M('vip')->select();
        $userMsg=M('user')->where('user_id='.$user_id)->find();
        $recordModel = D("Record");

        if(!empty($_POST)){
            $user_id=I('post.user_id');
            $data['vip_id']=I('post.vip_id');
            $data['san_money']=I('post.san_money');
            $money_change = $data['san_money'] - $userMsg['san_money'];
            $record = [
                "type" => 5,
                "remark" => "后台人员修改三联币金额",
                "data" => session("admin_key_loginname"),
                "uid" => $user_id,
                "money" =>$money_change
            ];
            try {
                $recordModel->startTrans();
                $result = M('user')->where('user_id=' . $user_id)->save($data);
                if($money_change){
                    $recordModel->addRecord($record);
                }
                if($result){
                    echo "<div id='close' style='display:none'>1</div>";
                }else{
                    throw new \Exception("更新用户三联币失败");
                }
                $recordModel->commit();
            } catch (\Exception $e) {
                $recordModel->rollback();
                echo "<div id='close' style='display:none'>2</div>";
            }


        }
        
        $this->assign('userMsg',$userMsg);
        $this->assign('vipMsg',$vipMsg);
        $this->display();
    }

    public function remark_edit(){
        $user_id=I('user_id');
        $userMsg=M('user')->where('user_id='.$user_id)->find();

        if(!empty($_POST)){
            $user_id=I('post.user_id');
            $data['remark']=I('post.remark');
            $result=M('user')->where('user_id='.$user_id)->save($data);
            if($result){
                echo "<div id='close' style='display:none'>1</div>";
            }else{
                echo "<div id='close' style='display:none'>2</div>";
            }
        }

        $this->assign('userMsg',$userMsg);
        $this->display();
    }

    public function person_del(){
        $id=I('post.id');
        $result=M('user')->where('user_id='.$id)->delete();
        $this->ajaxReturn($result);
    }

    /**
     *  用户钱包记录
     */
    public function money_record(){
        $user_id = $_REQUEST['user_id'];
        $this->assign("user_id",$user_id);
        $this->display();
    }

    /**
     *  获取用户钱包记录
     */
    public function getRecordList(){
        $recordModel = D("Record");
        $queryParams = [
            "limit" => $_REQUEST['limit'],
            "offset" => $_REQUEST['offset'],
            "sort" => $_REQUEST['sort'],
            "order" => $_REQUEST['order'],
            "start_time" => $_REQUEST['start_time'],
            "end_time" => $_REQUEST['end_time'],
            "uid" => $_REQUEST['user_id']
        ];

        $option = intval($_REQUEST['option']);
        $search = $_REQUEST['search'];
        if(!empty($search)||$search==0){
            switch($option){
                case 0:             //标题
                    $queryParams['id'] = $search;
                    break;
                case 1:             //标题
                    $queryParams['remark'] = $search;
                    break;
                case 2:             //标题
                    $queryParams['data'] = $search;
                    break;
            }
        }
        $data = $recordModel->search($queryParams);
        formatRes($data,$_REQUEST['draw']);
//        var_dump($data);
        response($data);
    }
    
    /**
     * 队徽分类
     */
    public function emblemCategory(){
        $categoryMsg=M('category')->select();
        foreach($categoryMsg as $key=>$val){
            $val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
            $categoryMsg[$key]=$val;
        }
        
        $this->assign('countCategory',count($categoryMsg));
        $this->assign('categoryMsg',$categoryMsg);
        $this->display();
    }
    public function addcategory(){
        if(!empty($_POST)){
            $categoryArr['name']=I('post.name');
            $categoryArr['content']=I('post.content');
            $categoryArr['create_time']=time();
            $result=M('category')->add($categoryArr);
            if($result){
                echo "<div id='close'>添加成功</div>";
            }
        }
        
        $this->display();
    }
    public function category_del(){
        $id=I('post.id');
        $result=M('category')->where('id='.$id)->delete();
        $this->ajaxReturn($result);
    }
    public function category_edit(){
        $id=I('get.id');
        $categoryMsg=M('category')->where('id='.$id)->find();
        
        $this->assign('categoryMsg',$categoryMsg);
        $this->display();
    }
    public function savecategory(){
        $id=I('post.id');
        $saveArr['name']=I('post.name');
        $saveArr['content']=I('post.content');
        $result=M('category')->where('id='.$id)->save($saveArr);
        if($result){
            echo "<div id='close'>修改成功</div>";
        }
    }
    
    /**
     * 队徽列表
     */
    public function emblemlist(){
        $emblemMsg=M('emblem')->select();
        foreach($emblemMsg as $key=>$val){
            $val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
            $val['name']=M('category')->where('id='.$val['category_id'])->getField('name');
            $emblemMsg[$key]=$val;
        }
        
        $this->assign('countEmblem',count($emblemMsg));
        $this->assign('emblemMsg',$emblemMsg);
        $this->display();
    }
    
    public function activity_edit(){
        $id=I('get.id');
        $editMsg=M('emblem')->where('id='.$id)->find();
        $category=M('category')->select();
               
        $this->assign('category',$category);
        $this->assign('editMsg',$editMsg);
        $this->display();
    }
    
    public function emblem_edit(){
        if(!empty($_POST)){
            $id=I('post.id');
            $dataArr['title']=I('post.title');
            $dataArr['content']=I('post.content');
            $dataArr['category_id']=I('post.category_id');
            if(!empty($_FILES)){
                $config = array(
                    'maxSize' => 2097152,
                    'rootPath' => './Public/',
                    'savePath' => '/Uploads/emblem/',
                    'saveName' => array('uniqid',''),
                    'exts' => array('jpg', 'gif', 'png', 'jpeg'),
                    'autoSub' => true,
                    'subName' => array('date','Y-m-d'),
                );
                $upload = new \Think\Upload($config);// 实例化上传类
                $info = $upload->upload();
                if($info){
                    $dataArr['logo']=C('__UPLOADS_PATH__').$info['file-2']['savepath'].$info['file-2']['savename'];
                    $result=M('emblem')->where('id='.$id)->save($dataArr);                   
                    if($result){
                        echo "<div id='close'>修改成功</div>";
                    }
                }else{
                    $this->error($upload->getError());
                }
            }else{
                $result=M('emblem')->where('id='.$id)->save($dataArr);
                if($result){
                    echo "<div id='close'>修改成功</div>";
                }
            }
        }
    }
    
    public function emblem_stop(){
        $id=I('post.id');
        $result=M('emblem')->where('id='.$id)->save(array('status'=>0));
        $this->ajaxReturn($result);
    }
    public function emblem_del(){
        $id=I('post.id');
        $result=M('emblem')->where('id='.$id)->delete();
        $this->ajaxReturn($result);
    }
    
    public function emblem_start(){
        $id=I('post.id');
        $result=M('emblem')->where('id='.$id)->save(array('status'=>1));
        $this->ajaxReturn($result);
    }
    
    public function message(){
        $userid=I('get.user_id');
        $deviceuuid=I('get.deviceuuid');
        
        $this->assign('userid',$userid);
        $this->assign('deviceuuid',$deviceuuid);
        $this->display();
    }
    
    public function sendTeamMessage(){
        $ball_team_id=I('get.ball_team_id');
        $jpushTool = new \JPushTool();
        if(!empty($_POST)){
            $send_team_id=I('post.ball_team_id');
            $title=I('post.title');
            $sendContent=I('post.content');
            $userMsg=M('ball_team_member')->where('ball_team_id='.$ball_team_id)->select();
            $deviceuuidstr='';
            for($i=0;$i<count($userMsg);$i++){
                $deviceuuidstr .=M('user')->where('user_id='.$userMsg[$i]['uid'])->getField('deviceuuid').',';
                $deviceuuidArr[$i]=M('user')->where('user_id='.$userMsg[$i]['uid'])->getField('user_id');
            }

            /*************************mongo数据**************************************/
            $sendMsgArr['title']=$title;
            $sendMsgArr['content']=$sendContent;
            $sendMsgArr['type']=4;//0订场 1排位赛 2活动 3球队管理 4系统消息
            $sendMsgArr['create_time']=time();
            $sendMsgArr['ext']=array(
                'msg_type'		=>4,
                'type'			=>0,
                'ball_team_id'	=>$ball_team_id,
                'ball_team_name'=>$userMsg['name'],
            );
            $sendMsgArr['target_user']=$deviceuuidArr;
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
//                $content['n_extras']	=array(
//                    'msg_type'	=>4,
//                    'ball_team_id'	=>$ball_team_id,
//                    'ball_team_name'=>$userMsg['name'],
//                    //'content'		=>$sendContent,
//                    '_id'           =>$id,
//                );
//
//                $JpushModel=new JpushModel();
//                $res=$JpushModel->push($data,$content);
                $sendMsgArr['ext']['_id'] = $id;
                $res = $jpushTool->sendToSomeone($sendMsgArr['target_user'],$sendMsgArr['title'],$sendMsgArr['ext']);
                if($res){
                    echo "<div id='close' style='display:none'>1</div>";
                }
            }
        }
        
        $this->assign('ball_team_id',$ball_team_id);
        $this->display();
    }
    
    public function sendPersonMessage(){
        $jpushTool = new \JPushTool();
        if(!empty($_POST)){
            $userid=I('post.userid');
            $deviceuuid=I('post.deviceuuid');
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
            $sendMsgArr['target_user']=[intval($userid)];
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
            $res=$Model->add($sendMsgArr);
            //var_dump(json_decode(json_encode($res),1)['$id']);
            if($res){
                $id=json_decode(json_encode($res),1)['$id'];
                $sendMsgArr['ext']['_id'] = $id;
//                $content['n_extras']	=array(
//                    'msg_type'	=>4,
//                    'ball_team_id'	=>'',
//                    'ball_team_name'=>'',
//                    //'content'		=>$sendContent,
//                    '_id'           =>$id,
//                );
//
//                $JpushModel=new JpushModel();
//                $res1=$JpushModel->push($data,$content);
                $res1 = $jpushTool->sendToSomeone($deviceuuid,$title,$sendMsgArr['ext']);
                if($res1){
                    M('user')->where('user_id='.$userid)->setInc('system_point');
                    echo "<div id='close' style='display:none'>1</div>";
                    $this->display('ActivityTeam/message');
                }
            }
        }
    }
    
    public function addTeamPerson(){
        $userMsg=M('user')->select();
        $ball_team_id=I('get.ball_team_id');
        $position=array(
            array('id'    =>1,'name'  =>'守门员'),
            array('id'    =>2,'name'  =>'右后卫'),
            array('id'    =>3,'name'  =>'中后卫'),
            array('id'    =>4,'name'  =>'左后卫'),
            array('id'    =>5,'name'  =>'后腰'),
            array('id'    =>6,'name'  =>'右前卫'),
            array('id'    =>7,'name'  =>'中前卫'),
            array('id'    =>8,'name'  =>'左前卫'),
            array('id'    =>9,'name'  =>'前腰'),
            array('id'    =>10,'name'  =>'右边锋'),
            array('id'    =>11,'name'  =>'中锋' ),
            array('id'    =>12,'name'  =>'左边锋'),
            array('id'    =>13,'name'  =>'前锋'),
            array('id'    =>14,'name'  =>'影锋'),
            array('id'    =>15,'name'  =>'自由人'),
            array('id'    =>16,'name'  =>'无位置'),
        );
        $type=array(
            array('id'    =>1,'name'  =>'队员'),
            array('id'    =>2,'name'  =>'总经理'),
            array('id'    =>3,'name'  =>'财务后勤'),
            array('id'    =>4,'name'  =>'主教练'),
            array('id'    =>5,'name'  =>'助理教练'),
            array('id'    =>6,'name'  =>'队医'),
            array('id'    =>7,'name'  =>'新闻官'),
            array('id'    =>8,'name'  =>'拉拉队员'),
            array('id'    =>9,'name'  =>'赞助商'),
            array('id'    =>10,'name'  =>'队务'),
            array('id'    =>11,'name'  =>'主席'),
            array('id'    =>12,'name'  =>'领队'),
            array('id'    =>13,'name'  =>'无角色'),
        );
        if(!empty($_POST)){
            $dataArr=array(
                'ball_team_id'      =>I('post.id'),
                'uid'               =>I('post.uid'),
                'clubnumber'        =>I('post.clubnumber'),
                'remark'            =>I('post.remark'),
                'create_time'       =>time(),
                'type'              =>I('post.type'),
                'position'          =>I('post.position')
            );
            
            $result=M('ball_team_member')->add($dataArr);
            $res=M('ball_team')->where('ball_team_id='.I('post.id'))->setInc('member_num');
            if($result){
                echo "<div id='close' style='display:none'>1</div>";
            }
        }
        $this->assign('type',$type);
        $this->assign('position',$position);
        $this->assign('id',$ball_team_id);
        $this->assign('userMsg',$userMsg);
        $this->display();
    }
    
    public function edit_team(){
        $ball_team_id=I('get.ball_team_id');
        $province=M('province')->select();
        $ball_team_Msg=M('ball_team')->where('ball_team_id='.$ball_team_id)->find();
        $userInfo = M('user')->field('nickname,phone')->where('user_id='.$ball_team_Msg['uid'])->find();
        $ball_team_Msg['username']=$userInfo['nickname'];
        $ball_team_Msg['userphone']=$userInfo['phone'];


        if(!empty($_POST)){
            $id=I('post.id');
            $dataArr=array(
                'name'              =>trim(I('post.name')),
                'uid'               =>I('post.uid'),
                'short_name'        =>trim(I('post.short_name')),
                'lead_name'         =>trim(I('post.lead_name'))?trim(I('post.lead_name')):$_SESSION['AdminUserMsg']['loginname'],
                'build_time'        =>strtotime(I('post.build_time')),
                'province_id'       =>I('post.province_id'),
                'city_id'           =>I('post.city_id'),
                'county_id'         =>I('post.county_id'),
                'intro'             =>I('post.intro'),
                'san_score'         =>I('post.san_score'),
                //'is_verify'         =>1,
                //'member_num'        =>1
            );
            if(!empty($_FILES)){
                $config = array(
                    'maxSize' => 2097152,
                    'rootPath' => './Public/',
                    'savePath' => '/Uploads/emblem/',
                    'saveName' => array('uniqid',''),
                    'exts' => array('jpg', 'gif', 'png', 'jpeg'),
                    'autoSub' => true,
                    'subName' => array('date','Y-m-d'),
                );
                $upload = new \Think\Upload($config);// 实例化上传类
                $info = $upload->upload();
                if($info){
                    $dataArr['logo']=C('__UPLOADS_PATH__').$info['file-2']['savepath'].$info['file-2']['savename'];
                    $result=M('ball_team')->where('ball_team_id='.$id)->save($dataArr);
                }else{
                    $this->error($upload->getError());
                }
            }else{
                $result=M('ball_team')->where('ball_team_id='.$id)->save($dataArr);
            }
            
            if($result){
                echo "<div id='close' style='display:none'>1</div>";
            }
            
        }
        
        
        
        $this->assign('ball_team_id',$ball_team_id);
        $this->assign('provinceMsg',$province);
        $this->assign('ballTeamMsg',$ball_team_Msg);
        $this->display();
    }
    
    public function addperson(){
        $provinceMsg=M('province')->select();
        
        if(!empty($_POST)){
            $where="phone=".I('post.phone')." or nickname= '".I('post.nickname')."'";
            $result=M('user')->where($where)->find();
            if($result){
               echo '该号码或昵称已被注册';
               die(); 
            }
            
            $dataArr=array(
                'phone'     =>I('post.phone'),
                'nickname'     =>I('post.nickname'),
                'password'     =>md5(I('post.password')),
                'sex'     =>I('post.sex'),
                'email'     =>I('post.email'),
                'customary'     =>I('post.customary'),
                'goodAt'     =>I('post.goodAt'),
                'province_id'     =>I('post.province_id'),
                'city_id'     =>I('post.city_id'),
                'county_id'     =>I('post.county_id'),
                'intro'     =>I('post.intro'),
                'create_time'   =>time()
            );
            if(!empty($_FILES)){
                $config = array(
                    'maxSize' => 2097152,
                    'rootPath' => './Public/',
                    'savePath' => '/Uploads/users/',
                    'saveName' => array('uniqid',''),
                    'exts' => array('jpg', 'gif', 'png', 'jpeg'),
                    'autoSub' => true,
                    'subName' => array('date','Y-m-d'),
                );
                $upload = new \Think\Upload($config);// 实例化上传类
                $info = $upload->upload();
                if($info){
                    $dataArr['avatar']=C('__UPLOADS_PATH__').$info['file-2']['savepath'].$info['file-2']['savename'];
                    
                    $result=M('user')->add($dataArr);
                }else{
                    $result=M('user')->add($dataArr);
                }                
            }
            
            if($result){
                echo "<div id='close' style='display:none'>1</div>";
            }
        }
        
        $this->assign('provinceMsg',$provinceMsg);
        $this->display();
    }
    
    public function getCity(){
        $province_id=I('post.province_id');
        $result=M('city')->where('province_id='.$province_id)->select();
        $this->ajaxReturn($result);
    }
    
    public function getCounty(){
        $city_id=I('post.city_id');
        $result=M('county')->where('city_id='.$city_id)->select();
        $this->ajaxReturn($result);
    }
    
    public function addemblem(){
        $category=M('category')->select();
        $this->assign('category',$category);
        if(!empty($_POST)){
            $dataArr['title']=I('post.title');
            $dataArr['content']=I('post.content');
            $dataArr['create_time']=time();
            $dataArr['status']=1;
            $dataArr['category_id']=(int)I('post.category_id');
            if(!empty($_FILES)){
                $config = array(
                    'maxSize' => 2097152,
                    'rootPath' => './Public/',
                    'savePath' => '/Uploads/emblem/',
                    'saveName' => array('uniqid',''),
                    'exts' => array('jpg', 'gif', 'png', 'jpeg'),
                    'autoSub' => true,
                    'subName' => array('date','Y-m-d'),
                );
                $upload = new \Think\Upload($config);// 实例化上传类
                $info = $upload->upload();
                if($info){
                    $dataArr['logo']=C('__UPLOADS_PATH__').$info['file-2']['savepath'].$info['file-2']['savename'];
                    $result=M('emblem')->add($dataArr);
                    if($result){
                       echo "<div id='close'>添加成功</div>"; 
                    }
                }else{
                    $this->error($upload->getError());
                }
            } 
        }
        
        $this->display();
    }
    /**
     * 根据build_team_apply表进行球队申请查询
     * 输出查询结果
     * 
     */
    /* public function Applyteamlist(){
        $build_team_applyMsg=M('build_team_apply')->select();
        foreach($build_team_applyMsg as $key=>$val){
            $val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
            $val['city']=M('city')->where('city_id='.$val['city_id'])->find();
            $val['province']=M('province')->where('province_id='.$val['province_id'])->find();
            $val['county']=M('county')->where('county_id='.$val['county_id'])->find();
            $val['userMsg']=M('user')->where('user_id='.$val['user_id'])->find();
            $build_team_applyMsg[$key]=$val;
        } 
        
       
        $this->assign('countTeam',count($build_team_applyMsg));
        $this->assign('teamlist',$build_team_applyMsg);
        $this->display();
    } */
    
    public function Applyteamlist(){
        $build_team_applyMsg=M('ball_team')->where('is_verify=0')->select();

        foreach($build_team_applyMsg as $key=>$val){
            $val['create_time']=date('Y-m-d H:i:s',$val['create_time']);
            if($val['city_id']){
                $val['city_id']=M('city')->where('city_id='.$val['city_id'])->find();
            }
            if ($val['province_id']){
                $val['province_id']=M('province')->where('province_id='.$val['province_id'])->find();
            }
            if ($val['county_id']){
                $val['county_id']=M('county')->where('county_id='.$val['county_id'])->find();
            }
            
            $val['userMsg']=M('user')->where('user_id='.$val['uid'])->find();
            $build_team_applyMsg[$key]=$val;
        }
        

        $this->assign('countTeam',count($build_team_applyMsg));
        $this->assign('teamlist',$build_team_applyMsg);
        $this->display();
    }
        
    public function agreeApply_start(){
        $id=I('post.id');   
        $deviceuuid=I('post.deviceuuid');
        $ball_team_name=I('post.ball_team_name');
        $userid=M('user')->where(array('deviceuuid'=>$deviceuuid))->getField('user_id');


        if(!empty($_POST)){
            $title='恭喜！您申请的球队已成功创建...';
            /*************************mongo数据**************************************/
            $sendMsgArr['title']='【三联球战】';
            $sendMsgArr['content']=$title;
            $sendMsgArr['type']=3;//0订场 1排位赛 2活动 3球队管理 4系统消息
            $sendMsgArr['create_time']=time();
            $sendMsgArr['ext']=array(
                'msg_type'		=>3,//0订场 1排位赛 2活动 3球队管理 4系统消息
                'ball_team_id'	=>$id,
                'type'          =>0,//0球队审核通过
                'ball_team_name'=>$ball_team_name,
            );
            $sendMsgArr['target_user']=(int)$userid;
            $sendMsgArr['__v']=0;
            /*************************jpush数据**************************************/
            $data['sendno']			=time();
            $data['receiver_type']	=3;
            $data['receiver_value']	=$deviceuuid;
            $data['msg_type']		=1;

            $content['n_builder_id']=1;
            $content['n_title']		='【三联球战】';
            $content['n_content']	=$title;
            $content['n_extras']	=array(
                'msg_type'	=>3,
                'type'          =>0,
                'ball_team_id'	=>$id,
                'ball_team_name'=>$ball_team_name,
                'content'		=>$title,
            );
            /*************************************************************************/

            $JpushModel=new JpushModel();
            $result=$JpushModel->push($data,$content);
            $Model=new MessageModel();
            $res2=$Model->add($sendMsgArr);
            if($res2){
                $result=M('user')->where('user_id='.$userid)->setInc('ball_team_point');
                $buildMsg=M('ball_team')->where('ball_team_id='.$id)->save(array('is_verify'=>1));
                $this->ajaxReturn($buildMsg);
            }

        }


    }
    public function agreeApply_stop(){
        $ball_team_id=I('get.ball_team_id');
        $deviceuuid=I('get.deviceuuid');
        $userid=I('get.userid');

        $this->assign('userid',$userid);
        $this->assign('deviceuuid',$deviceuuid);
        $this->assign('ball_team_id',$ball_team_id);
        $this->display();
    }
    public function agree_stop(){
        $build_team_id=I('post.ball_team_id');
        $remark=I('post.remark');
        $userid=I('post.userid');
        $deviceuuid=I('post.deviceuuid');

        $result=M('ball_team')->where('ball_team_id='.$build_team_id)->delete();
        if($result){
            $title='对不起！您申请创建的球队被退回...';
            /*************************mongo数据**************************************/
            $sendMsgArr['title']='【三联球战】';
            $sendMsgArr['content']=$title;
            $sendMsgArr['type']=3;//0订场 1排位赛 2活动 3球队管理 4系统消息
            $sendMsgArr['create_time']=time();
            $sendMsgArr['ext']=array(
                'msg_type'		=>4,//0订场 1排位赛 2活动 3球队管理 4系统消息
                'ball_team_id'	=>'',
                'type'          =>0,//0球队审核通过
                'remark'        =>$remark,
            );
            $sendMsgArr['target_user']=(int)$userid;
            $sendMsgArr['__v']=0;

            $Model=new MessageModel();
            $result=$Model->add($sendMsgArr);

            if($result){
                $id=json_decode(json_encode($result),1)['$id'];
                /*************************jpush数据**************************************/
                $data['sendno']			=time();
                $data['receiver_type']	=3;
                $data['receiver_value']	=$deviceuuid;
                $data['msg_type']		=1;

                $content['n_builder_id']=1;
                $content['n_title']		='【三联球战】';
                $content['n_content']	=$title;
                $content['n_extras']	=array(
                    'msg_type'	=>3,
                    'type'          =>0,
                    '_id'           =>$id,
                    'remark'		=>$remark,
                );
                $JpushModel=new JpushModel();
                $res=$JpushModel->push($data,$content);
                //var_dump($res);
                //if($res){
                    echo "<div id='close' style='display:none'>1</div>";
                    $this->display('ActivityTeam/agreeApply_stop');
               // }
            }
        }
    }
}