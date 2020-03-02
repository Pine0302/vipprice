
$(function() {
// localStorage.clear();
	//initData();//数据加载



});
function goToIndex(){
	if(yundian_id != -1 && yundian_id > 0){		//带云店参数的购物车页，跳转到云店的首页
		window.location.href = "../common_shop/jiushop/index.php?customer_id="+customer_id+"&yundian="+yundian_id;
	}else{
		window.location.href = "../common_shop/jiushop/index.php?customer_id="+customer_id;
	}
}

/*搜索跳转*/
function search(){
	document.location = "search.php?customer_id="+customer_id;
}
// 店铺全部商品选择
function shopAllProductCheck(obj){
	var shopIdx = obj.attr("shopid");
	if(obj.hasClass("li-select-on")){
		$(".shop"+shopIdx+"_item").addClass("item-select-on");
		$(".shop"+shopIdx+"_item").attr("src", "./"+$skin_img+"/list_image/checkbox_on.png");
		$(".shop"+shopIdx+"_item_edit").attr("src", "./"+$skin_img+"/list_image/checkbox_on.png");
		$("#shop"+shopIdx+"_edit").attr("src", "./"+$skin_img+"/list_image/checkbox_on.png");
	}else{
		$(".shop"+shopIdx+"_item").removeClass("item-select-on");
		$(".shop"+shopIdx+"_item").attr("src", "./images/list_image/checkbox_off.png");
		$(".shop"+shopIdx+"_item_edit").attr("src", "./images/list_image/checkbox_off.png");
		$("#shop"+shopIdx+"_edit").attr("src", "./images/list_image/checkbox_off.png");
	}
}

// 店铺全部商品选择检查函数
function checkShop(obj){
	var allCheck = 1;
	var shopIdx = obj.attr("shopId");
	if(obj.hasClass("item-select-on")){
		$(".shop"+shopIdx+"_item").each(function(){
			if(!$(this).hasClass("item-select-on")){
				allCheck = 0;
				return false;
			}
		});
		if(allCheck == 1){
			$("#shop"+shopIdx).attr("src", "./"+$skin_img+"/list_image/checkbox_on.png");
			$("#shop"+shopIdx+"_edit").attr("src", "./"+$skin_img+"/list_image/checkbox_on.png");
			$("#shop"+shopIdx).addClass("li-select-on");

		}
	}else{
		$("#shop"+shopIdx).attr("src", "./images/list_image/checkbox_off.png");
		$("#shop"+shopIdx+"_edit").attr("src", "./images/list_image/checkbox_off.png");
		$("#shop"+shopIdx).removeClass("li-select-on");
	}
}
/* $(window).resize(function() {
   row2_width = $(window).width();
	emptyCart();
}); */
function emptyCart(){
	var html = "";
	html += '<div class="content-row1">';
	html += '<div class="content-row1-top1">';
	html += '<img src="./images/goods_image/2016042902.png" >';
	html += '</div>';
	html += '<div class="content-row1-top2">';
	html += '<span>购物车快饿瘪了</span>';
	html += '</div>';
	html += '<div class="content-row1-top3">';
	html += '<span>主人快点给我挑点宝贝吧！</span>';
	html += '</div>';
	html += '<div class="content-row1-top4">';
	html += '<span id="row2-button1" onclick="goToIndex();">去逛逛</span>';
	html += '</div>';
	html += '</div>';

	$(".content-footer").remove();
	$("#containerDiv").html(html);

	if(yundian_id != -1){		//云店的购物车，暂时不显示推荐商品
	
	}else{
		cartGood();
		var row2_height = $("#cartGood").height();
        var row2_width = $(window).width();
		//$("#cartGood").css("height",row2_height+"px");
		$("#cartGood").css("width",row2_width+"px");
	 	var xh_img = $(".xh_img").width();
		$(".xh_img").height(xh_img);
		$(".xh_img").width(xh_img);
	}

	
}
function cartGood(){
	var html = "";
	html += '<div class="content-row2">';
	html += '<div class="content-row2-top1"> 商品推荐</div>';
	html += '<div id="row3-div">';
	$.ajax({
		type: "post",
		url: "get_cartGood.php",
		async: false,
		data: { customer_id: customer_id},
		success: function (results) {
			results = eval('('+results+')');
			var len	= results.length;
			if( len == 0 ){
				$("#cartGood").hide();
			}
			for(i=0;i<len;i++){
				html += '<div class="xh_pannel">';
				html +='<a href="product_detail.php?customer_id='+customer_id+'&pid='+results[i].pid+'">';
				html += '<img class="xh_img" src="'+results[i].default_imgurl+'" >';
				html += '<div class="xh_title" >';
				html += '<span>'+results[i].name+'</span>';
				html += '</div>';
				html += '<div class="xh_price"><span >'+
				(OOF_P==2 ? results[i].now_price+OOF_S : OOF_S+results[i].now_price)
				+'</span></div>';
				html += '</a>';
				html += '</div>';

			}
		}
	})

	html += '</div>';
	html += '<div class="div-clear"></div>';
	html += '<div class="content-row2-top3">';
	html += '<div><button onclick="cartGood();" id="row3-button1" type="button" class="am-btn am-btn-default">换一组</button></div>';
	html += '<div>';
	html += '<a href="list.php?customer_id='+customer_id+'&op=cartlike">';
	html += '<button id="row3-button2" type="button" class="am-btn am-btn-default">查看更多</button>';
	html += '</a>';
	html += '</div>';
	html += '</div>';
	html += '</div>';
	$("#cartGood").html(html);
	var row2_height = $("#cartGood").height();
	//$("#cartGood").css("height",row2_height+"px");
    var row2_width = $(window).width();
	$("#cartGood").css("width",row2_width+"px");
	var xh_img = $(".xh_img").width();
	$(".xh_img").height(xh_img);
	$(".xh_img").width(xh_img);
}



function initData(){
	var pid					= -1;			//产品id
	var p_num				= 0;			//产品数量
	var pos					= "";			//产品属性
	var live_room_id 		= 0;			//商城直播房间id
	var check_first_extend 	= 0;			//是否首次推广奖励
	var act_type 			= -1;			//产品活动类型，1积分兑换产品
	var act_id 				= -1;			//活动id
	var pro_yundian_id 		= -1;			//产品所属的云店id
	var delarr				= new Array();	//要删除的信息数组
	var LS_json             = "";           //购物车数据
	
	var date = new Date();
	var m = date.getMonth() + 1;
	var timeing	= date.getFullYear()+"-"+m+"-"+date.getDate()+" "+date.getHours()+":"+date.getMinutes()+":"+date.getSeconds();
	console.log(timeing);

	if(user_id > 0){
		if(o_shop_id > 0){
			if( !localStorage.getItem("cart_user_"+user_id+"_shop_"+o_shop_id) ){
				localStorage.setItem("cart_user_"+user_id+"_shop_"+o_shop_id,"");
			}
			LS_json = localStorage.getItem("cart_user_"+user_id+"_shop_"+o_shop_id);
			console.log(LS_json+"111");
		}else{
			if( !localStorage.getItem("cart_"+user_id) ){
				localStorage.setItem("cart_"+user_id,"");
			}
			LS_json = localStorage.getItem("cart_"+user_id);
			console.log(LS_json);
		}
	}else{
		if( !localStorage.getItem("cart_visitor") ){
			localStorage.setItem("cart_visitor","");
		}
		LS_json = localStorage.getItem("cart_visitor");
	}

	if(LS_json != ""){
		LS_json = eval(LS_json);
	}
	console.log(LS_json);

	var is_show = 1;
	is_show = check_is_show(yundian_id,LS_json);
	console.log(is_show);

	if( LS_json == "" || is_show == 0){
		emptyCart();
		return false;
	}
	
	//console.log(LS_json);
	//console.log("LS_json="+JSON.stringify(LS_json));

	/*店铺循环开始*/
	for( var i = 0; i < LS_json.length; i++){
		var html = "";
		var html3	= "";
		var html4	= "";
		var is_yundian = LS_json[i][2];//-1商城或者供应商店铺，大于0是属于云店
		var shopId = LS_json[i][0];//-1商城，大于0是供应商或者云店
		var yundian_pro_num = 0;

		if(is_yundian != -1){
			pro_yundian_id = LS_json[i][0];
		}
		
		for(var j=0;j<LS_json[i][1].length;j++){
			if(LS_json[i][1][j].length>7){
                if(LS_json[i][1][j][7] != -1 && LS_json[i][1][j][7] != yundian_id){		//统计购物车里非本云店的自营产品数量
                    yundian_pro_num = yundian_pro_num+1;
                }
            }
		}
		console.log(yundian_pro_num);

		if(yundian_pro_num >= LS_json[i][1].length){	//若非本云店的自营产品大于等于该数组里的数量，则不显示此店铺和产品
			continue;
		}

		$.ajax({				
			type: "post",
			url: "get_shopName.php",
			async: false,
			data: {
				id			: shopId,
				customer_id	: customer_id,
				o_shop_id   : o_shop_id,
				yundian_id  : pro_yundian_id,		//产品所属的云店ID，查找出购物车的产品所属店名
				is_yundian	: is_yundian,		//判断是否为云店
			},
			success: function (result) {
				shopName = result;
			}
		});
		html += '<li class="itemWrapper" id="shop'+shopId+'_list">';
		html += '	<div class="itemWrapper-view view'+shopId+'" >';
		html += '		<div class="item-header">';
		html += '			<div class="item-header-left1">';
		html += '				<img id="shop'+shopId+'" shopId="'+shopId+'" class="li-select" src="./images/list_image/checkbox_off.png" width="20" height="20">';
		html += '			</div>';
		html += '			<div class="item-header-left2">';
		html += '				<img src="./images/goods_image/iconfont-jiantou.png" >';
		html += '			</div>';
		html += '			<div class="item-header-left3">';
		html += '				<span>'+shopName+'</span>';
		html += '				<img src="./images/vic/right_arrow.png" >';
		html += '			</div>';
		html += '			<div class="shop-shangpin-list-edit">';
		html += '				<div class="shop-shangpin-list-edit-left1"></div>';
		html += '				<div class="shop-shangpin-list-edit-left2"><span>编辑</span></div>';
		html += '			</div>';
		html += '		</div>';
		html += '	</div>';
		html += '</li>';
		$("#resultData").append(html);
		//输出店铺名 end

		var pro = LS_json[i][1];
		console.log(pro);
		var isdelnum		= 0;//要删除产品数量
		/*产品循环开始*/
		for( var j = 0; j < pro.length; j++){

			pid 				= pro[j][0];//产品id
			p_num 				= pro[j][1];//产品数量
			pos 				= pro[j][2];//产品属性
			live_room_id		= pro[j][3];//商城直播房间id
			check_first_extend	= pro[j][4];//是否首次推广奖励
			act_type			= pro[j][5];//产品活动类型，1积分兑换产品
			act_id				= pro[j][6];//活动id
			pro_yundian_id		= pro[j][7];//产品所属的云店id
			
			//新加字段初始化，防止旧数据缓存里面没有会报错
			if( act_type == undefined || act_type == 'undefined' || act_type == '' ){
				act_type = -1;
			}
			if( act_id == undefined || act_id == 'undefined' || act_id == '' ){
				act_id = -1;
			}
			if( pro_yundian_id == undefined || pro_yundian_id == 'undefined' || pro_yundian_id == '' ){
				pro_yundian_id = -1;
			}

			if(pro_yundian_id != -1 && pro_yundian_id != yundian_id){		//判断当前的云店ID购物车和产品所属的云店id是否相同，不同的不显示此产品
				continue;
			}
			
			var html2			= "";
			var pname			= "";//产品名
			var isout			= 0;//是否下架
			var posArr			= "";//属性名
			var need_score 		= 0;//积分
			var storenum 		= 0;//库存
			var now_price 		= 0;//现价
			var propertyids		= ""//属性字符串
			var default_imgurl	= "";//封面图
			var result_json		= "";
			var isshowpro		= 0;//是否显示产品
			var limit_num		= 0;
			var isgobuy			= 0;
			var day_buy_num		= 0	;
			var is_identity		= 0	;//产品是否需要身份证购买
			var is_act_isvalid	= 1	;//活动是否有效
			var integral_p		= 0	;//积分兑换产品需要积分

			var yundian_status	= 0;	//云店信息
			var yundian_expire_time = '';	//云店到期时间

			var delpro		 	= new Array(); //要删除产品数组
			$.ajax({
				type: "post",
				url: "get_cartPro.php",
				async: false,
				data: {
					pid			: pid,
					pos			: pos,
					user_id		: user_id,
					customer_id	: customer_id,
					rcount		: p_num,
					act_type	: act_type,
					act_id		: act_id,
					pro_yundian_id : pro_yundian_id
				},
				success: function (result) {

					result_json = eval('('+result+')');
					console.log(result_json);
					if( result_json.code == 1 ){
						pname 			= result_json.name;				//产品名字
						isout 			= result_json.isout;			//是否下架 0否 1是
						posArr 			= result_json.posArr;			//属性名称
						storenum 		= result_json.storenum;			//库存
						need_score 		= result_json.need_score;		//需要的积分
						now_price 		= result_json.now_price;		//现价
						propertyids		= result_json.propertyids;		//产品属性IDs
						default_imgurl	= result_json.default_imgurl;	//产品默认图片
						tax_name 		= result_json.tax_name;
						/*行邮税 start */
						tax 			= result_json.tax;
						tax_type 		= result_json.tax_type;

						yundian_status	= result_json.yundian_status;
						yundian_expire_time	= result_json.yundian_expire_time;

						if(tax_type>1){
							this_tax_money  = tax.single_sum_tax;
						}
						/*行邮税 end */

						/*批发属性*/
						wholesale_id 	= result_json.wholesale_id;
						wholesale_num   = result_json.wholesale_num;
						/*批发属性*/


						is_identity		= result_json.is_identity;		//产品是否需要身份证购买
						islimit			= result_json.islimit;			//是否限购产品 ： 0否 1是
						if( islimit == 1 ){
							limit_num		= result_json.limit_data['limit_num'];		//产品限购数量
							isgobuy			= result_json.limit_data['isgobuy'];		//用户可选择的产品限购数量
							day_buy_num		= result_json.limit_data['day_buy_num'];	//用户当天购买此产品的数量
						}
						isshowpro		= 1;
						
						/*活动信息*/
						if (act_type > 0) {
							var act_pro_info = result_json.act_pro_info;
							
							switch (act_type) {
								case 1:
									if (act_pro_info.errcode >= 0) {
										if (act_pro_info.data.isvalid == 1) {
											integral_p 	= act_pro_info.data.integral_p;
											now_price 	= act_pro_info.data.money_p;
											storenum 	= act_pro_info.data.stock;
										} else {
											is_act_isvalid = 0;
										}
									} else {
										is_act_isvalid = 0;
									}
								break;
							}	
						}
						/*活动信息*/
						
						//console.log(default_imgurl);
					}else if( result_json.code == -1 ){
						isshowpro	= 0;
						isdelnum 	= isdelnum+1;
						if(	pro.length == isdelnum ){
							//LS_json.splice(i,1);
							delpro.push(i);
							$("#shop"+shopId+"_list").hide();
						}else{
							delpro.push(i,j);
							//LS_json[i][1].splice(j,1);
						}
						delarr.push(delpro);
						//localStorage.setItem("cart_"+user_id,JSON.stringify(LS_json));
					}else{
						//alert('网络出错！');
						showAlertMsg("提示","网络出错！","知道了");
						return false;
					}
				}
			});
			if( isshowpro == 1 ){
				var item_select_data = '<img class="item-select img_'+shopId+i+j+' shop'+shopId+'_item good_'+pid+j+'"  mini_num="'+wholesale_num+'" pname="'+pname+'"  need_score="'+need_score+'" price="'+now_price+'" tax_type="'+tax_type+'" sort="'+j+'" shopId="'+shopId+'" pid="'+pid+'" pos="'+pos+'" number="'+p_num+'" islimit="'+islimit+'" isgobuy="'+isgobuy+'" limit_num="'+limit_num+'"  day_buy_num="'+day_buy_num+'" is_identity="'+is_identity+'" pro_yundian_id="'+pro_yundian_id+'" src="./images/list_image/checkbox_off.png" width="20" height="20" live_room_id="'+live_room_id+'" check_first_extend="'+check_first_extend+'" act_type="'+act_type+'" act_id="'+act_id+'" need_integral="'+integral_p+'">';
				//var item_select_span = '<span class="item-select img_'+shopId+i+j+' "pid="'+pid+'" pos="'+pos+'" ></span>';
				var item_select_span = '<span class="item-select img_'+shopId+i+j+' "pid="'+pid+'" pos="'+pos+'" act_type="'+act_type+'" ></span>';
				item_select_data = encodeURI(item_select_data);
				item_select_span = encodeURI(item_select_span);
				
				html2 += '<div class="itemMainDiv pro_id_'+pid+'"  isout="'+isout+'" shop="'+shopId+'" sort="'+j+'" id="item_view'+j+'" mini_num="'+wholesale_num+'" pro_id="'+pid+'" pro_yundian_id="'+pro_yundian_id+'" item_select_data="'+item_select_data+'" item_select_span="'+item_select_span+'">';
				html2 += '<div class="item-content-left1">';

				if(pro_yundian_id != -1){		//云店自营产品的
					if(yundian_status != 1 || (yundian_status == 1 && Date.parse(yundian_expire_time) < Date.parse(timeing))){		//产品所属云店过期或者没有通过审核，则不能选择购买
						html2 += '<span class="item-select img_'+shopId+i+j+' "pid="'+pid+'" pos="'+pos+'" act_type="'+act_type+'" pro_yundian_id="'+pro_yundian_id+'" "></span>';
					}else{
						if( parseInt(storenum) >= parseInt(p_num) &&  isout != 1 && is_act_isvalid ){
							html2 += '<img class="item-select img_'+shopId+i+j+' shop'+shopId+'_item good_'+pid+j+'"  mini_num="'+wholesale_num+'" pname="'+pname+'"  need_score="'+need_score+'" price="'+now_price+'" tax_type="'+tax_type+'" sort="'+j+'" shopId="'+shopId+'" pid="'+pid+'" pos="'+pos+'" number="'+p_num+'" islimit="'+islimit+'" isgobuy="'+isgobuy+'" limit_num="'+limit_num+'"  day_buy_num="'+day_buy_num+'" is_identity="'+is_identity+'" pro_yundian_id="'+pro_yundian_id+'" src="./images/list_image/checkbox_off.png" width="20" height="20" live_room_id="'+live_room_id+'" check_first_extend="'+check_first_extend+'" act_type="'+act_type+'" act_id="'+act_id+'" need_integral="'+integral_p+'">';
						}else{
							// html2 += '<span class="item-select img_'+shopId+i+j+' "pid="'+pid+'" pos="'+pos+'" ></span>';
							html2 += '<span class="item-select img_'+shopId+i+j+' "pid="'+pid+'" pos="'+pos+'" act_type="'+act_type+'" pro_yundian_id="'+pro_yundian_id+'" "></span>';
						}
					}
				}else{		//非云店自营产品按照正常显示
					if( parseInt(storenum) >= parseInt(p_num) &&  isout != 1 && is_act_isvalid ){
						html2 += '<img class="item-select img_'+shopId+i+j+' shop'+shopId+'_item good_'+pid+j+'"  mini_num="'+wholesale_num+'" pname="'+pname+'"  need_score="'+need_score+'" price="'+now_price+'" tax_type="'+tax_type+'" sort="'+j+'" shopId="'+shopId+'" pid="'+pid+'" pos="'+pos+'" number="'+p_num+'" islimit="'+islimit+'" isgobuy="'+isgobuy+'" limit_num="'+limit_num+'"  day_buy_num="'+day_buy_num+'" is_identity="'+is_identity+'" pro_yundian_id="'+pro_yundian_id+'"  src="./images/list_image/checkbox_off.png" width="20" height="20" live_room_id="'+live_room_id+'" check_first_extend="'+check_first_extend+'" act_type="'+act_type+'" act_id="'+act_id+'" need_integral="'+integral_p+'">';
					}else{
						// html2 += '<span class="item-select img_'+shopId+i+j+' "pid="'+pid+'" pos="'+pos+'" ></span>';
						html2 += '<span class="item-select img_'+shopId+i+j+' "pid="'+pid+'" pos="'+pos+'" act_type="'+act_type+'" "></span>';
					}
				}	

				html2 += '</div>';
				html2 += '<img class="itemPhoto" src="'+default_imgurl+'">';
				html2 += '<div class="contentLiDiv">';
				html2 += '<div class="itemProName">'+pname+'</div>';

				html2 += '<div class="itemProContent">';
				for( var a = 0; a < posArr.length; a++ ){
					html2 += '<div class="pos_div"><font>'+posArr[a].pos_parent_name+':</font>';
					html2 += '<font class="pos_div_name">'+posArr[a].pos_name+'</font></div>';
				}
				html2 += '</div>';
				/*税收产品*/
				if(tax_type > 1){

					var tis;
					if(tax['code'] > 20000){
						tis = '<span msg="'+tax['result']+'" onclick="show_tax_msg(this,1);">点击查看错误信息<span>';
					}else{
						//tis = tax_name+'约：'+tax['single_sum_tax']+'元';
						tis = '<span onclick="show_tax_msg(this);">'+tax_name+'约：'+tax['single_sum_tax']+OOF_T;+'<span>';
					}

					html2 += '<div class="tax_div" ><button class="btn-shui tax" code="'+tax['code']+'"><div class="test5"><span>税</span></div><div style="display:inline-block;padding-right:3px;">'+tis+' </div></button><span style="font-size:12px;"></div>';
				}
				var ps_html = "";
				if( parseInt(storenum) < parseInt(p_num)){
					ps_html = '<div id="kucunbuzu" class="tishi kucunbuzu">库存不足</div>';
				}
				if( isout == 1 ){
					ps_html = '<div id="xiajia" class="tishi" >商品已下架</div>';
				}
				if( !is_act_isvalid ){
					ps_html = '<div id="act_unisvalid" class="tishi" >活动产品已失效</div>';
				} else {
					if (integral_p > 0) {
						ps_html = '<div id="integral" class="tishi" >积分：'+integral_p+'</div>';
					}
				}

				if(pro_yundian_id != -1){		//云店自营产品的
					if(yundian_status != 1 || (yundian_status == 1 && Date.parse(yundian_expire_time) < Date.parse(timeing))){		//产品所属云店过期或者没有通过审核，则不能选择购买
						ps_html = '<div id="shixiao" class="tishi" >商品已失效</div>';
					}
				}

				html2 += ps_html;
				html2 += '</div>';
				html2 += '<div class="rightWrapper">';
				html2 += '<div class="itemProPrice">'+
				(OOF_P==2 ? now_price+OOF_S : OOF_S+now_price)
				+'</div>';
				html2 += '<div class="itemProCount">';
				/*限购操作*/
				if(islimit ==1 ){
					if(p_num > isgobuy){	//如果购物车的数量，已超过可购买数量，则调整为可购买数量
						p_num = isgobuy;
					}
				}
				/*限购操作*/
				html2 += 'x<font class="count'+shopId+j+'">'+p_num+'</font>';
				html2 += '</div>';
				html2 += '</div>';
				html2 += '</div>';

				html3 += '<div class="itemMainDiv  pro_id_'+pid+'" shop="'+shopId+' sort="'+j+'" id="item_edit'+j+'"  mini_num="'+wholesale_num+'" pro_id="'+pid+'">';
				html3 += '<div class="item-content-left1">';
				html3 += '</div>';
				html3 += '<img class="itemPhoto" src="'+default_imgurl+'">';
				html3 += '<div class="contentLiDiv">';
				html3 += '<div class="itemProName">';
				html3 += '<div class="item-count-decrese" onclick="minuscount('+shopId+','+i+','+j+','+wholesale_id+','+pid+')">';
				html3 += '<span>-</span>';
				html3 += '</div>';
				html3 += '<input  storenum="'+storenum+'" class="Wcount Wcount'+shopId+j+'" onblur="modifycount('+shopId+','+i+','+j+','+wholesale_num+',this);" type="text" value="'+p_num+'">';
				html3 += '<div class="item-count-increse" onclick="addcount('+shopId+','+i+','+j+',this)">';
				html3 += '<span>+</span>';
				html3 += '</div>';
				html3 += '</div>';
				//html3 += html2;
				
				if(pro_yundian_id > 0 ){		//云店自营产品不显示
				}else{
				html3 += '<div class="itemProContent" onclick="get_pro_pos('+shopId+','+i+','+j+','+wholesale_id+');">';
				for( var a = 0; a < posArr.length; a++ ){
					html3 += '<div class="pos_div"><font>'+posArr[a].pos_parent_name+':</font>';
					html3 += '<font class="pos_div_name">'+posArr[a].pos_name+'</font></div>';
				}
				html3 += '<img  class="down_img'+j+'" src="./images/goods_image/20160050101.png" width="10" height="6">';
				html3 += '</div>';
				}


				html3 += '</div>';
				html3 += '<div class="rightWrapper rightWrapper-edit">';
				html3 += '<div class="rightWrapper-edit-left1">';
				html3 += '</div>';
				html3 += '<div class="item-delete-button" onclick="pro_del('+shopId+','+i+','+j+')"  item="'+j+'">';
				html3 += '<span>删除</span>';
				html3 += '</div>';
				html3 += '</div>';
				html3 += '</div>';
				$(".view"+shopId ).append(html2);
			}

		}
		/*产品循环结束*/
		html4 += '<div class="itemWrapper-edit"  style="display: none;">';
		html4 += '<div class="item-header">';
		html4 += '<div class="item-header-left1" style="height:20px">';
		html4 += '</div>';
		html4 += '<div class="item-header-left2">';
		html4 += '<img src="./images/goods_image/iconfont-jiantou.png">';
		html4 += '</div>';
		html4 += '<div class="item-header-left3">';
		html4 += '<span>'+shopName+'</span>';
		html4 += '<img src="./images/vic/right_arrow.png" >';
		html4 += '</div>';
		html4 += '<div class="shop-shangpin-list-complete">';
		html4 += '<div class="shop-shangpin-list-edit-left1"></div>';
		html4 += '<div class="shop-shangpin-list-edit-left2"><span>完成</span></div>';
		html4 += '</div>';
		html4 += '</div>';
		html4 += '<input type="hidden" id="mini_num" value="1">';
		html4 += html3;
		$("#shop"+shopId+"_list").append(html4);

	}
	/*店铺循环开始*/
	if(yundian_id > 0){
		check_yundian_time(LS_json,yundian_id); 	//判断云店产品是否需要下架，过期则执行弹出框和重新加载页面
	}

	//console.log(LS_json);
	console.log(delarr);
	for( var a = 0; a < delarr.length; a++){
		var leg = delarr[a];
		if( leg.length > 1 ){
			LS_json[leg[0]][1].splice(leg[1],1);
		}else{
			LS_json.splice(leg[0],1);
		}
	}
	if(o_shop_id > 0){
		localStorage.setItem("cart_user_"+user_id+"_shop_"+o_shop_id, JSON.stringify(LS_json));
	}else{
		localStorage.setItem("cart_"+user_id,JSON.stringify(LS_json));
	}
	//console.log(LS_json);
    
	// 编辑按键点击事件
	$(".shop-shangpin-list-edit").click(function(){
		$(this).parent().parent().hide();
		$(this).parent().parent().next().show();
		$(".shangpin-dialog").hide();
		//console.log(localStorage);
	});

	// 完成按键点击事件
	$(".shop-shangpin-list-complete").click(function(){
		$(this).parent().parent().hide();
		$(this).parent().parent().prev().show();
		modifyPrice();
		$(".shangpin-dialog").hide();
		//console.log(localStorage);

		/*行邮税*/
		var self = $(this);
		var parent = self.parents('.itemWrapper');
		var self_img = parent.find('.item-select');
		//console.log(self);
		//console.log(self_img);


		var i = 0;					//计数器
		self_img.each(function(index){
			var $this = $(this);
			var pid 	= $this.attr('pid');
			var rcount 	= $this.attr('number');
			var pos 	= $this.attr('pos');

			$.ajax({
				url 		:"./get_cartPro.php",
				dataType 	:"json",
				type 		:"post",
				async		:false,
				data 		:{
								pid:pid,
								rcount:rcount,
								user_id:user_id,
								pos:pos
					},
				success:function(result){
					 console.log(result);

					if(parseInt(rcount)<=parseInt(result.storenum))
					{
						if(parent.find('.kucunbuzu').eq(index).length >0)
						{	
							parent.find('.kucunbuzu').hide();
							console.log(parent.find('.pro_id_'+pid));
							var item_select_data = parent.find('.pro_id_'+pid).eq(0).attr('item_select_data');
							item_select_data = decodeURI(item_select_data);
							self_img.eq(index).parent().html(item_select_data);
						}
							
					}
					else
					{
						
						var item_select_span = parent.find('.pro_id_'+pid).eq(0).attr('item_select_span');
						item_select_span = decodeURI(item_select_span);
						self_img.eq(index).parent().html(item_select_span);

						if(parent.find('.kucunbuzu').eq(index).length == 0)
						{
							parent.find('.itemProContent').eq(index).parent().append('<div id="kucunbuzu" class="tishi kucunbuzu">库存不足</div>');
						}
						else
						{
							parent.find('.kucunbuzu').eq(index).show();
						}	
					}
					var html = "";
					if(result.tax_type > 1){
						var tis;
						if(result.tax['code'] > 20000){
							tis = '<span msg="'+result.tax['result']+'" onclick="show_tax_msg(this,1);">点击查看错误信息<span>';
						}else{
							//tis = result.tax_name+'约：'+result.tax['single_sum_tax']+'元';
							tis = '<span onclick="show_tax_msg(this);">'+result.tax_name+'约：'+result.tax['single_sum_tax']+OOF_T;+'<span>';
						}

						html += '<button class="btn-shui tax" code="'+result.tax['code']+'"><div class="test5"><span>税</span></div><div style="display:inline-block;padding-right:3px;">'+tis+' </div></button><span style="font-size:12px;">';
					}

					var tax_div = $this.parents('.itemWrapper').find('.itemMainDiv').eq(i).find('.tax_div');

					tax_div.html(html);
					i ++ ;
				}
			});

		});
		/*行邮税*/
	});

	//全选
	$(".all-select").click(function(){
		allProductCheck($(this));//选项框点击触发
	});
	$(".content-footer-left2").click(function(){
		var _this=$(this).prev().find('.all-select');
		allProductCheck(_this);//“全选”文字点击触发
	});

	//产品选择点击事件
	var ltemLength=$(".item-select").length;
	function itemSelectTouch(_this){//产品选择点击触发函数
	var index=0;
	var pid = _this.attr("pid");
	var sort = _this.attr("sort");
	var otherObj = $(".good_edit_"+pid+sort);
	var image=$(".item-select img").attr("src");
	if(_this.hasClass("item-select-on")){
		_this.removeClass("item-select-on");
		_this.attr("src", "./images/list_image/checkbox_off.png");
		otherObj.attr("src", "./images/list_image/checkbox_off.png");
		$(".all-select").removeClass("all-select-on");
		$(".all-select").attr("src","./images/list_image/checkbox_off.png");
	}else{
		_this.addClass("item-select-on");
		_this.attr("src", "./"+$skin_img+"/list_image/checkbox_on.png");
		otherObj.attr("src", "./"+$skin_img+"/list_image/checkbox_on.png");
	}
	$(".item-select").each(function(i){
			var haha=$(this).hasClass("item-select-on");
			if(haha){
				index++;
			}
	})
	if(index==ltemLength){
		$(".all-select").addClass("all-select-on");
		$(".all-select").attr("src","./"+$skin_img+"/list_image/checkbox_on.png");
	}
	checkShop(_this);
	modifyPrice();
}
	$('body').on('touchstart', ".item-select", function(){
		itemSelectTouch($(this));//点击选项框触发
	});
	$('body').on('touchstart', ".itemPhoto", function(){
		var _this=$(this).prev().find(".item-select");
		itemSelectTouch(_this);//点击图片触发
	});
	// 店铺checkbox 点击事件
    var ltemLength1=$(".li-select").length;
	$(".li-select").click(function(){
		var index=0;
		if($(this).hasClass("li-select-on")){
			$(this).attr("src", "./images/list_image/checkbox_off.png");
			$(this).removeClass("li-select-on");
			$(".all-select").removeClass("all-select-on");
			$(".all-select").attr("src","./images/list_image/checkbox_off.png");
		}else{
			$(this).attr("src", "./"+$skin_img+"/list_image/checkbox_on.png");
			$(this).addClass("li-select-on");
		}
		$(".li-select").each(function(i){
				var haha=$(this).hasClass("li-select-on");
				if(haha){
					index++;
				}
		})
		if(index==ltemLength1){
			$(".all-select").addClass("all-select-on");
			$(".all-select").attr("src","./"+$skin_img+"/list_image/checkbox_on.png");
		}
		shopAllProductCheck($(this));
		modifyPrice();
	});
    
}

//店铺全部选择
function allProductCheck(obj){
	if(obj.hasClass("all-select-on")){
		$(".li-select").removeClass("li-select-on");
		$(".li-select").attr("src", "./images/list_image/checkbox_off.png");
		$(".li-select-edit").attr("src", "./images/list_image/checkbox_off.png");
		$(".item-select").removeClass("item-select-on");
		$(".item-select").attr("src", "./images/list_image/checkbox_off.png");
		$(".item-select-edit").attr("src", "./images/list_image/checkbox_off.png");
		obj.removeClass("all-select-on");
		obj.attr("src", "./images/list_image/checkbox_off.png");
	}else{
		$(".li-select").addClass("li-select-on");
		$(".li-select").attr("src", "./"+$skin_img+"/list_image/checkbox_on.png");
		$(".li-select-edit").attr("src", "./"+$skin_img+"/list_image/checkbox_on.png");
		$('.item-select').each(function(){
			var num = $(this).attr('number');
			if( parseInt(num) > 0 ){
				$(this).addClass("item-select-on");
				$(this).attr("src", "./"+$skin_img+"/list_image/checkbox_on.png");
			}
		});
		$(".item-select-edit").attr("src", "./"+$skin_img+"/list_image/checkbox_on.png");
		obj.addClass("all-select-on");
		obj.attr("src", "./"+$skin_img+"/list_image/checkbox_on.png");
	}
	modifyPrice();

}
// 总价结算函数
function modifyPrice(){
	var allPrice = 0;
	var count , price ;
	$(".item-select-on").each(function(){
		price = parseFloat($(this).attr("price"));
		count = parseInt($(this).attr("number"));
		if( !isNaN(price) && !isNaN(count) ){
			allPrice +=price*count;
		}

	});
	$("#zongjia").text(allPrice.toFixed(2));
}

//获取属性信息
function get_pro_pos(shopId,i,j,wid){//wid 判断是否有批发属性
	var html = "";
	var LS_json2 = "";
	if(o_shop_id > 0){
		LS_json2 = localStorage.getItem("cart_user_"+user_id+"_shop_"+o_shop_id);
	}else{
		LS_json2 = localStorage.getItem("cart_"+user_id);
	}
	LS_json2 = eval(LS_json2);
	var pro = LS_json2[i][1];
	var pid 			= pro[j][0];//产品id
	var p_num 			= pro[j][1];//产品数量
	var pos 			= pro[j][2];//产品属性
	var act_type 		= pro[j][5];//产品活动类型，1积分兑换产品
	var act_id 			= pro[j][6];//活动id
	var pro_yundian_id	= pro[j][7];//产品所属的云店id
	
	//新加字段初始化，防止旧数据缓存里面没有会报错
	if( act_type == undefined || act_type == 'undefined' || act_type == '' ){
		act_type = -1;
	}
	if( act_id == undefined || act_id == 'undefined' || act_id == '' ){
		act_id = -1;
	}
	if( pro_yundian_id == undefined || pro_yundian_id == 'undefined' || pro_yundian_id == '' ){
		pro_yundian_id = -1;
	}

	if(pro_yundian_id != -1 && pro_yundian_id != yundian_id){		//判断当前的云店ID购物车和产品所属的云店id是否相同，不同的不显示此产品
		// continue;
	}
			
	var pname			= "";
	var isout			= 0;
	var posArr			= "";
	var need_score 		= 0;
	var storenum 		= 0;
	var now_price 		= 0;
	var propertyids		= ""
	var default_imgurl	= "";
	var is_act_isvalid	= 1	;//活动是否有效
	var integral_p		= 0	;//积分兑换产品需要积分
	var result_json		= "";
	$.ajax({
		type: "post",
		url: "get_cartPro.php",
		async: false,
		data: { pid: pid,pos: pos,customer_id: customer_id,rcount: p_num,act_type : act_type,act_id : act_id},
		success: function (result) {

			result_json = eval('('+result+')');
			console.log(result_json);
			if( result_json.code == 1 ){
				pname 			= result_json.name;
				isout 			= result_json.isout;
				posArr 			= result_json.posArr;
				storenum 		= result_json.storenum;
				need_score 		= result_json.need_score;
				now_price 		= result_json.now_price;
				propertyids		= result_json.propertyids;
				default_imgurl	= result_json.default_imgurl;
				
				/*活动信息*/
				if (act_type > 0) {
					var act_pro_info = result_json.act_pro_info;
					
					switch (act_type) {
						case 1:
							if (act_pro_info.errcode >= 0) {
								if (act_pro_info.data.isvalid == 1) {
									integral_p 	= act_pro_info.data.integral_p;
									now_price 	= act_pro_info.data.money_p;
									storenum 	= act_pro_info.data.stock;
								} else {
									is_act_isvalid = 0;
								}
							} else {
								is_act_isvalid = 0;
							}
						break;
					}	
				}
				/*活动信息*/
				
				//console.log(default_imgurl);
			}else{
				//alert('网络出错！');
				showAlertMsg("提示","网络出错！","知道了");
				return false;
			}
		}
	})



	var result_json	= "";
	$(".am-share").addClass("am-modal-active");
	$("body").append('<div class="sharebg"></div>');
	$(".sharebg").addClass("sharebg-active");
	$(".shangpin-dialog").show();
	html += '<div class = "content-base  row1"><div class = "dlg-row1-cell0" ><img  class="am-img-thumbnail am-circle" onclick="closeDialog();" src = "./images/goods_image/2016042704.png" width = "20" height = "20"></div></div>';
	html += '<div class = "content-base dialog-content">';
	html += '<div class = "content-base content-row1 pros-box">';
	html += '<div class = "dlg-content-row1-left" id="Preview" value="0">';
	html += '<img src = "" width = "50" height = "50">';
	html += '</div>';
	html += '<div class = "dlg-content-row1-right">';
	html += '<div class = "dlg-content-row1-right-top1">';
	html += '<span>'+pname+'</span>';
	html += '</div>';
	html += '<div class = "dlg-content-row1-right-top2">';
	html += '<span>'+
	(OOF_P==2 ? '<span id="now_price">'+now_price+'</span>'+OOF_S : OOF_S+'<span id="now_price">'+now_price+'</span>')
	+'</span> ';
	html += '<span><span class = "dlg-content-row1-right-top2-span">+<span id="need_score">'+need_score+'</span>积分</span>';
	html += '</div>';
	html += '</div>';
	html += '</div>';

	$(".sharebg-active,.share_btn").click(function(){
		$(".am-share").removeClass("am-modal-active");
		setTimeout(function(){
			$(".sharebg-active").removeClass("sharebg-active");
			$(".sharebg").remove();
			$(".shangpin-dialog").hide();
		},300);
	});

	var attr_img_str = '';
	var choose_img   = default_imgurl; //产品图片，若选择的主属性有图片，则显示主属性图片
	$.ajax({
		type: "post",
		url: "get_pro_pos.php",
		async: false,
		data: { pid: pid,pos: propertyids,customer_id: customer_id},
		success: function (result) {

			result_json = eval('('+result+')');
			attr_img_str= '{'+result_json.attr_json+'}';
			pos_price	= result_json.price;
			var arr = [];
			for(var item in result_json){
				arr.push(result_json[item]);
			}
			var arr1 = [];
			for(var item1 in arr[0]){
				arr1.push(arr[0][item1]);
			}
			
			for( var i = 0; i < arr1.length; i++ ){
				var one_name     = arr1[i].one_name;	//一级分类名
				var two_class    = arr1[i].two_class;	//二级分类
				var one_id       = arr1[i].one_id;		//一级分类
				var has_attr_img = arr1[i].has_attr_img;//是否有属性图片
				if(has_attr_img == 1){
					html += '<div class = "pro_div parent_attr_img">';
				}else{
					html += '<div class = "pro_div">';
				}
				html += '<div class = "big_pro_name">';
				html += '<span>'+one_name+':&nbsp;&nbsp;</span>';
				html += '</div>';
				html += '<div class = "small_pro_div" pos_name="'+one_name+'">';
				var two_l = two_class.length;
				
				for( var j = 0; j < two_l; j++ ){
					var two_id 	   = two_class[j].id;		 //二级id
					var attr_index = two_class[j].attr_index;//属性展示index
					if(attr_index != 0){
						var attr_img   = two_class[j].img;		 //属性图片
					}else{
						var attr_img   = default_imgurl;		 //默认图片
					}

					var pos_str = String(pos);

					pos_arr	= pos_str.split("_");

					if( pos_arr.indexOf(two_id) > -1 ){
						html += '<div class = "active pos_'+one_id+' pos_div" pos_id="'+two_id+'" id="pro_div_'+one_id+'_'+two_id+'" ontouchstart="chooseDiv('+one_id+','+two_id+',this);" attr_index="'+attr_index+'" attr_img="'+attr_img+'" parent_attr_img="'+has_attr_img+'"><span>'+two_class[j].pos_name+'</span></div>';
						if(attr_index != 0){
							choose_img =  attr_img;
						}
					}else{
						html += '<div class = "pos_'+one_id+' pos_div" pos_id="'+two_id+'" id="pro_div_'+one_id+'_'+two_id+'" ontouchstart="chooseDiv('+one_id+','+two_id+',this);" attr_index="'+attr_index+'" attr_img="'+attr_img+'" parent_attr_img="'+has_attr_img+'"><span>'+two_class[j].pos_name+'</span></div>';
					}
				}

				html += '</div>';
				html += '</div>';
			}


		}
	})
	var ch_num = 1;
	//拥有批发属性的情况下
	if(wid > 0){
		$.ajax({
			type: "post",
			url: "get_pro_pos.php",
			async: false,
			dataType:'json',
			data: {pid:pid,customer_id:customer_id,op:"get_wholesale"},
			success:function(result){
				result = eval(result);
				console.log(result);
				var wpid 	= result.pf_id;//wpid 即 批发属性的的id
				var wpname 	= result.pf_title;//wpid 即 批发属性的的名称
				var has_attr_img = result.has_attr_img;// 是否有属性图片 0为无
				var wc_arr 	= [];
				wc_arr		= result.ch_wholesale;
				if(has_attr_img == 1){
					html += '<div class = "pro_div parent_attr_img">';
				}else{
					html += '<div class = "pro_div">';
				}
				html += '<div class = "big_pro_name">';
				html += '<span>'+wpname+':&nbsp;&nbsp;</span>';
				html += '</div>';
				html += '<div class = "small_pro_div" pos_name="'+wpname+'">';
				for(var i=0;i<wc_arr.length;i++ ){
					var ch_id   	= wc_arr[i]['wholesale_id'];
					var ch_name 	= wc_arr[i]['wholesale_title'];
					var attr_index  = wc_arr[i]['attr_index'];		//属性图片序号，用于大图展示
					if(attr_index != 0){
						var attr_img   = wc_arr[i]['img'];		    //属性图片
					}else{
						var attr_img   = default_imgurl;		   //默认图片
					} 
					var pos_str = String(pos);

					pos_arr	= pos_str.split("_");
					if( pos_arr.indexOf(ch_id) > -1 ){
						html += '<div class = "active pos_'+wpid+' pos_div wholesale_div" pos_id="'+ch_id+'" id="pro_div_'+wpid+'_'+ch_id+'" pos_num="'+ch_num+'" onclick="setpfnum('+pid+','+ch_num+',this)" ontouchstart="chooseDiv('+wpid+','+ch_id+',this);" attr_index="'+attr_index+'" attr_img="'+attr_img+'" parent_attr_img="'+has_attr_img+'" ><span>'+ch_name+'</span></div>';
						$("#mount_count").html(ch_num);
						$("#mini_num").val(ch_num);//记录已经选好的产品，最低购买数量
						html += '<input type="hidden" id="wholesale_num" value="'+ch_num+'">';
						if(attr_index != 0){
							choose_img =  attr_img;
						}
					}else{
						html += '<div class = "pos_'+wpid+' pos_div wholesale_div" pos_id="'+ch_id+'" id="pro_div_'+wpid+'_'+ch_id+'" pos_num="'+ch_num+'" onclick="setpfnum('+pid+','+ch_num+',this)" ontouchstart="chooseDiv('+wpid+','+ch_id+',this);" attr_index="'+attr_index+'" attr_img="'+attr_img+'" parent_attr_img="'+has_attr_img+'" ><span>'+ch_name+'</span></div>';
						//$("#mini").val(ch_num);
					}

				}

				html += '</div>';
				html += '</div>';

			}
		})
	}


	html += '<div id="numDiv" class = "content-base content-row4">';
	html += '<span class = "dlg-content-row4-span">数量:&nbsp;&nbsp;</span>';
	html += '<div class = "num_div">';
	html += '<div class = "minus" onclick="minusNum();"><span>-</span></div>';
	html += '<div class = "count_div"><input type="text" onblur="modify();" value="'+p_num+'" id="mount_count" ></div>';
	html += '<div class = "add" onclick="addNum();"><span>+</span></div>';
	html += '</div>';
	html += '<div id="stock_div">仓存:<span id="stock">'+storenum+'</span></div>';
	html += '</div>';
	html += '</div>';
	html += '<div class = "content-button">';
	html += '<div onclick="confirmOrganization('+shopId+','+j+','+pid+',\''+pos+'\','+i+','+act_type+','+act_id+');" id = "queding">';
	html += '<span style = "color:white;"> 确定</span>';
	html += '</div>';
	html += '</div>';

	$(".shangpin-dialog").html(html);
	$('#Preview').find('img').attr('src',choose_img);//加载图片
	/*属性图预览*/

	var Preview_data = eval('(' + attr_img_str + ')');

	$('#Preview').Preview({
		data:Preview_data,/*数据*/
		index: $('#Preview').attr('value')/*选择的属性key*/
	});

	var clone = $('.parent_attr_img').clone();
				$('.parent_attr_img').remove();
				$('.pros-box').after(clone);


	
}
/*关闭属性编辑框开始*/
function closeDialog(){
	$(".am-share").removeClass("am-modal-active");
	setTimeout(function(){
		$(".sharebg-active").removeClass("sharebg-active");
		$(".sharebg").remove();
	},300);
	$(".shangpin-dialog").hide();
}
/*关闭属性编辑框结束*/

function check_pos(aa,arr){
	var reulst = new Array();
	reulst['code'] = false;
	for(var i =0;i<arr.length;i++){
		if(arr[i].proids == aa){
			//reulst = true;
			reulst['code'] = true;
			reulst['now_price'] = arr[i].now_price;
			reulst['need_score'] = arr[i].need_score;
			reulst['storenum'] = arr[i].storenum;
			break;
		}
	}
	return reulst;
}
/*选择属性开始*/
function chooseDiv(prid,subid,obj){
	var n_pridsubid=prid+"_"+subid;
	var classname = $("#pro_div_"+n_pridsubid).attr("class");
	var ind = classname.indexOf("active");
	if(classname.indexOf("active")!=-1){
		$("#pro_div_"+n_pridsubid).removeClass("active");
		$("#invalue_"+prid).attr("value","");
	}else{
		$(".pos_"+prid).removeClass("active");
		$("#pro_div_"+n_pridsubid).addClass("active");
		$("#invalue_"+prid).attr("value",subid);
	}
	var active = $(".active");
	var pos_arr = "";
	var pos = "";
	for(var j =0;j<active.length;j++){
		pos = active.eq(j).attr("pos_id");
		if( pos_arr == "" ){
			pos_arr += pos;
		}else{
			pos_arr += "_"+pos;
		}
	}
	// console.log(pos_price);
	// console.log(pos_arr);
	var is_pos = false;

	is_pos = check_pos(pos_arr,pos_price);
//cons
	var code = is_pos.code;
	if(code){
		try {
            document.getElementById("now_price").innerHTML =  is_pos.now_price;
        } catch (e) {
        }
		try {
            document.getElementById("need_score").innerHTML = is_pos.need_score;
        } catch (e) {
        }
		try {
            document.getElementById("stock").innerHTML = is_pos.storenum;
        } catch (e) {
        }
	}

	var attr_img = $(obj).attr('attr_img');
	var attr_id  = $(obj).attr('attr_index');
	var parent_attr_img = $(obj).attr('parent_attr_img');
	if(parent_attr_img == 1){
		$('#Preview').find('img').attr('src',attr_img);
		$('#Preview').attr('value',attr_id);
	}
}
/*选择属性结束*/

/*---批发属性点击开始---*/

function setpfnum(pid,num){
	$("#wholesale_num").val(num);
	$("#mini_num").val(num);
	$("#mount_count").attr("value",num);
	$(".pro_id_"+pid).attr("mini_num",num);

}
/*---批发属性点击开始---*/





/*数量加减开始*/
function addNum(){
    var mount_count = $("#mount_count").attr("value");
	var storenum = $("#stock").html();
	storenum = parseInt(storenum,10);

	if(parseInt(mount_count,10)>=storenum){
	   return;
	}
	mount_count ++;
	$("#mount_count").attr("value",mount_count);
}

function minusNum(){

    var mount_count = $("#mount_count").attr("value");
	if(mount_count>1){
		mount_count --;
		if($("#wholesale_num").val() > mount_count ){
			return;
		}
		$("#mount_count").attr("value",mount_count);
	}
}
function modify() {
    var a = parseInt($("#mount_count").val(), 10);
    if ("" == $("#mount_count").val()) {
        $("#mount_count").val(1);
        return
    }
    if (!isNaN(a)) {
        if (1 > a || a > 999) {
            $("#mount_count").val(1);
            return
        } else {
            $("#mount_count").val(a);
            return
        }
    } else {
        $("#mount_count").val(1);
    }
}
/*数量加减结束*/

/*选择属性提交开始*/
function confirmOrganization(shopId,sort,pid,pos,sort_i,act_type,act_id){

	var wholesale_num = $("#wholesale_num").val();
	//var mini_num = $("#pro_id_"+pid).attr('mini_num');
	//console.log(mini_num);
	var stock_num = $("#stock").html();

	var mount_count = $("#mount_count").val();//获取编辑框数量
	var now_price 	= $("#now_price").html();//获取编辑框价钱
	var need_score 	= $("#need_score").html();//获取编辑框积分
	
	var $small_pro_div = $('.small_pro_div');//查有多少个属性父级
	for( i = 0; i < $small_pro_div.length ; i++ ){
		var active = $small_pro_div.eq(i).find(".active").length;
		if( active < 1 ){
			var pos_name = $small_pro_div.eq(i).attr("pos_name");
			showAlertMsg("提示","请选择"+pos_name,"知道了");
			return;
		}
	}
	

	if( parseInt(wholesale_num) > parseInt(mount_count) ){
		showAlertMsg("提示","购买数量少于最低数量","知道了");
		return;
	}
	if( parseInt(mount_count) > parseInt(stock_num) ){
		//console.log(mount_count);
		//console.log(stock_num);
		showAlertMsg("提示","此商品库存不足啦！请重新选择数量","知道了");
		return;
	}

	var pos_div = $(".active");//选择的属性
	var pos_div_arr = "";//选择的属性id字符串
	/*获取、替换选择的属性id、名字开始*/
	for( var i = 0; i < pos_div.length; i++ ){
		var pos_div_id = pos_div.eq(i).attr("pos_id");
		if( pos_div_arr == "" ){
			pos_div_arr += pos_div_id;
		}else{
			pos_div_arr += "_"+pos_div_id;
		}
		var pos_div_str = pos_div.eq(i).find("span").html();	//属性名字
		$("#shop"+shopId+"_list #item_edit"+sort+" .itemProContent .pos_div").eq(i).find(".pos_div_name").html(pos_div_str);
		$("#shop"+shopId+"_list #item_view"+sort+" .contentLiDiv .pos_div").eq(i).find(".pos_div_name").html(pos_div_str);

	}
	/*获取、替换选择的属性id、名字结束*/

	/*替换属性、价钱、数量开始*/
	$("#shop"+shopId+"_list #item_view"+sort+" .item-select").attr("pos",pos_div_arr);
	$("#shop"+shopId+"_list #item_view"+sort+" .rightWrapper .itemProPrice").html(""+
	(OOF_P==2 ? now_price+OOF_S : OOF_S+now_price)
	);
	$(".Wcount"+shopId+sort).val(mount_count);
	$(".count"+shopId+sort).html(mount_count);

	$(".img_"+shopId+sort_i+sort).attr("number",mount_count);
	$(".img_"+shopId+sort_i+sort).attr("price",now_price);
	$(".img_"+shopId+sort_i+sort).attr("need_score",need_score);
	/*替换属性、价钱、数量结束*/

	var LS_arr = "";
	if(o_shop_id > 0){
		LS_arr = localStorage.getItem("cart_user_"+user_id+"_shop_"+o_shop_id);
	}else{
		LS_arr = localStorage.getItem("cart_"+user_id);
	}

	LS_arr = eval(LS_arr);
	//console.log(LS_arr);return;
	var is_del = false;//相同产品相同属性移除开关
	for( var i = 0; i < LS_arr.length; i++ ){
		var shop_id = LS_arr[i][0];//店铺id
		if( shop_id == shopId ){
			/*查找有没有相同产品相同属性产品开始*/
			var pro = LS_arr[i][1];
			for( var m = 0; m < pro.length; m++ ){
				var pro_id = pro[m][0];
				var pro_pos = pro[m][2];
				var pro_act_type = pro[m][5];
				var pro_act_id = pro[m][6];
				
				//新加字段初始化，防止旧数据缓存里面没有会报错
				if( pro_act_type == undefined || pro_act_type == 'undefined' || pro_act_type == '' ){
					pro_act_type = -1;
				}
				if( pro_act_id == undefined || pro_act_id == 'undefined' || pro_act_id == '' ){
					pro_act_id = -1;
				}

				if( act_type == undefined || act_type == 'undefined' || act_type == '' ){
					act_type = -1;
				}
				if( act_id == undefined || act_id == 'undefined' || act_id == '' ){
					act_id   = -1;
				}
				
				if( pro_id==pid && pro_pos == pos_div_arr && pro.length > 1 && act_type == pro_act_type && act_id == pro_act_id ){
					is_del = true;
					break;
				}
			}
			/*查找有没有相同产品相同属性产品结束*/

			/*更改数组开始*/
			for( var j = 0; j < pro.length; j++ ){
				var pro_id = pro[j][0];
				var pro_pos = pro[j][2];
				var pro_act_type = pro[j][5];
				var pro_act_id = pro[j][6];
				var pro_yundian_id_another = pro[j][7];
				
				//新加字段初始化，防止旧数据缓存里面没有会报错
				if( pro_act_type == undefined || pro_act_type == 'undefined' || pro_act_type == '' ){
					pro_act_type = -1;
				}
				if( pro_act_id == undefined || pro_act_id == 'undefined' || pro_act_id == '' ){
					pro_act_id = -1;
				}

				if( act_type == undefined || act_type == 'undefined' || act_type == '' ){
					act_type = -1;
				}
				if( act_id == undefined || act_id == 'undefined' || act_id == '' ){
					act_id   = -1;
				}
				if( pro_yundian_id_another == undefined || pro_yundian_id_another == 'undefined' || pro_yundian_id_another == '' ){
					pro_yundian_id_another = -1;
				}

				if(pro_yundian_id_another != -1 && pro_yundian_id_another != yundian_id){		//判断当前的云店ID购物车和产品所属的云店id是否相同，不同的不显示此产品
					continue;
				}
				
				if( pro_id == pid && pro_pos == pos && act_type == pro_act_type && act_id == pro_act_id ){
					if( is_del ){
					  //console.log(111);
						LS_arr[i][1].splice(j,1);
						$("#shop"+shopId+"_list #item_view"+sort).remove();
						$("#shop"+shopId+"_list #item_edit"+sort).remove();
					}else{
						//console.log(111);
						LS_arr[i][1][j][1] = mount_count;
						LS_arr[i][1][j][2] = pos_div_arr;
					}

					break;
				}
			}
			/*更改数组结束*/
		}
	}
	if(o_shop_id > 0){
		localStorage.setItem("cart_user_"+user_id+"_shop_"+o_shop_id, JSON.stringify(LS_arr));//数组转json存入localStorage
	}else{
		localStorage.setItem("cart_"+user_id,JSON.stringify(LS_arr));//数组转json存入localStorage
	}
	
	//console.log(LS_arr);return;
	upload_cart();//实时更新购物车
	closeDialog();//关闭属性编辑框
}
/*选择属性提交结束*/

/*编辑加、减、直接输入开始*/
function addcount(shop_id,i,j,obj){

	var LS_arr = "";
	if(o_shop_id > 0){
		LS_arr = localStorage.getItem("cart_user_"+user_id+"_shop_"+o_shop_id);
	}else{
		LS_arr = localStorage.getItem("cart_"+user_id);
	}
	LS_arr = eval(LS_arr);
	var dd = $(".count"+shop_id+j);
	var storenum = $(".Wcount"+shop_id+j).attr("storenum");
	storenum = parseInt(storenum,10);
	var count = $(".Wcount"+shop_id+j).val();
	if(parseInt(count,10)>=storenum){
	   return;
	}
	count ++;

	/*限购验证*/
	$this = $(obj);
	var location_arr = new Array(i,j);
	var limit_stu = check_limit_product_islegal($this,count,location_arr);
	if(!limit_stu){
		return ;
	}
	/*限购验证*/
	$(".Wcount"+shop_id+j).val(count);
	$(".count"+shop_id+j).html(count);
	$(".img_"+shop_id+i+j).attr("number",count);
	LS_arr[i][1][j][1] = count;
	if(o_shop_id > 0){
		localStorage.setItem("cart_user_"+user_id+"_shop_"+o_shop_id, JSON.stringify(LS_arr));
	}else{
		localStorage.setItem("cart_"+user_id,JSON.stringify(LS_arr));
	}
    
    upload_cart();
	
}

function minuscount(shop_id,i,j,wid,pid){//wid:判断是否拥有购物属性，wnum最低限制
	var LS_arr = "";
	if(o_shop_id > 0){
		LS_arr = localStorage.getItem("cart_user_"+user_id+"_shop_"+o_shop_id);
	}else{
		LS_arr = localStorage.getItem("cart_"+user_id);
	}
	LS_arr = eval(LS_arr);

	var count = $(".Wcount"+shop_id+j).val();
	var mini_num = $(".pro_id_"+pid).attr('mini_num');
	if( parseInt(count,10) > 1 ){
		count --;
		if( wid>0 ){//大于0则有批发属性
			if(count < mini_num){
				return;
			}

		}
		$(".Wcount"+shop_id+j).val(count);
		$(".count"+shop_id+j).html(count);
		$(".img_"+shop_id+i+j).attr("number",count);
		LS_arr[i][1][j][1] = count;

		if(o_shop_id > 0){
			localStorage.setItem("cart_user_"+user_id+"_shop_"+o_shop_id, JSON.stringify(LS_arr));
		}else{
			localStorage.setItem("cart_"+user_id,JSON.stringify(LS_arr));
		}
        
        upload_cart();
	}

}

function modifycount(shop_id,i,j,k,obj) {//k代表最少购买数
	//console.log(j);
	var LS_arr = "";
	if(o_shop_id > 0){
		LS_arr = localStorage.getItem("cart_user_"+user_id+"_shop_"+o_shop_id);
	}else{
		LS_arr = localStorage.getItem("cart_"+user_id);
	}

	LS_arr = eval(LS_arr);
    var a = parseInt($(".Wcount"+shop_id+j).val(), 10);
	var storenum = $(".Wcount"+shop_id+j).attr("storenum");
    if ("" == a) {
        a=k;
    }
    if (!isNaN(a)) {
        if (k > a ) {
			a=k;
        }else if( a > storenum ){
			a=storenum;
		}
    } else {
		 a=k;
    }
	/*限购验证*/
	$this = $(obj);
	var location_arr = new Array(i,j);
	var limit_stu = check_limit_product_islegal($this,a,location_arr);
	if(!limit_stu){
		return ;
	}
	/*限购验证*/
	$(".Wcount"+shop_id+j).val(a);
	$(".count"+shop_id+j).html(a);
	$(".img_"+shop_id+i+j).attr("number",a);
	LS_arr[i][1][j][1] = a;
	if(o_shop_id > 0){
		localStorage.setItem("cart_user_"+user_id+"_shop_"+o_shop_id, JSON.stringify(LS_arr));
	}else{
		localStorage.setItem("cart_"+user_id,JSON.stringify(LS_arr));
	}
    
    upload_cart();
	
}
/*编辑加、减、直接输入结束*/

/*商品删除开始*/
function pro_del(shop_id,i,j){

	var pid			= $(".img_"+shop_id+i+j).attr("pid");//要删除的产品id
	var pos			= $(".img_"+shop_id+i+j).attr("pos");//要删除的产品属性
	var act_type	= $(".img_"+shop_id+i+j).attr("act_type");//要删除的产品的活动类型
	var act_id		= $(".img_"+shop_id+i+j).attr("act_id");//要删除的产品的活动id
	var LS_arr 		= "";

	if(o_shop_id > 0){
		LS_arr = localStorage.getItem("cart_user_"+user_id+"_shop_"+o_shop_id);
	}else{
		LS_arr = localStorage.getItem("cart_"+user_id);
	}
	
	LS_arr = eval(LS_arr);
	console.log(shop_id+"-"+i+"-"+j);
	var LS_arr_len	= LS_arr.length;
	console.log(LS_arr);
	console.log('aaaaa');
	console.log(LS_arr_len);
	for( var x = 0; x < LS_arr_len; x++ ){
		var LS_shopid = LS_arr[x][0];//localStorage中的店铺id
		if( shop_id != LS_shopid ){
			continue;
		}
		var LS_per_arr	= LS_arr[x][1];//localStorage中的店铺产品数组

		var per_pro_arr_len = 0;
		for(var y=0;y<LS_arr[x][1].length;y++){							//判断删除后产品数量,排除其他云店自营产品
			if(LS_arr[x][1][y][7] != -1 && LS_arr[x][1][y][7] != yundian_id){
				
			}else{
				per_pro_arr_len = per_pro_arr_len+1;		//统计排除其他云店自营产品的产品数量
			}
		}

		var per_arr_len	= LS_per_arr.length;

		for( var z = 0; z < per_arr_len; z++ ){
			var LS_pid		= LS_per_arr[z][0];//localStorage中的店铺产品数组中的产品id
			var LS_pos		= LS_per_arr[z][2];//localStorage中的店铺产品数组中的产品属性
			var LS_act_type	= LS_per_arr[z][5];//localStorage中的店铺产品数组中的产品活动类型
			var LS_act_id	= LS_per_arr[z][6];//localStorage中的店铺产品数组中的产品活动id
			
			//新加字段初始化，防止旧数据缓存里面没有会报错
			if( LS_act_type == undefined || LS_act_type == 'undefined' || LS_act_type == '' ){
				LS_act_type = -1;
			}
			if( LS_act_id == undefined || LS_act_id == 'undefined' || LS_act_id == '' ){
				LS_act_id = -1;
			}

			if( act_type == undefined || act_type == 'undefined' || act_type == '' ){
				act_type = -1;
			}
			if( act_id == undefined || act_id == 'undefined' || act_id == '' ){
				act_id   = -1;
			}
				

			if( ( LS_pid == pid ) && ( LS_pos == pos ) && LS_act_type == act_type && LS_act_id == act_id ){
				if( per_arr_len > 1 ){
					console.log(LS_arr);

					LS_arr[x][1].splice(z,1);
					// LS_arr[i][1].splice(z,1);
					//$("#shop"+shop_id+"_list").remove();
					console.log(per_pro_arr_len);
					if(per_pro_arr_len > 1){					//若当前的店铺排除其他云店自营产品购物车的产品大于1时，保留店铺框

						$("#shop"+shop_id+"_list #item_view"+j).remove();
						$("#shop"+shop_id+"_list #item_edit"+j).remove();
					}else{										//若当前的店铺排除其他云店自营产品购物车的产品小于于1时，表示当前的云店环境没有产品了
						$("#shop"+shop_id+"_list").remove();
					}
				}else{
					LS_arr.splice(x,1);
					$("#shop"+shop_id+"_list").remove();
				}

				if(o_shop_id > 0){
					localStorage.setItem("cart_user_"+user_id+"_shop_"+o_shop_id, JSON.stringify(LS_arr));
				}else{
					localStorage.setItem("cart_"+user_id,JSON.stringify(LS_arr));
				}
				break;
			}
		}
	}
	var LS_json = "";
	if(o_shop_id > 0){
		LS_json = localStorage.getItem("cart_user_"+user_id+"_shop_"+o_shop_id);
	}else{
		LS_json = localStorage.getItem("cart_"+user_id);
	}
	LS_json	= eval(LS_json);
	console.log(LS_arr);
	
	var is_show = 1;
	is_show = check_is_show(yundian_id,LS_json);
	console.log('hhhh');
	console.log(is_show);

	if(LS_json == "" || is_show == 0 ){
		$(".content-footer").remove();
		$("#containerDiv").html("");
		
		get_bottom_label(user_id,is_publish);//加载底部菜单
		emptyCart();				
	}
    upload_cart();
}

/*
	上传实时购物车数据
*/
function upload_cart(){
	var timestamp = Date.parse(new Date());//获取当前时间戳
	timestamp = timestamp/1000;
	localStorage.setItem("cart_time_"+user_id,timestamp);//设置加入购物车时间
	var cart_data = localStorage.getItem("cart_"+user_id);
	var cart_time = localStorage.getItem("cart_time_"+user_id);
		$.ajax({ 
			type: "post",
			url: "/shop/index.php/Home/H5Cart/h5_cart_data2",
			data: {customer_id:customer_id2,user_id:user_id,cart_data:cart_data,cart_time:cart_time},
			async: false,
			success: function (result) {
				console.log(result);
			}    
		});
}

 function get_bottom_label(user_id,is_publish){
 	var cart_data = "";
 	if(o_shop_id > 0){
		cart_data = localStorage.getItem("cart_user_"+user_id+"_shop_"+o_shop_id);
 	}else{
		cart_data = localStorage.getItem("cart_"+user_id);
 	}
	 
	if(cart_data.length <= 2){  // 当购物车里的数据为空时，才显示底部菜单
		$.ajax({
			 type: "GET",
			 url: "bottom_label.php",
			 data: { customer_id: customer_id,fun:"order_cart",is_publish:is_publish},
			 dataType: "json",
			 async: false,
			 success: function(data){				
					var hasname = false;	
					var foot_html = '<div class="footer-box">';
					
					for(var i=0;i<data.length;i++){
						foot_html += '<div class="weidian">';
						foot_html += '<a href="'+data[i].jump_url+'" >';
						if(data[i].jump_url.indexOf("order_cart") > 0 ){
							foot_html += '<img src="'+data[i].icon_url_selected+'" alt="">';
							foot_html += '<p style="color:#'+data[i].color_selected+'">'+data[i].name+'</p>';
						}else{
							foot_html += '<img src="'+data[i].icon_url+'" alt="">';
							foot_html += '<p style="color:#'+data[i].color+'">'+data[i].name+'</p>';
						}
						if(data[i].name!=''){
							hasname = true;
						}
						foot_html += '</a>';
						foot_html += '</div>';
					}					 
					foot_html += '</div></div><div style="height:50px;"></div>';
					if(hasname){
						foot_html = '<div class="footer hasname">'+foot_html;
					}else{
						foot_html = '<div class="footer">'+foot_html;
					}
					$('body').append(foot_html);
			  }
		 }); 
	 }
 }
/* function pro_del(shop_id,i,j){
	var LS_arr = localStorage.getItem("cart_"+user_id);
	LS_arr = eval(LS_arr);
	var S_length = LS_arr[i][1].length;
	if( S_length > 1){
		LS_arr[i][1].splice(j,1);
	}else{
		LS_arr.splice(i,1);
	}
	//var dddd = LS_arr;

	//var fff = LS_arr[i][1];
	localStorage.setItem("cart_"+user_id,JSON.stringify(LS_arr));
	if( S_length == 1){
		$("#shop"+shop_id+"_list").remove();
	}else{
		$("#shop"+shop_id+"_list #item_view"+j).remove();
		$("#shop"+shop_id+"_list #item_edit"+j).remove();
	}


} */
/*商品删除结束*/

//结算函数
function statement(){
	/*模拟表单提交开始*/
	var objform = document.createElement('form');
	document.body.appendChild(objform);
	/*模拟表单提交结束*/
	var strurl 			= "order_form.php";	//结算页
	var shop_info		= new Array();//店铺数组
	var selected 		= $(".item-select-on");	//获取选中的产品
	var selected_len	= selected.length;
	if( selected_len < 1 ){
		showAlertMsg("提示","请选择商品！","知道了");
		return;
	}
	var all_number = 0;
	var tex_arr = new Array();
	var all_need_score	= 0;
	var all_need_integral	= 0;
	var tax_stu = 0;		 //税收标识：0表示true  负数表示false
    var act_type_bak = '';
	for( var i = 0; i < selected_len; i++ ){
		var pname 		= selected.eq(i).attr("pname");//产品名字
		var shopid 		= selected.eq(i).attr("shopid");//店铺id
		var pid 		= selected.eq(i).attr("pid");//产品id
		var pos 		= selected.eq(i).attr("pos");//产品属性
		var number 		= selected.eq(i).attr("number");//数量
		all_number += number;
		var is_identity	= selected.eq(i).attr("is_identity");//产品是否需要身份证购买
		var need_score	= selected.eq(i).attr("need_score");//积分
		all_need_score	= all_need_score + ( need_score * number );
		var tax_type 	= selected.eq(i).attr("tax_type");
		var mini_num 	= selected.eq(i).attr("mini_num");
		var live_room_id= selected.eq(i).attr("live_room_id");//商城直播房间id
		var check_first_extend= selected.eq(i).attr("check_first_extend");//是否首次推广奖励
		var act_type = selected.eq(i).attr("act_type");//产品活动类型，21积分产品 22积分兑换产品
		var act_id = selected.eq(i).attr("act_id");//活动id
		var need_integral = selected.eq(i).attr("need_integral");//兑换产品所需积分
		var pro_yundian_id = selected.eq(i).attr("pro_yundian_id");//产品所属的云店ID
		all_need_integral = all_need_integral + ( need_integral * number );
		/* 身份证验证产品检测 start */
		if( is_identity == 1 && selected_len > 1 ){
			showAlertMsg("提示","身份证验证产品不能和其他产品同时下单！","知道了");
			return;
		}
		/* 身份证验证产品检测 end */

		//新加字段初始化，防止旧数据缓存里面没有会报错
		if( act_type == undefined || act_type == 'undefined' || act_type == '' ){
			act_type = -1;
		}

		//判断该产品是否做了等级设置
		if(pro_card_level == 1)
		{	
			var viplevel = 0;
			$.ajax({
		        type: "post",
		        url: "check_viplevel.php",
				async: false,
		        data: { pid:pid,customer_id: customer_id},
		        success: function (result) {
					viplevel = result;
			    }
		    });
			if(viplevel == 0)
			{
				showAlertMsg("提示","会员等级不够，不能购买！","知道了");
				return;
			}
		}
		
		/* 最低购买数量限制提示 */
		//console.log(number+"----"+mini_num);

		if(parseInt(mini_num)  > parseInt(number)){
			//console.log(111111);
			showAlertMsg("提示","亲！您购买的产品【"+pname+"】最低购买数量不得少于："+mini_num+"件！","知道了");
			return;
		}
		/* 最低购买数量限制提示 */


		/*限购产品检测 start*/
		//限购参数
		var islimit 			= selected.eq(i).attr('islimit');						//是否是限购产品 0否1是
		if(islimit == 1){
			var product_limit 		= selected.eq(i).attr('limit_num');						//产品限购数量
			var day_buy_num 		= selected.eq(i).attr('day_buy_num');					//当天用户购买的数量
			var isgobuy 			= selected.eq(i).attr('isgobuy');						//当天用户可以购买的数量
			//得到购物车中当前商品数量
			var cart_num 			= get_this_product_num(pid);

			//判断限购产品是否符合限购规则
			var stu = check_limit_product_stu(product_limit,day_buy_num,0,cart_num,pname,1);
			if(stu){
			}else{
				return ;
			}
		}
		/*限购产品检测 end*/

		/* if( !shop_info.hasOwnProperty(shopid) ){
			shop_info[i]		= new Array();
		} */
		shop_info[i]		= new Array();
		var pro_info		= new Array();//商品信息数组
		pro_info.push(pid,pos,number,shopid,live_room_id,check_first_extend,act_type,act_id);
		console.group()
		console.log(pro_info);
		console.log(shop_info);
		console.groupEnd()
		
		shop_info[i].push(shopid,pro_info,pro_yundian_id);
		tex_arr.push(tax_type);						//添加到税收数组
		//console.log(tex_arr);

		/*行邮税*/
		if(tax_type>1){
			var tax_code = selected.eq(i).parents('.itemMainDiv').find('.tax').attr('code');
			//console.log(tax_code);
			if(tax_code == 10008){
				tax_stu += 0;
			}else{
				tax_stu += -1;
			}
		}
		/*行邮税*/
        
		if (act_type < 0) {
			/*抢购商品的购买时间是否合法-开始*/
			var return_key = check_sell_time(pid);
			if( return_key != true ){
					showAlertMsg("提示","商品【"+pname+"】"+return_key+"！","知道了");
					return;
			}
			/*抢购商品的购买时间是否合法-结束*/
		}
        
        if (act_type_bak != '' && act_type_bak != act_type && act_type !=-1 )
        {
            showAlertMsg("提示","不同活动的产品不能一起结算！","知道了");
			return;
        }
        if(act_type != -1){
        	act_type_bak = act_type;
        }
	}
	/*行邮税*/
	var is_tex = is_tax(tex_arr);	//判断选择的税收产品中是否有不同类型的税种
	if( is_tex == false ){
		showAlertMsg("提示","单次结算只能购买相同税种产品！","知道了");
		return false;
	}
	if(tax_stu <0){
		showAlertMsg("提示","结算的产品中含有错误税收产品！","知道了");
		return false;
	}
	/*行邮税*/

	/*总数量限购*/
	if( is_number_limit > 0 ){
		var is_all_limit = is_all_limit_f(all_number);	//判断选择的税收产品中是否有不同类型的税种
		if( is_all_limit ){
			showAlertMsg("提示","每人每天只可购买"+per_number_limit+"件，已超过限购数量","知道了");
			return false;
		}
	}
	/*总数量限购*/


	if( all_need_score > 0 ){
		var call_value = check_score(all_need_score,shop_card_id);//判断积分是否足够
		if( call_value ){
			return false;
		}
	}
	
	//判断商城积分是否足够
	if (all_need_integral > 0) {
		var call_value = check_user_integral(all_need_integral);
		if( call_value ){
			showAlertMsg("提示","积分不足，不能结算","知道了");
			return false;
		}
	}
	
	clear_local_Storage();//下单前清除订单页面的本地存储

	shop_info = JSON.stringify(shop_info);  //数组转json
	/*模拟input开始*/
	var obj_p = document.createElement("input");
	obj_p.type = "hidden";
	objform.appendChild(obj_p);
	obj_p.value = shop_info;
	obj_p.name = "pro_arr";

	var obj_p = document.createElement("input");
	obj_p.type = "hidden";
	objform.appendChild(obj_p);
	obj_p.value = 2;
	obj_p.name = "fromtype";

	var obj_p = document.createElement("input");
	obj_p.type = "hidden";
	objform.appendChild(obj_p);
	obj_p.value = customer_id;
	obj_p.name = "customer_id";

	//订货系统门店id通过隐藏域提交
	var obj_p = document.createElement("input");
	obj_p.type = "hidden";
	obj_p.value = o_shop_id;
	obj_p.name = "o_shop_id";
	objform.appendChild(obj_p);

	//云店id通过隐藏域提交
	var obj_p = document.createElement("input");
	obj_p.type = "hidden";
	obj_p.value = yundian_id;
	obj_p.name = "yundian_id";
	objform.appendChild(obj_p);

	/*模拟input结束*/
    
    var order_data = {
        pro_arr: shop_info,
        fromtype: 2,
        customer_id: customer_id,
        o_shop_id: o_shop_id,
        yundian_id: yundian_id,
    };

    order_data = JSON.stringify(order_data);
    strurl = window.location.protocol + '//' + window.location.hostname + '/weixinpl/mshop/' + strurl;
    
    if (checkUserLogin(strurl, order_data))
    {
        /*表单提交开始*/
        objform.action = strurl;
        objform.method = "POST"
        objform.submit();
        /*表单提交结束*/
    }
	
}

/*判断积分是否足够开始*/
function check_score(need_score,shop_card_id){
	var call_value =false;
	$.ajax({
        type: "post",
        url: "check_score.php",
		async: false,
        data: { shop_card_id: shop_card_id,customer_id: customer_id},
        success: function (result) {
			//return result;
			if( need_score > parseInt(result) ){
				showAlertMsg("提示","您的会员积分不够！","知道了");
				call_value = true;
			}
        }
    });
	return call_value;
}
/*判断积分是否足够结束*/

/*判断抢购商品的时间是够合法-开始*/
function check_sell_time(pid){
	var result_value =true;
	$.ajax({
        type: "post",
        url: "check_sell_time.php",
		async: false,
        data: { pid: pid},
        success: function (result) {
				result = eval('('+result+')');
				if( result["status"] == 3 ){
					result_value = true;
				}else{
					result_value = result["msg"];
				}
	    }
    });
	return result_value;
}
/*判断抢购商品的时间是够合法-结束*/

//下单前清除订单页面的本地存储
function clear_local_Storage(){


	localStorage.removeItem('info_'+user_id);//清空必填信息内容

	localStorage.removeItem('store_'+user_id);//清空门店内容

	localStorage.removeItem('envent_'+user_id);//清空被动事件内容

	localStorage.removeItem('coupon_'+user_id);//清空优惠券内容

	localStorage.removeItem('curr_'+user_id);//清空购物币内容

	localStorage.removeItem('invoice_'+user_id);//清空发票内容

	localStorage.removeItem('remark_'+user_id);//清空备注内容

	localStorage.removeItem('coupon_'+user_id);//清空优惠券内容

	localStorage.removeItem('delivery_time_'+user_id);//清空配送时间内容
    
    localStorage.removeItem('orderingretail_store_'+user_id);//清空订货系统门店
    
	clear_coupon();

}
/*行邮税*/
//判断数组中值是不是一致
function is_tax(array){

	var res   = true;
	array.some(function (item){
		if ( item != array[0]){
			res 	= false;
			return    true;
		}

	});
	return res;
}
//弹出行邮税信息
function show_tax_msg(obj,type){

	if(type ==1 ){
		$this = $(obj);
		showAlertMsg("提示",$this.attr('msg'),"知道了");
	}else{
		showAlertMsg("提示",'此税金仅供参考，具体税金以订单页面为准！',"知道了");
	}

}

/*行邮税*/

function clear_coupon(){		//清空使用优惠券记录
	$.ajax({
		url: "clear_coupon_class.php",
		dataType: 'json',
		type: 'post',
		data:{'customer_id':customer_id,'user_id':user_id},
		success:function(result){

		}
	})
}

/*数量是否超过总限购数量*/
function is_all_limit_f(all_number){
	var check = false;
	var pid_num = parseInt(bcount) + parseInt(all_number);
	if( per_number_limit < pid_num ){
		check = true;
	}
	return check;
}

/* 检测用户商城积分是否足够 */
function check_user_integral(all_need_integral){
	var check = false;
	
	$.ajax({
		url: "/mshop/web/index.php?m=integral&a=check_user_integral&customer_id="+customer_id,
		dataType: 'json',
		type: 'post',
		async: false,
		data:{
			'user_id'		: user_id,
			'need_integral' : all_need_integral
		},
		success: function(result){
			if (result.errcode > 0) {
				check = true;
			}
		}
	})
	
	return check;
}

function check_is_show(yundian_id,LS_json){
	
		if(LS_json == null){
			LS_json = [];
		}

		var arr_sid = [];
		var check_arr_sid = [];
		for( var i = 0; i < LS_json.length; i++){
			var sid = LS_json[i][0];//localStorage里的供应商id或者云店ID
			var check_sid = LS_json[i][2];//识别云店产品或者是平台的产品
			arr_sid.push(sid);
			check_arr_sid.push(check_sid);
		}
		console.log(arr_sid);
		console.log(check_arr_sid);

		if(arr_sid.indexOf( -1 ) > -1 || check_arr_sid.indexOf( -1 ) > -1){		//有部分是平台产品则显示
			return 1;
		}else{		//全部是云店产品
			var first_show = arr_sid.indexOf( yundian_id );
			console.log(first_show);
			if( arr_sid.indexOf( yundian_id ) > -1 && check_arr_sid[first_show] == yundian_id){
				return 1;
			}else{
				return 0;
			}
		}
}

//判断云店产品是否需要下架
function check_yundian_time(arr,yundian_id){
	var fun_yundian_status = 0;
	var fun_yundian_expire_time = 0;

	var date = new Date();
	var m = date.getMonth() + 1;
	var fun_timeing	= date.getFullYear()+"-"+m+"-"+date.getDate()+" "+date.getHours()+":"+date.getMinutes()+":"+date.getSeconds();

	$.ajax({				
			type: "post",
			url: "get_yundianBase.php",
			async: false,
			data: {
				yundian	: yundian_id,
			},
			success: function (result) {
				result_json = eval('('+result+')');
				console.log(result_json);

				fun_yundian_status = result_json.status;
				fun_yundian_expire_time = result_json.expire_time;
			}
	});

	// console.log(fun_yundian_status);
	// console.log(fun_yundian_expire_time);
	// console.log(fun_timeing);
	
	//检查数据里是否有该云店自营产品数据
	var is_have_yundian_pro = 0;
	for(var i=0; i<arr.length; i++){
		if(arr[i][0] == yundian_id && arr[i][2] > 0){
			is_have_yundian_pro = 1;
		}
	}

	if((fun_yundian_status != 1 || Date.parse(fun_yundian_expire_time) < Date.parse(fun_timeing)) && is_have_yundian_pro == 1 ){		//云店过期操作
		// console.log(arr);
		var k = 0;
		for(var i=0; i<arr.length; i++){
			if(arr[i][0] == yundian_id && arr[i][2] > 0){
				if(arr[i] != null ||arrp[i] != []){
					arr.splice(i,1);
				}
			}
		}
		// console.log(arr);
		localStorage.setItem("cart_"+user_id,JSON.stringify(arr));

		/*将处理过的数据实时更新购物车*/
		update_cart_location();
		
	}

	var fun_pro = '';
	var k = 0;
	var is_change_arr = 0;
	if(is_have_yundian_pro == 1){

		for(var i=0; i<arr.length; i++){
			if(arr[i][0] == yundian_id && arr[i][2] > 0){
				fun_pro = arr[i][1];
				k = i;
				break;
			}
		}

		var pid = 0;
		var p_num = 0;
		var pos = 0;
		var live_room_id = 0;
		var check_first_extend = 0;
		var act_type = 0;
		var act_id = 0;
		var pro_yundian_id = 0;

		var isout = 0;
		var storenum = 0;

		var fun_pro_len = fun_pro.length;
		var pro_id_arr = new Array();
		var z = 0;

		/*产品循环开始*/
		for( var j = 0; j < fun_pro_len; j++){
			pid 				= fun_pro[j][0];//产品id
			p_num 				= fun_pro[j][1];//产品数量
			pos 				= fun_pro[j][2];//产品属性
			live_room_id		= fun_pro[j][3];//商城直播房间id
			check_first_extend	= fun_pro[j][4];//是否首次推广奖励
			act_type			= fun_pro[j][5];//产品活动类型，1积分兑换产品
			act_id				= fun_pro[j][6];//活动id
			pro_yundian_id		= fun_pro[j][7];//产品所属的云店id


			$.ajax({
				type: "post",
				url: "get_cartPro.php",
				async: false,
				data: {
					pid			: pid,
					pos			: pos,
					user_id		: user_id,
					customer_id	: customer_id,
					rcount		: p_num,
					act_type	: act_type,
					act_id		: act_id,
					pro_yundian_id : pro_yundian_id
				},
				success: function (result) {

					result_json = eval('('+result+')');
					console.log(result_json);
					if( result_json.code == 1 ){
						isout 			= result_json.isout;			//是否下架 0否 1是
						storenum 		= result_json.storenum;			//库存
					}
				}
			});

			if(parseInt(storenum) < parseInt(p_num) || isout == 1){
				pro_id_arr[z] = pid;
				is_change_arr = 1;
				z++;
			}
		}
		/*产品循环结束*/
		console.log(pro_id_arr);

		
		if(is_change_arr){
			/*将下架或者售空的产品删除 start*/
			for(var j = 0; j < pro_id_arr.length; j++){
				console.log(arr[k][1]);
				for(var h = 0;h < arr[k][1].length ;h++){
					console.log(arr[k][1][h]);
					if(arr[k][1][h][0] == pro_id_arr[j]){
						if(arr[k][1].length > 1){
							arr[k][1].splice(h,1);
						}else{
							arr.splice(k,1);
							break;
						}
					}
				}
			}
			/*将下架或者售空的产品删除 end*/

		
			localStorage.setItem("cart_"+user_id,JSON.stringify(arr));
			/*将处理过的数据实时更新购物车*/
			update_cart_location();
		}
	}
}


function update_cart_location(){
	var timestamp = Date.parse(new Date());//获取当前时间戳
		timestamp = timestamp/1000;
		localStorage.setItem("cart_time_"+user_id,timestamp);//设置加入购物车时间
		var cart_data = localStorage.getItem("cart_"+user_id);
		var cart_time = localStorage.getItem("cart_time_"+user_id);

		// console.log(cart_data);

		$.ajax({ 
			type: "post",
			url: "/shop/index.php/Home/H5Cart/h5_cart_data2",
			data: {customer_id:customer_id2,user_id:user_id,cart_data:cart_data,cart_time:cart_time},
			async: false,
			success: function (result) {
				console.log(result);
				showAlertMsg("提示","产品已下架或库存不足","知道了");
				setTimeout(function(){
					window.location.href = "/weixinpl/mshop/order_cart.php?yundian="+yundian_id+"";
				},2500);
			}    
		});
}



