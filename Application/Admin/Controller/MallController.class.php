<?php
/**
 * Created by PhpStorm.
 * User: walter
 * Date: 2017/1/9
 * Time: 10:35
 */
namespace Admin\Controller;

use Common\Model\CommodityBannerModel;
use Common\Model\CommodityModel;
use Common\Model\CommodityOrderModel;

/**
 * Class MallController
 * @package Admin\Controller
 * @property CommodityModel $commodityModel
 * @property CommodityBannerModel $commodityBannerModel
 * @property CommodityOrderModel $commodityOrderModel
 */
class MallController extends CommonController{
    private $commodityModel;
    private $commodityOrderModel;
    private $commodityBannerModel;

    public function _initialize(){
        parent::_initialize();
        $this->commodityModel = D("Commodity");
        $this->commodityOrderModel = D("CommodityOrder");
        $this->commodityBannerModel = D("CommodityBanner");
    }

    /**
     *  商品列表
     */
    public function commodityList(){
        $this->display();
    }

    /**
     *  获取商品列表
     */
    public function getCommodityList(){
        $queryParams = [
            "limit" => $_REQUEST['limit'],
            "offset" => $_REQUEST['offset'],
            "sort" => $_REQUEST['sort'],
            "order" => $_REQUEST['order'],
            "start_time" => $_REQUEST['start_time'],
            "end_time" => $_REQUEST['end_time'],
        ];


        $option = intval($_REQUEST['option']);
        $search = $_REQUEST['search'];
        if(!empty($search)||$search==0){
            switch($option){
                case 0:             //标题
                    $queryParams['commodity_id'] = $search;
                    break;
                case 1:
                    $queryParams['title'] = trim($search);
                    break;
            }
        }

        $data = $this->commodityModel->search($queryParams);
        formatRes($data,$_REQUEST['draw']);
//        var_dump($data);
        response($data);
    }

    /**
     *  商品详情
     */
    public function commodityInfo(){
        $id = $_REQUEST['id'];
        $commodity = $this->commodityModel->getById($id);
        $this->assign("commodity",$commodity);
        $this->display();
    }

    /**
     *  新增商品页面
     */
    public function addCommodity(){
        $this->display();
    }

    /**
     *  新增商品
     */
    public function doAddCommodity(){
        $data = [
            "title"=>$_REQUEST['title'],
            "hot_point"=>$_REQUEST['hot_point'],
            "reference_price"=>$_REQUEST['reference_price'],
            "type"=>$_REQUEST['type'],
            "is_deliver"=>$_REQUEST['is_deliver'],
            "description" => $_REQUEST['description'],
            "number" => $_REQUEST['number'],
            "status" => $_REQUEST['status'],
        ];
        if($data['number']<0){
            $data['number'] = -1;
        }
        if(!empty($_REQUEST['img'])){
            $data['img'] = implode(',',$_REQUEST['img']);
        }else{
            response([],"图片至少一张",400);
        }
        try {
            $files = uploadFile('commodity');
            $data['banner'] = $files['banner']['src'];
            $this->commodityModel->addCommodity($data);
            response();
        } catch (\Exception $e) {
            response([],$e->getMessage(),500);
        }
    }

    /**
     *  修改商品页面
     */
    public function editCommodity(){
        $id = $_REQUEST['id'];
        $commodity = $this->commodityModel->getById($id);
        $this->assign("commodity",$commodity);
        $this->display();
    }

    /**
     *  修改商品
     */
    public function doEditCommodity(){
        $data = [
            "id" => $_REQUEST['id'],
            "title"=>$_REQUEST['title'],
            "hot_point"=>$_REQUEST['hot_point'],
            "reference_price"=>$_REQUEST['reference_price'],
            "type"=>$_REQUEST['type'],
            "is_deliver"=>$_REQUEST['is_deliver'],
            "description" => $_REQUEST['description'],
            "number" => $_REQUEST['number'],
            "status" => $_REQUEST['status'],
        ];
        if($data['number']<0){
            $data['number'] = -1;
        }
        if(!empty($_REQUEST['img'])){
            $data['img'] = implode(',',$_REQUEST['img']);
        }else{
            response([],"图片至少一张",400);
        }
        try {
            if(!empty($_FILES['banner'])){
                $files = uploadFile('commodity');
                $data['banner'] = $files['banner']['src'];
            }
            $this->commodityModel->updateCommodity($data);
            response();
        } catch (\Exception $e) {
            response([],$e->getMessage(),500);
        }
    }

    /**
     *  删除商品
     */
    public function delCommodity(){
        $id = $_REQUEST['id'];

        try {
            $result = $this->commodityModel->delById($id);
            response();
        } catch (\Exception $e) {
            response([],$e->getMessage(),500);
        }
    }

    /**
     *  商城Banner页面
     */
    public function commodityBannerList(){
        $this->display();
    }

    /**
     *  获取商城Banner列表
     */
    public function getCommodityBannerList(){
        $queryParams = [
            "limit" => $_REQUEST['limit'],
            "offset" => $_REQUEST['offset'],
            "sort" => $_REQUEST['sort'],
            "order" => $_REQUEST['order'],
            "start_time" => $_REQUEST['start_time'],
            "end_time" => $_REQUEST['end_time'],
        ];


        $option = intval($_REQUEST['option']);
        $search = $_REQUEST['search'];
        if(!empty($search)||$search==0){
            switch($option){
                case 0:             //标题
                    $queryParams['banner_id'] = $search;
                    break;

            }
        }

        $data = $this->commodityBannerModel->search($queryParams);
        formatRes($data,$_REQUEST['draw']);
//        var_dump($data);
        response($data);
    }

    /**
     *  新增Banner
     */
    public function addBanner(){
        $this->display();
    }

    public function doAddBanner(){
        $type = $_REQUEST['type']|0;
        $data = [
            "type" => $type,
            "status" => $_REQUEST['status'],
            "html" => "",
        ];
        switch($type){
            case 0:
                if(empty($_REQUEST['url'])){
                  response([],"链接地址必须",400);
                }
                $data['url'] = $_REQUEST['url'];
                break;
            case 1:
                if(empty($_REQUEST['commodity_id'])){
                    response([],"商品必须",400);
                }
                $data['commodity_id'] = $_REQUEST['commodity_id'];
                break;
            case 2:
                if(empty($_REQUEST['html'])){
                    response([],"富文本必须",400);
                }
                $data['html'] = $_REQUEST['html'];
                break;
            default:
                response([],"未知类型",400);
                break;
        }
        if(empty($_FILES['img'])){
            response([],"Banner必须",400);
        }
        try {
            $files = uploadImg('banner');
            $data['img'] = $files['img']['src'];
            $result = $this->commodityBannerModel->addCommodityBanner($data);
            response();
        } catch (\Exception $e) {
            response([],$e->getMessage(),500);
        }
    }

    /**
     *  搜索商品
     */
    public function searchCommodity(){
        $search = trim($_REQUEST['search']);
        $condition['title'] = array("like","%".$search."%");
        $data = $this->commodityModel->where($condition)->select();
        response($data);
    }

    /**
     *  修改Banner页面
     */
    public function editCommodityBanner(){
        $id = $_REQUEST['id'];
        $commodityBanner = $this->commodityBannerModel->getById($id);
        $this->assign("banner",$commodityBanner);
        $this->display("editBanner");
    }

    /**
     *  修改Banner
     */
    public function doEditCommodityBanner(){
        $type = $_REQUEST['type']|0;
        $data = [
            "id" => $_REQUEST['id'],
            "type" => $type,
            "status" => $_REQUEST['status'],
        ];
        switch($type){
            case 0:
                if(empty($_REQUEST['url'])){
                    response([],"链接地址必须",400);
                }
                $data['url'] = $_REQUEST['url'];
                break;
            case 1:
                if(empty($_REQUEST['commodity_id'])){
                    response([],"商品必须",400);
                }
                $data['commodity_id'] = $_REQUEST['commodity_id'];
                break;
            case 2:
                if(empty($_REQUEST['html'])){
                    response([],"富文本必须",400);
                }
                $data['html'] = $_REQUEST['html'];
                break;
            default:
                response([],"未知类型",400);
                break;
        }

        try {
            if(!empty($_FILES['img'])){
                $files = uploadImg('banner');
                $data['img'] = $files['img']['src'];
            }
            $result = $this->commodityBannerModel->updateCommodityBanner($data);
            response();
        } catch (\Exception $e) {
            response([],$e->getMessage(),500);
        }
    }

    /**
     *  删除Banner
     */
    public function delBanner(){
        $id = $_REQUEST['id'];
        try {
            $result = $this->commodityBannerModel->delById($id);
            response();
        } catch (\Exception $e) {
            response([],$e->getMessage(),500);
        }
    }

    /**
     *  商品订单列表
     */
    public function commodityOrderList(){
        $this->display();
    }

    /**
     *  检索商品订单列表
     */
    public function getCommodityOrderList(){
        $queryParams = [
            "limit" => $_REQUEST['limit'],
            "offset" => $_REQUEST['offset'],
            "sort" => $_REQUEST['sort'],
            "order" => $_REQUEST['order'],
            "start_time" => $_REQUEST['start_time'],
            "end_time" => $_REQUEST['end_time'],
        ];


        $option = intval($_REQUEST['option']);
        $search = $_REQUEST['search'];
        if(!empty($search)||$search==0){
            switch($option){
                case 0:             //标题
                    $queryParams['banner_id'] = $search;
                    break;

            }
        }

        $data = $this->commodityOrderModel->search($queryParams);
        formatRes($data,$_REQUEST['draw']);
//        var_dump($data);
        response($data);
    }

    /**
     *  商品订单详情
     */
    public function commodityOrderInfo(){
        $id = $_REQUEST['id'];
        $data = $this->commodityOrderModel->getById($id);
        $this->assign("data",$data);
        $this->display();
    }

    /**
     *  修改商品订单页面
     */
    public function editCommodityOrder(){
        $id = $_REQUEST['id'];
        $data = $this->commodityOrderModel->getById($id);
        $this->assign("data",$data);
        $this->display();
    }

    /**
     *  修改商品订单
     */
    public function doEditCommodityOrder(){
        $data = [
            "id" => $_REQUEST['id'],
            "tracking_no" => $_REQUEST['tracking_no'],
            "status" => $_REQUEST['status']
        ];
        try {
            $this->commodityOrderModel->updateCommodityOrder($data);
            response();
        } catch (\Exception $e) {
            response([],$e->getMessage(),500);
        }
    }

    /**
     *  删除商品订单
     */
    public function delCommodityOrder(){
        $id = $_REQUEST['id'];
        try {
            $this->commodityOrderModel->delById($id);
            response();
        } catch (\Exception $e) {
            response([],$e->getMessage(),500);
        }
    }

}