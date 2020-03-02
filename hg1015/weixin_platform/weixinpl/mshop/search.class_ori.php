<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php');
require('../common/utility.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
require('../common/own_data.php');
/* redis start */
require_once(PATH_REDIS_CLIENT);
define("REDIS_CONFIG_JSON",json_encode(include (PATH_REDIS_CONFIG)));
$redis_config = json_decode(REDIS_CONFIG_JSON,true);
ini_set('default_socket_timeout', -1);
$redis = new RedisClient($redis_config);
/* redis end */
//头文件----start
//require('../common/common_from.php');//2016-1103--qiao(修改)
//头文件----end
$info = new my_data();//own_data.php my_data类
$op = '';
$type_id=-1;//筛选传过来的分类ID
$tid=-1;//分类页传过来的分类ID
if(!empty($_GET["op"])){
	$op = $configutil->splash_new($_GET["op"]);
}
//获取用户的user_id
$user_id = -1;
if(!empty($_POST["user_id"])){
	$user_id = intval($configutil->splash_new($_POST["user_id"]));
}
$start  = 0;
if(!empty($_POST['pageNum'])){
	$start  = $configutil->splash_new($_POST["pageNum"]);
}

	$end  	= $configutil->splash_new($_POST["limit_num"]);

switch ($op){
	case 'search':
        /* 查找全局购物币抵扣比例 */
        $query = "select percentage from currency_percentage_t where isvalid=true and type=1 and customer_id=".$customer_id." limit 0,1";
		$result = _mysql_query($query) or die('Query failed: ' . mysql_error());
		$percentage = 0;//默认0
		while ($row = mysql_fetch_object($result)) {
			$percentage = $row->percentage;//全局购物币抵扣比例	
		}
        
        //查询是否开启购物币抵扣
        $query = "SELECT isOpen,custom FROM weixin_commonshop_currency WHERE customer_id=".$customer_id;
        $result = _mysql_query($query);
        $isOpenCurrency = 0;//默认不开启
        while( $row = mysql_fetch_object($result) ){
            $isOpenCurrency = $row->isOpen;
            $custom 		= $row->custom;

        }
	
		$query = "select is_cashback from weixin_commonshops where isvalid=true and customer_id=".$customer_id." limit 0,1";
		$result = _mysql_query($query) or die('Query failed: ' . mysql_error());
		$is_cashback = 0;//是否开启消费返现
		//$cashback_perday_old = 0; //每天限制领取返现金额
		$shop_id=-1;
		while ($row = mysql_fetch_object($result)) {
			$is_cashback = $row->is_cashback;//是否开启消费返现
			
		}

		//特权身份搜索
		if(!empty($_POST['user_level'])){
			$user_level = $configutil->splash_new($_POST["user_level"]);
            //1查询的时候把-1也查出来了（like查询）,添加排除-1
            $pl_con = "and privilege_level not LIKE '%-1%'";
		}else{
			$user_level = -1;
            $pl_con = "and 1=1";
		}
		//echo "user_level".$user_level;die;
		$rtn_data = array();		
		$product_data = array();
		$query = "select id,name,default_imgurl,is_supply_id,orgin_price,now_price,isvp,vp_score,cashback,cashback_r,show_sell_count,sell_count,is_free_shipping,need_score,back_currency,is_currency,tax_type,privilege_level,is_privilege,propertyids,storenum,is_QR,is_virtual,pro_card_level_id,islimit,is_first_extend from weixin_commonshop_products where isvalid=true and yundian_id<0 and isout=0 and customer_id=".$customer_id." AND ((is_privilege=1 and (privilege_level LIKE '%$user_level%' or privilege_level = '') ".$pl_con." ) or is_privilege=0)  ";
        
        /* 特权产品才判断特权身份，非特权产品直接查出来，edit by djy */
		
		/*********搜索条件start********/

		

		//1全站搜索2品牌代理商店内搜索
		if(!empty($_POST['search_from'])){
			$search_from = $configutil->splash_new($_POST['search_from']);
			
		}
		//供应商
		if(!empty($_POST['supply_id'])){
			$supply_id = $configutil->splash_new($_POST['supply_id']);
			
		}
		//品牌供应商分类ID
		if(!empty($_POST['brand_typeid'])){
			$brand_typeid = $configutil->splash_new($_POST['brand_typeid']);
			
		}
		
       
		//获取筛选传过来的分类ID
		if(!empty($_POST['type_id'])){ 
			$type_id = $configutil->splash_new($_POST['type_id']);
			$type_son="select id from weixin_commonshop_types where isvalid=true and is_shelves=1 and parent_id=".$type_id." and customer_id=".$customer_id."";
			$result_typeson=_mysql_query($type_son) or die ('typeson faild ' .mysql_error());
			while($row=mysql_fetch_object($result_typeson)){
			
				$typeson_id_a[]=$row->id;
			}
			
			if(empty($typeson_id_a)){
				$typeson_id_a=$type_id; 
			}else{
				array_push($typeson_id_a,$type_id);
				$typeson_id_a=implode(',',$typeson_id_a);  //查到一级分类及其子分类
			}

		}

         /**
         * result:返回的数组
         * parentID:需要查找的父节点
         * data:原始数据
         * dep:查找深度
         * 方法：深度优先递归
         */
        function getChild(&$result,$parentId,$data,$dep){
          /*
           * 遍历数据，查找parentId为参数$parentId指定的id
           */
          for($i = 0;$i<count($data);$i++){
            if($data[$i]['parent_id'] == $parentId){
              //$result[] = array('id'=>$data[$i]['id'],'parentId'=>$data[$i]['parentId'],'dep'=>$dep);
                $result[] = $data[$i]['id'];
              getChild($result,$data[$i]['id'],$data,$dep+1);
            }
          }
        }
		//分类页分类ID
		if(!empty($_POST['tid']) && $type_id<0){
			$tid = $configutil->splash_new($_POST['tid']);
			// $type_son="select id from weixin_commonshop_types where isvalid=true and is_shelves=1 and parent_id=".$tid." and customer_id=".$customer_id."";
			// $result_typeson=_mysql_query($type_son) or die ('typeson faild ' .mysql_error());
			// while($row=mysql_fetch_object($result_typeson)){
			
			// 	$typeson_id[]=$row->id;
			// }
			
			// if(empty($typeson_id)){
			// 	$typeson_id=$tid; 
			// }else{
			// 	array_push($typeson_id,$tid);
			// 	$typeson_id=implode(',',$typeson_id);  //查到一级分类及其子分类
			// }

            $data = array();
            $type_son="select id,parent_id from weixin_commonshop_types where isvalid=true and is_shelves=1 and customer_id=".$customer_id." and isvalid=true";
            // echo $type_son;
            $result_typeson=_mysql_query($type_son) or die ('typeson faild ' .mysql_error());
            while($row=mysql_fetch_assoc($result_typeson)){
                $data[]=$row;
            }
            

            $typeson_id = array();
            getChild($typeson_id,$tid,$data,1);


            if(empty($typeson_id)){
                $typeson_id=$tid; 
            }else{
                array_push($typeson_id,$tid);
                $typeson_id=implode(',',$typeson_id);  //查到一级分类及其各级子分类
            }			
		}
		

		if($search_from == 2 && $supply_id>0){ //本地搜索
			$condition .= " and is_supply_id = ".$supply_id."";
		}
		if($search_from == 2 && $brand_typeid>0){ //搜索供应商分类
			$condition .= " and LOCATE(',".$brand_typeid.",', brand_type_ids)>0";
		}
		if($search_from == 2 && $brand_typeid>0){ //搜索分类页产品
			$condition .= " and LOCATE(',".$brand_typeid.",', brand_type_ids)>0";
		}
		if($tid>0 && $type_id<0){ //搜索分类下产品(分类页)
			$condition = $condition." and (";
			$typeson_id_arr = explode(",",$typeson_id);
			$typeson_id_count = count($typeson_id_arr);
			for($j=0;$j<$typeson_id_count;$j++){
				$o_typeid = $typeson_id_arr[$j];
				if($j==0){
					$condition = $condition."( LOCATE(',".$o_typeid.",', type_ids)>0)";
					}else{
					$condition = $condition." or (LOCATE(',".$o_typeid.",', type_ids)>0)";
				}
			}
			$condition = $condition.")";
			unset($typeson_id);
		}
		
		if($type_id>0){ //搜索分类下产品(筛选)
			$condition = $condition." and (";
			$typeson_id_arr_a = explode(",",$typeson_id_a);
			$typeson_id_count = count($typeson_id_arr_a);
			for($j=0;$j<$typeson_id_count;$j++){
				$o_typeid = $typeson_id_arr_a[$j];
				if($j==0){
					$condition = $condition."( LOCATE(',".$o_typeid.",', type_ids)>0)";
					}else{
					$condition = $condition." or (LOCATE(',".$o_typeid.",', type_ids)>0)";
				}
			}
			$condition = $condition.")";
			unset($typeson_id_a);
		}
		
		//新品上市
		if(!empty($_POST['isnew'])){
			$isnew = $configutil->splash_new($_POST['isnew']);
			if($isnew==1){
				$condition .= " and isnew = true "; 
			}
		}
		
		//热卖产品
		if(!empty($_POST['ishot'])){
			$ishot = $configutil->splash_new($_POST['ishot']);
			if($ishot==1){
				$condition .= " and ishot = true "; 
			}
		}
		//VP产品
		if(!empty($_POST['isvp'])){
			$isvp = $configutil->splash_new($_POST['isvp']);
			if($isvp==1){
				$condition .= " and isvp = true "; 
			}
		}

		//特权专区
		if(!empty($_POST['is_privilege']) && $_POST['is_privilege'] > 0){
			$is_privilege = $configutil->splash_new($_POST['is_privilege']);
			if($is_privilege==1){
				$condition .= " and is_privilege = true "; 
			}
		}

		//积分专区
		if(!empty($_POST['isscore'])){
			$isscore = $configutil->splash_new($_POST['isscore']);
			if($isscore==1){
				$condition .= " and isscore = true "; 
			}
		}
		//最小价格
		if(!empty($_POST['curCostMin'])){
			$curCostMin = $configutil->splash_new($_POST['curCostMin']);
			$condition .= " and now_price >= ".$curCostMin."";
		}
		//最大价格
		if(!empty($_POST['curCostMax'])){
			$curCostMax = $configutil->splash_new($_POST['curCostMax']);
			$condition .= " and now_price <= ".$curCostMax."";
		}
		//最小积分
		if(!empty($_POST['curScoreMin'])){
			$curScoreMin = $configutil->splash_new($_POST['curScoreMin']);
			$condition .= " and need_score >= ".$curScoreMin."";
		}
		//最大积分
		if(!empty($_POST['curScoreMax'])){
			$curScoreMax = $configutil->splash_new($_POST['curScoreMax']);
			$condition .= " and need_score <= ".$curScoreMax."";
		}
		//关键词
		if(!empty($_POST['searchKey'])){
			$searchKey = $configutil->splash_new($_POST['searchKey']);

			$searchKey = fuzzy_search($searchKey);
			$condition .= " and name like '%".$searchKey."%'";
		}
		//猜你喜欢cartlike  morelike
		if(!empty($_POST['like_op'])){
			$like_op = $configutil->splash_new($_POST['like_op']);
			$pid=-1;
			$pidarr=[];

			switch($like_op){
				
				case "cartlike": //购物车猜你喜欢
					$cartlike_query="select pro_id from weixin_commonshop_guess_you_like where isvalid=true and customer_id=".$customer_id." order by asort desc,id desc";
					$cartlike_result=_mysql_query($cartlike_query) or die ("cartlike_query faild " .mysql_error());
					while($row=mysql_fetch_object($cartlike_result)){
						$pid=$row->pro_id;
						$pidarr[]=$pid;
					}
				break;
				
				case "morelike":
					
					if(!empty($_POST['like_pid'])){//关联的产品id
						$like_pid = $configutil->splash_new($_POST['like_pid']);
					}
					$prolike_query="select pid from products_relation_t where isvalid=true and parent_pid=".$like_pid." and customer_id=".$customer_id."";
					$prolike_result=_mysql_query($prolike_query) or die ("prolike_query faild " .mysql_error());
					while($row=mysql_fetch_object($prolike_result)){
						$pid=$row->pid;
						$pidarr[]=$pid;
					}
				
			}
			$pidstr=implode(",",$pidarr);
			if ($pidstr != '') {
				$condition .= " and id in(".$pidstr.")";
			}
			
			
		}
		//【队列活动专区】 获取当前活动的关联产品id 2018/4/19
		if(!empty($_POST['isqueue']) && $_POST['isqueue']){
			//当前活动关联的产品id
			$queue_sql = "SELECT p.pid FROM ".WSY_MARK.".weixin_commonshop_queue as q left join ".WSY_MARK.".weixin_commonshop_queue_product as p on q.id = p.queue_id WHERE q.isvalid = true and q.customer_id = '".$customer_id."' and q.isout = '1' and p.isvalid = true";
			$queue_result = _mysql_query($queue_sql) or die ("queue_sql faild " .mysql_error());
			$queue_pro = array();
			while($pid_row=mysql_fetch_object($queue_result)){
				$queue_pro[] = $pid_row->pid;
			}
			$queue_pro = implode(",",$queue_pro);
			$queue_pro_sql = " and id in(".$queue_pro.")";
			//当前活动关联的产品id end
			$condition .= $queue_pro_sql;
		}
		//【队列活动专区】  end
		
		//大小分类
		
		
		
		$condition .= $condition2;		
		
		/*********搜索条件end********/
		
		/*********排序条件atart********/
		//排序
		if(!empty($_POST['op_sort'])){
			$op_sort = $configutil->splash_new($_POST['op_sort']);
		}		
			switch ($op_sort){
				case 'default_d'://默认降序
					$condition .=  " order by asort_value desc ";
				break;
				case 'default_a'://默认升序
					$condition .=  " order by asort_value asc ";
				break;
				case 'sell_d'://销量高
					$condition .=  " order by show_sell_count+sell_count desc ";	//虚拟销售量
				break;
				case 'sell_a'://销量低
					$condition .=  " order by show_sell_count+sell_count asc ";
				break;
				case 'price_d'://价格高
					$condition .=  " order by now_price desc ";
				break;
				case 'price_a'://价格低
					$condition .=  " order by now_price asc ";
				break;
				case 'score_d'://积分高
					$condition .=  " order by need_score desc ";
				break;
				case 'score_a'://积分低
					$condition .=  " order by need_score asc ";
				break;
				case 'time_d'://时间新
					$condition .=  " order by createtime desc ";
				break;
				case 'time_a'://时间旧
					$condition .=  " order by createtime asc ";
				break;
				default:
					$condition .=  " order by asort_value desc ";
				break;
				
			}
		
		/*********排序条件end********/		
				
		$query .= $condition." ,id desc"." limit ".$start." , ".$end."" ;	//合并
		//var_dump($query);exit;
		$redisKey = md5($query.'result');
		$result = $redis->get($redisKey);
		if( $result ){
			$rtn_data = unserialize($result);
			$out = json_encode($rtn_data);
			echo $out;exit;
		}
		// echo $query;
				$pro_id				= '';//产品ID
				$pro_name 			= '';//产品名称
				$default_imgurl 	= '';//默认图片
				$pro_supply_id 		= '';//供应商ID
				$orgin_price 		= '';//原价
				$now_price 			= '';//现价
				$isvp 				= '';//是否VP产品
				$isscore			= '';//是否积分专区产品
				$vp_score 			= '';//VP值
				$p_cashback 			= -1;//返现金额
				$p_cashback_r 		= -1;//返现比例
				$show_sell_count	= '';//虚拟销售量
				$is_free_shipping	= '';//是否包邮，1是，0否
				$need_score			=0;  //兑换积分
				$sell_count			=0;  //真实销量
				$back_currency		=0;//返的购物币
				$is_currency		=0;//是否返购物币
				$tax_type 			=1;//1普通产品，2跨境零售，3海外直邮，4国内代发，5海外集货
				$propertyids		= '';//产品属性
				$storenum 			=0;//库存
				$is_QR				=0;//是否二维码产品
				$is_virtual			=0;//是否虚拟产品
				$pro_card_level_id	=0;//购买产品需要会员卡等级
				$islimit			=0;//是否限购产品
				$is_first_extend 	=0;//是否首次推广奖励产品

				/*$result=_mysql_query($query)or die('L41 Query failed'.mysql_error());
				while($row=mysql_fetch_object($result)){*/
                $result = redis_select("product_list_{$customer_id}", $query);
                foreach ($result as $key => $row) {
                    
                    $pro_id		  	  = $row->id;
					$pro_name		  = $row->name;
					$default_imgurl   = $row->default_imgurl;
					$pro_supply_id 	  = $row->is_supply_id;
					$orgin_price 	  = $row->orgin_price;
					$now_price 		  = $row->now_price;
					$isvp 			  = $row->isvp;
					$isscore		  = $row->isscore;
					$vp_score 		  = $row->vp_score;
					$p_cashback 	  = $row->cashback;
					$p_cashback_r	  = $row->cashback_r;
					$show_sell_count  = $row->show_sell_count;
					$is_free_shipping = $row->is_free_shipping;
					$need_score 	  = $row->need_score;
					$sell_count 	  = $row->sell_count;  
					$back_currency 	  = $row->back_currency;
					$is_currency 	  = $row->is_currency;
					$tax_type 		  = $row->tax_type;
					$privilege_level  = $row->privilege_level;
					$is_privilege 	  = $row->is_privilege;
					$propertyids 	  = $row->propertyids;
					$storenum 	  	  = $row->storenum;
					$is_QR			  = $row->is_QR;
					$is_virtual 	  = $row->is_virtual;
					$pro_card_level_id= $row->pro_card_level_id;
					$islimit		  = $row->islimit;
					$is_first_extend  = $row->is_first_extend;

					switch ($tax_type) {
						case '1':
							$tax_name = "1";
							break;
						case '2':
							$tax_name = "跨境零售";
							break;
						case '3':
							$tax_name = "国内代发";
							break;
						case '4':
							$tax_name = "海外集货";
							break;
						case '5':
							$tax_name = "海外直邮";
							break;
					}
					// //是否特权产品
					// if($privilege_level=='0_1_2_3_4_5' || $privilege_level==""){
					// 	$is_privilege = 0;
					// }else{
					// 	$is_privilege = 1;
					// }

                    /* 查找产品购物币抵扣比例 */
                    $query2 = "select currency_percentage from commonshop_product_discount_t where isvalid=true and pid=".$pro_id." limit 0,1";
                    $result2 = _mysql_query($query2) or die('Query failed: ' . mysql_error());
                    $currency_percentage = -1;//默认全局
                    while ($row2 = mysql_fetch_object($result2)) {
                        $currency_percentage = $row2->currency_percentage;//产品购物币抵扣比例	
                    }
                    if($currency_percentage<0){
                        $currency_percentage = $percentage;
                    }
                    
                    $currency_price = $currency_percentage*$now_price;
                    $currency_price = bcadd($currency_price,0,2);//保留两位小数，不四舍五入
					$product_data=array();
					$product_data['pro_id']           = $pro_id;
					$product_data['pro_name']         = $pro_name;
					$product_data['default_imgurl']   = $default_imgurl;
					$product_data['pro_supply_id']    = $pro_supply_id;
					$product_data['orgin_price']      = $orgin_price;
					$product_data['now_price']        = $now_price;
					$product_data['isvp']             = $isvp;
					$product_data['isscore']          = $isscore;
					$product_data['vp_score']         = $vp_score;
					/* $product_data['cashback']      = $cashback;
					$product_data['cashback_r']       = $cashback_r; */
					$product_data['show_sell_count']  = $show_sell_count+$sell_count;
					$product_data['cb_condition']     = $cb_condition;
					$product_data['is_free_shipping'] = $is_free_shipping;
					$product_data['need_score']       = $need_score;
					$product_data['tax_name']         = $tax_name;
					$product_data['is_privilege']     = $is_privilege;
					$product_data['isOpenCurrency']   = $isOpenCurrency;
					$product_data['currency_price']   = $currency_price;
					$product_data['propertyids']      = $propertyids;
					$product_data['storenum']         = $storenum;
					$product_data['is_QR']         	  = $is_QR;
					$product_data['is_virtual']       = $is_virtual;
					$product_data['pro_card_level_id']= $pro_card_level_id;
					$product_data['islimit']		  = $islimit;
					$product_data['is_first_extend']  = $is_first_extend;

					$isbrand=-1;//品牌供应商标识
					if($pro_supply_id>0){
						$brand_id=-1;
						$brand_query="select id from weixin_commonshop_brand_supplys where isvalid=true and brand_status=1 and user_id=".$pro_supply_id." limit 0,1";
						$brand_result=_mysql_query($brand_query) or die ('brand_query faild:' .mysql_error());
						while($row=mysql_fetch_object($brand_result)){
							$brand_id=$row->id;
						}
						if($brand_id>0){
							$isbrand=1; //是品牌供应商
						}	
					}
					$product_data['isbrand'] 	= $isbrand;
					
					if($orgin_price >0 && $now_price>0){
						$discount=$now_price/$orgin_price;
						$discount=round($discount ,2)*10;	
					}else{
						$discount=0;
					}
					$product_data['discount']= $discount;
					

					//判断产品是否为返购物币
					//不属于返购物币产品的话，返购物币显示为0
					if($is_currency){
						$product_data['back_currency']= $back_currency;
						$product_data['custom']= $custom;
					}else{
						$product_data['back_currency']=0;
					}
					
					
					if($is_cashback == 1){ //开启了消费返现
						$product_data['is_cashback']= 1;
						
					}
					/*返现金额开始*/
					$p_now_price=$now_price;
					$showAndCashback = $info->showCashback($customer_id,$user_id,$p_cashback,$p_cashback_r,$p_now_price);
					$product_data['pro_cash_money']=$showAndCashback['cashback_m'];
					$product_data['display']=$showAndCashback['display'];			
					//$product_data['query']=$brand_query;
					
					/* 查询是否预配送产品 */
					$query_delivery = "SELECT p.delivery_id, a.delivery_name FROM weixin_commonshop_pre_delivery_product_relation p INNER JOIN weixin_commonshop_pre_delivery a ON p.delivery_id=a.id WHERE p.pid=".$pro_id." AND p.isvalid=TRUE AND p.customer_id=".$customer_id." AND a.isvalid=TRUE";
					$result_delivery = _mysql_query($query_delivery) or die('Query_delivery failed:'.mysql_error());
					while( $row_delivery = mysql_fetch_object($result_delivery) ){
						$delivery_id = $row_delivery -> delivery_id;
						$delivery_name = $row_delivery -> delivery_name;
					}
					$product_data['delivery_id'] = $delivery_id;
					$product_data['delivery_name'] = $delivery_name;
					/* 查询是否预配送产品 */

					/* 查询批发属性 */
					$is_wholesale = 0;
					$wholesale_id = -1;
					$wholesale_parentid = "";
					$wholesale_childid = "";
					$query_wholesale = "SELECT id,wholesale_parentid,wholesale_childid FROM weixin_commonshop_product_extend WHERE isvalid=true AND customer_id=$customer_id AND pid=$pro_id LIMIT 1";
					//echo $query;
					$result_wholesale= _mysql_query($query_wholesale) or die('Query failed 1309: ' . mysql_error());
					while( $row_wholesale = mysql_fetch_object($result_wholesale) ){
						$wholesale_id 		= $row_wholesale->id;
						$wholesale_parentid = $row_wholesale->wholesale_parentid;
						$wholesale_childid 	= $row_wholesale->wholesale_childid;
					}
					if($wholesale_id > 0){
						$is_wholesale = 1;//判断是否拥有批发属性
						$parent_id = -1;
						$parent_name = "";
						$sql_wholesale2 = "SELECT id,name FROM weixin_commonshop_pros WHERE isvalid=true AND id=$wholesale_parentid AND parent_id=-1 AND is_wholesale=1 LIMIT 1";
						$res_wholesale2 = _mysql_query($sql_wholesale2) or die('Query failed 1320: ' . mysql_error());
						while( $info_wholesale2 = mysql_fetch_object($res_wholesale2) ){
							$parent_id = $info_wholesale2->id;
							$parent_name = $info_wholesale2->name;
						}
						$wholesale_arr = array();
						$wholesale_arr = explode("_",$wholesale_childid);
						for($i=0;$i<count($wholesale_arr);$i++){

							$child_id = -1;
							$child_name = "";
							$wholesale_num = 1;
							$query_child = "SELECT id,name,wholesale_num FROM weixin_commonshop_pros WHERE isvalid=true AND parent_id=$parent_id AND id=$wholesale_arr[$i]";
							$result_child= _mysql_query($query_child) or die('Query failed 1335: ' . mysql_error());
							while( $info_child = mysql_fetch_object($result_child) ){
								$child_id = $info_child->id;
								$child_name = $info_child->name;
								$wholesale_num = $info_child->wholesale_num;
							}
						}
					}
					$product_data['is_wholesale'] = $is_wholesale;
					$product_data['wholesale_num'] = $wholesale_num;
					/* 查询批发属性 */

					//预到货
					$aog_id = -1;
					$is_aog = 0;		//是否预到货产品
					$is_available = 0;	//是否有货
					$query_aog = "SELECT id FROM aog_products_t WHERE pid=".$pro_id." AND customer_id=".$customer_id." AND isvalid=true";
					$result_aog = _mysql_query($query_aog) or die('Query_aog failed:'.mysql_error());
					while ( $row_aog = mysql_fetch_object($result_aog) ) {
						$aog_id = $row_aog -> id;
					}
					if ($aog_id>0){
						$is_aog = 1;
					}
					$product_data['is_aog'] = $is_aog;

					/* 检测是否符合首次推广奖励 start */
					$my_parent_id		= -1;
					if(!empty($_POST['my_parent_id'])){
						$my_parent_id = $configutil->splash_new($_POST['my_parent_id']);
					}
					$extend_id = -1;
					if($user_id>0) {
					    //上级是否已获得该产品的首次推广奖励
					    $query_extend = "select id from weixin_commonshop_extend_logs where user_id=".$my_parent_id." and p_id=".$pro_id." and customer_id=".$customer_id." and isvalid=true";
					    $result_extend = _mysql_query($query_extend) or die('Query_extend failed:'.mysql_error());
					    while( $row_extend = mysql_fetch_object($result_extend) ){
					        $extend_id = $row_extend->id;
					    }
					}
					$product_data['extend_id'] = $extend_id;
					/* 检测是否符合首次推广奖励 end */

					array_push($rtn_data,$product_data);
				
				}
			$redis->set($redisKey, serialize($rtn_data), 60);
			if(!$rtn_data){
				$rtn_data = [];
			}
			$out = json_encode($rtn_data); 
			echo $out;
		
	break;
	
	case 'get_type':
		//type_tys 1平台的分类 2品牌代理商分类	
		/*if(!empty($_POST['type_tys'])){
			$type_tys = $configutil->splash_new($_POST['type_tys']);
			
		}*/
		
		$res_type 	  = array();
		$level_1_type = array();	//一级分类
		$level_2_type = array();	//二级分类	
		
		//V7.0分类新排序
		$sort_str="";
		$type_sort="select sort_str from weixin_commonshop_type_sort where customer_id=".$customer_id."";
		$result_type=_mysql_query($type_sort) or die ('type_sort faild' .mysql_error());
		while($row=mysql_fetch_object($result_type)){
		   $sort_str=$row->sort_str;									   
		}
		
		
		
		$query = "select id,name,parent_id,sendstyle,is_shelves,create_type,is_privilege from weixin_commonshop_types where isvalid=true and is_shelves=1 and customer_id=".$customer_id." and parent_id=-1 ";		
		//echo $query;
		if($sort_str){
			$query =$query.' order by field(id'.$sort_str.')';  
		}
		
		$pt_id 			= -1;
		$pt_name 		= '';
		$pt_parent_id 	= '';
		$pt_sendstyle 	= '';
		$create_type 	= '';
		$is_privilege   =  0;
		
		$result=_mysql_query($query)or die('Query failed'.mysql_error());
		while($row=mysql_fetch_object($result)){
			   $pt_id 			= $row->id;
			   $pt_name 		= $row->name;
			   $pt_parent_id 	= $row->parent_id;
			   $pt_sendstyle	= $row->sendstyle;
			   $pt_is_shelves	= $row->is_shelves;
			   $create_type 	= $row->create_type;
			   $is_privilege    = $row->is_privilege;
			
			   
			   $level_1_type['pt_id']   = $pt_id;
			   $level_1_type['pt_name'] = $pt_name;
			   $level_1_type['is_privilege'] = $is_privilege;
			   
			   if($pt_id>0){
				   
						$query_child = "select id,name,parent_id,sendstyle,is_shelves,create_type,asort from weixin_commonshop_types where isvalid=true and customer_id=".$customer_id." and parent_id=".$pt_id."";
		
						$pc_id 			= -1;
						$pc_name 		= '';
						$pc_sort 		= '';
						$pc_parent 		= '';
						$pc_is_shelves 	= '';
						$pc_create_type = '';
						$level_2_type_temp = array();
						
						$result_child = _mysql_query($query_child) or die("L279 : Query failed : ".mysql_error());						
						while($row_child = mysql_fetch_object($result_child)){
								$pc_id 				= $row_child->id;
								$pc_name 			= $row_child->name;
								$pc_sort 			= $row_child->asort;
								$pc_parent 			= $row_child->parent_id;
								$pc_is_shelves 		= $row_child->is_shelves;
								$pc_create_type 	= $row_child->create_type;
								
								 $level_2_type['pc_id']   = $pc_id;
								 $level_2_type['pc_name'] = $pc_name;
								 $level_2_type['pc_parent'] = $pc_parent;
								 
								array_push($level_2_type_temp,$level_2_type);
						}
						
						
			   }	
						//组装
						$level_1_type['child_types'] = $level_2_type_temp;
						//var_dump($level_1_type);
						array_push($res_type,$level_1_type);
		}

		//【队列活动专区】 获取当前活动的关联产品的所有分类 2018/4/19
		if(!empty($_POST['isqueue']) && $_POST['isqueue']){
			//当前活动关联的产品id
			$queue_sql = "SELECT p.pid FROM ".WSY_MARK.".weixin_commonshop_queue as q left join ".WSY_MARK.".weixin_commonshop_queue_product as p on q.id = p.queue_id WHERE q.isvalid = true and q.customer_id = '".$customer_id."' and q.isout = '1' and p.isvalid = true";
			$queue_result = _mysql_query($queue_sql) or die ("queue_sql faild " .mysql_error());
			$queue_pro = array();
			while($pid_row=mysql_fetch_object($queue_result)){
				$queue_pro[] = $pid_row->pid;
			}
			$queue_pro = implode(",",$queue_pro);
			$queue_pro_sql = " and id in(".$queue_pro.")";
			//当前活动关联的产品id end
			
			//当前活动产品 所有分类id
			$type_sql = "SELECT type_ids from weixin_commonshop_products where isvalid = true and isout = 0 and customer_id = '".$customer_id."'".$queue_pro_sql;
			$type_result = _mysql_query($type_sql) or die ("type_sql faild " .mysql_error());
			$queue_type = '';
			while($type_row=mysql_fetch_object($type_result)){
				$queue_type .= ltrim($type_row->type_ids, ',');
			}
			$queue_type = trim($queue_type, ',');
			$queue_type = explode(",",$queue_type);

			//去重
			$queue_type = array_flip($queue_type);
			$queue_type = array_flip($queue_type);
			$queue_type = array_values($queue_type);
			//去重 end
			
			$queue_type = implode(",",$queue_type);
			$queue_type_sql = " and id in(".$queue_type.")";
			//当前活动产品 所有分类id end

			// _file_put_contents("list_check_".date("Y_m_d").".txt", "[query_card]============".$type_sql."=============\r\n",FILE_APPEND);
			$res_type 	  = array();
			$level_1_type = array();	//一级分类
			$level_2_type = array();	//二级分类

			$level_1_type['pt_id']   = 0;
		    $level_1_type['pt_name'] = '队列活动专区分类';
		    $level_1_type['is_privilege'] = 0;

		    $query_child = "SELECT id,name,parent_id,sendstyle,is_shelves,create_type,asort from weixin_commonshop_types where isvalid = true and customer_id = '".$customer_id."'".$queue_type_sql;

			$level_2_type_temp = array();
			
			$result_que = _mysql_query($query_child) or die("L279 : Query failed : ".mysql_error());						
			while($row_que = mysql_fetch_object($result_que)){
				$level_2_type['pc_id']		= $row_que->id;
				$level_2_type['pc_name'] 	= $row_que->name;
				$level_2_type['pc_parent'] 	= $row_que->parent_id;
				
				array_push($level_2_type_temp,$level_2_type);
			}
			//组装
			$level_1_type['child_types'] = $level_2_type_temp;
			array_push($res_type,$level_1_type);
		}
		//【队列活动专区】  end	
		
		$out = json_encode($res_type);
		echo $out;
		
	break;	
	//查询分类是否属于特权分类
	case 'seach_pro_type':

		$type_id = $configutil->splash_new($_POST["type_id"]);
		$is_privilege_type = 0;
		$query = "SELECT is_privilege FROM weixin_commonshop_types WHERE isvalid=true AND customer_id=$customer_id AND id=$type_id";
		$result= _mysql_query($query) or die ('query faild 529' .mysql_error());
		while( $row = mysql_fetch_object($result) ){
			$is_privilege_type = $row->is_privilege;
		}

		echo json_encode($is_privilege_type);

	break;
}

//redis查询
function redis_select($key, $query, $is_change=false){
	global $redis;
    
    if (empty($redis)) {
        global $redis_config;
        $redis = new RedisClient($redis_config);
    }
    
	if(empty($query)){
		die(array('errcode'=>400,'msg'=>'query为空'));
	}
	
	$result = $redis->get($key);
    $result = unserialize($result);
	
	if(empty($result) or empty($result[md5($query)])){
        $new_result = [];
		$res = _mysql_query($query) or die('Query redis_select: ' . mysql_error());
		while ($row = mysql_fetch_object($res)) {
			$new_result[] = $row;
		}
        $result[md5($query)] = $new_result;
		$redis->set($key, serialize($result), 60);
		
        if ($is_change) {
            $result = redis_object_to_array($new_result);
        } else {
            $result = $new_result;
        }
		
	} else {
        if ($is_change) {
            $result = redis_object_to_array($result[md5($query)]);
        } else {
            $result = $result[md5($query)];
        }
    }
    
	return $result;
	
}

// 对象转数组
function redis_object_to_array($d) {
    if (is_object($d)) {
        $d = get_object_vars($d); //将第一层对象转换为数组
    }

    if (is_array($d)) {
        return array_map(__FUNCTION__, $d); //如果是数组使用array_map递归调用自身处理数组元素
    } else {
        return $d;
    }
}

// 模糊查询字段处理		2018.2.27
function fuzzy_search($string){

	$string = strip_tags($string);	//过滤html标签
	$regex  = "/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
    $string = preg_replace($regex,'',$string);		//过滤特殊字符号
    $string = preg_replace("/\s+/",'',$string);

    $length = mb_strlen($string,'utf-8');

    $arr = array();
    for ($i=0; $i<$length; $i++){  
        $arr[] = mb_substr($string, $i, 1, 'utf-8');
    }

    $searchKey = implode('%',$arr);

    return $searchKey;
}

?>