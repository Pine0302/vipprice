function addType(){
   var name=$("#name").attr("value");
   if($.trim(name)==""){
      alert('请输入名称');
      return;
   }
   $("#frm_producttype").submit();
}


function sel_sendtype(st){
	if(st==1){
		$("#pro-list-type li div:first-child").removeClass("item_on");
		$("#sendtype"+st).addClass("item_on");
	}else{
		$("#pro-list-type li div:first-child").removeClass("item_on");
		$("#sendtype"+st).addClass("item_on");
	}
		$("#sendstyle").attr("value",st);
}
function sel_sendtype2(st){
	if(st==1){
		$("#pro-list-type2 li div:first-child").removeClass("item_on");
		$("#sendtypes"+st).addClass("item_on");
	}else{
		$("#pro-list-type2 li div:first-child").removeClass("item_on");
		$("#sendtypes"+st).addClass("item_on");
	}
		$("#sendstyle2").attr("value",st);
}

function subPro(){
   var name = $("#name").attr("value");
   if($.trim(name)==""){
      alert('请输入属性名称');
	  return;
   }
   var pros = document.getElementsByName("PropertyList[]");
   var len = pros.length;
   var subpros="";
   for(i=0;i<len;i++){
       pro = pros[i];
	   var pv = pro.value;
	   if($.trim(pv)!=""){
	      pv = pv.replace("_","");
	      subpros = subpros+pv+"_";
	   }
   }

   if(subpros.length>0){
      subpros= subpros.substring(0,subpros.length-1);
   }
   document.getElementById("subpro").value=subpros;
   $("#frm_pro").submit();
}
var tradeprice_id = 1;
function addTradeprice(){


   var str = "<tr id=\"tradep_"+tradeprice_id+"\"><td>数量：<input type=\"text\" name=\"Qty[]\" value=\"\" class=\"form_input\" size=\"5\" maxlength=\"3\">";

   str = str +"价格：￥<input type=\"text\" name=\"Price[]\" value=\"\" class=\"form_input\" size=\"5\" maxlength=\"10\">";
   str = str +"<a href=\"javascript:removeTradeP("+tradeprice_id+");\">";
   str = str +"<img src=\"images/del.gif\" hspace=\"5\"></a>";
   str = str + "</td></tr>";
   $("#tb_tradeprice").append(str);
   tradeprice_id++;
}

function removeTradeP(id){
    $("#tradep_"+id).remove();
}

function saveProduct(e){

	var name = $("#name").val();
	var now_money = $('#now_money').val();
	var weiz_sort = $('#weiz_sort').val();
	var mess_num  = $('.diy_one_two').length;
	$("#mess_num").val(mess_num);
	selectText_last(); //保存详细介绍，产品规格，售后保障的最后一次修改
	if($.trim(name)==""){
		alert('请输入产品名称!');
		return;
	}

	var stu = check_proname();
	if(stu){
		alert('产品名称不能含有特殊字符串，请重新输入！');
		$('#name').val('');
		return;
	}
	if(now_money==""){
		alert('请输入产品现价!');
		return;
	}
   // var types = $("input[name='types']:checked");
   var types = $("#f-box .new_list");
   if(types.length == 0){
        alert('请选择产品隶属分类!');
       return;
   }
	if(isNaN(weiz_sort)){
		 alert('排序位置只能填写数字!');
		return;
	}
   var iscontinue = 1;
	//遍历原价是否有为空
	$('.orgin_price>.form_input ').each(function(){
		if($(this).val()==""){
			alert('原价不可为空!');
			iscontinue = 0;
			return false;
		}
	});
	if(!iscontinue){
		return;
	}
	//遍历现价是否有为空
	$('.now_price>.form_input ').each(function(){
		if($(this).val()==""){
			alert('现价不可为空!');
			iscontinue = 0;
			return false;
		}

		var pos_ul = $(this).parent().parent();

		if( parseFloat($(pos_ul).find('.now_price>.form_input ').val()) < parseFloat($(pos_ul).find('.base_price>.form_input').val())){
			alert('供货价不可大于现价!');
			iscontinue = 0;
			return false;
		}

		if( parseFloat($(pos_ul).find('.now_price>.form_input ').val()) > parseFloat($(pos_ul).find('.orgin_price>.form_input').val())){
			alert('现价不可大于原价!');
			iscontinue = 0;
			return false;
		}
	});
	if(!iscontinue){
		return;
	}
	if($('#is_link_package').attr('checked')){
		if($('#package_img').attr('src') == ''){
			alert('关联礼包图片不能为空');
			return false;
		}
	}
	//遍历成本是否有为空
	$('.for_price>.form_input ').each(function(){
		if($(this).val()==""){
			alert('成本不可为空!');
			iscontinue = 0;
			return false;

		}

		var pos_ul = $(this).parent().parent();
		if( parseFloat($(pos_ul).find('.for_price>.form_input ').val())  > parseFloat($(pos_ul).find('.now_price>.form_input').val()) ){
			alert('产品成本价不能大于现价!');
			iscontinue = 0;
			return false;
		}
	});
	$('.neet_score>.form_input ').each(function(){
		if($(this).val()==""){
			alert('所需积分不可为空!');
			iscontinue = 0;
			return false;
		}
	});
	$('.store_num>.form_input ').each(function(){
		if($(this).val()==""){
			alert('库存不可为空!');
			iscontinue = 0;
			return false;
		}
	});

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


	if(!iscontinue){
		return;
	}
	if(supply_id>0){
		//遍历供货价是否有为空
		$('.base_price>.form_input ').each(function(){
			if($(this).val()==""){
				alert('供货价不可为空!');
				iscontinue = 0;
				return false;
			}
			if($(this).val()<0){
				alert('供货价不可为负数!');
				iscontinue = 0;
				return false;
			}
			var pos_ul = $(this).parent().parent();
            var forprice = $(pos_ul).find('.for_price>.form_input ').val();
            var baseprice = $(pos_ul).find('.base_price>.form_input').val();
            if( parseFloat(forprice)<parseFloat(baseprice) && parseFloat(forprice) != 0 ){
                alert('成本不得小于供货价!');
                iscontinue = 0;
                return false;
            }

		});
		if(!iscontinue){
			return;
		}
	}
   /* var express_type = $("input[name='express_type']:checked");
   if(express_type.length == 0){
	   alert('请选择邮费计费方式!');
	   return;
   } */
   var typeids = "";
   for( var tsl=0;tsl<types.length;tsl++ ){
	   typeids +=",";
	   typeids += types.eq(tsl).data('type_id');
   }
   /*types.each(function(i,n){

	   //if(i > 0){
		   typeids+=",";

	   //}
	   typeids += n.value;
   });*/

 	typeids += ",";
	$("#type_ids").val(typeids);
   $("#type_id").val(types.eq(0).data('type_id'));
   // console.log(typeids);return;
   var ptids = document.getElementsByName("ptids");
   var pids = "";
   for(i=0;i<ptids.length;i++){
      var pobj = ptids[i];
	  if(pobj.checked){
	     var v = pobj.value;
		 pids = pids+v+"_";
	  }
   }
   if(pids.length>0){
       pids= pids.substring(0,pids.length-1);
   }
   document.getElementById("propertyids").value=pids;

   var qtys = document.getElementsByName("Qty[]");
   var len = qtys.length;
   var tradeprices="";
   if(len>0){
       var prices = document.getElementsByName("Price[]");
	   for(i=0;i<len;i++){
	      var qty = qtys[i].value;
		  var price = prices[i].value;
		  tradeprices=tradeprices+qty+","+price+"_"
	   }
   }
   if(tradeprices.length>0){
      tradeprices = tradeprices.substring(0,tradeprices.length-1);
   }
   document.getElementById("tradeprices").value=tradeprices;

   var imgs = $("#frmProImgs").contents().find(".imgPro");
   var imgPaths = ""; //图片路径
	if(imgs.length > 0){
		var len = imgs.length;
		for(var i = 0 ; i <= len-1 ; i++){
			var src = $(imgs[i]);
			var id = src.data("id");
			if(id =="" || id <= 0){
				if(imgPaths!=""){
					imgPaths+=";";
				}
				imgPaths += src.attr("src");
			}
		}
		/*imgs.each(function(i,n){
			var id = $(n).data("id");
			if(id !="" && id > 0){
				if(imgPaths!=""){
					imgPaths+=";";
				}
				imgPaths += $(n).attr("src");
			}
		});*/	}

  // var imgids = $("#imgids").attr("value");
   $("#imgids").val(imgPaths);

   var  proids = document.getElementsByName("proids");

   var pro_orgin_prices = document.getElementsByName("pro_orgin_price");
   var pro_now_prices = document.getElementsByName("pro_now_price");
   var pro_for_prices = document.getElementsByName("pro_for_price");
   var pro_storenums = document.getElementsByName("pro_storenum");
   var pro_need_scores = document.getElementsByName("pro_need_score");
   var pro_cost_prices = document.getElementsByName("pro_cost_price");
   var pro_foreign_marks = document.getElementsByName("pro_foreign_mark");
   //var pro_units = document.getElementsByName("pro_unit");
   var pro_weights = document.getElementsByName("pro_weight");
   var len = pro_orgin_prices.length;
   var str = "";
   var ordering_proids_last = '';
   var ordering_now_price_last = '';
   for(var i=0;i<len;i++){
       pro_orgin_price = pro_orgin_prices[i].value;
	   pro_for_price = pro_for_prices[i].value;
	   pro_now_price = pro_now_prices[i].value;
	   pro_storenum = pro_storenums[i].value;
	   pro_need_score = pro_need_scores[i].value;
	   pro_cost_price = pro_cost_prices[i].value;
	   //pro_unit = pro_units[i].value;
	   pro_unit = '个';
	   pro_weight = pro_weights[i].value;
	   // try{
			// if(parseFloat(pro_now_price) < parseFloat(pro_for_price)){
				  // alert("产品成本价不能大于售价");
				  // return;
			// }

		    // if(supply_id>0){

				// if( parseFloat(pro_for_price) > 0 &&  parseFloat(pro_cost_price) > 0  && parseFloat(pro_for_price) < parseFloat(pro_cost_price)){
				  // alert("成本价应设置大于供货价");
				  // return;
				// }
			// }


	   // }catch(e){}
	   pro_foreign_mark = "";
	   try{
	      pro_foreign_mark= pro_foreign_marks[i].value;
	   }catch(e){
	   }

	   proid = proids[i].value;
       ordering_proids_last += ','+proid;
       ordering_now_price_last += ','+pro_now_price;
	   var ts = proid+","+pro_orgin_price+"_"+pro_now_price+"_"+pro_storenum+"_"+pro_need_score+"_"+pro_cost_price+"_"+pro_foreign_mark+"_"+pro_unit+"_"+pro_weight+"_"+pro_for_price;
	   str = str +ts+"-";
   }
    if(ordering_proids_last != ''){
        ordering_proids_last = ordering_proids_last.substring(1);
    }
    if(ordering_now_price_last != ''){
        ordering_now_price_last = ordering_now_price_last.substring(1);
    }
   if(len==0){		//如果没有属性,则判断成本价是否为大于0,不是则不能提交保存
	  // var now_price = document.getElementsByName("now_price")[0].value;
	  // var cost_price = document.getElementsByName("cost_price")[0].value;
	  // var for_price = document.getElementsByName("for_price")[0].value;
		// if(parseFloat(now_price) < parseFloat(for_price)){
		  // alert("产品成本价不能大于售价");
		  // return;
		// }
		 // if(supply_id>0){

			// if( parseFloat(for_price) > 0 &&  parseFloat(cost_price) > 0  && parseFloat(for_price) < parseFloat(cost_price)){
				  // alert("成本价应设置大于供货价");
				  // return;
			// }
		// }
   }

   if(str!=""){
       str = str.substring(0,str.length-1);
   }

   document.getElementById("pro_price_detail").value=str;

   var define_share_image_flag = $("input[name='define_share_image_flag']:checked");
  console.log("define_share_image_flag : "+define_share_image_flag);
   if(define_share_image_flag &&  define_share_image_flag.val()!=0){//选择了自定义
		//alert('+++');
		var now_define_share_image = document.getElementById("now_define_share_image").value;
		var new_define_share_image = document.getElementById("new_define_share_image").value;
		//alert(now_define_share_image+'---------------'+new_define_share_image);
		if(now_define_share_image ==''&&new_define_share_image==''){
		alert('图片空');
		return;
		}
   }
   var show_sell_count = $("#show_sell_count").val();
   var rule =/^\d+$/;
   if(!rule.test(show_sell_count)){
	   alert("虚拟销售量输入不合法！");
	   return;
   }
  var buystart_time = document.getElementById("buystart_time").value;
	var countdown_time = document.getElementById("countdown_time").value;
	var issnapup = document.getElementById("issnapup").value;
	var buystart_time = Date.parse(new Date(buystart_time));
	var countdown_time = Date.parse(new Date(countdown_time));
	if(isNaN(buystart_time)){
		buystart_time = 0;
	}
	if(isNaN(countdown_time)){
		countdown_time = 0;
	}
	if(issnapup == 1){
		if(buystart_time<=0){
			alert('请选择正确的抢购开始时间！');
			return;
		}
		if(countdown_time<=0){
			alert('请选择正确的抢购结束时间！');
			return;
		}
		if(buystart_time >= countdown_time){
			alert('抢购开始时间不能大于等于抢购结束时间，请重新选取时间！');
			return;
		}
	}
	var islimit = document.getElementById("issnapup").value;
	var limit_num = document.getElementById("limit_num_val").value;
	if(islimit == 1){
		if(limit_num < 1){
			alert('限购数量不能小于0！');
			return;
		}
	}
   var is_Pinformation = $("#is_Pinformation").val()
   console.log(is_Pinformation);
   if(1==is_Pinformation){
	   var singletext_con = $('.singletext_con');
	   var singletext_con_len = singletext_con.length;
	   for(var i=0;i<singletext_con_len;i++){
		   if($.trim(singletext_con.eq(i).val())==''){
			   alert("必填信息不能为空！");
			   return;
		   }
	   }
   }
   var pro_reward = document.getElementById("pro_reward");
   if(pro_reward){
	   var cashback=pro_reward.value;
	   if(cashback==""){
		   alert("总佣金比例不能为空！");
		   return;
	   }
	   if( cashback < 0 && cashback != -1 ){
		   alert("总佣金比例不能等于"+cashback+"！");
		   return;
	   }
	   if(isNaN(cashback)){
			alert('总佣金比例必须为数字！');
			return;
		}
		if(cashback>1){
			alert("总佣金比例不能大于1！");
			return;
		}
   }
   var ipt_cashback = document.getElementById("cashback");
   if(ipt_cashback){
	    var cashback=ipt_cashback.value;

	    if(cashback==""){
		   alert("奖励金额不能为空！");
		   return;
	    }
	    if(cashback<0 && cashback != -1 ){
		   alert("奖励金额只能为正数或 “-1”！");
		   return;
	    }
	    if(isNaN(cashback)){
			alert('奖励金额必须为数字！');
			return;
		}
   }
   var is_charitable = $("#is_charitable").val();
   if(is_charitable > 0){
	   var donation_rate        = $("#donation_rate").val();
	   var charitable_propotion = $("#charitable_propotion").val();
		if(donation_rate==""){
			alert("捐赠比率不能为空！");
			return;
		}
		if(donation_rate<0){
			alert("捐赠比率不能为负数！");
			return;
		}
		if(isNaN(donation_rate)){
			alert('捐赠比率必须为数字！');
			return;
		}
		if(donation_rate < charitable_propotion){
			alert("捐赠比率低于"+charitable_propotion+"，无法提交！");
			return;
		}
		if(donation_rate>1){
			alert("捐赠比率不能大于1！");
			return;
		}
   }

	var cashback_r = document.getElementById("cashback_r");
	if(cashback_r){
		var cashback_r_v = cashback_r.value;

		if(cashback_r_v==""){
		    alert("返现比例不能为空！");
		    return;
		}
		if(cashback_r_v>1 || (cashback_r_v<0 && cashback_r_v != -1 )){
			alert("返现比例必须为0~1之间或 “-1”!");
			return;
		}
		if(isNaN(cashback_r_v)){
			alert('返现比例必须为数字！');
			return;
		}
	}
	/*行邮税*/
	var istax 		= $("#istax").val();
	var tariff 		= $("#tariff").val();
	var comsumption = $("#comsumption").val();
	var addedvalue 	= $("#addedvalue").val();
	var postal 		= $("#postal").val();
	if(istax > 0){
		if(tariff == '' || comsumption == '' || addedvalue == '' || postal == ''){
			alert('税收产品，税率不能为空！');
			return;
		}
		if(!(parseFloat(tariff) >=0 && parseFloat(tariff)<=100)){
			alert('请检查关税税率是否在0~100以内！');
			return;
		}
		if(!(parseFloat(comsumption) >=0 && parseFloat(comsumption)<=100)){
			alert('请检查消费税税率是否在0~100以内！');
			return;
		}
		if(!(parseFloat(addedvalue) >=0 && parseFloat(addedvalue)<=100)){
			alert('请检查增值税税率是否在0~100以内！');
			return;
		}
		if(!(parseFloat(postal) >=0 && parseFloat(postal)<=100)){
			alert('请检查行邮税率是否在0~100以内！');
			return;
		}

	}
	/*行邮税*/
	/*特权*/
	if( $("input[id='privilege_level']").is(':checked')==true ){
		var tq = $("input[name='privilege[]']:checked").length;
		if( tq <= 0 ){
			alert("特权身份必须选");
			return;
		}
	}

	// if( $("#wholesale_id").val() > 0 ){
	// 	var pf_lenght = $("input[id='child_wholesale']:checked").length;
	// 	//console.log("======="+pf_lenght);return;
	// 	if( pf_lenght <= 0 ){
	// 		alert("批发属性必须选！");
	// 		return false;
	// 	}
	// }
   //var offer_id = $("#offer_id").val();
 // console.log("offer_id="+offer_id);
	//var is_same_foreign_mark =  check_pro_foreign_mark_same();
	//if(is_same_foreign_mark ==0 ){
	//	alert('属性价格存在已使用的外部标识，请检查确保正确！');
	//	return;
	//}
	//重新触发selectText方法，确保详细介绍等三大金刚信息保存
	//selectText('#selectText_submit',0);
	/*非待审核产品跳转过来*/
	/*if (typeof(is_audit) == "undefined") {
		  $('#frm_product').submit();
	}else{
		if(is_audit == ""|| is_audit == null){
			 $('#frm_product').submit();
		}else{
			return true;  //审核产品判断返回结果
		}
	}*/
	/*郑培强*/
	if($("#is_youxiao_2").attr('checked')=="checked"){
		if($("#qr_select").val()==1){
			var QR_starttime=$("#QR_starttime").val();
			var QR_endtime=$("#QR_endtime").val();
			if(zhuanshijian(QR_starttime)>zhuanshijian(QR_endtime)){
				alert("产品有效期设置有误！"); 
				return false; 
			}
		}else{
			var QR_day=$("#QR_day").val();
			if(!(/^(\+|-)?\d+$/.test( QR_day )) || QR_day<0){
				alert("请输入整数天数！"); 
				return false; 
		    }
		}
	}
	
	
	/*郑培强*/
	 $('.WSY_bulkul02').html('');
	 if(ordering_retail > 0){
         var orgin_price = $("input[name='orgin_price']").val();
         var now_price = $("input[name='now_price']").val();
         var for_price = $("input[name='for_price']").val();
         var cost_price = $("input[name='cost_price']").val();
         var propertyids = $("input[name='propertyids']").val(); //普通属性
         var wholesale_id = $("input[name='wholesale_id']").val();
         var wholesale_childid = ''; //批次子属性
         $("input[class='child_wholesale_c']:checked").each(function() {
             wholesale_childid = wholesale_childid + $(this).val() + "_";
         });
         wholesale_childid = wholesale_childid.substring(0,wholesale_childid.length-1);
         var is_Synchronize = 1; //是否有更改价格或属性
         var del_pro = '';//被删掉的属性id
         if(propertyids_b != propertyids || wholesale_id_b != wholesale_id || wholesale_childid_b != wholesale_childid){ //属性修改
             var wholesale_childid_b_arr = wholesale_childid_b.split("_");
             var wholesale_childid_arr = wholesale_childid.split("_");
             for(var i=0;i<wholesale_childid_b_arr.length;i++){
                 if(wholesale_childid_arr.indexOf(wholesale_childid_b_arr[i]) == -1 && wholesale_childid_b_arr[i] !=''){
                     del_pro += '_' + wholesale_childid_b_arr[i];
                 }
             }
             var propertyids_b_arr = propertyids_b.split("_");
             var propertyids_arr = propertyids.split("_");
             for(var i=0;i<propertyids_b_arr.length;i++){
                 if(propertyids_arr.indexOf(propertyids_b_arr[i]) == -1 && propertyids_b_arr[i] !=''){
                     del_pro += '_' + propertyids_b_arr[i];
                 }
             }
             del_pro = del_pro.substring(1);
             if(del_pro){
                 $('#my_from').val('orderingretail_pro');
                 $('#del_pro').val(del_pro);
             }else {
                 is_Synchronize = 0;
             }
         }else if(now_price_b != now_price) { //现价修改
             $('#my_from').val('orderingretail_price');
         }else {
             is_Synchronize = 0;
         }


         var list_del = false; //删除一列属性，true有删除的
         var list_change = false; //列现价改变，true有改变
         var del_lsit_pro = ''; //删除的属性列
         var ordering_proids_last_arr = ordering_proids_last.split(",");
         var ordering_proids_arr = ordering_proids.split(",");
         for(var i=0;i<ordering_proids_arr.length;i++){
             if(ordering_proids_last_arr.indexOf(ordering_proids_arr[i]) == -1 && ordering_proids_arr[i] !=''){
                 del_lsit_pro += ',' + ordering_proids_arr[i];
             }
         }
         del_lsit_pro = del_lsit_pro.substring(1);//删掉的属性列
         if(del_lsit_pro && !del_pro){ //属性列有删除,未修改属性
             list_del = true;
             $('#del_lsit_pro').val(del_lsit_pro); //属性列不为空都要
             is_Synchronize = 1;
             $('#my_from').val('orderingretail_pro');
         }else if(ordering_now_price_last != ordering_now_price && !del_pro && list_del==false) { //未修改属性，未删除属性列
             $('#my_from').val('orderingretail_price');
             list_change = true;
             is_Synchronize = 1;
         }
         if(is_Synchronize){
             layer.confirm('该产品已关联到订货系统，是否同步编辑？', {
                 title:'提示',
                 btn: ['确认','取消']
             }, function(confirm){
                 layer.close(confirm);
                 $('#edit_or_product').val(1);
                 $('#frm_product').submit();
             }, function(){
                 $('#frm_product').submit();
             });
         }else {
             layer.close(confirm);
             $('#frm_product').submit();
         }
	}else{
		$('#frm_product').submit();
	}
}

/*郑培强*/
function zhuanshijian(string){
	var f = string.split(' ', 2);
	var d = (f[0] ? f[0] : '').split('-', 3);
	var t = (f[1] ? f[1] : '').split(':', 3);
	return (new Date(
			parseInt(d[0], 10) || null,
			(parseInt(d[1], 10) || 1) - 1,
			parseInt(d[2], 10) || null,
			parseInt(t[0], 10) || null,
			parseInt(t[1], 10) || null,
			parseInt(t[2], 10) || null
			)).getTime() / 1000;
}
/*郑培强*/

function changeOut(o){
   if(o.checked){
      document.getElementById("isout").value=1;
   }else{
      document.getElementById("isout").value=0;
   }
}
function changeNew(o){
   if(o.checked){
      document.getElementById("isnew").value=1;
   }else{
      document.getElementById("isnew").value=0;
   }
}
function changeHot(o){
   if(o.checked){
      document.getElementById("ishot").value=1;
   }else{
      document.getElementById("ishot").value=0;
   }
}
function changeSnap(o){
   if(o.checked){
      document.getElementById("issnapup").value=1;
	  $('.snap_up').show();
   }else{
      document.getElementById("issnapup").value=0;
	  $('.snap_up').hide();
   }
}
function changeVp(o){
   if(o.checked){
      document.getElementById("isvp").value=1;
	  $('#vp_score').show();
   }else{
      document.getElementById("isvp").value=0;
	  $('#vp_score').hide();
   }
}

function changeVirtual(o){
   if(o.checked){
      document.getElementById("is_virtual").value=1;
   }else{
      document.getElementById("is_virtual").value=0;
   }
}

function changeCurrency(o){
   if(o.checked){
   	//alert(1);
      document.getElementById("is_currency").value=1;
      $("#back_currency").show();
   }else{
   	//alert(2);
      document.getElementById("is_currency").value=0;
	  $("#backcurrency").val(0);
      $("#back_currency").hide();
   }
}
function tax(o){
   if(o.checked){
   	//alert(1);
      document.getElementById("istax").value=1;
      $("#is_tax").show();
   }else{
   	//alert(2);
      document.getElementById("istax").value=0;
	  $("#tariff").val(0);
	  $("#comsumption").val(0);
	  $("#addedvalue").val(0);
	  $("#postal").val(0);
      $("#is_tax").hide();
   }
}
function change_tax(){

	var arr = $('#tax_sel').val().split(',');
	//console.log(arr);
	$("#tariff").val(arr[0]);
	$("#comsumption").val(arr[1]);
	$("#addedvalue").val(arr[2]);
	$("#postal").val(arr[3]);
}
function changeFree_shipping(o){
   if(o.checked){
      document.getElementById("is_free_shipping").value=1;
   }else{
      document.getElementById("is_free_shipping").value=0;
   }
}
function changeisscore(o){
   if(o.checked){
      document.getElementById("isscore").value=1;
   }else{
      document.getElementById("isscore").value=0;
   }
}
function changeispickup(o){
   if(o.checked){
      document.getElementById("is_pickup").value=1;
   }else{
      document.getElementById("is_pickup").value=0;
   }
}
function changeislimit(o){
   if(o.checked){
      document.getElementById("islimit").value=1;
	  $("#limit_num").show();
   }else{
      document.getElementById("islimit").value=0;
	  $("#limit_num").hide();
   }
}
function changeIsFirstRxtend(o){
   if(o.checked){
      document.getElementById("is_first_extend").value=1;
	  $("#extend_money").show();
   }else{
      document.getElementById("is_first_extend").value=0;
	  $("#extend_money").hide();
   }
}
function changeGuess_you_like(o){
   if(o.checked){
      document.getElementById("is_guess_you_like").value=1;
   }else{
      document.getElementById("is_guess_you_like").value=0;
   }
}
function change_mini_mshop(o){
   if(o.checked){
      document.getElementById("is_mini_mshop").value=1;
   }else{
      document.getElementById("is_mini_mshop").value=0;
   }
}


function goUrl(url){
   document.location = url;
}

function chage_Pinformation(obj){
	$("#is_Pinformation").val(obj);
	if( obj ){
		$(".div_show").show();
	}else{
		$(".div_show").hide();
	}
}


function change_is_invoice(obj){
	$("#is_invoice").val(obj);
}

$('#edit1').click(function(){
	// $(".description").show();
	// $(".specifications").hide();
	// $(".service").hide();
	$(".edit1").css("background-color","white");
	$(".edit2").css("background-color","#ECECEC");
	$(".edit3").css("background-color","#ECECEC");
})
$('#edit2').click(function(){
	// $(".specifications").show();
	// $(".description").hide();
	// $(".service").hide();
	$(".edit2").css("background-color","white");
	$(".edit1").css("background-color","#ECECEC");
	$(".edit3").css("background-color","#ECECEC");
})
$('#edit3').click(function(){
	// $(".service").show();
	// $(".specifications").hide();
	// $(".description").hide();
	$(".edit3").css("background-color","white");
	$(".edit1").css("background-color","#ECECEC");
	$(".edit2").css("background-color","#ECECEC");
})

function mess_add(dtype){
	var num  = $('.singletext_con').length;
	if( 10 == num){
		layer.alert('至多填写十个信息');
		return false;
	}
		var mess_num  = $('.diy_one_two').length;
		mess_num++;
		var str = "<tr class=\"diy_one_two\" id=\"diy_item_"+mess_num+"\">"
					+"<input type=hidden name=\"name_id"+mess_num+"\" id=\"name_id"+mess_num+"\" value=\"-1\">"
					+ "<td><input type=text class=\"singletext_con\" name=\"singletext_con_"+mess_num+"\" id=\"singletext_con"+mess_num+"\" value=\"\"  /></td>"
					+ "<td><a title=\"删除\"  href=\"javascript:mess_del("+mess_num+");\"><img src=\"../../../common/images_V6.0/operating_icon/icon04.png\"></a>&nbsp;&nbsp;<a title=\"添加\" href=\"javascript:mess_add("+mess_num+");\"><img src=\"../../../common/images_V6.0/operating_icon/icon05.png\"></a></td>"
					+ "</tr>";
					console.log(str);
		$(".WSY_information tbody").append(str);


}

function mess_del(nu){
	var kid = $("#name_id"+nu).val();
	var num  = $('.singletext_con').length;
	if(1 == num){
		layer.alert('需至少保留一个信息栏');
		return false;
	}
	if( kid > 0 ){
		layer.confirm('您确认要删除吗？', {
			  btn: ['确认','取消']
			}, function(){
			  $.getJSON("del_mess.php", { kid: kid}, function(json){
				console.log("json.result : "+json.result);
				if(json.result == 1){
					document.getElementById("diy_item_"+nu).style.display="none";
					document.getElementById("diy_item_"+nu).innerHTML="";
					layer.msg('已删除', {icon: 1});
				}
			});
			}, function(){
			layer.msg('已取消', {icon: 1});
			return false;
			})
	}else{
		document.getElementById("diy_item_"+nu).style.display="none";
		document.getElementById("diy_item_"+nu).innerHTML="";
	}

}

//检查商品属性外部标识是否重复
function check_pro_foreign_mark_same(){
	var stu = 1;
	var myArray=new Array();
	var initial_foreign_mark = $("input[name='foreign_mark']").val();		//初始值
	//console.log(initial_foreign_mark);
	myArray.push(initial_foreign_mark);
	$("input[name='pro_foreign_mark']").each(function(){

		myArray.push($(this).val());
	});
	console.log(myArray);

	var nary=myArray.sort();

	for(var i=0;i<myArray.length;i++){

		if (nary[i]==nary[i+1]){

		console.log("属性重复内容："+nary[i]);
		//alert('属性价格存在已使用的外部标识，请检查确保正确！');
		stu = 0;
		break;
		}

	}
	console.log(stu);
	return stu;
}

//检查产品名称是否有单引号双引号
function check_proname(){
	var stu = false;
	var reg = /['"’”‘“]/g;
	var pro_name = $('#name').val();
	stu = reg.test(pro_name);
	return stu;
}

