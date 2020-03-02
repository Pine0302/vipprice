<?php
require('../../../../wsy_prod/admin/Product/logs.php');
header("Content-type: text/html; charset=utf-8");
require('../../../../weixinpl/config.php');
require('../../../../weixinpl/customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../../weixinpl/back_init.php');
require('../../../../weixinpl/common/utility.php');
require('../../../../weixinpl/common/utility_4m.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/mp/function_redis.php');


/*echo "<pre>";
$data_info = $_POST;
unset($data_info['description']);
unset($data_info['specifications']);
print_r($data_info);
exit;*/



$name = $configutil->splash_new(ereg_replace("/[\'\"\’\”\‘\“]/","",$_POST["name"]));//过滤单引号和双引号
$name = str_replace('-', '', $name);//过滤横杠
$keyid =$configutil->splash_new($_POST["keyid"]);
$adminuser_id = $configutil->splash_new($_GET["adminuser_id"]);
$orgin_adminuser_id = $configutil->splash_new($_GET["orgin_adminuser_id"]);
$owner_general 		= $configutil->splash_new($_GET["owner_general"]);
$offer_id =0;
$offer_id 		    = (int)$configutil->splash_new($_POST["offer_id"]);

$stock_pidarr="";
if(!empty($_POST["stock_pidarr"])){
	$stock_pidarr = $configutil->splash_new($_POST["stock_pidarr"]);
}

// var_dump($_POST);die();

//echo $stock_pidarr;
$orgin_price = $configutil->splash_new($_POST["orgin_price"]);
$now_price   = $configutil->splash_new($_POST["now_price"]);
$vip_price   = $configutil->splash_new($_POST["vip_price"]);
$cost_price	 = $configutil->splash_new($_POST["cost_price"]);
$for_price	 = $configutil->splash_new($_POST["for_price"]);
$orgin_price = round($orgin_price, 2);
$cost_price  = round($cost_price, 2);
$now_price 	 = round($now_price, 2);
$vip_price 	 = round($vip_price, 2);
$for_price 	 = round($for_price, 2);
//$unit		 = $configutil->splash_new($_POST["unit"]);
$unit		 = '个';
$weight = 0;
if(!empty($_POST["weight"])){
	$weight  = $configutil->splash_new($_POST["weight"]);
}

$foreign_mark 	 		 = $configutil->splash_new($_POST["foreign_mark"]);
$storenum	  	 		 = 0;
if(!empty($_POST["storenum"])){
	$storenum	  	 		 = $configutil->splash_new($_POST["storenum"]);
}
$need_score 			 = 0;
if(!empty($_POST["need_score"])){
	$need_score	  	 		 = $configutil->splash_new($_POST["need_score"]);
}
if(!empty($_POST["show_sell_count"])){
	$show_sell_count 		 = $configutil->splash_new($_POST["show_sell_count"]);
}else{
	$show_sell_count 		 = 0;
}
$asort_value	 		 = $configutil->splash_new($_POST["asort_value"]);
$nowprice_title  		 = $configutil->splash_new($_POST["define_price_tag"]);
$type_id		 		 = $configutil->splash_new($_POST["type_id"]);
$pro_price_detail 		 = $configutil->splash_new($_POST["pro_price_detail"]);
$is_QR 			  		 = $configutil->splash_new($_POST["is_QR"]);  //二维码产品
/*郑培强*/
if($is_QR==1){
	$QR_isforever 	         = $configutil->splash_new($_POST["QR_isforever"]);
	if($QR_isforever==1){
		$QR_select 			 = $configutil->splash_new($_POST["QR_select"]);
		if($QR_select==1){
			//$QR_starttime    = $configutil->splash_new($_POST["QR_starttime"]);
			//$QR_endtime 	 = date("Y-m-d H:i:s",strtotime($configutil->splash_new($_POST["QR_endtime"]))+24*3600-1);
			$QR_starttime    = $configutil->splash_new($_POST["QR_starttime"]);
			$QR_endtime      = $configutil->splash_new($_POST["QR_endtime"]);
		}else{
			$QR_isforever    = 2;
			$QR_starttime    = date("Y-m-d");
			$QR_day          = $configutil->splash_new($_POST["QR_day"]);
			$QR_endtime 	 = date("Y-m-d H:i:s",$QR_day*24*3600+strtotime($QR_starttime)-1);
		}
	}
}else{
	$QR_isforever            = 0;
	$QR_starttime            = "00-00-00 00:00:00";
	$QR_endtime              = "00-00-00 00:00:00";
}
/*郑培强*/
$city_id 		 		 = $configutil->splash_new($_POST["city_id"]);  //产品区域编号
$define_share_image_flag = $configutil->splash_new($_POST["define_share_image_flag"]);
$is_invoice 			 = $configutil->splash_new($_POST["is_invoice"]);//发票开关
$is_currency 			 = $configutil->splash_new($_POST["is_currency"]);//是否购物币
$is_guess_you_like 		 = $configutil->splash_new($_POST["is_guess_you_like"]);//是否猜您喜欢产品
$back_currency 			 = $configutil->splash_new($_POST["back_currency"]);
$is_free_shipping 		 = $configutil->splash_new($_POST["is_free_shipping"]);//是否包邮
//$first_division 		 = $configutil->splash_new($_POST["first_division"]);//一级分佣金额
$isscore 		 		 = $configutil->splash_new($_POST["isscore"]);//是否积分专区
$islimit 		 		 = $configutil->splash_new($_POST["islimit"]);//是否限购
$is_pickup 		 		 = $configutil->splash_new($_POST["is_pickup"]);//是否自提产品
$limit_num 		 		 = $configutil->splash_new($_POST["limit_num"]);//限购数量
$is_first_extend 		 = $configutil->splash_new($_POST["is_first_extend"]);//是否首次推广奖励产品
$extend_money 		 	 = $configutil->splash_new($_POST["extend_money"]);//首次推广奖励金额
$privilege_level 		 = $_POST['privilege'];
$currency_percentage 	 = $configutil->splash_new($_POST["currency_percentage"]);//购物币抵扣比例
if($currency_percentage==''){
        $currency_percentage = -1;
    }
if($currency_percentage>0){
    $currency_percentage     = $currency_percentage/100;
}
if($is_pickup==''){
	$is_pickup = 0;
}
$link_coupons 		 	 = $configutil->splash_new($_POST["link_coupons"]);//是否关联优惠券
if($link_coupons==""){
$link_coupons=-1;
}else{
$check_cous = $configutil->splash_new($_POST["link_coupons_save"]);
if($check_cous==""||$check_cous==-1){
$link_coupons=-1;
}else{
$link_coupons=$check_cous;
}
}

if(!empty($_POST['is_privilege'])){
	$is_privilege = 1;
}else{
	$is_privilege = 0;
}
//echo $is_privilege;die;

if(!empty($_POST['privilege'])){
	$privilege_level =  implode("_",$privilege_level);
}else{
	$privilege_level = "-1_0_1_2_3_4_5";
}

//主属性图片
$attr_img_arr = '';
if(!empty($_POST['attr_src'])){
	$attr_img_arr = $_POST["attr_src"];

}



$express_type = 0;
if(!empty($_POST["express_type"])){
	$express_type = $configutil->splash_new($_POST["express_type"]);//邮费计费方式
}
//echo $is_invoice;die;

$is_identity = 0;
if(!empty($_POST["is_identity"])){
	$is_identity = $configutil->splash_new($_POST["is_identity"]);
}
$donation_rate = 0;
if(!empty($_POST["donation_rate"])){
	$donation_rate = $configutil->splash_new($_POST["donation_rate"]);  //慈善比例
}
//$is_identity=$configutil->splash_new($_POST["is_identity"]);//产品是否需要身份证购买开关

$define_share_image='';
$install_price = 0;

if(!empty($_POST["install_price"])){
	$install_price = $configutil->splash_new($_POST["install_price"]);
}
$cashback = '';
if($_POST["cashback"]!=''){
	$cashback = $configutil->splash_new($_POST["cashback"]);
}
$cashback_r = '';
if($_POST["cashback_r"]!=''){
	$cashback_r = $configutil->splash_new($_POST["cashback_r"]);
}
$agent_discount = 0;
if(!empty($_POST["agent_discount"])){
	$agent_discount = $configutil->splash_new($_POST["agent_discount"]);//代理商折扣
}
$pro_card_level_id = -1;
if(!empty($_POST["pro_card_level_id"])){
	$pro_card_level_id = $configutil->splash_new($_POST["pro_card_level_id"]);//购买产品需要的会员卡等级ID
}
if($define_share_image_flag==1){
//_file_put_contents('hello.txt','**********'.$_FILES['new_define_share_image']['tmp_name']);
	if(!empty($_FILES['new_define_share_image']['name'])){
	//_file_put_contents('hello2.txt','-------');
		$rand1=rand(0,9);
		$rand2=rand(0,9);
		$rand3=rand(0,9);
		$filename=date("Ymdhis").$rand1.$rand2.$rand3;
		$filetype=substr($_FILES['new_define_share_image']['name'], strrpos($_FILES['new_define_share_image']['name'], "."),strlen($_FILES['new_define_share_image']['name'])-strrpos($_FILES['new_define_share_image']['name'], "."));
		$filetype=strtolower($filetype);
		if(($filetype!='.jpg')&&($filetype!='.png')&&($filetype!='.gif')){
				echo "<script>alert('文件类型或地址错误');</script>";
				echo "<script>history.back(-1);</script>";
				exit ;
			}
		$filename=$filename.$filetype;
		$savedir = "../../../".Base_Upload."Product/product/";



		if(!file_exists($savedir)){
			mkdir($savedir,0777,true);
		}
		//$savedir='../up/common_shop_define/';
		//_file_put_contents('hello3.txt',$davedir.'++++'.$filename);
		/*if(!is_dir($savedir)){
			mkdir($savedir,0777);
		}*/


		$savefile=$savedir.$filename;



		if (!_move_uploaded_file($_FILES['new_define_share_image']['tmp_name'], $savefile)){
			//echo "<script>文件上传成功！</script>";
			echo "<script>history.back(-1);</script>";
			exit;
		}
		$define_share_image=$savefile;
	}else{
		$define_share_image=$_POST['now_define_share_image'];
	}
	if(strpos($define_share_image,"/weixinpl/") === false){ //不包含才加上/weixinpl/
		$define_share_image = str_replace("../","",$define_share_image);
		$define_share_image = "/wsy_prod/".$define_share_image;
	}

}
//$f = fopen('out.txt', 'w');
//fwrite($f, "==pro_price_detail=====".$pro_price_detail."\r\n");

//fclose($f);
$specifications = "";
$customer_service = "";
$imgids=$configutil->splash_new($_POST["imgids"]);
$img_link=$configutil->splash_new($_POST["img_link"]);
$img_3d=$configutil->splash_new($_POST["img_3d"]);
$tradeprices=$configutil->splash_new($_POST["tradeprices"]);
$introduce = $configutil->splash_new($_POST["introduce"]);
$short_introduce_color = $configutil->splash_new($_POST["introduce_color"]);
$remarks = $configutil->splash_new($_POST["remarks"]);
$remark_color = $configutil->splash_new($_POST["remark_color"]);
$description = $configutil->splash_new($_POST["description"]);
// $description = preg_replace('/<p><img/','<p style="font-size:0;"><img',$description);
// $description = preg_replace('/<p><img/','<p style="font-size:1;"><img',$description);

$product_remark = array("color"=>$remark_color,"concent"=>$remarks);
$product_remark = array($product_remark);
$product_remark = str_replace('\\','\\\\',json_encode($product_remark));
#var_dump (mysql_real_escape_string($product_remark_ss));die;
#die(str_replace('\\','\\\\',$product_remark_ss));

//$description = mysql_real_escape_string($description);
$customer_service = $configutil->splash_new($_POST["service"]);
// var_dump($customer_service);exit;
$specifications = $configutil->splash_new($_POST["specifications"]);
$propertyids = $configutil->splash_new($_POST["propertyids"]);		//组合属性数组

$default_imgurl = $configutil->splash_new($_POST["default_imgurl"]);
$class_imgurl = $configutil->splash_new($_POST["class_imgurl"]);

$isout = $configutil->splash_new($_POST["isout"]);

if($isout==1){
	$isout_status = 0;//0:供应商可以修改产品上下架,1:不可以修改
}else{
	$isout_status = 1;
}
$isnew     = $configutil->splash_new($_POST["isnew"]);
$ishot     = $configutil->splash_new($_POST["ishot"]);
$issnapup  = $configutil->splash_new($_POST["issnapup"]);
$isvp      = $configutil->splash_new($_POST["isvp"]);      //是否属于vp产品，1：是；0：否
$vp_score  = 0;
$vp_score  = $configutil->splash_new($_POST["vp_score"]);  //vp值,vp产品消费累积满多少vp值可以提现佣金
$is_mini_mshop = 0;
if(!empty($_POST["is_mini_mshop"])){
	$is_mini_mshop  = $_POST["is_mini_mshop"];  //微信小程序显示产品
}

$buystart_time=0;
$countdown_time=0;
if($issnapup == 1){
	if(!empty($_POST["buystart_time"])){
		$buystart_time = $configutil->splash_new($_POST["buystart_time"]);  //商品抢购开始时间
	}
	if(!empty($_POST["countdown_time"])){
		$countdown_time = $configutil->splash_new($_POST["countdown_time"]);  //商品抢购结束时间
	}
}

$is_Pinformation_b = $configutil->splash_new($_POST["is_Pinformation_b"]);//必填信息大开关1：开 0：关
$is_Pinformation = 0;
if($is_Pinformation_b){
	$is_Pinformation   = $configutil->splash_new($_POST["is_Pinformation"]);
	//必填信息产品开关1：开 0：关
}

$freight_id = -1;
if(!empty($_POST["freight_id"])){
	$freight_id = $configutil->splash_new($_POST["freight_id"]);  //运费模板ID
}

$is_virtual	= 0;
if(!empty($_POST["is_virtual"])){
	$is_virtual = $configutil->splash_new($_POST["is_virtual"]);  //是否为虚拟产品 0:非虚拟产品,1:虚拟产品
}

$type_ids=$configutil->splash_new($_POST["type_ids"]);
$auth_user_id=$configutil->splash_new($_POST["auth_user_id"]);   //授权用户customer_users表的id
$asort = -1;
if(!empty($_POST["asort"])){		//排序优先级(已隐藏) 赋值 默认值-1
	$asort = $configutil->splash_new($_POST["asort"]);
}
//echo $imgids;
if(!empty($_POST["pro_discount"])){
   $pro_discount=$configutil->splash_new($_POST["pro_discount"]);
}else{
  $pro_discount=0;
}

if(!empty($_POST["pro_reward"])){
   $pro_reward=$configutil->splash_new($_POST["pro_reward"]);
}else{
  $pro_reward=0;
}
$head = 10;
if(!empty($_GET["head"])){
   $head = $configutil->splash_new($_GET["head"]);
}
$pagenum = 1;
if(!empty($_GET["pagenum"])){
   $pagenum = $configutil->splash_new($_GET["pagenum"]);
}

$product_voice = "";
if(!empty($_POST["product_voice"])){
	$product_voice = $configutil->splash_new(strFilter($_POST["product_voice"]));//产品宣传语音
}

$product_vedio = "";
if(!empty($_POST["product_vedio"])){
	$product_vedio = $configutil->splash_new(strFilter($_POST["product_vedio"]));//产品宣传视频
}
$istax 			= $configutil->splash_new($_POST["istax"]); 		//关税税率
$tariff 		= $configutil->splash_new($_POST["tariff"]); 		//关税税率
$comsumption 	= $configutil->splash_new($_POST["comsumption"]); 	//消费税税率
$addedvalue 	= $configutil->splash_new($_POST["addedvalue"]); 	//增值税税率
$postal 		= $configutil->splash_new($_POST["postal"]); 		//行邮税

if($istax > 0){
	$tax_type = $configutil->splash_new($_POST["tax_type"]); 		//产品税收类型
}else{
	$tax_type = 1;
}
// echo $istax."</br>";
// echo $tax_type;die;
//$tax_type = $configutil->splash_new($_POST["tax_type"]); 		//产品税收类型
//echo $istax."==".$keyid;die;

$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
_mysql_query("SET NAMES UTF8");
 //add by wenjun

$u4m = new Utiliy_4m_new();
$rearr = $u4m->is_4M_new($customer_id);
 //是4m分销
$is_shopgeneral = $rearr[0];

if ( $is_shopgeneral ){
	$customer_ids = $customer_id.",".$u4m->getAllSubCustomers_new($customer_id,2);
} else {
	$customer_ids=$customer_id;
}
 //$customer_ids = $customer_ids.",".$u4m->getAllSubCustomers($adminuser_id,$orgin_adminuser_id,$owner_general);
 // $customer_ids = $customer_ids.",".$u4m->getAllSubCustomers_new($customer_id,2);

//关联大礼包
$is_package = -1;
if(!empty($_POST['is_package'])){
	$is_package = $configutil->splash_new($_POST["is_package"]);
}

$link_package = -1;
$link_package_img = '';
if($is_package=='on'){
	if(!empty($_POST['link_package'])){
		$link_package = $configutil->splash_new($_POST["link_package"]);
	}
	/*图片上传*/
	 $uptypes=array('image/jpg', //上传文件类型列表
	'image/jpeg',
	'image/png',
	'image/gif',
	'image/x-png');
	$max_file_size=102400; //上传文件大小限制, 单位BYTE
	$path_parts=pathinfo($_SERVER['PHP_SELF']); //取得当前路径
	$destination_folder="../../../".Base_Upload."Product/product/";

								//上传文件路径

	 if ($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		if (!is_uploaded_file($_FILES["package_img"]["tmp_name"]))	//判断是否上传文件，是则不上传文件，使用旧文件
		{
		  $link_package_img = $configutil->splash_new($_POST['link_package_img']);
		  // echo  $destination;
		}else{
			$file = $_FILES["package_img"];
			if($max_file_size < $file["size"])
			//检查文件大小
			{
				echo "<script>alert('关联礼包图片太大！')</script>";
				echo "<script>history.go(-1);</script>";
			}
			if(!in_array($file["type"], $uptypes))
			//检查文件类型
			{
			  echo "<font color='red'>不能上传此类型文件！</font>";
			  exit;
			}
			if(!file_exists($destination_folder))
				mkdir($destination_folder,0777,true);

				$filename=$file["tmp_name"];

				$image_size = getimagesize($filename);

				$pinfo=pathinfo($file["name"]);

				$ftype=$pinfo["extension"];
				$link_package_img = $destination_folder.time().".".$ftype;
				$overwrite=true;

				if (file_exists($link_package_img) && $overwrite != true)
				{
				 echo "<font color='red'>同名文件已经存在了！</a>";
				 exit;
				}
				if(!_move_uploaded_file ($filename, $link_package_img))
				{
				 echo "<font color='red'>移动文件出错！</a>";
				 exit;
				}

				$link_package_img = str_replace("../","",$link_package_img);
				$link_package_img = "/wsy_prod/".$link_package_img;

		}
	}
}
//echo  $link_package;die;

/*  echo 'owner_general='.($owner_general).'<br>';
echo 'customer_ids='.($customer_ids).'<br>';
echo 'type_ids='.$type_ids.'<br>';
echo 'propertyids='.$propertyids.'<br>';  */
 //产品更新
 if($keyid>0){

     //外部标识--
     $pro_price_details = explode("-",$pro_price_detail);  //拆开 属性组合 和 价格等数据2部分
     $plen = count($pro_price_details);
     for($i=0;$i<$plen;$i++){
         $pro_price_item = $pro_price_details[$i];
         if(empty($pro_price_item)){
             continue;
         }
         $pro_price_items = explode(",",$pro_price_item);
         $proprices 	   	= $pro_price_items[1];
         $pprices 		= explode("_",$proprices);
         $p_n_foreign_mark = $pprices[5];
         if(!empty($p_n_foreign_mark)){
             $fm_num = 0;
             $query_fm_num ="select count(1) as num from weixin_commonshop_product_prices as pri INNER JOIN weixin_commonshop_products as pro on pri.product_id=pro.id where pri.customer_id=$c_id and pro.isvalid = true and pri.foreign_mark='".$p_n_foreign_mark."' and pri.product_id not in ($keyid)";
             $result_fm_num = _mysql_query($query_fm_num) or die('L231: Query_fm_num failed: ' . mysql_error());
             while ($row = mysql_fetch_object($result_fm_num)) {
                 $fm_num= $row->num;
             }
             $fm_num_m = 0;
             $query_fm_num_m ="select count(1) as num from weixin_commonshop_products where isvalid =true and customer_id=$c_id and foreign_mark='".$p_n_foreign_mark."' and id not in ($keyid)";
             $result_fm_num_m = _mysql_query($query_fm_num_m) or die('L231: Query_fm_num failed: ' . mysql_error());
             while ($row = mysql_fetch_object($result_fm_num_m)) {
                 $fm_num_m= $row->num;
             }
             if(0 < ($fm_num+$fm_num_m)){
                 echo "<script>alert('已存在的外部标识,请重新编辑属性外部标识');window.history.go(-1)</script>";
                 return;
             }
         }
     }
     //外部标识--

   $create_parent_id=-1;
   $fac_prices_ids = array();	//上级价格ID数组
   if(($owner_general and !empty($customer_ids)) or (!$owner_general)){
	  $carr = explode(",",$customer_ids);
	  foreach($carr as $keys=>$values){
		    $c_id = $values;
			$pid = -1;
			if(empty($c_id)){
				continue;
			}
			if($c_id==$customer_id){	//保存自己的产品
			   //厂家的产品更新,自己添加的产品也为-1
			   $create_type=-1;
			   if(!$owner_general){
				   //商家自己创建的是3；
				   $create_type=3;
			   }

			  if($foreign_mark != ""){
				  $fm_num = 0;
				  $query_fm_num ="select count(1) as num from weixin_commonshop_products where isvalid =true and customer_id=$c_id and foreign_mark='".$foreign_mark."' and id not in ($keyid)";
				  //echo $query_fm_num;
				  $result_fm_num = _mysql_query($query_fm_num) or die('L231: Query_fm_num failed: ' . mysql_error());
				  while ($row = mysql_fetch_object($result_fm_num)) {
					$fm_num= $row->num;
				  }
                  $fm_num_s = 0;
                  $query_fm_num ="select count(1) as num from weixin_commonshop_product_prices where customer_id=$c_id and foreign_mark='".$foreign_mark."' and product_id not in ($keyid)";

                  $result_fm_num = _mysql_query($query_fm_num) or die('L231: Query_fm_num failed: ' . mysql_error());
                  while ($row = mysql_fetch_object($result_fm_num)) {
                      $fm_num_s = $row->num;
                  }
				  if(0 < ($fm_num+$fm_num_s)){
					echo "<script>alert('已存在的外部标识,请重新编辑主产品外部标识');window.history.go(-1)</script>";
					return;
				  }
			  }

			  $sql="update weixin_commonshop_products set
			  donation_rate='".$donation_rate."',
			  asort_value=".$asort_value.",
			  default_imgurl='".$default_imgurl."',
			  class_imgurl='".$class_imgurl."',
			  unit='".$unit."',
			  type_ids='".$type_ids."',
			  pro_discount=".$pro_discount.",
			  pro_reward=".$pro_reward.",
			  name='".$name."',
			  orgin_price=".$orgin_price.",
			  now_price=".$now_price.",
			  vip_price=".$vip_price.",
			  storenum=".$storenum.",
			  need_score=".$need_score.",
			  cost_price=".$cost_price.",
			  for_price=".$for_price.",
			  foreign_mark='".$foreign_mark."',
			  introduce='".$introduce."',
			  short_introduce_color='".$short_introduce_color."',
			  remark='".$product_remark."',
			  description='".$description."',
			  specifications='".$specifications."',
			  customer_service='".$customer_service."',
			  asort=".$asort.",
			  isout=".$isout.",
			  isnew=".$isnew.",
			  ishot=".$ishot.",
			  issnapup=".$issnapup.",
			  isvp=".$isvp.",
			  vp_score='".$vp_score."',
			  tradeprices='".$tradeprices."',
			  propertyids='".$propertyids."',
			  show_sell_count=".$show_sell_count.",
			  define_share_image='".$define_share_image."',
			  isout_status=".$isout_status.",
			  create_type=".$create_type.",
			  install_price = '".$install_price."',
			  weight = '".$weight."',
			  is_QR = ".$is_QR.",
			  QR_isforever = ".$QR_isforever.",
			  QR_starttime = '".$QR_starttime."',
			  QR_endtime = '".$QR_endtime."',
			  pro_area = ".$city_id.",
			  agent_discount = ".$agent_discount.",
			  pro_card_level_id=".$pro_card_level_id.",
			  cashback='".$cashback."',
			  cashback_r='".$cashback_r."',
			  buystart_time='".$buystart_time."',
			  countdown_time='".$countdown_time."',
			  is_identity=".$is_identity.",
			  is_Pinformation=".$is_Pinformation.",
			  is_virtual=".$is_virtual.",
			  freight_id=".$freight_id.",
			  is_invoice=".$is_invoice.",
			  is_currency=".$is_currency.",
			  is_guess_you_like=".$is_guess_you_like.",
			  is_free_shipping=".$is_free_shipping.",
			  isscore=".$isscore.",
			  islimit=".$islimit.",
			  limit_num=".$limit_num.",
			  is_first_extend=".$is_first_extend.",
			  extend_money='".$extend_money."',
			  tax_type=".$tax_type.",
			  privilege_level='".$privilege_level."',
			  is_privilege=".$is_privilege.",
			  product_voice='".$product_voice."',
			  product_vedio='".$product_vedio."',
			  back_currency='".$back_currency."',
			  link_package=".$link_package.",
			  link_package_img='".$link_package_img."',
			  express_type=".$express_type.",
			  link_coupons='".$link_coupons."',
			  is_mini_mshop=".$is_mini_mshop.",
			  is_pickup=".$is_pickup."
			   where id=".$keyid;

				#echo "sql  : ".$sql." <br/>";return;
			  _mysql_query($sql)or die('L177: Query failed1: '. mysql_error());

			  //更新主属性图片
			  $set_attr_sql = 'update weixin_commonshop_product_attrimg set status = 0 where customer_id='.$customer_id.' and pro_id='.$keyid;
			  _mysql_query($set_attr_sql)or die('L547: Query failed1: '. mysql_error());

			  if(!empty($attr_img_arr)){
			  		foreach ($attr_img_arr as $key => $one_attr) {
			  			if(!empty($one_attr)){

				  			//one_attr:  'attrid_imgpath'
				  			$temp_attr_arr = explode('_',$one_attr);

				  			//2017.12.20   有些文件路径含有下划线，判断之后再组合为正确的路径
				  			if(count($temp_attr_arr)>2){
				  				$temp_attr_arr[1] = substr($one_attr,strpos($one_attr,'_')+1);
				  			}
				  			// echo $temp_attr_arr[1];

				  			$uni_attr_img  = 'select id from weixin_commonshop_product_attrimg where customer_id='.$customer_id.' and pro_id='.$keyid.' and attr_id='.$temp_attr_arr[0].' limit 1';

				  			$result_uni_attr = _mysql_query($uni_attr_img)or die('L553: Query failed1: ' . mysql_error());
				  			$temp_uni_id     = -1;
							while( $row_temp = mysql_fetch_object($result_uni_attr)){
								$temp_uni_id = $row_temp->id;
							}
							//数据库已经存在该产品属性数据
							if($temp_uni_id == -1){

								$int_sql = 'INSERT INTO weixin_commonshop_product_attrimg(customer_id,pro_id,attr_id,img,status,createtime) VALUES('.$customer_id.','.$keyid.','.$temp_attr_arr[0].',"'.$temp_attr_arr[1].'",1,now())';

								_mysql_query($int_sql)or die('Query failed L561: ' . mysql_error());
							}else{

								$upd_sql = 'UPDATE weixin_commonshop_product_attrimg SET img="'.$temp_attr_arr[1].'",status = 1 WHERE id='.$temp_uni_id;

								_mysql_query($upd_sql)or die('Query failed L564: ' . mysql_error());
							}
			  			}

			  		}

			  }

			  $time =date('Y-m-d H:i:s',time());
			  $sql2 = "update weixin_commonshop_supply_products set is_out =".$isout." where pid=".$keyid;
			  _mysql_query($sql2);

			  $Pkid = $keyid;
			  $pid = $keyid;
			  // echo $sql;return;
			  // echo '---------------------'.'<br>'; */

			  	  $tax_id = -1;
				  $query_tax = "SELECT id FROM weixin_commonshop_product_tax_detail WHERE product_id=$keyid LIMIT 1";
				  $result_tax= _mysql_query($query_tax)or die('L365: Query failed1: ' . mysql_error());
				  while( $row_tax = mysql_fetch_object($result_tax)){
				  	$tax_id = $row_tax->id;
				  }
				  if($tax_type > 1){
					  if($tax_id<0){
							$tax_sql = "INSERT INTO weixin_commonshop_product_tax_detail(product_id,tariff,comsumption,addedvalue,postal,is_dutyfree,premium) VALUES($keyid,$tariff,$comsumption,$addedvalue,$postal,$is_free_shipping,0)";
							_mysql_query($tax_sql)or die('L372: Query failed1: ' . mysql_error());
					  }elseif($tax_id>0){
							$tax_sql = "UPDATE weixin_commonshop_product_tax_detail SET tariff=$tariff,comsumption=$comsumption,addedvalue=$addedvalue,postal=$postal,is_dutyfree=$is_free_shipping WHERE product_id = $keyid";
							//echo $up_sql."111";die;
							_mysql_query($tax_sql)or die('L375: Query failed1: ' . mysql_error());
					  }
				  }
			  	if( $istax == 0 ){
			  		$query_d = "DELETE FROM weixin_commonshop_product_tax_detail WHERE product_id = $keyid";
			  		_mysql_query($query_d)or die('L378: Query failed1: ' . mysql_error());
			  	}

			  	/*---------记录批发选项--------*/
				$wholesale_id = $_POST['wholesale_id'];
				if($wholesale_id>0){

					$ptids = $_POST['ptids'];
					$ptids_str = "";

					if(!empty($ptids)){

						for($i=0;$i<count($ptids);$i++){
							$ptids_str .= $ptids[$i]."_";
						}
						$ptids_str = rtrim($ptids_str,"_");
						$id = -1;
						$query = "SELECT id FROM  weixin_commonshop_product_extend WHERE isvalid=true AND customer_id=$customer_id AND pid=$keyid LIMIT 1";
						$result= _mysql_query($query)or die('Query failed 32: ' . mysql_error());

						while( $row = mysql_fetch_object($result) ){
							$id = $row->id;
						}

						if( $id < 0 ){
							$sql = "INSERT INTO weixin_commonshop_product_extend(isvalid,customer_id,pid,wholesale_parentid,wholesale_childid) VALUES(true,$customer_id,$keyid,'$wholesale_id','$ptids_str')";
						}else{
							$sql = "UPDATE weixin_commonshop_product_extend SET wholesale_parentid='$wholesale_id',wholesale_childid='$ptids_str' WHERE pid=$keyid";
						}

						_mysql_query($sql)or die('Query failed 42: ' . mysql_error());
					}

				}else{
					$sql = "DELETE FROM weixin_commonshop_product_extend WHERE pid=$keyid";
					_mysql_query($sql)or die('Query failed 46: ' . mysql_error());
				}
				/*---------记录批发选项--------*/

			}else{
				//4M模式
				//查询自己所有下级产品

				//解决渠道修改总店身份后，以前的产品不同步问题--不加create_type查询
				$query="select id from weixin_commonshop_products where isvalid=true  and customer_id=".$c_id." and create_parent_id=".$keyid;
				//echo $query.'<br>';
				$result = _mysql_query($query) or die('L183: Query failed: ' . mysql_error());
		        while ($row = mysql_fetch_object($result)) {
					$pid= $row->id; 		//下级商家产品ID
					break;
				}

				//----拼接分类 start
				$type_id_arr = explode(",",$type_ids);
				$sub_type_ids = "";
				for($i=0;$i<count($type_id_arr);$i++){
					if($type_id_arr[$i] == ''){
						continue;
					}
					$type_id_f = $type_id_arr[$i];
					$query="select id from weixin_commonshop_types where customer_id=".$c_id." and isvalid=true and  create_parent_id=".$type_id_f;
					//echo $query.'<br>';
					$result = _mysql_query($query) or die('L203 :Query failed: ' . mysql_error());
					//$type_id_s=-1;
					$type_id_s='';
					while ($row = mysql_fetch_object($result)) {
						$type_id_s= $row->id;
						break;
					}

					if(empty($sub_type_ids)){
						$sub_type_ids=$type_id_s;
					}else{
						$sub_type_ids=$sub_type_ids.",".$type_id_s;
					}
				}
				//----拼接分类 end
			 /* echo "===========sub_type_ids=".$sub_type_ids."<br/>";  */

				//----拼接属性 start
				$pro_id_arr = explode("_",$propertyids);
				$pro_type_ids = "";
				for($i=0;$i<count($pro_id_arr);$i++){
					$pro_id = $pro_id_arr[$i];
					if(empty($pro_id)){
						continue;
					}
					$query="select id from weixin_commonshop_pros where customer_id=".$c_id." and isvalid=true and  create_parent_id=".$pro_id;
					$result = _mysql_query($query) or die('L243 :Query failed: ' . mysql_error());
					//$type_id_s=-1;
					$type_id_s='';
					while ($row = mysql_fetch_object($result)) {
						$type_id_s= $row->id;
						break;
					}

					if(empty($pro_type_ids)){
						$pro_type_ids=$type_id_s;
					}else{
						$pro_type_ids=$pro_type_ids."_".$type_id_s;
					}
				}

				$sql="update weixin_commonshop_products set
				donation_rate='".$donation_rate."',
				asort_value=".$asort_value.",
				default_imgurl='".$default_imgurl."',
				class_imgurl='".$class_imgurl."',
				unit='".$unit."',
				type_ids='".$sub_type_ids."',
				pro_discount=".$pro_discount.",
				pro_reward=".$pro_reward.",
				name='".$name."',
				orgin_price=".$orgin_price.",
				now_price=".$now_price.",
				vip_price=".$vip_price.",
				storenum=".$storenum.",
				need_score=".$need_score.",
				cost_price=".$cost_price.",
				for_price=".$for_price.",
				foreign_mark='".$foreign_mark."',
				introduce='".$introduce."',
				short_introduce_color='".$short_introduce_color."',
				remark='".$product_remark."',
				description='".$description."',
				specifications='".$specifications."',
				customer_service='".$customer_service."',
				asort=".$asort.",
				isout=".$isout.",
				isnew=".$isnew.",
				isvp=".$isvp.",
				vp_score='".$vp_score."',
				ishot=".$ishot.",
				issnapup=".$issnapup.",
				tradeprices='".$tradeprices."',
				propertyids='".$pro_type_ids."',
				show_sell_count=".$show_sell_count.",
				define_share_image='".$define_share_image."',
				isout_status=".$isout_status.",
				install_price = '".$install_price."',
				weight = ".$weight.",
				is_QR = ".$is_QR.",
				QR_isforever = ".$QR_isforever.",
				QR_starttime = '".$QR_starttime."',
				QR_endtime = '".$QR_endtime."',
				pro_area = ".$city_id.",
				agent_discount = ".$agent_discount.",
				pro_card_level_id=".$pro_card_level_id.",
				cashback='".$cashback."',
				cashback_r='".$cashback_r."',
				buystart_time='".$buystart_time."',
				countdown_time='".$countdown_time."',
				is_identity=".$is_identity.",
				is_Pinformation=".$is_Pinformation.",
			    is_virtual=".$is_virtual.",
			    freight_id=".$freight_id.",
				is_invoice=".$is_invoice.",
				is_currency=".$is_currency.",
				is_guess_you_like=".$is_guess_you_like.",
				is_free_shipping=".$is_free_shipping.",
				isscore=".$isscore.",
				islimit=".$islimit.",
				limit_num=".$limit_num.",
				is_first_extend=".$is_first_extend.",
				extend_money='".$extend_money."',
				tax_type=".$tax_type.",
				privilege_level='".$privilege_level."',
				is_privilege='".$is_privilege."',
				product_voice='".$product_voice."',
				product_vedio='".$product_vedio."',
				back_currency=".$back_currency.",
				link_package=".$link_package_img.",
				link_package_img='".$link_package_img."',
				express_type=".$express_type.",
			    is_mini_mshop=".$is_mini_mshop.",
			    is_pickup=".$is_pickup."
			     where id=".$pid;

				//echo $sql.'<br>';
				$time =date('Y-m-d H:i:s',time());
				$sql2 = "update weixin_commonshop_supply_products set is_out =".$isout." where pid=".$pid;
				_mysql_query($sql2);
				/*echo "============1end================<br/>";  */

			}
			if($pid==-1){
				//没有找到4M 下级商家，往下继续寻找
				continue;
			}
			  $tax_id = -1;
			  $query_tax = "SELECT id FROM weixin_commonshop_product_tax_detail WHERE product_id=$pid LIMIT 1";
			  //echo $query_tax;
			  $result_tax= _mysql_query($query_tax)or die('L365: Query failed1: ' . mysql_error());
			  while( $row_tax = mysql_fetch_object($result_tax)){
			  	$tax_id = $row_tax->id;
			  }
			  if($tax_type > 1){
				  if($tax_id<0){
						$tax_sql = "INSERT INTO weixin_commonshop_product_tax_detail(product_id,tariff,comsumption,addedvalue,postal,is_dutyfree,premium) VALUES($keyid,$tariff,$comsumption,$addedvalue,$postal,$is_free_shipping,0)";
						_mysql_query($tax_sql)or die('L372: Query failed1: ' . mysql_error());
				  }elseif($tax_id>0){
						$tax_sql = "UPDATE weixin_commonshop_product_tax_detail SET tariff=$tariff,comsumption=$comsumption,addedvalue=$addedvalue,postal=$postal,is_dutyfree=$is_free_shipping WHERE product_id = $pid";
						//echo $up_sql."111";die;
						_mysql_query($tax_sql)or die('L375: Query failed1: ' . mysql_error());
				  }
			  }
			  if( $istax == 0 ){
			  		$query_d = "DELETE FROM weixin_commonshop_product_tax_detail WHERE product_id = $keyid";
			  		_mysql_query($query_d)or die('L378: Query failed1: ' . mysql_error());
			  	}

			  	/*---------记录批发选项--------*/
				$wholesale_id = $_POST['wholesale_id'];
				if($wholesale_id>0){

					$ptids = $_POST['ptids'];
					$ptids_str = "";

					if(!empty($ptids)){

						for($i=0;$i<count($ptids);$i++){
							$ptids_str .= $ptids[$i]."_";
						}
						$ptids_str = rtrim($ptids_str,"_");
						$id = -1;
						$query = "SELECT id FROM  weixin_commonshop_product_extend WHERE isvalid=true AND customer_id=$customer_id AND pid=$keyid LIMIT 1";
						$result= _mysql_query($query)or die('Query failed 32: ' . mysql_error());

						while( $row = mysql_fetch_object($result) ){
							$id = $row->id;
						}

						if( $id < 0 ){
							$sql = "INSERT INTO weixin_commonshop_product_extend(isvalid,customer_id,pid,wholesale_parentid,wholesale_childid) VALUES(true,$customer_id,$keyid,'$wholesale_id','$ptids_str')";
						}else{
							$sql = "UPDATE weixin_commonshop_product_extend SET wholesale_parentid='$wholesale_id',wholesale_childid='$ptids_str' WHERE pid=$keyid";
						}

						_mysql_query($sql)or die('Query failed 42: ' . mysql_error());
					}

				}else{
					$sql = "DELETE FROM weixin_commonshop_product_extend WHERE pid=$keyid";
					_mysql_query($sql)or die('Query failed 46: ' . mysql_error());
				}
				/*---------记录批发选项--------*/

			_mysql_query($sql)or die('Query failed1: ' . mysql_error());

			$Pkid = $pid;
			//清除图片

			//插入修改运费模板日志 start
			$query="select id,express_id from weixin_commonshop_product_express_logs where isvalid=true and customer_id=".$customer_id." and pid=".$pid." order by id desc limit 0,1";
			$e_log_id 		  = -1;
			$e_log_express_id = -1;
		    $result = _mysql_query($query) or die('Query failed_express_logs2: ' . mysql_error());
		    while ($row = mysql_fetch_object($result)) {
			   $e_log_id 		  =	$row->id;
			   $e_log_express_id  = $row->express_id;
		    }
			// 如果有修改,则插入修改记录
			if($e_log_express_id != $freight_id){
				$sql_elog="insert into weixin_commonshop_product_express_logs(pid,express_id,isvalid,createtime,customer_id,operation,operation_user) values(".$pid.",".$freight_id.",true,now(),".$customer_id.",1,'".$_SESSION['username']."')";
				_mysql_query($sql_elog)or die('W945 : Query failed1-2: ' . mysql_error());
			}
			//插入修改运费模板日志 end*/

			//----插入图片 start
			if($imgids!=""){
				$imgidarr = explode(";",$imgids);
				for($i=0;$i<count($imgidarr);$i++){
				   $imgid = $imgidarr[$i];
				    //上传时不再添加到数据库，现改为在保存时统一提交
				   $sql="insert into weixin_commonshop_product_imgs(product_id,imgurl,isvalid,customer_id) values(".$pid.",'".$imgid."',true,".$c_id.")";
				   _mysql_query($sql)or die('L297 : Query failed1: ' . mysql_error());
				}
			}
			if ($img_link != '') {
                $sql="insert into weixin_commonshop_product_imgs(product_id,imgurl,isvalid,customer_id,3d_link) values(".$pid.",'".$img_3d."',true,".$c_id.",'".$img_link."')";
                _mysql_query($sql)or die('L297 : Query failed1: ' . mysql_error());
            }

			//----插入图片 end

			//----插入属性价格 start

			$pro_price_details = explode("-",$pro_price_detail);  //拆开 属性组合 和 价格等数据2部分
			$plen = count($pro_price_details);
			if( $plen > 0 ){
				$query= "select proids from weixin_commonshop_product_prices where product_id=".$pid;
				$result = _mysql_query($query) or die('L308 :Query failed: ' . mysql_error());
				$rpocount = 0;
				$ppLst = new ArrayList();
				while ($row = mysql_fetch_object($result)) {
					$proids = $row->proids;
					$ppLst->Add($proids);
				}
				$query=" delete from weixin_commonshop_product_prices where product_id=".$pid;
				_mysql_query($query)or die('L318 :Query failed1: ' . mysql_error());
			}
			//var_dump($pro_price_details);
			$fm_num2 = 0;

			//zhou 删除属性时对应商城产品属性值
			for($i=0;$i<$plen;$i++)//获取属性字符串
			{
				$pro_price_item = $pro_price_details[$i];
				if(empty($pro_price_item)){
				   continue;
				}
				$pro_price_items = explode(",",$pro_price_item);
				$proids_arr[] = $pro_price_items[0];
			}

			foreach($proids_arr as $k=>$v)//把属性字符串变成数组
			{
				$aa[$k] = explode('_', $proids_arr[$k]);
			}

			foreach ($aa as $k => $v) //把重复的数组去掉
			{
				foreach($v as $a=>$b)
				{
					$new_proids_arr[$b] = $b;
				}
			}

			$new_ptids    = $_POST['ptids'];//获取批量属性

			$last_proids  = array_diff($new_proids_arr,$new_ptids); //去除批量属性
			sort($last_proids);
			$la_proid_str = implode('_', $last_proids);

			if($la_proid_str != false)//更新产品属性值
			{
				$up_query     = "update weixin_commonshop_products set propertyids = '".$la_proid_str."' where customer_id = $customer_id and id = $pid";
				_mysql_query($up_query) or die('mysql error:'.mysql_error());
			}
			//zhou

			for($i=0;$i<$plen;$i++){
				//echo '/*==============价格更新开始=================*/<br>';

				$pro_price_item = $pro_price_details[$i];
				if(empty($pro_price_item)){
				   continue;
				}

				$pro_price_items = explode(",",$pro_price_item);
				//var_dump($pro_price_items);
				$pilen = count($pro_price_items);
				$proids =$pro_price_items[0];
				$fac_proids = '';				//上级属性组合
				$fac_proids = $proids;

				/*4M模式 start*/
				if($c_id!=$customer_id){
					//echo '/*===4M价格 开始===*/<br>';
					//查找4M下级的真实的属性编号
					$pro_id_arr = explode("_",$proids);
					$proids = "";
					for($j=0;$j<count($pro_id_arr);$j++){
						$pro_id = $pro_id_arr[$j];
						if(empty($pro_id)){
							continue;
						}
						//$query="select id from weixin_commonshop_pros where customer_id=".$c_id." and isvalid=true and  create_parent_id=".$pro_id; by ye 2017-1-5
						$query="select id from weixin_commonshop_pros where customer_id=".$c_id." and  create_parent_id=".$pro_id;
						//echo $query.'<br>';
						$result = _mysql_query($query) or die('L340 : Query failed: ' . mysql_error());
						$pro_id_s=-1;
						while ($row = mysql_fetch_object($result)) {
							$pro_id_s= $row->id;
							break;
						}
						if(empty($proids)){
							$proids=$pro_id_s;
						}else{
							$proids=$proids."_".$pro_id_s;
						}

					}

					//echo '/*===4M价格 结束===*/<br>';
				}/*4M模式 end*/
				 $is_added = false;
				if($ppLst->Contains($proids)){
					$is_added =true;
				}

				$proprices 	   	= $pro_price_items[1];


				$pprices 		= explode("_",$proprices);
				$pplen 			= count($pprices);

				$p_o_price 		  = $pprices[0];
				$p_n_price 		  = $pprices[1];
				$p_v_price 		  = $pprices[2];
				$p_n_storenum 	  = $pprices[3];
				$p_n_need_score   = $pprices[4];
				$p_n_cost_price   = $pprices[5];
				$p_n_foreign_mark = $pprices[6];
				$p_n_unit 		  = $pprices[7];
				$p_n_weight 	  = $pprices[8];
				$p_n_for_price 	  = $pprices[9];

				$p_o_price 		  = round($p_o_price, 2);
				$p_n_price 		  = round($p_n_price, 2);
				$p_v_price 		  = round($p_v_price, 2);
				$p_n_cost_price   = round($p_n_cost_price, 2);
				$p_n_for_price    = round($p_n_for_price, 2);

				/* if($p_n_unit!=""){
				  $p_unit = $p_n_unit;
				}

				if($p_n_weight!="" or ($p_n_weight=="0" and $is_added)){
				  $p_weight = $p_n_weight;
				}

				if($p_o_price!="" or ($p_o_price=="0" and $is_added)){
				  $p_orgin_price = $p_o_price;
				}
				if($p_n_price!="" or ($p_n_price=="0" and $is_added )){
				  $p_now_price = $p_n_price;
				}

				if($p_n_storenum!="" or ($p_n_storenum=="0" and $is_added)){
				  $p_storenum = $p_n_storenum;
				}

				if($p_n_need_score!="" or ($p_n_need_score=="0" and $is_added )){
				  $p_need_score = $p_n_need_score;
				}
				if($p_n_cost_price!="" or ($p_n_cost_price=="0" and $is_added )){
				  $p_cost_price = $p_n_cost_price;
				}
				if($p_n_for_price!="" or ($p_n_for_price=="0" and $is_added )){
					$p_for_price = $p_n_for_price;
				}
				 if($p_n_foreign_mark!=""){
				  $p_foreign_mark = $p_n_foreign_mark;
				} */

				if($p_n_foreign_mark != ""){
					$query_fm_num2 ="select count(1) as num2 from weixin_commonshop_product_prices as pri
									 LEFT JOIN weixin_commonshop_products as pro on pri.product_id=pro.id
									 where pri.foreign_mark='" . $p_n_foreign_mark . "' and pro.customer_id = ".$c_id;
					//echo $query_fm_num2;
					$result_fm_num2 = _mysql_query($query_fm_num2) or die('L616: Query_fm_num2 failed: ' . mysql_error());
						while ($row = mysql_fetch_object($result_fm_num2)) {
						$fm_num_2= $row->num2;
						$fm_num2 += $fm_num_2;
					}
				}
				/*4M 设置上级 start*/
				$prices_parent_id = -1;
				if($c_id!=$customer_id){
					$prices_parent_id = $fac_prices_ids[$i];
				}
				if(empty($owner_general)){
					$price_create_type = 3;
				}else{
					$price_create_type = $owner_general;
				}
				/*4M 设置上级 end*/
				$query="insert into weixin_commonshop_product_prices(
				product_id,
				proids,
				orgin_price,
				now_price,
				vip_price,
				storenum,
				need_score,
				cost_price,
				unit,
				foreign_mark,
				weight,
				for_price,
				create_type,
				create_parent_id,
                customer_id
				) values(
				".$pid.",
				'".$proids."',
				".$p_o_price.",
				".$p_n_price.",
				".$p_v_price.",
				".$p_n_storenum.",
				'".$p_n_need_score."',
				".$p_n_cost_price.",
				'".$p_n_unit."',
				'".$p_n_foreign_mark."',
				'".$p_n_weight."',
				".$p_n_for_price.",
				".$price_create_type.",
				".$prices_parent_id.",
				".$customer_id.")";

				//echo $query.'<br>';
				_mysql_query($query)or die('L420 : Query failed128: ' . mysql_error());
				/*4M 设置上级 start*/
				$fac_proids_partid = mysql_insert_id();
				if($c_id==$customer_id){	//当是上级
					array_push($fac_prices_ids,$fac_proids_partid);
				}
				/*4M 设置上级 end*/
				//echo '/*==============价格更新 结束=================*/<br>';
			}
			if(0 < $fm_num2){
				// echo "<script>alert('属性价格存在已使用的外部标识，请检查确保正确！');</script>";
			}

			//----插入属性价格 end

	  }//产品循环结束 end
	}

 }else{

     //外部标识--
     $pro_price_details = explode("-",$pro_price_detail);  //拆开 属性组合 和 价格等数据2部分
     $plen = count($pro_price_details);
     for($i=0;$i<$plen;$i++){
         $pro_price_item = $pro_price_details[$i];
         if(empty($pro_price_item)){
             continue;
         }
         $pro_price_items = explode(",",$pro_price_item);
         $proprices 	   	= $pro_price_items[1];
         $pprices 		= explode("_",$proprices);
         $p_n_foreign_mark = $pprices[5];
         if(!empty($p_n_foreign_mark)){
             $fm_num = 0;
             $query_fm_num ="select count(1) as num from weixin_commonshop_product_prices as pri INNER JOIN weixin_commonshop_products as pro on pri.product_id=pro.id where pri.customer_id=$c_id and pro.isvalid = true and pri.foreign_mark='".$p_n_foreign_mark."'";
             $result_fm_num = _mysql_query($query_fm_num) or die('L231: Query_fm_num failed: ' . mysql_error());
             while ($row = mysql_fetch_object($result_fm_num)) {
                 $fm_num= $row->num;
             }
             $fm_num_m = 0;
             $query_fm_num_m ="select count(1) as num from weixin_commonshop_products where isvalid =true and customer_id=$c_id and foreign_mark='".$p_n_foreign_mark."'";
             $result_fm_num_m = _mysql_query($query_fm_num_m) or die('L231: Query_fm_num failed: ' . mysql_error());
             while ($row = mysql_fetch_object($result_fm_num_m)) {
                 $fm_num_m= $row->num;
             }
             if(0 < ($fm_num+$fm_num_m)){
                 echo "<script>alert('已存在的外部标识,请重新编辑属性外部标识');window.history.go(-1)</script>";
                 return;
             }
         }
     }
     //外部标识--

	 $create_parent_id=-1;
	 $fac_prices_ids = array();	//上级价格ID数组
	 if(($owner_general and !empty($customer_ids)) or (!$owner_general)){
		$carr = explode(",",$customer_ids);
		// var_dump($customer_ids);
		foreach($carr as $keys=>$values){
		    $c_id = $values;
			if(empty($c_id)){
				continue;
			}
			$p_id=-1;
		    if($c_id==$customer_id){
				//厂家上传的
				$create_type=-1;
			    if(!$owner_general){
				   //商家自己创建的是3；
				   $create_type=3;
			    }

				if($foreign_mark != ""){
					$fm_num = 0;
					$query_fm_num ="select count(1) as num from weixin_commonshop_products where isvalid =true and customer_id=$c_id and foreign_mark='".$foreign_mark."'";
					// echo $query_fm_num;
					$result_fm_num = _mysql_query($query_fm_num) or die('L231: Query_fm_num failed: ' . mysql_error());
						while ($row = mysql_fetch_object($result_fm_num)) {
						$fm_num= $row->num;
					}
                    $fm_num_s = 0;
                    // $query_fm_num ="select count(1) as num from weixin_commonshop_product_prices where customer_id=$c_id and foreign_mark='".$foreign_mark."'";
                    $query_fm_num ="select count(1) as num from weixin_commonshop_product_prices as pri INNER JOIN weixin_commonshop_products as pro on pri.product_id=pro.id where pri.customer_id=$c_id and pro.isvalid = true and pri.foreign_mark='".$foreign_mark."'";
                    
                    $result_fm_num = _mysql_query($query_fm_num) or die('L231: Query_fm_num failed: ' . mysql_error());
                    while ($row = mysql_fetch_object($result_fm_num)) {
                        $fm_num_s = $row->num;
                    }
                    if(0 < ($fm_num+$fm_num_s)){
						echo "<script>alert('已存在的外部标识,请重新编辑主产品外部标识');window.history.go(-1)</script>";
						return;
					}
				}

				$sql="insert into weixin_commonshop_products(
				donation_rate,
				name,
				unit,
				type_id,
				orgin_price,
				now_price,
				vip_price,
				storenum,
				need_score,
				introduce,
				short_introduce_color,
				remark,
				description,
				specifications,
				customer_service,
				asort,
				isout,
				isnew,
				ishot,
				issnapup,
				isvp,
				vp_score,
				customer_id,
				isvalid,
				createtime,
				tradeprices,
				propertyids,
				pro_discount,
				pro_reward,
				type_ids,
				default_imgurl,
				class_imgurl,
				cost_price,
				for_price,
				foreign_mark,
				asort_value,
				show_sell_count,
				define_share_image,
				isout_status,
				create_type,
				create_parent_id,
				install_price,
				weight,
				agent_discount,
				auth_users,
				nowprice_title,
				is_QR,
				QR_isforever,
			    QR_starttime,
			    QR_endtime,
				pro_card_level_id,
				cashback,
				cashback_r,
				pro_area,
				buystart_time,
				countdown_time,
				is_identity,
				is_Pinformation,
				is_virtual,
				freight_id,
				is_invoice,
				is_currency,
				is_guess_you_like,
				is_free_shipping,
				isscore,
				islimit,
				limit_num,
				is_first_extend,
				extend_money,
				product_voice,
				product_vedio,
				back_currency,
				link_package,
				link_package_img,
				express_type,
				tax_type,
				privilege_level,
				is_privilege,
				link_coupons,
				is_mini_mshop,
				is_pickup
				)";
				$sql=$sql." values(
				'".$donation_rate."',
				'".$name."',
				'".$unit."',
				'".$type_id."',
				'".$orgin_price."',
				'".$now_price."',
				'".$vip_price."',
				'".$storenum."',
				'".$need_score."',
				'".$introduce."',
				'".$short_introduce_color."',
				'".$product_remark."',
				'".$description."',
				'".$specifications."',
				'".$customer_service."',
				'".$asort."',
				".$isout.",
				".$isnew.",
				".$ishot.",
				".$issnapup.",
				".$isvp.",
				'".$vp_score."',
				'".$c_id."',
				true,
				now(),
				'".$tradeprices."',
				'".$propertyids."',
				'".$pro_discount."',
				'".$pro_reward."',
				'".$type_ids."',
				'".$default_imgurl."',
				'".$class_imgurl."',
				'".$cost_price."',
				'".$for_price."',
				'".$foreign_mark."',
				'".$asort_value."',
				'".$show_sell_count."',
				'".$define_share_image."',
				'".$isout_status."',
				'".$create_type."',
				-1,
				'".$install_price."',
				'".$weight."',
				'".$agent_discount."',
				'".$auth_user_id."',
				'".$nowprice_title."',
				'".$is_QR."',
				'".$QR_isforever."',
			    '".$QR_starttime."',
			    '".$QR_endtime."',
				'".$pro_card_level_id."',
				'".$cashback."',
				'".$cashback_r."',
				'".$city_id."',
				'".$buystart_time."',
				'".$countdown_time."',
				".$is_identity.",
				".$is_Pinformation.",
				'".$is_virtual."',
				'".$freight_id."',
				'".$is_invoice."',
				'".$is_currency."',
				'".$is_guess_you_like."',
				".$is_free_shipping.",
				".$isscore.",
				".$islimit.",
				'".$limit_num."',
				".$is_first_extend.",
				'".$extend_money."',
				'".$product_voice."',
				'".$product_vedio."',
				'".$back_currency."',
				'".$link_package."',
				'".$link_package_img."',
				'".$express_type."',
				'".$tax_type."',
				'".$privilege_level."',
				".$is_privilege.",
				'".$link_coupons."',
				".$is_mini_mshop.",
				".$is_pickup."
				)";
// echo $sql;
// die;
				_mysql_query($sql)or die('L445 : Query failed321: ' . mysql_error());
				$error =mysql_error();

				$p_id = mysql_insert_id();
				$Pkid = $p_id;

				$keyid= $p_id;
				//更新主属性图片
				$set_attr_sql = 'update weixin_commonshop_product_attrimg set status = 0 where customer_id='.$customer_id.' and pro_id='.$keyid;
				_mysql_query($set_attr_sql)or die('L547: Query failed1: '. mysql_error());

				if(!empty($attr_img_arr)){
					foreach ($attr_img_arr as $key => $one_attr) {
						if(!empty($one_attr)){

			  			//one_attr:  'attrid_imgpath'
			  			$temp_attr_arr = explode('_',$one_attr);

			  			//2018.7.30 CRM16037 有些文件路径含有下划线，判断之后再组合为正确的路径
				  		if(count($temp_attr_arr)>2){
			  				$temp_attr_arr[1] = substr($one_attr,strpos($one_attr,'_')+1);
				  		}

			  			$uni_attr_img  = 'select id from weixin_commonshop_product_attrimg where customer_id='.$customer_id.' and pro_id='.$keyid.' and attr_id='.$temp_attr_arr[0].' limit 1';

			  			$result_uni_attr = _mysql_query($uni_attr_img)or die('L553: Query failed1: ' . mysql_error());
			  			$temp_uni_id     = -1;
						while( $row_temp = mysql_fetch_object($result_uni_attr)){
							$temp_uni_id = $row_temp->id;
						}

						//数据库已经存在该产品属性数据
						if($temp_uni_id == -1){

							$int_sql = 'INSERT INTO weixin_commonshop_product_attrimg(customer_id,pro_id,attr_id,img,status,createtime) VALUES('.$customer_id.','.$keyid.','.$temp_attr_arr[0].',"'.$temp_attr_arr[1].'",1,now())';

							_mysql_query($int_sql)or die('Query failed L561: ' . mysql_error());
						}else{

							$upd_sql = 'UPDATE weixin_commonshop_product_attrimg SET img="'.$temp_attr_arr[1].'",status = 1 WHERE id='.$temp_uni_id;

							_mysql_query($upd_sql)or die('Query failed L564: ' . mysql_error());
						}
						}

					}

				}

				$create_parent_id = $p_id;
				//echo "1124=".$sql.'<br>';die;
				if($istax == 1){
					$ins_sql = "INSERT INTO weixin_commonshop_product_tax_detail(product_id,tariff,comsumption,addedvalue,postal,is_dutyfree,premium) VALUES($p_id,$tariff,$comsumption,$addedvalue,$postal,$is_free_shipping,0)";
			  		//echo $ins_sql;die;
			  		_mysql_query($ins_sql)or die('L372: Query failed1: ' . mysql_error());
				}

				/*---------记录批发选项--------*/
				$wholesale_id = $_POST['wholesale_id'];
				if($wholesale_id>0){

					$ptids = $_POST['ptids'];
					$ptids_str = "";

					if(!empty($ptids)){

						for($i=0;$i<count($ptids);$i++){
							$ptids_str .= $ptids[$i]."_";
						}
						$ptids_str = rtrim($ptids_str,"_");
						$sql = "INSERT INTO weixin_commonshop_product_extend(isvalid,customer_id,pid,wholesale_parentid,wholesale_childid) VALUES(true,$customer_id,$p_id,'$wholesale_id','$ptids_str')";
						_mysql_query($sql)or die('Query failed 42: ' . mysql_error());
					}
				}
				/*---------记录批发选项--------*/

			}else{

				//找到下级对应的类型编号
				/* $query="select id from weixin_commonshop_types where customer_id=".$c_id." and isvalid=true and  create_parent_id=".$type_id;

				$result = _mysql_query($query) or die('L454 : Query failed: ' . mysql_error());
				$sub_type_id=-1;
				while ($row = mysql_fetch_object($result)) {
					$sub_type_id= $row->id;
					break;
				} */

				$type_id_arr = explode(",",$type_ids);

				$sub_type_ids = "";
				for($i=0;$i<count($type_id_arr);$i++){

					if(empty($type_id_arr[$i] )){
						continue;
					}
					$type_id = $type_id_arr[$i];
					$query="select id from weixin_commonshop_types where customer_id=".$c_id." and isvalid=true and  create_parent_id=".$type_id;

					$result = _mysql_query($query) or die('L466 : Query failed8: ' . mysql_error());
					//$type_id_s=-1;
					$type_id_s='';
					while ($row = mysql_fetch_object($result)) {
						$type_id_s= $row->id;
						break;
					}

					if(empty($sub_type_ids)){
						$sub_type_ids=$type_id_s;
					}else{
						$sub_type_ids=$sub_type_ids.",".$type_id_s;
					}


				}

				$pro_id_arr = explode("_",$propertyids);
				$pro_type_ids = "";
				for($i=0;$i<count($pro_id_arr);$i++){
					$pro_id = $pro_id_arr[$i];
					if(empty($pro_id)){
						continue;
					}
					$query="select id from weixin_commonshop_pros where customer_id=".$c_id." and isvalid=true and  create_parent_id=".$pro_id;
					$result = _mysql_query($query) or die('L507 : Query failed5: ' . mysql_error());
					//$type_id_s=-1;
					$type_id_s='';
					while ($row = mysql_fetch_object($result)) {
						$type_id_s= $row->id;
						break;
					}

					if(empty($pro_type_ids)){
						$pro_type_ids=$type_id_s;
					}else{
						$pro_type_ids=$pro_type_ids."_".$type_id_s;
					}
				}

				$sql="insert into weixin_commonshop_products(
				donation_rate,
				name,
				unit,
				type_id,
				orgin_price,
				now_price,
				vip_price,
				storenum,
				need_score,
				introduce,
				short_introduce_color,
				remark,
				description,
				specifications,
				customer_service,
				asort,
				isout,
				isnew,
				ishot,
				issnapup,
				isvp,
				vp_score,
				customer_id,
				isvalid,
				createtime,
				tradeprices,
				propertyids,
				pro_discount,
				pro_reward,
				type_ids,
				default_imgurl,
				class_imgurl,
				cost_price,
				for_price,
				foreign_mark,
				asort_value,
				show_sell_count,
				define_share_image,
				isout_status,
				create_type,
				create_parent_id,
				install_price,
				weight,
				agent_discount,
				auth_users,
				nowprice_title,
				is_QR,
				QR_isforever,
			    QR_starttime,
			    QR_endtime,
				pro_card_level_id,
				cashback,
				cashback_r,
				pro_area,
				buystart_time,
				countdown_time,
				is_identity,
				is_Pinformation,
				is_virtual,
				freight_id,
				is_invoice,
				is_currency,
				is_guess_you_like,
				is_free_shipping,
				isscore,
				islimit,
				limit_num,
				is_first_extend,
				extend_money,
				product_voice,
				product_vedio,
				back_currency,
				link_package,
				link_package_img,
				express_type,
				tax_type,
				privilege_level,
				is_privilege,
				link_coupons,
				is_mini_mshop,
				is_pickup
				)";
				$sql=$sql." values(
				'".$donation_rate."',
				'".$name."',
				'".$unit."',
				".$type_id.",
				".$orgin_price.",
				".$now_price.",
				".$vip_price.",
				".$storenum.",
				".$need_score.",
				'".$introduce."',
				'".$short_introduce_color."',
				'".$product_remark."',
				'".$description."',
				'".$specifications."',
				'".$customer_service."',
				".$asort.",
				".$isout.",
				".$isnew.",
				".$ishot.",
				".$issnapup.",
				".$isvp.",
				'".$vp_score."',".$c_id.",
				true,
				now(),
				'".$tradeprices."',
				'".$pro_type_ids."',
				".$pro_discount.",
				".$pro_reward.",
				'".$sub_type_ids."',
				'".$default_imgurl."',
				'".$class_imgurl."',
				".$cost_price.",
				".$for_price.",
				'".$foreign_mark."',
				".$asort_value.",
				".$show_sell_count.",
				'".$define_share_image."',
				".$isout_status.",
				".$owner_general.",
				".$create_parent_id.",
				'".$install_price."',
				".$weight.",
				".$agent_discount.",
				".$auth_user_id.",
				'".$nowprice_title."',
				".$is_QR.",
				".$QR_isforever.",
			    '".$QR_starttime."',
			    '".$QR_endtime."',
				".$pro_card_level_id.",
				'".$cashback."',
				'".$cashback_r."',
				".$city_id.",
				'".$buystart_time."',
				'".$countdown_time."',
				".$is_identity.",
				".$is_Pinformation.",
				".$is_virtual.",
				".$freight_id.",
				".$is_invoice.",
				".$is_currency.",
				".$is_guess_you_like.",
				".$is_free_shipping.",
				".$isscore.",
				".$islimit.",
				".$limit_num.",
				".$is_first_extend.",
				'".$extend_money."',
				'".$product_voice."',
				'".$product_vedio."',
				".$back_currency.",
				".$link_package.",
				'".$link_package_img."',
				".$express_type.",
				".$tax_type.",
				'".$privilege_level."',
				".$is_privilege.",
				'".$link_coupons."',
				".$is_mini_mshop.",
				".$is_pickup."
				)";
				//echo "1360".$sql;die;
				// echo $sql.'<br>';
				_mysql_query($sql)or die('L540 : Query failed123: ' . mysql_error());
				$error =mysql_error();
				$p_id = mysql_insert_id();
				$Pkid = $p_id;
				if($istax == 1){
					$ins_sql = "INSERT INTO weixin_commonshop_product_tax_detail(product_id,tariff,comsumption,addedvalue,postal,is_dutyfree,premium) VALUES($p_id,$tariff,$comsumption,$addedvalue,$postal,$is_free_shipping,0)";
			  		//echo $ins_sql;die;
			  		_mysql_query($ins_sql)or die('L372: Query failed1: ' . mysql_error());
				}

				/*---------记录批发选项--------*/
				$wholesale_id = $_POST['wholesale_id'];
				if($wholesale_id>0){

					$ptids = $_POST['ptids'];
					$ptids_str = "";

					if(!empty($ptids)){

						for($i=0;$i<count($ptids);$i++){
							$ptids_str .= $ptids[$i]."_";
						}
						$ptids_str = rtrim($ptids_str,"_");
						$sql = "INSERT INTO weixin_commonshop_product_extend(isvalid,customer_id,pid,wholesale_parentid,wholesale_childid) VALUES(true,$customer_id,$p_id,'$wholesale_id','$ptids_str')";
						_mysql_query($sql)or die('Query failed 42: ' . mysql_error());
					}
				}
				/*---------记录批发选项--------*/

			}

			//插入4M产品控制权限表
			$u4m->insert_control_product_4m($create_parent_id,$c_id,$p_id);

			//插入修改运费模板日志 start
			$query="select id,express_id from weixin_commonshop_product_express_logs where isvalid=true and customer_id=".$c_id." and pid=".$p_id." order by id desc limit 0,1";
			$e_log_id 		  = -1;
			$e_log_express_id = -1;
		    $result = _mysql_query($query) or die('Query failed_express_logs2: ' . mysql_error());
		    while ($row = mysql_fetch_object($result)) {
			   $e_log_id 		  =	$row->id;
			   $e_log_express_id  = $row->express_id;
		    }
			// 如果有修改,则插入修改记录
			if($e_log_express_id != $freight_id){
				$sql_elog="insert into weixin_commonshop_product_express_logs(pid,express_id,isvalid,createtime,customer_id,operation,operation_user) values(".$p_id.",".$freight_id.",true,now(),".$c_id.",1,'".$_SESSION['username']."')";
				_mysql_query($sql_elog)or die('W945 : Query failed1-2: ' . mysql_error());
			}
			//插入修改运费模板日志 end

			//插入图片
			$imgidarr = explode(";",$imgids);
			for($i=0;$i<count($imgidarr);$i++){
			   $imgid = $imgidarr[$i]; //新改过后，imgid为实际图片路径
			   if(empty($imgid)){
				   continue;
			   }
			   //上传时不再添加到数据库，现改为在保存时统一提交
			   $sql="insert into weixin_commonshop_product_imgs(product_id,imgurl,isvalid,customer_id) values(".$p_id.",'".$imgid."',true,".$c_id.")";
				_mysql_query($sql)or die('L555 : Query failed1: ' . mysql_error());

			  /* if($c_id==$customer_id){
				   //如果是厂家上传的图片，则更新产品编号
				   $sql = "update weixin_commonshop_product_imgs set product_id=".$p_id." where id=".$imgid;
				   _mysql_query($sql);
			   }else{
				   //赋值给商家，要重新再生成一条记录
				   $query="select imgurl from weixin_commonshop_product_imgs where id=".$imgid;
				   $result = _mysql_query($query) or die('Query failed2: ' . mysql_error());
				   $imgurl="";
				   while ($row = mysql_fetch_object($result)) {
					   $imgurl = $row->imgurl;
					   break;
				   }
				   $sql="insert into weixin_commonshop_product_imgs(product_id,imgurl,isvalid,customer_id) values(".$p_id.",'".$imgurl."',true,".$c_id.")";
				   _mysql_query($sql)or die('Query failed1: ' . mysql_error());
			   }*/
			}

            if ($img_link != '') {
                $sql="insert into weixin_commonshop_product_imgs(product_id,imgurl,isvalid,customer_id,3d_link) values(".$p_id.",'".$img_3d."',true,".$c_id.",'".$img_link."')";
                _mysql_query($sql)or die('L555 : Query failed1: ' . mysql_error());
            }
			//新增产品的时候，添加属性价格、库存
			$pro_price_details = explode("-",$pro_price_detail);

			$plen = count($pro_price_details);
			if( $plen > 0 ){
				$query= "select proids from weixin_commonshop_product_prices where product_id=".$p_id;
				$result = _mysql_query($query) or die('L308 :Query failed: ' . mysql_error());
				$rpocount = 0;
				$ppLst = new ArrayList();
				while ($row = mysql_fetch_object($result)) {
					$proids = $row->proids;
					$ppLst->Add($proids);
				}
				$query=" delete from weixin_commonshop_product_prices where product_id=".$p_id;
				_mysql_query($query)or die('L318 :Query failed1: ' . mysql_error());
			}

			$fm_num2 = 0;
			for($i=0;$i<$plen;$i++){
				$pro_price_item = $pro_price_details[$i];
				if(empty($pro_price_item)){
				   continue;
				}
				$pro_price_items = explode(",",$pro_price_item);
				$pilen = count($pro_price_items);
				$proids =$pro_price_items[0];
				if($c_id!=$customer_id){
					//查找下级的真实的属性编号
					$pro_id_arr = explode("_",$proids);
					$proids = "";
					for($j=0;$j<count($pro_id_arr);$j++){
						$pro_id = $pro_id_arr[$j];
						if(empty($pro_id)){
							continue;
						}
						/* $query="select id from weixin_commonshop_pros where customer_id=".$c_id." and isvalid=true and  create_parent_id=".$pro_id;  ----by yehecong 2017-1-9 ----*/
						$query="select id from weixin_commonshop_pros where customer_id=".$c_id." and  create_parent_id=".$pro_id;
						$result = _mysql_query($query) or die('L597 : Query failed1: ' . mysql_error());
						$pro_id_s=-1;
						while ($row = mysql_fetch_object($result)) {
							$pro_id_s= $row->id;
							break;
						}
						if(empty($proids)){
							$proids=$pro_id_s;
						}else{
							$proids=$proids."_".$pro_id_s;
						}
					}
				}

				$is_added = false;
				if($ppLst->Contains($proids)){
					$is_added =true;
				}

				$proprices      = $pro_price_items[1];
				$p_orgin_price  = $orgin_price;
				$p_unit         = $unit;
				$p_weight       = $weight;
				$p_now_price    = $now_price;
				$p_storenum     = $storenum;
				$p_cost_price   = $cost_price;
				$p_for_price   	= $for_price;
				$p_foreign_mark = $foreign_mark;
				$p_need_score   = $need_score;

				$p_orgin_price = round($p_orgin_price, 2);
				$p_now_price = round($p_now_price, 2);
				$p_for_price = round($p_for_price, 2);
				$p_cost_price = round($p_cost_price, 2);



				$pprices = explode("_",$proprices);
				$pplen = count($pprices);

				$p_o_price        = $pprices[0];
				$p_n_price        = $pprices[1];
				$p_v_price        = $pprices[2];
				$p_n_storenum     = $pprices[3];
				$p_n_need_score   = $pprices[4];
				$p_n_cost_price   = $pprices[5];
				$p_n_foreign_mark = $pprices[6];
				$p_n_unit 		  = $pprices[7];
				$p_n_weight 	  = $pprices[8];
				$p_n_for_price 	  = $pprices[9];

				$p_o_price 		= round($p_o_price, 2);
				$p_n_price 		= round($p_n_price, 2);
				$p_v_price 		= round($p_v_price, 2);
				$p_n_cost_price = round($p_n_cost_price, 2);
				$p_n_for_price = round($p_n_for_price, 2);

				$p_unit = '';
				if($p_n_unit!=""){  //单位
					$p_unit = $p_n_unit;
				}

				$p_weight = 0;
				if($p_n_weight!=""){  //重量
					$p_weight = $p_n_weight;
				}

				$p_orgin_price = 0;
				if($p_o_price != ''){  //原价
				  $p_orgin_price = $p_o_price;
				}

				$p_now_price = 0;
				if($p_n_price != ''){  //现价
				  $p_now_price = $p_n_price;
				}

				$p_v_price = 0;
				if($p_v_price != ''){  //VIP 价
				  $p_v_price = $p_v_price;
				}

				$p_storenum = 0;
				 if($p_n_storenum!=""){  //库存
				  $p_storenum = $p_n_storenum;
				}

				$p_need_score = 0;
				 if($p_n_need_score!=""){  //需要积分
				  $p_need_score = $p_n_need_score;
				}

				$p_cost_price = 0;
				if($p_n_cost_price!=""){    //供货价
				  $p_cost_price = $p_n_cost_price;
				}

				$p_for_price = 0;
				if($p_n_for_price!=""){    //成本价
				  $p_for_price = $p_n_for_price;
				}

				$p_foreign_mark = '';
				 if($p_n_foreign_mark!=""){    //外部标识
				  $p_foreign_mark = $p_n_foreign_mark;
				}

				if($p_foreign_mark != ""){
					$query_fm_num2 ="select count(1) as num2 from weixin_commonshop_product_prices as pri
									 LEFT JOIN weixin_commonshop_products as pro on pri.product_id=pro.id
									 where pri.foreign_mark='" . $p_foreign_mark . "' and pro.customer_id = ".$c_id;
					//echo $query_fm_num2;
					$result_fm_num2 = _mysql_query($query_fm_num2) or die('L616: Query_fm_num2 failed: ' . mysql_error());
						while ($row = mysql_fetch_object($result_fm_num2)) {
						$fm_num_2 = $row->num2;
						$fm_num2 += $fm_num_2;
					}
				}
				/*4M 设置上级 start*/
				$prices_parent_id = -1;
				if($c_id!=$customer_id){
					$prices_parent_id = $fac_prices_ids[$i];
				}
				if(empty($owner_general)){
					$price_create_type = 3;
				}else{
					$price_create_type = $owner_general;
				}
				/*4M 设置上级 end*/

				/* $query="insert into weixin_commonshop_product_prices(product_id,proids,orgin_price,now_price,for_price,storenum,need_score,cost_price,unit,foreign_mark,weight) value(".$p_id.",'".$proids."',".$p_orgin_price.",".$p_now_price.",".$p_for_price.",".$p_storenum.",".$p_need_score.",".$p_cost_price.",'".$p_unit."','".$p_foreign_mark."',".$p_weight.")"; ----by yehecong 2017-1-9 ----*/
				$query="insert into weixin_commonshop_product_prices(product_id,proids,orgin_price,now_price,vip_price,for_price,storenum,need_score,cost_price,unit,foreign_mark,weight,create_type,create_parent_id,customer_id) value(".$p_id.",'".$proids."',".$p_orgin_price.",".$p_now_price.",".$p_v_price.",".$p_for_price.",".$p_storenum.",".$p_need_score.",".$p_cost_price.",'".$p_unit."','".$p_foreign_mark."',".$p_weight.",".$price_create_type.",".$prices_parent_id.",".$customer_id.")";//echo  $query.'<br>';
				_mysql_query($query)or die('L1160 : Query failed1: ' . mysql_error());
				/*4M 设置上级 start*/
				$fac_proids_partid = mysql_insert_id();
				if($c_id==$customer_id){	//当是上级
					array_push($fac_prices_ids,$fac_proids_partid);
				}
				/*4M 设置上级 end*/
			}
			if(0 < $fm_num2){
				//echo "<script>alert('属性价格存在已使用的外部标识，请检查确保正确！');</script>";
			}

		}
	}
	//4M模式 End
 }

$sel2 = "SELECT count(id) as num2 FROM commonshop_product_discount_t WHERE isvalid=true and pid=".$Pkid;
$res2 = _mysql_query($sel2) or die('Query_sel2 failed26: ' . mysql_error());
while($row2=mysql_fetch_object($res2)){
	$num2 = $row2->num2;
}
if($num2==0){
	$ins_sql2 = "INSERT INTO commonshop_product_discount_t(isvalid,pid,currency_percentage) VALUES(true,".$Pkid.",".$currency_percentage.")";
	_mysql_query($ins_sql2) or die('Query_ins_sql2 failed32: ' . mysql_error());
}else{
	$update_sql2 = "UPDATE commonshop_product_discount_t SET currency_percentage=".$currency_percentage." WHERE isvalid=true and pid=".$Pkid." limit 1";
	//echo $update_sql;die;
	_mysql_query($update_sql2)or die('Query_update_sql2 failed31: ' . mysql_error());
}

if(!empty($stock_pidarr)){
	$stock_pidarr_new = explode("_",$stock_pidarr);
	$key = array_search($keyid, $stock_pidarr_new);
	if ($key !== false){
		array_splice($stock_pidarr_new, $key, 1);
		//var_dump($stock_pidarr_new);
	}
	$stock_pidarrlst=implode("_",$stock_pidarr_new);
	//echo $stock_pidarrlst;
	//return;
}

//必填信息入数据库
$mess_num = 0;//获取自定义名称个数
if(!empty($_POST["mess_num"])){
	$mess_num = $configutil->splash_new($_POST["mess_num"]); //添加信息个数

}

if( $is_Pinformation == 1 ){

	for( $i = 1; $i <= $mess_num; $i++){
		$singletext_con = "";
		$name_id        = -1;
		if(!empty($_POST["name_id".$i])){
			$name_id = $configutil->splash_new($_POST["name_id".$i]); //判断数据是否存在表里
		}
		if(!empty($_POST["singletext_con_".$i])){
			$singletext_con = $configutil->splash_new($_POST["singletext_con_".$i]); //自定义名称
		}

		$query = "select id from weixin_commonshop_product_information_t where name='".$singletext_con."' and p_id=".$Pkid." and customer_id=".$customer_id;
		$res = _mysql_query($query) or die('t Query failed: ' . mysql_error());
		if(mysql_num_rows($res)>0){
			$name_id = -1;
			$singletext_con = "";
		}//判断信息是否已存在

		if( 1 > $name_id ){
			if($singletext_con != ""){
				$sql_ins="insert into weixin_commonshop_product_information_t(customer_id,isvalid,name,p_id,createtime) values(".$customer_id.",true,'". $singletext_con ."',".$Pkid.",now())";
				$result = _mysql_query($sql_ins) or die('L1163 Query failed: ' . mysql_error());
			}
		}else{
			if($singletext_con != ""){
				$sql_ins="update weixin_commonshop_product_information_t set name='". $singletext_con ."'  where id = ".$name_id." and customer_id=".$customer_id;
			}else{
				$sql_ins="update weixin_commonshop_product_information_t set isvalid=flase  where id = ".$name_id." and customer_id=".$customer_id;
			}
			$result = _mysql_query($sql_ins) or die('L1163 Query failed: ' . mysql_error());
		}
			// echo $sql_ins;
			// $result = _mysql_query($sql_ins) or die('L1163 Query failed: ' . mysql_error());
	}

}

/* 清除redis缓存 start */
$redis_cache = array(
    "product_detail_{$keyid}",      //产品详情页
    "product_list_{$customer_id}"   //产品列表页
);

redis_del($redis_cache);
/* 清除redis缓存 end */

//echo '4M开发中ing...<br>';
//return;
if($offer_id >0){
	$status = 1;
	$pid 	= $keyid;
	$time   = date('Y-m-d H:i:s',time());
	if($offer_id==1){
		$sql = "Update weixin_commonshop_supply_products SET status=".$status.",is_out=0 where pid=".$pid." and isvalid=true";
		$sql2 = "Update weixin_commonshop_products SET isout=0,isvalid=true,createtime='".$time."' where id=".$pid."";
	}elseif($offer_id==2){
		$sql = "Update weixin_commonshop_supply_products SET status=".$status.",is_out=1 where pid=".$pid." and isvalid=true";
		$sql2 = "Update weixin_commonshop_products SET isout=1,isvalid=true,createtime='".$time."' where id=".$pid."";
	}
	$res = _mysql_query($sql);
	//$sql2 = "Update weixin_commonshop_products SET isout=0,createtime='".$time."' where id=".$pid." and isvalid=true";
	//$sql2 = "Update weixin_commonshop_products SET isout=0,isvalid=true,createtime='".$time."' where id=".$pid."";
	$res2 = _mysql_query($sql2);

	if($res && $res2){
		 echo "<script>location.href='examine.php?customer_id=".$customer_id_en."&status=0';</script>";
	}
}


$edit_or_product = 0;//是否同步到订货系统产品编辑页
if($_POST["edit_or_product"]!==""){
	$edit_or_product = $configutil->splash_new($_POST["edit_or_product"]);
}
$my_from = '';//是否是订货系统产品
if($_POST["my_from"]!==""){
    $my_from = $configutil->splash_new($_POST["my_from"]);
}
$del_pro = '';//删除掉的属性
if($_POST["del_pro"]!==""){
    $del_pro = $configutil->splash_new($_POST["del_pro"]);
}
$del_lsit_pro = '';//删除掉的属性列
if($_POST["del_lsit_pro"]!==""){
    $del_lsit_pro = $configutil->splash_new($_POST["del_lsit_pro"]);
}
if($my_from == 'orderingretail_pro'){
    require_once ("{$_SERVER['DOCUMENT_ROOT']}/wsy_prod/admin/Product/product/change_ordering_pro.php");
    $change_ordering_pro = new change_ordering_pro();
    if($my_from == 'orderingretail_pro'){
        $change_ordering_pro->change_pro($customer_id,$pid,$edit_or_product,true,$del_pro);
    }
    if($del_lsit_pro != ''){
        //$change_ordering_pro->change_code_list($customer_id,$pid,$del_lsit_pro);
    }
}
if($my_from == 'orderingretail_price'){
    require_once ("{$_SERVER['DOCUMENT_ROOT']}/wsy_prod/admin/Product/product/change_ordering_pro.php");
    $change_ordering_pro = new change_ordering_pro();
    if($my_from == 'orderingretail_price'){
        $change_ordering_pro->change_pro($customer_id,$pid,false);
    }
}


$error =mysql_error();
mysql_close($link);
echo $error;

//每次操作都清空模板缓存 xj
clear_template_cache("/tmp/weixin_platform/$customer_id");

 if(!empty($stock_pidarrlst)){
	 echo "<script>location.href='stock_product.php?customer_id=".$customer_id_en."&pagenum=".$pagenum."&stock_pidarr=".$stock_pidarrlst."';</script>";
 }elseif($edit_or_product > 0){ //同步到订货系统产品编辑页
 	 echo "<script>location.href='/addons/index.php/ordering_retail/Ordermanagement/product_edit?customer_id=".$customer_id_en."&pid=".$keyid."';</script>";
 }else{
	 if($head == 11){
		 echo "<script>location.href='sale.php?customer_id=".$customer_id_en."&pagenum=".$pagenum."';</script>";
	 }elseif($head == 12){
		 echo "<script>location.href='store.php?customer_id=".$customer_id_en."&pagenum=".$pagenum."';</script>";
	 }elseif($head == 13){
		 echo "<script>location.href='saleout.php?customer_id=".$customer_id_en."&pagenum=".$pagenum."';</script>";
	 }elseif($head == 14){
		 echo "<script>location.href='warn.php?customer_id=".$customer_id_en."&pagenum=".$pagenum."';</script>";
	 }else{
	 	if ($_POST['exglb'] != '') {
			echo"<script>location.href='".$_SERVER['HTTP_REFERER'].'&sa='.$_POST['sa']."'</script>";
	 	} else {
			echo "<script>location.href='sale.php?customer_id=".$customer_id_en."&pagenum=".$pagenum."';</script>";
	 	}
	 }
 }
 function strFilter($str){//过滤非法符号函数，待添加
    $str = str_replace("'", '', $str);
    $str = str_replace('"', '', $str);
    return trim($str);
}
/*  echo "<script>location.href='product.php?customer_id=".$customer_id."&pagenum=".$pagenum."';</script>" */
