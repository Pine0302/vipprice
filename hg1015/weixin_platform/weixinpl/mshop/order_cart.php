﻿<?php
header("Content-type: text/html; charset=utf-8");     
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]

$link = mysql_connect(DB_HOST,DB_USER,DB_PWD); 
mysql_select_db(DB_NAME) or die('Could not select database'); 
require('select_skin.php');

//头文件----start
require('../common/common_from.php');
//头文件----end

/* $owner_id 		= -1;
 $customer_id    = 3243;
$pid            = 1568;
$user_id 	 	= 191099 ; */
$is_virtual = $_SESSION['is_virtual_'.$user_id];
$shop_card_id   = -1;
$is_number_limit = -1;
$per_number_limit = 0;
$query	= "select pro_card_level,shop_card_id,is_number_limit,per_number_limit from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
$result = _mysql_query($query) or die('商品归属Query failed2: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$shop_card_id     = $row->shop_card_id;//分销会员卡
	$is_number_limit  = $row->is_number_limit;//分销会员卡
	$per_number_limit = $row->per_number_limit;//分销会员卡
	$pro_card_level   = $row->pro_card_level;//会员卡等级购物开关
}
$bcount = 0;//今天已购买数量
$day_buy = "select count(1) as bcount from weixin_commonshop_orders where  isvalid=true and user_id=".$user_id." and customer_id=".$customer_id." and day(createtime) = day(curdate()) ";
$result = _mysql_query($day_buy) or die('day_buy failed2: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$bcount = $row->bcount;
}

//微商城id
$shop_id = -1;
$sql = "SELECT id FROM weixin_commonshops WHERE isvalid=true AND customer_id=".$customer_id." LIMIT 1";

$res = _mysql_query($sql) or die('Query failed34: ' . mysql_error());
while( $row = mysql_fetch_object($res) ){
	$shop_id = $row->id;    //微商城唯一标识
}
//商城订单提示开关
$is_openOrderMessage = 0;
$query = "select is_openOrderMessage from weixin_commonshops_extend  where isvalid=true and customer_id=".$customer_id." and shop_id=".$shop_id;
$result = _mysql_query($query) or die('tc_erweima_W116 Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$is_openOrderMessage = $row->is_openOrderMessage;
}

$_SESSION['is_check_password_'.$user_id] = '';			//清除密码输入记录

$o_shop_id=-1;
if( !empty($_GET['shop_id']) ){
	$o_shop_id = $configutil->splash_new($_GET["shop_id"]);
}

$yundian_id=-1;
if( !empty($_GET['yundian']) ){		//云店ID
	$yundian_id = $configutil->splash_new($_GET["yundian"]);
}else{
	$session_yundian = $_SESSION['yundian_'.$customer_id];//-1时平台产品;大于0时为云店产品
    if(empty($session_yundian)){
       $session_yundian = -1;
    }
    $yundian_id = $session_yundian;
}
// echo "o_shop_id: ".$o_shop_id."<br/>";
?>
<!DOCTYPE html>

<html manifest="order_cart_cache.appcache">
<head>
    <title>购物车</title>
    <!-- 模板 -->   
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

    <script type="text/javascript" src="../../mp/currency_config/config.currency.default.js"></script><!--货币默认设置 -->
	<script type="text/javascript" src="../../mp/currency_config/config.currency.js"></script>  <!--货币用户设置-->
    <script type="text/javascript" src="./assets/js/jquery.min.js"></script>    
    <script type="text/javascript" src="./assets/js/amazeui.js"></script>
    <script type="text/javascript" src="./js/global.js"></script>
    <script type="text/javascript" src="./js/loading.js"></script>
	<?php if($is_openOrderMessage == 1){ ?>
		<script type="text/javascript" src="./js/order_message.js"></script>
	<?php } ?>
    <script src="./js/jquery.ellipsis.js"></script>
    <script src="./js/jquery.ellipsis.unobtrusive.js"></script>
    <!-- 模板 -->
    
    <!-- 页联系style-->
    <link type="text/css" rel="stylesheet" href="css/list_css/style.css" />
    <link type="text/css" rel="stylesheet" href="css/goods/global.css?<?php echo time(); ?>" />
	<link type="text/css" rel="stylesheet" href="css/order_css/global.css" />
    <link type="text/css" rel="stylesheet" href="css/goods/order_cart.css" />
    <!-- 属性图预览 -->
	<link type="text/css" rel="stylesheet" href="css/ImgPreview.css" />
	<link type="text/css" rel="stylesheet" href="css/swiper.min.css" />

    <link type="text/css" rel="stylesheet" href="./css/css_<?php echo $skin ?>.css" />        
	<!-- <link type="text/css" rel="stylesheet" href="./css/goods/product_detail.css?ver=<?php echo time(); ?>" /> -->
    <!-- 页联系style-->
    <style type="text/css">
		.btn-shui{height: 16px; background: #fff;border:1px solid #ff7109; color: #ff7109!important;border-radius: 2px;font-size: 12px;padding: 0;margin-right: 10px;}
	   .test5 {
		display: inline-block;
	    height:0;
	    width:20px;
	    color:#fff;
	    line-height: 0;
	    border-color:#ff7109 #fff transparent transparent;
	    border-style:solid solid dashed dashed;
	    border-width:14px 4px 0 0 ;
		}
      .test5 span{display: block;margin-top: -6px;color: #fff;font-weight: bold;}
	  .itemProContent{width:150%;}
	  .hide{display:none;}
	  
	/* .footer{position: fixed;bottom: 0px;left: 0px;width: 100%;height: 56px;background:#fff;z-index: 1110;line-height: 24px;border-top: 1px solid #eeeeee;}
	.footer .footer-box{margin:0 auto;width: 100%;height: 55px;display: -webkit-box;}
	.footer .footer-box .weidian{height: 55px;text-align: center;-webkit-box-flex: 1;
		-moz-box-flex: 1;display:flex;align-items:center;justify-content:center;}
	.footer .footer-box .weidian img{width: 55px;height: 55px;vertical-align: middle;}
	.footer .footer-box .weidian p{font-size: 12px;color: #a1a1a1;margin: 0;}
	.footer .footer-box .weidian.active p{color:#64b83c;white-space:nowrap;text-overflow:clip;overflow: hidden;}
	.footer .footer-box .weidian p.foot_grey{color: #a1a1a1;}
	.paddingBottom{height:55px;} */
	.footer{position: fixed;bottom: 0px;left: 0px;width: 100%;height: 49px;background:#fff;z-index: 50;line-height: 24px;border-top: 1px solid #eeeeee;box-shadow: 0 0 10px 0 rgba(155,143,143,0.6);
        -webkit-box-shadow: 0 0 10px 0 rgba(155,143,143,0.6);padding: 0px;}
	.footer .footer-box{margin:0 auto;width: 100%;height: 49px;display: -webkit-box;}
	.footer .footer-box .weidian{height: 49px;text-align: center;-webkit-box-flex: 1;
	    -moz-box-flex: 1;display:flex;align-items:center;justify-content:center;float: none;}
	.footer .footer-box .weidian img{width: 49px;height: 49px;vertical-align: middle;}
	.footer .footer-box .weidian p{font-size: 12px;color: #a1a1a1;margin: 0;}
	.footer .footer-box .weidian.active p{color:#64b83c;white-space:nowrap;text-overflow:clip;overflow: hidden;}
	.footer .footer-box .weidian p.foot_grey{color: #a1a1a1;}
	.paddingBottom{height:49px;}

	.footer.hasname{position:fixed;bottom:0px;left:0px;width:100%;height:49px;background:#fff;z-index:50;line-height:24px;border-top:1px solid #eeeeee;box-shadow:0 0 10px 0 rgba(155,143,143,0.6);-webkit-box-shadow:0 0 10px 0 rgba(155,143,143,0.6);padding:0px}
	.footer.hasname .footer-box{margin:0 auto;width:100%;height:49px;display:-webkit-box}
	.footer.hasname .footer-box .weidian{height:49px;text-align:center;-webkit-box-flex:1;-moz-box-flex:1;display:flex;align-items:center;justify-content:center;float:none}
	.footer.hasname .footer-box .weidian p{font-size:10px;color:#000000;margin:0;line-height:14px;overflow:hidden}
	.footer.hasname .footer-box .weidian.active p{color:#64b83c;white-space:nowrap;text-overflow:clip;overflow:hidden}
	.footer.hasname .footer-box .weidian p.foot_grey{color:#a1a1a1}
	.paddingBottom{height:49px}
	.footer.hasname .footer-box .weidian img{width:32px;height:32px;margin:0 auto;vertical-align:middle}
	.footer.hasname .footer-box .weidian .foot-text{font-size:10px;line-height:14px;white-space:nowrap;overflow:hidden}
.content-footer{box-sizing: border-box;overflow: hidden;}	
.content-footer-left3{max-width: 78%;}
.content-footer-left3-item1{max-width: 55%;}  
.content-footer-left3-item1-top1-font2{white-space: nowrap;

    display: inline-block;
}

    </style>
    <script src="./js/r_global_brain.js" type="text/javascript"></script>
</head>




<body data-ctrl=true>
	<!-- header部门-->
	<!-- <header data-am-widget="header" class="am-header am-header-default header">
		<div class="am-header-left am-header-nav header-btn">
			<img class="am-header-icon-custom"  src="./images/center/nav_bar_back.png"/><span>返回</span>
		</div>
	    <h1 class="header-title">购物车</h1>
	    <div class="am-header-right am-header-nav">
		</div>
	</header>
	<div class="topDiv" style="height:49px;"></div> -->     <!-- 暂时屏蔽头部 -->
	<!-- header部门-->
	
	<div id = "menu-row1" style="background:#f8f8f8" >
    		<!-- 搜索部门-->
		    <div class = "menu-row1-wrapper">
				<div class = "menu-row1-left1" id = "row1-button1">
					<a href="personal_center.php?customer_id=<?php echo $customer_id_en; ?>&yundian=<?php echo $yundian_id; ?>">
			    		<div class = "menu-row1-left1-top1">
			    			<img src="./images/firstPage/my_tab_4_sel_shequ.png" >
			    		</div>
			    		<div class = "menu-row1-left1-top2">
							<span>我的</span>
						</div>
					</a>
			    </div>
			    <div class="menu-row1-left2" style = "">
			        <input id="tvKeyword" class="search-input" onfocus="search();" type="text" placeholder="搜索" >
			    </div>
		    	<div class = "menu-row1-right" id = "row1-button2">
					<a href="class_page.php?customer_id=<?php echo $customer_id_en; ?>">
						<div class = "menu-row1-right-top1">
							<!-- <img src="./images/goods_image/2016042901.png" > -->
							<img src="./images/goods_image/pro-class.png"> 
						</div>
						<div class = "menu-row1-right-top2">
							<span>分类</span>
						</div>
					</a>
		    	</div>
		    </div>
			<!-- 搜索部门-->
     </div>
     <div style="height:52px;"></div> <!-- 占据搜索框的高度 -->
    <!-- content -->
    <div  class = "content-main" id="containerDiv" >
    	<ul id="resultData" style="width:100%;overflow:auto; margin-top:3px;padding-left:0px;margin-bottom:64px;">

		</ul>
	</div>
    <!-- content -->
    
    
	<div class="order_message hide" id="order_message" style="background-color: rgba(0,0,0,0.3);border-radius: 30px;height:30px;position:fixed;top: 52px;left: 10px;z-index: 10;">
		<img style="width: 24px;height: 24px;border-radius: 50%;vertical-align: middle;margin:2px 3px;display: inline-block;" src="/weixinpl/mshop/images/dalibao.jpg"/>
		<p style="color: #ffffff;font-size:12px;display: inline-block;margin: 0;vertical-align: middle;padding-right: 7px;"><span style="width:46px;display:inline-block;vertical-align:middle;text-overflow:ellipsis;white-space:nowrap;overflow:hidden;margin-right: 5px;">瞪圆德惠</span><span style="display:inline-block;vertical-align:middle;">下了一笔订单</span><span style="display:inline-block;vertical-align:middle;margin-left: 5px;">5分钟前</span></p>
	</div>
    
    <!-- footer -->
    <div class = "content-footer">
    	<div class = "content-footer-left1">

			<img class = "all-select" src = "./<?php echo $images_skin?>/list_image/checkbox_off.png" >
		</div>
    	<span class = "content-footer-left2">全选</span>
    	
    	<div class = "content-footer-left3">
    		<div class = "content-footer-left3-item1">
    			<div class = "content-footer-left3-item1-top1">
    				<div class = "content-footer-left3-item1-top1-wrapper">
	    				<font style="font-size:15px;">总价:</font>
	    				<?php if(OOF_P != 2){ ?><font class = "content-footer-left3-item1-top1-font1"><?php echo OOF_S ?></font><?php } ?>
	    				<font class = "content-footer-left3-item1-top1-font2" id = "zongjia">0</font>
	    				<?php if(OOF_P == 2){ ?><font class = "content-footer-left3-item1-top1-font1"><?php echo OOF_S ?></font><?php } ?>
	    				
    				</div>
    			</div>
    			<div class = "content-footer-left3-item1-top2">
    				<div class = "content-footer-left3-item1-top2-wrapper">不含运费</div>
    			</div>
    		</div>
    		<div class = "jiesan-button" onclick="statement();">
    			<span>结算</span>
    		</div>
    	</div>
    </div>
    <!-- footer -->
    
  
    <!-- dialog -->
    <div class="am-share shangpin-dialog" >
        <!-- 加入购买 -->
    	
	  	<!-- 加入购买 -->
	</div>
	
	<!-- 分类dialog-->
	<div id="leftmask" style="display:none;" data-role="none"></div>
	<div class="search_new"  id="seardiv"  style="display:none;" data-role="none">	
	    <ul class="area c-fix" id="industrydiv" style="display:none;">
            <div class="white-list" id="white-price">
            	<!-- 价格区间 List -->
		    </div>
	  	</ul>
	 </div>
    <!-- 分类dialog-->
	<!-- dialog -->
	<div id="cartGood">
	</div>

        <!--悬浮按钮-->
	<?php
	require_once('../common/utility_setting_function.php');
	$fun = "order_cart";
	$nav_is_publish = check_nav_is_publish($fun,$customer_id);
	$is_publish = check_is_publish(2,$fun,$customer_id);
	include_once('float.php');?>
	<!--悬浮按钮-->
    <!--底部菜单-->

    <!--底部菜单-->

</body>		
<!-- 页联系js -->
<script src="/shop/Public/Default/Home/js/cart_function_h5.js"></script>
<script>
var $skin_img='<?php echo $images_skin?>';
</script>
<script src="js/goods/global.js"></script>
<script src="js/goods/order_cart.js"></script>
<!--<script src="js/goods/order_cart_new.js?ver=<?php echo time(); ?>"></script>-->
<script src="js/goods/limitbuy.js"></script><!--限购方法ww-->
<!-- 页联系js -->
<script type="text/javascript" src="js/ImgPreview.js"></script>
<script type="text/javascript" src="js/swiper.min.js"></script>
<script>
var user_id 		= '<?php echo $user_id?>';
var customer_id		= '<?php echo $customer_id_en; ?>';
var shop_card_id	= '<?php echo $shop_card_id; ?>';
var is_number_limit	= '<?php echo $is_number_limit; ?>';
var per_number_limit= '<?php echo $per_number_limit; ?>';
var bcount			= '<?php echo $bcount; ?>';
var o_shop_id       = <?php echo $o_shop_id; ?>; //订货系统门店id
var pro_card_level  = '<?php echo $pro_card_level ?>';
var from_type       = '<?php echo $from_type;?>';
var g_app_type      = '<?php echo $g_app_type;?>';
var yundian_id 		= '<?php echo $yundian_id;?>';	//云店id
var customer_id2    = '<?php echo $customer_id; ?>';

is_exit_localStorage(2)
// console.log(localStorage);
sessionStorage.setItem('orderingretail_store_addr'+user_id,'');//清空
</script>
<?php require('../common/share.php');?>
<?php
require_once('../common/utility_setting_function.php');
$fun = "order_cart";
$is_publish = check_is_publish(2,$fun,$customer_id);
?>
<script>
 <?php if($is_publish){?>
    get_bottom_label(user_id,is_publish);
 <?php }?>
</script>
<script src="js/CheckUserLogin.js?ver=<?= JSVER ?>"></script><!--检验用户是否已登录-->
</body>
</html>