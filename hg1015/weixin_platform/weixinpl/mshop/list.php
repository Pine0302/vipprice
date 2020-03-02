<?php
header("Content-type: text/html; charset=utf-8");
require('../config.php');
require('../common/utility.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
 
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
require('select_skin.php');
//头文件----start
require('../common/common_from.php');
//头文件----end
require('../proxy_info.php');
$new_baseurl = Protocol.$http_host; //新商城图片显示

//---------判断身份--------//-----2016-11-28--特权专区身份判断--qiao
//var_dump($_SESSION);
$user_level = -1;//-1:普通粉丝;1：推广员；2：青铜(代理)；3：白银(渠道)；4：黄金(总代)；5：白金(股东)
$promoter_id = -1;
$promoter_status = -1;
$promoter_is_consume = -1;

$customer_id = $_SESSION['customer_id'] ? : -1;
if(!empty($_SESSION['user_id_'.$customer_id])){
	$user_id = $_SESSION['user_id_'.$customer_id];
	//查询是否推广员，是否拥有股东身份
	$query = "SELECT id,status,is_consume FROM promoters WHERE isvalid=true AND customer_id=$customer_id AND user_id=$user_id LIMIT 1";
	$result= _mysql_query($query) or die('Query failed:24 ' . mysql_error());
	while( $row = mysql_fetch_object($result) ){
		$promoter_id = $row->id;
		$promoter_status = $row->status;
		$promoter_is_consume = $row->is_consume;
	}
}
if( $promoter_is_consume > 0 && $promoter_status == 1 && $promoter_id > 0 ){	//股东
	$user_level = $promoter_is_consume+1;
}elseif( $promoter_is_consume <= 0 && $promoter_status == 1 && $promoter_id > 0 ){	//普通推广员
	$user_level = 1;
}elseif( $promoter_id < 0 ){	//粉丝
	$user_level = -1;
}
//---------判断身份--------//
//echo $user_level;
$is_privilege_link = 0;
$privilege_link = "";
$query = "SELECT is_privilege_link,privilege_link FROM weixin_commonshops_extend WHERE isvalid=true AND customer_id=$customer_id LIMIT 1";
//echo $query;
$result = _mysql_query($query) or die('Query failed:47 ' . mysql_error());
while( $row = mysql_fetch_object($result)){
	$is_privilege_link = $row->is_privilege_link;
	$privilege_link = $row->privilege_link;
}


/******搜索内容******/
$search_data = '';

if(!empty($_GET["searchname"])){//商城首页传过来的搜索值
	$search_data = $configutil->splash_new($_GET["searchname"]);
}

if(!empty($_GET["search_data"])){
	$search_data = $configutil->splash_new($_GET["search_data"]);
}


/******搜索内容******/

$reload = 0;
if(!empty($_GET["reload"])){
	$reload = $configutil->splash_new($_GET["reload"]);
}


$search_from = 1;	//search_from:1全站搜索2供应商ID商店内搜索
if(!empty($_GET["search_from"])){
	$search_from = $configutil->splash_new($_GET["search_from"]);
    if($search_from=='undefined'){
        $search_from = 1;
    }
}

$supply_id = 0;
if(!empty($_GET["supply_id"])){	//供应商ID
	$supply_id = $configutil->splash_new($_GET["supply_id"]);
	if($reload == 0)
	{
		$search_from = 2; //假如有供应商ID带进来，默认会本店搜索
	}	
}

$typestr = "产品搜索";
$isnew = 0;
if(!empty($_GET["isnew"])){
    $isnew=$configutil->splash_new($_GET["isnew"]);
	$typestr="新品上市";
}
$ishot = 0;
if(!empty($_GET["ishot"])){
    $ishot=$configutil->splash_new($_GET["ishot"]);
	$typestr="热卖产品";
}
$isvp = 0;
if(!empty($_GET["isvp"])){
    $isvp=$configutil->splash_new($_GET["isvp"]);
	$typestr="VP产品";
}

$isscore = 0;
if(!empty($_GET["isscore"])){
    $isscore=$configutil->splash_new($_GET["isscore"]);
	$typestr="积分专区";
}

$isscore = 0;
if(!empty($_GET["isscore"])){
    $isscore=$configutil->splash_new($_GET["isscore"]);
	$typestr="积分专区";
}

$isqueue = 0;
if(!empty($_GET["isqueue"])){
    $isqueue=$configutil->splash_new($_GET["isqueue"]);
	$typestr="队列活动专区";

}

$exp_user_id = -1;
if( !empty($_GET['exp_user_id']) ){
	$exp_user_id = $configutil->splash_new($_GET["exp_user_id"]);
	$exp_user_id = passport_decrypt((string)$exp_user_id);

	if(!empty($isqueue)){
		$_SESSION['exp_user_id_' . $customer_id]=$exp_user_id;
	}
}

//是否立刻搜索
$s_n = -1;	//-2热门搜索分类,-1关键词搜索
if(!empty($_GET["s_n"])){
    $s_n=$configutil->splash_new($_GET["s_n"]);
}
$searchpage = -1;	//是否搜索页过来，1是
if(!empty($_GET["searchpage"])){

    $searchpage=$configutil->splash_new($_GET["searchpage"]);
}
$is_privilege_type = 0;
//查询分类是否属于特权分类
if(!empty($_GET["tid"])){
	$type_id =$configutil->splash_new($_GET["tid"]);
	$query = "SELECT is_privilege FROM weixin_commonshop_types WHERE isvalid=true AND customer_id=$customer_id AND id='$type_id'";
	$result= _mysql_query($query) or die ('query faild 529' .mysql_error());
	while( $row = mysql_fetch_object($result) ){
		$is_privilege_type = $row->is_privilege;
	}
	//把这个ID加入SESSION  class_page页面用
	$_SESSION['anchorid'] = $type_id;
}
//print_r($row);


//接收参数
$type_id = '';	//分类ID
if(!empty($_GET["type_id"])){
	$type_name = '';
    $type_id=$configutil->splash_new($_GET["type_id"]);
	$type_sql="select name from weixin_commonshop_types where isvalid=true and id=".$type_id." and customer_id=".$customer_id."";
	$result=_mysql_query($type_sql) or die ('query faild' .mysql_error());
		while($row=mysql_fetch_object($result)){
			$type_name=$row->name;
		}
}
$curCostMin = '';	//最低价
if(!empty($_GET["curCostMin"])){
    $curCostMin=$configutil->splash_new($_GET["curCostMin"]);
}
$curCostMax = '';	//最高价
if(!empty($_GET["curCostMax"])){
    $curCostMax=$configutil->splash_new($_GET["curCostMax"]);
}
$curScoreMin = '';	//最低积分
if(!empty($_GET["curScoreMin"])){
    $curScoreMin=$configutil->splash_new($_GET["curScoreMin"]);
}
$curScoreMax = '';	//最高积分
if(!empty($_GET["curScoreMax"])){
    $curScoreMax=$configutil->splash_new($_GET["curScoreMax"]);
}
$searchKey = '';	//关键词
if(!empty($_GET["searchKey"])){
    $searchKey=$configutil->splash_new($_GET["searchKey"]);
}
$op_sort = '';	//排序
if(!empty($_GET["op_sort"])){
    $op_sort=$configutil->splash_new($_GET["op_sort"]);
}
$pageNum = '';	//翻页数
if(!empty($_GET["pageNum"])){
    $pageNum=$configutil->splash_new($_GET["pageNum"]);
}


$tid = -1;
$sendstyle=-1;
$isOpenSales=0;
$isshowdiscount=0;
$define_share_image="";//分享图片
$shop_introduce="";//商城简介
if(!empty($_GET["tid"])){
    $tid=$configutil->splash_new($_GET["tid"]);
	if($tid>0){  //搜索分类选择的模板，优先级最高
		$query="select sendstyle from weixin_commonshop_types where isvalid=true and customer_id=".$customer_id." and  id=".$tid." limit 0,1";
		//echo $query;
		$result=_mysql_query($query) or die ('query faild' .mysql_error());
		while($row=mysql_fetch_object($result)){
			$sendstyle=$row->sendstyle;
		}

	}
}
//查找全局分类页模板,开启销量，显示折扣
$list_style="select list_type,isOpenSales,isshowdiscount,define_share_image,introduce from weixin_commonshops where isvalid=true and customer_id=".$customer_id." limit 0,1";
$list_result=_mysql_query($list_style) or die ('list_style faild' .mysql_error());
while($row=mysql_fetch_object($list_result)){
	$list_type=$row->list_type; //模板ID
	$isOpenSales=$row->isOpenSales;//显示销量
	$isshowdiscount=$row->isshowdiscount;//显示折扣
	$list_tempid=$list_type;
	$define_share_image = $row->define_share_image; ///分享图片
	$shop_introduce = $row->introduce; //商城介绍
	$shop_introduce=str_replace(PHP_EOL, '', $shop_introduce);//过滤换行
	$shop_introduce = str_replace(chr(10),'',$shop_introduce);
	$shop_introduce = str_replace(chr(13),'',$shop_introduce);
	$shop_introduce = str_replace("\\",'',$shop_introduce);

}
//分享默认图标
if(empty($define_share_image)){
    $logo = '';	//商城LOGO
	$query = "SELECT logo FROM weixin_commonshops_extend WHERE isvalid=true AND customer_id='$customer_id' LIMIT 1";
	$result= _mysql_query($query) or die('Query failed 64: ' . mysql_error());
	while( $row = mysql_fetch_object($result) ){
		$logo = $row->logo;
	}
	$define_share_image = $logo;
}

$is_division_show     =  0;//返现与购物币显示开关
$is_promoter_show     =  0;//只有推广员显示返现与购物币开关
$is_show_original      =  0;//显示原价开关
$sql = "select is_division,is_promoter,is_show_original from weixin_commonshops_extend where isvalid=true and customer_id=".$customer_id." limit 0,1";
$result1 = _mysql_query($sql) or die('Query failed: ' . mysql_error());
while ($row1 = mysql_fetch_object($result1)) {

		$is_division_show	= $row1->is_division;
		$is_promoter_show	= $row1->is_promoter;
		$is_show_original	= $row1->is_show_original;
}

/*判断是否显示购物币以及返现*/
 /*require('../common/own_data.php');
$info = new my_data();//own_data.php my_data类
$showAndCashback = $info->showCashback($customer_id,$user_id,-1,-1,-1);*/
/*判断是否显示购物币以及返现结束*/

if($define_share_image){
	$define_share_image=$new_baseurl."/".$define_share_image;
}
if($sendstyle>0){
	$list_tempid=$sendstyle; //模板ID
}
if($isOpenSales){
	$isOpenSales=1;
}
if($isshowdiscount){
	$isshowdiscount=1;
}
/*显示vp值 */
$isvp_switch = 0;
$query_vp = "select isvp_switch from weixin_commonshop_vp_bases where isvalid=true and customer_id=".$customer_id." limit 0,1";
$result_vp = _mysql_query($query_vp) or die('Query failed: ' . mysql_error());
while ($row_vp = mysql_fetch_object($result_vp)) {
	$isvp_switch = $row_vp->isvp_switch;
}
if($isvp_switch){
	$isvp_switch=1;
}
$brand_typeid=-1;
if(!empty($_GET["brand_typeid"])){ //品牌供应商的分类ID
	$brand_typeid = $configutil->splash_new($_GET["brand_typeid"]);
}
$tid=-1;
if(!empty($_GET["tid"])){ //分类页传过来的分类ID
	$tid = $configutil->splash_new($_GET["tid"]);
}
$placeholder="搜索";
if(0<$supply_id){
	$placeholder="搜索本店内宝贝";
}
//猜你喜欢，购物车以及产品详情传过来
//购物车: cartlike 商品详情：morelike
$like_op="";
$like_pid=-1;//产品ID
if(!empty($_GET["op"])){ //操作，购物车还是商品详情
	$like_op = $configutil->splash_new($_GET["op"]);
	$list_tempid=4;//猜你喜欢显示列表统一为模板4
}
if($isscore>0){
	$list_tempid=2;//积分专区使用模板2
}

if(!empty($_GET["pid"])){ //分类页传过来的分类ID
	$like_pid = $configutil->splash_new($_GET["pid"]);
}

$is_privilege = -1; 
if(!empty($_GET["is_privilege"])){ //分类页传过来的分类ID
	$is_privilege = $configutil->splash_new($_GET["is_privilege"]);
}

$page_type="list";// 作为底部菜单高亮的判断 list为列表页，class_page 为分类页



?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $typestr;?>列表</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta content="no" name="apple-touch-fullscreen">
    <meta name="MobileOptimized" content="320"/>
    <meta name="format-detection" content="telephone=no">
    <meta name=apple-mobile-web-app-capable content=yes>
    <meta name=apple-mobile-web-app-status-bar-style content=black>
    <meta http-equiv="pragma" content="nocache">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8">

    <link type="text/css" rel="stylesheet" href="./assets/css/amazeui.min.css" />
    <link type="text/css" rel="stylesheet" href="./css/order_css/global.css" />


    <link rel="stylesheet" id="wp-pagenavi-css" href="./css/list_css/pagenavi-css.css" type="text/css" media="all">
	<link rel="stylesheet" id="twentytwelve-style-css" href="./css/list_css/style.css" type="text/css" media="all">

	<link type="text/css" rel="stylesheet" href="./css/list_css/r_style.css" />
	<link type="text/css" rel="stylesheet" href="./css/vic.css" />
	<link type = "text/css" rel = "stylesheet" href = "./css/goods/search.css">
	<link type = "text/css" rel = "stylesheet" href = "./css/goods/global.css" />
	<link type = "text/css" rel = "stylesheet" href = "./css/goods/list_style<?php echo $list_tempid;?>.css" />

	<link type="text/css" rel="stylesheet" href="./css/css_<?php echo $skin ?>.css" />

	<script type="text/javascript" src="./assets/js/jquery.min.js"></script>


<style>
#m-type-area{z-index:100;top:49px;width: 100%;padding:0;}
#m-type-area .am-btn.cancel{margin-left:6px;background-color:white;color: #aaa;}
.search_top{line-height:30px;}
.am-btn-warning.am-radius{background-color: #F37B1D;border-color: #F37B1D;}
.list_mail{color: #F37B1D;
    font-size: 8px;
    background-color: #FFFFFF;
    padding: 1px 6px;
    border-radius: 4px;
    border: 1px solid #F37B1D;
    white-space: nowrap;}
 //ld 点击效果
        .button{
        	-webkit-transition-duration: 0.4s; /* Safari */
        	transition-duration: 0.4s;
        }

        .buttonclick:hover{
        	box-shadow:  0 0 5px 0 rgba(0,0,0,0.24);
        }

               .btn-shui{height: 14px; background: #fff;border:1px solid #ff7109; color: #ff7109;border-radius: 2px;font-size: 10px;padding: 0;}
	   .btn-shui div{font-size: 10px;}
	   .test5 {
		display: inline-block;
	    height:0;
	    width:20px;
	    color:#fff;
	    line-height: 0;
	    border-color:#ff7109 #fff transparent transparent;
	    border-style:solid solid dashed dashed;
	    border-width:15px 4px 0 0 ;
		}
      .test5 span{display: block;margin-top: -7px;color: #fff;font-weight: bold;font-size: 10px;}
      .privilege{
      	color: red;
	    float: left;
	    line-height: 30px;
	    padding-left: 15px;
	    padding-right: 10px;
	    font-weight: bold;
      }
      .privilege_one{
          position:absolute;
          top:0;
          left:13px;
          width:11%
      }
      #gonglue a{
      	text-align: center;
	    display: block;
	    width: 80px;
	    height: 26px;
	    margin: auto;
	    background-color: #ff8430;
	    line-height: 26px;
	    color:#fff;

      }
      #gonglue{display: none;}
}

</style>


</head>
<!-- Loading Screen -->
<div id='loading' class='loadingPop'style="display: none;"><img src='./images/loading.gif' class="loading-gif"/><p class=""></p></div>

<body data-ctrl=true style="background:#f8f8f8;" id="body">
	<!-- <header data-am-widget="header" class="am-header am-header-default">
		<div class="am-header-left am-header-nav">
			<img class="am-header-icon-custom" src="./images/center/nav_bar_back.png" /><span class="back_span">返回</span>
		</div>
	    <h1 class="am-header-title" >男春装</h1>
	    <div class="am-header-right am-header-nav" onclick="bianjiShangpin();">
			<img class="am-header-icon-custom" src="./images/center/nav_home.png" />
		</div>
	</header>
	<div class="topDiv"></div> --><!-- 暂时屏蔽头部 -->
	<input id="search_from"  type="hidden"  value="<?php echo $search_from;?>"> <!--1全站2本店-->
	<input id="supply_id"  type="hidden"  value="<?php echo $supply_id;?>"><!--供应商ID-->
	<input id="isnew"  type="hidden"  value="<?php echo $isnew;?>"><!--新品-->
	<input id="ishot"  type="hidden"  value="<?php echo $ishot;?>"><!--热卖-->
	<input id="isvp"  type="hidden"  value="<?php echo $isvp;?>"><!--VP产品-->
	<input id="isscore"  type="hidden"  value="<?php echo $isscore;?>"><!--积分专区-->
	<input id="isqueue"  type="hidden"  value="<?php echo $isqueue;?>"><!--队列活动专区-->
	<input id="tid"  type="hidden"  value="<?php echo $tid;?>"><!--分类ID-->
	<input id="list_tempid" type="hidden" value="<?php echo $list_tempid;?>"><!--风格ID-->
	<input id="isOpenSales" type="hidden" value="<?php echo $isOpenSales;?>"><!--开启销量-->
	<input id="isshowdiscount" type="hidden" value="<?php echo $isshowdiscount;?>"><!--开启折扣-->
	<input id="isvp_switch" type="hidden" value="<?php echo $isvp_switch;?>"><!--开启VP-->
	<input id="is_show_original" type="hidden" value="<?php echo $is_show_original;?>"><!--开启显示原价-->

	    <div class="topDivSerch<?php if($supply_id){echo "1";}?>" >
			<div id="m-type-area">
				<?php if(0<$supply_id){?>
				<div>
  
					<div id="shopType1" searval='2' class='search_top <?php if($search_from==2){echo "se_selected";}?>'><span>本店</span></div>
					<div id="shopType2" searval='1' class='search_top <?php if($search_from==1){echo "se_selected";}?>'><span>全站</span></div>
				</div>
				<?php }?>
				<div class="am-input-group">
				<form id="myform" action="" onsubmit="return false;">
					<input type="search" id="tvKeyword" class="am-form-field search" type="text" placeholder="<?php echo $placeholder;?>" value="<?php echo $search_data;?>">
				</form>
					<span class="am-input-group-btn">

						<button  class="title_serch  button buttonclick search_btn" type="button" id="search_btn" >搜索</button>
					</span>
				</div>
			</div>
		    <!--<div class="am-input-group">
	            <input id="tvKeyword" class="am-form-field search" type="text" placeholder="搜索" value="<?php echo $search_data;?>">
	            <span class="am-input-group-btn">
	                <button id="search_btn"  class="title_serch" type="button" >搜索</button>
	            </span>
	        </div>
			-->
	    </div>
	    <div style="<?php if($supply_id){echo "height:74px";}else{echo "height:52px";}?>"></div> <!-- 占据搜索框的高度 -->
	    <div class="topDivSel data_search" >
		    <div id="middle-tab6" class="tabbar">
	            <div id="sortDef" class="sort-flds" value="0">
	                <span class="title_sel" >默认<img class="am-header-icon-custom" src="./images/list_image/tagbg_item_down.png" /></span>
	            </div>

				<div id="sortSaleNum" class="sort-flds" value="0">
	                <span class="title_sel select" >销量<img class="am-header-icon-custom" src="./images/list_image/tagbg_item_down.png" /></span>
	            </div>
				<?php if($isscore==0){?>
				<div id="sortCost" class="sort-flds" value="0">
	                <span class="title_sel" >价格<img class="am-header-icon-custom" src="./images/list_image/tagbg_item_down.png" /></span>
	            </div>
				<?php }else{?>
				<div id="sortScore" class="sort-flds" value="0">
	                <span class="title_sel" >积分<img class="am-header-icon-custom" src="./images/list_image/tagbg_item_down.png" /></span>
	            </div>
	            <?php }?>
				<div id="sortTime" class="sort-flds" value="0">
	                <span class="title_sel" >时间<img class="am-header-icon-custom" src="./images/list_image/tagbg_item_down.png" /></span>
	            </div>

				<div id="sortsel" class="sort-fld-end" onclick="javascript:showSearch(1);">
	                <span class="title_sel" >筛选<img class="am-header-icon-custom" src="./images/list_image/tagbg_item5.png" /></span>
	            </div>
	        </div>
	    </div>
	    <div style="height:34px;"></div> <!-- 占据筛选框的高度 -->
    <!-- Marsk Start-->
	 <div id="leftmask" style="display:none;" data-role="none"></div>
	 <div class="search_new" id="seardiv"  style="display:none;" data-role="none">
		    <!-- 分类 -->
		    <ul class="area c-fix" id="industrydiv" style="display:none;">
		    	 <div class="m_titleDiv" >
				    <div class="btnTitleLeft" onclick="SelectArea(0);" style="visibility: hidden;">返回</div>
				    <font class="str_ftitle">分类</font>
				    <div class="btnTitleRight" onclick="SelectArea(0);" >确认</div>
	            </div>
	            <div class="white-kind" id="white-kind" >
	            		<!-- 分类 List -->
			    </div>
		  	</ul>
		  	<!-- 筛选 -->
			<ul class="area c-fix" id="areadiv">
			   	<div class="m_titleDivSel" >
				    <div class="btnTitleLeft" onclick="popClose();" >取消</div>
	                <font class="str_ftitle">筛选</font>
	                <div class="btnTitleRight" onclick="confirmOpt();" >确认</div>
	            </div>
	            <div class="white-list" style="margin-top:10px;">
			        <div class="list-one" onclick="javascript:SelectCtgr();">
			            <div class="left-title"><span >分类</span></div>
			            <div class="center-content"><span id="ctgrTitle" class="rights-spanStr">全部</span></div>
			            <div class="right-action"><img src="./images/btn_right.png" class="right-actionImg" alt=""></div>
			        </div>
			        <div class="line"></div>
					<?php if($isscore==0){?>
			        <div class="list-one" onclick="javascript:SelectCost();">
			            <div class="left-title"><span >价格</span></div>
			            <div class="center-content"><span id="costTitle" class="rights-spanStr">全部</span></div>
			            <div class="right-action"><img src="./images/btn_right.png" class="right-actionImg" alt=""></div>
			        </div>
					<?php }else{//积分专区?>
					<div class="list-one" onclick="javascript:SelectScore();">
			            <div class="left-title"><span >积分</span></div>
			            <div class="center-content"><span id="scoreTitle" class="rights-spanStr">全部</span></div>
			            <div class="right-action"><img src="./images/btn_right.png" class="right-actionImg" alt=""></div>
			        </div>
					<?php }?>
			    </div>
			    <div class="btndiv_cancel">
	                <button class="small-type-button6" type="button" onclick="popClearClose(0);" style="width:100%;">清除选项</button>
	            </div>
			</ul>
			<?php if($isscore==0){?>
			<!-- 价格区间 -->
			<ul  class="div_mo"  id="modiv" style="width:100%;">
				<div class="m_titleDiv">
				    <div class="btnTitleLeft" onclick="SelectArea(0);" style="visibility: hidden;">返回</div>
				    <font class="str_ftitle">价格区间</font>
					<div class="btnTitleRight" onclick="SelectArea(0);" >确认</div>
				    <img class="popokBtn" onclick="confirmChildOpt(1);" style="visibility: hidden;" src="./images/list_image/okBtn.jpg" />
	            </div>
	            <div class="list-one" onclick="">
		            <div class="cost-title" id="selCostTempTitle" >已选择：￥0 - ￥100</div>
		        </div>
	            <div class="white-list" id="white-price" >
	            	<!-- 价格区间 List -->
			    </div>
			    <div class="list-one" onclick="">
		            <div class="cost-title" >自定义</div>
		        </div>
		        <div class="input_costDiv">
		            <input type="text" class="inpucost_str" id="costCustMin">&nbsp;&nbsp;-&nbsp;
		            <input type="text" class="inpucost_str" id="costCustMax">

		            <div class="btnTitleRight" onclick="confirmCostCust();"  style="padding:8px 6%;">确认</div>
		        </div>
			    <div class="btndiv_cancel">
	                <button class="small-type-button6" type="button" onclick="popClearClose(1);" style="width:100%;">清除选项</button>
	            </div>
			</ul>
			<?php }else{?>
			<!-- 积分区间 -->
			<ul  class="div_mo"  id="modiv" style="width:100%;">
				<div class="m_titleDiv">
				    <div class="btnTitleLeft" onclick="SelectArea(0);" style="visibility: hidden;">返回</div>
				    <font class="str_ftitle">积分区间</font>
					<div class="btnTitleRight" onclick="SelectArea(0);" >确认</div>
				    <img class="popokBtn" onclick="confirmChildOpt(1);" style="visibility: hidden;" src="./images/list_image/okBtn.jpg" />
	            </div>
	            <div class="list-one" onclick="">
		            <div class="cost-title" id="selScoreTempTitle" >已选择1：0积分 - 100积分</div>
		        </div>
	            <div class="white-list" id="white-score" >
	            	<!-- 积分区间 List -->
			    </div>
			    <div class="list-one" onclick="">
		            <div class="cost-title" >自定义</div>
		        </div>
		        <div class="input_costDiv">
		            <input type="text" class="inpucost_str" id="scoreCustMin">&nbsp;&nbsp;-&nbsp;
		            <input type="text" class="inpucost_str" id="scoreCustMax">
		            <div class="btnTitleRight" onclick="confirmScoreCust();" >确认</div>
		        </div>
			    <div class="btndiv_cancel">

	                <button class="small-type-button6" type="button" onclick="popClearClose(1);" style="width:100%;color: #ff8430;">清除选项</button>
	            </div>
			</ul>
			<?php }?>

	 </div>
    <!-- Marsk End-->
	<input type="hidden" id="pro_type_id" value="<?php echo $type_id;?>">
	<input type="hidden" id="is_privilege_type" value="<?php echo $is_privilege_type;?>">
    <!-- 推荐商品列表 start -->
    <div id="productContainerDiv" class="productParentDiv data_search">
    	<div class="productDiv" id="productDiv">

    	<!-- 商品列表 start -->
	    	<div class="entry-content">
				<div id="<?php if($is_privilege_link){echo 'search_block';} else {echo 'search_none';}?>">
					<img src="images/search_none.png" class="search_none_img">
					<span class="search_none_tips"></span>
					<div id="gonglue" style="width:100%;display:block;">
						<a href="<?php echo $privilege_link;?>">查看攻略</a>
					</div>

				</div>
				<ul class="pinterestUl col2" id="pinterestList" fixcols="<?php if($list_tempid==2||$list_tempid==1){echo "1";}else{echo "2";}?>">
					<!-- 商品列表 -->
				</ul>
				<p id="pinterestMore" style="display: block;">----- 向下滚动加载更多 -----</p>
				<p id="pinterestDone">----- 已全部加载完毕 -----</p>
			</div><!-- .entry-content -->

    	</div>
    	<!-- 商品列表 end -->


    </div>

    <!-- 推荐商品列表 end -->

    <!-- tabbar start -->
    <?php
	//底部菜单栏
	//include_once("foot.php");
	/*判断是否显示底部菜单 start*/
	require_once('../common/utility_setting_function.php');
	$fun = "mshop_product_list";		//产品列表
	if($isscore > 0){
		$fun = "list_score";
	}else if($isvp >0){
	   $fun = "vp_area";
	}else if($isnew > 0){
	   $fun = "new";
	}else if($ishot > 0){
	   $fun = "hot";
	}
	$is_publish = check_is_publish(2,$fun,$customer_id);
	if($is_publish){
		require_once('./bottom_label.php');
	}
	/*判断是否显示底部菜单 end*/

	$nav_is_publish = check_nav_is_publish($fun,$customer_id);
	include_once('float.php');
	?>
    <!-- tabbar end -->
    <?php
    include '../common_shop/jiushop/share.php';
    ?>
</body>

<input id="isAllClear" type="hidden" value="0" name="">
<input id="selParentCtgr" type="hidden" value="-1" name="全部">
<input id="selChildCtgr" type="hidden" value="-1" name="">
<input id="selCostMin" type="hidden" value="0" name="">
<input id="selCostMax" type="hidden" value="0" name="">
<input id="selScoreMin" type="hidden" value="0" name="">
<input id="selScoreMax" type="hidden" value="0" name="">

<input id="curParentCtgr" type="hidden" value="-1" name="全部">
<input id="curChildCtgr" type="hidden" value="-1" name="">
<input id="curCostMin" type="hidden" value="0" name="">
<input id="curCostMax" type="hidden" value="0" name="">
<input id="curScoreMin" type="hidden" value="0" name="">
<input id="curScoreMax" type="hidden" value="0" name="">
<script>
var user_id 	    = "<?php echo $user_id ;?>";
var customer_id 	= "<?php echo $customer_id_en ;?>";
var brand_typeid 	= "<?php echo $brand_typeid ;?>";
var tid 			= "<?php echo $tid ;?>";
var like_op 		= "<?php echo $like_op ;?>";
var like_pid 		= "<?php echo $like_pid ;?>";
var s_n = '<?php echo $s_n ;?>';
var searchpage		= "<?php echo $searchpage;?>";
var $images_skin    = "<?php echo $images_skin?>";
var type_id         = "<?php echo $type_id; ?>";
var type_name       = "<?php echo $type_name; ?>";
var curCostMin      = "<?php echo $curCostMin; ?>";
var curCostMax      = "<?php echo $curCostMax; ?>";
var curScoreMin     = "<?php echo $curScoreMin; ?>";
var curScoreMax     = "<?php echo $curScoreMax; ?>";
var searchKey       = "<?php echo $searchKey; ?>";
var op_sort         = "<?php echo $op_sort; ?>";
var user_level 		= "<?php echo $user_level; ?>";//用户身份
var is_privilege_link = "<?php echo $is_privilege_link;?>";
var privilege_link  = "<?php echo $privilege_link;?>";
var is_privilege_type = "<?php echo $is_privilege_type;?>";
var is_privilege    = "<?php echo $is_privilege;?>";
//console.log("is_privilege_type="+is_privilege_type);
var share_url = "<?php echo Protocol.$http_host;?>/weixinpl/common_shop/jiushop/forward.php?type=20&customer_id=<?php echo $customer_id_en;?>&exp_user_id=<?php echo passport_encrypt((string)$user_id);?>";//默认分享链接
$("#shopType1").click(function(){ //点击本店，全站
	$("#shopType1").addClass("se_selected");
	$("#shopType2").removeClass("se_selected");
	$("#tvKeyword").attr("placeholder","搜索本店内宝贝");
	$("#search_from").attr("value",2);
});
$("#shopType2").click(function(){
	$("#shopType2").addClass("se_selected");
	$("#shopType1").removeClass("se_selected");
	$("#tvKeyword").attr("placeholder","搜索全站内宝贝");
	$("#search_from").attr("value",1);
});
if(searchpage==1){ //修改搜索页过来不能搜索
	$("#search_btn").trigger("click");
}
search_keyword ='<?php echo $search_data;?>';
var $color='<?php echo $skin?>';

</script>
    <script type="text/javascript" src="../../mp/currency_config/config.currency.default.js"></script><!--货币默认设置 -->
	<script type="text/javascript" src="../../mp/currency_config/config.currency.js"></script>  <!--货币用户设置-->
	<script type="text/javascript" src="./js/global.js"></script>
	<script type="text/javascript" src="./assets/js/amazeui.js"></script>
    <script src="./js/r_global_brain.js" type="text/javascript"></script>
	<!-- <script type="text/javascript" src="./js/r_jquery.mobile-1.2.0.min.js"></script>  -->
	<script src="./js/r_pinterest.js" type="text/javascript"></script>
    <script type="text/javascript" src="./js/loading.js"></script>
    <script src="./js/jquery-cookie.js"></script>
<script>
	var reloadurl = top.location.href;
	$(".search_top").click(function(){
		var searchval = $(this).attr('searval');
		window.location.href = reloadurl+'&search_data=&reload=1&search_from='+searchval
	});
	// <!--微信分享页面参数start-->
	var debug      = false;//调试
	title="<?php echo $typestr."列表";?>"; //标题
	desc='<?php echo $shop_introduce;?>'; //分享内容
	imgUrl="<?php echo $define_share_image;?>";//分享LOGO
	share_type=1;//自定义类型
	/*	share_type:菜单类型
	-1：显示所有，除去复制链接以及查看公众号。
	1 ：只显示 发送给朋友，分享到朋友圈，收藏，刷新，调整字体，投诉。
	2 ：只显示 发送给朋友，分享到朋友圈，分享到QQ，分享到QQ空间，收藏，刷新，调整字体，投诉。
	3 : 只显示收藏，刷新，调整字体，投诉。
	*/

	// <!--微信分享页面参数end-->
</script>
<script src="./js/goods/list.js?ver=<?php echo JSVER?>"></script>

<!--引入微信分享文件start-->
<?php require('../common/share.php');?>
<!--引入微信分享文件end-->
</body>
</html>