<?php
namespace Admin\Controller;
use Think\Controller;
class CommonController extends Controller {
	
     public function _initialize(){
      if (!session('?admin_key_id')){
				$this->redirect('Login/index');
			}
    }
}