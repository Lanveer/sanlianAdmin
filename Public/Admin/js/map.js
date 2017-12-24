/**get map lat lng
 * 
 */

function initMap(){
  createMap();//创建地图
  setMapEvent();//设置地图事件
  addMapControl();//向地图添加控件
  addMapOverlay();//向地图添加覆盖物
}
function createMap(){ 
  map = new BMap.Map("map"); 
  map.centerAndZoom(new BMap.Point(104.1438,30.56745),13);
  map.addEventListener("click",function(e){
		//alert(e.point.lng + "," + e.point.lat);
		console.info(e.point.lng + "," + e.point.lat);
        map.clearOverlays();    //清除地图上所有覆盖物
        map.addOverlay(new BMap.Marker(e.point));    //添加标注
		$("input[name='lng']").val(e.point.lng);
		$("input[name='lat']").val(e.point.lat);
	});
}
function setMapEvent(){
  map.enableScrollWheelZoom();
  map.enableKeyboard();
  map.enableDragging();
  map.enableDoubleClickZoom()
}
function addClickHandler(target,window){
  target.addEventListener("click",function(e){
    _blank.openInfoWindow(window);   
  });
  
}


function addMapOverlay(){
  var lng = $("input[name='lng']").val();
  var lat = $("input[name='lat']").val();
  var zoom = map.getZoom();
  var point = new BMap.Point(lng,lat);
  console.log(point);
  map.centerAndZoom(point, zoom);
  map.addOverlay(new BMap.Marker(point));
}
//向地图添加控件
function addMapControl(){
  var scaleControl = new BMap.ScaleControl({anchor:BMAP_ANCHOR_BOTTOM_LEFT});
  scaleControl.setUnit(BMAP_UNIT_IMPERIAL);
  map.addControl(scaleControl);
  var navControl = new BMap.NavigationControl({anchor:BMAP_ANCHOR_TOP_LEFT,type:BMAP_NAVIGATION_CONTROL_LARGE});
  map.addControl(navControl);
  var overviewControl = new BMap.OverviewMapControl({anchor:BMAP_ANCHOR_BOTTOM_RIGHT,isOpen:true});
  map.addControl(overviewControl);
}
var map;
  initMap();