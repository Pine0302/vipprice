<?php
//require('../../../../wsy_prod/admin/Product/logs.php');
header("Content-type: text/html; charset=utf-8");
require('../../../../weixinpl/config.php');
require('../../../../weixinpl/customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../../weixinpl/back_init.php');
require('../../../../weixinpl/common/utility.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database'); 

require('../../../../weixinpl/proxy_info.php');
_mysql_query("SET NAMES UTF8");
require('../../../../weixinpl/common/utility_4m.php');
require('../../../../weixinpl/common/tupian/CreateExpQR.php');
require('../../../../wsy_prod/admin/Product/product/product.class.php');           //分类关联属性等方法

//require('../../../../weixinpl/auth_user.php');
$u4m = new Utiliy_4m();
$rearr = $u4m->is_4M($customer_id);

//是4m分销
$is_shopgeneral = $rearr[0]  ;
//厂家编号
$adminuser_id = $rearr[1] ;
//是否是厂家总店
$is_samelevel = $rearr[2] ;
//总店模板编号
$general_template_id = $rearr[3] ;
//总店商家编号
$general_customer_id = $rearr[4] ;

//是否本身就是厂家总店
//1：厂家总店； 2：代理商总店
$owner_general = $rearr[5] ;

$orgin_adminuser_id = $rearr[6] ;

//获取下级所有的权限控制 by @ye
$getAllSubcontrol = $u4m->getAllSubcontrol($adminuser_id);

//var_dump($getAllSubcontrol);
//查询商家是否有上传产品权限
$is_upload_pros = $u4m->check_cus_authority($customer_id,$getAllSubcontrol,1);

//查询商家是否有修改产品价格权限
$is_change_pros_price = $u4m->check_cus_authority($customer_id,$getAllSubcontrol,2);

/* echo $_SESSION['is_auth_user'].'==='.$_SESSION['user_id']; */
$stock_pidarr="";
if(!empty($_GET["stock_pidarr"])){
    $stock_pidarr =$configutil->splash_new($_GET["stock_pidarr"]);
}
if(!empty($_GET["adminuser_id"])){
    $adminuser_id = $configutil->splash_new($_GET["adminuser_id"]);
}
if(!empty($_GET["orgin_adminuser_id"])){
    $orgin_adminuser_id = $configutil->splash_new($_GET["orgin_adminuser_id"]);
}
if(!empty($_GET["owner_general"])){
    $owner_general = $configutil->splash_new($_GET["owner_general"]);
}
$sa = 1;
if (!empty($_GET['sa'])) {
    $sa = $_GET['sa'];
}
$description="";
$product_id=-1;
$product_name="";
$product_orgin_price=0;//商品原价 默认值0---2016-11-14-dongqiao
$product_now_price=0;//商品现价 默认值0---2016-11-14-dongqiao
$product_type_id=-1;
$product_introduce="";
$product_description="";
$specifications="";
$customer_service="";
$product_voice="";
$product_vedio="";
$product_unit="";
$product_asort=-1;
$product_isout=0;
$product_isnew=0;
$product_ishot=0;
$product_issnapup=0;
$product_is_free_shipping=0;  //是否包邮
$product_isvp =0;   //是否属于vp产品，1：是；0：否
$vp_score =0;       //vp值,vp产品消费累积满多少vp值可以提现佣金
$product_tradeprices="";
$product_propertyids = "";
$product_storenum=1;
$product_need_score=0;

$product_cost_price=0; //供货价
$product_for_price=0; //成本价
$product_foreign_mark="";
$imgids="";
$imglink = '';
$img = '';

$pro_discount=0;
$pro_reward=-1;
$issell=0;
$type_ids="";
$default_imgurl="";
$class_imgurl="";
$product_asort_value=0;
$sell_count=0;
$show_sell_count=999;
$define_share_image_flag=0;
$supply_id=-1;
$product_id=-1;
$detail_template_type=1;
$define_share_image="";
$install_price = 0;
$weight = 0;
$product_weight = 0;
$nowprice_title="";
$pro_area = -1;

$agent_discount      = 0;//代理商折扣
$pro_card_level_id   = -1;//购买产品需要的会员卡等级ID
$isOpenAgent         = 0;//是否开启代理商
$isOpenInstall       = 0;//安装平台
$isOpenPublicWelfare = 0;//公益基金
$cashback            = -1;//奖励金额
$cashback_r          = -1;//返现比例
$is_identity         = 0;//产品是否需要身份证购买开关
$shop_is_identity    = 1;//身份证购买开关
$is_invoice          = 0;//发票开关

$is_Pinformation    =  0;//必填信息产品开关1：开 0：关
$freight_id         = -1;//运费模板ID
$is_virtual         =  0;//是否为虚拟产品 0:非虚拟产品,1:虚拟产品
$is_currency        = 0;//是否购物币产品 0：不是（默认） 1：是
$is_guess_you_like  = 0;//是否猜您喜欢产品 0：不是（默认） 1：是
$back_currency      = 0;//购物币返佣金额
//$first_division   = 0;//一级奖励金额
$express_type       = 0;//邮费计费方式
$isscore            = 0;//兑换专区
$islimit            = 0;//是否限购 0：不是（默认） 1：是
$limit_num          = 1;//限购数量
$is_first_extend    = 0;//是否首次推广奖励产品
$extend_money       = 0;//首次推广奖励金额
$init_reward        = 0;//奖励总比例
$issell             = 0;//是否开启分销
$detail_template_type= 0;//详情模板分类类型
$tax_type           = 1;//
$privilege_list     = [];//特权专区等级
$privilege_level    = "";
$link_package       = -1;
$exp_name           = "推广员";
$common_name        = "粉丝";

$query ="select isOpenPublicWelfare,nowprice_title,isOpenInstall,isOpenAgent,pro_card_level,shop_card_id,is_identity,issell,detail_template_type,init_reward,exp_name,sendstyle_pickup from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
$result = _mysql_query($query) or die('Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
   $isOpenPublicWelfare  = $row->isOpenPublicWelfare;   //公益基金
   $isOpenInstall        = $row->isOpenInstall; //安装平台
   $isOpenAgent          = $row->isOpenAgent;   //是否开启代理商
   $base_nowprice_title  = $row->nowprice_title;
   $pro_card_level       = $row->pro_card_level;//购买产品需要会员卡级别开关
   $shop_card_id         = $row->shop_card_id;//会员卡ID
   $shop_is_identity     = $row->is_identity;
   $issell               = $row->issell;
   $detail_template_type = $row->detail_template_type;
   $init_reward          = $row->init_reward;
   $exp_name             = $row->exp_name;
   $sendstyle_pickup     = $row->sendstyle_pickup;  //是否自提
}

$nowprice_title      = "现价";


$is_charitable        = 0;//慈善开关
$charitable_propotion = 0;//慈善最低比例
$query ="select is_charitable,charitable_propotion from charitable_set_t where isvalid=true and customer_id=".$customer_id;
$result = _mysql_query($query) or die('Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
    $is_charitable        = $row->is_charitable;
    $charitable_propotion = $row->charitable_propotion;
}

$is_QR = 0;
$buystart_time = "";
$countdown_time = "";
$product_type_parent_id = -1;
$donation_rate = $charitable_propotion;
$link_coupons = -1;
$is_mini_mshop = 0;//微信小程序产品
$is_pickup = 1;//默认为自提产品
if(!empty($_GET["product_id"])){
   $product_id = $configutil->splash_new($_GET["product_id"]);

   //$isvalid = $_REQUEST['status']?'false':'true';
   $isvalid = $_REQUEST['is_audit']?'false':'true';//是否是审核产品跳转过来

   $query="select customer_id,name,donation_rate,orgin_price,asort_value,unit,default_imgurl,class_imgurl,cost_price,foreign_mark,pro_discount,pro_reward,now_price,need_score,type_id,introduce,description,specifications,customer_service,product_voice,product_vedio,asort,isout,isnew,ishot,issnapup,isvp,is_free_shipping,vp_score,storenum,tradeprices,propertyids,type_ids,sell_count,show_sell_count,define_share_image,is_supply_id,install_price,weight,agent_discount,nowprice_title,is_QR,QR_isforever,QR_starttime,QR_endtime,pro_card_level_id,cashback,cashback_r,pro_area,buystart_time,countdown_time,is_identity,for_price,is_Pinformation,freight_id,is_virtual,is_invoice,is_currency,is_guess_you_like,back_currency,express_type,isscore,islimit,limit_num,is_first_extend,extend_money,tax_type,is_privilege,privilege_level,link_package,link_package_img,link_coupons,remark,short_introduce_color,ordering_retail,is_mini_mshop,is_pickup,skuid from weixin_commonshop_products where id=".$product_id;
  // _file_put_contents('hello.txt',$query);
  // echo $query;
   $result = _mysql_query($query) or die('Query failed1: ' . mysql_error());
   while ($row = mysql_fetch_object($result)) {
      $tax_type                    = $row->tax_type;
      $donation_rate               = $row->donation_rate == 0?$charitable_propotion:$row->donation_rate;
      $product_name                = $row->name;
      $product_orgin_price         = $row->orgin_price;
      $product_now_price           = $row->now_price;
      $product_unit                = $row->unit;
      $product_type_id             = $row->type_id;
      $introduce                   = $row->introduce;
      $product_description         = $row->description;
      $specifications              = $row->specifications;
      $customer_service            = $row->customer_service;
      $product_voice               = $row->product_voice;
      $product_vedio               = $row->product_vedio;
      $product_asort               = $row->asort;
      $product_isout               = $row->isout;
      $is_QR                       = $row->is_QR;
      /*郑培强*/
      $QR_isforever                = $row->QR_isforever;
      $QR_starttime                = $row->QR_starttime;
      $QR_endtime                  = $row->QR_endtime;
      /*郑培强*/
      $product_isnew               = $row->isnew;
      $product_ishot               = $row->ishot;
      $product_issnapup            = $row->issnapup;
      $product_is_free_shipping    = $row->is_free_shipping;
      $product_isvp                = $row->isvp;
      $vp_score                    = $row->vp_score;
      $product_tradeprices         = $row->tradeprices;
      $product_propertyids         = $row->propertyids;
      $product_storenum            = $row->storenum;
      $pro_area                    = $row->pro_area;
      $product_need_score          = $row->need_score;
      $pro_discount                = $row->pro_discount;
      $pro_reward                  = $row->pro_reward;
      $customer_id                 = $row->customer_id;
      $type_ids                    = $row->type_ids;
      $default_imgurl              = $row->default_imgurl;
      $class_imgurl                = $row->class_imgurl;
      $buystart_time               = $row->buystart_time;//商品抢购开始时间
      $countdown_time              = $row->countdown_time;//商品抢购结束时间
      $product_cost_price          = $row->cost_price;
      $product_for_price           = $row->for_price;
      $product_foreign_mark        = $row->foreign_mark;
      $sell_count                  = $row->sell_count;
      $show_sell_count             = $row->show_sell_count;
      $product_asort_value         = $row->asort_value;
      $define_share_image          = $row->define_share_image;
      $supply_id                   = $row->is_supply_id;//供应商ID
      $define_share_image_flag     = $define_share_image?1:0;
      $install_price               = $row->install_price;
      $product_weight              = $row->weight;//产品重量
      $agent_discount              = $row->agent_discount;//代理商折扣
      $nowprice_title              = $row->nowprice_title;//"现价"自定义名称
      $pro_card_level_id           = $row->pro_card_level_id;//购买产品需要的会员卡等级ID
      $cashback                    = $row->cashback;
      $cashback_r                  = $row->cashback_r;
      $is_identity                 = $row->is_identity;
      $is_Pinformation             = $row->is_Pinformation;//必填信息产品开关1：开 0：关
      $freight_id                  = $row->freight_id;     //运费模板ID
      $is_virtual                  = $row->is_virtual?1:0;     //是否为虚拟产品 0:非虚拟产品,1:虚拟产品
      $is_invoice                  = $row->is_invoice;  //发票开关
      $is_currency                 = $row->is_currency;//是否购物币产品
      $is_guess_you_like           = $row->is_guess_you_like;//是否猜您喜欢产品
      $back_currency               = $row->back_currency;
     // $first_division            = $row->first_division;
      $express_type                = $row->express_type;
      $isscore                     = $row->isscore;//兑换专区
      $islimit                     = $row->islimit;//是否限购
      $limit_num                   = $row->limit_num;//限购数量
      $is_first_extend             = $row->is_first_extend;//是否首次推广奖励产品
      $extend_money                = $row->extend_money;//首次推广奖励金额
      $privilege_level             = $row->privilege_level;
      $is_privilege                = $row->is_privilege;
      $link_package                = $row->link_package;
      $link_package_img            = $row->link_package_img;
      $link_coupons                = $row->link_coupons;
      $product_remark              = $row->remark;//备注
      $short_introduce_color       = $row->short_introduce_color;//短简介颜色
      $ordering_retail             = $row->ordering_retail;
      $is_mini_mshop               = $row->is_mini_mshop;//微信小程序产品
      $is_pickup                   = $row->is_pickup;//是否为自提产品
      $skuid                       = $row->skuid;
      break;
  }
//  var_dump($product_type_id);
  //防止编辑器上传的内容中没有文字导致显示时，字体大小为0
  if(strstr($product_description,'font-size:0;',true)){
      $product_description = str_replace("font-size:0;","font-size:1;",$product_description);
  }
  
  if (substr($link_coupons,0,1) == ',') {
    $link_coupons=substr($link_coupons,1);
  }

  //备注json转数组
  $product_remark = json_decode($product_remark);
  $remark_color = $product_remark[0]->color;
  $remarks = $product_remark[0]->concent;

//echo $is_privilege;
   //将特权专区分拆成数组
    $privilege_list = explode("_", $privilege_level);


  if($limit_num < 1){
      $limit_num = 1;
  }

  $query_type = "select parent_id from weixin_commonshop_types where isvalid = true and id = ".$product_type_id;
   $result_type = _mysql_query($query_type) or die('L163 : Query failed1: ' . mysql_error());
   if($row_type = mysql_fetch_object($result_type)){
       $product_type_parent_id = $row_type->parent_id;
   }

  $query="select id, imgurl, 3d_link link_3d from weixin_commonshop_product_imgs where isvalid=true and product_id=".$product_id;
  $result = _mysql_query($query) or die('Query failed4: ' . mysql_error());
  while ($row = mysql_fetch_object($result)) {
     $imgid = $row->id;
     $imgids = $imgids.$imgid."_";
     if (!empty($row->link_3d)) {
        $imglink = $row->link_3d;
        $img = $row->imgurl;
     }
  }
}


if($imgids!=""){
   $imgids = rtrim($imgids,"_");
}
$propertylst= new ArrayList();
if($product_propertyids!=""){
   $propertyarr = explode("_",$product_propertyids);
   $len = count($propertyarr);
   for($i=0;$i<$len;$i++){
       $propertyid= $propertyarr[$i];
       $propertylst->add($propertyid);
   }
}

$pidpnames="";

$head = 10;
if(!empty($_GET["head"])){
    $head = $configutil->splash_new($_GET["head"]);
}
$pagenum = 1;
if(!empty($_GET["pagenum"])){
    $pagenum = $configutil->splash_new($_GET["pagenum"]);
}


$typeLst =  new ArrayList();
//$typeLst->Add($product_type_id);
$typeid_arr = explode(",",$type_ids);
$tlen = count($typeid_arr);
if($tlen>0){
    for($i=0;$i<$tlen; $i++){
        $stid = $typeid_arr[$i];
        $typeLst->Add($stid);
    }
}


//代理模式,分销商城的功能项是 266
$is_distribution=0;//渠道取消代理商功能
$is_disrcount=0;
$query1="select count(1) as is_disrcount from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.sys_name='商城代理模式' and c.id=cf.column_id";
$result1 = _mysql_query($query1) or die('W_is_disrcount Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result1)) {
   $is_disrcount = $row->is_disrcount;
   break;
}
if($is_disrcount>0){
   $is_distribution=1;
}

//供应商模式,渠道开通与不开通
$is_supplierstr=0;//渠道取消供应商功能
$sp_count=0;//渠道取消供应商功能
$sp_query="select count(1) as sp_count from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.sys_name='商城供应商模式' and c.id=cf.column_id";
$sp_result = _mysql_query($sp_query) or die('W_is_supplier Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($sp_result)) {
   $sp_count = $row->sp_count;
   break;
}
if($sp_count>0){
   $is_supplierstr=1;
}

//扫码模式,渠道开通与不开通
$is_scancode=0;//渠道取消供应商功能
$sc_count=0;//渠道取消供应商功能
$sc_query="select count(1) as sc_count from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.sys_name='商城扫码模式' and c.id=cf.column_id";
$sc_result = _mysql_query($sc_query) or die('W_is_scancode Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($sc_result)) {
   $sc_count = $row->sc_count;
   break;
}
if($sc_count>0){
   $is_scancode=1;
}

$query_cashback="select count(1) as count_cashback from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.sys_name='消费返现' and c.id=cf.column_id";
$is_opencashback=0; //是否开通了消费返现 0不开通 1开通
$count_cashback=0;
$result_cashback = _mysql_query($query_cashback) or die('W_count_cashback Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result_cashback)) {
   $count_cashback = $row->count_cashback;
   break;
}
if($count_cashback>0){
   $is_opencashback=1;
}

$query="select product_num from customers where isvalid=true and id=".$customer_id;
$result = _mysql_query($query) or die('Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
   $product_num = $row->product_num;//最多上架商品数量
   break;
}
$query="select isout,count(1) as num from weixin_commonshop_products where isout=0 and isvalid=true and customer_id=".$customer_id;
$result = _mysql_query($query) or die('Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
   $num = $row->num;//已经上架商品数量
   break;
}

$auth_user_id = -1;
if($_SESSION['is_auth_user']=='yes' && $_SESSION['user_id']){
    $auth_user_id = $_SESSION['user_id'];
}

/* $query = "select cb_condition from weixin_commonshop_cashback where isvalid=true and customer_id=".$customer_id." limit 0,1";
$result = _mysql_query($query) or die('L324: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
   $cb_condition = $row->cb_condition;   //奖励金额模式    0：固定金额  1：产品价格按比例
} */

$sql = "select * from weixin_commonshops_extend where isvalid=true and customer_id=".$customer_id;
$result1 = _mysql_query($sql) or die('Query failed: ' . mysql_error());
while ($row1 = mysql_fetch_object($result1)) {
        $is_Pinformation_b=$row1->is_Pinformation;//必填信息大开关1：开 0：关
}

//全球分红奖励
$sql1 = "SELECT isOpenGlobal,Global_all FROM weixin_globalbonus where isvalid=true and customer_id=".$customer_id;
$res1 =  _mysql_query($sql1) or die('Query failed: ' . mysql_error());
$row1=mysql_fetch_assoc($res1);
$isOpenGlobal = $row1['isOpenGlobal'];
if($isOpenGlobal==0){
    $globalbonus_pro = 0;
}else{
    $globalbonus_pro = $row1['Global_all'];
}

//查询是否开启团队奖励喝团队奖励
$query = "select is_team,is_shareholder from weixin_commonshops where isvalid=true and customer_id=".$customer_id." limit 0,1";
$result = _mysql_query($query) or die('Query failed: ' . mysql_error());
$is_team = 0;
$is_shareholder = 0;
while ($row = mysql_fetch_object($result)) {
    $is_team        = $row->is_team;//是否开启区域奖励
    $is_shareholder = $row->is_shareholder;
}
//团队比例
$query_team = "SELECT team_all from weixin_commonshop_team where isvalid=true and customer_id=".$customer_id." limit 0,1";
$result_team = _mysql_query($query_team);
while($row=mysql_fetch_assoc($result_team)){
    if($is_team==0){
        $team_all = 0;
    }else{
        $team_all=$row['team_all'];
    }

}
//股东比例
$a_name = "";   //代理
$b_name = "";   //渠道
$c_name = "";   //总代
$d_name = "";   //股东
$query_shareholder = "SELECT shareholder_all,a_name,b_name,c_name,d_name FROM weixin_commonshop_shareholder where isvalid=true and customer_id=".$customer_id;
$result_shareholder = _mysql_query($query_shareholder);
while($row = mysql_fetch_assoc($result_shareholder)){
    if($is_shareholder==0){
        $shareholder_all = 0;
    }else{
        $shareholder_all = $row['shareholder_all'];
    }
    $a_name = mysql_real_escape_string($row['a_name']);
    $b_name = mysql_real_escape_string($row['b_name']);
    $c_name = mysql_real_escape_string($row['c_name']);
    $d_name = mysql_real_escape_string($row['d_name']);

}

$all = 1-($globalbonus_pro+$team_all+$shareholder_all);


$query = "select cb_condition,cashback,cashback_r from weixin_commonshop_cashback where isvalid=true and customer_id=".$customer_id." limit 0,1";
$result = _mysql_query($query) or die('L39: '.mysql_error());
$cb_condition = 0;
$public_cashback = 0;
$public_cashback_r = 0;
while($row = mysql_fetch_object($result)){
    $cb_condition = $row->cb_condition;    //奖励金额模式    0：固定金额  1：产品价格按比例
    $public_cashback = $row->cashback;
    $public_cashback_r = $row->cashback_r;
}

$tax_id         =-1;
$istax          = 0;
$tariff         = 0;//关税税率
$comsumption    = 0;//消费税税率
$addedvalue     = 0;//增值税税率
$postal         = 0;//行邮税率
$query = "SELECT id,tariff,comsumption,addedvalue,postal FROM weixin_commonshop_product_tax_detail WHERE product_id= ".$product_id." LIMIT 1";
$result= _mysql_query($query) or die('L436: '.mysql_error());
while( $row = mysql_fetch_object($result)){
    $tax_id         = $row->id;
    $tariff         = $row->tariff;
    $comsumption    = $row->comsumption;
    $addedvalue     = $row->addedvalue;
    $postal         = $row->postal;
}
if($tax_id>0){
    $istax = 1;
}
//分类排序
$sort_str = "";
$type_sort = "SELECT sort_str FROM weixin_commonshop_type_sort WHERE customer_id=".$customer_id;
$result_sort = _mysql_query($type_sort) or die ('type_sort failed:'.mysql_error());
while( $row_sort = mysql_fetch_object($result_sort) ){
   $sort_str = $row_sort -> sort_str;
}

$currency_percentage = -1;//默认-1，使用全局配置
$sql2  = "SELECT currency_percentage FROM commonshop_product_discount_t WHERE isvalid=true and pid=".$product_id." limit 0,1";
$res2  = _mysql_query($sql2);
while ($row2 = mysql_fetch_object($res2) ){
    $currency_percentage = $row2->currency_percentage;
}
if($currency_percentage>0){
    $currency_percentage = $currency_percentage*100;
}

$status = $_GET['status'];

$currency_isOpen = 0;//购物币抵扣开关开关
$is_rebate_open = 1;//消费返购物币开关
$sql2  = "SELECT isOpen,is_rebate_open FROM weixin_commonshop_currency WHERE isvalid=true and customer_id=".$customer_id;
$res2  = _mysql_query($sql2);
while ($row2 = mysql_fetch_object($res2) ){
    $currency_isOpen = $row2->isOpen;
    $is_rebate_open = $row2->is_rebate_open;
}

//查询主属性是否有图片
$attr_img_str   = '';
$attr_parent_id = '';
if($product_id != -1){
  $sql_attr_img = 'select wxcpai.attr_id,wxcpai.img,wxcp.parent_id from weixin_commonshop_product_attrimg wxcpai inner join weixin_commonshop_pros wxcp on wxcpai.attr_id = wxcp.id where wxcpai.customer_id='.$customer_id.' and wxcpai.pro_id='.$product_id.' and wxcpai.status=1';

  $result_attr_img = _mysql_query($sql_attr_img) or die('Query failed sql_attr_img: ' . mysql_error());
  while ($row_attr_img = mysql_fetch_object($result_attr_img)) {

    $attr_parent_id  = $row_attr_img->parent_id;
    $temp_img        = $row_attr_img->img;
    $temp_attr_id    = $row_attr_img->attr_id;
    if( !empty($temp_img) && !empty($temp_attr_id) ){
      $attr_img_str .= $temp_attr_id.'_'.$temp_img.',';
    }
  }
  if( !empty($attr_img_str) ){
    $attr_img_str = substr($attr_img_str,0,strlen($attr_img_str)-1);
  }

  // var_dump($attr_img_str);
}

//是否开启3d
$threed_open=0;
$sql_threed = "SELECT is_open FROM ".WSY_PROD.".3d_model_setting WHERE customer_id={$customer_id}";
$res_threed  = _mysql_query($sql_threed);
while ($row = mysql_fetch_object($res_threed) ){
  $threed_open = $row->is_open;
}
$parent_relation_type = array();
//渠道控制d开关
$is_travelcard = 0;
$query="select count(1) as is_travelcard from customer_funs cf left join columns c on c.id=cf.column_id where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.sys_name='3D素材'";
$funs = _mysql_query($query) or die('L274 is_travelcard Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($funs)) {
    $is_travelcard = $row->is_travelcard;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>添加产品1</title>
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content.css">

<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content<?php echo $theme; ?>.css">
<link rel="stylesheet" type="text/css" href="../../Common/css/Product/product.css">
<link rel="stylesheet" type="text/css" href="../../Common/css/Mode/charitable/set_up.css">

<script type="text/javascript" src="../../../common/js_V6.0/assets/js/jquery.min.js"></script>
<script type="text/javascript" src="../../../common/utility.js"></script>
<script type="text/javascript" src="../../../common_shop/jiushop/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="../../../js/WdatePicker.js"></script><!--添加时间插件-->

<!--编辑器多图片上传引入开始-->
<script type="text/javascript" src="/weixin/plat/Public/js/jquery.dragsort-0.5.2.min.js"></script>
<script type="text/javascript" src="/weixin/plat/Public/swfupload/swfupload/swfupload.js"></script>
<script type="text/javascript" src="/weixin/plat/Public/swfupload/js/swfupload.queue.js"></script>
<script type="text/javascript" src="/weixin/plat/Public/swfupload/js/fileprogress.js"></script>
<script type="text/javascript" src="/weixin/plat/Public/swfupload/js/handlers.js"></script>


<link rel="stylesheet" href="../../Common/css/Product/product/colorpicker.css" type="text/css" />
   <link rel="stylesheet" media="screen" type="text/css" href="../../Common/css/Product/product/layout.css" />
    <script type="text/javascript" src="../../Common/js/Product/product/colorpicker.js"></script>
    <script type="text/javascript" src="../../Common/js/Product/product/eye.js"></script>
    <script type="text/javascript" src="../../Common/js/Product/product/utils.js"></script>
   <script type="text/javascript" src="../../Common/js/Product/product/layout.js?ver=1.0.2"></script>
<style type="text/css">
.show_price{
    color:red;
}
.del{
    color:blue;
}
.del:hover{
    cursor:pointer;
}
<?php
if(!$is_show_tag){
?>
.threed_show{
    display: none;
}
<?php
	}
?>
</style>
<!--编辑器多图片上传引入结束-->
<script type="text/javascript">
    var ordering_proids = '';
    var ordering_now_price ='';
Array.prototype.contains = function(item){
   return RegExp("\\b"+item+"\\b").test(this);
};
ppriceHash = new Hashtable();
var oldProArray = new Array();
var newProArray = new Array(); //用于添加产品时
var oldPidArray = new Array();
var old_name = new Array();
var i = 0;
var supply_id = "<?php echo $supply_id; ?>";//供应商ID
<?php
$proids = '';
$query="select pp.proids,pp.orgin_price,pp.now_price,pp.storenum,pp.need_score,pp.cost_price,pp.foreign_mark,pp.unit,pp.weight,pp.for_price,cp.name
        from weixin_commonshop_product_prices pp
        left join weixin_commonshop_pros cp on pp.proids=cp.id
        where product_id=".$product_id;
$result = _mysql_query($query) or die('Query failed7: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {

       $proids = $row->proids;
       $name = $row->name;
       $orgin_price = $row->orgin_price;
       $now_price = $row->now_price;
       $storenum = $row->storenum;
       $need_score = $row->need_score;
       $cost_price = $row->cost_price;
       $for_price = $row->for_price;
       $foreign_mark = $row->foreign_mark;
       $unit = $row->unit;
       $weight = $row->weight;
       $parent_id = $row->parent_id;

    ?>

      var $name = "<?php echo $name; ?>";
      // var proids = "<?php echo "{$proids},{$name}"; ?>";
      var proids = "<?php echo $proids; ?>";
      var orgin_price = '<?php echo $orgin_price; ?>';
      var now_price = '<?php echo $now_price; ?>';
      var storenum = '<?php echo $storenum; ?>';
      var need_score = '<?php echo $need_score; ?>';
      var cost_price = '<?php echo $cost_price; ?>';
      var for_price = '<?php echo $for_price; ?>';
      var foreign_mark = "<?php echo $foreign_mark; ?>";
      var unit = "<?php echo $unit; ?>";
      var weight = "<?php echo $weight; ?>";
      var parent_id = "<?php echo $parent_id; ?>";
      ordering_proids += ','+"<?php echo $proids; ?>";
      ordering_now_price += ','+"<?php echo $now_price; ?>";


      old_name.push(name)
      ppriceHash.add(proids,orgin_price+"_"+now_price+"_"+storenum+"_"+need_score+"_"+cost_price+"_"+foreign_mark+"_"+unit+"_"+weight+"_"+for_price);
      if(proids!=""){
          // console.log(proids,'-----')
          var proArr = proids.split("_");
          // console.log(proArr,'++++')
          for(var j = 0 ; j < proArr.length ; j++){
              if(i == 0){
                  oldProArray[j] = new Array();
              }
              if(oldProArray[j].contains(proArr[j]) == false){
                 var length = oldProArray[j].length;
                 oldProArray[j][length] = proArr[j];
              }
          }
      }

      i++;
    <?php }
    if(!empty($proids)){
      if(strpos($proids,'_') !== false){
        $pro_link = explode('_', $proids);

        foreach ($pro_link as $key_p => $one) {
          $parent_sql = "select parent_id from weixin_commonshop_pros where id ='".$one."'";

          $result_parent = _mysql_query($parent_sql) or die('Query failed result_parent: ' . mysql_error());
          while ( $row_p = mysql_fetch_object($result_parent)) {

              $parent_id_t = $row_p->parent_id;

          }
    ?>
          var parent_id = "<?php echo $parent_id_t; ?>";
          oldPidArray.push(parent_id);
    <?php

        }
      }else{
          $parent_sql = "select parent_id from weixin_commonshop_pros where id ='".$proids."'";
          $result_parent = _mysql_query($parent_sql) or die('Query failed result_parent: ' . mysql_error());
          while ($row_p = mysql_fetch_object($result_parent)) {
              $parent_id_t = $row_p->parent_id;
          }
    ?>
          var parent_id = "<?php echo $parent_id_t; ?>";
          oldPidArray.push(parent_id);
    <?php
      }
    }
    ?>
    if(ordering_proids != ''){
        ordering_proids = ordering_proids.substring(1)
    }
    if(ordering_now_price != ''){
        ordering_now_price = ordering_now_price.substring(1)
    }
</script>
<style type="text/css">
    .type_detail{
        text-align: center;
    }

    .product_type_1{
        border-top: 1px solid #ccc;
        border-left: 1px solid #ccc;
        border-right: 1px solid #ccc;
    }
    .product_type_2{
        border-right: 1px solid #ccc;
        border-top: 1px solid #ccc;
    }
    .product_type_3{
        border-right: 1px solid #ccc;
        border-top: 1px solid #ccc;
    }
    .product_type_4{
        border-right: 1px solid #ccc;
        border-top: 1px solid #ccc;
    }
    .type_detail:last-child .product_type_1{
        border-bottom:1px solid #ccc;
    }
    .type_detail:last-child .product_type_2{
        border-bottom:1px solid #ccc;
    }
    .type_detail:last-child .product_type_3{
        border-bottom:1px solid #ccc;
    }
    .type_detail:last-child .product_type_4{
        border-bottom:1px solid #ccc;
    }
    .all_do_prompt{
        cursor:pointer;
    }
    .copy{
        cursor:pointer;
        color: blue;
    }
    .option{
        height: 340px;
        overflow-y: auto;
    }
    .red_border{
        border: 2px red solid;
    }
    /*3D素材---开始*/
    .mask_3d{
        width: 100%;
        height: 100%;
        position: fixed;
        top: 0;
        display: none;
        background: rgba(0,0,0,.4);
        z-index: 9999;
    }
    .mask_3d th{
        text-align: center;
    }
    .mask_3d td{
        word-wrap: break-word;
        border: 1px solid #d8d8d8;
        text-align:center !important;
    }
    .box_3D{
        width: 80%;
        background: #FFF;
        margin:0 auto;
        border-radius: 5px;
        height: 730px;
        margin-top: 40px;
    }
    .box_box{
      height: 600px;
      overflow: scroll;
      margin-top: 20px;
    }
    .title_3D{
        font-size: 17px;
        font-weight: 900;
        margin-left: 2%;
        color: black;
        padding-top: 35px;
    }
    /*3D素材---结束*/

    .product-tab-box{display:flex;justify-content:center;align-items:center;flex-wrap:wrap;}
    .product-tab-box li{color:#666;line-height:30px;font-size:14px;margin:10px 15px;cursor:pointer;}
    .product-tab-box li.tab-active{color:#333;}
    .product-tab-box li.tab-active span{background-color:red;}
    .product-tab-box li span{width:30px;height:30px;margin:0 10px 0 0;background-color:#ccc;display:inline-block;text-align:center;border-radius:50%;color:#fff;}
    .product-tab-list{display:none;}
    .product-tab-list.tab-active{display:block;}
    .product-input-box{overflow:visible;display:flex;}
    .product-input-box dd{float:left;line-height:24px;}
    .product-input-box dt{text-align:right;}

    .product-footer-btn{display:flex;align-items:center;justify-content:center;clear:both;}
    .product-footer-btn button{margin:15px 15px;}
    product-tab-list .WSY_table{border-collapse:collapse;}
    .product-tab-list .WSY_table th,.product-tab-list .WSY_table td{text-align:center;vertical-align:middle;}
    .product-tab-list .WSY_table th{padding:5px 0;}
    .product-tab-list .WSY_table td{border:solid 1px #ccc;}
    .product-tab-list .WSY_table td input{width:50px;height:20px;display:inline-block;}
    .product-tab-list .WSY_table .operation{display:flex;justify-content:space-around;align-items:center;}
    .input-tip{position:absolute;border:solid 1px #ccc;padding:2px 10px;background-color:#eee;font-size:12px;color:red;border-radius:10px;left:5px;top:-29px;min-width:100px;}
    .input-tip:before,.input-tip:after{display:block;content:'';position:absolute;}
    .input-tip:before{border-left:6px solid transparent;border-right:6px solid transparent;border-top:10px solid #eee;left:11px;bottom:-9px;z-index:9;}
    .input-tip:after{border-left:7px solid transparent;border-right:7px solid transparent;border-top:10px solid #ccc;left:10px;bottom:-10px;z-index:8;}
    .WSY_clorop p label{display:block;}
    .product-name{width:500px!important;}
    .product-input-list input{width:100px!important;}
    .product-input-list dt{}
    .product-input-list dd{width:150px;}

    .WSY_bulkboximgbox{display:inline-block;vertical-align:top;}
    .WSY_bulkboximg{float:none;display:inline-block;vertical-align:top;}

    /*上传属性图片*/
    .product-attr{height:30px;vertical-align:middle;min-width:200px;border-radius:3px;border:solid 1px #ddd; }
    .product-attr-list{margin:20px 0 30px 20px;}
    .product-attr-list .attr-list{margin:15px 0;}
    .product-attr-list .attr-file{position:relative;width:100px;height:30px;line-height:30px;text-align:center;background-color:#eee;border:solid 1px #ccc;font-size:12px;color:#666;cursor:pointer;}
    .product-attr-list .attr-name{min-width:40px;text-align:right;display:inline-block;vertical-align:middle;}
    .product-attr-list .attr-img{display:inline-block;vertical-align:middle;}
    .product-attr-list .attr-file-btn{position:absolute;width:100%;height:100%;left:0;top:0;opacity:0;cursor:pointer;}
    .product-attr-list .attr-img-box{display:none;}
    .product-attr-list .attr-img-box img{vertical-align:middle;width:50px;height:50px;vertical-align:middle;}
    .product-attr-list .attr-img-box .attr-delet{display:inline-block;vertical-align:middle;cursor:pointer;margin:0 0 0 10px;}
    input[readonly]{background-color:rgb(235,235,228);}
</style>
</head>

<body>
<!--内容框架-->
<div class="WSY_content">
    <!--列表内容大框-->
    <div class="WSY_columnbox">
        <?php require('../../../../wsy_prod/admin/Product/product/public/head.php');?>

        <ul class="product-tab-box edit"><!--编辑状态时，加class = edit，实现点击切换-->
            <li <?php if($sa == 1) echo 'class="tab-active"'; ?> onclick="sa(1);"><span>1</span>产品信息</li>
            <li <?php if($sa == 2) echo 'class="tab-active"'; ?> onclick="sa(2);"><span>2</span>选择分类</li>
            <li <?php if($sa == 3) echo 'class="tab-active"'; ?> onclick="sa(3);"><span>3</span>选择产品属性</li>
            <li <?php if($sa == 4) echo 'class="tab-active"'; ?> onclick="sa(4);"><span>4</span>上传属性图片</li>
            <li <?php if($sa == 5) echo 'class="tab-active"'; ?> onclick="sa(5);"><span>5</span>产品详情</li>
            <li <?php if($sa == 6) echo 'class="tab-active"'; ?> onclick="sa(6);"><span>6</span>其他属性</li>
            <li <?php if($sa == 7) echo 'class="tab-active"'; ?> onclick="sa(7);"><span>7</span>佣金分配</li>
        </ul>

        <!--关注用户开始-->
        <form id="frm_product" class="r_con_form" method="post" action="save_product.php?head=<?php echo $head?>&customer_id=<?php echo $customer_id_en; ?>&pagenum=<?php echo $pagenum; ?>&adminuser_id=<?php echo $adminuser_id; ?>&owner_general=<?php echo $owner_general; ?>&orgin_adminuser_id=<?php echo $orgin_adminuser_id; ?>" enctype="multipart/form-data">
            <div class="WSY_data">
                <div class="product-tab-list <?php if($sa == 1) echo 'tab-active'; ?>">
                    <dl class="WSY_bulkbox w90px product-input-box">
                        <dt>产品名称：</dt>
                        <dd>
                            <input type="text" name="name" id="name" class="product-name" value="<?php echo $product_name;?>" onkeyup="//checkname(this);">
                            <span style="color:red;">注意：不要使用tr、td等html标签和特殊符号"-"</span>
                        </dd>
                    </dl>

                    <dl class="WSY_bulkbox w90px product-input-box">
                        <dt>外部标识：</dt>
                        <dd>
                            <input type="text" name="foreign_mark" value="<?php echo $product_foreign_mark; ?>" class="form_input" size="5" maxlength="50" onkeyup="checkmark(this);">
                            <span style="color:red;">注意：不要使用tr、td等html标签和特殊符号"-"</span>
                        </dd>
                    </dl>

                    <div class="WSY_bulkbox02">
                    <!--<div class="WSY_bulkbox_top">-->
                        <div class="WSY_bulkboximgbox">
                            <div class="WSY_bulkboximg" id="WSY_bulkboximg">
                                <p>封面图片</p>
                                <iframe src="iframe_images_defaultproduct.php?customer_id=<?php echo $customer_id_en; ?>&product_id=<?php echo $product_id; ?>&default_imgurl=<?php echo $default_imgurl; ?>" height=200 width=1024 FRAMEBORDER=0 SCROLLING=no></iframe>
                            </div>
                            <input type=hidden name="default_imgurl" id="default_imgurl" value="<?php echo $default_imgurl ; ?>" />

                            <div class="WSY_bulkboximg" style="height:260px">
                                <p>产品备注</p>
                                <dl class="WSY_bulkdl">
                                    <span style="color:red;font-size: 14px;">（建议控制在30个字符以内）</span>
                                    <p>
                                        <div id="colorSelector" class="remark"><div style="background-color: <?php if(!empty($remark_color)){echo $remark_color;}else{echo "#000000";}?>;"></div></div>
                                    </p>
                                    <dd><textarea name="remarks" class="remarks" maxlength=30 style="color:<?php if(!empty($remark_color)){echo $remark_color;}else{echo "#000000";}?>;"><?php echo $remarks; ?></textarea></dd>
                                    <input type="hidden" name="remark_color" class="remark_color" value="<?php if(!empty($remark_color)){echo $remark_color;}else{echo '#000000';} ?>"  >
                                </dl>
                            </div>
                        </div>
                    <!--</div>-->
                    </div>
                    <div class="WSY_bulkbox02">
                        <div class="WSY_bulkboximgbox">
                            <div class="WSY_bulkboximg" id="WSY_bulkboximg" style="height:260px">
                                <p>产品图片</p>
                                <dl style="margin:30px 0px 0px 0px">
                                   <iframe id="frmProImgs" src="iframe_images.php?customer_id=<?php echo $customer_id_en; ?>&product_id=<?php echo $product_id; ?>&detail_template_type=<?php echo $detail_template_type; ?>" height="400" width="1024" FRAMEBORDER=0 SCROLLING=no></iframe>
                                </dl>
                            </div>
                            <input type=hidden name="imgids" id="imgids" value="<?php echo $imgids ; ?>" />
                            <input type=hidden name="img_link" id="img_link" value="<?php echo $imglink ; ?>" />
                            <input type=hidden name="img_3d" id="img_3d" value="<?php echo $img ; ?>" />

                        </div>
                        <div class="WSY_bulkboximg" style="height:260px">
                            <p>简短介绍</p>
                            <dl class="WSY_bulkdl">
                                <span style="color:red;font-size: 14px;">（建议控制在30个字符以内）</span>
                                <p>
                                    <div id="colorSelector" class="product_introduce"><div style="background-color: <?php if(!empty($short_introduce_color)){echo $short_introduce_color;}else{echo "#000000";}?>;"></div></div>
                                </p>
                                <dd><textarea name="introduce" class="briefdesc" maxlength=30 style="color:<?php if(!empty($short_introduce_color)){echo $short_introduce_color;}else{echo "#000000";}?>;"><?php echo $introduce; ?></textarea></dd>
                                <input type="hidden" name="introduce_color" class="introduce_color" value="<?php if(!empty($short_introduce_color)){echo $short_introduce_color;}else{echo '#000000';} ?>">

                            </dl>
                        </div>
                    </div>

                    <div class="product-footer-btn">
                        <button class="WSY_button"  type="button" onclick="first_step(this);">下一步</button>
                    <?php if($status == 0 && $status !=null){ ?><!--待审核状态 0:、待审核-->
                        <button class="WSY_button" onclick="offer_save(this)" type="button">保存通过<img style="position:relative;top:2px;left:5px;width:15px;" id="product_offer" src="../../Common/images/Base/help.png"></button>
                        <button class="WSY_button" onclick="offer(this)" type="button">通过上架</button>
                        <button class="WSY_button" onclick="refundProduct(this)" type="button">驳回</button>
                        <button class="WSY_button" onclick="javascript:history.go(-1);" type="button">返回</button>
                    <?php } else {?>
                        <button class="WSY_button" <?php if ($product_id == -1) echo "style='display:none'";?> type="button" onclick="saveProduct()">提交保存</button>
                    <?php } ?>
                    </div>
                </div>

                <div class="product-tab-list <?php if($sa == 2) echo 'tab-active'; ?>">
                    <dl class="WSY_bulkdl w90px">
                        <div class="form_box">
                            <label class="label-control">产品分类：</label>
                            <div id='f-box'>
                                <?php
                                $type_ids_new   = trim($type_ids,',');  //去掉两边的逗号
                                $type_id_sel    = -1;   //已选择分类id
                                $name_sel       = "";   //已选择分类名
                                $parent_id_sel  = -1;   //已选择分类上级id
                                $type_level     = 1;    //已选择分类级别
                                $top_id         = -1;   //已选择分类顶级id
                                $gflag          = "";   //已选择分类基因
                                if( !empty( $type_ids_new ) ){
                                    //查询已选分类
                                    $query_type_sel = "select id,name,parent_id,level,top_id,gflag from weixin_commonshop_types where id in(".$type_ids_new.") and isvalid=true and is_shelves=1";
                                    $result_type_sel = _mysql_query($query_type_sel) or die('Query_type_sel failed:'.mysql_error());
                                    while( $row_type_sel = mysql_fetch_object($result_type_sel) ){
                                        $type_id_sel    = $row_type_sel -> id;
                                        $name_sel       = $row_type_sel -> name;
                                        $parent_id_sel  = $row_type_sel -> parent_id;
                                        $type_level     = $row_type_sel -> level;
                                        $top_id         = $row_type_sel -> top_id;
                                        $gflag          = $row_type_sel -> gflag;

                                        $up_typeid      = -1;
                                        $up_typename    = "";
                                        $up_typenames    = "";
                                        if( $type_level > 1 ){
                                            $gflag = trim($gflag,',');//去掉两边的逗号 ,-1,1227,1289,1303,
                                            $gflag = substr($gflag,3);

                                            $sql_type = "select id,name from weixin_commonshop_types where id in(".$gflag.") and isvalid=true and is_shelves=1 order by level asc";
                                            $result_type_up = _mysql_query($sql_type) or die('sql_type failed:'.mysql_error());
                                            while( $row_up = mysql_fetch_object($result_type_up) ){
                                                $up_typeid      = $row_up -> id;
                                                $up_typename    = $row_up -> name;
                                                $up_typenames   .= $up_typename.">";
                                            }
                                        }

                                    $up_typenames       .= $name_sel;
                                    ?>
                                <div class="new_list" id="type_<?php echo $type_id_sel;?>" data-type_id="<?php echo $type_id_sel;?>" checked="checked">
                                    <p><?php echo $up_typenames;?></p>
                                    <span class="del_type" data-type_id="<?php echo $type_id_sel;?>">x</span>
                                </div>
                                <?php
                                }
                            }
                            ?>
                            </div>
                        </div>

                        <div class="msg_box">
                            <div class="msg" style="display:block;margin-top:10px;">
                                <div class="option_box">
                                    <p>一级</p>
                                    <div class="option">
                                        <ul class="level_1" >
                                        <?php
                                        //一级分类
                                        $query_type = "SELECT id,name,is_privilege FROM weixin_commonshop_types WHERE customer_id=".$customer_id." AND isvalid=true AND is_shelves=1 AND parent_id=-1";
                                        if($sort_str){
                                            $query_type .= ' order by field(id'.$sort_str.')';
                                        }
                                        $result_type = _mysql_query($query_type) or die('Query_type failed:'.mysql_error());
                                        while( $row_type = mysql_fetch_object($result_type) ){
                                            $type_id   = $row_type -> id;
                                            $type_name = $row_type -> name;
                                            $is_privilege_type = $row_type->is_privilege;
                                            //统计二级分类数量
                                            $query_type2 = "SELECT count(1) as two_count FROM weixin_commonshop_types WHERE customer_id=".$customer_id." AND isvalid=true AND is_shelves=1 AND parent_id=".$type_id;
                                            $two_count = 0;
                                            $result_ctype = _mysql_query($query_type2) or die('Query_ctype failed:'.mysql_error());
                                            while( $row_ctype = mysql_fetch_object($result_ctype) ){
                                                $two_count  = $row_ctype -> two_count;
                                            }
                                        ?>
                                            <li <?php if( $two_count > 0 ){ ?>  class="type_next" <?php }?> typeid="<?php echo $type_id;?>" level="1" type_name="<?php echo $type_name;?>">
                                                <span class="text" ><?php if($is_privilege_type==1){?>
                                                    <img src="../../../mshop/images/special.png" alt="" style="margin-top:5px;margin-right:15px;float:left;width:30px">
                                            <?php }?><?php echo $type_name;?></span>

                                            <?php
                                            if( $two_count > 0 ){
                                            ?>
                                                <img class="right" src="../../Common/images/Product/gray_right.png">
                                            <?php
                                            }else{
                                            ?>
                                                <i class="add-icon">
                                                <span data-type_id="<?php echo $type_id;?>" class="add-list" type_name="<?php echo $type_name;?>" >+</span>
                                                </i>
                                            <?php
                                            }
                                            ?>
                                            </li>
                                        <?php
                                        }
                                        ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </dl>
                    <div class="product-footer-btn">
                        <button class="WSY_button"  type="button" onclick="prev_tab(this);">上一步</button>
                        <button class="WSY_button"  type="button" onclick="second_step(this);">下一步</button>
                    <?php if($status == 0 && $status !=null){ ?><!--待审核状态 0:、待审核-->
                        <button class="WSY_button" onclick="offer_save(this)" type="button">保存通过<img style="position:relative;top:2px;left:5px;width:15px;" id="product_offer" src="../../Common/images/Base/help.png"></button>
                        <button class="WSY_button" onclick="offer(this)" type="button">通过上架</button>
                        <button class="WSY_button" onclick="refundProduct(this)" type="button">驳回</button>
                        <button class="WSY_button" onclick="javascript:history.go(-1);" type="button">返回</button>
                    <?php } else {?>
                        <button class="WSY_button" <?php if ($product_id == -1) echo "style='display:none'";?> type="button" onclick="saveProduct()">提交保存</button>
                    <?php } ?>
                    </div>
                </div>

                <div class="product-tab-list <?php if($sa == 3) echo 'tab-active'; ?>">
                    <div id="product-tab-ex">
                      <dl class="WSY_bulkbox w90px product-input-box product-input-list">
                        <dt>原价：</dt>
                        <dd class='orgin_price'>
                            <?php if(OOF_P != 2) echo OOF_S ?>
                            <input type="text" name="orgin_price" value="<?php echo $product_orgin_price; ?>" class="form_input num_check" size="5" maxlength="10">
                            <?php if(OOF_P == 2) echo OOF_S ?>
                        </dd>
                        <dt>
                            <?php if($nowprice_title){echo $nowprice_title;}
                            else if($base_nowprice_title){echo $base_nowprice_title;}
                            else{echo "现价";}?>：
                        </dt>
                        <dd class='now_price'>
                            <?php if(OOF_P != 2) echo OOF_S ?>
                            <input type="text" name="now_price" value="<?php echo $product_now_price; ?>" class="form_input num_check calc_np" size="5" maxlength="10">
                            <?php if(OOF_P == 2) echo OOF_S ?>
                        </dd>
                        <dt>成本：</dt>
                        <dd class='for_price'>
                            <?php if(OOF_P != 2) echo OOF_S ?>
                            <input type="text" name="for_price" value="<?php echo $product_for_price; ?>" class="form_input num_check calc_fp" size="5" maxlength="10">
                            <?php if(OOF_P == 2) echo OOF_S ?>
                        </dd>
                        <dt>供货价：</dt>
                        <dd class='base_price'>
                            <?php if(OOF_P != 2) echo OOF_S ?>
                            <input type="text" name="cost_price" value="<?php echo $product_cost_price; ?>" class="form_input num_check calc_bp" size="5" maxlength="10">
                            <?php if(OOF_P == 2) echo OOF_S ?>
                        </dd>
                      </dl>

                      <dl class="WSY_bulkbox w90px product-input-box product-input-list">
						            <!--
                        <dt>单位：</dt>
                        <dd>
                            <input type="text" name="unit" value="<?php echo $product_unit; ?>" class="form_input" size="5" maxlength="10">
                        </dd>
						            -->
                        <dt>重量：</dt>
                        <dd>
                            <input type="text" name="weight" value="<?php echo $product_weight; ?>" class="form_input num_check " size="5" maxlength="10">KG
                        </dd>
                        <dt>所需积分：</dt>
                        <dd class='neet_score'>
                            <input type="text" name="need_score" value="<?php echo $product_need_score; ?>" class="form_input num_check" size="5" maxlength="10">
                        </dd>
                        <dt>库存：</dt>
                        <dd class='store_num'>
                            <input type="text" name="storenum" value="<?php echo $product_storenum; ?>" class="form_input num_check" size="5" maxlength="10">
                        </dd>
                        <dt id="product-input-ex"></dt>
                      </dl>
                    </div>
                    <dl class="WSY_bulkdl w90px">
                        <ul class="WSY_bulkul wdw">
                        <?php
                        /*
                        父级 ：1没有关联  2选中的分类关联（父级）属性  3选中的分类关联（子级）属性   显示
                        子级： 1没有关联  2选中的分类关联（子级） 3产品选择的属性 显示
                        不显示的场景：已经关联分类而未被选中的属性
                        */
                            /* 查询选中的分类中关联的属性 start  */
                            $new_typeid_arr = array();
                            //查找产品选择的子类以及父类
                            for($i=0;$i<count($typeid_arr);$i++){
                                if($typeid_arr[$i] =='' || $typeid_arr[$i] == 0 || $typeid_arr[$i] == null){    //清除无效的数值
                                    continue;
                                }
                                array_push($new_typeid_arr,$typeid_arr[$i]);        //重新组合数组

                                //查找父类，不排除属性关联的是父类分类
                                $type_parent_id = -1;
                                $query = "select parent_id from weixin_commonshop_types where isvalid=true and customer_id=".$customer_id." and id=".$typeid_arr[$i]."";
                                //echo $query;
                                $result = _mysql_query($query) or die('Query failed: ' . mysql_error());
                                while ($row = mysql_fetch_object($result)){
                                    $type_parent_id         = $row->parent_id;
                                }
                                if($type_parent_id>0){                              //当找到父类则加入数组
                                    array_push($new_typeid_arr,$type_parent_id);
                                }
                            }
                            $typeid_arr_str = implode(',',$new_typeid_arr) ;        //数组转字符串

                            //查找产品选择的分类关联的属性
                            $product_sel_type_pros = array();

                            if(count($new_typeid_arr)>0){                           //当选择了分类才查找关联的属性
                                $extends_id = -1;
                                $extends_pros_id = -1;
                                $query3 = "select id,relation_type_id,pros_id from weixin_commonshop_pros_extends where isvalid=true and customer_id=".$customer_id." and relation_type_id in (".$typeid_arr_str.")";
                                //echo $query3;
                                $result3 = _mysql_query($query3) or die('L628 Query failed: ' . mysql_error());
                                while ($row3 = mysql_fetch_object($result3)){
                                    $extends_id = $row3->id;
                                    $extends_pros_id = $row3->pros_id;
                                    array_push($product_sel_type_pros,$extends_pros_id);
                                }

                            }
                            //var_dump($product_sel_type_pros);
                            /* 查询选中的分类中关联的属性 end */

                            /*查找已选的属性和父类属性 start*/

                            $new_pros_arr = array();                //所选的属性以及父类属性
                            for($i=0;$i<count($propertyarr);$i++){
                                if($propertyarr[$i] =='' || $propertyarr[$i] == 0 || $propertyarr[$i] == null){ //清除无效的数值
                                    continue;
                                }
                                array_push($new_pros_arr,$propertyarr[$i]);     //重新组合数组

                                //查找父类
                                $pro_parent_id = -1;
                                $query = "select parent_id from weixin_commonshop_pros where is_wholesale=0 AND isvalid=true and customer_id=".$customer_id." and id=".$propertyarr[$i]."";
                                //echo $query;
                                $result = _mysql_query($query) or die('Query failed: ' . mysql_error());
                                while ($row = mysql_fetch_object($result)){
                                    $pro_parent_id      = $row->parent_id;
                                }
                                if($pro_parent_id>0){                               //当找到父类则加入数组
                                    array_push($new_pros_arr,$pro_parent_id);
                                }

                            }
                            //var_dump($new_pros_arr)   ;
                            /*查找已选的属性和父类属性 end*/

                            /*产找所有分类关联的属性 start*/
                            $relate_pros = array();
                            $query = "select pros_id,relation_type_id from weixin_commonshop_pros_extends where isvalid=true and customer_id=".$customer_id."";
                            //echo $query;
                            $result = _mysql_query($query) or die('Query failed: ' . mysql_error());
                            while ($row = mysql_fetch_object($result)){
                                    $pros_id                = $row->pros_id;
                                    $relation_type_id       = $row->relation_type_id;
                                    if($pros_id >0 && $relation_type_id>0){                             //当找到父类则加入数组
                                    array_push($relate_pros,$pros_id);
                                }
                            }
                            //var_dump($relate_pros)    ;
                            /*产找所有分类关联的属性 end*/

                            /*************************商城属性遍历 start**************************/
                            $pros_array = array();
                            $parent_id = -1;        //父类ID
                            $parent_name = '';      //父类名称
                            // var_dump($supply_id<0 && $status!=1);
                            $supply_id = $supply_id==-1?$_REQUEST['supply_id']:$supply_id;
                             if($supply_id<=0){
                                  $query="select id,name,is_suning_pros from weixin_commonshop_pros where is_wholesale=0 AND isvalid=true and parent_id=-1 and customer_id=".$customer_id." and supply_id<0";
                             }else{
                                  $query="select id,name,is_suning_pros from weixin_commonshop_pros where is_wholesale=0 AND isvalid=true and parent_id=-1 and customer_id=".$customer_id." and supply_id=".$supply_id;
                             }
                             //echo $query;
                             $result = _mysql_query($query) or die('L643 Query failed11: ' . mysql_error());
                             while ($row = mysql_fetch_object($result)) {
                                $parent_id = $row->id;      //父类ID
                                $parent_name = $row->name;
                                $is_suning_pros = $row->is_suning_pros;
                                if ($is_suning_pros == 1) {
                                  $parent_name .= '(苏宁)';
                                }
                                $is_parent_type = 0;
                                if(in_array($parent_id,$product_sel_type_pros)){        //查询所选的分类中是否已经关联属性，0表示没有，1表示有
                                    $is_parent_type = 1;
                                }

                                //查询该属性是否关联分类
                                $extends_id         = -1;
                                $relation_type_id   = -1;
                                $query3 = "select id,relation_type_id from weixin_commonshop_pros_extends where isvalid=true and customer_id=".$customer_id." and pros_id=".$parent_id."";
                                $result3 = _mysql_query($query3) or die('L654 Query failed: ' . mysql_error());
                                while ($row3 = mysql_fetch_object($result3)){
                                    $extends_id         = $row3->id;
                                    $relation_type_id   = $row3->relation_type_id;
                                    $parent_relation_type[$parent_id] = $relation_type_id;
                                }

                                //累加选中的子类分类中是否有关联属性，0表示无，1表示有
                               $tem_array = array();
                               $sum_is_child_type = 0;
                               $query2="select id,name,is_suning_pros from weixin_commonshop_pros where isvalid=true and parent_id=".$parent_id." and customer_id=".$customer_id;
                               $result2 = _mysql_query($query2) or die('L663 Query failed12: ' . mysql_error());
                               while ($row2 = mysql_fetch_object($result2)) {
                                   $p_id = $row2->id;
                                   $p_name = $row2->name;
                                   $p_is_suning_pros = $row2->is_suning_pros;
                                   if ($p_is_suning_pros > 0) {
                                     $p_name .= '(苏宁)';
                                   }
                                    $is_child_type = 0;
                                    if(in_array($p_id,$product_sel_type_pros)){         //查询所选的分类中是否已经关联属性，有则显示
                                        $is_child_type = 1;
                                    }
                                    $sum_is_child_type += $is_child_type;

                                    //查询该属性是否关联分类，无则默认显示
                                    $extends_id2      = -1;
                                    $query4 = "select id from weixin_commonshop_pros_extends where isvalid=true and customer_id=".$customer_id." and pros_id=".$p_id."";
                                    $result4 = _mysql_query($query4) or die('L675 Query failed: ' . mysql_error());
                                    while ($row4 = mysql_fetch_object($result4)){
                                        $extends_id2        = $row4->id;
                                    }


                                      array_push($tem_array,
                                          array(
                                           'child_id'=>$p_id,
                                           'child_name'=>$p_name,
                                           'child_is_suning_pros'=>$p_is_suning_pros,
                                           'is_child_type'=>$is_child_type,
                                           'extends_id2'=>$extends_id2,
                                           )
                                       );

                               }
                                //echo $is_parent_type.'_'.$extends_id.'_'.$sum_is_child_type.'_'.$parent_id.'<br>';

                                //if($is_parent_type==1 || $extends_id<0 ||$sum_is_child_type ==1){             //显示的场景：该父类属性关联了选择的分类，该父类属性未被关联，该子类被选择的分类关联
                                //if( $extends_id>0 || !($is_parent_type==0 && $extends_id>0 && $sum_is_child_type ==0) || in_array($parent_id,$new_pros_arr) ){                //显示的场景：该父类属性关联了选择的分类，该父类属性未被关联，该子类被选择的分类关联
                                if(check_pros_show($product_sel_type_pros,$new_pros_arr,$relate_pros,$parent_id)>0){
                            ?>
                            <dd class="add_relation_pros_<?php echo $relation_type_id;?>" pro_parent_id="<?php echo $parent_id;?>">
                                <div class="WSY_cloropbox">
                                    <span id="parent_name_<?php echo $parent_id;?>"><?php echo $parent_name; ?></span><input type="hidden" name="hidden_parent" value="<?php echo $parent_id;?>"/>
                                    <div class="WSY_clorop">
                                <?php
                                        //遍历所有子类属性
                                        foreach($tem_array as $key => $value){
                                            $p_id               = $value['child_id'];
                                            $p_name             = $value['child_name'];
                                            $p_is_suning_pros   = $value['child_is_suning_pros'];
                                            $p_is_child_type    = $value['is_child_type'];
                                            $p_extends_id2      = $value['extends_id2'];


                                        //echo $p_is_child_type.'_'.$p_extends_id2.'_'.$p_id.'<br>';
                                       //if($p_is_child_type==1 || $p_extends_id2<0){           //显示场景：该子级未被关联，该子级被选择的分类关联
                                      // if(!($p_is_child_type==0 && $p_extends_id2>0) || in_array($p_id,$new_pros_arr)){
                                         if(check_pros_show($product_sel_type_pros,$new_pros_arr,$relate_pros,$p_id)>0){
                                ?>
                                            <p><label for="<?php echo $p_id; ?>"><input type="checkbox" data_name="prop_<?php echo $parent_id; ?>" data_pid="<?php echo $p_id; ?>" data_text="<?php echo $p_name; ?>" data_parent="<?php echo $parent_id; ?>" value="<?php echo $p_id; ?>" <?php if($propertylst->Contains($p_id)){ $pidpnames = $pidpnames.$p_id.",".$parent_name."(".$p_name."),".$parent_id."_"; ?>checked<?php } ?> name="ptids" <?php if($skuid > 0){ echo 'disabled="disabled"'; }; if($p_is_suning_pros > 0){ echo 'disabled="disabled"';} ?> onclick="chkPro(this);">
                                                    <?php echo $p_name; ?>
                                                    <input type="hidden" id="<?php echo $p_id; ?>" value="<?php echo $p_name; ?>"/>
                                            </label></p>
                                <?php
                                       }
                                    }
                                         /* array_push($pros_array,array(
                                         'parent_id'=>  $parent_id,
                                         'parent_name'=>  $parent_name,
                                         'is_parent_type'=>  $is_parent_type,
                                         'child'=>  $tem_array
                                          ));*/
                                ?>
                                    </div>
                                </div>
                            </dd>

                            <?php

                                }
                             }
                             //var_dump($pros_array);
                             /*************************商城属性遍历 end**************************/
                           ?>

                             <?php
                             $pidpnames = rtrim($pidpnames,"_");
                            ?>
                            <input type=hidden name="pidpnames" id="pidpnames" value="<?php echo $pidpnames; ?>" />
                            <input type=hidden name="propertyids" id="propertyids" value="<?php echo $product_propertyids; ?>"/>
                        </ul>
                    </dl>
                    <dl class="WSY_bulkdl WSY_bulkdl03 w90px" >
                        <dt>产品批发属性：</dt>
                        <dd class="dd_margin">
                            <?php
                                $wholesale_parentid = -1;
                                $wholesale_childid = "";
                                $wholesale_arr = array();
                                $sql = "SELECT wholesale_parentid,wholesale_childid FROM weixin_commonshop_product_extend WHERE isvalid=true AND customer_id=$customer_id AND pid=$product_id LIMIT 1";
                                //echo $sql;
                                $res = _mysql_query($sql)or die ('query faild 998' .mysql_error());
                                while( $row = mysql_fetch_object($res) ){
                                    $wholesale_parentid = $row->wholesale_parentid;
                                    $wholesale_childid  = $row->wholesale_childid;
                                }
                                $wholesale_arr = explode("_", $wholesale_childid);
                            ?>
                            <select name="" id="wholesale" >
                                <option value="-1">请选择批发属性</option>
                                <?php


                                    $id = -1;
                                    $name = "";
                                    $query = "SELECT id,name FROM weixin_commonshop_pros where isvalid=true AND customer_id=$customer_id AND is_wholesale=1 AND parent_id=-1";
                                    $result= _mysql_query($query) or die ('query faild 989' .mysql_error());
                                    while( $row = mysql_fetch_object($result)){
                                        $id     = $row->id;
                                        $name   = $row->name;
                                ?>
                                <option value="<?php echo $id;?>" <?php if($wholesale_parentid==$id){echo 'selected';}?> ><?php echo $name;?></option>
                                <?php }?>
                            </select>
                        </dd>
                        <input type="hidden" name="wholesale_id" value="<?php echo $wholesale_parentid; ?>" id="wholesale_id">
                    </dl>
                    <?php
                        $parent_id = -1;
                        $parent_name = "";
                        $query = "SELECT id,name FROM weixin_commonshop_pros where isvalid=true AND customer_id=$customer_id AND is_wholesale=1 AND parent_id=-1";
                        $result= _mysql_query($query) or die ('query faild 989' .mysql_error());
                        while( $row = mysql_fetch_object($result)){
                            $parent_id    = $row->id;
                            $parent_name  = $row->name;
                    ?>
                            <dl class="WSY_bulkdl WSY_bulkdl03 w90px  wholesale_date" id="wholesale_<?php echo $parent_id;?>" style="margin-left:110px;margin-top:-10px;<?php if($wholesale_parentid!=$parent_id){echo 'display:none';}?>;">
                            <!-- <dt>产品批发属性：</dt> -->
                                <dd class="">
                                    <div class="WSY_cloropbox add_relation_pros_-1"  pro_parent_id="<?php echo $parent_id;?>">
                                        <span id="parent_name_<?php echo $parent_id;?>" ><?php echo $parent_name; ?></span><input type="hidden" name="hidden_parent" value="<?php echo $parent_id;?>"/>
                                        <div class="WSY_clorop">
                                            <?php
                                                $sql = "SELECT id,name,is_suning_pros FROM weixin_commonshop_pros WHERE isvalid=true AND customer_id=$customer_id AND parent_id=$parent_id";
                                                $res = _mysql_query($sql) or die ('query faild 1019' .mysql_error());
                                                while( $info = mysql_fetch_object($res) ){
                                                    $id = $info->id;
                                                    $name = $info->name;
                                                    $is_suning_pros = $info->is_suning_pros;
                                            ?>
                                            <p><input type="checkbox" class="child_wholesale_c" id="child_wholesale" data_name="prop_<?php echo $parent_id; ?>" data_pid="<?php echo $id; ?>" data_text="<?php echo $name; ?>" data_parent="<?php echo $parent_id; ?>" value="<?php echo $id; ?>" <?php if(in_array($id,$wholesale_arr)){echo 'checked';}?>  name="ptids[]" <?php if($is_suning_pros > 0){ echo 'disabled="disabled"';} ?> onclick="chkPro(this);"><?php echo $name;?>
                                                <input type="hidden" id="<?php echo $id; ?>" value="<?php echo $name; ?>"/>
                                            </p>
                    <?php }?>
                                        </div>
                                    </div>
                                </dd>
                          </dl>
                      <?php }?>
                    <table width="97%" class="WSY_table">
                        <thead class="WSY_table_header">
                            <th width="">属性名称</th>
                            <th>原价</th>
                            <th>现价</th>
                            <th>成本</th>
                            <th>供货价</th>
                            <!--<th>单位</th>-->
                            <th>重量(KG)</th>
                            <th>所需积分</th>
                            <th>库存</th>
                            <th>外部标识</th>
                            <th>总返佣金额</th>
                            <th>操作管理</th>
                        </thead>
                        <tbody id="div_proprices">

                        </tbody>
                    </table>

                    <div class="product-footer-btn">
                        <button class="WSY_button"  type="button" onclick="prev_tab(this);">上一步</button>
                        <button class="WSY_button"  type="button" onclick="third_step(this);">下一步</button>
                    <?php if($status == 0 && $status !=null){ ?><!--待审核状态 0:、待审核-->
                        <button class="WSY_button" onclick="offer_save(this)" type="button">保存通过<img style="position:relative;top:2px;left:5px;width:15px;" id="product_offer" src="../../Common/images/Base/help.png"></button>
                        <button class="WSY_button" onclick="offer(this)" type="button">通过上架</button>
                        <button class="WSY_button" onclick="refundProduct(this)" type="button">驳回</button>
                        <button class="WSY_button" onclick="javascript:history.go(-1);" type="button">返回</button>
                    <?php } else {?>
                        <button class="WSY_button" <?php if ($product_id == -1) echo "style='display:none'";?> type="button" onclick="saveProduct()">提交保存</button>
                    <?php } ?>
                    </div>
                </div>

                <div class="product-tab-list <?php if($sa == 4) echo 'tab-active'; ?>">
                    <dl class="WSY_bulkbox w90px product-input-box" >
                        <dt style="width:auto;line-height:30px;">选择已选择的产品属性：</dt>
                        <dd>
                            <select class="product-attr" name="product_attr">
                            </select>
                            <span style="color:red;">不选择或者是选择的属性没有上传，那么默认显示产品封面图</span>
                        </dd>
                    </dl>
                    <dl class="WSY_bulkbox w90px product-input-box" style="margin:0 0 0 20px;">
                        <dt style="width:auto;color:#888;line-height:30px;">图片尺寸：</dt>
                        <dd style="color:#888;line-height:30px;">640*640，建议在500K以内</dd>
                    </dl>
                    <div class="product-attr-list">

                    </div>

                    <div class="product-footer-btn">
                        <button class="WSY_button"  type="button" onclick="prev_tab(this);">上一步</button>
                        <button class="WSY_button"  type="button" onclick="third_step(this);">下一步</button>
                    <?php if($status == 0 && $status !=null){ ?><!--待审核状态 0:、待审核-->
                        <button class="WSY_button" onclick="offer_save(this)" type="button">保存通过<img style="position:relative;top:2px;left:5px;width:15px;" id="product_offer" src="../../Common/images/Base/help.png"></button>
                        <button class="WSY_button" onclick="offer(this)" type="button">通过上架</button>
                        <button class="WSY_button" onclick="refundProduct(this)" type="button">驳回</button>
                        <button class="WSY_button" onclick="javascript:history.go(-1);" type="button">返回</button>
                    <?php } else {?>
                        <button class="WSY_button" <?php if ($product_id == -1) echo "style='display:none'";?> type="button" onclick="saveProduct()">提交保存</button>
                    <?php } ?>
                    </div>
                </div>

                <div class="product-tab-list <?php if($sa == 5) echo 'tab-active'; ?>">
                    <dl class="WSY_bulkdl WSY_bulkdldt w90px">
                        <dt style="width:120px">语音链接：</dt>
                        <dd><input type="text" style="width:200px" id="product_voice" name="product_voice" value="<?php echo $product_voice; ?>" placeholder="请输入链接"></dd>
                        <dd style="color:red">（必须填写MP3等格式外链接,方法一：本地文件->上传至QQ邮箱中转站->下载该文件->复制下载链接->从http截取至MP3->得到链接）</dd>
                    </dl>
                    <dl class="WSY_bulkdl WSY_bulkdldt w90px">
                        <dt style="width:120px">视频链接：</dt>
                        <dd><input type="text" style="width:200px" id="product_vedio" name="product_vedio" value='<?php echo $product_vedio; ?>' placeholder='请输入链接'></dd>
                        <dd style="color:red">（请填写通用代码，如腾讯视频->分享->复制相关代码）</dd>
                    </dl>
                    <dl class="WSY_bulkdl w90px">
                        <dt class="editor edit1 selected" style="background-color:white;" id="edit1" data-name='description' onclick="selectText(this,1)">详细介绍</dt>
                        <dt class="editor edit2" id="edit2" data-name='specifications' onclick="selectText(this,2)">产品规格</dt>
                        <dt class="editor edit3" id="edit3" data-name='service' onclick="selectText(this,3)">售后保障</dt>
                        <input type=hidden id="selectText_submit">
                        <!-- <div class="text_box input description">
                            <textarea id="editor1"   name="description"><?php echo $product_description; ?></textarea>
                        </div>
                                        <div class="text_box input specifications" style="display:none">
                            <textarea id="editor2"   name="specifications"><?php echo $specifications; ?></textarea>
                        </div>
                                        <div class="text_box input service" style="display:none">
                            <textarea id="editor3"   name="service"><?php echo $customer_service; ?></textarea>
                        </div>  -->
                        <div class="text_box input description">
                        <textarea id="editor"><?php echo $product_description; ?></textarea>
                        </div>
                        <!-- <div class="editor1"><textarea class="text_box input description" id="editor1" name="p-details" placeholder="详细介绍"></textarea></div> -->
                        <!-- <div class="editor2" style="display:none"><textarea class="" rows="5" id="editor2" name="p-specifications" placeholder="产品规格"></textarea></div>
                        <div class="editor3" style="display:none"><textarea class="" rows="5" id="editor3" name="p-service" placeholder="售后保障"></textarea></div> -->

                        <div style="display:none"><textarea name="description" id="description" placeholder="详细介绍"><?php echo $product_description; ?></textarea></div>
                        <div style="display:none"><textarea name="specifications" id="specifications" placeholder="产品规格"><?php echo $specifications; ?></textarea></div>
                        <div style="display:none"><textarea name="service" id="service" placeholder="售后保障"><?php echo $customer_service; ?></textarea></div>
                    </dl>
                    <div class="product-footer-btn">
                        <button class="WSY_button"  type="button" onclick="prev_tab(this)">上一步</button>
                        <button class="WSY_button"  type="button" onclick="next_tab(this)">下一步</button>
                    <?php if($status == 0 && $status !=null){ ?><!--待审核状态 0:、待审核-->
                        <button class="WSY_button" onclick="offer_save(this)" type="button">保存通过<img style="position:relative;top:2px;left:5px;width:15px;" id="product_offer" src="../../Common/images/Base/help.png"></button>
                        <button class="WSY_button" onclick="offer(this)" type="button">通过上架</button>
                        <button class="WSY_button" onclick="refundProduct(this)" type="button">驳回</button>
                        <button class="WSY_button" onclick="javascript:history.go(-1);" type="button">返回</button>
                    <?php } else {?>
                        <button class="WSY_button" <?php if ($product_id == -1) echo "style='display:none'";?> type="button" onclick="saveProduct()">提交保存</button>
                    <?php } ?>
                    </div>
                </div>

                <div class="product-tab-list <?php if($sa == 6) echo 'tab-active'; ?>">
                    <dl class="WSY_bulkdl w90px">
                        <dt>产品标签：</dt>
                        <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($product_isout){?>checked<?php } ?> id="chk_isout" onclick="changeOut(this);"><label for="chk_isout">下架</label></dd>
                        <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($product_isnew){?>checked<?php } ?> id="chk_isnew" onclick="changeNew(this);"><label for="chk_isnew">新品</label></dd>
                        <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($product_ishot){?>checked<?php } ?> id="chk_ishot" onclick="changeHot(this);"><label for="chk_ishot">热卖</label></dd>
                        <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($product_issnapup){?>checked<?php } ?> id="chk_issnapup" onclick="changeSnap(this);"><label for="chk_issnapup">抢购</label><img style="width:12px;" id="snapup_product" src="../../Common/images/Base/help.png"></dd>
                        <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($product_isvp){?>checked<?php } ?> id="chk_isvp" onclick="changeVp(this);"><label for="chk_isvp" style="float:left">vp产品</label><img style="width:12px;" id="vp_product" src="../../Common/images/Base/help.png"></dd>
                        <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($is_virtual){?>checked<?php } ?> id="chk_virtual" onclick="changeVirtual(this);"><label for="chk_virtual" style="float:left">虚拟产品</label><img style="width:12px;" id="product_virtual" src="../../Common/images/Base/help.png"></dd>
                        <dd class="WSY_bulkdldd dd_margin" <?php if ($is_rebate_open == 0) echo "style='display:none'";?>><input type="checkbox" <?php if($is_currency){?>checked<?php } ?> id="chk_currency" onclick="changeCurrency(this);"><label for="chk_currency"><?php echo defined('PAY_CURRENCY_NAME') ?PAY_CURRENCY_NAME: '购物币'; ?>产品</label></dd>
                        <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($is_guess_you_like){?>checked<?php } ?> id="chk_guess_you_like" onclick="changeGuess_you_like(this);"><label for="chk_guess_you_like">猜您喜欢产品</label><img style="width:12px;" id="product_guess_you_like" src="../../Common/images/Base/help.png"></dd>
                        <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($product_is_free_shipping){?>checked<?php } ?> id="chk_freeshipping" onclick="changeFree_shipping(this);"><label for="chk_freeshipping">包邮</label></dd>
                        <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($isscore){?>checked<?php } ?> id="chk_isscore" onclick="changeisscore(this);"><label for="chk_isscore">兑换专区</label></dd>
                        <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($islimit){?>checked<?php } ?> id="chk_islimit" onclick="changeislimit(this);"><label for="chk_islimit">限购</label></dd>
                        <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($is_first_extend){?>checked<?php } ?> id="chk_is_first_extend" onclick="changeIsFirstRxtend(this);"><label for="chk_is_first_extend">首次推广奖励</label></dd>
                        <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($istax==1){?>checked<?php } ?> id="chk_istax" onclick="tax(this);"><label for="chk_tax">税收产品</label></dd>

                        <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($is_privilege==1){echo "checked";}?> id="privilege_level"  name="is_privilege" onclick="change_Privilege(this);"><label for="">特权专区</label></dd>
                        <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($link_package>0){?>checked<?php } ?> id="is_link_package" name="is_package" onclick="change_link_package(this);"><label for="">关联礼包</label></dd>
  
                        <dd class="WSY_bulkdldd dd_margin" <?php if ($sendstyle_pickup == 0) echo "style='display:none'";?>><input type="checkbox" <?php if($is_pickup){?>checked<?php } ?> id="chk_ispickup" onclick="changeispickup(this);"><label for="chk_ispickup">自提产品</label></dd>

                        <?php
                         $link_coupons_array=array();
                          if($link_coupons==-1){
                             $is_open_link_coupons=0;
                          }else{
                             $is_open_link_coupons=1;
                             $link_coupons_array1=explode(',',$link_coupons);
                             foreach ($link_coupons_array1 as $key => $value) {
                                $query_open_coupons="select is_open,id from weixin_commonshop_coupons where isvalid=true and id=".$value." limit 1";
                                $resule_open_coupons=_mysql_query($query_open_coupons) or die(" faild：".mysql_error());
                                while($row = mysql_fetch_object($resule_open_coupons)){
                                    $is_open=$row->is_open;
                                    $id=$row->id;
                                    if($is_open>0){
                                        $link_coupons_array[]=$id;
                                    }
                                }
                             }
                            $link_coupons=implode(",",$link_coupons_array);
                          }
                        ?>
                        <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($is_open_link_coupons>0){ ?>checked="checked" <?php } ?> id="coupons" name="link_coupons" value="1" onclick="change_link_coupons(this);"><label for="">关联优惠券</label></dd>
                        <?php if($ordering_retail>0){?>
                        <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" checked id="ordering_retail" name="ordering_retail" onclick="return false"><label for="ordering_retail">订货系统</label></dd>
                        <?php }?>
                        <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($is_mini_mshop>0){?>checked<?php } ?> id="chk_mini_mshop" name="chk_mini_mshop" onclick="change_mini_mshop(this);"><label for="chk_mini_mshop">微信小程序</label></dd>
                    </dl>
                    <dl class="WSY_bulkdl WSY_bulkdl03 w90px">
                        <dt>运费模板：</dt>
                        <dd class="dd_margin">
                            <select name="freight_id">
                                <option value="-1" >无</option>
                                <?php
                                    $express_id   = -1;
                                    $express_name = "";
                                    $query = "SELECT id,title FROM express_template_t where customer_id=".$customer_id." and isvalid=true ";
                                    //默认平台运费模板
                                    $express_sql = " and supply_id=-1 ";
                                    if( 0 < $supply_id ){
                                        //供应商运费模板
                                        $express_sql = " and supply_id=".$supply_id;
                                    }
                                    $query .= $express_sql;
                                    $result = _mysql_query($query) or die('Query failed: ' . mysql_error());
                                    while ($row = mysql_fetch_object($result)) {
                                        $express_id    = $row->id;
                                        $express_name  = $row->title;

                                ?>
                                <option value="<?php echo $express_id;?>" <?php if($express_id == $freight_id){ echo 'selected="selected"'; } ?> ><?php echo $express_name;?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </dd>
                        <dd><a href="freight_log.php?customer_id=<?php echo $customer_id_en;?>&pid=<?php echo $product_id;?>"><img style="width:20px;" title="查看修改日志" src="../../Common/images/Base/basicdesign/icon-log.png"/></a></dd>
                    </dl>
                    <dl class="WSY_bulkdl WSY_bulkdl03 w90px">
                        <dt>邮费计费方式：</dt>
                        <dd>
                            <dd class="dd_margin"><input type="radio" name="express_type" id="express_type_1" value="1" <?php if(1==$express_type){echo 'checked=checked';}?>><label for="express_type_1">按件数</label></dd>
                            <dd class="dd_margin"><input type="radio" name="express_type" id="express_type_2" value="2" <?php if(2==$express_type){echo 'checked=checked';}?>><label for="express_type_2">按重量</label></dd>
                        </dd>
                    </dl>
                    <?php if( $is_charitable ){ ?>
                    <dl class="WSY_bulkdl WSY_bulkdldt w90px">
                        <dt>捐赠比率：</dt>
                        <dd>
                            <input name="donation_rate" id="donation_rate" type="text" value="<?php echo $donation_rate;?>">
                            <i class="WSY_red">比例范围(<?php echo $charitable_propotion ?>~1)</i>
                        </dd>
                    </dl>
                    <?php } ?>
                    <dl class="WSY_bulkdl WSY_bulkdldt w90px">
                        <dt>真实销售量：</dt>
                        <dd style="line-height:20px"><?php echo $sell_count; ?></dd>
                    </dl>
                    <dl class="WSY_bulkdl WSY_bulkdldt w90px">
                        <dt>虚拟销售量：</dt>
                        <dd><input type="text" id="show_sell_count" name="show_sell_count" value="<?php echo $show_sell_count; ?>"><i class="WSY_red">显示销量=虚拟销售量+真实销售量</i></dd>
                    </dl>
                    <dl class="WSY_bulkdl WSY_bulkdl03 w90px">
                        <dt>产品分享图片：</dt>
                        <dd class="dd_margin"><input type="radio" id="define_share_image_flag_0" name="define_share_image_flag" value="0" <?php if($define_share_image_flag==0){ ?>checked<?php } ?> >
                            <label for="define_share_image_flag_0">默认</label></dd>
                        <dd class="dd_margin"><input type="radio" id="define_share_image_flag_1" name="define_share_image_flag" value="1" <?php if($define_share_image_flag==1){ ?>checked<?php } ?>>
                            <label for="define_share_image_flag_1">自定义</label></dd>
                        <!--上传文件代码开始-->
                        <div class="uploader white"  id="define_share_image_div" <?php if(!$define_share_image_flag) echo "style='display:none'"; ?>>
                            <input type="text" class="filename" readonly />
                            <input type="button" name="file" class="button" value="上传..."/>
                            <input type="file" size="30" name='new_define_share_image' id='new_define_share_image'/>
                            <input type='hidden' name='now_define_share_image' id='now_define_share_image' value='<?php echo $define_share_image;?>'>
                        </div>
                        <!--上传文件代码结束-->
                    </dl>
                    <!--郑培强-->
                    <dl class="WSY_bulkdl WSY_bulkdl03 w90px" <?php if(false == $is_scancode){ echo 'style="display:none"';  $is_QR=0;}  ?>  id="qr_button" >
                        <dt>二维码核销：</dt>
                        <dd class="dd_margin"><input type="radio" value="0" <?php if($is_QR==0){ ?>checked<?php } ?> name="is_QR" id="is_QR_0" ><label for="is_QR_0">关</label></dd>
                        <dd class="dd_margin"><input type="radio" value="1" <?php if($is_QR==1){ ?>checked<?php } ?> name="is_QR" id="is_QR_1"><label for="is_QR_1">开</label></dd>
                    </dl>
                    <script>
                       $("#is_QR_0").click(function(){
                           $("#qr_content").remove();
                       });
                       $("#is_QR_1").click(function(){
                           if($("#qr_content").length >0) {

                           }else{
                               $("#qr_button").after('<dl class="WSY_bulkdl WSY_bulkdl03 w90px" id="qr_content" >'+
                                 '<dt>产品有效期：</dt>'+
                                 '<dd class="dd_margin"><input type="radio" value="0" name="QR_isforever" id="is_youxiao_1" checked onclick="yuoxiao1()" ><label for="is_QR_0">永久有效</label></dd>'+
                                 '<dd class="dd_margin"><input type="radio" value="1" name="QR_isforever" id="is_youxiao_2" onclick="yuoxiao2()" ><label for="is_QR_1">限定时间有效</label></dd>'+
                                 '<dd class="dd_margin" >'+
                                 '<select name="QR_select" id="qr_select" onchange="qrselect()" style="display:none;">'+
                                 '<option value="1" >指定时间段</option>'+
                                 '<option value="2" >固定天数</option>'+
                                 '</select>'+
                                 '</dd>'+
                                 '<dd class="dd_margin" id="qr_shiajin_1_1" style="display:none;">'+
                                 '<input type="text" placeholder="开始时间" id="QR_starttime" name="QR_starttime" value="" style="border: solid 1px #ccc;height: 20px;margin-top: -3px;" onclick="date_chuxian1()" >'+
                                 '</dd>'+
                                 '<dd class="dd_margin" id="qr_shiajin_1_t" style="display:none;">至</dd>'+
                                 '<dd class="dd_margin" id="qr_shiajin_1_2" style="display:none;">'+
                                 '<input type="text" placeholder="结束时间" id="QR_endtime" name="QR_endtime" value="" style="border: solid 1px #ccc;height: 20px;margin-top: -3px;" onclick="date_chuxian2()" >'+
                                 '</dd>'+
                                 '<dd class="dd_margin" id="qr_shiajin_2" style="display:none;" >'+
                                 '<input type="text" id="QR_day" name="QR_day" style="border: solid 1px #ccc;height: 20px;margin-top: -3px;">天'+
                                 '</dd>'+
                                 '</dl>');
                           }
                       });
                       function yuoxiao1(){
                           $("#qr_select").css("display","none");
                           $("#qr_shiajin_1_1").css("display","none");
                           $("#qr_shiajin_1_t").css("display","none");
                           $("#qr_shiajin_1_2").css("display","none");
                           $("#qr_shiajin_2").css("display","none");
                       }
                       function yuoxiao2(){
                           $("#qr_select").css("display","block");
                           $("#qr_shiajin_1_1").css("display","block");
                           $("#qr_shiajin_1_t").css("display","block");
                           $("#qr_shiajin_1_2").css("display","block");
                           //$("#qr_shiajin_2").css("display","block");
                       }
                       function qrselect(){
                           if($("#qr_select").val()==1){
                               $("#qr_shiajin_1_1").css("display","block");
                               $("#qr_shiajin_1_t").css("display","block");
                               $("#qr_shiajin_1_2").css("display","block");
                               $("#qr_shiajin_2").css("display","none");
                           }else if($("#qr_select").val()==2){
                               $("#qr_shiajin_1_1").css("display","none");
                               $("#qr_shiajin_1_t").css("display","none");
                               $("#qr_shiajin_1_2").css("display","none");
                               $("#qr_shiajin_2").css("display","block");
                           }
                       }
                       function date_chuxian1(){
                           //WdatePicker({dateFmt:'yyyy-MM-dd'})
                           WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss'})
                       }
                       function date_chuxian2(){
                           //WdatePicker({dateFmt:'yyyy-MM-dd'})
                           WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss'})
                       }
                    </script>
                    <?php
                    if($is_QR==1){
                    ?>
                    <dl class="WSY_bulkdl WSY_bulkdl03 w90px" id="qr_content" >
                        <dt>产品有效期：</dt>
                        <dd class="dd_margin"><input type="radio" name="QR_isforever" value="0" id="is_youxiao_1" <?php if($QR_isforever==0){echo "checked";}?> onclick="yuoxiao1()" ><label for="is_QR_0">永久有效</label></dd>
                        <dd class="dd_margin" id="qr_tjbj" ><input type="radio" name="QR_isforever" value="1" id="is_youxiao_2" <?php if($QR_isforever==1 || $QR_isforever==2){echo "checked";}?> onclick="yuoxiao2()" ><label for="is_QR_1">限定时间有效</label></dd>
                        <dd class="dd_margin">
                            <select name="QR_select" id="qr_select" onchange="qrselect()" <?php if($QR_isforever==0){echo "style='display:none;'";}?> >
                                <option value="1" >指定时间段</option>
                                <option value="2" <?php if($QR_isforever==2){echo 'selected="selected"';}?> >固定天数</option>
                            </select>
                        </dd>
                        <dd class="dd_margin" id="qr_shiajin_1_1" <?php if($QR_isforever==0 || $QR_isforever==2){echo "style='display:none;'";}?> >
                            <input type="text" placeholder="开始时间" id="QR_starttime" name="QR_starttime" style="border: solid 1px #ccc;height: 20px;margin-top: -3px;" onclick="date_chuxian1()" value="<?php if($QR_isforever==1){echo $QR_starttime;}?>" >
                        </dd>
                        <dd class="dd_margin" id="qr_shiajin_1_t" <?php if($QR_isforever==0 || $QR_isforever==2){echo "style='display:none;'";}?> >至</dd>
                        <dd class="dd_margin" id="qr_shiajin_1_2" <?php if($QR_isforever==0 || $QR_isforever==2){echo "style='display:none;'";}?> >
                            <input type="text" placeholder="结束时间" id="QR_endtime" name="QR_endtime" style="border: solid 1px #ccc;height: 20px;margin-top: -3px;" onclick="date_chuxian2()" value="<?php if($QR_isforever==1){echo $QR_endtime;}?>" >
                        </dd>
                        <dd class="dd_margin" id="qr_shiajin_2" <?php if($QR_isforever==0 || $QR_isforever==1){echo 'style="display:none;"';}?> >
                            <input type="text" id="QR_day" name="QR_day" value="<?php if($QR_isforever==2){echo (strtotime($QR_endtime)+1-strtotime($QR_starttime))/86400;}?>" style="border: solid 1px #ccc;height: 20px;margin-top: -3px;">天
                        </dd>
                    </dl>
                    <?php
                    }
                    ?>
                    <!--郑培强-->
                    <?php
                    if( $shop_is_identity ){
                    ?>
                    <dl class="WSY_bulkdl WSY_bulkdl03 w90px">
                        <dt>身份证购买：</dt>
                        <dd class="dd_margin"><input type="radio" value="0" <?php if( 0 == $is_identity ){ ?> checked <?php } ?> name="is_identity" id="is_identity_0"><label for="is_identity_0">关</label></dd>
                        <dd class="dd_margin"><input type="radio" value="1" <?php if( 1 == $is_identity ){ ?> checked <?php } ?> name="is_identity" id="is_identity_1"><label for="is_identity_1">开</label></dd>
                    </dl>
                    <?php } ?>
                    <dl class="WSY_bulkdl WSY_bulkdl03 w90px">
                        <dt>产品所属城市：</dt>
                        <dd class="dd_margin">
                            <select name="city_id">
                                <option value="-1" >无</option>
                                <?php

                            $query_area='select id,province,city,area from weixin_commonshop_product_area where isvalid=true and customer_id='.$customer_id;
                            $result_area = _mysql_query($query_area) or die('Query_area failed: ' . mysql_error());
                            while ($row_area = mysql_fetch_object($result_area)) {
                                $province=  $row_area->province;
                                $city=   $row_area->city;
                                $city_id=   $row_area->id;
                            ?>
                                <option value="<?php echo $city_id;?>" <?php if($city_id == $pro_area){ echo 'selected="selected"'; } ?> ><?php echo $province.$city;?></option>
                            <?php
                            }
                            ?>
                            </select>
                        </dd>
                    </dl>


                    <dl class="WSY_bulkdl package" id="back_package" style="<?php if($link_package>0){?>display:block;<?php }else{?>display:none;<?php }?>">
                        <dt style="width:90px">选择关联礼包：</dt>
                        <select id="link_package" name="link_package">
                            <?php
                                $query_package = "select id,package_name from package_list_t where isvalid=true and customer_id=".$customer_id." and isout=0";
                                $package_id = -1;
                                $package_name = "";
                                $result_package = _mysql_query($query_package) or die("query_package faild：".mysql_error());
                                while($row_package = mysql_fetch_object($result_package)){
                                    $package_id = $row_package->id;
                                    $package_name = $row_package->package_name;
                            ?>
                            <option value="<?php echo $package_id; ?>" <?php if($link_package==$package_id){echo "selected";} ?>><?php echo $package_name; ?></option>
                                <?php } ?>
                        <select>
                    </dl>

                    <!--关联优惠券-->


                    <input type="hidden" value="<?php echo $link_coupons; ?>" name="link_coupons_save" id="link_coupons_save"/>
                    <dl class="WSY_bulkdl coupons" id=""  <?php if($is_open_link_coupons==0){ ?>style="display:none;"<?php } ?>>
                        <dt style="width:90px">请选择优惠券:</dt>
                        <button style="background-color:#06a7e1;color:#f9fdff;border:solid 1px #06a7e1;width:60px;" id="" type="button" onclick="select_coupons()">选择</button>
                        <span onclick='show_check_cous()' style="margin-left:10px;color:#06a7e1;">已经选择<span style="color:#06a7e1;" class='cous_length'><?php if($is_open_link_coupons){ echo count($link_coupons_array);}else{echo 0;} ?></span>张优惠券</span>
                    </dl>
                    <!--优惠券列表-->
                    <dl class="coupons_list" style="width:80%;display:none;">
                    <dl style="margin-left:50%;font-size:17px;margin-top:10px;">选择关联优惠券</dl>
                    <dl class="WSY_bulkdl WSY_bulkdldt w90px" style="margin-top:10px;"><dt style="width:70px;">优惠券名称:<dd><input style="width:200px;"  id="search_cou_name" name="title" /></dd></dt>
                    <dt style="width:55px;">使用场景:</dt><dd>
                    <select id="user_scene">
                    <option value="-1">全部</option>
                    <option value="0">平台通用</option>
                    <option value="1">指定单品</option>
                    </select></dd>

                   <dt style="width:55px;">选择时间:</dt>
                   <dd>
                    <select class="time_search">
                    <option value="-1">全部</option>
                    <option value="1">创建时间</option>
                    <option value="2">领取时间</option>
                    <option value="3">使用时间</option>
                    </select>
                    </dd>
                    <dd class="show_time_search" style="display:none;"><input style="width:150px;"  type=text id="cou_starttime" name="cou_starttime" value="" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm'});"  /><span>-</span><input style="width:150px;margin-left:5px" class="login-input-username" type=text id="cou_endtime" name="cou_endtime" value="" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm'});"  /></dd>
                   <dd style="float:right;" class="WSY_bulkdldd dd_margin"><input style="margin-top:3px;width:30px;height:13px;" class="show_check" type="checkbox" ><label >已选择</label>
                   <button style="background-color:#06a7e1;color:#f9fdff;border:solid 1px #06a7e1;width:60px;" onclick="search_coupons()" id="search" type="button" >搜索</button>
                   </dd>
                    </dl>
                    <dl>
                    <div id="coupons_load" style="width:100%;height:320px;overflow: auto;">
                      <table  class="WSY_table WSY_t2 cou_table" id="WSY_t1" >
                      <thead class="WSY_table_header">
                        <th width="3%">券号</th>
                        <th width="7%">名称</th>
                        <th width="6%">优惠金额</th>
                        <th width="7%">使用场景</th>
                        <th width="20%">有效期</th>
                        <th width="7%">是否有效</th>
                        <th width="9%">选择</th>
                      </thead>
                      <?php
                      $query_coupons_id='select id from weixin_commonshop_coupons where isvalid=true and customer_id='.$customer_id.' and is_open=1 order by id desc ';
                      $result_coupons_id=_mysql_query($query_coupons_id) or die('W21 Query failed: ' . mysql_error());
                      $all_coupons_id="";
                      while ($row = mysql_fetch_object($result_coupons_id)) {
                          $all_coupons_id.=($row->id).",";
                      }
                      $all_coupons_id=rtrim($all_coupons_id, ",");

                      $query_coupons='select c.id,c.is_open,c.title,c.NeedMoney,c.CanGetNum,c.Days,c.DaysType,c.class_type,c.MinMoney,c.MaxMoney,c.user_scene,c.couponNum,c.MoneyType,c.personNum,c.getStartTime,c.getEndTime,c.createtime,c.startline,p.name from weixin_commonshop_coupons c LEFT JOIN weixin_commonshop_products p on c.connected_id=p.id where c.isvalid=true and c.customer_id='.$customer_id.' and c.is_open=1 order by c.id desc limit 0,10';
                      $result_coupons=_mysql_query($query_coupons) or die('W21 Query failed: ' . mysql_error());
                      $coupons_id           = -1;
                      $title            =  "";
                      $p_name          = '';
                      $NeedMoney        =  0;
                      $MoneyType      =  0;
                      $is_use ="失效";//是否失效,0为已失效,1位有效
                      while ($row = mysql_fetch_object($result_coupons)) {
                        $coupons_id = $row->id;
                        $title      = $row->title;      //优惠券标题
                        $MaxMoney   = $row->MaxMoney;   //随机领取最低金额
                        $MinMoney   = $row->MinMoney;   //随机领取最高金额
                        $Days       = $row->Days;       //截止使用天数
                        $DaysType   = $row->DaysType;   //截止使用天数
                        $MoneyType    = $row->MoneyType;    //领取金额类型，0:随机金额,1:固定金额
                        $user_scene_str = "平台通用";
                        $user_scene  = $row->user_scene;    //代金券使用场景：0平台，1单品，2供应商
                        if($user_scene==1){
                            $user_scene_str = "指定单品";
                        }
                        $getStartTime = $row->getStartTime; //领取开始时间
                        $getEndTime   = $row->getEndTime;   //领取结束时间
                        $createtime   = $row->createtime;   //创建时间
                        $startline    = $row->startline;    //使用开始时间
                        $p_name       = $row->name; //单品名
                        if($DaysType==1){
                        if(strtotime($Days)>=time()){
                        $is_use="有效";
                        }else{
                        $is_use="失效";
                        }
                        }else{
                        $is_use="有效";
                        }


                      ?>
                      <tr class="pro_tr" >
                        <td align="center" align="center"><?php echo $coupons_id; ?></td>
                        <td align="center" align="center"><?php echo $title; ?></td>
                        <td align="center" align="center"><?php if(!$MoneyType){echo $MaxMoney;}else{echo $MinMoney.'-'.$MaxMoney;} ?></td>
                        <td align="center">
                        <dt><?php echo $user_scene_str; ?></dt>
                            <?php if(!empty($p_name)){ ?>
                                <dt><?php echo $p_name; ?></dt>
                            <?php } ?>
                        </td>
                        <td align="center">
                            <dt>创建时间：<?php echo $createtime; ?></dt>
                            <dt>领取时间：<?php echo $getStartTime; ?>至<?php echo $getEndTime; ?></dt>
                            <?php if($DaysType){ ?>
                            <dt>使用时间：<?php echo $startline; ?>至<?php echo $Days; ?></dt>
                            <?php }else{ ?>
                                <dt>使用时间：<?php echo date("Y-m-d H:i:s",time()); ?>至<?php echo date('Y-m-d H:i:s',strtotime("+".$Days." day")); ?></dt>
                            <?php } ?>
                        </td>
                        <td><?php echo $is_use; ?></td>
                        <td align="center" id=""><input style="height:13px;" <?php if(in_array($coupons_id, $link_coupons_array)){ ?>checked="checked" <?php } ?> type="checkbox" onchange="add_value(this)" name="check_cous[]" class=" <?php if(in_array($coupons_id, $link_coupons_array)){ ?>save_check<?php } ?>"  value="<?php echo $coupons_id; ?>" /></td>
                      </tr>
                      <?php } ?>
                    </table>
                    </div>
                    </dl>
                    <dl class="WSY_bulkdl"><input style="margin-left:20%;" id="check_all" type="checkbox">全选<button style="background-color:#06a7e1;color:#f9fdff;border:solid 1px #06a7e1;width: 80px;height: 30px;margin-left:10%;font-size:15px;" id="open_list" type="button" >保存</button>
                    <button style="color:#111;width: 80px;height: 30px;margin-left:10%;font-size: 15px;" id="cancel_list" type="button" >取消</button>
                    </dl>
                    </dl>
                    <!--优惠券列表-->
                    <!--关联优惠券-->


                    <dl class="WSY_bulkdl package" id="back_package" style="<?php if($link_package>0){?>display:block;<?php }else{?>display:none;<?php }?>">
                        <dt style="width:90px">上传礼包关联图：</dt>
                        <dd>
                            <input size="17" name="package_img" id="img" class="upfile" type=file value="<?php echo $link_package_img; ?>">
                            <input type=hidden value="<?php echo $link_package_img; ?>" name="link_package_img" />
                            <span>支持格式：JPG、JPEG、PNG、JIF，图片大小：小于100K，图片宽度：640，高度不限</span>
                        </dd>
                    </dl>
                    <dl class="WSY_bulkdl package" id="back_package" style="<?php if($link_package>0){?>display:block;<?php }else{?>display:none;<?php } ?>">
                        <img id="package_img" name="packageimg" src="<?php echo $link_package_img; ?>" width=640 style="display:<?php if(empty($link_package_img)){echo 'none';}else{echo 'block';} ?>"/>
                    </dl>


                     <dl class="WSY_bulkdl" id="back_privilege" style="<?php if($is_privilege==0){?>display:none;<?php }else{?>display:block;<?php }?>">
                        <dt style="width:90px">特权身份：</dt>
                        <dd><input type="checkbox" class="privilege" value="-1" name="privilege[]" <?php if(in_array("-1",$privilege_list) && $privilege_level!="-1_0_1_2_3_4_5"){?>checked<?php }?>><?php echo $common_name;?></dd>
                        <dd><input type="checkbox" class="privilege" value="1" name="privilege[]" <?php if(in_array("1",$privilege_list) && $privilege_level!="-1_0_1_2_3_4_5"){?>checked<?php }?>><?php echo $exp_name;?></dd>
                        <dd><input type="checkbox" class="privilege" value="2" name="privilege[]" <?php if(in_array("2",$privilege_list) && $privilege_level!="-1_0_1_2_3_4_5"){?>checked<?php }?>><?php echo $d_name;?></dd>
                        <dd><input type="checkbox" class="privilege" value="3" name="privilege[]" <?php if(in_array("3",$privilege_list) && $privilege_level!="-1_0_1_2_3_4_5"){?>checked<?php }?>><?php echo $c_name;?></dd>
                        <dd><input type="checkbox" class="privilege" value="4" name="privilege[]" <?php if(in_array("4",$privilege_list) && $privilege_level!="-1_0_1_2_3_4_5"){?>checked<?php }?>><?php echo $b_name;?></dd>
                        <dd><input type="checkbox" class="privilege" value="5" name="privilege[]" <?php if(in_array("5",$privilege_list) && $privilege_level!="-1_0_1_2_3_4_5"){?>checked<?php }?>><?php echo $a_name;?></dd>
                    </dl>

                    <dl class="WSY_bulkdl" id="back_currency" style="<?php if(($is_currency) && ($is_rebate_open == 1)){?>display:block;<?php }else{?>display:none;<?php }?>">
                        <dt style="width:120px">返佣<?php echo defined('PAY_CURRENCY_NAME') ?PAY_CURRENCY_NAME: '购物币'; ?>：</dt>
                        <dd><input type="text" value="<?php echo $back_currency;?>" name="back_currency" id="backcurrency" style="width:150px;height:20px;padding-left:5px;border-radius:2px;margin-top:0;margin-right:5px;border:1px solid #ccc;"></dd>
                    </dl>

                    <dl class="WSY_bulkdl WSY_bulkdldt w90px snap_up">
                        <dt style="width:120px">商品抢购开始时间：</dt>
                        <dd><input type="text" style="width:200px" id="buystart_time" name="buystart_time" value="<?php echo $buystart_time; ?>"  onclick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm',minDate:'2015-10-25 10:00',maxDate:'2018-10-25 21:30'});" ></dd>
                    </dl>
                    <dl class="WSY_bulkdl WSY_bulkdldt w90px snap_up">
                        <dt style="width:120px">商品抢购结束时间：</dt>
                        <dd><input type="text" style="width:200px" id="countdown_time" name="countdown_time" value="<?php echo $countdown_time; ?>"  onclick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm',minDate:'2015-10-25 10:00',maxDate:'2018-10-25 21:30'});" ></dd>
                    </dl>
                    <dl class="WSY_bulkdl w90px" id="vp_score">
                        <dt>vp值：</dt>
                        <dd class="WSY_bulkdldd dd_margin">
                            <input class="weiz_input" type="text" name="vp_score" value="<?php echo $vp_score; ?>" />
                        </dd>
                    </dl>
                    <dl class="WSY_bulkdl w90px" id="limit_num">
                        <dt style="width:115px;">（每天）限购数量：</dt>
                        <dd class="WSY_bulkdldd dd_margin">
                            <input class="weiz_input" type="text" name="limit_num" id="limit_num_val" onkeyup="num_check(this)" value="<?php echo $limit_num; ?>" />
                        </dd>
                    </dl>
                    <dl class="WSY_bulkdl w90px" id="extend_money">
                        <dt style="width:115px;">首次推广奖励金额：</dt>
                        <dd class="WSY_bulkdldd dd_margin">
                            <input class="weiz_input" type="text" name="extend_money" id="extend_money_val" onkeyup="num_check1(this)" value="<?php echo $extend_money; ?>" />
                        </dd>
                    </dl>

                    <dl class="WSY_bulkdl" id="is_tax" style="<?php if($istax==1){?>display:block;<?php }else{?>display:none;<?php }?> line-height:22px;">
                        <dt style="width:80px;">税率模板：</dt>
                        <dd class="dd_margin">
                            <select name="tax_id" id="tax_sel" onclick='change_tax();'>
                                <option value="" >模板选择</option>
                                <?php
                            $p_taxid        = -1;
                            $p_tariff       = 0;
                            $p_comsumption  = 0;
                            $p_addedvalue   = 0;
                            $p_postal       = 0;
                            $query_tax='SELECT id,tariff,comsumption,addedvalue,postal FROM weixin_commonshop_product_tax_public WHERE customer_id='.$customer_id;
                            $result_tax = _mysql_query($query_tax) or die('Query_area failed: ' . mysql_error());
                            while ($row_tax = mysql_fetch_object($result_tax)) {
                                $p_taxid        =   $row_tax->id;
                                $p_tariff       =   $row_tax->tariff;
                                $p_comsumption  =   $row_tax->comsumption;
                                $p_addedvalue   =   $row_tax->addedvalue;
                                $p_postal       =   $row_tax->postal;
                            ?>
                                <option value="<?php echo $p_tariff;?>,<?php echo $p_comsumption;?>,<?php echo $p_addedvalue;?>,<?php echo $p_postal;?>"  ><?php echo "关税税率:".$p_tariff."%；消费税税率:".$p_comsumption."%；增值税税率:".$p_addedvalue."%；行邮税:".$p_postal."%"; ?></option>
                            <?php
                            }
                            ?>
                            </select>
                        </dd>
                        <dt style="width:80px;">税收标签：</dt>
                        <dd>
                            <select name="tax_type" id="">
                                <!-- <option value="1" <?php if($tax_type == 1){ echo 'selected';}?> >普通产品</option> -->
                                <option value="2" <?php if($tax_type == 2){ echo 'selected';}?> >跨境零售</option>
                                <option value="3" <?php if($tax_type == 3){ echo 'selected';}?> >国内代发</option>
                                <option value="4" <?php if($tax_type == 4){ echo 'selected';}?> >海外集货</option>
                                <option value="5" <?php if($tax_type == 5){ echo 'selected';}?> >海外直邮</option>
                            </select>
                        </dd>
                        <dt style="width:80px">关税税率：</dt>
                        <dd><input type="text" value="<?php echo $tariff;?>" name="tariff" id="tariff" style="width:80px;height:20px;padding-left:10px;border-radius:2px;margin-top:0;margin-right:5px;border:1px solid #ccc;">%</dd>
                        <dt style="width:80px">消费税税率：</dt>
                        <dd><input type="text" value="<?php echo $comsumption;?>" name="comsumption" id="comsumption" style="width:80px;height:20px;padding-left:10px;border-radius:2px;margin-top:0;margin-right:5px;border:1px solid #ccc;">%</dd>
                        <dt style="width:80px">增值税税率：</dt>
                        <dd><input type="text" value="<?php echo $addedvalue;?>" name="addedvalue" id="addedvalue" style="width:80px;height:20px;padding-left:10px;border-radius:2px;margin-top:0;margin-right:5px;border:1px solid #ccc;">%</dd>
                        <dt style="width:80px">行邮税率：</dt>
                        <dd><input type="text" value="<?php echo $postal;?>" name="postal" id="postal" style="width:80px;height:20px;padding-left:10px;border-radius:2px;margin-top:0;margin-right:5px;border:1px solid #ccc;">%</dd>
                    </dl>

                    <div class="WSY_remind_main">
                        <dl class="WSY_bulkdl  w90px">
                            <dt>发票支持：</dt>
                            <?php if($is_invoice==1){ ?>
                            <ul style="background-color: rgb(255, 113, 112);margin-top:2px;">
                                <p style="color: rgb(255, 255, 255); margin: 0px 0px 0px 22px;">开</p>
                                <li onclick="change_is_invoice(0)" class="WSY_bot" style="left: 0px;"></li>
                                <span onclick="change_is_invoice(1)" class="WSY_bot2" style="display: none; left: 0px;"></span>
                            </ul>
                            <?php }else{ ?>
                            <ul style="background-color: rgb(203, 210, 216);margin-top:2px;">
                                <p style="color: rgb(127, 138, 151); margin: 0px 0px 0px 6px;">关</p>
                                <li onclick="change_is_invoice(0)" class="WSY_bot" style="display: none; left: 30px;"></li>
                                <span onclick="change_is_invoice(1)" class="WSY_bot2" style="display: block; left: 30px;"></span>
                            </ul>
                            <?php } ?>
                        </dl>
                        <input type="hidden" name="is_invoice" id="is_invoice" value="<?php echo $is_invoice;?>">
                    </div>

                    <!-- 必填信息start -->
                    <?php if($is_Pinformation_b==1){ ?>
                    <div class="WSY_remind_main">
                        <dl class="WSY_bulkdl  w90px">
                            <dt>必填信息：</dt>
                            <?php if($is_Pinformation==1){ ?>
                            <ul style="background-color: rgb(255, 113, 112);margin-top:2px;">
                                <p style="color: rgb(255, 255, 255); margin: 0px 0px 0px 22px;">开</p>
                                <li onclick="chage_Pinformation(0)" class="WSY_bot" style="left: 0px;"></li>
                                <span onclick="chage_Pinformation(1)" class="WSY_bot2" style="display: none; left: 0px;"></span>
                            </ul>
                            <?php }else{ ?>
                            <ul style="background-color: rgb(203, 210, 216);margin-top:2px;">
                                <p style="color: rgb(127, 138, 151); margin: 0px 0px 0px 6px;">关</p>
                                <li onclick="chage_Pinformation(0)" class="WSY_bot" style="display: none; left: 30px;"></li>
                                <span onclick="chage_Pinformation(1)" class="WSY_bot2" style="display: block; left: 30px;"></span>
                            </ul>
                            <?php } ?>
                        </dl>
                    </div>
                    <input type="hidden" name="is_Pinformation_b" id="is_Pinformation_b" value="<?php echo $is_Pinformation_b; ?>" />
                    <input type="hidden" name="is_Pinformation" id="is_Pinformation" value="<?php echo $is_Pinformation; ?>" />

                    <?php } if($is_Pinformation_b==1){ ?>

                    <div class="div_show" id="mess" >
                        <dl class="WSY_remind_dl02">
                            <table width="50%" class="WSY_table WSY_information" id="WSY_t1">
                                <thead class="WSY_table_header">
                                    <th width="25%" class="WSY_table_little">信息</th>
                                    <th width="25%" class="WSY_table_little">操作</th>
                                </thead>
                                <?php
                                $query    = "select id,name from weixin_commonshop_product_information_t where isvalid=true and customer_id=".$customer_id." and p_id=".$product_id;
                                //echo $query;
                                $result   = _mysql_query($query) or die('Query failed: ' . mysql_error());
                                $rcount_q = mysql_num_rows($result);
                                $mess_num = 1;
                                if( 0 < $rcount_q ){

                                    while ($row = mysql_fetch_object($result)) {
                                        $name        = $row->name;
                                        $name_id     = $row->id;
                                    ?>
                                    <tr class="diy_one_two" id="diy_item_<?php echo $mess_num; ?>">
                                        <input type=hidden name="name_id<?php echo $mess_num; ?>" id="name_id<?php echo $mess_num; ?>" value="<?php echo $name_id; ?>" />
                                        <td>
                                            <input type=text class="singletext_con" name="singletext_con_<?php echo $mess_num; ?>" id="singletext_con<?php echo $mess_num; ?>" value="<?php echo $name; ?>">
                                        </td>
                                        <td>
                                            <a title="删除" href="javascript:mess_del(<?php echo $mess_num; ?>);"><img src="../../../common/images_V6.0/operating_icon/icon04.png"></a>&nbsp;
                                            <a title="添加" href="javascript:mess_add(1);"><img src="../../../common/images_V6.0/operating_icon/icon45.png"></a>
                                        </td>
                                    </tr>
                                <?php
                                        $mess_num++;
                                    }
                                }else{
                                ?>
                                    <tr class="diy_one_two" id="diy_item_<?php echo $mess_num; ?>">
                                        <input type=hidden name="name_id<?php echo $mess_num; ?>" id="name_id" value="-1" />
                                        <td>
                                            <input type=text class="singletext_con" name="singletext_con_<?php echo $mess_num; ?>" id="singletext_con<?php echo $mess_num; ?>" value="<?php echo $name; ?>" />
                                        </td>
                                        <td>
                                            <a title="删除" href="javascript:mess_del(<?php echo $mess_num; ?>);"><img src="../../../common/images_V6.0/operating_icon/icon04.png"></a>&nbsp;
                                            <a title="添加" href="javascript:mess_add(1);"><img src="../../../common/images_V6.0/operating_icon/icon45.png"></a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </dl>
                    </div>
                    <?php } ?>

                    <dl class="WSY_bulkdl w90px">
                        <dt>排序位置：</dt>
                        <dd class="WSY_bulkdldd dd_margin">
                            <input class="weiz_input" id="weiz_sort" type="text" name="asort_value" value="<?php echo $product_asort_value; ?>" />(按降序排序)
                            <?php
                                if($product_num !=-1 and $num>=$product_num ){
                            ?>
                            (<span style="color:red">你上架的商品已经超过<?php echo $product_num;?>件了！</span>)
                            <?php
                                }
                            ?>
                        </dd>
                    </dl>

                    <?php if($pro_card_level){?>
                    <dl class="WSY_bulkdl WSY_bulkdl03 w90px">
                        <dt>需要会员级别：</dt>
                        <dd>
                            <select name="pro_card_level_id" id="pro_card_level_id">
                                <option value="-1"  <?php if($pro_card_level_id==-1){ ?>selected<?php } ?>>不限制</option>
                                <?php
                               $query="select id,title from weixin_card_levels where isvalid=true and card_id=".$shop_card_id;
                               $result = _mysql_query($query) or die('Query failed: ' . mysql_error());
                               while ($row = mysql_fetch_object($result)) {
                                   $cid = $row->id;
                                   $cname = $row->title;
                            ?>
                                <option value="<?php echo $cid;?>" <?php if($pro_card_level_id==$cid){ echo 'selected="selected"'; } ?> ><?php echo $cname;?></option>
                            <?php
                            }
                            ?>
                            </select>
                        </dd>
                    </dl>
                    <?php } ?>



                    <div class="product-footer-btn">
                        <button class="WSY_button"  type="button" onclick="prev_tab(this);">上一步</button>
                        <button class="WSY_button"  type="button" onclick="next_step(this);">下一步</button>
                    <?php if($status == 0 && $status !=null){ ?><!--待审核状态 0:、待审核-->
                        <button class="WSY_button" onclick="offer_save(this)" type="button">保存通过<img style="position:relative;top:2px;left:5px;width:15px;" id="product_offer" src="../../Common/images/Base/help.png"></button>
                        <button class="WSY_button" onclick="offer(this)" type="button">通过上架</button>
                        <button class="WSY_button" onclick="refundProduct(this)" type="button">驳回</button>
                        <button class="WSY_button" onclick="javascript:history.go(-1);" type="button">返回</button>
                    <?php } else {?>
                        <button class="WSY_button" <?php if ($product_id == -1) echo "style='display:none'";?> type="button" onclick="saveProduct()">提交保存</button>
                    <?php } ?>
                    </div>
                </div>

                <div class="product-tab-list <?php if($sa == 7) echo 'tab-active'; ?>">
                    <?php if($issell){ ?>
                            <!--<dl class="WSY_bulkdl WSY_bulkdldt w90px">
                                <dt>购买折扣率：</dt>
                                <dd><input type="text" name="pro_discount" id="pro_discount" style="width:50px;" value="<?php echo $pro_discount; ?>"><i style="color:#646464">% (0:表示无折扣)</i></dd>
                            </dl>-->
                            <dl class="WSY_bulkdl WSY_bulkdldt w90px">

                                <dt style="width:200px;text-align:right;">产品奖励比例：</dt>
                                <dd><input type="text" name="pro_reward" id="pro_reward" style="min-width:100px" value="<?php echo $pro_reward; ?>"><i style="color:#646464">（0～1）</i></dd>
                                 <dd><font style="color:red;margin-left:10px;">（填写0则不奖励，填写-1则按奖励总比例<?php echo $init_reward;?>）</font></dd>
                            </dl>
                            <?php if($isOpenInstall){?>
                             <dl class="WSY_bulkdl WSY_bulkdldt w90px">
                                <dt style="width:200px;text-align:right;">产品安装费：</dt>
                                <dd><input type="text" name="install_price" id="install_price" class="form_input" style="min-width:100px" value="<?php echo $install_price; ?>" /><?php echo OOF_T ?></dd>
                            </dl>
                            <?php }?>
                            <?php if($is_distribution){?>
                                <dl class="WSY_bulkdl WSY_bulkdldt w90px">
                                    <dt style="width:200px;text-align:right;">代理商折扣率：</dt>
                                    <dd><input type="text" onkeyup="clearNoNum(this,4);" name="agent_discount" id="agent_discount" style="min-width:100px" value="<?php echo $agent_discount; ?>" >%</dd>
                                </dl>
                            <?php }?>
                            <?php if($is_opencashback){?>
                            <dl class="WSY_bulkdl WSY_bulkdldt w90px">
                                <?php if($cb_condition==0){?>
                                <dt style="width:200px;text-align:right;">消费奖励金额（固定金额）：</dt>
                                <dd><input type="text" name="cashback" id="cashback" style="min-width:100px" value="<?php echo $cashback; ?>"><i style="color:#646464"><?php echo OOF_T ?></i><font style="color:red;margin-left:10px;">填写0则不赠送，填写-1则按公共设置赠送<?php echo $public_cashback;?><?php echo OOF_T ?></font></dd>
                                <?php }else{?>
                                <dt style="width:200px;text-align:right;">奖励金额（产品价格按比例）：</dt>
                                <dd><input type="text" name="cashback_r" id="cashback_r" style="min-width:100px" value="<?php echo $cashback_r; ?>"><i style="color:#646464">（0～1）<font style="color:red;margin-left:10px;">填写0则不赠送，填写-1则按公共设置赠送<?php echo $public_cashback_r*100;?>%</font></i></dd>
                                <?php }?>
                            </dl>
                            <?php }?>
                    <?php }?>
                    <?php if($currency_isOpen>0){?>
                    <dl class="WSY_bulkdl WSY_bulkdldt w90px">
                        <dt style="width:200px;text-align:right;"><?php echo defined('PAY_CURRENCY_NAME') ?PAY_CURRENCY_NAME: '购物币'; ?>抵扣比例：</dt>
                        <dd><input type="text" style="min-width:100px" id="currency_percentage" name="currency_percentage" value="<?php echo $currency_percentage; ?> " placeholder="请输入<?php echo defined('PAY_CURRENCY_NAME') ?PAY_CURRENCY_NAME: '购物币'; ?>抵扣比例：" onkeyup="clearNoNumNew(this,2);">%</dd>
                        <dd style="color:red">（-1为等同于全局比例，亦可填0-100的百分比数，最多可支持小数点后两位，如1.01%）</dd>
                    </dl>
                    <?php }?>

                    <div class="product-footer-btn">
                        <button class="WSY_button"  type="button" onclick="prev_tab(this)">上一步</button>
                    <?php if($status == 0 && $status !=null){ ?><!--待审核状态 0:、待审核-->
                        <button class="WSY_button" onclick="offer_save(this)" type="button">保存通过<img style="position:relative;top:2px;left:5px;width:15px;" id="product_offer" src="../../Common/images/Base/help.png"></button>
                        <button class="WSY_button" onclick="offer(this)" type="button">通过上架</button>
                        <button class="WSY_button" onclick="refundProduct(this)" type="button">驳回</button>
                        <button class="WSY_button" onclick="javascript:history.go(-1);" type="button">返回</button>
                    <?php } else {?>
                        <button class="WSY_button" type="button" onclick="saveProduct()">提交保存</button>
                    <?php } ?>
                    </div>
                </div>






            <input type=hidden name="stock_pidarr" id="stock_pidarr" value="<?php echo $stock_pidarr; ?>" />
            <input type=hidden name="keyid" id="keyid" value="<?php echo $product_id; ?>" />
            <input type=hidden name="offer_id" id="offer_id" value="0">
            <input type=hidden name="isout" id="isout" value=<?php echo $product_isout; ?> />
            <input type=hidden name="isnew" id="isnew" value=<?php echo $product_isnew; ?> />
            <input type=hidden name="ishot" id="ishot" value=<?php echo $product_ishot; ?> />
            <input type=hidden name="issnapup" id="issnapup" value=<?php echo $product_issnapup; ?> />
            <input type=hidden name="isvp"  id="isvp"  value=<?php echo $product_isvp; ?> />
            <input type=hidden name="is_virtual"  id="is_virtual"  value=<?php echo $is_virtual; ?>  />
            <input type=hidden name="is_charitable"  id="is_charitable"  value=<?php echo $is_charitable; ?> />
            <input type=hidden name="charitable_propotion"  id="charitable_propotion"  value=<?php echo $charitable_propotion; ?> />
            <input type=hidden name="pro_price_detail" id="pro_price_detail" />
            <input type=hidden name="tradeprices" id="tradeprices" value="<?php echo $product_tradeprices; ?>" />
            <input type=hidden name="type_ids" id="type_ids" value="<?php echo $type_ids; ?>" />
            <input type=hidden name="type_id" id="type_id" value="<?php echo $type_id; ?>" />
            <input type=hidden name="auth_user_id" id="auth_user_id" value=<?php echo $auth_user_id; ?> />
            <input type=hidden name="mess_num" id="mess_num" value="<?php echo $mess_num; ?>" />
            <input type=hidden name="is_currency" id="is_currency" value="<?php echo $is_currency; ?>" />
            <input type=hidden name="is_guess_you_like" id="is_guess_you_like" value="<?php echo $is_guess_you_like; ?>" />
            <input type=hidden name="is_free_shipping" id="is_free_shipping" value="<?php echo $product_is_free_shipping; ?>" />
            <input type=hidden name="isscore" id="isscore" value="<?php echo $isscore; ?>" />
            <input type=hidden name="is_pickup" id="is_pickup" value="<?php echo $is_pickup; ?>" />
            <input type=hidden name="islimit" id="islimit" value="<?php echo $islimit; ?>" />
            <input type=hidden name="is_first_extend" id="is_first_extend" value="<?php echo $is_first_extend; ?>" />
            <input type=hidden name="istax" id="istax" value="<?php echo $tax_id; ?>" />
            <input type=hidden name="is_mini_mshop" id="is_mini_mshop" value="<?php echo $is_mini_mshop; ?>" />
            <input type=hidden name="exglb" value="" />
            <input type=hidden name="sa" value="" />
            <!-- <div class="WSY_text_input01">
                <?php if($status == null) { ?>
                    <div class="WSY_text_input"><button class="WSY_button" id="btnSave" type="button" onclick="saveProduct()">提交保存</button></div>
                    <div class="WSY_text_input"><button class="WSY_button" onclick="javascript:history.go(-1);" type="button">返回</button></div>
                <?php } ?>
                <?php if($status == 0 && $status !=null){ ?><!--待审核状态 0:、待审核-->
                    <!-- <div class="WSY_text_input"><button class="WSY_button" onclick="offer_save(this)" type="button">保存通过<img style="position:relative;top:2px;left:5px;width:15px;" id="product_offer" src="../../Common/images/Base/help.png"></button></div>
                    <div class="WSY_text_input"><button class="WSY_button" onclick="offer(this)" type="button">通过上架</button></div>
                    <div class="WSY_text_input"><button class="WSY_button" onclick="refundProduct(this)" type="button">驳回</button></div>
                    <div class="WSY_text_input"><button class="WSY_button" onclick="javascript:history.go(-1);" type="button">返回</button></div>
                <?php }else if($status == 1){ ?>
                    <div class="WSY_text_input"><button class="WSY_button" onclick="javascript:history.go(-1);" type="button">返回</button></div>
                <?php }else if($status == 2 || $status == 3){ ?>
                    <div class="WSY_text_input"><button class="WSY_button" onclick="del(this)" type="button">删除</button></div>
                    <div class="WSY_text_input"><button class="WSY_button" onclick="javascript:history.go(-1);" type="button">返回</button></div>
                <?php } ?>
            </div> -->
        </div>
            <div class="prompt">
                <div class="prompt_head">
                    复制到
                </div>
                <div class="prompt_cont">
                    <ul>
                    </ul>
                </div>
                <div class="prompt_end">

                    <input type="button" class="do_prompt" value="确定" onclick="do_prompt();">
                    <input type="button" value="取消" onclick="hide_prompt();">
                </div>
            </div>

            <div class="all_prompt" style="display:none;">
                <div class="prompt_head">
                    全部设置
                </div>
                <div class="prompt_cont" style="padding:20px 0;">
                    把相关属性复制到产品中，是否复制
                </div>
                <div class="prompt_end">
                    <input type="button" class="do_prompt" value="确定" onclick="all_prompt();">
                    <input type="button" value="取消" onclick="hide_prompt();">
                </div>
            </div>
        </div>
        <input type="hidden" value="0" name="edit_or_product" id="edit_or_product"/>
        <input type="hidden" value="" name="my_from" id="my_from"/>
        <input type="hidden" value="" name="del_pro" id="del_pro"/>
        <input type="hidden" value="" name="del_lsit_pro" id="del_lsit_pro"/>
</form>
</div>
<!-- 3D素材 -->
<div class="mask_3d">
    <!---->
    <div class="box_3D">
        <div>
          <img src="../../Common/images/Product/3D_close.png" style="float: right;margin:20px;cursor: pointer;" id="3d_close">
        </div>
        <div class="columnbox_table" style="width: 98%;margin: 0 auto;margin-top: 20px;">
            <p class="title_3D">选择3D素材</p>
            <!--表格开始-->
            <div class="box_box">
              <table width="96%" class="WSY_table" id="WSY_t1_3d" style="background: #FFF">
            </table>
            </div>
        </div>
        <!--翻页开始-->
        <div class="WSY_page" style="display:none;"></div>
        <!--翻页结束-->
        <input type="hidden" id="pagenum" value="1">
        <input type="hidden" id="pageCount" value="1">
        <input type="hidden" id="type_num" value="-1">
    </div>
</div>
<script src="/weixinpl/js/fenye/jquery.page1.js"></script>
<!--配置ckeditor和ckfinder-->
<script type="text/javascript" src="../../../../weixin/plat/Public/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="../../../../weixin/plat/Public/ckfinder/ckfinder.js"></script>
<!--编辑器多图片上传引入开始-->
<script type="text/javascript" src="../../../../weixin/plat/Public/js/jquery.dragsort-0.5.2.min.js"></script>
<script type="text/javascript" src="../../../../weixin/plat/Public/swfupload/swfupload/swfupload.js"></script>
<script type="text/javascript" src="../../../../weixin/plat/Public/swfupload/js/swfupload.queue.js"></script>
<script type="text/javascript" src="../../../../weixin/plat/Public/swfupload/js/fileprogress.js"></script>
<script type="text/javascript" src="../../../../weixin/plat/Public/swfupload/js/handlers.js"></script>
<!--编辑器多图片上传引入结束-->
<script type="text/javascript" src="../../../common/js_V6.0/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="../../../common/js_V6.0/content.js"></script>
<script charset="utf-8" src="../../../common/js/layer/V2_1/layer.js"></script>
<script type="text/javascript" src="../../../js/ajaxfileupload.js"></script>
<script>
    var customer_id =<?php echo $customer_id;?>;
    var wsy_page = $('.WSY_page');
    var pagenum=1;
    var search_val='';
    var parent_relation_type  = <?php echo json_encode($parent_relation_type);?>;
    parent_relation_type = eval(parent_relation_type);
    function go_page(p,search_val){
        $.ajax({
            url:'/wsy_prod/admin/3dmodel/three_d_api.php',
            data:{'data_op':'get_model_more','customer_id':customer_id,'pagenum':p,'search_val':search_val},
            type:'GET',
            async: false,
            success:function(res){
                html = '<thead class="WSY_table_header">';
                html += '<th width="10%">编号</th>';
                html += '<th width="20%">素材名称</th>';
                html += '<th width="20%">素材图片</th>';
                html += '<th width="20%">类型</th>';
                html += '<th width="20%">素材链接</th>';
                html += '<th width="10%">操作</th>';
                html += '</thead>';
                var json_return = $.parseJSON(res);
                var data = json_return['data']['data'];
                for(var i in data){
                    html += '<tr >';
                    html += '<td style="text-align:center;">'+data[i].id+'</td>';
                    html += '<td style="text-align:center;"> '+data[i].title+'</td>';
                    html += '<td style="text-align:center;"> <img width="100" height="100" src="'+data[i].cover_img+'"></td>';
                    html += '<td style="text-align:center;">3D模型</td>';
                    html += '<td style="text-align:center;"><a target="_blank" href="'+data[i].embedLink+'">'+data[i].embedLink+' </a></td>';
                    html += '<td style="text-align:center;"><span class="WSY_buttontj" style="padding:3px 15px;border-radius:3px;margin-left:20px;cursor:pointer;display:inline-block;margin-top:5px;" onclick="WSY_buttontj_tj(this)">添加</span></td>';
                    html += '</tr>';
                }
            }

        });
        $('#WSY_t1_3d').html(html);
    }

    function jumppage(){
        var p=$("input[name='WSY_jump_page']").val()
        $.ajax({
            url:'/wsy_prod/admin/3dmodel/three_d_api.php',
            data:{'data_op':'get_model_more','customer_id':customer_id,'pagenum':p,'search_val':search_val},
            type:'GET',
            async: false,
            success:function(res){
                var json_return = $.parseJSON(res);
                var data = json_return['data']['data'];
                html = '<thead class="WSY_table_header">';
                html += '<th width="10%">编号</th>';
                html += '<th width="20%">素材名称</th>';
                html += '<th width="20%">素材图片</th>';
                html += '<th width="20%">类型</th>';
                html += '<th width="20%">素材链接</th>';
                html += '<th width="10%">操作</th>';
                html += '</thead>';
                for(var i in data){
                    html += '<tr >';
                    html += '<td>'+data[i].id+'</td>';
                    html += '<td style="text-align:center;"> '+data[i].title+'</td>';
                    html += '<td style="text-align:center;"> <img width="100" height="100" src="'+data[i].cover_img+'"></td>';
                    html += '<td style="text-align:center;">3D模型</td>';
                    html += '<td style="text-align:center;"><a target="_blank" href="'+data[i].embedLink+'">'+data[i].embedLink+' </a></td>';
                    html += '<td style="text-align:center;"><span class="WSY_buttontj" style="padding:3px 15px;border-radius:3px;margin-left:20px;cursor:pointer;display:inline-block;margin-top:5px;" onclick="WSY_buttontj_tj(this)">添加</span></td>';
                    html += '</tr>';
                }
                wsy_page.createPage({
                    pageCount:  Math.ceil(json_return['data']['total']/20),
                    current:json_return['data']['current_page'],
                    backFn:function(p){
                        go_page(p,search_val);
                    }
                });
            }

        });
        $('#WSY_t1_3d').html(html);
    }

    function show_three_d(){
        html = '<thead class="WSY_table_header">';
        html += '<th width="10%">编号</th>';
        html += '<th width="20%">素材名称</th>';
        html += '<th width="20%">素材图片</th>';
        html += '<th width="20%">类型</th>';
        html += '<th width="20%">素材链接</th>';
        html += '<th width="10%">操作</th>';
        html += '</thead>';
        $.ajax({
            url:'/wsy_prod/admin/3dmodel/three_d_api.php',
            data:{'data_op':'get_model_more','customer_id':customer_id,'pagenum':pagenum,'search_val':search_val},
            type:'GET',
            async: false,
            success:function(res){
                var json_return = $.parseJSON(res);
                var data = json_return['data']['data'];
                for(var i in data){
                    html += '<tr >';
                    html += '<td>'+data[i].id+'</td>';
                    html += '<td style="text-align:center;"> '+data[i].title+'</td>';
                    html += '<td style="text-align:center;"> <img width="100" height="100" src="'+data[i].cover_img+'"></td>';
                    html += '<td style="text-align:center;">3D模型</td>';
                    html += '<td style="text-align:center;"><a target="_blank" href="'+data[i].embedLink+'">'+data[i].embedLink+' </a></td>';
                    html += '<td style="text-align:center;"><span class="WSY_buttontj" style="padding:3px 15px;border-radius:3px;margin-left:20px;cursor:pointer;display:inline-block;margin-top:5px;" onclick="WSY_buttontj_tj(this)">添加</span></td>';
                    html += '</tr>';
                }
                wsy_page.createPage({
                    pageCount:  Math.ceil(json_return['data']['total']/20),
                    current:json_return['data']['current_page'],
                    backFn:function(p){
                        go_page(p,search_val);
                    }
                });
            }

        });
        $('#WSY_t1_3d').html(html);
        $('#WSY_t0').show();
        wsy_page.show();
    }
</script>
<script>
    layer.config({
        extend: '/extend/layer.ext.js'
    });
    var orgin_price_b = $("input[name='orgin_price']").val();
    var now_price_b = $("input[name='now_price']").val();
    var for_price_b = $("input[name='for_price']").val();
    var cost_price_b = $("input[name='cost_price']").val();
    var propertyids_b = $("input[name='propertyids']").val();
    var wholesale_id_b = $("input[name='wholesale_id']").val();
    var is_del_pro = 0;
    var wholesale_childid_b = '';
    $("input[class='child_wholesale_c']:checked").each(function() {
        wholesale_childid_b = wholesale_childid_b + $(this).val() + "_";
    });
    wholesale_childid_b = wholesale_childid_b.substring(0,wholesale_childid_b.length-1);
    var ordering_retail = '<?php echo $ordering_retail; ?>';
    var attr_img_str   = '<?php echo $attr_img_str;  ?>';  //主属性图片
    var attr_img_array = new Array();
    customer_id_en = '<?php echo $customer_id_en;?>';
    //page_index = 4;
    var search_type_id = '';

    //下标数组形式
    if(attr_img_str != ''){
        attr_img_a = attr_img_str.split(",");
        console.log(attr_img_a);
        for (var i =0; i<attr_img_a.length; i++) {
            temp_attr = attr_img_a[i].split("_");
            temp_attr_zone = temp_attr[0];

            //2017.12.20   有些文件路径含有下划线，判断之后再组合为正确的路径
            if(temp_attr.length>2){
              temp_attr[0] = '';
              temp_attr[1] = temp_attr.join("_");
              temp_attr[1] = temp_attr[1].substr(1);
            }
            console.log(temp_attr[1]);

            attr_img_array[temp_attr_zone] = temp_attr[1];
        }
    }

    var attr_parent_id = '<?php echo $attr_parent_id;?>';  //主属性父id
    var now_sa         = '<?php echo $sa;?>';
    var product_id_2   = '<?php echo $product_id; ?>';     //产品ID
    get_product_attr();//初始化也执行一次，以防修改产品时不保存跳步
    if(now_sa == 4){
      get_product_attr();
    }
    /*上传属性图片begin*/
    //添加所选属性（主属性图片步骤）
    function get_product_attr(){
        //子属性清除一遍
        //$('.product-attr-list').html('');
        //str_p 是父属性名称  str是已经添加图片的属性
        var str_p = '<option value="0">请选择</option>';
        if(product_id_2 != -1 && newProArray.length == 0){//判断哪个已经是选择的
            console.log(oldPidArray,'++++++1');
            if(oldPidArray.length != 0 ){
                for(var i=0 ;i<oldPidArray.length;i++){
                    temp_p_name = $('#parent_name_'+oldPidArray[i]).html();
                    if(oldPidArray[i] == attr_parent_id){
                        str_p += '<option value="'+oldPidArray[i]+'" selected="selected">'+temp_p_name+'</option>';
                        if(typeof(parent_relation_type[attr_parent_id]) == 'undefined'){
                            var attr_box = $('.add_relation_pros_-1[pro_parent_id="'+oldPidArray[i]+'"]');
                        }else{
                            var attr_box = $('.add_relation_pros_'+parent_relation_type[attr_parent_id]+'[pro_parent_id="'+oldPidArray[i]+'"]');
                        }
                        var str = '';
                        attr_box.find('.WSY_clorop').children('p').each(function(){
                            var check = $(this).find('input[type="checkbox"]');
                            if(check.is(':checked')){
                                var attr_name = check.attr('data_text'),
                                attr_id   = check.val();
                                str += '<div class="attr-list">';
                                str += '<span class="attr-name">'+attr_name+'：</span>';
                                str += '<div class="attr-img">';
                                var if_exist = -1;  //判断是否存在旧数据
                                if( attr_img_array.hasOwnProperty(attr_id)){
                                    if_exist = attr_id;
                                }
                                console.log(attr_img_array);
                                if(if_exist != -1){
                                    str += '<div class="attr-img-box" style="display:block;">';
                                    str += '<img class="attr-src" src="'+attr_img_array[if_exist]+'">';
                                    str += '<input type="hidden" name="attr_src[]" class="attr-hide" value="'+if_exist+'_'+attr_img_array[if_exist]+'">';
                                    str += '<span class="attr-delet" onclick="del_attr_img(this)">删除图片</span></div>';
                                    str += '<div class="attr-file" style="display:none;">';
                                }else{
                                    str += '<div class="attr-img-box">';
                                    str += '<img class="attr-src" src="">';
                                    str += '<input type="hidden" name="attr_src[]" class="attr-hide" value="">';
                                    str += '<span class="attr-delet" onclick="del_attr_img(this)">删除图片</span></div>';
                                    str += '<div class="attr-file" >';
                                }

                                str += '<input type="file" onchange="getAttrImg(this);" name="file_'+attr_id+'" id="file_'+attr_id+'" value="Submit" class="attr-file-btn" attr_id="'+attr_id+'"/>上传图片';
                                str += '</div></div></div>';
                            }
                        });
                        $('.product-attr-list').html(str);
                    }else{
                        str_p += '<option value="'+oldPidArray[i]+'">'+temp_p_name+'</option>';
                    }
                }
            }
        }else{
            console.log(newProArray,'------1');
            if(newProArray.length != 0){
                for(var i=0 ;i<newProArray.length;i++){
                    temp_arr  = newProArray[i][0].split(',');
                    temp_p_id = temp_arr[2];
                    temp_p_name = $('#parent_name_'+temp_p_id).html();
                    if(temp_p_id == attr_parent_id){
                        str_p += '<option value="'+temp_p_id+'" selected="selected">'+temp_p_name+'</option>';
                        if(typeof(parent_relation_type[temp_p_id]) == 'undefined'){
                            var attr_box = $('.add_relation_pros_-1[pro_parent_id="'+temp_p_id+'"]');
                        }else{
                            var attr_box = $('.add_relation_pros_'+parent_relation_type[attr_parent_id]+'[pro_parent_id="'+temp_p_id+'"]');
                        }
                        var str = '';
                        attr_box.find('.WSY_clorop').children('p').each(function(){
                            var check = $(this).find('input[type="checkbox"]');
                            if(check.is(':checked')){
                                var attr_name = check.attr('data_text'),
                                attr_id   = check.val();
                                str += '<div class="attr-list">';
                                str += '<span class="attr-name">'+attr_name+'：</span>';
                                str += '<div class="attr-img">';
                                var if_exist = -1;  //判断是否存在旧数据

                                if( attr_img_array.hasOwnProperty(attr_id)){
                                    if_exist = attr_id;
                                }

                                if(if_exist != -1){
                                    str += '<div class="attr-img-box" style="display:block;">';
                                    str += '<img class="attr-src" src="'+attr_img_array[if_exist]+'">';
                                    str += '<input type="hidden" name="attr_src[]" class="attr-hide" value="'+if_exist+'_'+attr_img_array[if_exist]+'">';
                                    str += '<span class="attr-delet" onclick="del_attr_img(this)">删除图片</span></div>';
                                    str += '<div class="attr-file" style="display:none;">';
                                }else{
                                    str += '<div class="attr-img-box">';
                                    str += '<img class="attr-src" src="">';
                                    str += '<input type="hidden" name="attr_src[]" class="attr-hide" value="">';
                                    str += '<span class="attr-delet" onclick="del_attr_img(this)">删除图片</span></div>';
                                    str += '<div class="attr-file" >';
                                }

                                str += '<input type="file" onchange="getAttrImg(this);" name="file_'+attr_id+'" id="file_'+attr_id+'" value="Submit" class="attr-file-btn" attr_id="'+attr_id+'"/>上传图片';
                                str += '</div></div></div>';
                            }
                        });
                        $('.product-attr-list').html(str);
                    }else{
                        str_p += '<option value="'+temp_p_id+'">'+temp_p_name+'</option>';
                    }

                }
            }
        }
        $('.product-attr').html(str_p);
    }

    $('.product-attr').on('change',function(){
        var _val = $(this).val();
        console.log(_val);
        if(_val != 0){
            //var attr_box = $('.add_relation_pros_-1[pro_parent_id="'+_val+'"]');
            var attr_box = $('body').find('[pro_parent_id="'+_val+'"]');
            var str = '';
            attr_box.find('.WSY_clorop').children('p').each(function(){
            var check = $(this).find('input[type="checkbox"]');
            if(check.is(':checked')){
                var attr_name = check.attr('data_text'),
                attr_id = check.val();
                str += '<div class="attr-list">';
                str += '<span class="attr-name">'+attr_name+'：</span>';
                str += '<div class="attr-img">';
                var if_exist = -1;  //判断是否存在旧数据
                for(index in attr_img_array){
                    if(index == attr_id){
                        if_exist = index;
                    }
                }
                if(if_exist != -1){
                    str += '<div class="attr-img-box" style="display:block;">';
                    str += '<img class="attr-src" src="'+attr_img_array[if_exist]+'">';
                    str += '<input type="hidden" name="attr_src[]" class="attr-hide" value="'+if_exist+'_'+attr_img_array[if_exist]+'">';
                    str += '<span class="attr-delet" onclick="del_attr_img(this)">删除图片</span></div>';
                    str += '<div class="attr-file" style="display:none;">';
                }else{
                    str += '<div class="attr-img-box">';
                    str += '<img class="attr-src" src="">';
                    str += '<input type="hidden" name="attr_src[]" class="attr-hide" value="">';
                    str += '<span class="attr-delet" onclick="del_attr_img(this)">删除图片</span></div>';
                    str += '<div class="attr-file" >';
                }

                str += '<input type="file" onchange="getAttrImg(this);" name="file_'+attr_id+'" id="file_'+attr_id+'" value="Submit" class="attr-file-btn" attr_id="'+attr_id+'"/>上传图片';
                str += '</div></div></div>';
                }
            });
            $('.product-attr-list').html(str);
        }else{
            $('.product-attr-list').html('');
        }
    });

    function getAttrImg(obj){
        var box = $(obj).parents('.attr-img');
        var imgPath = $(obj).val();
        var file_id = $(obj).attr('id');
        var attr_id = $(obj).attr('attr_id');
        //判断上传文件的后缀名
        var strExtension = imgPath.substr(imgPath.lastIndexOf('.') + 1);
        if (strExtension != 'jpg' && strExtension != 'gif' && strExtension != 'png' && strExtension != 'bmp') {
            alert('上传图片的格式不正确，请上传jpg、gif、png或者bmp的格式的图片！');
            return;
        }
        console.log(attr_id);
        console.log(file_id);
        console.log(imgPath,'imgPath');
        $.ajaxFileUpload({
            url: 'save_product_attrimg.php?customer_id=<?php echo $customer_id_en;?>&product_id=<?php echo $product_id; ?>&attr_id='+attr_id, //用于文件上传的服务器端请求地址
            secureuri: false, //是否需要安全协议，一般设置为false
            fileElementId: file_id, //文件上传域的ID
            dataType: 'json', //返回值类型 一般设置为json
            success: function (data, status)  //服务器成功响应处理函数
            {
                console.log(data)
                if(data.status=='ok'){
                    box.find('.attr-src').attr('src',data.info);
                    box.find('.attr-hide').val(attr_id+'_'+data.info);
                    box.find('.attr-img-box').show();
                    box.find('.attr-file').hide();
                    attr_img_array[attr_id] = data.info;
                }else{
                    alert('上传图片失败，请重新上传！');
                }
            },
            error: function (data, status, e)//服务器响应失败处理函数
            {
                console.log(data)
                alert('上传图片失败，请重新上传！'+e+data.info);
            }
        })
    }
    //删除图片
    function del_attr_img(obj){
        var box = $(obj).parents('.attr-img');
        box.find('.attr-src').attr('src','');
        box.find('.attr-hide').val('');
        box.find('.attr-img-box').hide();
        box.find('.attr-file').show();
    }

    /*上传属性图片end*/
    $('input[name="exglb"]').val('');
    /*切换tab*/
    var arr = '<?php echo $product_id; ?>'
        if (arr != '-1') {
            $('.product-tab-box.edit li').on('click',function(){
            var that = $(this),
            index = that.index();
            $('input[name="exglb"]').val(1);
            /*判断数据是否更改*/
            layer.confirm('尚未保存的东西将会丢失，是否保存？', {btn: ['保存','不保存','取消'],
                  btn1:function(){that.addClass('tab-active').siblings().removeClass('tab-active');
                      var result = saveProduct();
                  }, btn2:function(){
                       that.addClass('tab-active').siblings().removeClass('tab-active');
                      //$('.WSY_data .product-tab-list').eq(index).addClass('tab-active').siblings().removeClass('tab-active');
                      location.href=window.location.href+'&sa='+(index+1);
                  }
            });
        })
    }

    function sa(obj) {
        $('input[name="sa"]').val(obj);
    }

    /*上一步*/
    function prev_tab(obj){
        var index = $(obj).parents('.product-tab-list').index();
        $('.product-tab-box li').eq(index-1).addClass('tab-active').siblings().removeClass('tab-active');
        $('.WSY_data .product-tab-list').eq(index-1).addClass('tab-active').siblings().removeClass('tab-active');
    }
    /*下一步*/
    function next_tab(obj){
        var index = $(obj).parents('.product-tab-list').index();
        $('.product-tab-box li').eq(index+1).addClass('tab-active').siblings().removeClass('tab-active');
        $('.WSY_data .product-tab-list').eq(index+1).addClass('tab-active').siblings().removeClass('tab-active');
    }
    $('input,textarea').on('focus',function(){
        clear_tip();
    })
    /*第一步判断*/
    function first_step(obj){
        var parent          = $(obj).parents('.tab-active'),
            name            = parent.find('input[name="name"]'),
            pro_orgin_price = parent.find('input[name="orgin_price"]'),
            //pro_unit        = parent.find('input[name="unit"]'),
            pro_now_price   = parent.find('input[name="now_price"]'),
            pro_weight      = parent.find('input[name="weight"]'),
            pro_for_price   = parent.find('input[name="for_price"]'),
            pro_need_score  = parent.find('input[name="need_score"]'),
            pro_cost_price  = parent.find('input[name="cost_price"]'),
            pro_storenum    = parent.find('input[name="storenum"]'),
            foreign_mark    = parent.find('input[name="foreign_mark"]'),
            introduce       = parent.find('textarea[name="introduce"]');
            name_val        = name.val();
        if(name.val() === ''){
            input_tip('请输入产品名称！',name);
            return false;
        }

        if(name_val.indexOf("-") > 0){
           input_tip('产品名称不能输入非法字符 - ',name);  
          return false;   
        }


        if(pro_orgin_price.val() === ''){
            input_tip('请输入产品原价！',pro_orgin_price);
            return false;
        }
        // if(pro_unit.val() === ''){
        //  input_tip('请输入产品单位！',pro_unit);
        //  return false;
        // }
        if(pro_now_price.val() === ''){
            input_tip('请输入产品现价！',pro_now_price);
            return false;
        }
        if(pro_weight.val() === ''){
            input_tip('请输入产品重量！',pro_weight);
            return false;
        }
        if(pro_for_price.val() === ''){
            input_tip('请输入产品成本！',pro_for_price);
            return false;
        }
        if(pro_need_score.val() === ''){
            input_tip('请输入所需积分！',pro_need_score);
            return false;
        }
        if(pro_cost_price.val() === ''){
            input_tip('请输入产品供货价！',pro_cost_price);
            return false;
        }
        if(pro_storenum.val() === ''){
            input_tip('请输入产品库存！',pro_storenum);
            return false;
        }
        // if(foreign_mark.val() === ''){
        //  input_tip('请输入关键字！',foreign_mark);
        //  return false;
        // }
        // if(introduce.val() === ''){
        //  input_tip('请输入产品介绍！',introduce);
        //  return false;
        // }
        next_tab(obj);
    }
    /*第二步判断*/
    function second_step(obj){
        var parent   = $(obj).parents('.tab-active'),
            classify = parent.find('#f-box');
            console.log(classify.html().replace(/(^\s*)|(\s*$)/g, ""));
        if(classify.html().replace(/(^\s*)|(\s*$)/g, "") === ''){
            input_tip('请选择产品分类！',$('.slide_btn'));
            alert('请选择产品分类！');
            return false;
        } 

        if($('#f-box').find("div").length==0){
            alert('请选择产品分类！');
            return false;
        }
        next_tab(obj);
    }
    /*第三步判断*/
    function third_step(obj){
        var status = true;

        //没有选择批发属性子属性后，导致商城产品详情页部分属性显示错误，现在加上选择判断   2018.1.29  cjj
        var wholesale_num = $("#wholesale").val();
        console.log(wholesale_num);
        if(wholesale_num != -1){
            var wholesale_select_num = 0;
            // var wholesale_id_check = $("#wholesale_"+wholesale_num).val();
            $("#wholesale_"+wholesale_num).find('.WSY_clorop').children('p').each(function(){
              var check = $(this).find('input[type="checkbox"]');
                if(check.is(':checked')){
                  wholesale_select_num = wholesale_select_num +1;
                }else{
                
                }
            });

            console.log(wholesale_select_num);
            if(wholesale_select_num==0){
              alert('请选择产品批发子属性！');
              return false;
            }
        }

        $('.tab-active .WSY_table input').each(function(){
            // console.log($(this).attr('name'));
            if($(this).attr('name') != 'pro_foreign_mark') {
                if($(this).val().replace(/(^\s*)|(\s*$)/g, "") === ''){
                    input_tip('内容不能为空！',$(this));
                    status = false;
              }
            }
        })
        if(status){
            get_product_attr();
            next_tab(obj);
        }
    }
    /*第四步判断*/
    function next_step(obj) {
        var status = true;
        var is_charitable = $("#is_charitable").val();
        if(is_charitable > 0){
            var donation_rate        = $("#donation_rate").val();
            var charitable_propotion = $("#charitable_propotion").val();
            if(donation_rate==""){
                alert("捐赠比率不能为空！");
                status = false;
            }
            if(donation_rate<0){
                alert("捐赠比率不能为负数！");
                status = false;
            }
            if(isNaN(donation_rate)){
                alert('捐赠比率必须为数字！');
                status = false;
            }
            if(status == true && donation_rate < charitable_propotion){
                alert("捐赠比率低于"+charitable_propotion+"，无法提交！");
                status = false;
            }
            if(donation_rate>1){
                alert("捐赠比率不能大于1！");
                return;
            }
       }
       if(status){
            next_tab(obj);
        }
    }
    /*错误提示*/
    function input_tip(concent,obj){
        var str = '<div class="input-tip">'+concent+'</div>';
        if(!$('.input-tip').length){
            obj.parent().css({'position':'relative','z-index':'999'});
            obj.after(str);
        }
    }
    /*删除错误提示*/
    function clear_tip(){
        $('.input-tip').remove();
    }

    function selectText(obj,id){
        // $('.editor1').hide();
        // $('.editor2').hide();
        // var win = $('iframe')[1].contentWindow;
        // 获取点击前标签的内容
        var beforeValue = $('.cke_wysiwyg_frame').contents().find('body').html()
        // 点击前id对应的标签
        var beforeName = $(obj).siblings('.selected').data('name')
        // 赋值value保存
        $('#'+beforeName).html(beforeValue)
        // 选择标识
        $(obj).addClass('selected');
        $(obj).siblings().removeClass('selected')
        // 获取点击后标签将要显示的内容
        var nowName = $(obj).data('name')
        var nowValue = $('#'+nowName).text()
        // 赋值点击后标签
        $('.cke_wysiwyg_frame').contents().find('body').html(nowValue)
        // 初始化
        $('#ck_fs_upload_progress').text('未选择文件')
        $('#divStatus').text('')
    }

    //保存最后一次修改
    function selectText_last(){
        // console.log('selectText_last');
        var text_selected = $('.editor.selected');
        var name = text_selected.data('name');
        $('.cke_wysiwyg_frame').contents().find('body').find('style').remove();/*清除旧版本内容出现的无用<style>标签*/
        var text_cont = $('.cke_wysiwyg_frame').contents().find('body').html();
        $("#"+name).html(text_cont);
        // console.log(text_selected);
        // console.log(text_cont);
    }

    //批发select框事件
    $("#wholesale").on('change',function(){
        $("#wholesale_id").val($(this).val());
        //当批发选择更换的时候，先清空当前所有选择
        $("input[id='child_wholesale']").each(function(){
            this.checked=false;
            chkPro();//执行勾选取消动作
        })
        $(".wholesale_date").hide();
        $("#wholesale_"+$(this).val()).show();
    });

    function change_Privilege(i){
        if(i.checked){
            $("#back_privilege").show('slow');
        }else{
            $("input[name='privilege[]']").each(function(){
                $(this).attr("checked", false);
            });
            $("#back_privilege").hide('slow');
        }
    }
    function change_link_package(i){
        if(i.checked){
            $(".package").show('slow');
        }else{
            $(".package").hide('slow');
        }
    }
    function change_link_coupons(i){
        if(i.checked){
            $(".coupons").show('slow');
        }else{
            $(".coupons").hide('slow');
            $(".coupons_list").hide('slow');
        }
    }
    function select_coupons(){
        $(".coupons_list").show('slow');
    }

    function hide_prompt(){
        $(".prompt").hide();
        $(".all_prompt").hide();
    }
    function do_prompt(){
        var pos_num = $("input[name='pos']:checked").val();
        var pos     = $(".do_prompt").attr("pos");
        var obj=$("tr[pos='"+pos+"']");
        var form_input = obj.find(".form_input");
        var show_price = obj.find(".show_price");
        var ul = $(".pos_ul");
        
        if( pos_num > 0 ){
            var strs    = new Array(); //定义一数组
            strs        = pos.split("_"); //字符分割
            var pos_id  = strs[pos_num-1];
            if(pos_num > 1){
                pos_id = "_"+pos_id;
            }else{
                pos_id = pos_id+"_";
            }
            for ( var i=0;i<ul.length ;i++ ){
                var ul_posid = ul.eq(i).attr("pos");
                if(ul_posid.indexOf(pos_id)>=0){
                    ul.eq(i).find(".form_input").eq(0).val(form_input.eq(0).val());
                    ul.eq(i).find(".form_input").eq(1).val(form_input.eq(1).val());
                    ul.eq(i).find(".form_input").eq(2).val(form_input.eq(2).val());
                    ul.eq(i).find(".form_input").eq(3).val(form_input.eq(3).val());
                    ul.eq(i).find(".form_input").eq(4).val(form_input.eq(4).val());
                    ul.eq(i).find(".form_input").eq(5).val(form_input.eq(5).val());
                    ul.eq(i).find(".form_input").eq(6).val(form_input.eq(6).val());
                    ul.eq(i).find(".form_input").eq(7).val(form_input.eq(7).val());
                    ul.eq(i).find(".form_input").eq(8).val(form_input.eq(8).val());
                    ul.eq(i).find(".show_price").html(show_price.html());
                }
            }
        }else{
            for ( var i=0;i<ul.length ;i++ ){
                var ul_posid = ul.eq(i).attr("pos");
                ul.eq(i).find(".form_input").eq(0).val(form_input.eq(0).val());
                ul.eq(i).find(".form_input").eq(1).val(form_input.eq(1).val());
                ul.eq(i).find(".form_input").eq(2).val(form_input.eq(2).val());
                ul.eq(i).find(".form_input").eq(3).val(form_input.eq(3).val());
                ul.eq(i).find(".form_input").eq(4).val(form_input.eq(4).val());
                ul.eq(i).find(".form_input").eq(5).val(form_input.eq(5).val());
                ul.eq(i).find(".form_input").eq(6).val(form_input.eq(6).val());
                ul.eq(i).find(".form_input").eq(7).val(form_input.eq(7).val());
                ul.eq(i).find(".form_input").eq(8).val(form_input.eq(8).val());
                ul.eq(i).find(".show_price").html(show_price.html());
            }
        }
        $(".prompt").hide();
    }
    //全部设置
    function all_prompt(){
        var form_input = $('.main_form .form_input');
        var show_price = $('.main_form .show_price');
        var pos_ul = $(".pos_ul");
        for ( var i=0;i<pos_ul.length;i++ ){
            pos_ul.eq(i).find(".form_input").eq(0).val(form_input.eq(0).val());
            pos_ul.eq(i).find(".form_input").eq(1).val(form_input.eq(1).val());
            pos_ul.eq(i).find(".form_input").eq(2).val(form_input.eq(2).val());
            pos_ul.eq(i).find(".form_input").eq(3).val(form_input.eq(3).val());
            pos_ul.eq(i).find(".form_input").eq(4).val(form_input.eq(4).val());
            pos_ul.eq(i).find(".form_input").eq(5).val(form_input.eq(5).val());
            pos_ul.eq(i).find(".form_input").eq(6).val(form_input.eq(6).val());
            pos_ul.eq(i).find(".form_input").eq(7).val(form_input.eq(7).val());
            pos_ul.eq(i).find(".form_input").eq(8).val(form_input.eq(8).val());
            pos_ul.eq(i).find(".show_price").html(show_price.html());
        }
        $(".all_prompt").hide();
    }

    if( 0 == <?php echo $is_Pinformation; ?> ){
        $(".div_show").hide();
    }
    var charitable_propotion = <?php echo $charitable_propotion ?>;
    layer.config({
        extend: '/extend/layer.ext.js'
    });
    /* 抢购产品提示 */
    $('#snapup_product').on('click', function(){
        layer.tips('开通后,该产品只能在抢购时间内购买','#snapup_product');
    });

    /* VP产品提示 */
    $('#vp_product').on('click', function(){
        layer.tips('开通后,购买vp产品消费累积满多少vp值可以提现佣金','#vp_product');
    });

    /* 虚拟产品提示 */
    $('#product_virtual').on('click', function(){
        layer.tips('虚拟产品不需要收货地址','#product_virtual');
    });

    /* 猜您喜欢产品提示 */
    $('#product_guess_you_like').on('click', function(){
        layer.tips('在产品详情页显示的猜您喜欢产品','#product_guess_you_like');
    });
    /* 微信小程序提示 */
    // $('#mini_mshop').on('click', function(){
    //     layer.tips('目前微信小程序，暂不支持任何形式的促销和优惠，仅作为普通商品进行销售','#mini_mshop');
    // }); 取消这个了
    <?php if($status == 0 && $status !=null){ ?><!--待审核状态 0:、待审核 审核保存信息显示-->
        $('#product_offer').on('mouseover', function(){
            layer.tips('通过后进入仓库中','#product_offer');
        });
    <?php }?>

    $(function(){
        var num=<?php echo $num;?>;//已经上架的数量
        var product_num=<?php echo $product_num;?>;//限制上架数量
        var product_id=<?php echo $product_id;?>;
        var is_auth_user = '<?php echo $_SESSION['is_auth_user'];?>';  //是否是授权用户, yes 是, no 不是

        if(product_num<=num && product_num != -1){
            document.getElementById("isout").value=1;
            document.getElementById("chk_isout").checked="checked";
            document.getElementById("chk_isout").disabled="disabled";
        }

        if(is_auth_user=="yes"){    //授权用户 上传产品只能是下架状态.
            document.getElementById("isout").value=1;
            document.getElementById("chk_isout").checked="checked";
            document.getElementById("chk_isout").disabled="disabled";
        }

        $("#div_proprices").on("keyup",".num_check",function(){
            var val = $(this).val();
            if(isNaN(val) || val < 0){
                $(this).val(0);
            }
        });

        //打开产品编辑是否显示抢购时间设置
        var issnapup = '<?php echo $product_issnapup;?>';
        if(issnapup==1){
            $('.snap_up').show();
        }else{
            $('.snap_up').hide();
        }

        //打开产品编辑是否显示限购数量设置
        var islimit = '<?php echo $islimit;?>';
        if(islimit==1){
            $('#limit_num').show();
        }else{
            $('#limit_num').hide();
        }

        //打开产品编辑是否显示首次推广奖励金额设置
        var is_first_extend = '<?php echo $is_first_extend;?>';
        if(is_first_extend==1){
            $('#extend_money').show();
        }else{
            $('#extend_money').hide();
        }

        //打开产品编辑,是否显示vp值设置
        var product_isvp = '<?php echo $product_isvp;?>';
        if(product_isvp==1){
            $('#vp_score').show();
        }else{
            $('#vp_score').hide();
        }

        function getObjectURL(file) {
            var url = null ;
            if(window.createObjectURL!=undefined) {
                url = window.createObjectURL(file) ;
            }
            else if (window.URL!=undefined) {
                url = window.URL.createObjectURL(file) ;
            }
            else if (window.webkitURL!=undefined) {
                url = window.webkitURL.createObjectURL(file) ;
            }
            return url ;
        }

        $(".upfile").change(function(){
            $('#package_img').show();
            var obj = $('#package_img');
            var objUrl;
            if(navigator.userAgent.indexOf("MSIE")>0){
                objUrl = this.value;
            }else{
                objUrl = getObjectURL(this.files[0]);
                $(obj).attr("src",objUrl);
            }
        });
    });

    CKEDITOR.replace( 'editor', //详细介绍
    {
        extraAllowedContent: 'img iframe[*]',
        filebrowserBrowseUrl : '../../../../weixin/plat/Public/ckfinder/ckfinder.html',
        filebrowserImageBrowseUrl : '../../../../weixin/plat/Public/ckfinder/ckfinder.html?Type=Images',
        filebrowserFlashBrowseUrl : '../../../../weixin/plat/Public/ckfinder/ckfinder.html?Type=Flash',
        filebrowserUploadUrl : '../../../../weixin/plat/Public/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
        filebrowserImageUploadUrl : '../../../../weixin/plat/Public/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
        filebrowserFlashUploadUrl : '../../../../weixin/plat/Public/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash'
    });
    /*zpq*/
    var threed_open="<?php echo $threed_open; ?>"
    var threed_qudao_open="<?php echo $is_travelcard; ?>"
    if(threed_open=="1" && threed_qudao_open=="1"){
      var k=1;
      k=setInterval(function(){
        if($("#cke_1_top").html()){
          $("#cke_1_top").append('<span id="cke_60" class="cke_toolbar" role="toolbar">'+
                        '<span class="cke_toolbar_start"></span>'+
                        '<span class="cke_toolgroup" role="presentation">'+
                          '<input name="threed_add" type="button" value="3D素材" style="height: 27px;width: 50px;text-align: center;" onmouseover="three_over()" onmouseleave="three_leave()" onclick="show_threed()" >'+
                        '</span>'+
                        '<span class="cke_toolbar_end"></span>'+
                      '</span>');
          clearInterval(k)
        }
      },100)
      function three_over(){
        $("input[name='threed_add']").css("box-shadow","0 0 1px rgba(0,0,0,.3) inset")
        $("input[name='threed_add']").css("background","#ccc")
        $("input[name='threed_add']").css("background-image","-webkit-linear-gradient(top,#f2f2f2,#ccc)")
      }
      function three_leave(){
        $("input[name='threed_add']").css("box-shadow","")
        $("input[name='threed_add']").css("background","")
        $("input[name='threed_add']").css("background-image","")
      }
    }
    /*zpq*/

    var pro_reward = '<?php echo $pro_reward;?>';       //产品奖励比例
    var init_reward = '<?php echo $init_reward;?>';     //总返佣比例
    var promoter_reward = '<?php echo $all;?>';         //推广员返佣比例
    // console.log(pro_reward);
    // console.log(init_reward);
    // console.log(promoter_reward);
    //如果产品奖励比例,则使用总奖励比例
    if( pro_reward == -1 ){
        var pro_reward = init_reward;       //总返佣比例
    }

    function num_check(obj){    //最小为1
        var val = $(obj).val();
        if(isNaN(val) || val < 1){
            $(obj).val(1);
        }
    }

    function num_check1(obj){   //最小为0
        var val = $(obj).val();
        if(isNaN(val) || val < 0){
            $(obj).val(0);
        }
    }

    function setParentImgIds(ids){
       $("#imgids").attr("value",ids);
    }
    //3d素材
    $("#img_link").attr("value",'');
    $("#img_3d").attr("value",'');
    function setParentImgLink(link){
        $("#img_link").attr("value",link);
    }
    function setParentImg(img){
        $("#img_3d").attr("value",img);
    }
    function chkPro(obj){ // this, pid , parent_name , parent_id
         var allpids = $("input[name='hidden_parent']");
         //判断属性数量,php post默认input数量为1000，超过就报错
         var all_line = 1;
         for(var i = 0 ; i < allpids.length ; i++ ){ //循环所有属性类型
            var tmp_num = $('.wdw dd').eq(i).find('input:checked').length;
         
            if (tmp_num>0) {
              all_line = tmp_num*all_line;
            }
         }
         //批发属性
         var tmp_num_1 = $("input[class='child_wholesale_c']:checked").length;
         if (tmp_num_1 >0) {all_line = tmp_num_1*all_line;}
         
         if (all_line*9>950) {
            $(obj).attr("checked",false);
            layer.msg('属性数量已达上限');
            return;
         }
         $('#div_proprices').html('');

         var str = "";
         var parentArray = new Array();
         for(var i = 0 , index = 0; i < allpids.length ; i++ ){ //循环所有属性类型
            var parent_id = allpids[i].value;
            var allprops = $("input[data_name='prop_"+parent_id+"']:checked");  //获取属性类型中选中的属性值
            if(allprops.length > 0){
                var childArray = new Array();
                for(var j = 0 ; j < allprops.length; j++ ){ //将每个选中的属性值信息拼接添加到数组
                    var ckprop = allprops[j];
                    //console.log("ckprop : "+ckprop);
                    var pid = $(ckprop).attr("data_pid");
                    var text = $(ckprop).attr("data_text");
                    var parent = $(ckprop).attr("data_parent");
                    childArray[j] = pid+","+text+","+parent;
                }
                parentArray[index] = childArray;
                index++;
             }
         }
         // console.log(parentArray,'===')
         setPropPrice(parentArray);
         newProArray = parentArray;  //添加产品时做记录
    }
    // console.log(oldProArray,'===');
    setPropPrice(oldProArray,old_name);
    function setPropPrice(propArrays,old_name){
        //console.log(propArrays,'=====')
        var cIndex = propArrays.length - 1;
        var counter = new Array();
        var str = getAppendText("" , "" , "");
        if(propArrays.length > 0){
            var total = 1;
            for(var i = 0 ; i<propArrays.length ; i++){
                counter[i] = 0;
                total = total * propArrays[i].length;
            }
            for(var j = 0;j < total ; j ++){ //笛卡尔乘积
                var ids = "";
                var text = "";
                for(var index = 0 ; index < propArrays.length ; index++){
                    // console.log(old_name)
                    // if(old_name[counter[index]]){
                    //     propArrays[index][counter[index]] = propArrays[index][counter[index]]+','+old_name[counter[index]]
                    // }
                    // a = '2295';
                    // console.log(propArrays[index][counter[index]])
                    var props = propArrays[index][counter[index]].split(",");

                    // console.log(props,'=====')
                    if(props.length == 1){
                        props[1] = $("#"+props[0]).val();

                    }
                    if(index > 0){
                        ids += "_";
                        text += " - ";
                    }
                    ids += props[0];
                    text += props[1];
                }
                str = getAppendText(str , ids , text);
                calcIndex();
            }
            // console.log(total);
        }
        var divp = document.getElementById("div_proprices");
        // str = str + "</div></div>";
        divp.innerHTML = str;
        if(propArrays.length > 0){  //有属性才显示全部设置
            $('.all_do_prompt').show();
        }
        $('input,textarea').on('focus',function(){
            clear_tip();
        })
        function calcIndex(){
            counter[cIndex]++;
            if(counter[cIndex] >= propArrays[cIndex].length){
                counter[cIndex] = 0 ;
                cIndex --;
                if(cIndex >= 0){
                    calcIndex();
                }
                cIndex = propArrays.length -1;
            }
        }
        var obj = $('#div_proprices');
            $(obj).children().eq(0).remove();
    }
    // console.log(ppriceHash);

    function getAppendText(str,pid,text){
        var orgin_price  = $('input[name="orgin_price"]').val();
        var now_price    = $('input[name="now_price"]').val();
        var storenum     = $('input[name="storenum"]').val();
        var need_score   = $('input[name="need_score"]').val();
        var cost_price   = $('input[name="cost_price"]').val();
        var foreign_mark = $('input[name="foreign_mark"]').val();
        var unit         = $('input[name="unit"]').val();
        var weight       = $('input[name="weight"]').val();
        var for_price    = $('input[name="for_price"]').val();

        if(ppriceHash.contains(pid)){
            var onprice = ppriceHash.items(pid);
            onprices =  onprice.split("_");
            orgin_price = onprices[0];
            now_price = onprices[1];
            storenum = onprices[2];
            need_score = onprices[3];
            cost_price = onprices[4];
            foreign_mark = onprices[5];
            unit = onprices[6];
            weight = onprices[7];
            for_price = onprices[8];
        }
        if(pid!=""){
            str += '<tr class="pos_ul" pos="'+pid+'">';
            str += '<td>'+text+'</td>';
            str += '<td class="orgin_price"><?php if(OOF_P != 2) echo OOF_S ?><input type=\"text\" name=\"pro_orgin_price\" value=\"'+orgin_price+'\" class=\"form_input num_check \" size=\"5\" maxlength=\"10\"><?php if(OOF_P == 2) echo OOF_S ?></td>';
            str += '<td class="now_price"><?php if(OOF_P != 2) echo OOF_S ?><input type=\"text\" name=\"pro_now_price\" value=\"'+now_price+'\" class=\"form_input num_check calc_np\" size=\"5\" maxlength=\"10\"><?php if(OOF_P == 2) echo OOF_S ?></td>';
            str += '<td class="for_price"><?php if(OOF_P != 2) echo OOF_S ?><input type=\"text\" name=\"pro_for_price\" value=\"'+for_price+'\" class=\"form_input num_check calc_fp\" size=\"5\" maxlength=\"10\"><?php if(OOF_P == 2) echo OOF_S ?></td>';
            str += '<td class="base_price"><?php if(OOF_P != 2) echo OOF_S ?><input type=\"text\" name=\"pro_cost_price\" value=\"'+cost_price+'\" class=\"form_input num_check calc_bp\" size=\"5\" maxlength=\"10\"><?php if(OOF_P == 2) echo OOF_S ?></td>';
            //str += '<td><input type=\"text\" name=\"pro_unit\" value=\"'+unit+'\" class=\"form_input\" size=\"5\" maxlength=\"10\"></td>';
            str += '<td><input type=\"text\" name=\"pro_weight\" value=\"'+weight+'\" class=\"form_input num_check \" size=\"5\" maxlength=\"10\"></td>';
            str += '<td class="neet_score"><input type=\"text\" name=\"pro_need_score\" value=\"'+need_score+'\" class=\"form_input num_check\" size=\"5\" maxlength=\"10\"></td>';
            str += '<td class="store_num"><input type=\"text\" name=\"pro_storenum\" value=\"'+storenum+'\" class=\"form_input num_check\" size=\"5\" maxlength=\"10\"></td>';
            str += '<td><input type=\"text\" name=\"pro_foreign_mark\" value=\"'+foreign_mark+'\" class=\"form_input\" size=\"5\" maxlength=\"50\"></td>';
            var reward = 0;
            if(pro_reward !="" && (parseFloat(pro_reward) >= -1 && parseFloat(pro_reward) <=1 ) &&  //佣金比
                now_price != "" && parseFloat(now_price) > 0 && //现价
                cost_price != "" && parseFloat(cost_price) >= 0 && for_price != "" && parseFloat(for_price) >= 0){ //成本
                reward = calcReward(now_price,for_price,cost_price,pro_reward);
                //console.log("now_price : "+now_price+" ; cost_price : "+cost_price+" ; profit : "+profit+" ; reward : "+reward);
            }
            str += "<td class='show_price'>"+(reward > 0 ? "<?php if(OOF_P != 2) echo OOF_S ?>"+reward+"<?php if(OOF_P == 2) echo OOF_S ?>" : "<?php if(OOF_P != 2) echo OOF_S ?>0<?php if(OOF_P == 2) echo OOF_S ?>" )+"</td>";
            str += '<td class="operation"><div <?php if($skuid < 0 || empty($skuid)){ echo 'class="del"';} ?> >删除</div><div class="copy">复制到</div><input type=hidden name=\"proids\" value=\"'+pid+'\" /></td>';
            str += '</tr>';

            // str = str + '<ul class="WSY_bulkul01 pos_ul" pos="'+pid+'">';
            // str = str + '<li class="WSY_bulkuli_red"></li>';
            // str = str +" <li class='orgin_price'>原价:<?php if(OOF_P != 2) echo OOF_S ?><input type=\"text\" name=\"pro_orgin_price\" value=\""+orgin_price+"\" class=\"form_input num_check \" size=\"5\" maxlength=\"10\"><?php if(OOF_P == 2) echo OOF_S ?></li>";
            // str = str +" <li class='now_price'><?php if($nowprice_title){echo $nowprice_title;}else if($base_nowprice_title){echo $base_nowprice_title;}else{echo "现价";}?>:<?php if(OOF_P != 2) echo OOF_S ?><input type=\"text\" name=\"pro_now_price\" value=\""+now_price+"\" class=\"form_input num_check calc_np\" size=\"5\" maxlength=\"10\"><?php if(OOF_P == 2) echo OOF_S ?></li>";
            // str = str +" <li class='for_price'>成本:<?php if(OOF_P != 2) echo OOF_S ?><input type=\"text\" name=\"pro_for_price\" value=\""+for_price+"\" class=\"form_input num_check calc_fp\" size=\"5\" maxlength=\"10\"><?php if(OOF_P == 2) echo OOF_S ?></li>";
            // str = str +" <li class='base_price'>供货价:<?php if(OOF_P != 2) echo OOF_S ?><input type=\"text\" name=\"pro_cost_price\" value=\""+cost_price+"\" class=\"form_input num_check calc_bp\" size=\"5\" maxlength=\"10\"><?php if(OOF_P == 2) echo OOF_S ?></li>";
            // str = str +" <li>单位:<input type=\"text\" name=\"pro_unit\" value=\""+unit+"\" class=\"form_input\" size=\"5\" maxlength=\"10\"></li>";
            // str = str +" <li>重量:<input type=\"text\" name=\"pro_weight\" value=\""+weight+"\" class=\"form_input num_check \" size=\"5\" maxlength=\"10\">KG</li>";
            // str = str +" <li class='neet_score'>所需积分: <input type=\"text\" name=\"pro_need_score\" value=\""+need_score+"\" class=\"form_input num_check\" size=\"5\" maxlength=\"10\"></li>";
            // str = str +" <li class='store_num'>库存: <input type=\"text\" name=\"pro_storenum\" value=\""+storenum+"\" class=\"form_input num_check\" size=\"5\" maxlength=\"10\"></li>";
            // str = str +" <li>外部标识: <input type=\"text\" name=\"pro_foreign_mark\" value=\""+foreign_mark+"\" class=\"form_input\" size=\"5\" maxlength=\"50\"></li>";
            var reward = 0;
            if(pro_reward !="" && (parseFloat(pro_reward) >= -1 && parseFloat(pro_reward) <=1 ) &&  //佣金比
                now_price != "" && parseFloat(now_price) > 0 && //现价
                cost_price != "" && parseFloat(cost_price) >= 0 && for_price != "" && parseFloat(for_price) >= 0){ //成本
                reward = calcReward(now_price,for_price,cost_price,pro_reward);
                //console.log("now_price : "+now_price+" ; cost_price : "+cost_price+" ; profit : "+profit+" ; reward : "+reward);
            }
            // str = str +" <li class='show_price'>"+(reward > 0 ? "（总返佣金额：<?php if(OOF_P != 2) echo OOF_S ?>"+reward+"<?php if(OOF_P == 2) echo OOF_S ?>）" : "" )+"</li>";
            // str = str +" <li class='del'>删除</li>";
            // str = str +" <li class='copy'>复制到</li>";
            // str = str +" <input type=hidden name=\"proids\" value=\""+pid+"\" />";
            // str = str +"</ul>";
        }else{
            str += '<tr  class="pos_ul" pos="'+pid+'">';
            str += '<td>'+text+'</td>';
            str += '<td class="orgin_price"><?php if(OOF_P != 2) echo OOF_S ?><input type=\"text\" name=\"pro_orgin_price\" value=\"'+orgin_price+'\" class=\"form_input num_check \" size=\"5\" maxlength=\"10\"><?php if(OOF_P == 2) echo OOF_S ?></td>';
            str += '<td class="now_price"><?php if(OOF_P != 2) echo OOF_S ?><input type=\"text\" name=\"pro_now_price\" value=\"'+now_price+'\" class=\"form_input num_check calc_np\" size=\"5\" maxlength=\"10\"><?php if(OOF_P == 2) echo OOF_S ?></td>';
            str += '<td class="for_price"><?php if(OOF_P != 2) echo OOF_S ?><input type=\"text\" name=\"pro_for_price\" value=\"'+for_price+'\" class=\"form_input num_check calc_fp\" size=\"5\" maxlength=\"10\"><?php if(OOF_P == 2) echo OOF_S ?></td>';
            str += '<td class="base_price"><?php if(OOF_P != 2) echo OOF_S ?><input type=\"text\" name=\"pro_cost_price\" value=\"'+cost_price+'\" class=\"form_input num_check calc_bp\" size=\"5\" maxlength=\"10\"><?php if(OOF_P == 2) echo OOF_S ?></td>';
            //str += '<td><input type=\"text\" name=\"pro_unit\" value=\"'+unit+'\" class=\"form_input\" size=\"5\" maxlength=\"10\"></td>';
            str += '<td><input type=\"text\" name=\"pro_weight\" value=\"'+weight+'\" class=\"form_input num_check \" size=\"5\" maxlength=\"10\"></td>';
            str += '<td class="neet_score"><input type=\"text\" name=\"pro_need_score\" value=\"'+need_score+'\" class=\"form_input num_check\" size=\"5\" maxlength=\"10\"></td>';
            str += '<td class="store_num"><input type=\"text\" name=\"pro_storenum\" value=\"'+storenum+'\" class=\"form_input num_check\" size=\"5\" maxlength=\"10\"></td>';
            str += '<td><input type=\"text\" name=\"pro_foreign_mark\" value=\"'+foreign_mark+'\" class=\"form_input\" size=\"5\" maxlength=\"50\"></td>';
            str += '<td class="operation"><div <?php if($skuid < 0 || empty($skuid)){ echo 'class="del"';} ?> >删除</div><div class="copy">复制到</div><input type=hidden name=\"proids\" value=\"'+pid+'\" /></td>';
            str += '</tr>';

            // str = str + "<div class='WSY_bulkul01 main_form'>";
            // str = str + '<span class="WSY_red">现价和成本一致,则不返佣</span><br>';
            // str = str +"  <li class='orgin_price'>原价:<?php if(OOF_P != 2) echo OOF_S ?><input type=\"text\" name=\"orgin_price\" value=\"<?php echo $product_orgin_price; ?>\" class=\"form_input num_check\" size=\"5\" maxlength=\"10\"><?php if(OOF_P == 2) echo OOF_S ?> </li>";
            // str = str +" <li class='now_price'> <?php if($nowprice_title){echo $nowprice_title;}else if($base_nowprice_title){echo $base_nowprice_title;}else{echo "现价";}?>:<?php if(OOF_P != 2) echo OOF_S ?><input type=\"text\" name=\"now_price\" value=\"<?php echo $product_now_price; ?>\" class=\"form_input num_check calc_np\" size=\"5\" maxlength=\"10\"><?php if(OOF_P == 2) echo OOF_S ?> </li>";
            // str = str +" <li class='for_price' > 成本:<?php if(OOF_P != 2) echo OOF_S ?><input type=\"text\" name=\"for_price\" value=\"<?php echo $product_for_price; ?>\" class=\"form_input num_check calc_fp\" size=\"5\" maxlength=\"10\"><?php if(OOF_P == 2) echo OOF_S ?> </li>";
            // str = str +" <li class='base_price'>供货价:<?php if(OOF_P != 2) echo OOF_S ?><input type=\"text\" name=\"cost_price\" value=\"<?php echo $product_cost_price; ?>\" class=\"form_input num_check calc_bp\" size=\"5\" maxlength=\"10\"><?php if(OOF_P == 2) echo OOF_S ?></li>";
            // str = str +" <li> 单位:<input type=\"text\" name=\"unit\" value=\"<?php echo $product_unit; ?>\" class=\"form_input\" size=\"5\" maxlength=\"10\"> </li>";
            // str = str +" <li> 重量:<input type=\"text\" name=\"weight\" value=\"<?php echo $product_weight; ?>\" class=\"form_input num_check \" size=\"5\" maxlength=\"10\">KG</li>";
            // str = str +" <li class='neet_score'> 所需积分: <input type=\"text\" name=\"need_score\" value=\"<?php echo $product_need_score; ?>\" class=\"form_input num_check\" size=\"5\" maxlength=\"10\"> </li>";
            // str = str +" <li class='store_num'> 库存: <input type=\"text\" name=\"storenum\" value=\"<?php echo $product_storenum; ?>\" class=\"form_input num_check\" size=\"5\" maxlength=\"10\"> </li>";
            // str = str +" <li> 外部标识: <input type=\"text\" name=\"foreign_mark\" value=\"<?php echo $product_foreign_mark; ?>\" class=\"form_input\" size=\"5\" maxlength=\"50\"></li>";
            // str = str +"<li class='all_do_prompt' style='color:blue;display:none;'>全部设置</li>";

            var reward = 0;
            var now_price = '<?php echo $product_now_price; ?>';
            var cost_price = '<?php echo $product_cost_price; ?>';
            var for_price = '<?php echo $product_for_price; ?>';
            if(pro_reward !="" && (parseFloat(pro_reward) >=-1 && parseFloat(pro_reward) <= 1 ) &&  //佣金比
                now_price != "" && parseFloat(now_price) > 0 && //现价
                cost_price != "" && parseFloat(cost_price) >= 0 ){ //成本
                reward = calcReward(now_price,for_price,cost_price,pro_reward);
                //console.log("now_price : "+now_price+" ; cost_price : "+cost_price+" ; profit : "+profit+" ; reward : "+reward);
            }
            $('#product-input-ex').children().remove();
            $('#product-input-ex').append("<dd class='show_price'>"+(reward > 0 ? "（总返佣金额：<?php if(OOF_P != 2) echo OOF_S ?>"+reward+"<?php if(OOF_P == 2) echo OOF_S ?>）" : "（总返佣金额：<?php if(OOF_P != 2) echo OOF_S ?>0<?php if(OOF_P == 2) echo OOF_S ?>）" )+"</dd>");
            // str = str +"</div>";
            // str = str + '<div class="WSY_bulkul02box" style="width:95%">';
            // str = str + '<div class="WSY_bulkul02">';
            // str = str + '<span class="WSY_red">属性价格</span>';
        }
        return str;
    }
    //now_price:现价
    //for_price:成本价
    //base_price:供货价
    //rate:分佣比例
    function calcReward(now_price,for_price,base_price,rate){
        //最大分佣
        //var profit= now_price - base_price;
        var profit  = sub(now_price,base_price)* 100;
        var profit2 = sub(now_price,for_price);
        //var reward= (now_price-for_price)* parseFloat(rate);
        var reward  = profit2* parseFloat(rate* 1000/10);  //0.57 0.58 *100 会出现精度问题
        reward = reward > profit ? profit : reward;
        reward = reward/100;
        reward = reward.toFixed(2);
        //reward = Math.floor(reward)/100;       //截取2位小数*/
        return reward;
    }

    //JS处置加减乘除误差较大
    function sub(arg1, arg2) {
        var r1, r2, m, n;
        try { r1 = arg1.toString().split('.')[1].length } catch (e) { r1 = 0 }
        try { r2 = arg2.toString().split('.')[1].length } catch (e) { r2 = 0 }
        m = Math.pow(10, Math.max(r1, r2));
        //动态控制精度长度
        n = (r1 >= r2) ? r1 : r2;
        return parseFloat(((arg1 * m - arg2 * m) / m).toFixed(n));
    }

    $(document).ready(function() {
        //var first_division = 0;
        //var init_reward_1 = <?php echo $init_reward_1;?>;
        $("input[name='define_share_image_flag']").click(function() {
            var $selectedvalue = this.value;
            if ($selectedvalue == 1) {
                $("#define_share_image_div").show();
            }
            else {
                $("#define_share_image_div").hide();
            }
        });
        $("#product-tab-ex").on("blur",".product-input-box",function(){
            var rate = $("#pro_reward").val();
            //如果产品奖励比例,则使用总奖励比例
            if(rate == -1){
                rate = init_reward;
            }
            if(isNaN(rate) && (parseFloat(rate) <= -1 || parseFloat(rate) >= 1)){
                alert("请输入正确的佣金比！");
                return;
            }
            //crm14366 bp使用pro_cost_price 查找为undefined
            var np = $("input[name='now_price']").val();
            var bp = $("input[name='cost_price']").val();
            var fp = $("input[name='for_price']").val();
            if(np != "" && bp != "" && fp != ""){
              np = parseFloat(np);
              bp = parseFloat(bp);
              fp = parseFloat(fp);
              reward = calcReward(np,fp,bp,rate);
              // $(this).text("<?php if(OOF_P != 2) echo OOF_S ?>"+reward+"<?php if(OOF_P == 2) echo OOF_S ?>");
              $('#product-input-ex').children().remove();
              $('#product-input-ex').append("<dd class='show_price'>（总返佣金额：<?php if(OOF_P != 2) echo OOF_S ?>"+reward+"<?php if(OOF_P == 2) echo OOF_S ?>）</dd>");
            }
        });
        $("#pro_reward").on("blur",function(){
            var rate = $(this).val();
            //如果产品奖励比例,则使用总奖励比例
            if(rate == -1){
                rate = init_reward;
            }
            if(isNaN(rate) && (parseFloat(rate) <= -1 || parseFloat(rate) >= 1)){
                alert("请输入正确的佣金比！");
                return;
            }
            var all_show = $(".show_price");
            var i = 1;
            all_show.each(function(){
                var np = $(this).siblings(".now_price").find("input").val();
                var bp = $(this).siblings(".base_price").find("input").val();
                var fp = $(this).siblings(".for_price").find("input").val();
                if(np != "" && bp != "" && fp != ""){
                    np = parseFloat(np);
                    bp = parseFloat(bp);
                    fp = parseFloat(fp);
                    reward = calcReward(np,fp,bp,rate);
                    $(this).text("<?php if(OOF_P != 2) echo OOF_S ?>"+reward+"<?php if(OOF_P == 2) echo OOF_S ?>");
                    /*if(1==i){
                        first_division = parseFloat(init_reward_1 * reward * promoter_reward,2).toFixed(2);
                        if(first_division<0){
                            first_division = 0;
                        }
                        $('#first_division').val(first_division);
                    }*/
                    i++;
                }
            });
        });
        $("#div_proprices").on("blur",".calc_bp",function(){
            //console.log("成本 - blur ");
            var rate = $("#pro_reward").val();
            //如果产品奖励比例,则使用总奖励比例
            if(rate == -1){
                rate = init_reward;
            }
            if(!isNaN(rate) && parseFloat(rate) >= -1){
                var np = $(this).parent().siblings(".now_price").find("input").val();
                var bp = $(this).val();
                var fp = $(this).parent().siblings(".for_price").find("input").val();
                if(np != "" && bp != "" && fp != ""){
                    np = parseFloat(np);
                    bp = parseFloat(bp);
                    fp = parseFloat(fp);
                    reward = calcReward(np,fp,bp,rate);
                    $(this).parent().siblings(".show_price").text("<?php if(OOF_P != 2) echo OOF_S ?>"+reward+"<?php if(OOF_P == 2) echo OOF_S ?>");
                    /*
                    first_division = parseFloat(init_reward_1 * reward * promoter_reward,2).toFixed(2);
                    if(first_division<0){
                        first_division = 0;
                    }
                    $('#first_division').val(first_division);*/
                }
            }
        });
        $("#div_proprices").on("blur",".calc_np",function(){
            //console.log("现价 - blur ");
            var rate = $("#pro_reward").val();
            //如果产品奖励比例,则使用总奖励比例
            if(rate == -1){
                rate = init_reward;
            }
            if(!isNaN(rate) && parseFloat(rate) >= -1){
                var np = $(this).val();
                var bp = $(this).parent().siblings(".base_price").find("input").val();
                var fp = $(this).parent().siblings(".for_price").find("input").val();
                if(np != "" && bp != "" && fp != ""){
                    np = parseFloat(np);
                    bp = parseFloat(bp);
                    fp = parseFloat(fp);
                    reward = calcReward(np,fp,bp,rate);
                    $(this).parent().siblings(".show_price").text("<?php if(OOF_P != 2) echo OOF_S ?>"+reward+"<?php if(OOF_P == 2) echo OOF_S ?>");
                    /*
                    first_division = parseFloat(init_reward_1 * reward * promoter_reward,2).toFixed(2);
                    if(first_division<0){
                        first_division = 0;
                    }
                    $('#first_division').val(first_division);*/
                }
            }
        });
        $("#div_proprices").on("blur",".calc_fp",function(){
            //console.log("现价 - blur ");
            var rate = $("#pro_reward").val();
            //如果产品奖励比例,则使用总奖励比例
            if(rate == -1){
                rate = init_reward;
            }
            if(!isNaN(rate) && parseFloat(rate) >= -1){
                var np = $(this).parent().siblings(".now_price").find("input").val();
                var bp = $(this).parent().siblings(".base_price").find("input").val();//
                var fp = $(this).val();
                if(np != "" && bp != "" && fp != ""){
                    np = parseFloat(np);
                    bp = parseFloat(bp);
                    fp = parseFloat(fp);
                    reward = calcReward(np,fp,bp,rate);
                    $(this).parent().siblings(".show_price").text("<?php if(OOF_P != 2) echo OOF_S ?>"+reward+"<?php if(OOF_P == 2) echo OOF_S ?>");
                    /*
                    first_division = parseFloat(init_reward_1 * reward * promoter_reward,2).toFixed(2);
                    if(first_division<0){
                        first_division = 0;
                    }
                    $('#first_division').val(first_division);*/
                }
            }
        });
        $("#div_proprices").on("click",".del",function(){
        	if(ordering_retail>0){
        		var that=$(this);
//        		layer.confirm('该属性组合涉及订货系统的销货、代发功能，是否删除', {
//				title:'提示',
//				btn: ['确认','取消']
//				}, function(confirm){
//					layer.close(confirm);
                is_del_pro = 1;
				that.parents('tr').remove();
//				}, function(){
//				});
        	}else{
        		$(this).parents('tr').remove();
        	}
        });

        /*复制属性信息开始*/
        $("#div_proprices").on("click",".copy",function(){
            var str = '<li class="li_pos"><input type="radio" name="pos" value="0" checked="checked"><label>所有产品</label></li>';
            $(".prompt_cont ul").html("");
            var pos = $(this).parents('tr').attr("pos");
            var strs= new Array(); //定义一数组
            strs=pos.split("_"); //字符分割
            for ( var i=0;i<strs.length ;i++ ){
                var obj=$("input[data_pid='"+strs[i]+"']");
                var pos_name = obj.parent().parent().parent().parent().find("span").eq(0).text();
                var val = i+1;
                str += '<li class="li_pos"><input type="radio" name="pos" value="'+val+'"><label>相同'+pos_name+'</label></li>';
            }
            $(".prompt_cont ul").append(str);
            var ttop = event.pageY;
            $(".do_prompt").attr("pos",pos);
            $(".prompt").css("top",ttop+"px");
            $(".prompt").show();
        });
        /*复制属性信息结束*/
        /*全部设置属性开始*/
        $("#div_proprices").on("click",".all_do_prompt",function(){
            var ttop = event.pageY;
            $(".all_prompt").css("top",ttop+"px");
            $(".all_prompt").show();
        });
        /*全部设置属性结束*/
    });

    function setParentDefaultimgurl(default_imgurl){
        document.getElementById("default_imgurl").value=default_imgurl;
    }
    function setParentClassDefaultimgurl(class_imgurl){
        document.getElementById("class_imgurl").value=class_imgurl;
    }

    //显示或隐藏分类关联的属性
    function add_relation_pros(relation_type_id,obj){
        var new_list = $('#type_'+relation_type_id);
        var check = new_list.attr('checked');
        if(check == 'checked'){             //选中才添加属性
            $.ajax({
                type: "post",
                url: "product_data.php?op=check_pros_extends&customer_id=<?php echo $customer_id_en;?>",
                async: true,
                data: { data: relation_type_id},
                success: function (result) {
                    //alert(result);
                    var result = eval('(' + result + ')');
                    //console.log(result);
                    if(result.code==10008){
                        $.each(result.data,function(i,val){
                            //console.log(val);
                            var pro_parent_id = val['pro_id'];
                            var pro_parent_name = val['pros_name'];
                            var pro_dd = $('.wdw>dd');      //获取所有的属性dd
                            var is_had = 0;
                            //判断页面是否已经存在该属性
                            $.each(pro_dd,function(){
                                var dd_pro_parent_id = $(this).attr('pro_parent_id');
                                if(dd_pro_parent_id == pro_parent_id){  //查找到+1
                                    is_had++;
                                }
                            });
                            if(is_had ==0){     //当已经存在该属性则不需要加载
                                var html = '<dd class="add_relation_pros_'+relation_type_id+'" pro_parent_id="'+pro_parent_id+'"><div class="WSY_cloropbox" >';
                                    html     +='<span id="parent_name_'+pro_parent_id+'">'+pro_parent_name+'</span><input type="hidden" name="hidden_parent" value="'+pro_parent_id+'"><div class="WSY_clorop">';
                                var html2 = '';
                                for(var i=0;i<val['pros_child_data'].length;i++){
                                    var pro_id = val['pros_child_data'][i][0];
                                    var pro_name = val['pros_child_data'][i][1];
                                    html2 += '<p><input type="checkbox" data_name="prop_'+pro_parent_id+'" data_pid="'+pro_id+'" data_text="'+pro_name+'" data_parent="'+pro_parent_id+'" value="'+pro_id+'" name="ptids" onclick="chkPro(this);">  '+pro_name+'<input type="hidden" id="'+pro_id+'" value="'+pro_name+'"></p>';
                                }
                                    html        +=  html2   +'</div></div></dd>';
                                //console.log(html);
                                $('.wdw').append(html);
                            }
                        });
                    }
                }
            })
        }else{//清除新增加的属性，本身就有则不清除
            $.ajax({
                type: "post",
                url: "product_data.php?op=check_pros_extends&customer_id=<?php echo $customer_id_en;?>",
                async: false,
                data: { data: relation_type_id},
                success: function (result) {
                    var result = eval('(' + result + ')');
                    //console.log(result);
                    if(result.code==10008){
                        var is_checked = 0; //检查是否属性被选中
                        $.each(result.data,function(i,val){
                            //console.log(val);
                            for(var i=0;i<val['pros_child_data'].length;i++){
                                //console.log(val['pros_child_data']);
                                var pro_id = val['pros_child_data'][i][0];
                                var pro_name = val['pros_child_data'][i][1]
                                var input = $('.WSY_clorop input[value='+pro_id+']');
                                var check = input.attr('checked');
                                var WSY_clorop = input.parents('.WSY_clorop');
                                if(check == 'checked'){
                                    WSY_clorop.addClass('red_border');
                                    is_checked++;
                                }
                            }
                        });
                        if(is_checked){     //选中后无法删除
                            alert('删除的分类中含有关联的属性，请取消后再删除分类！');
                            is_del = false;
                        }else{
                            $('.add_relation_pros_'+relation_type_id).remove();
                            is_del = true;
                        }
                    }
                }
            })
        }
    }

    var is_del = true;      //是否可以删除分类 ：true 可以 ；false否
    /* 产品分类 start*/
    $(function(){
        var msg=$(".msg");
        //点击下拉
        $(".slide_btn").click(function(){
            if(msg.is(":visible")){
                msg.fadeOut(500);
                $(this).hide();
                $(".slide_btn_grey").show();
            }else{
                msg.fadeIn(500);
            }
        });
        $(".slide_btn_grey").click(function(){
            if(msg.is(":visible")){
                msg.fadeOut(500);
            }else{
                msg.fadeIn(500);
                $(this).hide();
                $(".slide_btn").show();
            }
        })
        $(".msg").on("click",".option_box .type_next", function(){
            var level_str   = "";
            var html        = "";
            var type_id     = $(this).attr('typeid');
            var level       = parseInt($(this).attr('level'));
            var up_name     = $(this).attr('type_name');
            switch( level ){
                case 1:
                    level_str = "二级";
                    $(".box_2").remove();
                    $(".box_3").remove();
                    $(".box_4").remove();
                    break;
                case 2:
                    level_str = "三级";
                    $(".box_3").remove();
                    $(".box_4").remove();
                    //alert('三级和四级关联属性已失效');
                    break;
                case 3:
                    level_str = "四级";
                    $(".box_4").remove();
                    //alert('三级和四级关联属性已失效');
                    break;
                default:
                    level_str = "二级";
                    break;
            }
            level++;
            $.ajax({
                type: "post",
                url: "get_next_class.php?customer_id=<?php echo $customer_id_en;?>",
                async: false,
                data: { type_id: type_id,level:level},
                success: function (result) {
                    var obj     = eval('('+result+')');
                    var obj_len = obj.length;
                    html    += '<div class="option_box box_'+level+'">';
                    html    += '<p>'+level_str+'</p>    ';
                    html    += '<div class="option">';
                    html    += '<ul class="level_1" >';
                    for(var i = 0; i < obj_len; i++){
                        var next_num = obj[i][3];//下级数量
                        var obj_id = obj[i][1];//分类id
                        var obj_name = obj[i][2];//分类名
                        var is_privilege = obj[i][4];//是否特权
                        html += '<li type_name="'+up_name+">"+obj_name+'" level="'+level+'" typeid="'+obj_id+'"';
                        if( next_num > 0 ){
                            html += ' class="type_next"';
                        }
                        html += '>';
                        //if(is_privilege==1){
                            html += '<img scr="../../../mshop/images/special.png" style="width: 30px;margin-top: 5px;float: left;">';
                        //}
                        html += '<span class="text" >'+obj_name+'</span>';
                        if( next_num > 0 ){
                           html += '<img class="right" src="../../Common/images/Product/gray_right.png"> ';
                        }else{
                            html += '<i class="add-icon"><span   data-type_id="'+obj_id+'" class="add-list" type_name="'+up_name+">"+obj_name+'" >+</span> </i>';
                        }
                        html += '</li>';
                    }
                    html    += '</ul>';
                    html    += '</div>';
                    html    += '</div>';
                    $(".msg").append(html);
                }
            })
        });

        //添加分类
        var all_array = '<?php echo $type_ids?>';//储存已选选项
        //如果没有数据，则添加一个，
        if(all_array.length == 0)
        {
            all_array +=',';
        }
        $(".msg").on("click",".option_box .add-list", function(){
            var type_name = $(this).attr('type_name');
            var type_id=$(this).attr('data-type_id');
            //alert(all_array);
            if( all_array.indexOf(","+type_id) == -1 ){
                var html='<div class="new_list" id="type_'+type_id+'" data-type_id="'+type_id+'" checked="checked"><p>'+type_name+'</p><span class="del_type" data-type_id="'+type_id+'">x</span></div>';
                $('#f-box').append(html);//输出所选项
                all_array += type_id+",";
                //console.log(all_array);
                add_relation_pros(type_id,'#type_'+type_id);    //显示关联属性
            }else{
                alert('已存在分类');
            }
        });
        //删除分类
        $("#f-box").on("click",".new_list .del_type", function(){
        //$('body').on('click','.del',function(){
            var type_id = $(this).attr('data-type_id');
            all_array = all_array.replace(","+type_id+",",",");
            $(this).parent('.new_list').removeAttr('checked');
            console.log(all_array);
            add_relation_pros(type_id,'#type_'+type_id);    //去掉关联属性
            if(is_del){
                 $(this).parent('.new_list').remove();
            }
        });

    });
    /* 产品分类 end*/

    //中文，英文，多小数点过滤
    /* function clearNoNum(obj){
        obj.value = obj.value.replace(/[^\d.]/g,""); //清除"数字"和"."以外的字符
        obj.value = obj.value.replace(/^\./g,""); //验证第一个字符是数字而不是
        obj.value = obj.value.replace(/\.{2,}/g,"."); //只保留第一个. 清除多余的
        obj.value = obj.value.replace(".","$#$").replace(/\./g,"").replace("$#$",".");
        obj.value = obj.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3'); //只能输入两个小数
    } */

    //中文，英文，多小数点过滤
     function clearNoNumNew(obj){
         //要输入负数，所以屏蔽前两个
        //obj.value = obj.value.replace(/[^\d.]/g,""); //清除"数字"和"."以外的字符
        //obj.value = obj.value.replace(/^\./g,""); //验证第一个字符是数字而不是
        obj.value = obj.value.replace(/\.{2,}/g,"."); //只保留第一个. 清除多余的
        obj.value = obj.value.replace(".","$#$").replace(/\./g,"").replace("$#$",".");
        obj.value = obj.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3'); //只能输入两个小数
    }

    //过滤单引号和双引号
    function checkname(obj){
        obj.value = obj.value.replace(/['"’”‘“]/g,""); //清除"数字"和"."以外的字符
    }

    var mess_num  = <?php echo $mess_num;?>-1;//必填信息
    var supply_id = '<?php echo $supply_id;?>';//供应商ID
    var is_audit = '<?php echo $_REQUEST['is_audit']; ?>';        //是否是审核产品跳转过来，1是

    var last_save_arr="";
    var link_coupons = "<?php echo $link_coupons; ?>";
    if(link_coupons==-1){
        var link_coupons_arr=new Array();
    }else{
        var link_coupons_arr = link_coupons.split(',');
    }
    function add_value(obj){
        if($(obj).is(':checked')){
            if(link_coupons_arr.indexOf(obj.value)<1){
                link_coupons_arr.push(obj.value);
            }
        }else{
            if(link_coupons_arr.indexOf(obj.value)>-1){
                $.each(link_coupons_arr,function(index,item){
                // index是索引值（即下标）   item是每次遍历得到的值；
                    if(item==obj.value){
                        link_coupons_arr.splice(index,1);
                    }
                });
            }
        }
    }

    var page = 0;
    var is_lock = 0;
    var idsStr = "";
    function show_check_cous(){
        $(".coupons_list").show('slow');
        idsStr=link_coupons_arr.join(",");
        show_coupons(idsStr);
        $('.show_check').attr('checked',true);
    }

    $(".time_search").change(function(){
        if($(this).val()==1 || $(this).val()==2 || $(this).val()==3){
            $('.show_time_search').css('display','block');
        }else{
            $('.show_time_search').css('display','none');
            $('#cou_endtime').val('');
            $('#cou_starttime').val('');
        }
    });
    $("#check_all").click(function(){
        $("input[name='check_cous[]']").each(function(){
            if($('#check_all').attr("checked")){
                $(this).attr("checked","true");
                link_coupons_arr="<?php echo $all_coupons_id; ?>".split(',');
            }
            else{
                $(this).removeAttr("checked");
                link_coupons_arr=new Array();
            }
        })
    });

    $('#coupons_load').scroll(function(){//加载产品
        var $this =$(this),
        viewH =$(this).height(),//可见高度
        contentH =$(this).get(0).scrollHeight,//内容高度
        scrollTop =$(this).scrollTop();//滚动高度
        if(scrollTop/(contentH -viewH)>=0.98){ //到达底部100px时,加载新内容
            show_coupons(idsStr);
        }
    });

    function search_coupons(){
        var search_coupons_name = $('#search_cou_name').val();//搜索产品名字
        var user_scene = $('#user_scene').val();
        if($('.show_check').is(':checked')){
            if(link_coupons_arr.join(",")==""){
                idsStr=-1;
            }else{
                idsStr=link_coupons_arr.join(",");
            }
        }else{
                idsStr = "";
        }
        var cou_starttime=$('#cou_starttime').val();
        var cou_endtime=$('#cou_endtime').val();
        if(cou_endtime<cou_starttime){
        alert('开始时间必须大于结束时间');
        return;
        }
        page=0;
        show_coupons(idsStr);
        $("#check_all").removeAttr("checked");
    }

    function show_coupons(idsStr){
        var search_coupons_name = $('#search_cou_name').val();//搜索产品名字
        var user_scene= $('#user_scene').val();
        var cou_starttime=$('#cou_starttime').val();
        var cou_endtime=$('#cou_endtime').val();
        var time_search=$('.time_search').val();
        if(!is_lock){
            is_lock = 1;
            $.ajax({
                type: 'POST',
                url: './ajax_coupon_list.php?customer_id=<?php echo $customer_id_en; ?>',
                async:false,
                data:{
                    page:page,
                    search_cou_name : search_coupons_name,
                    user_scene:user_scene,
                    time_search:time_search,
                    cou_starttime:cou_starttime,
                    cou_endtime:cou_endtime,
                    product_id:'<?php echo $product_id;?>',
                    idsStr:idsStr
                },
                dataType: 'json',
                success: function(data){
                    if(page==0){
                        $('.pro_tr').remove();
                    }
                    var result = '';
                    var i = 0;
                    for(i=0;i<data.coupons.length;i++){
                        result+='<tr class="pro_tr">';
                        result+='<td align="center">'+data.coupons[i].coupons_id+'</td>';
                        result+='<td align="center">'+data.coupons[i].cou_title+'</td>';
                        result+='<td align="center">'+data.coupons[i].coupons_money+'</td>';
                        var set='<dt>'+data.coupons[i].user_scene_str+'</dt>';
                        if(data.coupons[i].p_name!=""){
                        set+='<dt>'+data.coupons[i].p_name+'</dt>';
                        }
                        result+='<td align="center">'+set+'</td>';
                        result+='<td align="center"><dt>创建时间：'+data.coupons[i].createtime+'</dt><dt>'+data.coupons[i].get_time+'</dt><dt>'+data.coupons[i].use_time+'</dt></td>';
                        result+='<td align="center">'+data.coupons[i].is_use+'</td>';
                        if(data.coupons[i].is_check==1){
                        if($.inArray(data.coupons[i].coupons_id,link_coupons_arr)!=-1){
                        result+='<td align="center" id=""><input type="checkbox" onchange="add_value(this)"  style="height:13px;" class="save_check" checked="checked" name="check_cous[]" value="'+data.coupons[i].coupons_id+'"   /></td>';
                        }else{
                        result+='<td align="center" id=""><input type="checkbox" onchange="add_value(this)" style="height:13px;"  name="check_cous[]" value="'+data.coupons[i].coupons_id+'"   /></td>';
                        }
                        }else{
                        if($.inArray(data.coupons[i].coupons_id,link_coupons_arr)!=-1){
                        result+='<td align="center" id=""><input type="checkbox" onchange="add_value(this)" class="save_check" style="height:13px;" checked="checked" name="check_cous[]" value="'+data.coupons[i].coupons_id+'"   /></td>';
                        }else{
                        result+='<td align="center" id=""><input type="checkbox" onchange="add_value(this)"  style="height:13px;" name="check_cous[]" value="'+data.coupons[i].coupons_id+'"   /></td>';
                        }
                        }
                        result+='</tr>';
                    }

                    $('.cou_table').append(result);
                    is_lock = 0;
                    page++;
                },
                error: function(type){
                    alert('Ajax error!');
                }
            });
        }
    }

    $("#cancel_list").click(function(){
        $("#link_coupons_save").val(last_save_arr);
        link_coupons_arr=last_save_arr.split(',');
        $("input[name='check_cous[]']").each(function(i){//将后来选中的取消保存
            if($(this).hasClass('save_check')||$(this).hasClass('save_check_1')){
                $(this).attr('checked','checked');
            }else{
                $(this).attr("checked", false);
            }
        });
        $(".coupons_list").hide('slow');
        $("#check_all").attr('checked',false);
    });

    $("#open_list").click(function(){
        for(var i=0;i<link_coupons_arr.length;i++){
          if(link_coupons_arr[i]==''){
            link_coupons_arr.splice(i,1);
          }
        }
        var check_cous=link_coupons_arr.length;
        $("input[name='check_cous[]']").each(function(i){//将后来选中的保存
            if($(this).attr('checked')){
                $(this).addClass('save_check');
            }else{
                $(this).removeClass('save_check');
            }
        });
        $(".cous_length").html(check_cous);
        $(".coupons_list").hide('slow');
        $("#coupons_count").css('display','block');
        $("#link_coupons_save").val(link_coupons_arr.join(','));
        last_save_arr=link_coupons_arr.join(',');
    });

    window.onload = function(){
        var if_edit = "<?php echo $product_id;?>";
        if(if_edit > 0)
        {
            $('.WSY_bulkul01').each(function(){
                var type_price=$(this).find('.calc_np').val();
                if(type_price.length == 0)
                {
                    $(this).remove();
                }
            });
        }
    }
    /*删除产品*/
    function del(obj){
        var pid = '<?php echo $_GET['product_id']; ?>';
        $.ajax({
            url: "./save_examine.php",
            type:"POST",
            data:{'pid':pid,'isvalid':0,'op':"del"},
            dataType:"json",
            success: function(res){
                if(res){
                    location.href="examine.php?status=0&customer_id="+customer_id_en;
                }
            }
        });
    }
    /*通过保存产品*/
    function offer_save(obj){
        //var pid = '<?php echo $_GET['product_id']; ?>';
        $("#offer_id").val(2);
        var result = saveProduct();//判断待审核产品是否符合上架要求
        /*if(result !== true){
        return;
        }*/
        /*$.ajax({
        url: "./save_examine.php",
        type:"POST",
        data:{'pid':pid,'status':1,'op':"offer"},
        dataType:"json",
        success: function(res){
          if(res){
              location.href="//"+document.domain+"/wsy_prod/admin/Product/product/examine.php?status=0&customer_id="+customer_id_en;
          }
        }
        });*/
    }
    /*保存上架产品*/
    function offer(obj){
        //var pid = '<?php echo $_GET['product_id']; ?>';
        $("#offer_id").val(1);
        var result = saveProduct();//判断待审核产品是否符合上架要求
        /*if(result !== true){
          return;
        }*/
        /*$.ajax({
          url: "./save_examine.php",
          type:"POST",
          data:{'pid':pid,'status':1,'op':"offer"},
          dataType:"json",
          success: function(res){
              if(res){
                  location.href="//"+document.domain+"/wsy_prod/admin/Product/product/examine.php?status=0&customer_id="+customer_id_en;
              }
          }
        });*/
    }
    // 驳回产品
    function refundProduct(obj){
        var pid = '<?php echo $_GET['product_id']; ?>';
        layer.confirm('您确定要驳回此产品吗？', {
            title:'驳回产品',
            btn: ['驳回','取消']
        },function(confirm){
            layer_open();
            layer.close(confirm);
            layer.prompt({
                formType: 0,
                title: '驳回维权备注',
                value: '商品信息不全'
            },function(reason, prompt, elem){
                layer.close(prompt);
                if(!reason || reason  == ""){
                    layer.alert("驳回请输入理由！");
                    return;
                }
                $.ajax({
                    url: "./save_examine.php",
                    type:"POST",
                    data:{'pid':pid,'reason':reason,'status':2,'op':"refundProduct"},
                    dataType:"json",
                    success: function(res){
                        layer.close(index_layer);
                        if(res){
                            location.href="examine.php?status=0&customer_id="+customer_id_en;
                        }
/*                        if(res.errcode>0){
                            layer.alert(res.errmsg);
                        }else{
                            layer.alert(res.msg);
                        }*/
                    },
                    error:function(){
                        layer.close(index_layer);
                        layer.alert("网络错误请检查网络");
                    }
                });
            });
        }, function(){
            layer.msg('已取消', {
                time: 2000,
                btn: ['确认'],
                icon:1
            });
        });
    }

    var index_layer;
    function layer_open(){
        index_layer= layer.load(0, {
            shade: [0.1,'#000'], //0.1透明度的白色背景
            content: '<div style="position:relative;top:30px;width:200px;color:red">数据处理中</div>'
        });
    }

    window.onload = function() {
        setTimeout("abc()", 500);

        chkPro();
        chkPro();
        // obj.prop('checked',true);

    };
    function abc(){
        var val = $('#description').text();
        // console.log(val);
        // chkPro();
        $('.cke_wysiwyg_frame').contents().find('body').html(val);
    }
    //3D素材
    $(function(){
        $("#frmProImgs").on("load", function(event){
            $("#test_threed",this.contentDocument).click(function(){
                show_three_d();
                var status=$(this).attr('status');
                var leg=$(this).attr('leg');
                if (leg >= 1) {
                    status = 0;
                    alert('只能上传一张3D素材');
                    return;
                }
                if(status!=0){
                    $('.mask_3d').show()
                } else {
                    alert('上传了图片不能上传3D素材');
                }
            });
        });
    });
    var threed_insert_ck=0;
    function show_threed(){
        threed_insert_ck=1;
        show_three_d();
        $('.mask_3d').show()
    }
    $('#3d_close').click(function () {
        threed_insert_ck=0;
        $('.mask_3d').hide()
    })
    function WSY_buttontj_tj(obj){
        if(threed_insert_ck){
            var newstr=$(obj).parent().parent().children(4).children("a").html().replace('http','https');
            $("iframe").contents().find("body").append("<div style='position:relative'><img src='https://admin.weisanyun.cn/mshop/admin/Common/images/Product/3d_logo.png' style='width: 30px;height: 30px;position: absolute;top: 0;left: 0;' class='threed_show' ><iframe src='"+newstr+"' height='500px' width='100%'></iframe></div>");
        }else{
            // var template_3d = $(obj).parent().prev().children('a').html();
            var id_3d = $(obj).parent().parent().children(0).html();
            $("#frmProImgs").attr('src','iframe_images.php?id_3d='+id_3d+'&customer_id=<?php echo $customer_id_en; ?>&product_id=<?php echo $product_id; ?>&detail_template_type=<?php echo $detail_template_type; ?>');
        }
        $('.mask_3d').hide();
    }
    
//过滤-
function checkmark(obj){
    obj.value = obj.value.replace(/\-/,""); //清除-
}     
</script>
<!-- <script type="text/javascript" src="../../Common/js/Product/product_common.js"></script> -->
<script type="text/javascript" src="../../Common/js/Product/product/product.js"></script>
</body>
</html>
