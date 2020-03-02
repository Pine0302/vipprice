<?php
require('../../../../wsy_prod/admin/Product/product/public/product_head.php');
// require($_SERVER['DOCUMENT_ROOT']."/wsy_prod/admin/Product/product/public/product_head.php"); 
header("Content-type: text/html; charset=utf-8");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>产品管理－出售中</title>
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content.css">
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content<?php echo $theme; ?>.css">
<link rel="stylesheet" type="text/css" href="../../Common/css/Product/product.css"><!--内容CSS配色·蓝色-->
<link rel="stylesheet" href="../../Common/js/percent/jquery.percentageloader.0.2.css">
<script type="text/javascript" src="../../../common/js_V6.0/assets/js/jquery.min.js"></script>
<script type="text/javascript" src="../../../common/js_V6.0/jscolor.js"></script><!--拾色器js-->
<script type="text/javascript" src="../../../js/WdatePicker.js"></script><!--添加时间插件-->
<style>
#li_sale input[type="checkbox"]{margin-top:-3px;}
.div_item{float:left;padding:10px;font-size:14px;}
.div_item label{margin-left:5px;font-size:14px;}
.div_item input{border:1px solid #ccc; border-radius: 2px;}
.layui-layer-content button{float: left;margin-top: 56px;margin-bottom: 19px;width: 80px;height: 30px;}
.xubox_title{background: none!important;}
.xubox_title em{left: 0!important;text-align: center!important;width: 100%!important;}
/*.privilege_list ul li{float: left;}
.privilege_list ul{float: left;}*/
/*<!--excel导出动画-->*/
#topLoader {width: 256px;height: 256px;margin-bottom: 32px;position:absolute;width:400px; left:50%; top:50%; margin-left:-200px; height:auto; z-index:100; padding:1px;}
#per_container {width: 500px;padding: 10px;margin-left: auto;margin-right: auto;}
#BgDiv{background-color:#e3e3e3; position:absolute; z-index:99; left:0; top:0; display:none; width:100%;height:1000px;opacity:0.5;filter: alpha(opacity=50);-moz-opacity: 0.5;}
#DialogDiv{position:absolute;width:400px; left:50%; top:50%; margin-left:-200px; height:auto; z-index:100;background-color:#fff; border:1px #8FA4F5 solid; padding:1px;}
/*<!--excel导出动画End-->*/
</style>
</head>

<body>
	<!--excel导出动画-->
	<div id="BgDiv"></div>
	<div id="per_container">
		<div style="display:none" id="topLoader"></div>
	</div>
	<!--excel导出动画 End-->
<?php
require('../../../../wsy_prod/admin/Product/product/public/product_type.php');
?>

<!--内容框架开始-->
<div class="WSY_content" id="WSY_content_height">

       <!--列表内容大框开始-->
	<div class="WSY_columnbox">
    	<?php require('../../../../wsy_prod/admin/Product/product/public/head.php');?>

	<?php

		$search_type = -1;
		if($search_type_id>0){
			$search_type = $search_type_id;
		}

	//	require('../../../../weixinpl/function_model/shop/product.php');
	//	$product = new Product();
		$condition = array();
		$fields =  "";
		$pagesize = 20;
		$pagenum = 1;

		if(!empty($_GET["pagenum"])){
		   $pagenum = $_GET["pagenum"];
		}

		$fields ="id,name,asort_value,type_id,type_ids,orgin_price,now_price,cost_price,need_score,default_imgurl,isnew,createtime,isout,ishot,issnapup,isvp,is_virtual,is_currency,is_guess_you_like,is_free_shipping,isscore,islimit,is_first_extend,good_level,meu_level,bad_level,is_supply_id,create_type,sell_count,is_QR,storenum,tax_type,extend_money,back_currency,vp_score,buystart_time,countdown_time,limit_num,privilege_level,is_privilege,link_package,ordering_retail,is_mini_mshop,nowprice_title";
		$condition['isvalid'] = '=true';
		$condition['customer_id'] = '='.$customer_id;
		$condition['isout'] = '=0';

		if($_SESSION['is_auth_user']=='yes' && $_SESSION['user_id']){
			$condition['auth_users'] = "(auth_users=".$auth_user_id." or is_supply_id>0)";
		}
		if($keyword!=""){
		   $condition['name'] = "like'%".$keyword."%'";
		}
		if($foreign_mark!=""){
		   $condition['foreign_mark'] = "like'%".$foreign_mark."%'";
		}
		if($search_type_id>0){


		    //$query3=$query3." AND type_id in (".$search_type.") ";
			$parent_id	=-1;
			$top_id		=-1;
			$level	= 0;
			$type_SQL="select parent_id,level,top_id from weixin_commonshop_types where id='".$search_type_id."'";
			$type_result = _mysql_query($type_SQL) or die('Query failed: ' . mysql_error());
			while ($type_row = mysql_fetch_object($type_result)) {
				$parent_id	= $type_row->parent_id;
				$level		= $type_row->level;
				$top_id		= $type_row->top_id;
			 }
			 $Str=" type_ids like '%,".$search_type.",%'";
			 if($parent_id>0){
				//$type_ID_SQL="select id from weixin_commonshop_types where parent_id='".$search_type_id."'";
				$type_ID_SQL="select id from weixin_commonshop_types where top_id='".$top_id."' and level>".$level." and gflag like '%,".$search_type_id.",%'" ;
				$type_ID_result = _mysql_query($type_ID_SQL) or die('Query failed: ' . mysql_error());
				while ($type_row = mysql_fetch_object($type_ID_result)) {
					$type_id=$type_row->id;
					//$Str=$Str."or type_ids like '%,".$type_id.",%' or type_ids like '".$type_id.",%' or type_ids like '%,".$type_id."'";
					$Str=$Str."or type_ids like '%,".$type_id.",%'";
				 }

			 }else{
			 	$type_ID_SQL="select id from weixin_commonshop_types where top_id='".$search_type_id."' and level>".$level ;
				$type_ID_result = _mysql_query($type_ID_SQL) or die('Query failed: ' . mysql_error());
				while ($type_row = mysql_fetch_object($type_ID_result)) {
					$type_id=$type_row->id;
					//$Str=$Str."or type_ids like '%,".$type_id.",%' or type_ids like '".$type_id.",%' or type_ids like '%,".$type_id."'";
					$Str=$Str."or type_ids like '%,".$type_id.",%'";
				 }
			 }
			$condition['type_ids'] = "(".$Str.")";


		}
		if($supply_id>0){
			$condition['is_supply_id'] = "=".$supply_id;
		}
		if($search_source > 0 && $supply_id==0){
			if($search_source == 1){//平台
				$condition['is_supply_id'] = "<0";
			}else if($search_source == 2){
				$condition['is_supply_id'] = ">0";
				if($search_supply > 0 ){
					$condition['is_supply_id'] = "=".$search_supply;
				}
			}
		}

		if($search_other_id>0){
		   switch($search_other_id){
		      case 1:
			    $condition['isout'] = "=true";
			    break;
			  case 2:
				$condition['isnew'] = "=true";
			    break;
			  case 3:
				$condition['ishot'] = "=true";
			    break;
			  case 4:
				$condition['isvp'] = "=true";
			    break;
			  case 5:
				$condition['issnapup'] = "=true";
			  break;
			  case 6:
				$condition['is_virtual'] = "=true";
			  break;
			  case 7:
				$condition['is_currency'] = "=true";
			  break;
			  case 8:
				$condition['is_guess_you_like'] = "=true";
			  break;
			  case 9:
				$condition['is_free_shipping'] = "=true";
			  break;
			  case 10:
				$condition['isscore'] = "=true";
			  break;
			  case 11:
				$condition['islimit'] = "=true";
			  break;
			  case 12:
				$condition['is_first_extend'] = "=true";
			  break;
			  case 13:
				$condition['is_privilege'] = "=1";
			  break;
			  case 14:
				$condition['link_package'] = ">0";
			  break;
			  case 15:
				$condition['is_mini_mshop'] = "=true";
			  break;
		   }
		}

		if($sales==1){
			$condition['order'] = ' order by sell_count desc,id desc ';
			$condition['limit'] = ' limit ';
		}else{
			$condition['order'] = ' order by asort_value desc,id desc ';
			$condition['limit'] = ' limit ';
		}

		$rcount_q2=1;
		//var_dump($condition);
		$product_list = $product->select_shop_product($fields,$condition,$pagesize,$pagenum);
	/* 	echo "<pre>";
		print_r($product_list);
		exit(); */
		$rcount_q2 = $product_list['count'];
		$page=ceil($rcount_q2/$pagesize);
	//	echo $page;

	//查询购物币开关
		$currency_sql    = "SELECT is_rebate_open FROM ".WSY_SHOP.".weixin_commonshop_currency where isvalid=true and customer_id='".$customer_id."'";
		$currency_result = _mysql_query($currency_sql) or die('Query failed 195: '.mysql_error());
		while ($row_currency = mysql_fetch_object($currency_result)) {
			$is_rebate_open  = $row_currency->is_rebate_open;
		}

		$currency_symbol_sql = "select currency_symbol,currency_text,symbol_position from weixin_currency_symbol_set where customer_id='".$customer_id."' limit 1";
		$currency_symbol_res = _mysql_query($currency_symbol_sql) or die("mysql failed:ji_sql-".mysql_error());
		while($row=mysql_fetch_object($currency_symbol_res))
		{
		    $currency_symbol = $row->currency_symbol;
		    $currency_text = $row->currency_text;
		    $symbol_position = $row->symbol_position;
		}
		//获取商城现价自定义
		$base_nowprice_title = "";
		$nowprice_title_sql = "select nowprice_title from weixin_commonshops where isvalid=true and customer_id='".$customer_id."'";
		$nowprice_title_result = _mysql_query($nowprice_title_sql) or die('Query failed 195: '.mysql_error());
		while ($row = mysql_fetch_object($nowprice_title_result)) {
			$base_nowprice_title  = $row->nowprice_title;
		}

	?>

    <!--产品管理代码开始-->
    <div class="WSY_data">
    	<div class="WSY_agentsbox">
        	<div class="WSY_agents WSY_agents001" style="display:none">
                 <li class="WSY_bottonli" id="WSY_bottonli">
                    <input type="button" value="批量删除">
                 </li>
			</div>
		<form class="search" action="sale.php?customer_id=<?php echo $customer_id_en; ?>">
			<div class="WSY_search_q">
			<div class="WSY_search_div">
                <li>关键词：<input type="text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"/></li>
                <li>外部标识：<input type="text" name="foreign_mark" id="foreign_mark" value="<?php echo $foreign_mark; ?>" /></li>
				<li>合作商ID：<input type="text" name="supply_id" id="supply_id" value="<?php echo $supply_id; ?>" /></li>
                <li>产品分类：
                                      <select name="search_type_id" id="search_type_id">
                        <option value="">--请选择--</option>
						<?php
							$parent_id = -1;
							$parent_name = ''; // 顶级分类
							$query = "SELECT id,name FROM weixin_commonshop_types WHERE isvalid=true AND customer_id=$customer_id AND parent_id=-1 AND is_shelves=1";
							$result= _mysql_query($query)or die('Query failed 145: ' . mysql_error());
							while( $row = mysql_fetch_object($result) ){
								$parent_id = $row->id;
								$parent_name = $row->name;
						?>
						<option value="<?php echo $parent_id;?>" <?php if($search_type_id == $parent_id){ echo 'selected';}?> ><?php echo $parent_name;?></option>
						 	<?php
						 		$ch_id2 = -1;
						 		$ch_name2 = '';// 第二级分类
						 		$query_c2 = "SELECT id,name FROM weixin_commonshop_types WHERE isvalid=true AND customer_id=$customer_id AND parent_id=$parent_id AND is_shelves=1";
						 		$result_c2= _mysql_query($query_c2)or die('Query failed 145: ' . mysql_error());
						 		while( $row_c2 = mysql_fetch_object($result_c2) ){
						 			$ch_id2 = $row_c2->id;
						 			$ch_name2 = $row_c2->name;
						 			if($ch_id2 != -1){

						 	?>
						 		<option value="<?php echo $ch_id2;?>" <?php if($search_type_id == $ch_id2){ echo 'selected';}?>><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;--&nbsp;&nbsp;'.$ch_name2;?></option>
						 			<?php
						 				$ch_id3 = -1;
						 				$ch_name3 = '';// 第三级分类
						 				$query_c3 = "SELECT id,name FROM weixin_commonshop_types WHERE isvalid=true AND customer_id=$customer_id AND parent_id=$ch_id2 AND is_shelves=1";
						 				$result_c3= _mysql_query($query_c3)or die('Query failed 167: ' . mysql_error());
						 				while( $row_c3 = mysql_fetch_object($result_c3) ){
						 					$ch_id3 = $row_c3->id;
						 					$ch_name3 = $row_c3->name;
						 			?>
						 			<option value="<?php echo $ch_id3;?>"  <?php if($search_type_id == $ch_id3){ echo 'selected';}?>><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;--&nbsp;&nbsp;'.$ch_name3;?></option>
							 			<?php
								 			$ch_id4 = -1;
							 				$ch_name4 = '';// 第四级分类
							 				$query_c4 = "SELECT id,name FROM weixin_commonshop_types WHERE isvalid=true AND customer_id=$customer_id AND parent_id=$ch_id3 AND is_shelves=1";
							 				$result_c4= _mysql_query($query_c4)or die('Query failed 167: ' . mysql_error());
							 				while( $row_c4 = mysql_fetch_object($result_c4) ){
							 					$ch_id4 = $row_c4->id;
							 					$ch_name4 = $row_c4->name;
							 			?>
							 			<option value="<?php echo $ch_id4;?>"  <?php if($search_type_id == $ch_id4){ echo 'selected';}?>><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;--&nbsp;&nbsp;'.$ch_name4;?></option>
							 			<?php }?>
						 			<?php }?>
						 		<?php }}?>
						<?php }?>
                    </select>
                </li>
                <li>产品标签：
                   <select name="search_other_id" id="search_other_id">
					<option value="-1">--请选择--</option>
					<option value="2" <?php if($search_other_id==2){?>selected <?php } ?>>新品</option>
					<option value="3" <?php if($search_other_id==3){?>selected <?php } ?>>热卖</option>
					<option value="4" <?php if($search_other_id==4){?>selected <?php } ?>>vp产品</option>
					<option value="5" <?php if($search_other_id==5){?>selected <?php } ?>>抢购</option>
					<option value="6" <?php if($search_other_id==6){?>selected <?php } ?>>虚拟产品</option>
					<option value="7" <?php if($search_other_id==7){?>selected <?php } ?>><?php echo defined('PAY_CURRENCY_NAME') ?PAY_CURRENCY_NAME: '购物币'; ?>产品</option>
					<option value="8" <?php if($search_other_id==8){?>selected <?php } ?>>猜您喜欢产品</option>
					<option value="9" <?php if($search_other_id==9){?>selected <?php } ?>>包邮</option>
					<option value="10" <?php if($search_other_id==10){?>selected <?php } ?>>兑换专区</option>
					<option value="11" <?php if($search_other_id==11){?>selected <?php } ?>>限购</option>
					<option value="12" <?php if($search_other_id==12){?>selected <?php } ?>>首次推广奖励</option>
					<option value="13" <?php if($search_other_id==13){?>selected <?php } ?>>特权专区</option>
					<option value="14" <?php if($search_other_id==14){?>selected <?php } ?>>关联礼包</option>
					<option value="15" <?php if($search_other_id==15){?>selected <?php } ?>>微信小程序</option>
				</select>
                </li>
				<li>商品来源：
                   <select name="search_source" id="search_source">
					<option value="-1">--所有--</option>
					<option value="1" <?php if($search_source==1){?>selected <?php } ?>>平台</option>
					<option value="2" <?php if($search_source==2){?>selected <?php } ?>>合作商</option>
				</select>
                </li>
				<li id="li_supply" <?php if($search_source != 2){?>style='display:none'<?php }?>>合作商：

					<select name="search_supply" id="search_supply">
						<option value="-1">--所有--</option>
						<?php
							$query_c     = "select distinct is_supply_id from weixin_commonshop_products where isvalid = true and yundian_id < 0 and is_supply_id > 0 and customer_id = ".$customer_id."";
							$result_c    = _mysql_query($query_c);
							$i           = 0;
							$c_id        = array();
							while($row_c = mysql_fetch_object($result_c))
							{
								$c_id[$i]  = "'".$row_c->is_supply_id."'";
								
								$i++;
							}
							
							$c_id_str    = implode(',',$c_id);
							if(empty($c_id_str)){
								$c_id_str = "''";
							}
							//$query_s     = "SELECT id ,name,weixin_name FROM weixin_users WHERE isvalid=true AND id in (".$c_id_str.")";
							$query_s    = "select u.id,u.name,u.weixin_name from weixin_commonshop_applysupplys as su left join weixin_users as u on su.user_id=u.id where u.isvalid=true and su.isvalid=true and u.id in(".$c_id_str.")";
							$result_s    = _mysql_query($query_s);
							while($row_s = mysql_fetch_object($result_s))
							{
								$s_id = $row_s->id;
								$s_name = $row_s->name;
								$s_weixin_name = $row_s->weixin_name;
							
						?>
						<option value="<?php echo $s_id;?>" <?php if($search_supply==$s_id){?>selected <?php } ?>><?php echo $s_name."(".$s_weixin_name.")";?></option>
						<?php }	?>
					</select>
                </li>
				<li id="li_sale">
                  <input type="checkbox" name="sales" id="ordersale" <?php echo $sales == 1 ? "checked" : "" ;?> value="<?php echo $sales;?>" style="width:auto;"/>
				  <label for="ordersale">按销量排序</label>
                </li>
            	<li class="WSY_bottonliss"><input type="submit" value="搜索"></li>
			</div>
			<div class="WSY_search_div">
				<ul>
					<li class="WSY_bottonliss left" ><input type="button" style="width:100px" id="btn_export" value="导出产品"></li>
					<li class="WSY_bottonliss left" ><input type="button" style="width:100px" id="btn_check_store" value="校对库存"></li>
					<li class="WSY_bottonliss left" ><input type="button" style="width:100px" id="mul_unsale" value="批量下架"></li>
					<li class="WSY_bottonliss left" ><input type="button" style="width:100px" class="mul_property" data-action="add" value="批量添加标签"></li>
					<li class="WSY_bottonliss left" ><input type="button" style="width:100px" class="mul_property" data-action="delect" value="批量取消标签"></li>
                    <li class="WSY_bottonliss left" ><input type="button" style="width:150px" class="mul_currency" value="批量设置<?php echo defined('PAY_CURRENCY_NAME') ?PAY_CURRENCY_NAME: '购物币'; ?>抵扣比例"></li>
				</ul>
				<li class="bfont aright">
					<?php echo $product_num==-1 ?
				"<span style='color:red'>不限制</span>上架商品个数" :
				"商家可上架<span style='color:red'>".$product_num."</span>个商品 , 已上架产品 <span style='color:red'>".$num."</span> 个" ; ?>
				</li>
				<li class="bfont aright">总记录数 <span class="bfont rcolor"><?php echo $rcount_q2;?></span></li>

			</div>
          </div>
		 </form>
		  <div id="div_check_store" class="div_op" style="display:none;height:auto;margin-left:20px">
			<form  id="frm_import" action="../../../excel/import_excel_store.php?customer_id=<?php echo $customer_id_en; ?>&frompage=sale" enctype="multipart/form-data" method="post" class="store">
                <div class="uploader white" style="box-shadow: 0px 0px 0px #ddd;">
					<input type="text" class="filename" readonly/>
					<input type="button" name="file" id="btn_upfile" class="button" value="上传..."/>
					<input  name="excelfile" id="excelfile" type="file" style="display:none"/>
					&nbsp;&nbsp;&nbsp;
					<input type="button" class="button" value="导入库存" onclick="importMember();" style="margin-left:10px;  border-radius: 5px;" />
					&nbsp;&nbsp;&nbsp;
					<a href="../../../excel/store_template.xls" style="line-height:32px">下载模板文件</a>
				</div>
			</form>
          </div>
		<div id="div_export" class="div_op" style="display:none;height:auto;margin-left:20px">
		   <div class="uploaderbox">
			<em><input type="radio" value="1" id="rdoCond" checked name="exportCond"/> <label for="rdoCond">按当前条件</label></em>
		    <em><input type="radio" value="2" id="rdoAll" name="exportCond"/> <label for="rdoAll">所有</label></em>
			<input type="button" class="butqd" value="确定" onclick="exportProduct();">
			</div>
		 </div>
            <table width="97%" class="WSY_table" id="WSY_t1">

				  <thead class="WSY_table_header">
					<th width="3%"><input id="ck_all"  type="checkbox"></th>
					<th width="5%">序号</th>
					<th width="5%">排序(降序)</th>
					<th width="23%">名称</th>
					<th width="12%">产品分类</th>
					<th width="7%">价格</th>
					<th width="4%">销量</th>
					<th width="7%">库存</th>
					<th width="7%">图片</th>
					<th width="7%">标签</th>
					<th width="8%">时间</th>
					<th width="5%">好评/中评/差评</th>

					<!--4m产品权限-->
					<?php
					if(($is_shopgeneral == 1 and $owner_general==1 ) or ($is_shopgeneral == 1 and $owner_general==2 )){ ?>
					<th width="5%">4M产品权限</th>

					<?php
					}
					?>
					<!--4m产品权限-->
					<th width="8%">操作</th>
				  </thead>

				  <?php


			$supply_id = -1; //供应商user_id
			$p_isvp    = -1; //vp产品
			//print_r($product_list['product_list']);
			foreach($product_list['product_list'] as $v){
				$p_id              = $v['id'];
				$p_name 		   = $v['name'];
				$p_orgin_price     = $v['orgin_price'];
				$p_now_price       = $v['now_price'];
				$p_cost_price      = $v['cost_price'];
				$p_need_score      = $v['need_score'];
				$p_isnew           = $v['isnew'];
				$p_createtime      = $v['createtime'];
				$p_type_id         = $v['type_id'];
				$p_isout           = $v['isout'];
				$p_isnew           = $v['isnew'];
				$p_ishot           = $v['ishot'];
				$p_issnapup        = $v['issnapup'];
				$p_isvp            = $v['isvp'];
				$is_virtual        = $v['is_virtual'];
				$is_currency       = $v['is_currency'];
				$is_guess_you_like = $v['is_guess_you_like'];
				$is_free_shipping  = $v['is_free_shipping'];
				$isscore           = $v['isscore'];
				$islimit           = $v['islimit'];
				$is_first_extend   = $v['is_first_extend'];
				$is_QR             = $v['is_QR'];
				$type_ids          = $v['type_ids'];
				$asort_value       = $v['asort_value'];
				$supply_id         = $v['is_supply_id'];
				$create_type       = $v['create_type'];
				$sell_count        = $v['sell_count'];
				$storenum          = $v['storenum'];
				$tax_type          = $v['tax_type'];
				$vp_score          = $v['vp_score'];
				$buystart_time     = $v['buystart_time'];
				$countdown_time    = $v['countdown_time'];
				$back_currency     = $v['back_currency'];
				$extend_money      = $v['extend_money'];
				$limit_num         = $v['limit_num'];
				$is_privilege 	   = $v['is_privilege'];
				$link_package 	   = $v['link_package'];
				$privilege_level   = $v['privilege_level'];
				$ordering_retail   = $v['ordering_retail'];
				$is_mini_mshop     = $v['is_mini_mshop'];
				$nowprice_title    = $v['nowprice_title'];
				if($privilege_level == '' || $privilege_level == NULL){
					$privilege_level = '0_1_2_3_4_5';
				}
			  // $type_ids=substr($type_ids,1,-1);//取消首尾两个逗号
			//echo $p_id.'---'.$type_ids.'~';
			  $typename="";
			   if(!empty($type_ids)){
					if(strpos($type_ids,",") === 0){
					   $type_ids = substr($type_ids,1);
				   }
				   if(substr($type_ids,strlen($type_ids)-1) == ","){
					   $type_ids = substr($type_ids,0,strlen($type_ids)-1);
				   }

					if(!empty($type_ids)){
						$type_ids = str_replace(',,',',',$type_ids);
					   $query3="select name from weixin_commonshop_types where isvalid=true and id in (".$type_ids.")  ORDER BY create_parent_id asc ";
					 // echo  $query3;
					   $result3 = _mysql_query($query3) or die('L259 : Query failed: ' . mysql_error());

					   while ($row3 = mysql_fetch_object($result3)) {
						  $typename = $typename."/".$row3->name;
					   }
				   }
			   }
				//echo $typename.'<br>';

			   $imgurl = $v['default_imgurl'];
			   if(empty($imgurl)){
				   $query3="select imgurl from weixin_commonshop_product_imgs where isvalid=true and product_id=".$p_id." limit 0,1";
				   $result3 = _mysql_query($query3) or die('L268 : Query failed: ' . mysql_error());
				   $imgurl="";
				   while ($row3 = mysql_fetch_object($result3)) {
					  $imgurl = $row3->imgurl;
				   }
			   }

			   $otherstr="";
			   if($p_isout){
				  $otherstr=$otherstr."下架";
			   }
			   if($p_isnew){
				  $otherstr=$otherstr."/新品";
			   }
			   if($p_ishot){
				  $otherstr=$otherstr."/热卖";
			   }
			   if($p_issnapup){
				  $otherstr=$otherstr."/抢购";
			   }
			   if($p_isvp){
				  $otherstr=$otherstr."/vp产品";
			   }
			   if($is_virtual){
				  $otherstr=$otherstr."/虚拟产品";
			   }
			   if($is_currency){
				  $otherstr=$otherstr."/购物币产品";
			   }
			   if($is_guess_you_like){
				  $otherstr=$otherstr."/猜您喜欢产品";
			   }
			   if($is_free_shipping){
				  $otherstr=$otherstr."/包邮";
			   }
			   if($isscore){
				  $otherstr=$otherstr."/兑换专区";
			   }
			   if($islimit){
				  $otherstr=$otherstr."/限购";
			   }
			   if($is_first_extend){
				  $otherstr=$otherstr."/首次推广奖励";
			   }
			   if($link_package>0){
				  $otherstr=$otherstr."/关联礼包";
			   }if($is_privilege){
			   	  $otherstr=$otherstr."/特权产品";
			   }
			   if ($ordering_retail){
                   $otherstr=$otherstr."/订货系统";
               }
               if ($is_mini_mshop){
                   $otherstr=$otherstr."/微信小程序";
               }
			   $tariff 		= 0;	//关税税率
			   $comsumption = 0;	//消费税税率
			   $addedvalue 	= 0;	//增值税税率
			   $postal 		= 0;	//行邮税率
			   if($tax_type>1){
				  $otherstr=$otherstr."/税收产品";
				  $query_tax = 'SELECT tariff,comsumption,addedvalue,postal FROM weixin_commonshop_product_tax_detail WHERE product_id='.$p_id;
				  $result_tax = _mysql_query($query_tax) or die('Query_tax failed:'.mysql_error());
				  while ( $row_tax = mysql_fetch_object($result_tax) ){
					  $tariff 		= $row_tax -> tariff;
					  $comsumption 	= $row_tax -> comsumption;
					  $addedvalue 	= $row_tax -> addedvalue;
					  $postal 		= $row_tax -> postal;
				  }
			   }

			   $good_level=$v['good_level'];
			   $meu_level = $v['meu_level'];
			   $bad_level = $v['bad_level'];


			   $data= BaseURL."common_shop/jiushop/detail.php?pid=".$p_id."&customer_id=".$customer_id;


				$Query2= "SELECT name,phone,weixin_name,weixin_fromuser FROM weixin_users WHERE isvalid=true AND id=".$supply_id;
				//echo $query2;
				$Result2 = _mysql_query($Query2) or die('L295 : Query failed35: ' . mysql_error());
				$supply_username="";
				$supply_userphone="";
				$supply_weixin_fromuser="";
				$supply_username = "";
				while ($Row2 = mysql_fetch_object($Result2)) {
					$supply_username=$Row2->name;
					$supply_userphone = $Row2->phone;
					$supply_weixin_fromuser= $Row2->weixin_fromuser;
					$supply_weixin_name=$Row2->weixin_name;
					$supply_username = $supply_username."(".$supply_weixin_name.")";//供应商名称加昵称
					break;
				}

				if($supply_id==-1){ $supply_username ="";}//如果不是供应商上传的产品,则为空;
				$shopSupplyName= new createExpQrUtility();
				//$shopSupplyName->mb_substrgb($user_id,$parent_id,$customer_id,1);	//1:商家后台手动改动关系 2:通过分享建立关系 3:推广二维码扫描建立关系;
				$supply_username = $shopSupplyName->mb_substrgb($supply_username,16);//限制文字长度
				//4M判断是否够资格编辑产品
				//厂家默认为true
				if($owner_general == 1){
					$is_change_pros_price = true ;
				}
				$check_is_edit = $u4m->check_is_edit($is_shopgeneral,$owner_general,$create_type,$is_change_pros_price);

		  ?>

				<tr id="WSY_q1">
					<td>
						<input type="checkbox" name="pro_ids" value="<?php echo $p_id; ?>">
					</td>
					<td><?php echo $p_id;?>
						<?php if($supply_id > 0){?>
						<img src="../../../common/images_V6.0/contenticon/gong.png"/>
						<br/>
						<a href="../../Mode/supplier/supply.php?search_user_id=<?php echo $supply_id;?>&customer_id=<?php echo $customer_id_en;?>" style="font-weight:bold">
						<?php echo $supply_username;?></a>
						<?php }?>
					</td>
					<td>
						<input class="WSY_sorting" id="<?php echo $p_id; ?>" type="text" value="<?php echo $asort_value; ?>" onblur="change_Sort(<?php echo $p_id; ?>,this)" >
					</td>
					<td>

					<span id="proname_<?php echo $p_id;?>" data-proname="<?php echo $p_name?>" style="float:left;padding:10px 0;"><?php echo htmlspecialchars($p_name); if($is_QR == 1){ echo ' (券)';} ?></span>
					<?php
					if( $check_is_edit ==1){ ?>

						<img id="saveimg_<?php echo $p_id;?>" src="../../../common/images_V6.0/operating_icon/icon53.png" style="padding:10px 0;" class="ep_img" onclick="toEditName('<?php echo $p_id;?>')">
					<?php }else{?>
							<img  src="../../../common/images_V6.0/operating_icon/icon26.png" style="padding:15px 0; "  class="ep_img no_gengal">
					<?php }?>


					</td>
					<td>
						<span id="protype_<?php echo $p_id;?>" style="float:left;padding:10px 0;"><?php echo $typename; ?></span>
						<?php
						if( $check_is_edit ==1){ ?>
						<img id="savetypeimg_<?php echo $p_id;?>" src="../../../common/images_V6.0/operating_icon/icon53.png" class=" ep_img pro_typeimg" style="padding:10px 0;"
							data-pro-typeid="<?php echo $type_ids;?>" data-pro-tparent="<?php echo $tparent_id;?>" data-pro-id="<?php echo $p_id;?>">
						<?php }else{?>
							<img  src="../../../common/images_V6.0/operating_icon/icon26.png" style="padding:15px 0; "  class="ep_img no_gengal">
						<?php }?>
					</td>
					<td>
						<div class="WSY_pricebox" style="display: inline-block;">
<!-- 							<li class="WSY_price01" id="prooprice_<?php echo $p_id;?>" >原价：<?php if(OOF_P != 2) echo OOF_S ?><?php echo $p_orgin_price; ?><?php if(OOF_P == 2) echo OOF_S ?></li>
							<li id="pronprice_<?php echo $p_id;?>" ><?php if($base_nowprice_title){echo $base_nowprice_title;}else{echo '现价';}?>：<?php if(OOF_P != 2) echo OOF_S ?><?php echo $p_now_price; ?><?php if(OOF_P == 2) echo OOF_S ?></li>
 -->						
							<li class="WSY_price01" id="prooprice_<?php echo $p_id;?>" >原价：<?php if($symbol_position != 2) echo $currency_symbol ?><?php echo $p_orgin_price; ?><?php if($symbol_position == 2) echo $currency_symbol ?></li>
							<li id="pronprice_<?php echo $p_id;?>" ><?php if($nowprice_title){echo $nowprice_title;}else if($base_nowprice_title){echo $base_nowprice_title;}else{echo '现价';}?>：<?php if($symbol_position != 2) echo $currency_symbol ?><?php echo $p_now_price; ?><?php if($symbol_position == 2) echo $currency_symbol ?></li>
 						</div>
						<!--4M价格修改-->

						<?php
						if( $check_is_edit ==1){ ?>
							<img id="savepriceimg_<?php echo $p_id;?>" src="../../../common/images_V6.0/operating_icon/icon53.png" data-pro-id="<?php echo $p_id;?>" data-prooprice="<?php echo $p_orgin_price;?>" data-pronprice="<?php echo $p_now_price;?>"style="padding:10px 0; display: inline-block;" class="ep_img pro_priceimg" >
						<?php }else{?>
							<img  src="../../../common/images_V6.0/operating_icon/icon26.png" style="padding:15px 0; "  class="ep_img no_gengal">
						<?php }?>
						<!--4M价格修改-->
					</td>
					<td>
						<?php echo $sell_count; ?>
					</td>
					<td>
						<span id="prostock_<?php echo $p_id;?>" style="float:left;padding:18px 0; "><?php echo $storenum; ?></span>
						<?php
						if( $check_is_edit ==1){ ?>
						<img id="savestockimg_<?php echo $p_id;?>" src="../../../common/images_V6.0/operating_icon/icon53.png" style="padding:15px 0; " data-pro-id="<?php echo $p_id;?>" data-prostock="<?php echo $storenum?>" class="ep_img pro_stockimg">
						<?php }else{?>
							<img  src="../../../common/images_V6.0/operating_icon/icon26.png" style="padding:15px 0; "  class="ep_img no_gengal">
						<?php }?>


					</td>

					<td>
					<img src="<?php echo "//".$new_baseurl.$imgurl; ?>" class="WSY_fixed"  />
					</td>
					<td>
						<span id="proattr_<?php echo $p_id;?>" style="float:left;padding:10px 0;"><?php echo $otherstr; ?></span>
						<?php
						if( $check_is_edit ==1){ ?>
						<img id="saveattrimg_<?php echo $p_id;?>" src="../../../common/images_V6.0/operating_icon/icon53.png" class=" ep_img pro_attrimg" style="padding:10px 0;" data-pro-id="<?php echo $p_id;?>" data-isout="<?php echo $p_isout;?>"data-ishot="<?php echo $p_ishot;?>" data-isnew="<?php echo $p_isnew;?>" data-isvp="<?php echo $p_isvp;?>" data-issnapup="<?php echo $p_issnapup;?>" data-is_virtual="<?php echo $is_virtual;?>" data-is_currency="<?php echo $is_currency;?>" data-is_guess="<?php echo $is_guess_you_like;?>" data-is_freeshipping="<?php echo $is_free_shipping;?>" data-is_score="<?php echo $isscore;?>" data-is_limit="<?php echo $islimit;?>" data-is_first_extend="<?php echo $is_first_extend;?>" data-vp_score="<?php echo $vp_score;?>" data-back_currency="<?php echo $back_currency;?>" data-buystart_time="<?php echo $buystart_time;?>" data-countdown_time="<?php echo $countdown_time;?>" data-extend_money="<?php echo $extend_money;?>" data-limit_num="<?php echo $limit_num;?>" data-tax_type="<?php echo $tax_type;?>" data-tariff="<?php echo $tariff;?>" data-comsumption="<?php echo $comsumption;?>" data-addedvalue="<?php echo $addedvalue;?>" data-postal="<?php echo $postal;?>" data-is_privilege="<?php echo $is_privilege;?>" data-privilege_level="<?php echo $privilege_level;?>" data-is_mini_mshop="<?php echo $is_mini_mshop;?>">
							<?php }else{?>
							<img  src="../../../common/images_V6.0/operating_icon/icon26.png" style="padding:15px 0; "  class="ep_img no_gengal">
						<?php }?>
					</td>
					<td>
						<?php echo $p_createtime; ?>
					</td>
					<td>
						<?php
							//计算产品评论数
							$pl_sql = "SELECT COUNT(LEVEL) AS good,(SELECT COUNT(LEVEL) FROM weixin_commonshop_product_evaluations WHERE isvalid=TRUE AND LEVEL=2 AND product_id=$p_id) AS meu,(SELECT COUNT(LEVEL) FROM weixin_commonshop_product_evaluations WHERE isvalid=TRUE AND LEVEL=3 AND product_id=$p_id) AS bad FROM weixin_commonshop_product_evaluations WHERE isvalid=TRUE AND LEVEL =1 AND product_id=$p_id";
							$result = _mysql_query($pl_sql) or die("pl_sql query error : ".mysql_error()."----query : ".$pl_sql);
							while( $pl_row = mysql_fetch_object($result)){
								$good_level = $pl_row->good;
								$meu_level 	= $pl_row->meu;
								$bad_level 	= $pl_row->bad;
							}
						?>
						<a href="../comment/discuss.php?customer_id=<?php echo $customer_id_en; ?>&product_id=<?php echo $p_id; ?>" style="color:rgb(56, 167, 238)"><?php echo $good_level."/".$meu_level."/".$bad_level; ?></a>
					</td>

					<!--4m产品权限-->
						<?php
					if(($is_shopgeneral == 1 and $owner_general==1 ) or ($is_shopgeneral == 1 and $owner_general==2 )){ ?>
					<?php
						if( $check_is_edit ==1){ ?>
					<td>
						<img id="4mpro_<?php echo $p_id;?>" src="../../../common/images_V6.0/operating_icon/icon53.png" style="padding:15px 0; " data-pro-id="<?php echo $p_id;?>" class="ep_img pro_4m_pro">

					</td>
					<?php }else{?>
					<td>
						<img  src="../../../common/images_V6.0/operating_icon/icon26.png" style="padding:15px 0; "  class="ep_img no_gengal">
					</td>
					<?php }?>
					<?php }?>

					<!--4m产品权限-->

					<td class="WSY_t4" id="WSY_t4">
						<?php
						if($_SESSION['is_auth_user']=='no' or ($_SESSION['is_auth_user']=='yes' and $p_isout==1)){ // 如果是授权用户,则需要商家下架后才能编辑 或者 商家才能编辑?>
						  <?php
						  /*4m：    产品是产家，自己也是总店；产品是代理商，自己也是代理商；产品是商家，自己也是商家；
						    不是4m：产品是商家自己
						  */

						if( $check_is_edit ==1){ ?>
							<a href="add_product.php?customer_id=<?php echo $customer_id_en; ?>&product_id=<?php echo $p_id; ?>&pagenum=<?php echo $pagenum; ?>&adminuser_id=<?php echo $adminuser_id; ?>&owner_general=<?php echo $owner_general; ?>&orgin_adminuser_id=<?php echo $orgin_adminuser_id; ?>"
								title="修改"><img src="../../../common/images_V6.0/operating_icon/icon05.png"></a>
							<?php }
						}?>
						<a title="产品推广二维码，扫描即可购买" href="javascript:showMediaMap('<?php echo QRURL."?qrtype=1&customer_id=".$customer_id; ?>&product_id=<?php echo $p_id; ?>&data=<?php echo $data; ?>')"><img src="../../../common/images_V6.0/operating_icon/icon09.png"></a>
						<?php if( 1 > $supply_id and $is_Pcode > 0 ){?>
						<a title="产品防伪二维码" href="code/security_code.php?customer_id=<?php echo $customer_id_en; ?>&product_id=<?php echo $p_id; ?>"><img src="../../../common/images_V6.0/operating_icon/icon71.png"></a>
						<?php } ?>
						<?php
						if($_SESSION['is_auth_user']=='no' or ($_SESSION['is_auth_user']=='yes' and $p_isout==1)){ // 如果是授权用户,则需要商家下架后才能编辑 或者 商家才能编辑?>

						 <?php
						if( $check_is_edit ==1){
						    if($ordering_retail){
						        $delete_msg = "该产品已参与订货系统，删除后有影响，确定吗？";
                            }else{
                                $delete_msg = "删除后不可恢复，继续吗？";
                            }
						    ?>
								<a href="javascript:;" class="del-btn" data-pid="<?php echo $p_id;?>" title="删除"><img src="../../../common/images_V6.0/operating_icon/icon04.png"></a>
							<?php
							}
						}?>
						<?php
						if( $check_is_edit ==1){ ?>
							<a title="下架" href="sale.php?customer_id=<?php echo $customer_id_en; ?>&keyid=<?php echo $p_id; ?>&pagenum=<?php echo $pagenum;?>&op=unsale" onclick="if(!confirm(&#39;<?php if($is_shopgeneral ==1 ){echo '是否确定将上级商品下架？下架后则无法恢复';}else{echo '是否确定将该商品下架？';}?>&#39;)){return false};"><img src="../../../common/images_V6.0/operating_icon/icon41.png"></a>
						<?php }?>

						<a href="product_relation.php?customer_id=<?php echo $customer_id_en; ?>&product_id=<?php echo $p_id; ?>&pagenum=<?php echo $pagenum; ?>&"
								title="产品关联"><img src="../../../common/images_V6.0/operating_icon/icon60.png"></a>
						<?php if($supply_id < 0){?>
						   <a title="投放广告链接" href="javascript:showLabelTag('<?php echo "../../MarkPro/advertise_tag/label_pro.php?customer_id=".$customer_id."&product_id=".$p_id;?>')"><img src="../../../common/images_V6.0/operating_icon/icon30.png"></a>
						<?php }?>
						 <!--<a title="复制前端链接" style="cursor: pointer;" class="copy_btn" onclick="copy_url(<?php echo $p_id;?>);"><img src="../../Common/images/Product/copy.png" alt=""></a>-->
						<a title="上下架日志" href="product_log.php?customer_id=<?php echo $customer_id_en; ?>&keyid=<?php echo $p_id; ?>&pagenum=<?php echo $pagenum;?>"><img src="../../../common/images_V6.0/operating_icon/icon11.png" alt=""></a>
					</td>
				</tr>
				<?php } ?>
            </table>
			<!--4m商家修改产品价格权限start-->
			<input type="hidden" name="4m_is_change_pros_price" value="<?php echo $is_change_pros_price ;?>"/>
			<!--4m商家修改产品价格权限end-->
    	</div>
        <!--翻页开始-->
        <div class="WSY_page">

        </div>
        <!--翻页结束-->
    </div>
    <!--产品管理代码结束-->
	</div>
   </div>
 </div>
</div>
<div class="div_item" id="div_tax_html" style="display:none;">
	<div class="div_item" id="div_tax_text" style="display:none;text-align:left;width:90%;">
		<div style="margin:5px 0;">
			税率模板：
			<select name="tax_id" id="tax_sel" onclick='change_tax();' style="width:75%;">
				<option value="" >模板选择</option>
				<?php
				$p_taxid 		= -1;
				$p_tariff		= 0;
				$p_comsumption	= 0;
				$p_addedvalue	= 0;
				$p_postal		= 0;
				$query_tax='SELECT id,tariff,comsumption,addedvalue,postal FROM weixin_commonshop_product_tax_public WHERE customer_id='.$customer_id;
				$result_tax = _mysql_query($query_tax) or die('Query_area failed: ' . mysql_error());
				while ($row_tax = mysql_fetch_object($result_tax)) {
					$p_taxid 		=  	$row_tax->id;
					$p_tariff 		=   $row_tax->tariff;
					$p_comsumption 	=   $row_tax->comsumption;
					$p_addedvalue 	=   $row_tax->addedvalue;
					$p_postal 		=   $row_tax->postal;
				?>
				<option value="<?php echo $p_tariff;?>,<?php echo $p_comsumption;?>,<?php echo $p_addedvalue;?>,<?php echo $p_postal;?>"  ><?php echo "关税税率:".$p_tariff."%；消费税税率:".$p_comsumption."%；增值税税率:".$p_addedvalue."%；行邮税:".$p_postal."%"; ?></option>
				<?php
				}
				?>
			</select>
		</div>
		<div style="margin:5px 0;">
			税收标签：
			<select name="tax_type" id="tax_type">
				<option value="2" >跨境零售</option>
				<option value="3" >国内代发</option>
				<option value="4" >海外集货</option>
				<option value="5" >海外直邮</option>
			</select>
		</div>
		<div style="margin:5px 0;">
			<dd style="margin-bottom:5px;">
				关税税率：
				<input type="text" value="" name="tariff" id="tariff">%
			</dd>
			<dd style="margin-bottom:5px;">
				消费税税率：
				<input type="text" value="" name="comsumption" id="comsumption">%
			</dd>
			<dd style="margin-bottom:5px;">
				增值税税率：
				<input type="text" value="" name="addedvalue" id="addedvalue">%
			</dd>
			<dd style="margin-bottom:5px;">
				行邮税率：
				<input type="text" value="" name="postal" id="postal">%
			</dd>
		</div>
	</div>
</div>
<?php

mysql_close($link);
?>
<script type="text/javascript">
pagenum = <?php echo $pagenum; ?>;
rcount_q = <?php echo $rcount_q2?>;
pagesize = <?php echo $pagesize ?>;
count =Math.ceil(rcount_q/pagesize);//总页数
//page = count;
page = '<?php echo $page;?>';
customer_id_en = '<?php echo $customer_id_en;?>';
//page_index = 0;
ordersale = '<?php echo $sales;?>';
pagename = "sale";
customer_id = '<?php echo $customer_id;?>';
auth_user_id = '<?php echo  $auth_user_id; ?>';
tax_html = $('#div_tax_html').html();
$('#div_tax_html').remove();
link_url = "<?php echo $_SERVER['SERVER_NAME']?>";
is_rebate_open  = Boolean("<?php echo $is_rebate_open?>");
//customer_id = "<?php echo $customer_id_en;?>";
</script>
<!--内容框架结束-->
<script type="text/javascript" src="../../Common/js/Product/product/clipboard.js"></script>
<script type="text/javascript" src="../../../common/js_V6.0/content.js"></script>
<script src="../../../js/fenye/jquery.page1.js"></script>
<script type="text/javascript" src="../../../common/js/layer/layer.js"></script>
<script type="text/javascript" src="../../../common/js/layer/V2_1/layer.js"></script>
<script src="../../../common/js/floatBox.js"></script>
<script type="text/javascript" src="../../Common/js/Product/product_common.js?v=<?php echo time() ;?>"></script>
<script type="text/javascript" src="../../Common/js/Product/product_common_4m.js"></script>
<script type="text/javascript" src="../../Common/js/Product/product/sale.js"></script>
<script src="../../Common/js/percent/jquery.percentageloader.0.2.js"></script>


</body>
</html>
