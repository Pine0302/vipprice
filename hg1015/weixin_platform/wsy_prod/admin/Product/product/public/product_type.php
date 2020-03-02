<script type="text/javascript">
	var type_parent = new Array();
	var type_children = new Array();

	var product_tprice = new Array();
	var product_price = new Array();
	
	var search_type_id = '<?php echo $search_type_id;?>';		
</script>
<!-- 修改产品分类 begin -->
	<div id="div_out">
		<div id="div_inner">
			<img src="../../Common/images/Product/shanchuicon.png" style="width:25px;height:25px;border-radius: 5px;"/>
		</div>
		<div class="div_out_title">
			<font style="font-weight: bold;">产品名称：</font><span id="span_out_title">新款秋装针织衫M</span>
		</div>
  
		<div class="div_out_item WSY_ddbulk" style="width: 95%;"> 
	
			<?php
				$query_type_parent = "select id,name from weixin_commonshop_types where isvalid = true and customer_id = ".$customer_id." and parent_id = -1";
				$result_type_parent = _mysql_query($query_type_parent) or die("L161 query error : ".mysql_error());
				while($row_type_parent = mysql_fetch_object($result_type_parent)){ 
					$ptid = $row_type_parent->id;
					$ptname = $row_type_parent->name;
				?>
				<script type="text/javascript">
				type_parent.push(['<?php echo $ptid;?>','<?php echo $ptname;?>']);
				type_children['<?php echo $ptid;?>'] = new Array();
				</script>
				<?php 
					$query_type_child = "select id,name from weixin_commonshop_types where isvalid = true and customer_id = ".$customer_id." and parent_id = ".$ptid;
					$result_type_child = _mysql_query($query_type_child) or die("L169 query error : ".mysql_error());
					$childs = mysql_num_rows($result_type_child);
				?>
				<div class="WSY_divbulk">
                	<h1 class="noeh1">
						<?php if($childs <= 0){ ?>
							<input type="checkbox" name="types" value="<?php echo $ptid;?>" id="ck_p<?php echo $ptid;?>" 
							>
						<?php }?><label for="ck_p<?php echo $ptid;?>"><?php echo $ptname;?></label>
                    </h1>
					<?php if($childs > 0){?>
                    <ul class="twoul">
						<?php
						while($row_type_child = mysql_fetch_object($result_type_child)){ 
							$pcid = $row_type_child->id;
							$pcname = $row_type_child->name;
						?>
						<script type="text/javascript">
							type_children['<?php echo $ptid;?>'].push(['<?php echo $pcid;?>','<?php echo $pcname;?>']);	
						</script>
                    	<li><input type="checkbox" name="types" value="<?php echo $pcid;?>" id="ck_p<?php echo $ptid;?>_c<?php echo $pcid;?>"
						>
						<label for="ck_p<?php echo $ptid;?>_c<?php echo $pcid;?>" >
						<?php echo $pcname;?></label></li>
                        <?php 
					}?>  
                    </ul>
					<?php 
					}?>
                </div>
			<?php }?>
		
		
		</div>	
		<input type="hidden" value="-1" id="hid_out_proid"/>
		<input type="button" value="确定修改" id="btn_changetype" class="div_out_btn"/>
			
	</div>
<!-- 修改产品分类 end -->
<!-----------------------------------------分割线-------------------------------------------->
<!-- 修改产品属性 begin -->
	
	<?php
	//查询购物币开关是否开启 start
		$currency_sql    = "SELECT is_rebate_open FROM ".WSY_SHOP.".weixin_commonshop_currency where isvalid=true and customer_id='".$customer_id."'";
		$currency_result = _mysql_query($currency_sql) or die('Query failed 195: '.mysql_error());
		while ($row_currency = mysql_fetch_object($currency_result)) {
			$is_rebate_open  = $row_currency->is_rebate_open;
		}
	//查询购物币开关是否开启 end
	//查询区块链积分名称 start
		$block_sql = "SELECT name FROM ".WSY_SHOP.".block_chain_setting where customer_id=".$customer_id;
   	    $block_result = _mysql_query($block_sql) or die('L268 : Query failed: ' . mysql_error());
   	    $block_chain_name ="";
   	    while ($block_row = mysql_fetch_object($block_result)) {
   	     	$block_chain_name = $block_row->name;
   	    }   	
	//查询区块链积分名称 end
	?>
	
	<div id="div_out1">
		<div id="div_inner1">
			<img src="../../Common/images/Product/shanchuicon.png" style="width:25px;height:25px;border-radius: 5px;"/>
		</div>
		<div class="div_out_title">
			<font style="font-weight: bold;">产品名称：</font><span id="span_prop_title">新款秋装针织衫M</span>
		</div>
		<div class="div_out_item">
			<input id="check_out" type="checkbox" name="ck_props" value="1" />
			<label for="check_out">下架</label>
		</div>	
		<div class="div_out_item">
			<input id="check_new" type="checkbox" name="ck_props" value="1" />
			<label for="check_new">新品</label>
		</div>
		<div class="div_out_item">
			<input id="check_hot" type="checkbox" name="ck_props" value="1" />
			<label for="check_hot">热卖</label>
		</div>
		<div class="div_out_item">
			<input id="check_snapup" type="checkbox" name="ck_props" value="1" />
			<label for="check_snapup">抢购</label>
		</div>
		<div class="div_out_item">
			<input id="check_vp" type="checkbox" name="ck_props" value="1" />
			<label for="check_vp">vp产品</label>
		</div>
		<div class="div_out_item">
			<input id="check_virtual" type="checkbox" name="ck_props" value="1" />
			<label for="check_virtual">虚拟产品</label>
		</div>
		<?php if($is_rebate_open==1){ ?>
		<div class="div_out_item">
			<input id="check_currency" type="checkbox" name="ck_props" value="1" />
			<label for="check_currency"><?php echo defined('PAY_CURRENCY_NAME') ?PAY_CURRENCY_NAME: '购物币'; ?>产品</label>
		</div>
		<?php } ?>
		<div class="div_out_item">
			<input id="check_guess" type="checkbox" name="ck_props" value="1" />
			<label for="check_guess">猜您喜欢产品</label>
		</div>
		<div class="div_out_item">
			<input id="check_freeshipping" type="checkbox" name="ck_props" value="1" />
			<label for="check_freeshipping">包邮</label>
		</div>
		<div class="div_out_item">
			<input id="check_score" type="checkbox" name="ck_props" value="1" />
			<label for="check_score">兑换专区</label>
		</div>
		<div class="div_out_item">
			<input id="check_limit" type="checkbox" name="ck_props" value="1" />
			<label for="check_limit">限购</label>
		</div>
		<div class="div_out_item">
			<input id="check_extend" type="checkbox" name="ck_props" value="1" />
			<label for="check_extend">首次推广奖励</label>
		</div>
		<div class="div_out_item">
			<input id="check_tax" type="checkbox" name="ck_props" value="1" />
			<label for="check_tax">税收产品</label>
		</div>
		<div class="div_out_item">
			<input id="check_link_package" type="checkbox" name="ck_props" value="1" />
			<label for="check_link_package">关联礼包</label>
		</div>
		<div class="div_out_item">
			<input id="check_mini_mshop" type="checkbox" name="ck_props" value="1" />
			<label for="check_mini_mshop">微信小程序</label>
		</div>
		<div class="div_out_item">
			<input id="check_block_chain" type="checkbox" name="ck_props" value="1" />
			<label for="check_block_chain"><?php echo $block_chain_name;?></label>
		</div>
		<?php

			//股东比例
			$a_name	= "";	//代理
			$b_name	= "";	//渠道
			$c_name	= "";	//总代
			$d_name	= "";	//股东	
			$query_shareholder = "SELECT a_name,b_name,c_name,d_name FROM weixin_commonshop_shareholder where isvalid=true and customer_id=".$customer_id;
			$result_shareholder = _mysql_query($query_shareholder);
			while($row = mysql_fetch_assoc($result_shareholder)){
				$a_name = mysql_real_escape_string($row['a_name']);
				$b_name = mysql_real_escape_string($row['b_name']);
				$c_name = mysql_real_escape_string($row['c_name']);
				$d_name = mysql_real_escape_string($row['d_name']);
				
			}
			$exp_name = "推广员";
			$query ="select exp_name from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
			$result = _mysql_query($query) or die('Query failed: ' . mysql_error());
			while ($row = mysql_fetch_object($result)) {
			   $exp_name 			 = $row->exp_name;
			}


		?>
		<div class="div_out_item">
			<input id="check_privilege" type="checkbox" name="ck_props" value="1" onclick="change_privilege(this);"/>
			<label for="check_privilege">特权产品</label>
		</div>
		
		<div id="privilege_list" style="display:none;">
			<span style="float:left;">特权身份：</span>
			<ul style="float:left;">
				<li style="float:left;margin-left:5px;"><input id="privilege_1" type="checkbox" name="privilege[]" value="1" /><?php echo $exp_name;?></li>
				<li style="float:left;margin-left:5px;"><input id="privilege_2" type="checkbox" name="privilege[]" value="2" /><?php echo $d_name;?></li>
				<li style="float:left;margin-left:5px;"><input id="privilege_3" type="checkbox" name="privilege[]" value="3" /><?php echo $c_name;?></li>
				<li style="float:left;margin-left:5px;"><input id="privilege_4" type="checkbox" name="privilege[]" value="4" /><?php echo $b_name;?></li>
				<li style="float:left;margin-left:5px;"><input id="privilege_5" type="checkbox" name="privilege[]" value="5" /><?php echo $a_name;?></li>
			</ul>
		</div>

		<div class="div_out_item02" id="div_block_chain_type" style="display:none;">
			<label>赠送区块链积分：</label>
			<ul>
			<li>
			<input type="radio" name="block_chain_type" id="block_type_1" value="1" <?php if(1==$block_chain_type){echo 'checked=checked';}?> >
			<label>产品现价X</label><input id="block_chain_bfb" type="text" name="block_chain_bfb" value="" style="width:14%;border:1px solid #ccc;" />%
		    </li>
		    <li>
			<input type="radio" name="block_chain_type" id="block_type_2" value="2" <?php if(2==$block_chain_type){echo 'checked=checked';}?> >
			<label>固定金额 </label><input id="block_chain_money" type="text" name="block_chain_money" value="" style="width:14%;border:1px solid #ccc;" />
			</li>
		    </ul>
		</div>




		<div class="div_out_item02" id="div_type_vp" style="display:none;">
			<label>VP值：</label>
			<input id="vp_text" type="text" name="vp_text" value="0" style="width:14%;border:1px solid #ccc;" />
		</div>
		<?php if($is_rebate_open==1){ ?>
		<div class="div_out_item02" id="div_type_currency" style="display:none;">
			<label>返佣<?php echo defined('PAY_CURRENCY_NAME') ?PAY_CURRENCY_NAME: '购物币'; ?>：</label>
			<input id="currency_text" type="text" name="currency_text" value="0" style="width:14%;border:1px solid #ccc;" />
		</div>
		<?php } ?>
		<div class="div_out_item02" id="div_type_link_package" style="display:none;">
			<label>关联礼包：</label>
			<select id="link_package_text" name="link_package">
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
		</div>
		<div class="div_out_item02" id="div_type_package_img"style="display:none;">
			<label>上传礼包关联图：</label>
			<form enctype="multipart/form-data" method="post" id="uploadForm" name="uploadForm">
			<input size="17" name="package_img" id="img_package" class="upfile" type=file value="">
			<input type="submit" id="file_button" name="file_button" class="file_button" style="background-color: rgb(6, 167, 225);color: #f9fdff;border-radius: 2px;border: solid 1px rgb(6, 167, 225);text-align: center;cursor:pointer;" value="确定此图">
			<input type=hidden value="" name="link_package_img" id="hidden_img"/>
			<span>支持格式：JPG、JPEG、PNG、JIF，图片大小：小于100K，图片宽度：640，高度不限</span>
			</form>
			<img id="package_imgurl" name="packageimg" src="" width=320 />   
		</div>
		<div class="div_out_item02" id="div_type_starttime"style="display:none;">
			<label>抢购开始时间：</label>
			<input id="buystart_time" type="text" name="buystart_time" value="" onclick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm'});" style="width:14%;border:1px solid #ccc;" />
		</div>
		<div class="div_out_item02" id="div_type_endtime"style="display:none;">
			<label>抢购结束时间：</label>
			<input id="countdown_time" type="text" name="countdown_time" value="" onclick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm'});" style="width:14%;border:1px solid #ccc;" />
		</div>
		<div class="div_out_item02" id="div_type_limit" style="display:none;">
			<label>限购数量：</label>
			<input id="limit_text" type="text" name="limit_text" value="0" style="width:14%;border:1px solid #ccc;" />
		</div>
		<div class="div_out_item02" id="div_type_extend" style="display:none;">
			<label>首次推广奖励金额：</label>
			<input id="extend_text" type="text" name="extend_text" value="0" style="width:14%;border:1px solid #ccc;" />
		</div>
		<div class="div_out_item" id="div_type_tax" style="display:none;">
			<div style="margin-bottom:20px;">
			<label>税率模板：</label>
			<select name="tax_id2" id="tax_sel2" onclick='change_tax2();' style="width:14%;height:20px;">
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
		<div style="margin-bottom:20px;">
			<label>税收标签：</label>
			<select name="tax_type2" id="tax_type2" style="width:18%;height:20px;">
				<option value="2" >跨境零售</option>
				<option value="3" >国内代发</option>
				<option value="4" >海外集货</option>
				<option value="5" >海外直邮</option>
			</select>
		</div>
		<div style="margin-bottom:20px;">
			<dd style="margin-bottom:20px;">
				<label>关税税率：</label>
				<input type="text" value="" name="tariff2" id="tariff2" style="width:14%;border:1px solid #ccc;">%
			</dd>
			<dd style="margin-bottom:20px;">
				<label>消费税税率：</label>
				<input type="text" value="" name="comsumption2" id="comsumption2" style="width:14%;border:1px solid #ccc;">%
			</dd>
			<dd style="margin-bottom:20px;">
				<label>增值税税率：</label>
				<input type="text" value="" name="addedvalue2" id="addedvalue2" style="width:14%;border:1px solid #ccc;">%
			</dd>
			<dd style="margin-bottom:20px;">
				<label>行邮税率：</label>
				<input type="text" value="" name="postal2" id="postal2" style="width:14%;border:1px solid #ccc;">%
			</dd>
		</div>
		</div>
		<input type="hidden" value="-1" id="hid_prop_proid"/>
		<input type="button" value="确定修改" id="btn_changeprop" class="div_out_btn"/>
			
	</div>
<!-- 修改产品属性 end -->

<!-----------------------------------------分割线-------------------------------------------->



<!-- 修改产品价格 begin -->
	<div id="div_out2" style="display:none">
		<div id="div_inner2"> 
			<img src="../../Common/images/Product/shanchuicon.png" style="width:25px;height:25px;border-radius: 5px;"/>
		</div>
		<div class="div_out_title">
			<font style="font-weight: bold;">产品名称：</font><span id="span_price_title">新款秋装针织衫M</span>
		</div>
		<div class="div_out_item1" style="width: 515px;">
			<table style="width: 95%;" class="WSY_table tblcls" >
				<thead class="WSY_table_header"     style="background-color:  #666666;">
					<th width="15%">名称</th>
					<th width="12%">原价</th>
					<th width="12%">现价</th>
					<th width="12%">VIP价</th>
					<th width="16%">成本</th>
					<th width="16%">供货价</th>
						<th >所需积分</th>
				</thead>
				<tbody id="WSY_t1">
				</tbody>
			</table>
		</div>	
		<input type="hidden" value="-1" id="hid_price_proid"/>
		<input type="button" value="确定修改" id="btn_changeprice" class="div_out_btn"/>		
	</div> 
<!-- 修改产品属性 end -->

<!-- 修改产品库存 begin -->
	<div id="div_out3" style="display:none">
		<div id="div_inner3"> 
			<img src="../../Common/images/Product/shanchuicon.png" style="width:25px;height:25px;border-radius: 5px;"/>
		</div>
		<div class="div_out_title">
			<font style="font-weight: bold;">产品名称：</font><span id="span_stock_title">新款秋装针织衫M</span>
		</div>
		<div class="div_out_item1" style="width: 430px;">
			<table style="width: 95%;" class="WSY_table tblcls" >
				<thead class="WSY_table_header"     style="background-color:  #666666;">
					<th width="60%">名称</th>
					<th width="40%">库存</th>
				</thead>
				<tbody id="WSY_t2">
				</tbody>
			</table>
		</div>
		<input type="hidden" value="-1" id="hid_stock_proid"/>
		<input type="button" value="确定修改" id="btn_changestock" class="div_out_btn"/>		
	</div>
<!-- 修改产品库存 end -->
<!-- 4m产品控制权限 start -->
<!--<iframe src="./public/4m_control.php?customer_id=<?php echo $customer_id_en ;?>" id="iframe" scrolling="no" cid="<?php echo $customer_id_en ;?>"> 

</iframe>-->
 <div id="div_out4">
 
 </div>
<!-- 4m产品控制权限 end -->

<!-- 批量设置购物币可抵扣金额 start -->
 <div id="div_out6">
    <div class="div_out6_title">
        <span style="font-size:18px">批量设置<?php echo defined('PAY_CURRENCY_NAME') ?PAY_CURRENCY_NAME: '购物币'; ?>抵扣比例</span>
        <span style="color:red;float: left;margin-top: 10px;margin-bottom: 10px;">（-1为等同于全局比例，亦可填0-100的百分比数，最多可支持小数点后两位，如1.01%）</span>
    </div>
    <input type="text" value="-1" class="div_out6_input" id="currency_percentage" onkeyup="clearNoNumNew(this);" /><span style="font-size: 18px;float: left;margin-top: 4px;">%</span>
    <input type="button" value="确定" class="div_out6_btn1" />
    <input type="button" value="取消" class="div_out6_btn2" />
 </div>
<!-- 批量设置购物币可抵扣金额 end -->



