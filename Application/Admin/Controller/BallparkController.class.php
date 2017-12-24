<?php
namespace Admin\Controller;
use Think\Controller;
class BallparkController extends CommonController{
    /**
     * 球场列表
     */
    public function ballparklist(){
        if(session('admin_key_auth')==3){
            $courtMsg=M('court')->where('venue_id='.session('admin_key_venue_id'))->select();
        }else{
            $courtMsg=M('court')->select();
        }
        
        foreach ($courtMsg as $key=>$val){
            $val['ball_park_name']=M('venue')->where('venue_id='.$val['venue_id'])->getField('name');
            $courtMsg[$key]=$val;
        }
        
        $this->assign('countCourt',count($courtMsg));
        $this->assign('courtMsg',$courtMsg);
        $this->display();
    }
    
    public function addCourt(){
        if(session('admin_key_auth')==3){
            $venueMsg=M('venue')->where('venue_id='.session('admin_key_venue_id'))->select();
        }else{
            $venueMsg=M('venue')->select();
        }
        
        if(!empty($_POST)){
            $dataArr['name']        = I('post.name');            
            $dataArr['phone']       = I('post.phone');
            $dataArr['venue_id']    = I('post.venue_id');
            $dataArr['is_freeze']   = I('post.is_freeze');
            $dataArr['type']        = I('post.type');
            $dataArr['is_vip']      = I('post.is_vip');
            $dataArr['is_discount'] = I('post.is_discount');
            $dataArr['is_referee']  = I('post.is_referee');
            $dataArr['sort']        = I('post.sort')?I('post.sort'):0;
            $dataArr['time_limit']  = strtotime(I('post.time_limit'))?strtotime(I('post.time_limit')):time();
            $dataArr['address']     = I('post.address');
            $dataArr['intro']       = I('post.intro');
            $dataArr['lat']         = I('post.lat');
            $dataArr['lng']         = I('post.lng');            
            $dataArr['discount_tag']= i('post.discount_tag');
            if(!empty($_FILES)){
                $config = array(
                    'maxSize' => 2097152,
                    'rootPath' => './Public/',
                    'savePath' => '/Uploads/court/',
                    'saveName' => array('uniqid',''),
                    'exts' => array('jpg', 'gif', 'png', 'jpeg'),
                    'autoSub' => true,
                    'subName' => array('date','Y-m-d'),
                );
                $upload = new \Think\Upload($config);// 实例化上传类
                $info = $upload->upload();
                if($info){
                    $dataArr['image_thumb']=C('__UPLOADS_PATH__').$info['file-2']['savepath'].$info['file-2']['savename'];
                    $dataArr['image']      = json_encode(array($dataArr['image_thumb']));
                    
                    $result=M('court')->add($dataArr);
                }else{
                    $this->error($upload->getError());
                }
            }else{
                $result=M('court')->add($dataArr);
            }
            if($result){
                echo "<div id='close' style='display:none'>1</div>";
            }
        }
        
        $this->assign('venueMsg',$venueMsg);
        $this->display();
    }
    public function addTimes(){
        $court_id=I('court_id');
        $courtMsg=M('court')->where('court_id='.$court_id)->getField('name');
        
        if(!empty($_POST)){            
            $time=date('Y').'-'.date('m').'-'.date('d').' 00:00:00';//当天整零点时间戳            
            $time=strtotime($time);
            $dataArr['court_id']=I('post.court_id');
            //$dataArr['start_time']          =$time-strtotime(I('post.start_time'));
            //$dataArr['end_time']            =$time-strtotime(I('post.end_time'));
            $dataArr['start_time']          =strtotime(I('post.start_time'))-$time;
            $dataArr['end_time']            =strtotime(I('post.end_time'))-$time;
            $dataArr['price']               =I('post.price');
            $dataArr['discount_start_time'] =strtotime(I('post.discount_start_time'));
            $dataArr['discount_end_time']   =strtotime(I('post.discount_end_time'));
            $dataArr['discount_price']      =I('post.discount_price');
            $dataArr['is_open']             =I('post.is_open');

            
            $result=M('once')->add($dataArr);
            if($result){
                echo "<div style='display:none' id='close'>1</div>";
            }else{
                echo "<div style='display:none' id='close'>2</div>";
            }
        }
        
        
        $this->assign('court_id',$court_id);
        $this->assign('courtMsg',$courtMsg);
        $this->display();
    }
    
    public function timelist(){
        $court_id=I('get.court_id');
        $time=date('Y').'-'.date('m').'-'.date('d').' 00:00:00';//当天整零点时间戳
        $time=strtotime($time);
        $onceMsg=M('once')->where('court_id='.$court_id)->select();

        foreach($onceMsg as $key=>$val){
            $val['datestr']='';
            $val['courtName']=M('court')->where('court_id='.$val['court_id'])->getField('name');
            $val['start_time']=date('H:i:s',$val['start_time']+$time);
            $val['end_time']=date('H:i:s',$val['end_time']+$time);
            $val['discount_start_time']=date('Y-m-d H:i:s',$val['discount_start_time']);
            $val['discount_end_time']=date('Y-m-d H:i:s',$val['discount_end_time']);
            $val['close_once']=M('close_once')->field('once_id,time')->where('once_id='.$val['once_id'])->group('time')->select();
            if($val['close_once']==''){
                $val['close_once']=date('Y-m-d',time()+3600*24);
            }else{
                //$val['close_once']=date('Y-m-d',$val['close_once']);
                foreach($val['close_once'] as $k=>$v){
                    $v['time']=date('Y-m-d',$v['time']);
                    $val['datestr'] .=$v['time'].',';
                    $val['close_once'][$k]=$v;
                }

            }
            $val['datestr']=substr($val['datestr'],0,strlen($val['datestr'])-1);
            $onceMsg[$key]=$val;
        }

        $this->assign('court_id',$court_id);
        $this->assign('countOnceMsg',count($onceMsg));
        $this->assign('onceMsg',$onceMsg);
        $this->display();
    }
    public function edit_times(){
        $time=date('Y').'-'.date('m').'-'.date('d').' 00:00:00';//当天整零点时间戳
        $time=strtotime($time);

        $once_id=I('once_id');
        $onceMsg=M('once')->where('once_id='.$once_id)->find();
        $onceMsg['courtName']=M('court')->where('court_id='.$onceMsg['court_id'])->getField('name');
        $onceMsg['start_time'] = date("Y-m-d H:i",($onceMsg['start_time']+$time));
        $onceMsg['end_time'] = date("Y-m-d H:i",($onceMsg['end_time']+$time));
        $onceMsg['discount_start_time'] = date("Y-m-d H:i",$onceMsg['discount_start_time']);
        $onceMsg['discount_end_time'] = date("Y-m-d H:i",$onceMsg['discount_end_time']);
        
        if(!empty($_POST)){
            $once_id=I('post.once_id');
            $dataArr['start_time']          =strtotime(I('post.start_time'))-$time;
            $dataArr['end_time']            =strtotime(I('post.end_time'))-$time;
            $dataArr['price']               =I('post.price');
            $dataArr['discount_start_time'] =strtotime(I('post.discount_start_time'));
            $dataArr['discount_end_time']   =strtotime(I('post.discount_end_time'));
            $dataArr['discount_price']      =I('post.discount_price');
            $dataArr['is_open']             =I('post.is_open');
            
            $res=M('once')->where('once_id='.$once_id)->save($dataArr);
            if($res){
                echo "<div style='display:none' id='close'>1</div>";
            }else{
                echo "<div style='display:none' id='close'>2</div>";
            }
        }
        
        $this->assign('onceMsg',$onceMsg);
        $this->display();
    }
    
    public function timePrice_stop(){
        $once_id=I('post.id');
        $result=M('once')->where('once_id='.$once_id)->save(array('is_open'=>0));
        $this->ajaxReturn($result);
    }
    public function timePrice_start(){
        $once_id=I('post.id');
        $result=M('once')->where('once_id='.$once_id)->save(array('is_open'=>1));
        $this->ajaxReturn($result);
    }
    public function timePrice_del(){
        $once_id=I('post.id');
        $result=M('once')->where('once_id='.$once_id)->delete();
        $this->ajaxReturn($result);
    }
    public function editBallpark(){
        if(session('admin_key_auth')==3){
            $venueMsg=M('venue')->where('venue_id='.session('admin_key_venue_id'))->select();
        }else{
            $venueMsg=M('venue')->select();
        }
        $court_id=I('get.court_id');
        $ballparkMsg=M('court')->where('court_id='.$court_id)->find();
        
        if(!empty($_POST)){
        $dataArr['name']        = I('post.name');            
            $dataArr['phone']       = I('post.phone');
            $dataArr['venue_id']    = I('post.venue_id');
            $dataArr['is_freeze']   = I('post.is_freeze');
            $dataArr['type']        = I('post.type');
            $dataArr['is_vip']      = I('post.is_vip');
            $dataArr['is_discount'] = I('post.is_discount');
            $dataArr['is_referee']  = I('post.is_referee');
            $dataArr['sort']        = I('post.sort');
            $dataArr['time_limit']  = strtotime(I('post.time_limit'))?strtotime(I('post.time_limit')):time();
            $dataArr['address']     = I('post.address');
            $dataArr['intro']       = I('post.intro');
            $dataArr['lat']         = I('post.lat');
            $dataArr['lng']         = I('post.lng');
            //$dataArr['bus_line']    = I('post.bus_line');
            $dataArr['discount_tag']= i('post.discount_tag');
            
            if(!empty($_FILES['file-2']['size'])){
                $config = array(
                    'maxSize' => 2097152,
                    'rootPath' => './Public/',
                    'savePath' => '/Uploads/court/',
                    'saveName' => array('uniqid',''),
                    'exts' => array('jpg', 'gif', 'png', 'jpeg'),
                    'autoSub' => true,
                    'subName' => array('date','Y-m-d'),
                );
                $upload = new \Think\Upload($config);// 实例化上传类
                $info = $upload->upload();
                if($info){
                    $dataArr['image_thumb']=C('__UPLOADS_PATH__').$info['file-2']['savepath'].$info['file-2']['savename'];
                    $dataArr['image']      = json_encode(array($dataArr['image_thumb']));
                    $result=M('court')->where('court_id='.I('get.court_id'))->save($dataArr);
                }else{
                    $this->error($upload->getError());
                }
            }else{
                $result=M('court')->where('court_id='.I('get.court_id'))->save($dataArr);
            }
            if($result){
                echo "<div id='close' style='display:none'>1</div>";
            }else{
                echo "<div id='close' style='display:none'>2</div>";
            }
        
        }
        
        
        
        $this->assign('ballparkMsg',$ballparkMsg);
        $this->assign('venueMsg1',$venueMsg);
        $this->display();
    }
    
    public function arenalist(){
        if(session('admin_key_auth')==3){
            $venueMsg=M('venue')->where('venue_id='.session('admin_key_venue_id'))->order('is_sanlian')->select();
        }else{
            $venueMsg=M('venue')->order('is_sanlian')->select();
        }
        
        foreach($venueMsg as $key=>$val){
            if($val['end_time']==0){
                $val['end_time']='无';
            }else{
                $val['end_time']=date('Y-m-d H:i:s',$val['end_time']);
            }
            
            $venueMsg[$key]=$val;
        }
        
        $this->assign('venueMsg',$venueMsg);
        $this->assign('countVenueMsg',count($venueMsg));
        $this->display();
    }
    
    public function arena_stop(){
        $venue_id=I('post.id');
        $savedata['is_freeze']=1;
        $result=M('venue')->where('venue_id='.$venue_id)->save($savedata);
        $this->ajaxReturn($result);
    }
    
    public function arena_start(){
        $venue_id=I('post.id');
        $savedata['is_freeze']=0;
        $result=M('venue')->where('venue_id='.$venue_id)->save($savedata);
        $this->ajaxReturn($result);
    }
    
    public function addArena(){
        if(!empty($_POST)){
            $venuArr['name']        =I('post.name');
            $venuArr['address']     =I('post.address');
            $venuArr['lat']         =I('post.lat');
            $venuArr['lng']         =I('post.lng');
            $venuArr['phone']       =I('post.phone');
            $venuArr['intro']       =I('post.intro');
            $venuArr['is_freeze']   =I('post.is_freeze');
            $venuArr['end_time']    =strtotime(I('post.end_time'))?strtotime(I('post.end_time')):0;
            $venuArr['is_sanlian']  =I('post.is_sanlian');
            $venuArr['is_referee']  =I('post.is_referee');
            $venuArr['is_park']     =I('post.is_park');
            $venuArr['is_rest']     =I('post.is_rest');
            $venuArr['is_camera']   =I('post.is_camera');
            $venuArr['bus_line']    = I('post.bus_line');
            if(!empty($_FILES)){
                $config = array(
                    'maxSize' => 2097152,
                    'rootPath' => './Public/',
                    'savePath' => '/Uploads/venue/',
                    'saveName' => array('uniqid',''),
                    'exts' => array('jpg', 'gif', 'png', 'jpeg'),
                    'autoSub' => true,
                    'subName' => array('date','Y-m-d'),
                );
                $upload = new \Think\Upload($config);// 实例化上传类
                $info = $upload->upload();
                if($info){
                    $venuArr['image_thumb']=C('__UPLOADS_PATH__').$info['file-2']['savepath'].$info['file-2']['savename'];
                    $result=M('venue')->add($venuArr);
                }else{
                    $this->error($upload->getError());
                }
            }else{
                $result=M('venue')->add($venuArr);
            }
            
            
            if($result){
                echo "<div id='close' style='display:none'>1</div>";
            }else{
                echo "<div id='close' style='display:none'>2</div>";
            }
        }
        
        
        $this->display();
    }
    
    public function editArena(){
        $venue_id=I('get.venue_id');
        $getVenueMsg=M('venue')->where('venue_id='.$venue_id)->find();
        
        if(!empty($_POST)){
            $venue_id               = I('post.venue_id');
            $venuArr['name']        = I('post.name');
            $venuArr['address']     = I('post.address');
            $venuArr['lat']         = I('post.lat');
            $venuArr['lng']         = I('post.lng');
            $venuArr['phone']       = I('post.phone');
            $venuArr['intro']       = I('post.intro');
            $venuArr['is_freeze']   = I('post.is_freeze');
            $venuArr['end_time']    = strtotime(I('post.end_time'))?strtotime(I('post.end_time')):0;
            $venuArr['is_sanlian']  = I('post.is_sanlian');
            $venuArr['is_referee']  = I('post.is_referee');
            $venuArr['is_park']     = I('post.is_park');
            $venuArr['is_rest']     = I('post.is_rest');
            $venuArr['is_camera']   = I('post.is_camera');
            $venuArr['bus_line']    = I('post.bus_line');
            if(!empty($_FILES['file-2']['size'])){
                $config = array(
                    'maxSize' => 2097152,
                    'rootPath' => './Public/',
                    'savePath' => '/Uploads/venue/',
                    'saveName' => array('uniqid',''),
                    'exts' => array('jpg', 'gif', 'png', 'jpeg'),
                    'autoSub' => true,
                    'subName' => array('date','Y-m-d'),
                );
                $upload = new \Think\Upload($config);// 实例化上传类
                $info = $upload->upload();
                if($info){
                    $venuArr['image_thumb']=C('__UPLOADS_PATH__').$info['file-2']['savepath'].$info['file-2']['savename'];
                    $result=M('venue')->where('venue_id='.$venue_id)->save($venuArr);
                }else{
                    $this->error($upload->getError());
                }
            }else{
                $result=M('venue')->where('venue_id='.$venue_id)->save($venuArr);
            }
            
            
            if($result){
                echo "<div id='close' style='display:none'>1</div>";
            }else{
                echo "<div id='close' style='display:none'>2</div>";
            }
        }
        
        $this->assign('getVenueMsg',$getVenueMsg);
        $this->display();
    }
    
    /***************************************************************************/
    public function picturelist(){
        $court_id=I('get.court_id');
        $courtPic=M('court')->where('court_id='.$court_id)->getField('image');
        $courtPic=json_decode($courtPic,true);

        //print_r($courtPic);
        $this->assign('court_id',$court_id);
        $this->assign('countUrl',count($courtPic));
        $this->assign('urlArr',$courtPic);
        $this->display();
    }
    public function pic_del(){
        $urljsonArr=json_encode(I('post.urljsonArr'));
        $court_id=I('post.court_id');
        $result=M('court')->where('court_id='.$court_id)->save(array('image'=>$urljsonArr));

        $this->ajaxReturn($result);
    }
    public function pic_add(){
        $court_id=I('court_id');

        if(!empty($_POST)){
            $court_id=I('post.court_id');
            if($_FILES){
                $config = array(
                    'maxSize' => 2097152,
                    'rootPath' => './Public/',
                    'savePath' => '/Uploads/court/',
                    'saveName' => array('uniqid',''),
                    'exts' => array('jpg', 'gif', 'png', 'jpeg'),
                    'autoSub' => true,
                    'subName' => array('date','Y-m-d'),
                );
                $upload = new \Think\Upload($config);// 实例化上传类 style='display: none ["http:\/\/127.0.0.1\/T_ball\/Public\/Uploads\/court\/2016-08-18\/57b52ac576c19.jpg","http:\/\/121.40.101.191\/admin\/files\/2016\/05\/09\/572ff56862b65.jpg?size=580x429","http:\/\/121.40.101.191\/admin\/files\/2016\/05\/10\/5731413cc91a6.jpg?size=568x800","http:\/\/121.40.101.191\/admin\/files\/2016\/05\/10\/5731413cc91a6.jpg?size=568x800","http:\/\/121.40.101.191\/admin\/files\/2016\/05\/10\/5731413cc91a6.jpg?size=568x800","http:\/\/121.40.101.191\/admin\/files\/2016\/05\/10\/5731413cc91a6.jpg?size=568x800","http:\/\/127.0.0.1\/T_ball\/Public\/Uploads\/court\/2016-08-18\/57b52ac576c19.jpg","http:\/\/127.0.0.1\/T_ball\/Public\/Uploads\/court\/2016-08-18\/57b52ac576c19.jpg","http:\/\/127.0.0.1\/T_ball\/Public\/Uploads\/court\/2016-08-18\/57b52ac576c19.jpg","http:\/\/127.0.0.1\/T_ball\/Public\/Uploads\/court\/2016-08-18\/57b52ac576c19.jpg","http:\/\/127.0.0.1\/T_ball\/Public\/Uploads\/court\/2016-08-18\/57b52ac576c19.jpg","http:\/\/127.0.0.1\/T_ball\/Public\/Uploads\/court\/2016-08-18\/57b52ac576c19.jpg","http:\/\/127.0.0.1\/T_ball\/Public\/Uploads\/court\/2016-08-18\/57b52ac576c19.jpg","http:\/\/127.0.0.1\/T_ball\/Public\/Uploads\/court\/2016-08-18\/57b52ac576c19.jpg","http:\/\/127.0.0.1\/T_ball\/Public\/Uploads\/court\/2016-08-18\/57b52ac576c19.jpg","http:\/\/127.0.0.1\/T_ball\/Public\/Uploads\/court\/2016-08-18\/57b52ac576c19.jpg","http:\/\/127.0.0.1\/T_ball\/Public\/Uploads\/court\/2016-08-18\/57b52ac576c19.jpg","http:\/\/127.0.0.1\/T_ball\/Public\/Uploads\/court\/2016-08-18\/57b52ac576c19.jpg","http:\/\/127.0.0.1\/T_ball\/Public\/Uploads\/court\/2016-08-18\/57b52ac576c19.jpg","http:\/\/127.0.0.1\/T_ball\/Public\/Uploads\/court\/2016-08-18\/57b52ac576c19.jpg"]
                $info = $upload->upload();
                if($info){
                    $image=C('__UPLOADS_PATH__').$info['file-2']['savepath'].$info['file-2']['savename'];
                    $imageStr=M('court')->where('court_id='.$court_id)->getField('image');
                    $iamgeArr=json_decode($imageStr,true);
                    $iamgeArr[]=$image;
                    $dataArr=json_encode($iamgeArr);

                    $result=M('court')->where('court_id='.$court_id)->save(array('image'=>$dataArr));
                    if($result){
                        echo "<div id='close' style='display: none'>1</div>";

                    }else{
                        echo "<div id='close' style='display: none'>2</div>";
                    }

                }else{
                    $this->error($upload->getError());
                }
            }
        }

        $this->assign('court_id',$court_id);
        $this->display();
    }


    public function breakpoint(){
        header("Access-Control-Allow-Origin:*");
        $once_id=I('post.once_id');
        $dateValue=I('post.dateValue');
        $dateArr=explode(',',$dateValue);

        $result=M('close_once')->where('once_id='.$once_id)->delete();

        foreach($dateArr as $key=>$val){
            $val=strtotime($val);
            $res=M('close_once')->add(array('once_id'=>$once_id,'time'=>$val,'create_time'=>time()));
        }
        if($res){
            $this->ajaxReturn(1);
        }else{
            $this->ajaxReturn(2);
        }
    }

    public function emptytimes(){
        header("Access-Control-Allow-Origin:*");
        $once_id=$_REQUEST['once_id'];
        $result=M('close_once')->where('once_id='.$once_id)->delete();

        $this->ajaxReturn($result);
    }

    /**
     *  球馆VIP设置页面
     */
    public function setVenueVip(){
        $id = $_REQUEST['id']|0;
        $venue = D("Venue")->where(['venue_id'=>$id])->find();
        $venue_vip = D("VenueVip")->where(['venue_id'=>$id])->select();
        $vips = D("Vip")->select();
        foreach($vips as $index=>$vip){
            $vips[$index]['discount'] = 1;
            foreach($venue_vip as $vi){
                if($vi['vip_id']==$vip['id']){
                    $vips[$index]['discount'] = $vi['discount'];
                    break;
                }
            }
        }
        $this->assign("vips",$vips);
        $this->assign("venue",$venue);
        $this->assign("venue_vip",$venue_vip);
        $this->display();
    }

    /**
     *  球馆VIP设置
     */
    public function doSetVenueVip(){
        $venue_id = $_REQUEST['venue_id'];
        $venueVipModel = D("VenueVip");
        $vips = $_REQUEST['vips'];
        $venueVipModel->startTrans();
        try {
            $venueVipModel->where(['venue_id'=>$venue_id])->delete();
            foreach($vips as $vip_id => $discount){
                $discount = floatval($discount);
                if($discount>0&&$discount<=1){
                    $discount = round($discount,2);
                }else{
                    $discount=1;
                }
                $data = [
                    "venue_id" => $venue_id,
                    "vip_id" => $vip_id,
                    "discount" => $discount,
                    "create_time" => time()
                ];
                $venueVipModel->add($data);
            }
            $venueVipModel->commit();
            response();
        } catch (\Exception $e) {
            $venueVipModel->rollback();
            response([],$e->getMessage(),500);
        }
    }

    /**
     *  球馆裁判组设置页面
     */
    public function setVenueReferee(){
        $venue_id = $_REQUEST['id']|0;
        $venue = D("Venue")->where(['venue_id'=>$venue_id])->find();
        $venueRefereeModel = D("VenueReferee");
        $refereeModel = D("Referee");
        $venue_referee = $venueRefereeModel->where(['venue_id'=>$venue_id])->select();
        $referees = $refereeModel->where(['is_show'=>1])->select();
        foreach($referees as $index=>$referee) {
            $referees[$index]['checked'] = 0;
            foreach($venue_referee as $vr){
                if($referee['id']==$vr['referee_id']){
                    $referees[$index]['checked'] = 1;
                    break;
                }
            }
        }
        $this->assign("venue",$venue);
        $this->assign("referees",$referees);
        $this->display();
    }

    /**
     *  球馆裁判组设置
     */
    public function doSetVenueReferee(){
        $referee = $_REQUEST['referee'];
        $venue_id = $_REQUEST['venue_id']|0;
        $venueRefereeModel = D("VenueReferee");
        if(empty($referee)){
            response();
        }
        $venueRefereeModel->startTrans();
        try {
            $venueRefereeModel->where(['venue_id'=>$venue_id])->delete();
            foreach($referee as $referee_id => $check){
                $data = [
                    "venue_id" => $venue_id,
                    "referee_id" => $referee_id,
                    "create_time" => time()
                ];
                $venueRefereeModel->add($data);
            }
            $venueRefereeModel->commit();
            response();
        } catch (\Exception $e) {
            $venueRefereeModel->rollback();
            response([],$e->getMessage(),500);
        }
    }

    public function userList(){
        $this->display();
    }

    public function getUserList(){
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

        $ext = [];

        if(session('admin_key_auth')==3){
            $venue_id = session('admin_key_venue_id')|0;
            $courtIds = D("Court")->where(['venue_id'=>$venue_id])->getField('court_id',true);
//            var_dump($courtIds);
            if(empty($courtIds)){
                $courtIds = [0];
            }
            $query['court_id'] = array("in",$courtIds);
            $ballTeam = D("qualifying")->where($query)->getField("home_team_id",true);
            if(empty($ballTeam)){
                $ballTeam = [0];
            }
            $query = [
                "ball_team_id" => array("in",$ballTeam)
            ];
            $userIds = D("BallTeamMember")->where($query)->getField("uid",true);
            if(empty($userIds)){
                $userIds = [0];
            }
            $ext["user_id"] = array("in",$userIds);
        }

        $data = $userModel->search($queryParams,$ext);
        formatRes($data,$_REQUEST['draw']);
//        var_dump($data);
        response($data);
    }
    
}