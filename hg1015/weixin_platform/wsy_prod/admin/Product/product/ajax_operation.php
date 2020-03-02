<?php
header("Content-type: text/html; charset=utf-8"); 
require('../../../../weixinpl/config.php');
require('../../../../weixinpl/customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../../weixinpl/back_init.php');
$link =mysql_connect(DB_HOST,DB_USER, DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

require('../../../../weixinpl/common/utitily_back_newshop.php');
$store_check = new Back_newshop_utitily();


$log_username = $_SESSION['username'];
require('../../../../weixinpl/function_model/shop/product.php');
$product = new Product();

$resultArr = array(); //用于JSON返回的结果

/* redis start */
require_once($_SERVER['DOCUMENT_ROOT'].'/mp/function_redis.php');
/* redis end */

$op=$configutil->splash_new($_GET['op']);
if($op == 1){ //修改产品名
	$id=$configutil->splash_new($_GET['id']);
	$val=$configutil->splash_new($_GET['val']);
	$val = str_replace('-', '', $val);//过滤横杠
	$sql="update weixin_commonshop_products set name = '".$val."' where isvalid = true and  id=".$id;
	_mysql_query($sql);
	$error=mysql_error();
	if($error!=""){
		$resultArr["code"] = 0;
		$resultArr["msg"] = "失败";
	}else{
		$resultArr["code"] = 1;
		$resultArr["msg"] = "成功";
	}
    
    /* 清除redis缓存 start */
    redis_del("product_detail_{$id}");
    redis_del("product_list_{$customer_id}");
    /* 清除redis缓存 end */
}else if($op == 2){ //获取产品的父分类
	$type_id=$configutil->splash_new($_GET['type_id']);
	//echo "  type_id : ".$type_id;
	$sql="select parent_id from weixin_commonshop_types where isvalid = true and id = ".$type_id;
	$result = _mysql_query($sql);
	$parent_id = -1;
	if($row = mysql_fetch_object($result)){
		$parent_id = $row->parent_id;
	}
	//echo "  parent_id : ".$parent_id;
	if($parent_id <= 0){
		$resultArr["parent_id"] = $type_id;
		$resultArr["child_id"] = -1;
	}else{
		$resultArr["parent_id"] = $parent_id;
		$resultArr["child_id"] = $type_id;
	}
	$error=mysql_error();
	if($error!=""){
		$resultArr["code"] = 0;
		$resultArr["msg"] = "失败";
	}else{
		$resultArr["code"] = 1;
		$resultArr["msg"] = "成功";
	}
}else if($op == 3){ //修改产品分类
	$id=$configutil->splash_new($_GET['id']);
	$type_id=$configutil->splash_new($_GET['type_id']);
	$sql="update weixin_commonshop_products set type_ids = '".$type_id."' where isvalid = true and id=".$id;
	_mysql_query($sql);
	$error=mysql_error();
	if($error!=""){ 
		$resultArr["code"] = 0;
		$resultArr["msg"] = "失败:".$error;
	}else{
		$resultArr["code"] = 1;
		$resultArr["msg"] = "修改成功";
	}
    
    /* 清除redis缓存 start */
    redis_del("product_detail_{$id}");
    redis_del("product_list_{$customer_id}");
    /* 清除redis缓存 end */
}else if($op == 4){ //修改产品属性
	$id              = $configutil->splash_new($_GET['id']);
	$isnew           = $configutil->splash_new($_GET['isnew']);
	$isout           = $configutil->splash_new($_GET['isout']);
	$ishot           = $configutil->splash_new($_GET['ishot']);
	$issnapup        = $configutil->splash_new($_GET['issnapup']);
	$isvp            = $configutil->splash_new($_GET['isvp']);
	$is_virtual      = $configutil->splash_new($_GET['is_virtual']);
	$is_currency     = $configutil->splash_new($_GET['is_currency']);
	$is_guess        = $configutil->splash_new($_GET['is_guess']);
	$is_freeshipping = $configutil->splash_new($_GET['is_freeshipping']);
	$is_score        = $configutil->splash_new($_GET['is_score']);
	$is_limit        = $configutil->splash_new($_GET['is_limit']);
	$is_first_extend = $configutil->splash_new($_GET['is_first_extend']);
	$buystart_time   = $configutil->splash_new($_GET['buystart_time']);
	$countdown_time  = $configutil->splash_new($_GET['countdown_time']);
	$vp_text         = $configutil->splash_new($_GET['vp_text']);
	$currency_text   = $configutil->splash_new($_GET['currency_text']);
	$limit_text      = $configutil->splash_new($_GET['limit_text']);
	$extend_text     = $configutil->splash_new($_GET['extend_text']);
	$tax_type	     = $configutil->splash_new($_GET['tax_type']);
	$tariff		     = $configutil->splash_new($_GET['tariff']);
	$comsumption     = $configutil->splash_new($_GET['comsumption']);
	$addedvalue    	 = $configutil->splash_new($_GET['addedvalue']);
	$postal 	   	 = $configutil->splash_new($_GET['postal']);
	$is_mini_mshop 	 = $configutil->splash_new($_GET['is_mini_mshop']);
	$link_package = -1;
	if(!empty($_GET['link_package'])){
		$link_package 	   	 = $configutil->splash_new($_GET['link_package']);
	}
	
	$link_package_img 	   	 = $configutil->splash_new($_GET['link_package_img']);
	$isout_status = 0;	//供应商商品是否平台已确认上架:1.已上架 0.未上架
	$privilege_str   = $configutil->splash_new($_GET['privilege_str']);
	$is_privilege    = $configutil->splash_new($_GET['is_privilege']);
	
	//echo "is_privilege===".$is_privilege."////privilege_str===".$privilege_str;die;

	if( !$isvp ){
		$vp_text = 0;
	}
	if( !$is_currency ){
		$currency_text = 0;
	}
	if( !$issnapup ){
		$buystart_time  = '';
		$countdown_time = '';
	}
	if( !$is_limit ){
		$limit_text = 0;
	}
	if( !$is_first_extend ){
		$extend_text = 0;
	}
	
	if(0 == $isout){	//isout 0:上架 ,1:下架
		$isout_status = 1;	//将供应商字段改为上架
		
		
		// 查找供应商产品成本价和库存是否设置为0 ----start
			$sql_product="select cost_price,storenum,is_supply_id from weixin_commonshop_products where isvalid = true and id=".$id;
			$result_product = _mysql_query($sql_product);
			$is_supply_id  = -1;  //成本价
			$tc_price  	   = -1;  //成本价
			$ts_num        = -1;  //库存
			$c_check       =  0;  //判断供货价是否为0 ; c_check 0:可修改上架 1:不可修改上架
			$num_check     =  0;  //判断库存是否为0 ; num_check 0:可修改上架 1:不可修改上架
			while($row = mysql_fetch_object($result_product)){
				$tc_price      = $row->cost_price;
				$ts_num        = $row->storenum;
				$is_supply_id  = $row->is_supply_id;
				/* if(0 >= $tc_price && 0 < $is_supply_id ){
					$resultArr["code"] = 0;
					$error = "供应商产品供货价不可为0";
					$resultArr["msg"] = "失败:".$error;
					echo json_encode($resultArr);
					exit;
				}  */
				if(0 >= $ts_num){
					$resultArr["code"] = 0;
					$error = "库存不可为0";
					$resultArr["msg"] = "失败:".$error;
					echo json_encode($resultArr);
					exit;
				}
				break;
			}
			$sql_price = "select cost_price,storenum from weixin_commonshop_product_prices where product_id=".$id;
			$result_price = _mysql_query($sql_price) or die('L104 :'.mysql_error());
			$c_price = -1;
			$s_num   = -1;
			while($row = mysql_fetch_object($result_price)){
				$c_price = $row->cost_price;
				$s_num   = $row->storenum;
				/* if(0 >= $tc_price && 0 < $is_supply_id){
					 $resultArr["code"] = 0;
					 $error = "供应商产品供货价不可为0";
					 $resultArr["msg"] = "失败:".$error;
					 echo json_encode($resultArr);
					 exit;
				} */
				if(0 >= $s_num){
					$resultArr["code"] = 0;
					$error = "库存不可为0";
					$resultArr["msg"] = "失败:".$error;
					echo json_encode($resultArr);
					exit;
				}
			}
		// 查找供应商产品供货价和库存是否设置为0 ----end
	}
	if($isout == 0){
		$result_msg = (string)$store_check->check_product_info($customer_id,$id);
			if($result_msg == "正确"){
			$sql="update weixin_commonshop_products set isout_status = ".$isout_status.", isnew = ".$isnew.",isout = ".$isout.",ishot = ".$ishot.",issnapup = ".$issnapup.",isvp = ".$isvp.",is_virtual = ".$is_virtual.",is_currency = ".$is_currency.",is_guess_you_like = ".$is_guess.",is_free_shipping = ".$is_freeshipping.",isscore = ".$is_score.",islimit = ".$is_limit.",is_first_extend = ".$is_first_extend.",back_currency = ".$currency_text.",vp_score = ".$vp_text.",buystart_time = '".$buystart_time."',countdown_time = '".$countdown_time."',limit_num = ".$limit_text.",extend_money = ".$extend_text.",tax_type=".$tax_type.",is_privilege=".$is_privilege.",privilege_level='".$privilege_str."',link_package=".$link_package.",link_package_img='".$link_package_img."',is_mini_mshop=".$is_mini_mshop." where isvalid = true and id=".$id;
			//echo $sql;die;
				_mysql_query($sql);
				$error=mysql_error();
				
				
				$sql2="update weixin_commonshop_supply_products set is_out=0 where isvalid = true and pid=".$id;
				_mysql_query($sql2) or die("L73 : query error : ".mysql_error());
				
				
				

				if($error!=""){
					$resultArr["code"] = 0;
					$resultArr["msg"] = "失败:".$error;
					$resultArr["sql"] = "sql:".$sql;
				}else{
					$resultArr["code"] = 1;
					$resultArr["msg"] = "修改成功";
					$res = $product->insert_shop_product_log($id,2,$log_username,1);	   //插入上架日志
				}
		}else{
			$resultArr["code"] = 0;
			$resultArr["msg"] = "失败:".$result_msg;
		}
	}else{
		$sql="update weixin_commonshop_products set isout_status = ".$isout_status.", isnew = ".$isnew.",isout = ".$isout.",ishot = ".$ishot.",issnapup = ".$issnapup.",isvp = ".$isvp.",is_virtual = ".$is_virtual.",is_currency = ".$is_currency.",is_guess_you_like = ".$is_guess.",is_free_shipping = ".$is_freeshipping.",isscore = ".$is_score.",islimit = ".$is_limit.",is_first_extend = ".$is_first_extend.",back_currency = ".$currency_text.",vp_score = ".$vp_text.",buystart_time = '".$buystart_time."',countdown_time = '".$countdown_time."',limit_num = ".$limit_text.",extend_money = ".$extend_text.",tax_type=".$tax_type.",is_privilege=".$is_privilege.",privilege_level='".$privilege_str."',link_package=".$link_package.",link_package_img='".$link_package_img."',is_mini_mshop=".$is_mini_mshop." where isvalid = true and id=".$id;
		_mysql_query($sql);
		$error=mysql_error();
		
		$sql2="update weixin_commonshop_supply_products set is_out=1 where isvalid = true and pid=".$id;
		_mysql_query($sql2) or die("L73 : query error : ".mysql_error());
		if($error!=""){
			$resultArr["code"] = 0;
			$resultArr["msg"] = "失败:".$error;
		}else{
			$res = $product->insert_shop_product_log($id,1,$log_username,1);	   //插入下架日志
			$resultArr["code"] = 1;
			$resultArr["msg"] = "修改成功";
		}
	}
    
	/* 税收产品 start*/
	if ( $tax_type > 1 ){
		$tax_id = -1;
		$query_tax = "SELECT id FROM weixin_commonshop_product_tax_detail WHERE product_id=".$id." LIMIT 1";
		$result_tax = _mysql_query($query_tax) or die('L365: Query failed1: ' . mysql_error());
		while( $row_tax = mysql_fetch_object($result_tax) ){
			$tax_id = $row_tax->id;
		}
		if ( $tax_id > 0 ){
			$sql_tax = 'UPDATE weixin_commonshop_product_tax_detail SET tariff='.$tariff.',comsumption='.$comsumption.',addedvalue='.$addedvalue.',postal='.$postal.',is_dutyfree='.$is_freeshipping.' WHERE product_id='.$id;
		} else {
			$sql_tax = 'INSERT INTO weixin_commonshop_product_tax_detail(product_id,tariff,comsumption,addedvalue,postal,is_dutyfree,premium) VALUES('.$id.','.$tariff.','.$comsumption.','.$addedvalue.','.$postal.','.$is_freeshipping.',0)';
		}
		_mysql_query($sql_tax) or die('Sql_tax failed:'.mysql_error());
	} else {
		$query_tax_del = 'DELETE FROM weixin_commonshop_product_tax_detail WHERE product_id='.$id;
		_mysql_query($query_tax_del) or die('Query_tax_del failed: '.mysql_error());
	}
	/* 税收产品 end */
	
    /* 清除redis缓存 start */
    redis_del("product_detail_{$id}");
    redis_del("product_list_{$customer_id}");
    /* 清除redis缓存 end */
}else if($op == 5){ //获取价格
	$tpid=$configutil->splash_new($_GET['id']);
	$sql_product="select orgin_price,now_price,vip_price,cost_price,need_score,for_price,storenum from weixin_commonshop_products where isvalid = true and id=".$tpid;
	$result_product = _mysql_query($sql_product);
	$to_price = -1;
	$tn_price = -1;
	$tv_price = -1;
	$tc_price = -1;
	$tb_price = -1;
	$tn_score = -1;
	$ts_num = -1;
	while($row = mysql_fetch_object($result_product)){
		$to_price = $row->orgin_price;
		$tn_price = $row->now_price;
		$tv_price = $row->vip_price;
		$tc_price = $row->cost_price;
		$tb_price = $row->for_price;
		$tn_score = $row->need_score;
		$ts_num = $row->storenum;
		
	}//总价相关
		
		
	$resultArr[0]['fpid'] = 0;
	$resultArr[0]['pid'] = $tpid;
	$resultArr[0]['proids'] = '产品';
	$resultArr[0]['o_price'] = $to_price;
	$resultArr[0]['n_price'] = $tn_price;
	$resultArr[0]['v_price'] = $tv_price;
	$resultArr[0]['c_price'] = $tc_price;
	$resultArr[0]['b_price'] = $tb_price;
	$resultArr[0]['n_score'] = $tn_score;	
	$resultArr[0]['s_num'] = $ts_num;//库存
	
	$sql_price = "select id,proids,orgin_price,now_price,vip_price,cost_price,need_score,storenum,for_price from weixin_commonshop_product_prices where product_id=".$tpid;
	$result_price = _mysql_query($sql_price) or die('L104 :'.mysql_error());
	$i = 1;
	$opid = -1;
	$proids = -1;
	$o_price = -1;
	$n_price = -1;
	$v_price = -1;
	$c_price = -1;
	$b_price = -1;
	$n_score = -1;
	$s_num = -1;
	while($row = mysql_fetch_object($result_price)){
		$pid = $row->id;
		$proids = $row->proids;
		$o_price = $row->orgin_price;
		$n_price = $row->now_price;
		$v_price = $row->vip_price;
		$c_price = $row->cost_price;
		$b_price = $row->for_price;
		$n_score = $row->need_score;
		$s_num = $row->storenum;
		//单价相关
		$resultArr[$i]['fpid'] = $pid;
		$resultArr[$i]['pid'] = $tpid;
		$resultArr[$i]['o_price'] = $o_price;
		$resultArr[$i]['n_price'] = $n_price;
		$resultArr[$i]['v_price'] = $v_price;
		$resultArr[$i]['c_price'] = $c_price;
		$resultArr[$i]['b_price'] = $b_price;
		$resultArr[$i]['n_score'] = $n_score;
		$resultArr[$i]['s_num'] = $s_num;
		
		
		$proid = -1;
		if(strpos($proids,"_")){
			$proid = explode("_",$proids);
			$pname_a = "";
			$pname = "";
			foreach ($proid as $v=>$a){ 
				$sql_pname = "select name from weixin_commonshop_pros where isvalid = true and id=".$a." and customer_id=".$customer_id;
				$result_pname = _mysql_query($sql_pname);				
				while($row = mysql_fetch_object($result_pname)){
					$pname_a = $row->name;				
				}
				if($pname_a!=""){
					$pname .=$pname_a.'/'; 
				}
			 }
			 $pname = substr($pname,0,-1);
		}else{
			$proid = $proids;
			$sql_pname = "select name from weixin_commonshop_pros where isvalid = true and id=".$proid." and customer_id=".$customer_id;
			$result_pname = _mysql_query($sql_pname);
			$pname = "";
			while($row = mysql_fetch_object($result_pname)){
				$pname = $row->name;
			}
		}
		
		$resultArr[$i]['proids'] = $pname;	
		
		
		$i++;		
	}
}else if($op == 6){ //修改价格
	$pid=$configutil->splash_new($_GET['id']);
	$aids=$configutil->splash_new($_GET['aids']);
	$val_os=$configutil->splash_new($_GET['val_os']);
	$val_ns=$configutil->splash_new($_GET['val_ns']);
	$val_vs=$configutil->splash_new($_GET['val_vs']);
	$val_cs=$configutil->splash_new($_GET['val_cs']);
	$val_bs=$configutil->splash_new($_GET['val_bs']);
	$val_ss=$configutil->splash_new($_GET['val_ss']);

	$arr_ids = explode(",",$aids); //属性ID
	$arr_os = explode(",",$val_os); //原价
	$arr_ns = explode(",",$val_ns); //现价
	$arr_vs = explode(",",$val_vs); //VIP价
	$arr_cs = explode(",",$val_cs); //供货价
	$arr_bs = explode(",",$val_bs); //成本价
	$arr_ss = explode(",",$val_ss); //所需积分

    //检查是否订货系统产品 lml 20171130
    $ordering_retail = 0;
    $query_ordering  = "SELECT ordering_retail FROM weixin_commonshop_products WHERE isvalid=true AND id='".$pid."' AND customer_id='".$customer_id."'";
    $result_ordering = _mysql_query($query_ordering) or die(__LINE__." Query failed: ".mysql_error());
    while($row_ordering = mysql_fetch_object($result_ordering)){
        $ordering_retail = $row_ordering -> ordering_retail;
        break;
    }
	$is_change_now_price = false;
	for($i = 0 ; $i < count($arr_ids) ; $i++ ){
		$id = $arr_ids[$i]; 
		$arr_os[$i] = round($arr_os[$i], 2);
		$arr_ns[$i] = round($arr_ns[$i], 2);
		$arr_vs[$i] = round($arr_vs[$i], 2);
		$arr_cs[$i] = round($arr_cs[$i], 2);
		$arr_bs[$i] = round($arr_bs[$i], 2);
		if($ordering_retail > 0){ //是订货系统的产品，判断现价是否有改变
            if($id == 0){
                $sql_p = "select now_price from weixin_commonshop_products where isvalid = true and  id=".$pid;
            }else{
                $sql_p = "select now_price from weixin_commonshop_product_prices where id=".$id;
            }
            $res_p = _mysql_query($sql_p);
            while($row_p = mysql_fetch_object($res_p)){
                $now_price_b = $row_p -> now_price;
                if($arr_ns[$i] != $now_price_b){
                    $is_change_now_price = true;
                }
            }
        }
		if($id == 0){
			$sql="update weixin_commonshop_products set orgin_price = '".$arr_os[$i]."',now_price = '".$arr_ns[$i]."',vip_price = '".$arr_vs[$i]."',cost_price = '".$arr_cs[$i]."',need_score = '".$arr_ss[$i]."',for_price = '".$arr_bs[$i]."' where isvalid = true and  id=".$pid;
		}else{
			$sql="update weixin_commonshop_product_prices set orgin_price = '".$arr_os[$i]."',now_price = '".$arr_ns[$i]."',vip_price = '".$arr_vs[$i]."',cost_price = '".$arr_cs[$i]."',need_score = '".$arr_ss[$i]."',for_price = '".$arr_bs[$i]."' where id=".$id;
		}
		//var_dump($sql);
		_mysql_query($sql);
	}$error=mysql_error();
	if($error!=""){
		$resultArr["code"] = 0;
		$resultArr["msg"] = "失败";
	}else{
		$resultArr["code"] = 1;
		$resultArr["msg"] = "成功";
	}
	if($is_change_now_price){
        require_once ("{$_SERVER['DOCUMENT_ROOT']}/wsy_prod/admin/Product/product/change_ordering_pro.php");
        $change_ordering_pro = new change_ordering_pro();
        $change_ordering_pro->change_pro($customer_id,$pid,false);
    }
    $resultArr["is_change_now_price"] = $is_change_now_price; //是否是订货系统的产品并修改了现价
    /* 清除redis缓存 start */
    redis_del("product_detail_{$pid}");
    redis_del("product_list_{$customer_id}");
    /* 清除redis缓存 end */
}else if($op == 7){ //修改库存
	$pid=$configutil->splash_new($_GET['id']);
	$aid=$configutil->splash_new($_GET['aid']);
	$val_s=$configutil->splash_new($_GET['val_s']);
	
	$arr_ids = explode(",",$aid); //属性ID
	$arr_s = explode(",",$val_s); //库存
	for($i = 0 ; $i < count($arr_ids) ; $i++ ){
		$id = $arr_ids[$i]; 
		if($id == 0){
			$sql="update weixin_commonshop_products set storenum = '".$arr_s[$i]."' where isvalid = true and id=".$pid;
		}else{
			$sql="update weixin_commonshop_product_prices set storenum = '".$arr_s[$i]."' where id=".$id;
		}_mysql_query($sql);
	}$error=mysql_error();
	if($error!=""){
		$resultArr["code"] = 0;
		$resultArr["msg"] = "失败";
	}else{
		$resultArr["code"] = 1;
		$resultArr["msg"] = "成功";
	}
    
    /* 清除redis缓存 start */
    redis_del("product_detail_{$pid}");
    redis_del("product_list_{$customer_id}");
    /* 清除redis缓存 end */
}else if($op == 8){ //批量设置购物币抵扣比例
	$idsStr=$configutil->splash_new($_GET['idsStr']);
    $currency_percentage=$configutil->splash_new($_GET['currency_percentage']);
    if($currency_percentage>0){
        $currency_percentage = $currency_percentage/100;
    }
	$ids_Str = explode(",",$idsStr);
	foreach($ids_Str as $key=>$value){
	//	$ids .= ','.$value;
    $sel = "SELECT count(id) as num FROM commonshop_product_discount_t WHERE isvalid=true and pid=".$value;
    $res = _mysql_query($sel) or die('Query_sel2 failed26: ' . mysql_error());
    while($row=mysql_fetch_object($res)){
        $num = $row->num;
    }
    if($num==0){
        $query = "INSERT INTO commonshop_product_discount_t(isvalid,pid,currency_percentage) VALUES(true,".$value.",".$currency_percentage.")";
    }else{
        $query = "UPDATE commonshop_product_discount_t SET currency_percentage=".$currency_percentage." WHERE isvalid=true and pid=".$value." limit 1";
    }
    _mysql_query($query) or die('query_curr failed32: ' . mysql_error());
    $error=mysql_error();
    if($error!=""){
        break;
    }
	}
    //$ids = ltrim($ids,",");//去掉第一个逗号
    
    //$query = "update commonshop_product_discount_t set currency_percentage = ".$currency_percentage." where isvalid=true and pid in (".$ids.")";
    //echo $query;
    //_mysql_query($query);
    //$error=mysql_error();
	
	if($error!=""){
		$resultArr["code"] = 0;
		$resultArr["msg"] = "批量设置".defined('PAY_CURRENCY_NAME') ?PAY_CURRENCY_NAME: '购物币'."抵扣比例失败";
	}else{
		$resultArr["code"] = 1;
		$resultArr["msg"] = "批量设置".defined('PAY_CURRENCY_NAME') ?PAY_CURRENCY_NAME: '购物币'."抵扣比例成功";
	}
}else if($op == 9){ //删除产品
	$resultArr["code"] = 1;
	$resultArr["msg"]  = "删除后不可恢复，继续吗？";
	$resultArr["from"]  = ""; //用来标识是否要同步订货系统的产品
	$pid = -1;
	if(!empty($_GET['pid'])){
		$pid   =  $configutil->splash_new($_GET['pid']);
	}
	
	//检查F2C系统库存  ---start
	$isopen_f2c = false;	//F2C系统开关
	$f2c_stock  = 0;		//F2C产品库存
	$query  = "SELECT isopen_f2c FROM f2c_setting WHERE id=".$customer_id;
	$result = _mysql_query($query) or die("L453 Query failed: ".mysql_error());
	while($row = mysql_fetch_object($result)){
		$isopen_f2c = $row -> isopen_f2c;
	}
	
	if( $isopen_f2c ){
		$query2  = "SELECT sum(stock) AS f2c_stock FROM f2c_warehouse WHERE isvalid=true and product_id=".$pid;
		$result2 = _mysql_query($query2) or die("L461 Query failed: ".mysql_error());
		while($row2 = mysql_fetch_object($result2)){
			$f2c_stock = $row2 -> f2c_stock;
		}
		
		
		if( $f2c_stock > 0 ){
			$resultArr["code"] = 0;
			$resultArr["msg"]  = "此产品在F2C店的仓库中仍有库存，是否继续删除？";
		}
	}
	//检查F2C系统库存  ---end
	
	//检查是否订货系统产品 lml 20171130
	$ordering_retail = 0;
	$query_ordering  = "SELECT ordering_retail FROM weixin_commonshop_products WHERE isvalid=true AND id='".$pid."' AND customer_id='".$customer_id."'";
	$result_ordering = _mysql_query($query_ordering) or die(__LINE__." Query failed: ".mysql_error());
	while($row_ordering = mysql_fetch_object($result_ordering)){
		$ordering_retail = $row_ordering -> ordering_retail;
		break;
	}
	//检查是否订货系统产品 end
	if($ordering_retail > 0){
		$resultArr["code"] = 0;
//		$resultArr["msg"] = '该产品已关联到订货系统，是否同步删除？';
        $resultArr["from"] = 'orderingretail';
	}
	
	/****************** 检查产品是否关联了换购活动 start (824需求 2017/10/28) **************************/
	$res_exchange = $product->check_is_exchange($pid);
	$is_exchange = $res_exchange['flag'];
	if($is_exchange){ //如果是关联了换购活动的产品不能删除
		$resultArr["code"] = 2;
		$resultArr["msg"]  = "此产品关联了满赠活动[{$res_exchange['exchange_id']}-{$res_exchange['exchange_title']}]，请前往满赠活动列表取消关联之后才能删除！";
	}
	/******************检查产品是否关联了换购活动 end **************************/
	
}
echo json_encode($resultArr);
?>