/*function showMediaMap(url){
	$("#imgQRCode").attr("src",url);
	$("#divQRCode").show();
}*/
  var i;
function showMediaMap(url){
	i = $.layer({
		type : 2,
		shadeClose: true,
		offset : ['10px' , '80px'],
		time : 0,
		iframe : {
			//src : '../common_shop/jiushop/forward.php?type=2&customer_id='+customer_id+'&product_id='+product_id
			src:url
		},
		title : "该产品二维码(扫码即可以购买)",
		//fix : true,
		zIndex : 2,
		border : [5 , 0.3 , '#437799', true],
		area : ['500px','400px'],
		closeBtn : [0,true],
		success : function(){ //层加载成功后进行的回调
			//layer.shift('right-bottom',1000); //浏览器右下角弹出s
		},
		end : function(){ //层彻底关闭后执行的回调
			/*$.layer({
				type : 2,
				offset : ['100px', ''],
				iframe : {
					src : '//sentsin.com/about/'
				},
				area : ['960px','500px']
			})*/
		}
	});
}

function showLabelTag(url){
	i = $.layer({
		type : 2,
		shadeClose: true,
		offset : ['200px' , '500px'],
		time : 0,
		iframe : {
			//src : '../common_shop/jiushop/forward.php?type=2&customer_id='+customer_id+'&product_id='+product_id
			src:url
		},
		title : "选择投放广告平台并复制链接",
		//fix : true,
		zIndex : 2,
		border : [5 , 0.3 , '#437799', true],
		area : ['500px','400px'],
		closeBtn : [0,true],
		success : function(){ //层加载成功后进行的回调
			//layer.shift('right-bottom',1000); //浏览器右下角弹出s
		},
		end : function(){ //层彻底关闭后执行的回调
			/*$.layer({
				type : 2,
				offset : ['100px', ''],
				iframe : {
					src : '//sentsin.com/about/'
				},
				area : ['960px','500px']
			})*/
		}
	});
}
function change_Sort(id,e){
	var value=e.value;
	var before_val=e.id;
	var a=$(e);
	//alert(value+'=='+before_val);
	if(value == before_val){
		return;
	}else if(!value){
		alert('请输入排序数字');
		return;
	}else if(isNaN(value)){
		alert('输入错误,排序只能是数字');
		return;
	}else{
		a.after('<img id="ajax_deal" class="ajax_deal" src="../../Common/images/Product/loading/ajax_small.gif" />');
		$.ajax({
			url:'/weixinpl/back_newshops/Product/product/save_asort_value.php',
			dataType:'json',
			data:{'id':id,'val':value},
			success:function(result){
				if(result.code==0){
					$('#ajax_deal').attr('src',"../../Common/images/Product/loading/s_success.png");
					setTimeout(function(){
						$('#ajax_deal').remove();
					},500);
				}else{
						$('#ajax_deal').attr('src',"../../Common/images/Product/loading/s_error.png");
						setTimeout(function(){
							$('#ajax_deal').remove();

						},500);
					}
				}
			})

	}
}

/*$(function(){
	$(".WSY_columnnav a").removeClass("white1");
	$(".WSY_columnnav a").eq(page_index).addClass("white1");
});*/

//修改名称
function toEditName(pid){
	var o_name = $("#proname_"+pid).data("proname");
	var txt = "<input type='text' class='ipt_img' id='ipt_proname_"+pid+"' value='"+o_name+"' maxlength='30'/>";
	var o_txt = "<input type='hidden' class='ipt_img' id='o_ipt_proname_"+pid+"' value='"+o_name+"'/>";
	$("#proname_"+pid).html(txt);
	$("#proname_"+pid).append(o_txt);
	$("#saveimg_"+pid).attr("src","../../../common/images_V6.0/operating_icon/icon23.png");
	$("#saveimg_"+pid).attr("onclick","doEditName('"+pid+"')");
}

function doEditName(pid){
	var pattern=/[`~!@#$%^&?:"{},;'[\]]|[^-]/g;
	var n_name = $("#ipt_proname_"+pid).val();
	var o_name = $("#o_ipt_proname_"+pid).val();
	// if(n_name !="" && n_name != o_name && !pattern.test(n_name)){
	if(n_name !="" && n_name != o_name ){
		$.getJSON("ajax_operation.php",{id:pid,val:n_name,op:1},function(json){
			if(json.code == 1){
				$("#proname_"+pid).html(n_name);
				$("#proname_"+pid).data("proname",n_name);
			}else{
				$("#proname_"+pid).html(o_name);
				$("#proname_"+pid).data("proname",o_name);
			}
		});
	}else if(pattern.test(n_name)){
		alert('提示信息：您输入的数据含有非法字符！');
		$("#proname_"+pid).html(o_name);

	}
	if(n_name == o_name){
		$("#proname_"+pid).html(o_name);
	}

	$("#saveimg_"+pid).attr("src","../../../common/images_V6.0/operating_icon/icon53.png");
	$("#saveimg_"+pid).attr("onclick","toEditName('"+pid+"')");
}

//列表中产品分类编辑 begin
$(function(){
	$(".pro_typeimg").click(function(){
		var tleft = event.pageX;
		var ttop = event.pageY;
		$("#div_out").css("left",tleft+10+"px");
		$("#div_out").css("top",ttop+10+"px");

		var pid = $(this).data("pro-id");
		var ptid = $(this).attr("data-pro-typeid");
		var ptparent = $(this).data("pro-tparent");

		var pname = $("#proname_"+pid).data("proname");
		$("#span_out_title").html(pname);

		$("#div_out").find("input[name='types']").removeAttr("checked");


		var typeids = ptid.split(",");
		for(var i = 0 ; i < typeids.length ; i++){
			var tid = typeids[i];
			$("input[value='"+tid+"']").attr("checked","checked");
		}




		$("#hid_out_proid").val(pid);
		$("#div_out").show();
	});
	$("#div_inner").click(function(){
		$("#div_out").hide();
	});

	$("#btn_changetype").click(function(){
		$(this).attr("disabled","disabled");
		var pid = $("#hid_out_proid").val(); //产品编号

		var types = $("input[name='types']:checked");

		if(types.length == 0){
			alert("请选择类型！");
			$("#btn_changetype").removeAttr("disabled");			return;
		}
		var tids = "";
		var tname = "";
		types.each(function(i,n){
		   //if(i > 0){
			   tids+=",";
		   //}
		   tids += n.value;
		   var label = $(n).next("label").text();
		   tname += "/"+label;
	   });
	   tids += ",";

		$.getJSON("ajax_operation.php",{type_id:tids,id:pid,op:3},function(json){
			alert(json.msg);
			if(json.code == 1){
				$("#protype_"+pid).html(tname);
				$("#savetypeimg_"+pid).attr("data-pro-typeid",tids);
			};
			$("#div_out").hide();
			$("#btn_changetype").removeAttr("disabled");
		});
	});
});

//修改价格
$(function(){
    var o_price_str_before = '';
    var n_price_str_before = '';
    var b_price_str_before = '';
    var c_price_str_before = '';
	$(".pro_priceimg").click(function(){
		var tleft = event.pageX;
		var ttop = event.pageY;
		$("#div_out2").css("left",tleft+10+"px");
		$("#div_out2").css("top",ttop+10+"px");

		var pid = $(this).data("pro-id"); //产品编号
		var pname = $("#proname_"+pid).data("proname");
		var price_o = $("#savepriceimg_"+pid).data("prooprice");
		var price_n = $("#savepriceimg_"+pid).data("pronprice");

		$("#span_price_title").html(pname);
		$.getJSON("ajax_operation.php",{id:pid,val_o:price_o,val_n:price_n,op:5},function(json){
			var array = eval(json);
			$("#WSY_t1").html("");
			var str = '';
			for (var i=0;i<array.length;i++){
				var apid = array[i]['fpid'];
				var pid = array[i]['pid'];
				var proids = array[i]['proids'];
				var o_price = array[i]['o_price'];
				var n_price = array[i]['n_price'];
				var c_price = array[i]['c_price'];
				var b_price = array[i]['b_price'];
				var n_score = array[i]['n_score'];

                o_price_str_before += o_price+',';
                n_price_str_before += n_price+',';
                b_price_str_before += b_price+',';
                c_price_str_before += c_price+',';

				str = str + '<tr class="WSY_q1" data-tag="'+apid+'" >';
				str = str + '<td>'+proids+'</td>';
				str = str + '<td><input class="input_txprice" type="text" name="tx_prooprice" value="'+o_price+'" />元</td>';
				str = str + '<td><input class="input_txprice" type="text" name="tx_pronprice" value="'+n_price+'" />元</td>';
				str = str + '<td><input class="input_txprice" type="text" name="tx_probprice" value="'+b_price+'" />元</td>';
				str = str + '<td><input class="input_txprice" type="text" name="tx_procprice" value="'+c_price+'" />元</td>';
				str = str + '<td><input class="input_txprice" type="text" name="tx_pronscore" value="'+n_score+'" />分</td>';
				str = str + '</tr>';
			}
            o_price_str_before = o_price_str_before.substring(0,o_price_str_before.length-1);
            n_price_str_before = n_price_str_before.substring(0,n_price_str_before.length-1);
            b_price_str_before = b_price_str_before.substring(0,b_price_str_before.length-1);
            c_price_str_before = c_price_str_before.substring(0,c_price_str_before.length-1);
            console.log('o_price_str='+o_price_str_before+';'+'n_price_str='+n_price_str_before+';'+'b_price_str='+b_price_str_before+';'+'c_price_str='+c_price_str_before+';');

            $("#WSY_t1").append(str);
			$("#hid_price_proid").val(pid);
			$("#div_out2").show();
			$(".input_txprice").on("keyup",function(){
				var val = $(this).val();
				val = val.replace(/[^\d.]/g,""); //清除"数字"和"."以外的字符
				val = val.replace(/^\./g,""); //验证第一个字符是数字而不是
				val = val.replace(/\.{2,}/g,"."); //只保留第一个. 清除多余的
				val = val.replace(".","$#$").replace(/\./g,"").replace("$#$",".");
				val = val.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3'); //只能输入两个小数
				$(this).val(val);
			});
		});
	});
	$("#div_inner2").click(function(){
		$("#div_out2").hide();
	});
	$("#btn_changeprice").click(function(){
		var pid = $("#hid_price_proid").val();
		var o_price = $("input[name='tx_prooprice']");
		var n_price = $("input[name='tx_pronprice']");
		var b_price = $("input[name='tx_probprice']");
		var c_price = $("input[name='tx_procprice']");
		var n_score = $("input[name='tx_pronscore']");
		//console.log(o_price.val() +" == "+n_price.val()+" == "+b_price.val()+" == "+c_price.val()+" == "+n_score.val());
		//console.log(o_price.size() +" == "+n_price.size()+" == "+c_price.size()+" == "+n_score.size());

        var o_p = parseFloat(o_price.val()); //原价
        var n_p = parseFloat(n_price.val());//现价
        var b_p = parseFloat(b_price.val()); //成本价
        var c_p = parseFloat(c_price.val()); //供货价

		if(o_p < 0 ||  n_p < 0 ||  b_p < 0 ||  c_p < 0 ||  n_score.val() < 0  ){
			alert("不可为负数");
			return false;
		}

		if( o_p  < n_p ){
			alert("现价不可大于原价");
			return false;
		}
		if( n_p  < b_p ){
			alert("成本价不可大于现价");
			return false;
		}
		if( n_p  < c_p ){
			alert("供货价不可大于现价");
			return false;
		}
		if( b_p  < c_p && b_p !=0){
			alert("供货价不可大于成本价");
			return false;
		}
		if(o_price.size() != n_price.size() ||  o_price.size() != c_price.size()  || o_price.size()!= n_score.size()){
			alert("数据异常！");
			return false;
		}
		var o_price_str = "";
		var n_price_str = "";
		var b_price_str = "";
		var c_price_str = "";
		var n_score_str = "";
		var n_fpid_str  = "";
		var check_price = 1 ; //1:允许执行 0,停止执行
		o_price.each(function(i,n){
			if(i > 0){
				o_price_str +=",";
				n_price_str +=",";
				b_price_str +=",";
				c_price_str +=",";
				n_score_str +=",";
				n_fpid_str += ",";
			}
			o_price_str += $(n).val();
			var fpid = $(n).parent().parent().data("tag");
			n_fpid_str  += fpid;
			n_price_str += $(n_price[i]).val();
			b_price_str += $(b_price[i]).val();
			c_price_str += $(c_price[i]).val();
			n_score_str += $(n_score[i]).val();
			if( $(n).val() < 0 || $(n_price[i]).val() < 0 || $(b_price[i]).val() < 0 || $(c_price[i]).val() < 0 || $(n_score[i]).val() < 0 ){
				alert("不可为负数");
				check_price = 0;
				return false;
			}
			/*console.log('$(n_price[i]).val()==='+$(n_price[i]).val());
			console.log('$(b_price[i]).val()==='+$(b_price[i]).val());
			console.log('$(c_price[i]).val()==='+$(c_price[i]).val());
			*/
			var o_p = parseFloat($(n).val()); //原价
            var n_p = parseFloat($(n_price[i]).val());//现价
            var b_p = parseFloat($(b_price[i]).val()); //成本价
            var c_p = parseFloat($(c_price[i]).val()); //供货价
			if( o_p  < n_p ){
				alert("现价不可大于原价"+i);
				check_price = 0;
				return false;
			}
			if( n_p < b_p ){
               // console.log("b_price : "+$(b_price[i]).val() +"  n_price : "+$(n_price[i]).val());
				alert("成本价不可大于现价"+i);
				check_price = 0;
				return false;
			}
			if( n_p < c_p ){
				alert("供货价不可大于现价"+i);
				check_price = 0;
				return false;
			}
			if( b_p < c_p && b_p !=0){
				alert("供货价不可大于成本价"+i);
				check_price = 0;
				return false;
			}

			//console.log(o_price_str +" == "+n_price_str +" == "+b_price_str+" == "+c_price_str+" == "+n_score_str);
		});
		if(check_price == 0){
			return false;
		}
		$(this).attr("disabled","disabled");
		$.getJSON("ajax_operation.php",{id:pid,aids:n_fpid_str,val_os:o_price_str,val_ns:n_price_str,val_bs:b_price_str,val_cs:c_price_str,val_ss:n_score_str,op:6},function(json){
				if(json.code == 1){
					var price_o =  o_price[0].value;
					var price_n =  n_price[0].value;
					var price_b =  b_price[0].value;
					var price_c =  c_price[0].value;
					price_o = Math.round(price_o*100)/100;
					price_n = Math.round(price_n*100)/100;
					price_b = Math.round(price_b*100)/100;
					price_c = Math.round(price_c*100)/100;

					//+尹志雄----2017年3月3日10:35:15----禅道7825
					//修改$("# "+pid).html("现金:￥"+price_o);为$("#prooprice_"+pid).html("￥"+price_o);
					$("#prooprice_"+pid).html("￥"+price_o);
					$("#pronprice_"+pid).html("￥"+price_n);
					$("#pronprice_"+pid).attr("data-forprice",price_b);
					$("#savepriceimg_"+pid).data("prooprice",price_o);
					$("#savepriceimg_"+pid).data("pronprice",price_n);
					$("#pronprice_"+pid).data("costprice",price_c);
				};
            console.log('o_price_str='+o_price_str+';'+'n_price_str='+n_price_str+';'+'b_price_str='+b_price_str+';'+'c_price_str='+c_price_str+';');
            if(json.is_change_now_price){ //是订货系统的产品并修改了现价
                layer.confirm('该产品已关联到订货系统，是否同步编辑？', {
                    title:'警告',
                    btn: ['确认同步','取消']
                }, function(confirm){
                    layer.close(confirm);
                    location.href='/addons/index.php/ordering_retail/Ordermanagement/product_edit?customer_id='+customer_id_en+"&pid="+pid;
                }, function(){
                    layer.msg('取消同步', {
                        time: 4000,
                        btn: ['确认'],
                        icon:1
                    });
                });
            }
			});
		$("#div_out2").hide();
		$("#btn_changeprice").removeAttr("disabled");
	});
});
//修改库存
$(function(){
	$(".pro_stockimg").click(function(){
		var tleft = event.pageX;
		var ttop = event.pageY;
		$("#div_out3").css("left",tleft+10+"px");
		$("#div_out3").css("top",ttop+10+"px");

		var pid = $(this).data("pro-id");
		var pname = $("#proname_"+pid).data("proname");
		var stock = $("#savestockimg_"+pid).data("prostock");

		$("#span_stock_title").html(pname);
		$.getJSON("ajax_operation.php",{id:pid,val_s:stock,op:5},function(json){
			var array = eval(json);
			$("#WSY_t2").html("");
			var str = '';
			for (var i=0;i<array.length;i++){
				var apid = array[i]['fpid'];
				var pid = array[i]['pid'];
				var proids = array[i]['proids'];
				var s_num = array[i]['s_num'];
				str = str + '<tr class="WSY_q2" data-tag="'+apid+'">';
				str = str + '<td>'+proids+'</td>';
				str = str + '<td><input class="input_txprock" type="text" name="tx_prostock" value="'+s_num+'" /></td>';
				str = str + '</tr>';
			}
			$("#WSY_t2").append(str);
			$("#hid_stock_proid").val(pid);
			$("#div_out3").show();
			$(".input_txprock").on("keyup",function(){
				var val = $(this).val();
				val = val.replace(/\D/g,'');
				$(this).val(val);
			});
		});
	});
	$("#div_inner3").click(function(){
		$("#div_out3").hide();
	});
	$("#btn_changestock").click(function(){
		$(this).attr("disabled","disabled");
		var pid = $("#hid_stock_proid").val();
		var s_num = $("input[name='tx_prostock']");
		var s_num_str = "";
		var n_fpid_str = "";
		var error_num = 0;			//错误累计数
		s_num.each(function(i,n){
			if(i > 0){
				s_num_str +=",";
				n_fpid_str += ",";
			}
			if(parseInt($(n).val())<0){	//库存为负数
				error_num ++;
			}
			s_num_str += $(n).val();
			var fpid = $(n).parent().parent().data("tag");
			n_fpid_str  += fpid;
		});
		if(error_num>0){
			alert('库存不能为负数');
			$("#btn_changestock").removeAttr("disabled");
			return;
		}

		$.getJSON("ajax_operation.php",{id:pid,aid:n_fpid_str,val_s:s_num_str,op:7},function(json){
			if(json.code == 1){
				var num_s =  s_num[0].value;
				$("#prostock_"+pid).html(num_s);
				$("#savestockimg_"+pid).data("prostock",num_s);
			};
		});
		$("#div_out3").hide();
		$("#btn_changestock").removeAttr("disabled");
	});
});

function isCon(arr, val){
	for(var i=0; i<arr.length; i++){
	if(arr[i] == val)
		return true;
	}
	return false;
}

function change_privilege(o){
	if(o.checked){
		$("#privilege_list").show();
	}else{
		$("#privilege_list").hide();
	}
}

//列表中产品属性编辑编辑 begin
$(function(){
	$(".pro_attrimg").click(function(){

		$("#check_hot").removeAttr("checked");
		$("#check_new").removeAttr("checked");
		$("#check_out").removeAttr("checked");
		$("#check_snapup").removeAttr("checked");
		$("#check_vp").removeAttr("checked");
		$("#check_virtual").removeAttr("checked");
		$("#check_currency").removeAttr("checked");
		$("#check_guess").removeAttr("checked");
		$("#check_freeshipping").removeAttr("checked");
		$("#check_score").removeAttr("checked");
		$("#check_limit").removeAttr("checked");
		$("#check_extend").removeAttr("checked");
		$("#check_tax").removeAttr("checked");
		$("#check_privilege").removeAttr("checked");
		$("#check_link_package").removeAttr("checked");
		$("#check_mini_mshop").removeAttr("checked");
		$('#package_imgurl').attr('src','');
		$('#hidden_img').val('');
		$('#div_type_link_package').hide();
		$('#div_type_package_img').hide();
		for(var i=0;i<6;i++){
			$("#privilege_"+i).removeAttr("checked");
		}
		$("#privilege_list").hide();
		var tleft = event.pageX;
		var ttop = event.pageY;
		$("#div_out1").css("left",tleft/2-270+"px");
		$("#div_out1").css("top",ttop+10+"px");

		var pid = $(this).data("pro-id");
		var ishot = $(this).data("ishot");
		var isnew = $(this).data("isnew");
		var isout = $(this).data("isout");
		var isvp = $(this).data("isvp");
		var issnapup = $(this).data("issnapup");
		var is_virtual = $(this).data("is_virtual");
		var is_currency = $(this).data("is_currency");
		var is_guess = $(this).data("is_guess");
		var is_freeshipping = $(this).data("is_freeshipping");
		var is_score = $(this).data("is_score");
		var is_limit = $(this).data("is_limit");
		var is_first_extend = $(this).data("is_first_extend");
		var tax_type = $(this).data("tax_type");
		var vp_score = $(this).data("vp_score");
		var buystart_time = $(this).data("buystart_time");
		var countdown_time = $(this).data("countdown_time");
		var back_currency = $(this).data("back_currency");
		var extend_money = $(this).data("extend_money");
		var limit_num = $(this).data("limit_num");
		var tariff = $(this).data("tariff");
		var comsumption = $(this).data("comsumption");
		var addedvalue = $(this).data("addedvalue");
		var postal = $(this).data("postal");
		var privilege_level = $(this).data('privilege_level');
		var is_privilege = $(this).data('is_privilege');
		var link_package = $(this).data('link_package');
		var link_package_img = $(this).data('link_package_img');
		var is_mini_mshop = $(this).data('is_mini_mshop');
		if(privilege_level != undefined ){
			//alert(privilege_level.toString().length);
			if( privilege_level.toString().length == 1 )
			{
				var aa = privilege_level;
				var privilege_level = [];
				privilege_level.unshift(aa);
			}else{
				var privilege_level = privilege_level.split("_");
			}
			
		}

		if(parseInt(is_privilege) == 1){
			$("#privilege_list").show();
			$("#check_privilege").attr("checked",true);
			for(var i=0;i<6;i++){
				if(isCon(privilege_level, i)){
					$("#privilege_"+i).attr("checked",true);
				}
			}
		}




		var pname = $("#proname_"+pid).data("proname");
		$("#span_prop_title").html(pname);

		if(parseInt(ishot) == 1){
			$("#check_hot").attr("checked",true);
		}
		if(parseInt(isnew) == 1){
			$("#check_new").attr("checked",true);
		}
		if(parseInt(isout) == 1){
			$("#check_out").attr("checked",true);
		}
		if(parseInt(isout) == 1){
			$("#check_out").attr("checked",true);
		}
		if(parseInt(is_mini_mshop) == 1){
			$("#check_mini_mshop").attr("checked",true);
		}
		if(parseInt(link_package) > 0){
			$("#check_link_package").attr("checked",true);
			$('#div_type_link_package').show();
			$('#div_type_package_img').show();
			$('#package_imgurl').attr('src',link_package_img);
			var link_package_type_options = document.getElementById("link_package_text").options;
			var link_package_type_options_len = link_package_type_options.length;
			for ( var i = 0; i < link_package_type_options_len; i++ ){
				if ( link_package == link_package_type_options[i].value ){
					link_package_type_options[i].selected = true;
					break;
				}
			}
		}
		if(parseInt(isvp) == 1){
			$("#check_vp").attr("checked",true);
			$('#div_type_vp').show();
			$('#vp_text').val(vp_score)
		}else{
			$('#div_type_vp').hide();
		}
		if(parseInt(issnapup) == 1){
			$("#check_snapup").attr("checked",true);
			$('#div_type_starttime').show();
			$('#div_type_endtime').show();
			$('#buystart_time').val(buystart_time)
			$('#countdown_time').val(countdown_time)
		}else{
			$('#div_type_starttime').hide();
			$('#div_type_starttime').hide();
		}
		if(parseInt(is_virtual) == 1){
			$("#check_virtual").attr("checked",true);
		}
		if(parseInt(is_currency) == 1){
			$("#check_currency").attr("checked",true);
			$('#div_type_currency').show();
			$('#currency_text').val(back_currency)
		}else{
			$('#div_type_currency').hide();
		}
		if(parseInt(is_guess) == 1){
			$("#check_guess").attr("checked",true);
		}
		if(parseInt(is_freeshipping) == 1){
			$("#check_freeshipping").attr("checked",true);
		}
		if(parseInt(is_score) == 1){
			$("#check_score").attr("checked",true);
		}
		if(parseInt(is_limit) == 1){
			$("#check_limit").attr("checked",true);
			$('#div_type_limit').show();
			$('#limit_text').val(limit_num)
		}else{
			$('#div_type_limit').hide();
		}
		if(parseInt(is_first_extend) == 1){
			$("#check_extend").attr("checked",true);
			$('#div_type_extend').show();
			$('#extend_text').val(extend_money)
		}else{
			$('#div_type_extend').hide();
		}
		if(parseInt(tax_type) > 1){
			$("#check_tax").attr("checked",true);
			$('#div_type_tax').show();

			var tax_type_options = document.getElementById("tax_type2").options;
			var tto_len = tax_type_options.length;
			for ( var i = 0; i < tto_len; i++ ){
				if ( tax_type == tax_type_options[i].value ){
					tax_type_options[i].selected = true;
					break;
				}
			}

			$('#tariff2').val(tariff)
			$('#comsumption2').val(comsumption)
			$('#addedvalue2').val(addedvalue)
			$('#postal2').val(postal)
		}else{
			$('#div_type_tax').hide();
		}

		$('#check_vp').click(function(){
			if($('#check_vp').prop("checked")==true){
				$('#div_type_vp').show();
			}else{
				$('#div_type_vp').hide();
			}
		});
		$('#check_snapup').click(function(){
			if($('#check_snapup').prop("checked")==true){
				$('#div_type_starttime').show();
				$('#div_type_endtime').show();
			}else{
				$('#div_type_starttime').hide();
				$('#div_type_endtime').hide();
			}
		});
		$('#check_currency').click(function(){
			if($('#check_currency').prop("checked")==true){
				$('#div_type_currency').show();
			}else{
				$('#div_type_currency').hide();
			}
		});
		$('#check_limit').click(function(){
			if($('#check_limit').prop("checked")==true){
				$('#div_type_limit').show();
			}else{
				$('#div_type_limit').hide();
			}
		});
		$('#check_extend').click(function(){
			if($('#check_extend').prop("checked")==true){
				$('#div_type_extend').show();
			}else{
				$('#div_type_extend').hide();
			}
		});
		$('#check_link_package').click(function(){
			if($('#check_link_package').prop("checked")==true){
				$('#div_type_link_package').show();
				$('#div_type_package_img').show();

			}else{
				$('#div_type_link_package').hide();
				$('#div_type_package_img').hide();
			}
		});
		$('#check_tax').click(function(){
			if($('#check_tax').prop("checked")==true){
				$('#div_type_tax').show();
			}else{
				$('#div_type_tax').hide();
			}
		});

		$("#hid_prop_proid").val(pid);
		$("#div_out1").show();
	});
	$("#div_inner1").click(function(){
		$("#div_out1").hide();
	});

	$("#btn_changeprop").click(function(){
		var privilege_str = "";
		var is_privilege = 0;
		if( $('input[id="check_privilege"]').is(':checked')==true ){
			is_privilege = 1;
			//privilege_str += "0";
			for(var i=0;i<6;i++){
				if( $('input[id="privilege_'+i+'"]').is(':checked')==true ){
					privilege_str += '_'+i;
				 }
			}
			privilege_str = privilege_str.substr(1);
		}else{
			is_privilege = 0;
			privilege_str = "-1_1_2_3_4_5";
		}


		//console.log(privilege_str);
		//return;

		$(this).attr("disabled","disabled");
		var pid = $("#hid_prop_proid").val(); //产品编号
		var costprice =  $('#pronprice_'+pid).data('costprice');
		var supply_id =  $('#pronprice_'+pid).attr('data-supplyid');	//供应商ID
		if( supply_id > 0 && costprice == 0){
			alert('供货价不能为0不能上架');
			$("#div_out1").hide();
			$("#btn_changeprop").removeAttr("disabled");
			return false;
		}

		var ishot = 0;
		var isnew = 0;
		var isout = 0;
		var issnapup = 0;
		var isvp = 0;
		var is_virtual = 0;
		var is_currency = 0;
		var is_guess = 0;
		var is_freeshipping = 0;
		var is_score = 0;
		var is_limit = 0;
		var is_first_extend = 0;
		var vp_text = 0;
		var currency_text = 0;
		var buystart_time = '';
		var countdown_time = '';
		var limit_text = 0;
		var extend_text = 0;
		var showStr = "";
		var tax_type = 1;
		var tariff = "";
		var comsumption = "";
		var addedvalue = "";
		var postal = "";
		var link_package = "";
		var link_package_img = "";
		var is_mini_mshop = 0;
		var get = 1;
		if($("#check_link_package").attr("checked")){
			link_package = $('#link_package_text').val();
			if($('#hidden_img').val() != ''){
				link_package_img = $('#hidden_img').val();
			}else if($('#package_imgurl').attr('src') != ''){
				link_package_img = $('#package_imgurl').attr('src');
			}else{
				alert('请设置关联礼包图片！');
				get = 0;
			}
		}
		if($("#check_out").attr("checked")){
			isout = 1;
			showStr+="/下架";
		}
		if($("#check_new").attr("checked")){
			isnew = 1;
			showStr+="/新品";
		}
		if($("#check_hot").attr("checked")){
			ishot = 1;
			showStr+="/热卖";
		}
		if($("#check_snapup").attr("checked")){
			issnapup = 1;
			showStr+="/抢购";
			buystart_time = $('#buystart_time').val();
			countdown_time = $('#countdown_time').val();
		}
		if($("#check_vp").attr("checked")){
			isvp = 1;
			showStr+="/vp产品";
			vp_text = $('#vp_text').val();
			if( vp_text > 0 ){

			}else{
				alert('VP值必须大于0');
				$("#btn_changeprop").removeAttr("disabled");
				return;
			}
		}
		if($("#check_virtual").attr("checked")){
			is_virtual = 1;
			showStr+="/虚拟产品";
		}
		if($("#check_currency").attr("checked")){
			is_currency = 1;
			showStr+="/购物币产品";
			currency_text = $('#currency_text').val();
		}
		if($("#check_guess").attr("checked")){
			is_guess = 1;
			showStr+="/猜您喜欢产品";
		}
		if($("#check_freeshipping").attr("checked")){
			is_freeshipping = 1;
			showStr+="/包邮";
		}
		if($("#check_score").attr("checked")){
			is_score = 1;
			showStr+="/兑换专区";
		}
		if($("#check_limit").attr("checked")){
			is_limit = 1;
			showStr+="/限购";
			limit_text = $('#limit_text').val();
		}
		if($("#check_extend").attr("checked")){
			is_first_extend = 1;
			showStr+="/首次推广奖励";
			extend_text = $('#extend_text').val();
		}
		if($("#check_tax").attr("checked")){
			tax_type = $('#tax_type2').val();
			tariff = $('#tariff2').val();
			comsumption = $('#comsumption2').val();
			addedvalue = $('#addedvalue2').val();
			postal = $('#postal2').val();
			showStr+="/税收产品";
			// extend_text = $('#extend_text').val();
		}
		if($("#check_link_package").attr("checked")){
			showStr+="/关联礼包";
			// extend_text = $('#extend_text').val();
		}if($("#check_privilege").attr("checked")){
			showStr+="/特权产品";
		}
		if($("#check_mini_mshop").attr("checked")){
			is_mini_mshop = 1;
			showStr+="/微信小程序";
		}
		console.log("isnew : "+isnew+" isout : "+isout+" ishot : "+ishot+" pid : "+pid+" isvp : "+isvp+" is_virtual : "+is_virtual);
		if(get==1){
			$.getJSON("ajax_operation.php",{isnew:isnew,isout:isout,ishot:ishot,issnapup:issnapup,isvp:isvp,is_virtual:is_virtual,is_currency:is_currency,is_guess:is_guess,is_freeshipping:is_freeshipping,is_score:is_score,is_limit:is_limit,is_first_extend:is_first_extend,id:pid,op:4,buystart_time:buystart_time,countdown_time:countdown_time,vp_text:vp_text,currency_text:currency_text,limit_text:limit_text,extend_text:extend_text,tax_type:tax_type,tariff:tariff,comsumption:comsumption,addedvalue:addedvalue,postal:postal,is_privilege:is_privilege,s_privilege:is_privilege,privilege_str:privilege_str,link_package:link_package,link_package_img:link_package_img,is_mini_mshop:is_mini_mshop},function(json){

			alert(json.msg);
			if(json.code == 1){
				$("#proattr_"+pid).html(showStr);
				$("#saveattrimg_"+pid).data("isout",isout);
				$("#saveattrimg_"+pid).data("isnew",isnew);
				$("#saveattrimg_"+pid).data("ishot",ishot);
				$("#saveattrimg_"+pid).data("issnapup",issnapup);
				$("#saveattrimg_"+pid).data("isvp",isvp);
				$("#saveattrimg_"+pid).data("is_virtual",is_virtual);
				$("#saveattrimg_"+pid).data("is_currency",is_currency);
				$("#saveattrimg_"+pid).data("is_guess",is_guess);
				$("#saveattrimg_"+pid).data("is_freeshipping",is_freeshipping);
				$("#saveattrimg_"+pid).data("is_score",is_score);
				$("#saveattrimg_"+pid).data("is_limit",is_limit);
				$("#saveattrimg_"+pid).data("is_first_extend",is_first_extend);
				$("#saveattrimg_"+pid).data("vp_score",vp_text);
				$("#saveattrimg_"+pid).data("buystart_time",buystart_time);
				$("#saveattrimg_"+pid).data("countdown_time",countdown_time);
				$("#saveattrimg_"+pid).data("back_currency",currency_text);
				$("#saveattrimg_"+pid).data("limit_num",limit_text);
				$("#saveattrimg_"+pid).data("extend_money",extend_text);
				$("#saveattrimg_"+pid).data("tax_type",tax_type);
				$("#saveattrimg_"+pid).data("tariff",tariff);
				$("#saveattrimg_"+pid).data("comsumption",comsumption);
				$("#saveattrimg_"+pid).data("addedvalue",addedvalue);
				$("#saveattrimg_"+pid).data("postal",postal);
				$("#saveattrimg_"+pid).data("privilege_level",privilege_str);
				$("#saveattrimg_"+pid).data("is_privilege",is_privilege);
				$("#saveattrimg_"+pid).data("link_package",link_package);
				$("#saveattrimg_"+pid).data("is_mini_mshop",is_mini_mshop);
			};

			});
			$("#div_out1").hide();
		}


		$("#btn_changeprop").removeAttr("disabled");
	});

});



function jumppage(){
	var a=parseInt($("#WSY_jump_page").val());
	if((a<1) || (a==pagenum) || (a>page) || isNaN(a)){
		return false;
	}else{
		var search_type_id = $("#search_type_id").val();
		var search_status = $("#search_status").val();
		var keyword = $("#keyword").val();
		var search_other_id = $("#search_other_id").val();
		var search_status = $("#search_status").val();
		var search_source = $("#search_source").val();
		var search_supply = $("#search_supply").val();
		var supply_id = $("#supply_id").val();
		var foreign_mark  = $("#foreign_mark").val();
		document.location= pagename+".php?customer_id="+customer_id_en+"&foreign_mark="+foreign_mark+"&sales="+ordersale+"&pagenum="+a+"&search_type_id="+search_type_id
			+"&search_status="+search_status+"&keyword="+keyword+"&search_other_id="+search_other_id+"&search_status="+search_status
			+"&search_source="+search_source+"&search_supply="+search_supply+"&supply_id="+supply_id;
	}
}
$(function(){
	if(search_type_id){
		var sel_tparent = $("#sel_tparent");
		for(var i = 0 ; i < type_parent.length ; i++){
			var id = type_parent[i][0];
			var name = type_parent[i][1];
			sel_tparent.append("<option value='"+id+"'>"+name+"</option>");
		}
		// var sel_type = $("#search_type_id");
		// for(var i = 0 ; i < type_parent.length ; i++){
		// 	var id = type_parent[i][0];
		// 	var name = type_parent[i][1];
		// 	sel_type.append("<option value='"+id+"'"+(search_type_id == id ? "selected" : "" )+">"+name+"</option>");
		// 	var c_arr = type_children[id];
		// 	if(c_arr && c_arr.length > 0){
		// 		for(var j = 0 ; j < c_arr.length ; j++){
		// 			var cid = c_arr[j][0];
		// 			var cname = c_arr[j][1];
		// 			sel_type.append("<option value='"+cid+"'"+(search_type_id == cid ? "selected" : "" )+">&nbsp;&nbsp;&nbsp;&nbsp;--&nbsp;&nbsp;"+cname+"</option>");
		// 		}
		// 	}
		// }
	}

	$("#sel_tparent").change(function(){
		var pid = this.value;
		var sel_tchild = $("#sel_tchild");
		sel_tchild.html("");
		if(pid != "" && parseInt(pid) > 0){
			var c_arr = type_children[pid];
			if(c_arr && c_arr.length > 0){
				for(var i = 0 ; i < c_arr.length ; i++){
					var id = c_arr[i][0];
					var name = c_arr[i][1];
					sel_tchild.append("<option value='"+id+"'>"+name+"</option>");
				}
			}else{
				sel_tchild.append("<option value='-1'>无子级</option>");
			}

		}else{
			sel_tchild.append("<option value='-1'>无子级</option>");
		}
	});

	$("#ordersale").click(function(){
		if(this.checked){
			ordersale = 1 ;
		}else{
			ordersale = 0;
		}
		console.log("checked : "+this.checked +"  ordersale : "+ordersale);
		var search_type_id = $("#search_type_id").val();
		var search_status = $("#search_status").val();
		var keyword = $("#keyword").val();
		var search_other_id = $("#search_other_id").val();
		var search_status = $("#search_status").val();
		var search_source = $("#search_source").val();
		var search_supply = $("#search_supply").val();
		var supply_id  = $("#supply_id").val();
		var foreign_mark  = $("#foreign_mark").val();
		document.location= pagename+".php?customer_id="+customer_id_en+"&foreign_mark="+foreign_mark+"&sales="+ordersale+"&pagenum="+pagenum+"&search_type_id="+search_type_id
			+"&search_status="+search_status+"&keyword="+keyword+"&search_other_id="+search_other_id+"&search_status="+search_status
			+"&search_source="+search_source+"&search_supply="+search_supply+"&supply_id="+supply_id;
	});

	if($(".WSY_page") && $(".WSY_page").length > 0){
		$(".WSY_page").createPage({
			pageCount:page,
			current:pagenum,
			backFn:function(p){
			var search_type_id = $("#search_type_id").val();
			var search_status = $("#search_status").val();
			var keyword = $("#keyword").val();
			var search_other_id = $("#search_other_id").val();
			var search_status = $("#search_status").val();
			var search_source = $("#search_source").val();
			var search_supply = $("#search_supply").val();
			var supply_id = $("#supply_id").val();
			var foreign_mark  = $("#foreign_mark").val();
			document.location= pagename+".php?customer_id="+customer_id_en+"&foreign_mark="+foreign_mark+"&sales="+ordersale+"&pagenum="+p+"&search_type_id="+search_type_id
				+"&search_status="+search_status+"&keyword="+keyword+"&search_other_id="+search_other_id+"&search_status="+search_status
				+"&search_source="+search_source+"&search_supply="+search_supply+"&supply_id="+supply_id;
		   }
		});
	}
	$("#search_source").change(function(){
		if($(this).val() == 2){
			$("#li_supply").show();
		}else{
			$("#li_supply").hide();
		}
	});


	$("#btn_upfile").click(function(){
		$("#excelfile").click();
	});

	$("#btn_check_store").click(function(){
		var d = $("#div_check_store");
		$(".div_op").not(d).fadeOut("fast");
		d.slideToggle("fast");
	});

	$("#btn_export").click(function(){
		var d = $("#div_export");
		$(".div_op").not(d).fadeOut("fast");
		d.slideToggle("fast");
	});

});

function exportProduct(){
	//var auth_user_id = <?php echo  $auth_user_id; ?>;
	var url='/weixin/plat/app/index.php/Excel/commonshop_excel_product/customer_id/'+customer_id;

	/*导出自行安装订单筛选框*/
	var excelArray = [
						["product_id","产品ID"],
						["name","名称"],
						["type_id","类型"],
						["orgin_price","原价"],
						["now_price","现价"],
						["for_price","成本"],
						["cost_price","供货价"],
						["asort","优先级别"],
						["isnew","新品"],
						["ishot","热卖"],
						["isout","下架"],
						["tradeprices","属性价格"],
						["propertyids","属性"],
						["storenum","库存"],
						["foreign_mark","外部标识"],
						["good_level","好评"],
						["meu_level","中评"],
						["bad_level","差评"],
						["sell_count","销量"],
						["createtime","创建时间"]
					 ];
	exportBox(excelArray);
	$(".floatbox").show();

	$(".floatinputs").click(function(){
		var str="";
		$("input[name='excel_field[]']:checkbox").each(function(){
            if($(this).is(':checked')){
                str += $(this).val()+","
            }
        })
        str = str.substring(0,str.length-1);

		if(str != ""){
			url = url + "/excel_fields/" + str;
		}



	var etype = $("input[type='radio']:checked").val();
	if(etype == 1){
		 var keyword 			= document.getElementById("keyword").value;			//关键词
		 var foreign_mark 		= document.getElementById("foreign_mark").value;	//外部标识
		 var supply_id 			= document.getElementById("supply_id").value;		//供应商ID
		 var search_type_id 	= document.getElementById("search_type_id").value;	//产品分类
		 var search_other_id 	= document.getElementById("search_other_id").value;	//其他属性
		 var search_source 		= document.getElementById("search_source").value;	//商品来源


        var obj=document.getElementsByName('pro_ids'); //选择所有name="'test'"的对象，返回数组
		//取到对象数组后，我们来循环检测它是不是被选中
		var check_ids='';
		for(var i=0; i<obj.length; i++){
		if(obj[i].checked){check_ids+=obj[i].value+','}; //如果选中，将value添加到变量s中
		}
		 if(pagename!="") {
			 url=url+'/pagetype/'+pagename;
		 }
		 if( search_source == 2 ){
			 var search_supply 		= document.getElementById("search_supply").value;//供应商id
			 url=url+'/search_source/'+search_source;
			 url=url+'/search_supply/'+search_supply;
		 }else if( search_source == 1 ){
			 url=url+'/search_source/'+search_source;
		 }
		  if(supply_id!="") {

			 url=url+'/supply_id/'+supply_id;
		 }
		 if(keyword!="") {

			 url=url+'/keyword/'+keyword;
		 }
		 if(foreign_mark!="") {

			 url=url+'/foreign_mark/'+foreign_mark;
		 }
		 if(search_type_id!="" && search_type_id>0) {

			 url=url+'/search_type_id/'+search_type_id;
		 }
		 if(search_other_id!="" && search_other_id>0) {

			 url=url+'/search_other_id/'+search_other_id;
		 }
		 url=url+'/auth_user_id/'+auth_user_id+'/';
		 if(check_ids!=""){
		 url=url+'/check_ids/'+check_ids+'/';
		 }
	}

	var url_base=url;
		inti_per();
		ShowDIV('topLoader');
		/* alert('show loader');
		alert('url_base:'+url_base); */
		if (topLoaderRunning) {
			return;
		}
		topLoaderRunning = true;
		var oFunc = function () {

			url = url_base + '/limit_count/200/limit_p/'+obj_json.page+'/page_count/'+obj_json.page_count+'/count/'+obj_json.count+'/';
			//alert(url);
			//console.log(url);
			$.ajax({type:'GET', async:false, url:url,
				success:function(data){
					// console.log(data);
					// return;
					obj_json = eval('('+data+')');

					if(obj_json.page_count<obj_json.page){
						closeDiv('topLoader');
						//alert('over');
						window.location.href=url+'output/go/';

					}else{ }


					console.log(obj_json.code);
				}
			});
			glo_add = glo_add + glo_per;

			$topLoader.percentageLoader({progress: glo_add});
			$topLoader.percentageLoader({value: ('导出中，请勿刷新和关闭页面！')});
			//console.log('nothing'+obj_json.page);

			console.log(glo_add.toFixed(2)+"|"+glo_add+" = "+glo_add+" + "+glo_per);
			if(glo_add<1){
				setTimeout(oFunc, 200);
			}else{
				topLoaderRunning = false;
			}
		}
		if(obj_json.length==0){
			$topLoader.percentageLoader({progress: glo_add});
			$topLoader.percentageLoader({value: ('导出中，请勿刷新和关闭页面！')});
			url = url_base + '/limit_count/200/limit_p/0/';
			$.ajax({type:'GET', async:false, url:url,
				success:function(data){					
					obj_json = eval('('+data+')');
					glo_per = 1 / obj_json.page_count;
					//console.log(obj_json.code);
					setTimeout(oFunc, 1000);

				}
			});
		}else{ }
		$(".floatbox").hide();

	// document.location = url;
	// 	$(".floatbox").hide();
	// 	$(".floatbox").remove();
	});
}

	//excel导出动画
	var glo_add;
	var glo_per;//完成百份比
	var obj_json;
	var topLoaderRunning;
	var $topLoader;
	// $(function() {
	// 	inti_per();
	// });

	function inti_per(){
		glo_add = 0.0;
		glo_per = 0.0;
		obj_json = new Array();
		$topLoader = $("#topLoader").percentageLoader({
			width: 256, height: 256, controllable: true, progress: glo_add, onProgressUpdate: function (val) {
				this.setValue(Math.round(val * 100.0) + '%初始化中，请勿刷新和关闭页面！');
			}
		});
		topLoaderRunning = false;
	}

	function ShowDIV(thisObjID) {
		$("#BgDiv").css({ display: "block", height: $(document).height() });
		var yscroll = document.documentElement.scrollTop;
		$("#" + thisObjID).css("top", "100px");
		$("#" + thisObjID).css("display", "block");
		document.documentElement.scrollTop = 0;
	}

	function closeDiv(thisObjID) {
		$("#BgDiv").css("display", "none");
		$("#" + thisObjID).css("display", "none");
	}
	//excel导出动画 End


function importMember(){
     var f_content = document.getElementById("excelfile").value;
	 var fileext=f_content.substring(f_content.lastIndexOf("."),f_content.length)
     fileext=fileext.toLowerCase()
    if (fileext!='.xls')
    {
        alert("对不起，导入数据格式必须是xls格式文件哦，请您调整格式后重新上传，谢谢 ！");
        return false;
    }

	document.getElementById("frm_import").submit();
}

//列表产品分类编辑 end

function colse_layerOpen(){
	$('.layui-layer').remove();
	$('.layui-layer-shade').remove();
}
function change_tax(){
	var arr = $('#tax_sel').val().split(',');
	$("#tariff").val(arr[0]);
	$("#comsumption").val(arr[1]);
	$("#addedvalue").val(arr[2]);
	$("#postal").val(arr[3]);
}
function change_tax2(){
	var arr = $('#tax_sel2').val().split(',');
	$("#tariff2").val(arr[0]);
	$("#comsumption2").val(arr[1]);
	$("#addedvalue2").val(arr[2]);
	$("#postal2").val(arr[3]);
}
function saveLabel(obj){
	var action = $(obj).attr('action');
	var ckp = $("input[name='ckp_props']:checked");
	if(ckp.length==0){
		layer.alert('请选择标签！');
		return;
	}


	var pro_out = 0;
	var pro_new = 0;
	var pro_hot = 0;
	var pro_issnapup = 0;
	var pro_vp = 0;
	var pro_virtual = 0;
	var pro_currency = 0;
	var pro_guess = 0;
	var pro_freeshipping = 0;
	var pro_score = 0;
	var pro_limit = 0;
	var pro_extend = 0;
	var pro_tax = 0;
	var issnapup_start = "";
	var issnapup_end = "";
	var issnapup_end = "";
	var vp_text = "";
	var limit_text = "";
	var extend_text = 0;
	var tax_type = 1;
	var tariff = 0;
	var comsumption = 0;
	var addedvalue = 0;
	var postal = 0;
	var is_mini_mshop = 0;//是否小程序显示
	var currency_text = 0;

	if($('#pro_out').prop("checked")==true){
		if(action=="add"){
			pro_out = 1;
		}else{
			pro_out = 2;
		}
	}
	if($('#pro_new').prop("checked")==true){
		if(action=="add"){
			pro_new = 1;
		}else{
			pro_new = 2;
		}
	}
	if($('#pro_hot').prop("checked")==true){
		if(action=="add"){
			pro_hot = 1;
		}else{
			pro_hot = 2;
		}
	}
	if($('#pro_virtual').prop("checked")==true){
		if(action=="add"){
			pro_virtual = 1;
		}else{
			pro_virtual = 2;
		}
	}
	if($('#pro_currency').prop("checked")==true){
		if(action=="add"){
			pro_currency = 1;
			currency_text = $('#currency_text_input').val();
			if(currency_text=="" || currency_text==undefined){
				layer.alert("请填写购物币数量");
				return ;
			}
			if(currency_text<1){
				layer.alert("购物币数量必须大于1");
				return ;
			}
		}else{
			pro_currency = 2;
		}
	}
	if($('#pro_guess').prop("checked")==true){
		if(action=="add"){
			pro_guess = 1;
		}else{
			pro_guess = 2;
		}
	}
	if($('#pro_freeshipping').prop("checked")==true){
		if(action=="add"){
			pro_freeshipping = 1;
		}else{
			pro_freeshipping = 2;
		}
	}
	if($('#pro_score').prop("checked")==true){
		if(action=="add"){
			pro_score = 1;
		}else{
			pro_score = 2;
		}
	}
	if($('#pro_issnapup').prop("checked")==true){
		if(action=="add"){
			pro_issnapup = 1;
			issnapup_start = $('#issnapup_start_input').val();
			issnapup_end = $('#issnapup_end_input').val();
			if(issnapup_start=="" || issnapup_end==""|| issnapup_start==undefined|| issnapup_end==undefined){
				layer.alert("请填写抢购时间");
				return;
			}
			if(issnapup_start>issnapup_end){
				layer.alert("开始时间必须小于结束时间");
				return;
			}
		}else{
			pro_issnapup = 2;
		}
	}
	if($('#pro_vp').prop("checked")==true){
		if(action=="add"){
			pro_vp = 1;
			vp_text = $('#vp_text_input').val();
			if(vp_text=="" || vp_text==undefined){
				layer.alert("请填写VP值");
				return;
			}
			if(vp_text<1){
				layer.alert("VP值必须大于0");
				return;
			}
		}else{
			pro_vp = 2;
		}
	}
	if($('#pro_limit').prop("checked")==true){
		if(action=="add"){
			pro_limit = 1;
			limit_text = $('#limit_text_input').val();
			if(limit_text=="" || limit_text==undefined){
				layer.alert("请填写限购数量");
				return;
			}
			if(limit_text<1){
				layer.alert("限购数量必须大于0");
				return;
			}
		}else{
			pro_limit = 2;
		}

	}
	if($('#pro_extend').prop("checked")==true){
		if(action=="add"){
			pro_extend = 1;
			extend_text = $('#extend_text_input').val();
			if(extend_text=="" || extend_text==undefined){
				layer.alert("请填写首次推广奖励金额");
				return;
			}
			if(extend_text<1){
				layer.alert("推广奖励金额必须大于0");
				return;
			}
		}else{
			pro_extend = 2;
		}
	}
	if($('#pro_tax').prop("checked")==true){
		if(action=="add"){
			pro_tax = 1;
			tax_type = $('#tax_type').val();
			tariff = $('#tariff').val();
			comsumption = $('#comsumption').val();
			addedvalue = $('#addedvalue').val();
			postal = $('#postal').val();
			if(tariff=="" || tariff==undefined){
				layer.alert("请填写关税税率");
				return;
			}
			if(tariff<0){
				layer.alert("关税税率不能小于0");
				return;
			}
			if(comsumption=="" || comsumption==undefined){
				layer.alert("请填写消费税税率");
				return;
			}
			if(comsumption<1){
				layer.alert("消费税税率不能小于0");
				return;
			}
			if(addedvalue=="" || addedvalue==undefined){
				layer.alert("请填写增值税税率");
				return;
			}
			if(addedvalue<1){
				layer.alert("增值税税率不能小于0");
				return;
			}
			if(postal=="" || postal==undefined){
				layer.alert("请填写行邮税率");
				return;
			}
			if(postal<1){
				layer.alert("行邮税率不能小于0");
				return;
			}
		}else{
			pro_tax = 2;
		}
	}
	if($('#pro_mini_mshop').prop("checked")==true){
		if(action=="add"){
			is_mini_mshop = 1;
		}else{
			is_mini_mshop = 2;
		}
	}

	var idsStr = "";
	var ckIds = $("input[name='pro_ids']:checked");
	ckIds.each(function(i,n){
		if(i > 0){
			idsStr += ",";
		}
		idsStr = idsStr + n.value;
	});
	
	var url = "sale_label.php?action="+action+"&customer_id="+customer_id+"&idsStr="+idsStr+"&pro_out="+pro_out+"&pro_new="+pro_new+"&pro_hot="+pro_hot+"&pro_virtual="+pro_virtual+"&pro_currency="+pro_currency+"&pro_guess="+pro_guess+"&pro_freeshipping="+pro_freeshipping+"&pro_score="+pro_score+"&pro_issnapup="+pro_issnapup+"&pro_vp="+pro_vp+"&pro_limit="+pro_limit+"&pro_extend="+pro_extend+"&pro_tax="+pro_tax+"&is_mini_mshop="+is_mini_mshop;
	

	if(action=="add"){
		
		url += "&issnapup_start="+issnapup_start+"&issnapup_end="+issnapup_end+"&vp_text="+vp_text+"&limit_text="+limit_text+"&currency_text="+currency_text+"&extend_text="+extend_text+"&tax_type="+tax_type+"&tariff="+tariff+"&comsumption="+comsumption+"&addedvalue="+addedvalue+"&postal="+postal;
	
	}
	location.href = url;

}
//底图操作函数---start
 $("#uploadForm").submit(function(e){//上传底片
	e.preventDefault();
	 var formData = new FormData();
	 formData.append("file_button", "submit");
	 var formData = new FormData(document.getElementById("uploadForm"));//获取文件file数据
	 $.ajax({
		  url: 'uploadify.php' ,
		  type: 'POST',
		  data: formData,
		  async: false,
		  cache: false,
		  contentType: false,
		  processData: false,
		  success: function (returndata) {
		  switch(returndata){
			  case '10001':
					alert('不能上传此类型文件！');
			  break;
			  case '10002':
					alert('同名文件已经存在了！');
			  break;
			  case '10003':
					alert('移动文件出错！');
			  break;
			  case '10004':
					alert('文件太大！');
			  break;
			  case '10005':
					alert('请选择文件！');
			  break;
			  default:
					$('#hidden_img').val(returndata);
					$('#package_imgurl').attr('src',returndata);

			break;
		  }
			return;
		  },
		  error: function (returndata) {
			  alert('上传出错');
		  }
	 });
	return;
});
//底图操作函数---end

//批量设置购物币抵扣比例
$(".mul_currency").click(function(){
    //var currency_percentage = $("#currency_percentage").val();
    var ckIds = $("input[name='pro_ids']:checked");
    if(ckIds.length == 0){
        layer.alert("请选择需要批量设置购物币抵扣比例的产品！");
        return;	
    }
    $("#div_out6").show();
/*     var idsStr = "";
	ckIds.each(function(i,n){
		if(i > 0){
			idsStr += ",";
		}
		idsStr = idsStr + n.value;
	});
    $.getJSON("ajax_operation.php",{idsStr:idsStr,currency_percentage:currency_percentage,op:8},function(json){
        alert(json.msg);
    }); */
});

$(".div_out6_btn1").click(function(){
    $(this).attr("disabled","disabled");
    var currency_percentage = $("#currency_percentage").val();
    var ckIds = $("input[name='pro_ids']:checked");
    if(ckIds.length == 0){
        layer.alert("请选择需要批量设置购物币抵扣比例的产品！");
        return;	
    }
    var idsStr = "";
	ckIds.each(function(i,n){
		if(i > 0){
			idsStr += ",";
		}
		idsStr = idsStr + n.value;
	});
    $.getJSON("ajax_operation.php",{idsStr:idsStr,currency_percentage:currency_percentage,op:8},function(json){
        alert(json.msg);        
        $("#currency_percentage").removeAttr("disabled");
        $("#div_out6").hide();
    });   
    
});   

$(".div_out6_btn2").click(function(){ 
    $("#div_out6").hide();
});


$(".del-btn").click(function(){
	$(this).attr("disabled","disabled");
	var pid = $(this).data("pid");
	
	$.getJSON("ajax_operation.php",{pid:pid,op:9},function(json){
		$(this).removeAttr("disabled");

		if(json.code==2){//关联了换购活动的产品不能删除
			layer.alert(json.msg);
		}else{
			layer.confirm(json.msg, {
				title:'警告',
				btn: ['确认删除','取消']
			}, function(confirm){

                // 如同步，则在商城原有删除逻辑后判断是否为订货系统产品，是的话同时将orderingretail_product中isvalid=false 。 如不同步，则将订货系统产品改为下架store_status = down；
                var my_url = document.URL + "&keyid="+ pid +"&pagenum="+ pagenum +"&op=del";
                if(json.from == 'orderingretail'){
                    layer.confirm('该产品已关联到订货系统，是否同步删除？', {
                        title:'警告',
                        btn: ['确认同步','取消']
                    }, function(confirm){
                        layer.close(confirm);
                        location.href = my_url + "&from=orderingretail&is_Synchronize=1";//同步
                        // console.log('my_url:'+my_url + "&from='orderingretail'&is_Synchronize=1");
                    }, function(){
                        layer.msg('取消同步', {
                            time: 4000,
                            btn: ['确认'],
                            icon:1
                        });
                        location.href = my_url + "&from=orderingretail&is_Synchronize=0";//不同步
                        // console.log('my_url:'+my_url + "&from='orderingretail'&is_Synchronize=0");
                    });
                }


                if(json.from != 'orderingretail'){
                    layer.close(confirm);
                    location.href = my_url;
                }
				
			}, function(){
				layer.msg('已取消', {
					time: 4000,
					btn: ['确认'],
					icon:1
				});
			});
		}
        
    }); 
});

//中文，英文，多小数点过滤
 function clearNoNumNew(obj){
     //要输入负数，所以屏蔽前两个
	//obj.value = obj.value.replace(/[^\d.]/g,""); //清除"数字"和"."以外的字符
    //obj.value = obj.value.replace(/^\./g,""); //验证第一个字符是数字而不是
    obj.value = obj.value.replace(/\.{2,}/g,"."); //只保留第一个. 清除多余的
    obj.value = obj.value.replace(".","$#$").replace(/\./g,"").replace("$#$",".");
    obj.value = obj.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3'); //只能输入两个小数
} 
 
$("#currency_percentage").on("blur",function(){
		
		var currency_percentage = $(this).val();
		console.log(parseFloat(currency_percentage));
		if(isNaN(currency_percentage) || (parseFloat(currency_percentage) < -1 || parseFloat(currency_percentage) > 100 || (-1< parseFloat(currency_percentage) && parseFloat(currency_percentage) <0))){
			alert("请输入正确的购物币抵扣比例！");
            $('#currency_percentage').val('-1');
			return;
		}
	});  

	     
//批量设置购物币抵扣比例---end

//复制前端链接到剪切板
/* 	var clipboard = new Clipboard('.copy_btn', {	
        text: function(e) {
            return link_url+"/weixinpl/mshop/product_detail.php?pid="+$(e).attr('data-id')+"&customer_id="+customer_id;
        }
    });
    clipboard.on('success', function(e) {
        alert("复制成功！")
    }); */
//复制前端链接到剪切板