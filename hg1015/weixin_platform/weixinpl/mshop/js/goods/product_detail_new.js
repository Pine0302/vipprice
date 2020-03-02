// 状态变数
var menuState = 1; // 1- 3-tab页, 2- 4-tab页
var secondState = 1;
var shangpin ;
var mid_flag = 0;
var bar_z_index = 100;
var change_menuState_1_2 = 0;
var badgeCount =0;
var is_open = 1;


$(document).ready(function () {

    // data  initialize
    if (p_need_score < 1) {
        $(".sign_add").hide();
        $(".need_score_span").hide();
    }

    $("#gallery-tab").css("maxHeight", w_width + "px");
    $("#scroll").css("maxHeight", w_width + "px");
    $("#scroll").css("overflow", "hidden");
    if (is_showdiscuss > 0) {
        pullUpAction(0, dis_pagenum);
    }

    //initData();
    if (issnapup == 1) {
        GetRTime2(buystart_time, buyend_time);
    }
    //旧限购活动
    if (islimit == 1) {
    	if( is_buy_sale_model==0){
			$("#joinCar").attr("disabled", true);
			$('#button4').attr("disabled", true);
		}else if( is_buy_sale_model==1 ){
			$("#another_joinCar").attr("disabled", true);
		}
    }
    //新限购活动
    if (restricted_isout == 1) {
        check_user_restricted();
        GetActCountDown(buystart_time, buyend_time);
    }

    //积分兑换活动，要倒计时
    if (pro_act_type == 22) {
        collageTime(collage_start_time, collage_end_time);
    }

    //拼团活动倒计时
    if (is_collage_product == 1) {
    	console.log(collage_start_time);
    	console.log(collage_end_time);
        collageTime(collage_start_time, collage_end_time);
    }

    guess_you_like();

    shanpinFirstShow();
    menuState = 1;
    $(window).scrollTop(0);
    $(".shangpin-dialog").hide();
    // scroll事件监听
    $(window).scroll(function () {
        var bot = 300; //bot是底部距离的高度
        var top = $("#secondmenu2").position().top;
        var curPosY = top - $(window).scrollTop();

        if ((curPosY <= 0 && curPosY >= -40) && (menuState != 0)) {
            //showMenuBar(1);
            $(".titlebar-container-div").css("z-index", "-10");
            menuState = 0;
        }


        if ((curPosY < -40) && (menuState != 2)) {
            //showMenuBar(2);
            menuState = 2;
            $(".titlebar-container-div").css("z-index", "120");
        }
        if ((curPosY > 0) && (menuState != 1)) {
            //showMenuBar(1);
            menuState = 1;
            $(".titlebar-container-div").css("z-index", "120");
        }

    });
    var winWidth = $(window).width();
    // $(".titlebar-container-menu-item").width((winWidth-30)/3);

    //$("#badge-span").hide();
    //详情栏到顶固定
    var st = $('#secondmenu2').offset().top;
    $(window).scroll(function () {
        if ($(window).scrollTop() >= st) {
            $('#secondmenu2').addClass('fix');
            $('.titlebar-container-menu-div-top').hide();
        }
        else if ($(window).scrollTop() <= st) {
            $('#secondmenu2').removeClass('fix');
            $('.titlebar-container-menu-div-top').show();
        }
    });
    get_product_number(); //更新购物车商品数量

    var clone = $('.parent_attr_img').clone();
    $('.parent_attr_img').remove();
    $('.pros-box').prepend(clone);

    //属性点击事件
    $(".parent_attr_img").find(".pos_div").click(function () {
        var attr_img = $(this).attr('attr_img');
        var attr_id = $(this).attr('attr_index');
        if (attr_img != '') {
            $('#Preview').find('img').attr('src', attr_img);
            $('#Preview').attr('value', attr_id);
        } else {
            $('#Preview').find('img').attr('src', default_imgurl);
            $('#Preview').attr('value', 0);
        }
    });

});



// 事件监听器
//返回按键点击事件
 $(".header-btn").click(
 	 function(){
 	 	goBack();
 	 }
 );
 //详情页标签显示
 $(".titlebar-container-menu-secondary #item1").find('.menu-title').find('span').addClass('select');

/*评论加载开始*/


function pullUpAction (type,pagenum) {
	if (eva_lock ==false ){ //如果没有上锁
		eva_lock=true;
	$.ajax({
		url: "get_discuss.php",
		type:"POST",

		data:{'pid':pid,'type':type,'dis_pagenum':pagenum},
		dataType:"json",
		success: function(results){
			if( results == 0 ){
				is_open = 0;

				return;
			}

			dis_pagenum = pagenum+1;
			var len 	= results.length;
			var str 	= "";
			//console.log(results[0].user_id);
			for(i=0;i<len;i++){
				//console.log(results[i]);
				li = document.createElement('li');
				var uid 			= results[i].user_id;
				var username 		= results[i].username;
				var headimgurl 		= results[i].headimgurl;
				var level 			= results[i].level;
				var discuss 		= results[i].discuss;//评论
				var img_div 		= results[i].img_div;//评论图片
				var timestr 		= results[i].timestr;//评论时间
				var discuss_author 	= results[i].discuss_author;//商家回复
				var timestr_author 	= results[i].timestr_author;//商家回复时间
				var discuss_zj	 	= results[i].discuss_zj;//粉丝追加评论
				var img_div2 		= results[i].img_div2;//粉丝追加图片
				var timestr2 		= results[i].timestr2;//粉丝追加评论时间
				var discuss_author2 = results[i].discuss_author2;//商家回复追加评论
				var timestr_author2	= results[i].timestr_author2;//商家回复追加评论时间
				var levelname="好评";
				switch(parseInt(level)){
					case 2:
					   levelname="中评";
					   break;
					case 3:
					   levelname="差评";
					   break;
				}

				timestr=timestr.substring(0,10);//截取日期

				str +="<li><div class = \"item\" >";
				str +="<div class = \"pingjia-item-left\" ><img class=\"am-circle\" src = "+headimgurl+" width=5 height=50></div>";
				str +="<div class = \"pingjia-item-right\" >";
				str +="<div class = \"pingjia-item-right-row1\" >";
				str +="<span class = \"pingjia-item-right-row1-span1\" >"+username+"</span>";
				str +="<span> &nbsp;&nbsp;&nbsp;</span>";
				str +="<span class = \"pingjia-item-right-row1-span2\" >"+levelname+"</span>";
				str +="<div class = \"pingjia-item-right-row1-right\" >"+timestr+"</div>";
				str +="</div>";
				str +="<div class = \"pingjia-row1\" >"+discuss+"</div>";
				if(img_div != "" ){
					str +="<div class = \"pingjia-row2\" >"+img_div+"</div>";
				}
				if( discuss_author != "" ){
					str +="<div class = \"pingjia-row4\" >";
					str +="<span class=\"pingjia-row4-span1\"> 商家回复:</span>";
					str +="<span class=\"pingjia-row4-span2\">"+discuss_author+"</span>";
					str +="</div>";
				}
				if( discuss_zj != "" || img_div2 != "" ){
					str +="<div class = \"pingjia-row4\" >";
					str +="<span class=\"pingjia-row4-span1\"> 追加:</span>";
					if( discuss_zj != "" ){
						str +="<span class=\"pingjia-row4-span2\">"+discuss_zj+"</span>";
					}
					if( img_div2 != "" ){
						str +="<span class=\"pingjia-row4-span2\">"+img_div2+"</span>";
					}
					str +="</div>";
				}
				if( discuss_author2 != "" ){
					str +="<div class = \"pingjia-row4\" >";
					str +="<span class=\"pingjia-row4-span1\"> 商家回复:</span>";
					str +="<span class=\"pingjia-row4-span2\">"+discuss_author2+"</span>";
					str +="</div>";
				}
				str +="</div>";
				str +="</div></li>";
			}

			$("#thelist").append(str);
			eva_lock=false;
			var pingjia_image_width = ($(window).width()-90-10*2)/3
			$("#wrapper .pingjia_image").css("width",pingjia_image_width);
			$("#wrapper .pingjia_image").css("height",pingjia_image_width);

			if(len<10){ //判断评论加载完毕
				$('#pinterestDone').show();
				$('#pinterestMore').hide();
				eva_done=1;
			}else{
				$('#pinterestDone').hide();
				$('#pinterestMore').show();
				eva_done=0;
			}
		},error: function(results){
			//console.log(results);
		}

	});
}
}
	$(window).scroll(function () {
	if($('.shangpin-second4').length>0){
	  if(!$('.shangpin-second4').is(':hidden')){
	  	if(eva_done){
	       			$('#pinterestDone').show();
					$('#pinterestMore').hide();
	       		}
	       		else{
	    var st=$('#pinterestMore').offset().top-$(window).height();
	       	if($(window).scrollTop()>=st){
	       			pullUpAction(PL,dis_pagenum);
	       			console.log('happen'+dis_pagenum);
	       		}
			}
		}
	}
	    });








/*评论加载结束*/


/*猜你喜欢开始*/
function guess_you_like(){
	var xhHtml = "";
	$.ajax({
		url: "get_guess.php",
		type:"POST",
		data:{'customer_id':customer_id,'pid':pid},
		dataType:"json",
		success: function(results){
			var len 	= results.length;
			if( len == 0 ){
				$(".shangpin-panel4").hide();
				$(".shangpin-panel4_xian").hide();
			}
			for(i=0;i<len;i++){
				xhHtml +='<div class = "xh_pannel" id = "xh_pannel_'+results[i].pid+'"indexid = "'+results[i].pid+'">';
				xhHtml +='<a href="product_detail.php?customer_id='+customer_id+'&pid='+results[i].pid+'">';
				xhHtml +='<img class="xh_img" src = "'+results[i].default_imgurl+'">';
				xhHtml +='	    		<div class="xh_title shangpin-panel4-item2-xh_title"><span>'+results[i].name+'</span></div>';
				xhHtml +='	    		<div class="shangpin-panel4-item2-red-span"><span>￥'+results[i].now_price+'</span></div>';
				xhHtml +='	    	</a>';
				xhHtml +='	    	</div>';
			}
			$(".shangpin-panel4 #shangpin-panel4-item2").html(xhHtml);
			var xh_img_width = ($(window).width()-10*2-5*2*3)/3;//($(window).width()-10*2-10*2)/3;
			$(".xh_img").css("width",xh_img_width);
			$(".xh_img").css("height",xh_img_width);
			$(".xh_title").css("width",xh_img_width);
		}
	});
}
/*猜你喜欢结束*/


/*倒计时开始*/
// function judge_type(buystart_time,buyend_time,now_time){
// 	var type = 1;//1、即将开始；2、抢购中；3、已结束
// 	var surplus_time = 0;
// 	if( now_time < buystart_time ){
// 		type = 1;
// 	}else if( now_time > buyend_time ){
// 		type = 3;
// 	}else if(  buystart_time < now_time < buyend_time ){
// 		type = 2;
// 		surplus_time = buyend_time - now_time;
// 	}
// 	GetRTime(type,surplus_time);
// }

// function GetRTime(type,surplus_time){
// 	switch(type){
// 		case 1:
// 			$('.shangpin-first-remain').html("即将开始！");
// 			$("#joinCar").attr("disabled", true);
// 			$("#buyDiv").attr("disabled", true);
// 			break;
// 		case 3:
// 			$(".shangpin-first-remain").html("已结束！");
// 			$("#joinCar").attr("disabled", true);
// 			$("#buyDiv").attr("disabled", true);
// 			break;
// 		case 2:

// 			var nMS = surplus_time*1000-runtimes*1000;
// 			var nD  = Math.floor(nMS/(1000*60*60*24));
// 			var nH  = Math.floor(nMS/(1000*60*60))%24;
// 			var nM  = Math.floor(nMS/(1000*60)) % 60;
// 			var nS  = Math.floor(nMS/1000) % 60;
// 			$("#day").html(nD);
// 			$("#hour").html(nH);
// 			$("#minute").html(nM);
// 			$("#second").html(nS);
// 			break;
// 	}
// 	runtimes++;
// 	t = setTimeout("GetRTime("+type+","+surplus_time+")",1000);
// }
function GetRTime2(buystart_time,buyend_time){
var startTime ;
        $.ajax({type:"HEAD",url:'ajax_get_servertime.php',complete:function(x){ startTime = new Date(x.getResponseHeader("Date")).getTime();}})//获取服务器时间
    var count=0;
    document.addEventListener("visibilitychange", function (e) { //解决锁屏导致倒计时不准确的bug
         $.ajax({type:"HEAD",url:'ajax_get_servertime.php',complete:function(x){ startTime = new Date(x.getResponseHeader("Date")).getTime();}})//获取服务器时间
         count=0;
    }, true);
	timeInterval = setInterval(function(){
       count++;
       now_time=startTime+count*1000;
       now_time=now_time.toString().substring(0,10);
		$(".shangpin-first-remain").show();
			if( now_time < buystart_time){
				surplus_time = buystart_time - now_time;
				var nMS = surplus_time*1000-runtimes*1000;
				var nD  = Math.floor(nMS/(1000*60*60*24));
				var nH  = Math.floor(nMS/(1000*60*60))%24;
				var nM  = Math.floor(nMS/(1000*60)) % 60;
				var nS  = Math.floor(nMS/1000) % 60;
				$(".shangpin-first-remain").css("background", "#56A1E8");
				$(".shangpin-first-remain .information").css("background", "#0072e3");
				$("#time_name").html("距开抢仅剩:");
				$("#day").html(nD);
				$("#hour").html(nH);
				$("#minute").html(nM);
				$("#second").html(nS);
				$("#joinCar").attr("disabled", true);
				$("#sellDiv").attr("disabled", true);
				$("#buyDiv").attr("disabled", true);
				$("#singleBuy").attr("disabled", true);
				$("#another_joinCar").attr("disabled", true);
				$("#another_buyNow").attr("disabled", true);

			}else if( now_time > buyend_time ){
				$(".shangpin-first-remain").css("background", "#3f3f3f");
				$(".shangpin-first-remain").html("活动已结束！");
				$("#joinCar").attr("disabled", true);
				$("#buyDiv").attr("disabled", true);
				$("#sellDiv").attr("disabled", true);
				$("#singleBuy").attr("disabled", true);
				$("#another_joinCar").attr("disabled", true);
				$("#another_buyNow").attr("disabled", true);
				clearInterval(timeInterval);
			}else if(  buystart_time < now_time < buyend_time ){
				surplus_time = buyend_time - now_time;
				if( surplus_time <= 60){
					$(".shangpin-first-remain").css("background", "#e61529");

					$(".shangpin-first-remain .information").css("background", "#9c131a");
				}else{
					$(".shangpin-first-remain").css("background", "#02B300");
					$(".shangpin-first-remain .information").css("background", "#006000");
				}
				var nMS = surplus_time*1000-runtimes*1000;
				var nD  = Math.floor(nMS/(1000*60*60*24));
				var nH  = Math.floor(nMS/(1000*60*60))%24;
				var nM  = Math.floor(nMS/(1000*60)) % 60;
				var nS  = Math.floor(nMS/1000) % 60;

				$("#time_name").html("距结束仅剩:");
				$("#day").html(nD);
				$("#hour").html(nH);
				$("#minute").html(nM);
				$("#second").html(nS);
				$("#sellDiv").attr("disabled", false);
				$("#joinCar").attr("disabled", false);
				$("#buyDiv").attr("disabled", false);
				$("#singleBuy").attr("disabled", false);
				$("#another_joinCar").attr("disabled", false);
				$("#another_buyNow").attr("disabled", false);
			}
		if(now_time==0){ //没有网络的情况下无法获取当前时间
			$(".shangpin-first-remain").css("background", "#3f3f3f");
			$("#time_name").html("您的网络异常");
			$("#day").html("");
			$("#hour").html("");
			$("#minute").html("");
			$("#second").html("");
			$("#sellDiv").attr("disabled", true);
			$("#joinCar").attr("disabled", true);
			$("#buyDiv").attr("disabled", true);
			$("#singleBuy").attr("disabled", true);
			$("#another_joinCar").attr("disabled", true);
				$("#another_buyNow").attr("disabled", true);
		}
			//runtimes++;
			//setTimeout("GetRTime2("+buystart_time+","+buyend_time+","+now_time+")",1000); */

},1000);
}

/*倒计时结束*/

//拼团活动倒计时
function collageTime(collage_start_time,collage_end_time){
var startTime ;
        $.ajax({type:"HEAD",url:'ajax_get_servertime.php',complete:function(x){ startTime = new Date(x.getResponseHeader("Date")).getTime();}})//获取服务器时间
    var count=0;
    document.addEventListener("visibilitychange", function (e) { //解决锁屏导致倒计时不准确的bug
         $.ajax({type:"HEAD",url:'ajax_get_servertime.php',complete:function(x){ startTime = new Date(x.getResponseHeader("Date")).getTime();}})//获取服务器时间
         count=0;
    }, true);
    
    if(startTime == "" || startTime == undefined){	//防止以上的startTime出现undefined的情况
    	startTime = new Date().getTime();
    }

	timeInterval = setInterval(function(){
       count++;
       now_time=startTime+count*1000;
       now_time=now_time.toString().substring(0,10);
		$(".shangpin-first-remain-collage").show();
			if( now_time < collage_start_time){
				surplus_time2 = collage_start_time - now_time;
				var nMS = surplus_time2*1000-runtimes*1000;
				var nD  = Math.floor(nMS/(1000*60*60*24));
				var nH  = Math.floor(nMS/(1000*60*60))%24;
				var nM  = Math.floor(nMS/(1000*60)) % 60;
				var nS  = Math.floor(nMS/1000) % 60;
				$(".shangpin-first-remain-collage").css("background", "#56A1E8");
				$(".shangpin-first-remain-collage .information").css("background", "#0072e3");
				$("#collage_time_name").html("距开始仅剩:");
				$("#collage-day").html(nD);
				$("#collage-hour").html(nH);
				$("#collage-minute").html(nM);
				$("#collage-second").html(nS);
				$("#collageBuy").attr("disabled", true);
				// $("#collage_buyNow").attr("disabled", true);

			}else if( now_time > collage_end_time ){
				$(".shangpin-first-remain-collage").css("background", "#3f3f3f");
				$(".shangpin-first-remain-collage").html("活动已结束！");
				$("#collageBuy").attr("disabled", true);
				// $("#collage_buyNow").attr("disabled", true);
				// clearInterval(timeInterval);
			}else if(  collage_start_time < now_time < collage_end_time ){
				surplus_time2 = collage_end_time - now_time;
				if( surplus_time2 <= 60){
					$(".shangpin-first-remain-collage").css("background", "#e61529");

					$(".shangpin-first-remain-collage .information").css("background", "#9c131a");
				}else{
					$(".shangpin-first-remain-collage").css("background", "#02B300");
					$(".shangpin-first-remain-collage .information").css("background", "#006000");
				}
				var nMS = surplus_time2*1000-runtimes*1000;
				var nD  = Math.floor(nMS/(1000*60*60*24));
				var nH  = Math.floor(nMS/(1000*60*60))%24;
				var nM  = Math.floor(nMS/(1000*60)) % 60;
				var nS  = Math.floor(nMS/1000) % 60;

				$("#collage_time_name").html("距结束仅剩:");
				$("#collage-day").html(nD);
				$("#collage-hour").html(nH);
				$("#collage-minute").html(nM);
				$("#collage-second").html(nS);
				$("#collageBuy").attr("disabled", false);
				// $("#collage_buyNow").attr("disabled", false);
			}
		if(now_time==0){ //没有网络的情况下无法获取当前时间
			$(".shangpin-first-remain-collage").css("background", "#3f3f3f");
			$("#collage_time_name").html("您的网络异常");
			$("#collage-day").html("");
			$("#collage-hour").html("");
			$("#collage-minute").html("");
			$("#collage-second").html("");
			$("#collageBuy").attr("disabled", true);
			// $("#collage_buyNow").attr("disabled", true);
		}
	},1000);
}



// titlebar菜单点击事件
$(".titlebar-container-menu-primary-second-item").click(function(){
	if(product_voice=="" && product_vedio==""){
		//showAlertMsg("提示","商家未提供语音及视频","知道了");
        alertAutoClose("商家未提供语音及视频");
		return false;
	}else{
	/* 将GET方法改为POST ----start---*/
	var strurl = "media.php?customer_id="+customer_id+"&pid="+pid+"&sid="+sid;

    var objform = document.createElement('form');
	document.body.appendChild(objform);

	var obj_p = document.createElement("input");
	obj_p.type = "hidden";
	objform.appendChild(obj_p);
	obj_p.value = pid;
	obj_p.name = "pid";

	var obj_s = document.createElement("input");
	obj_s.type = "hidden";
	objform.appendChild(obj_s);
	obj_s.value = sid;
	obj_s.name = "sid";

	objform.action = strurl;
	objform.method = "POST"
	objform.submit();
	/* 将GET方法改为POST ----end---*/
}
});

// titlebar菜单点击事件


$(".shangpin-panel4 #shangpin-panel4-item3 #changeset").click(function(){
	guess_you_like();
});

/*详情，规格，售后，评论选择*/
$(".titlebar-container-menu-secondary #item1").click(function(){
	$(this).siblings('div').find('.menu-title').find('span').removeClass('select');
	$(this).find('.menu-title').find('span').addClass('select');
	secondState = 1;
	shanpinSecondShow();
});

$(".titlebar-container-menu-secondary #item2").click(function(){
	$(this).siblings('div').find('.menu-title').find('span').removeClass('select');
	$(this).find('.menu-title').find('span').addClass('select');
	secondState = 2;
	shanpinSecondShow();
});
$(".titlebar-container-menu-secondary #item3").click(function(){
	$(this).siblings('div').find('.menu-title').find('span').removeClass('select');
	$(this).find('.menu-title').find('span').addClass('select');
	secondState = 3;
	shanpinSecondShow();
});

$(".titlebar-container-menu-secondary #item4").click(function(){
	$(this).siblings('div').find('.menu-title').find('span').removeClass('select');
	$(this).find('.menu-title').find('span').addClass('select');
	secondState = 4;
	shanpinSecondShow();
});
//商品页2显示函数
function shanpinSecondShow(){
	$(".shangpin-content-second").hide();
	$(".shangpin-second"+secondState).show();
	$(".titlebar-container-menu-secondary .menu-underline").hide();
	$(".titlebar-container-menu-secondary #item"+secondState+" .menu-underline").show();

}
/*详情，规格，售后，评论选择*/

var collect_check = true;
//收藏产品、店铺

function collect(c_id,type,op){
	//type 1:商品 2：店铺
	var checkLogin = checkUserLogin();
	if ( !checkLogin ) {
		return;
	}
	/*if( from_type == 0 && user_id < 0 ){
		document.location = "../mshop/login.php?customer_id="+customer_id;
		return;
	}*/
	if(type == 2 && c_id == user_id){	//用户不能收藏自己的店铺
		showAlertMsg ("提示：","不能收藏自己的店铺","知道了");
		return false;
	}
	if(type == 1 && supply_id == user_id){	//用户不能收藏自己的产品
		showAlertMsg ("提示：","不能收藏自己的产品","知道了");
		return false;
	}
	if(collect_check){
		collect_check = false;
		if(op == 'add' && 1 == type){
			$('.collect_1').hide();
			$('.collect_2').show();
		}
		if(op == 'del' && 1 == type){
			$('.collect_1').show();
			$('.collect_2').hide();
		}
		if(op == 'add' && 2 == type){
			$('.collect_shop_1').hide();
			$('.collect_shop_2').show();
		}
		if(op == 'del' && 2 == type){
			$('.collect_shop_1').show();
			$('.collect_shop_2').hide();
		}
		$.ajax({
			url: 'collect_data.php?customer_id='+customer_id,
			data:{
				user_id:user_id,
				c_id:c_id,				//产品ID或店铺ID
				type:type,				//类型
				op:op				//收藏或取消收藏
			},
			type:"POST",
			dataType:"json",
			async:true,
			success:function(res){
				if(op == 'add'){
					if(-1 == res['status']){
						$('.collect_1').show();
						$('.collect_2').hide();
						showAlertMsg("提示","收藏失败！","知道了");
					}else if(-3 == res['status']){
						$('.collect_shop_1').show();
						$('.collect_shop_2').hide();
						showAlertMsg("提示","收藏失败！","知道了");
					}else if(3 == res['status']){
						$('#collect_num').text(res['collect_num']);
					}
				}else if(op == 'del'){
					if(-2 == res['status']){
						$('.collect_1').hide();
						$('.collect_2').show();
						showAlertMsg("提示","收藏失败！","知道了");
					}else if(-4 == res['status']){
						$('.collect_shop_1').hide();
						$('.collect_shop_2').show();
						showAlertMsg("提示","取消收藏失败！","知道了");
					}else{
						$('#collect_num').text(res['collect_num']);
					}
				}
				collect_check = true;
			},
			error:function(er){

			}
		});
	}
}



$("#totop").click(function(){ // 返回顶部
		shanpinFirstShow();
		$('body,html').animate({scrollTop:0},500);
		return false;
});

// button点击事件




// 商品页1显示函数
function shanpinFirstShow(){
	$(".titlebar-container-menu-primary").show();
	$(".titlebar-container-menu-secondary").hide();

	$(".shangpin-content").hide();
	$(".shangpin-first").show();
	//$(".shangpin-first #shangpin-img").attr("src",shangpin.url);
	//showRemainTime();

	//$(".shangpin-panel3-item1 #url2").attr("src",shangpin.url2);
	//$(".shangpin-panel3-item1 #name").text(shangpin.name);
    menuState = 1;
    $(".shangpin-second"+secondState).show();
    $("#secondmenu2").show();
    $(".titlebar-container-menu-secondary .menu-underline").hide();
	$(".titlebar-container-menu-secondary #item"+secondState+" .menu-underline").show();

}
$(".wholesale_div").on('click',function(){
	if($(this).hasClass('no-storenum')){ //属性库存置灰不可点击,不改变购买数量
		return;
	}
	var wholesale_num = $(this).attr('pos_num');
	$("#wholesale_num").val(wholesale_num);
	if(wholesale_num=='' || wholesale_num==undefined || wholesale_num==0){
        $("#mount_count").val(Math.round(1));
    }else{
        $("#mount_count").val(wholesale_num);
    }

	$(".minus").css({'background-color':'#ccc'});


})

/*商品二维码开始*/
function showcode(){
	$("#qrcode_div").slideToggle();
	$("#screen").show();

	var max_h=w_heigjt-130;
	$("#code_img").css("max-height",max_h+"px");

    get_qrcode();

/*  	var img = new Image();

	var dd = Math.random();
	var img_url ="../up/product/"+customer_id2+"/"+user_id+"/"+pid+"/product_qr_"+pid+".jpg?ver="+dd;
	img.src=  img_url;
	img.onload = function() {
		$("#code_img").html('<img src="'+img_url+'">');
		$("#code_img img").load(function(){
			var img_width = $("#code_img").width();
			var img_m = ( w_width - img_width )/2;
			//$("#code_img").css("left",img_m+"px");
			$(this).css("max-height",max_h+"px");
			$("#close_qr").css("padding-right",img_m+20+"px");
			$("#close_qr").show();
		});
	}
	img.onerror = function() {
		get_qrcode();
	} */
}

function show_personal_code(){
	if( user_id <= 0 ){
		showConfirmMsg('提示','您还有没有登录商城哦，赶快去登陆吧！','去登录','取消',function(){
			location.href='personal_center.php';
		})
		return false;
	}
	if( refresh('check_qr') ){ //判断临时二维码是否过期
		myUpload(callbackConfirm);
		var mh=$(window).height()*0.85;
		$('.am-modal-dialog').find('img').css('maxHeight',mh);
	}
	// refresh('check_qr');
}

function myUpload(callbackfunc) {
	$('#my-confirm').modal({
		relatedTarget: this,
		onConfirm: function(options) {
			callbackfunc("ok");
		},
		onCancel: function() {
			callbackfunc('cancel');
		}
	});
	$('#my-confirm').css('marginTop',0);
}

function callbackConfirm(retVal) {
	alert(retVal);
}

function close_code(){
	$("#qrcode_div").hide();
	$("#screen").hide();

}
function get_qrcode(){
    console.log('pid='+pid+';'+'customer_id='+customer_id+';'+'owner_id='+owner_id+';'+'user_id='+user_id+';'+'share_url='+share_url+';')
	$("#code_img").html('<i class="wx_loading_icon"></i>');
	$.ajax({
        type: "post",
        url: "get_product_qr.php",
        data: { pid: pid,customer_id:''+customer_id+'',owner_id:owner_id,user_id:user_id,share_url:share_url,yundian:yundian},
        success: function (result) {
			$("#code_img").html('<img src="'+result+'">');
			$("#code_img img").load(function(){
				var img_width = $("#code_img").width();
				var img_m = ( w_width - img_width )/2;
				//$("#code_img").css("left",img_m+"px");
				var max_h=w_heigjt-130;
				$(this).css("max-height",max_h+"px");
				$("#close_qr").css("padding-right",img_m+20+"px");
				$("#close_qr").show();
			});
        }
    })
}

/*商品二维码结束*/

/*身份提示*/
function showlevel(i){
	if(i==0){
		alertAutoClose("抱歉，此产品只有指定身份的人才能购买");
		return false;
	}
	return true;
}

/*属性选择开始*/
function showBuyDiv(type,is_buy_sale){
	if( isout == 1 ){
		//alert('商品已下架！');
		alertAutoClose("商品已下架！");
		return;
	}

	// if( is_QR == 1 ){
	// 	alertAutoClose("二维码核销商品不能购买！");
	// 	return;
	// }
	if( !(type == 2 && is_collage_product == 1 && groupBuyType == 2) ){	//拼团没有身份特权限制
		if(is_allow_buy == 0){
			alertAutoClose("抱歉，此产品只有指定身份的人才能购买");
			return;
		}
	}

	if( is_buy_sale == undefined ){
		is_buy_sale = 0;
	}

	$(".shangpin-dialog .content-button").hide();
	if(is_buy_sale == 0){

		if( type == 1 ){

			//预配送产品不能加入购物车
			if ( delivery_id > 0 ) {
				alertAutoClose("抱歉，"+delivery_name+"产品不能加入购物车");
				return;
			}

			$(".shangpin-dialog #div_joinCar").show();
			if(pro_act_type==22){//积分兑换产品活动
		    	alertAutoClose("该商品参与兑换活动，不能加入购物车");
				return;
		    }
		}else if( type == 2 ){

			if ( delivery_id > 0 ) {
				var preDeliveryDate = $('#preDeliveryDate').val(),
					preDeliveryTime = $('#preDeliveryTime').val();

				if ( preDeliveryDate == '' || preDeliveryTime == '' ) {
					isAutoShowPros = 1;
					$('.getmovetime').eq(0).click();
					return;
				}
			}

			$(".shangpin-dialog #div_buyNow").show();
			if( is_collage_product == 1 ){
				if( groupBuyType == 1 ){
					// $('#now_price').text(singlePrice);
					$('#now_price').text(p_now_price);
					$('#stock').text(p_storenum);
				} else {
					$('#now_price').text(groupPrice);
					$('#stock').text(groupStock);
				}
			}
		}
	}else if(is_buy_sale == 1){
		if ( delivery_id > 0 ) {
			var preDeliveryDate = $('#preDeliveryDate').val(),
				preDeliveryTime = $('#preDeliveryTime').val();

			if ( preDeliveryDate == '' || preDeliveryTime == '' ) {
				isAutoShowPros = 1;
				$('.getmovetime').eq(0).click();
				return;
			}
		}

		$(".shangpin-dialog #div_joinCar").show();
		$(".shangpin-dialog #div_buyNow").show();
		$("#div_joinCar").width('50%');
		$(" #div_buyNow").width('50%');
		$("#div_joinCar").css('line-height','0px');
		$(" #div_buyNow").css('line-height','0px');
		$(" #div_buyNow").css('float','left');
		$(" #div_joinCar").css('float','left');
		$(" #div_buyNow").css('background-color','#ffffff');
		$(" #div_joinCar").css('background-color','#ffffff');
	}

	$(".am-share").addClass("am-modal-active");
	//$("#share").hide();
	$("body").append('<div class="sharebg"></div>');
	$(".sharebg").addClass("sharebg-active");
	$(".shangpin-dialog").show();
	//$(".content").css({"height":w_heigjt-160+"px","overflow":"hidden"});
	$(".sharebg-active").click(function(){
		$(".am-share").removeClass("am-modal-active");
		setTimeout(function(){
			$(".sharebg").removeClass("sharebg-active");
			$(".sharebg").remove();
			$(".shangpin-dialog").hide();
			//$(".content").css({"height":"","overflow":""});
			$("#share").show();
		},300);
	});

	/*setTimeout(function(){
		$(".sharebg-active").trigger('click');
	},2000);*/
}

/*属性1选择开始*/
function showBuyDiv3(type){
	if( isout == 1 ){
		//alert('商品已下架！');
		alertAutoClose("商品已下架！");
		return;
	}
	if(is_allow_buy == 0){
		alertAutoClose("抱歉，此产品只有指定身份的人才能购买");
		return;
	}

	$(".shangpin-dialog .content-button").hide();
	if( type == 3 ){
		$(".shangpin-dialog #div_joinCar").show();
		$(".shangpin-dialog #div_buyNow").show();
		if( is_collage_product == 1 ){
			if( groupBuyType == 1 ){
				// $('#now_price').text(singlePrice);
				$('#now_price').text(p_now_price);
				$('#stock').text(p_storenum);
			} else {
				$('#now_price').text(groupPrice);
				$('#stock').text(groupStock);
			}
		}
	}

	$(".am-share").addClass("am-modal-active");
	$("#share").hide();
	$("body").append('<div class="sharebg"></div>');
	$(".sharebg").addClass("sharebg-active");
	$(".shangpin-dialog").show();
	//$(".content").css({"height":w_heigjt-160+"px","overflow":"hidden"});
	$(".sharebg-active").click(function(){
		$(".am-share").removeClass("am-modal-active");
		setTimeout(function(){
			$(".sharebg").removeClass("sharebg-active");
			$(".sharebg").remove();
			$(".shangpin-dialog").hide();
			//$(".content").css({"height":"","overflow":""});
			$("#share").show();
		},300);
	});

	/*setTimeout(function(){
		$(".sharebg-active").trigger('click');
	},2000);*/
}

/*卖弹窗开始*/
/*function showsellDiv(type){
	$("body").append('<div class="am-share confirm"></div>');
    $(".confirm").addClass("am-modal-active");
    $("body").append('<div class="sharebg" style="opacity:0"></div>');
    $(".sharebg").animate({"opacity":1});
    $(".sharebg").addClass("sharebg-active");
    if( from_type == 0 &&  user_id < 0 ){
		document.location = "../mshop/login.php?customer_id="+customer_id;
		return;
	}
    var sellprice = 0;
    $.ajax({
        type: "post",
        url: "reckon_commision.php",
		async: false,
        data: {
				user_id: user_id,
				is_promoters: is_promoters,
				Plevel: Plevel,
				now_price: p_now_price,
				init_reward: init_reward,
				pro_reward: pro_reward,
				issell_model: issell_model,
				for_price: for_price,
				is_consume: is_consume,
				cost_price: cost_price,
				customer_id: customer_id}
			   ,
        success: function (result) {
				sellprice=result;
        },
		error : function() {
				showAlertMsg("提示","网络错误","知道了");
		}
    });
    var html = "";
	    html += '<div class = "close_button">';
	    html += '<img src = "/weixinpl/mshop/images/info_image/btn_close.png"  width = "30">';
	    html += '</div>';
	    html += '<div class ="sellcontent">';
	    html += '  <div class = "sell_content_row1">';
	    html += '       <p class="sellp1 zicol">赚<span class="sellspan1">'+sellprice+'</span></p>';
	    html += '    </div>';
	    html += '<div class = "sell_content_row2">';
	    html += '    <p class="sellp2">只要你的好友通过你的链接购买此商品，你就能得到至少<span class="sellspan2 zicol">'+sellprice+'</span>的利润哦！</p>';
	    html += '</div>';
	    html += '</div>';
	    html += '<div class = "dlg_commit_left fuzhilianjie">';
	    html += '    <p class="sellp3" ><img class="sellimg3" src = "/weixinpl/mshop/images/info_image/fuzhiimg.png"/>分享</p>';
	    html += '</div>';
	    html += '<div class = "dlg_commit_left erweima">';
	    html += '    <p class="sellp4" onclick="showcode()"><img class="sellimg4" src = "/weixinpl/mshop/images/info_image/erweimaimgs.png"/>二维码</p>';
	    html += '</div>';


    $(".confirm").html(html);

    // dialog cancel_btn按键点击事件
    $(".sharebg-active,.close_button").click(function(){
        $(".am-share").removeClass("am-modal-active");
        $(".sharebg").animate({"opacity":0});
        $(".sharebg").remove();
      setTimeout(function(){
          $(".sharebg-active").removeClass("sharebg-active");
          $(".sharebg").remove();
          $(".confirm").remove();
      },300);
    });

    //复制链接事件
    $(".fuzhilianjie").click(function(){
      $(".helper").show();
      $("body").css({'overflow':"hidden"});
      $(".am-share").removeClass("am-modal-active");
      $(".sharebg").animate({"opacity":0});
      setTimeout(function(){
          $(".sharebg-active").removeClass("sharebg-active");
          $(".sharebg").remove();
          $(".confirm").remove();
      },100);
    });
    //二维码事件
    $(".erweima").click(function(){
      //?方法
      $(".am-share").removeClass("am-modal-active");
      $(".sharebg").animate({"opacity":0});
      setTimeout(function(){
          $(".sharebg-active").removeClass("sharebg-active");
          $(".sharebg").remove();
          $(".confirm").remove();
      },100);
    });
}*/

function showsellDiv(type){
	$("body").append('<div class="am-share confirm"></div>');
    $(".confirm").addClass("am-modal-active");
    $("body").append('<div class="sharebg" style="opacity:0"></div>');
    $(".sharebg").animate({"opacity":1});
    $(".sharebg").addClass("sharebg-active");
	var checkLogin = checkUserLogin();
	if ( !checkLogin ) {
		return;
	}
    /*if( from_type == 0 &&  user_id < 0 ){
		document.location = "../mshop/login.php?customer_id="+customer_id;
		return;
	}*/
    var sellprice = 0;
    $.ajax({
        type: "post",
        url: "reckon_commision.php",
		async: true,
        data: {
				user_id: user_id,
				is_promoters: is_promoters,
				Plevel: Plevel,
				now_price: p_now_price,
				init_reward: init_reward,
				pro_reward: pro_reward,
				issell_model: issell_model,
				for_price: for_price,
				is_consume: is_consume,
				cost_price: cost_price,
				customer_id: customer_id,
				model: type
				}
			   ,
        success: function (result) {
				sellprice=result;
				var html = "";
				html += '<div class = "close_button">';
				html += '<img src = "/weixinpl/mshop/images/info_image/btn_close.png"  width = "30">';
				html += '</div>';
				html += '<div class ="sellcontent">';
				html += '  <div class = "sell_content_row1">';
				html += '       <p class="sellp1 zicol">赚<span class="sellspan1">'+sellprice+'</span></p>';
				html += '    </div>';
				html += '<div class = "sell_content_row2">';
				html += '    <p class="sellp2">只要你的好友通过你的链接购买此商品，你就能得到约<span class="sellspan2 zicol">'+sellprice+'</span>的利润哦！</p>';
				html += '</div>';
				html += '</div>';
				html += '<div class = "dlg_commit_left fuzhilianjie">';
				html += '    <p class="sellp3" ><img class="sellimg3" src = "/weixinpl/mshop/images/info_image/fuzhiimg.png"/>分享</p>';
				html += '</div>';
				html += '<div class = "dlg_commit_left erweima">';
				html += '    <p class="sellp4" onclick="showcode()"><img class="sellimg4" src = "/weixinpl/mshop/images/info_image/erweimaimgs.png"/>二维码</p>';
				html += '</div>';

				$(".confirm").html(html);

				//开始绑定事件
				// dialog cancel_btn按键点击事件
				$(".sharebg-active,.close_button").click(function(){
					$(".am-share").removeClass("am-modal-active");
					$(".sharebg").animate({"opacity":0});
					$(".sharebg").remove();
				  setTimeout(function(){
					  $(".sharebg-active").removeClass("sharebg-active");
					  $(".sharebg").remove();
					  $(".confirm").remove();
				  },300);
				});

				//复制链接事件
				$(".fuzhilianjie").click(function(){
				  $(".helper").show();
				  $("body").css({'overflow':"hidden"});
				  $(".am-share").removeClass("am-modal-active");
				  $(".sharebg").animate({"opacity":0});
				  setTimeout(function(){
					  $(".sharebg-active").removeClass("sharebg-active");
					  $(".sharebg").remove();
					  $(".confirm").remove();
				  },100);
				});
				//二维码事件
				$(".erweima").click(function(){
				  //?方法
				  $(".am-share").removeClass("am-modal-active");
				  $(".sharebg").animate({"opacity":0});
				  setTimeout(function(){
					  $(".sharebg-active").removeClass("sharebg-active");
					  $(".sharebg").remove();
					  $(".confirm").remove();
				  },100);
				});

        },
		error : function() {
				alertAutoClose("网络错误");
		}
    });



}


function chooseDiv(prid,subid){
	console.log("chooseDiv("+prid+","+subid+")");
	
	//console.log('ppriceHash_key_length:'+ppriceHash_key_length);
	var n_pridsubid=prid+"_"+subid;
	var classname = $("#pro_div_"+n_pridsubid).attr("class");
	var ind = classname.indexOf("active");
	
	if($("#pro_div_"+n_pridsubid).hasClass('no-storenum')){ //属性库存置灰不可点击
		return;
	}
	
	if(classname.indexOf("active")!=-1){
		$("#pro_div_"+n_pridsubid).removeClass("active");
		$("#invalue_"+prid).attr("value","");
		subid = "";
		
		//当属性组合任一个取消后，选项重新可选 start
		if(typeof(compareKey_arr) != 'undefined' ){
			console.log('compareKey_arr')
			console.log(compareKey_arr)
			for( i in compareKey_arr){
				$(".pos_"+compareKey_arr[i]).removeClass("no-storenum");
			}
			compareKey_arr = [];//清空数组
		}
		
		//当属性组合任一个取消后，选项重新可选 end
	}else{
		$(".pos_"+prid).removeClass("active");
		$("#pro_div_"+n_pridsubid).addClass("active");
		$("#invalue_"+prid).attr("value",subid);
		
		if(typeof(compareKey_arr) != 'undefined' ){
			
			/*属性置灰后，相同父属性一级，点击其他子属性不改变置灰状态 start */
			
			for(i in compareKey_arr){
		        var is_set = true;
					
					for(key in selproHash._hash){
					
						if(selproHash.items(key).indexOf(compareKey_arr[i]) >= 0){
							if(key == prid)
							{
								is_set = false;    
								break;
							}								
							
						}		

					}
					if(is_set){
						$(".pos_"+compareKey_arr[i]).removeClass("no-storenum");
					}	
			}
		
			/*属性置灰后，相同父属性一级，点击其他子属性不改变置灰状态 end */
			
		}
	}
	var removeid = "";
	//console.log("selproHash_table:"+selproHash);
	if (selproHash.contains(prid)) {
        removeid = selproHash.items(prid);
    }
    choose_arr[prid] = subid;
	var removeids = removeid.split("_");
	var str = "";
    defaultpids = default_pids.split("_");
	var dlen = defaultpids.length;
	var isadd = false;

	for (var i = 0; i < dlen; i++) {
        var did = defaultpids[i];
        if (!did) {
            continue;
        }
        var isin = false;
        for (var j = 0; j < removeids.length; j++) {
            var rid = removeids[j];
            if (rid == did) {
                isin = true;
            }
        }
        if (subid > did) {
            if (!isin) {
                str = str + did + "_";
            }
            if (i == dlen - 1) {
                if (!isadd) {
                    if (subid != "") {
                        str = str + subid + "_";
                        isadd = true;
                    }
                }
            }

        } else {
            if (!isadd) {
                if (subid != "") {
                    str = str + subid + "_";
                }
                isadd = true;
            }
            if (!isin) {
                str = str + did + "_";
            }
        }
    }
	if (str != "") {
        str = str.substring(0, str.length - 1);
        default_pids = str;
    } else {
        if (subid != "") {
            str = subid;
            default_pids = default_pids + str;
        } else {
            default_pids = "";
        }

    }
	
	
	/*产品属性组合是否可用判断start*/
	var sStr    = str;
	check_stock_change_btn(sStr);
	/*产品属性组合是否可用判断end*/
	setPrice(str);

}


function sortNumber(a,b)
{
return a - b;
}
function setPrice(dstr) {
	console.log(ppriceHash);
	console.log("proids:"+dstr);
	if (ppriceHash.contains(dstr)) {
		var pprices = ppriceHash.items(dstr); //console.log("3:"+pprices);
        var pparr = pprices.split("_");
		var orgin_price = pparr[0];
		if( is_collage_product == 1 ){
			if( groupBuyType == 1 ){
				// var now_price = singlePrice;	//所有属性统一价格
				// var storenum = pparr[2];		//团购库存
				var now_price = pparr[1];
				var storenum = pparr[2];
			} else {
				var now_price = groupPrice;		//团购价
				var storenum = groupStock;		//团购库存

			}
		} else {
			var now_price = pparr[1];
			var storenum = pparr[2];
		}

        if(pro_act_type==22){//积分兑换产品库存价格不变
            var now_price = integral_now_price;
            var storenum = p_storenum;
        }

		var need_score = pparr[3];
		var unit = pparr[4];
		var pro_weight = pparr[5];
		try {
            document.getElementById("now_price").innerHTML =  now_price;
        } catch (e) {
        }
		try {
            document.getElementById("need_score").innerHTML = need_score;
			if( need_score > 0 ){
				$(".sign_add").show();
				$(".need_score_span").show();
			}else{
				$(".sign_add").hide();
				$(".need_score_span").hide();
			}
        } catch (e) {
        }
		try {
            document.getElementById("stock").innerHTML = storenum;
        } catch (e) {
        }
	}else {
		console.log(333);
		//console.log(p_storenum);
		//$("#stock").html(p_storenum);
		try {
            document.getElementById("now_price").innerHTML = p_now_price;
        } catch (e) {
        }
		try {
            document.getElementById("need_score").innerHTML = p_need_score;
			if( p_need_score > 0 ){
				$(".sign_add").show();
				$(".need_score_span").show();
			}else{
				$(".sign_add").hide();
				$(".need_score_span").hide();
			}
        } catch (e) {
        }
		try {
            document.getElementById("stock").innerHTML = p_storenum;
            //document.getElementById("stock").innerHTML = 0;
        } catch (e) {
        }
		if( is_collage_product == 1 ){
			if( groupBuyType == 2 ){
				document.getElementById("now_price").innerHTML = groupPrice;
				document.getElementById("stock").innerHTML = groupStock;
			}
		}
        if(pro_act_type==22){//积分兑换产品库存价格不变
            document.getElementById("now_price").innerHTML = integral_now_price;
            document.getElementById("stock").innerHTML = p_storenum;
        }
	}
}

/*属性选择结束*/

/*数量加减开始*/
function addNum(){

    var mount_count = $("#mount_count").val();
	if( is_collage_product && groupBuyType == 2 ){
		//拼团不做限购限制
	} else if( parseInt(mount_count) + parseInt(count_commodity) >= parseInt(limit_num) && islimit==1 ){
		showXiangouMsg("当前商品当天限购数量为"+limit_num,"知道了");
		return
	}
	if(mount_count==999){
		return
	}
	var storenum = $("#stock").html();
	storenum = parseInt(storenum,10);

	if(parseInt(mount_count,10)>=storenum){
	   return;
	}
	mount_count ++;

	//批发属性
	if(is_wholesale==1){
		var wholesale_num = $("#wholesale_num").val();

		if( mount_count > wholesale_num ){
			$(".minus").css({'background-color':'#fff'});
		}
	}

	$("#mount_count").val(mount_count);
	if(yundian == pro_yundian_id && yundian != -1){
		$("#yundian_num").val(mount_count);
	}
}

function minusNum(){
    var mount_count = $("#mount_count").val();
	if(mount_count==1){
		return
	}
	if(mount_count>1){
		mount_count --;
		//批发属性
		if(is_wholesale==1){
			var wholesale_num = $("#wholesale_num").val();
			if( mount_count < wholesale_num ){
				$(".minus").css({'background-color':'#ccc'});
				return;
			}
		}
		$("#mount_count").val(mount_count);
		if(yundian == pro_yundian_id && yundian != -1){
		  $("#yundian_num").val(mount_count);
		}
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

/*购物数量固定格式开始*/
function clearNoNum(obj)
{
	//先把非数字的都替换掉，除了数字
	obj.value = obj.value.replace(/[^\d]/g,"");
	if(obj.value>999){
		obj.value = 999;
	}
	if( is_collage_product && groupBuyType == 2 ){
		//拼团不做限购限制
	} else if( obj.value >= limit_num && islimit==1 ){
		showXiangouMsg("当前商品限购数量为"+limit_num,"知道了");
		$(obj).val(limit_num);
		return
	}
}
/*购物数量固定格式结束*/

/*加入购物车开始*/
function addToCart(){
	/*var o_shop_id = localStorage.getItem("cart_user_"+user_id+"_shopid");
	if(o_shop_id==null || o_shop_id==''){
		o_shop_id=-1;
	}*/
	// return alert("o_shop_id: " + o_shop_id);
    //先登录才能加入购物车
    if (!checkUserLogin(window.location.href, '')) {
        return;
    }
    
	//预配送产品不能加入购物车
	if ( delivery_id > 0 ) {
		alertAutoClose("抱歉，"+delivery_name+"产品不能加入购物车");
		return;
	}
	if( is_QR == 1 ){
		alertAutoClose("二维码核销商品不能加入购物车！");
		return;
	}
	//判断产品为批发产品时，加入购物车数量是否大于等于最低限制
	if(is_wholesale == 1){
		var wholesale_num = $("#wholesale_num").val();
		var mount_count = $("#mount_count").val();
		if( parseInt(mount_count) < parseInt(wholesale_num) ){
			alertAutoClose("您购买的数量少于产品最低批发数量");
			return;
		}
	}

    if(pro_act_type==22){//积分兑换产品活动
    	alertAutoClose("该商品参与兑换活动，不能加入购物车");
		return;
    }



	// if( from_type == 0 && user_id < 0 ){
		// document.location = "../mshop/login.php?customer_id="+customer_id;
		// return;
	// }
	if( isvalid == false ){
		alertAutoClose("产品已下架！");
		return;
	}
	/* //重复判断
	if( is_QR == 1 ){
		alertAutoClose("二维码核销商品不能加入购物车");
		return;
	}*/
	// if( is_identity == 1 && pis_identity == 1){
		// alertAutoClose("提示","身份证验证商品不能加入购物车","知道了");
		// return;
	// }
	if( is_virtual == 1 ){
		alertAutoClose("虚拟商品不能加入购物车");
		return;
	}
	if( is_restricted == 1 && restricted_isout == 1 ){
		alertAutoClose("限购商品不能加入购物车");
		return;
	}
	var pro_arr		= [];//产品信息数组：商品id、数量、属性、主播房间id
	var pro_arrs 	= [];//产品数组
	var sid_arr		= [];//商店数组
	var json_arr	= [];//整个购物车数组
	var pid 		= $("#pid").val();//产品id
	var num 		= $("#mount_count").val();//数量
	var supply_id 	= $("#supply_id").val();//供应商ID
	var stock 		= $("#stock").text();//库存
	var pos_len 	= $(".pros-box .active").length;
	var pos_arr 	= "";//选中属性id拼接字符串

	var call_value = check_pos();//判断是否选择了属性


	if( call_value ){
		return;
	}
	if( num == 0 ){
		alertAutoClose('数量必须大于0才能加入购物车！');
		return;
	}
	if( is_stockOut == 1 && pro_act_type != 22){  //积分兑换产品库存不做判断
		var call_value = check_storenum(pid);
		if( call_value ){
			return;
		}
	}
	if( pro_card_level == 1 && pro_card_level_id != -1){
		var call_value = check_cardLevel(pro_card_level_id,shop_card_id);
		if( call_value ){
			return;
		}
	}
	if( parseInt(stock) < parseInt(num)  && pro_act_type != 22){ //积分兑换产品库存不做判断
		//alert('库存不足！');
		$(".sharebg").removeClass("sharebg-active");
		$(".sharebg").remove();
		$(".shangpin-dialog").hide();
		$("#share").show();
		alertAutoClose("库存不足！");
		return;
	}
	if( isout == 1 ){
		//alert('商品已下架！');
		$(".sharebg").removeClass("sharebg-active");
		$(".sharebg").remove();
		$(".shangpin-dialog").hide();
		$("#share").show();
		alertAutoClose("商品已下架！");
		return;
	}
	if( !yundian_isvalid ){
		alertAutoClose("店铺的产品已失效，不能进行购买",1000);
		return;
	}
	/*for( i = 0; i < pos_len ; i++ ){
	var pos_id = $(".pros-box .active").eq(i).attr("pos_id");
		if( pos_arr == "" ){
			pos_arr += pos_id;
		}else{
			pos_arr += "_"+pos_id;
		}
	}*/
	if(p_pro_str != ''){
		if( p_pro_str.indexOf('_') == -1 ){
			pos_arr = choose_arr[p_pro_str];
	    }else{
	    	for(p_index in p_pro_arr){
	    		pos_arr += choose_arr[p_pro_arr[p_index]]+'_';
	    	}
	    	pos_arr = pos_arr.substring(0, pos_arr.length - 1);
	    }
	}
	//所选地区是否有货
	selectedPros = pos_arr;
	if ( is_aog ) {
		var _check_aog = check_aog();
		if( _check_aog != 1 ) {
			alertAutoClose('您所在地区暂时无货！');
			return;
		}
	}
	var day_buy_num = 0;
	var cart_num 	= 0;
	if( islimit == 1 ){
		alertAutoClose("限购商品不能加入购物车");
		return;
		/*day_buy_num = is_limit_num(1);
		day_buy_num = is_limit_num(1);
		cart_num	= get_this_product_num(pid);
		var can_buy_num = parseInt(limit_num) - parseInt(day_buy_num);
		if( can_buy_num <= 0 ){
			showXiangouMsg("此商品限购"+limit_num+"件，您已购买！","知道了");
			return;
		}
		add_cart_num = parseInt(can_buy_num) - parseInt(cart_num);
		if( add_cart_num < 0 ){
			add_cart_num = 0;
		}
		if( add_cart_num < num ){
			num = add_cart_num;
		}*/
	}
	var topic_id=$('#topic_id').val().trim();//商城直播房间id
	var resource_id=$('#resource_id').val().trim();//商城直播房间id
	var mb_topic = '';
	if( topic_id > 0 ){
		mb_topic = 'topicid_'+topic_id;
	}else if( resource_id > 0 ){
		mb_topic = 'resourceid_'+resource_id;
	}
	// pro_arr.push(pid,num,pos_arr,mb_topic,check_first_extend,pro_act_type,pro_act_id)
	
	if(pro_yundian_id > 0){
		pro_arr.push(pid,num,pos_arr,mb_topic,check_first_extend,pro_act_type,pro_act_id,pro_yundian_id)
	}else{
		pro_arr.push(pid,num,pos_arr,mb_topic,check_first_extend,pro_act_type,pro_act_id,pro_yundian_id)
	}

	console.log(pro_arr);
	//localStorage.clear();
	/*判断localStorage.cart是否存在开始*/
	if( !localStorage.getItem("cart_"+user_id) ){
		localStorage.setItem("cart_"+user_id,"");
	}
	/*判断localStorage.cart是否存在结束*/
	if( from_type != 1 && user_id < 0 ){ //游客身份
		if( !localStorage.getItem("cart_visitor") ){
			localStorage.setItem("cart_visitor","");
		}
		var json = localStorage.getItem("cart_visitor");
	}else{
		if(o_shop_id>0){ //订货系统门店下购物车
			if( !localStorage.getItem("cart_user_"+user_id+"_shop_"+o_shop_id) ){
				localStorage.setItem("cart_user_"+user_id+"_shop_"+o_shop_id,"");
			}
			var json = localStorage.getItem("cart_user_"+user_id+"_shop_"+o_shop_id);
		}else{
			var json = localStorage.getItem("cart_"+user_id);
		}
	}
	console.log(json);

	if(json != ""){
		json_arr = eval(json);

		if(json_arr == null){		//防止购物车数据出现null值的情况
			json_arr = [];
		}
		console.log(json_arr);
		
		var arr_sid = [];
		var check_arr_sid = [];
		for( var i = 0; i < json_arr.length; i++){
			console.log(json_arr);
			var sid = json_arr[i][0];//localStorage里的供应商id
			var check_sid = json_arr[i][2];//识别云店产品或者是平台的产品
            if(check_sid == undefined){
                json_arr[i][2] = -1;
                check_sid = -1;
            }
			arr_sid.push(sid);
			check_arr_sid.push(check_sid);
		}
		console.log(arr_sid);
		console.log(check_arr_sid);		//防止出现供应商id和云店ID相同的情况，

		if(pro_yundian_id == -1){		//针对供应商产品或者平台产品的数组
			if( arr_sid.indexOf( supply_id ) > -1){
				for( var i = 0; i < json_arr.length; i++){
					var sid = json_arr[i][0];
					console.log(supply_id);
					console.log(sid);
					console.log(json_arr[i][2]);
					console.log(pro_yundian_id);
					if( sid == supply_id && json_arr[i][2] == -1){			//供应商产品或者平台产品的
						console.log('fdhsdfds');
						var pid_arr = json_arr[i][1];
						var join = true;
						var pid_num = 0;
						for( var j = 0; j < pid_arr.length; j++){
							var arr_pid = json_arr[i][1][j][0];//localStorage里的产品id
							if( arr_pid == pid ){
								pid_num = parseInt(pid_num) + parseInt(json_arr[i][1][j][1]);
							}
						}
						if( islimit ==1 && pid_num == can_buy_num ){
							join = false;
						}
						for( var j = 0; j < pid_arr.length; j++){
							var arr_pid = json_arr[i][1][j][0];//localStorage里的产品id
							var arr_pos = json_arr[i][1][j][2];//localStorage里的产品属性
							var arr_topic = json_arr[i][1][j][3];//localStorage里的电商直播id
							var arr_act_type = json_arr[i][1][j][5];//localStorage里的产品活动类型
							var arr_act_id = json_arr[i][1][j][6];//localStorage里的活动id
							if( arr_pid == pid && arr_pos == pos_arr && mb_topic == arr_topic && ((arr_act_type == pro_act_type && arr_act_id == pro_act_id) || (pro_act_type == -1 && pro_act_id == -1 && arr_act_type == undefined)) ){
								var arr_num = json_arr[i][1][j][1];//localStorage里的产品数量
								var count = 0;
								can_buy_num = parseInt(limit_num) - parseInt(pid_num) - parseInt(day_buy_num);
								count = parseInt(arr_num) + parseInt(num);
								json_arr[i][1][j][1] = count;//修改数量

								if (arr_act_type == undefined) {	//修改旧数据的活动类型和活动id
									json_arr[i][1][j][5] = pro_act_type;	//修改活动类型
									json_arr[i][1][j][6] = pro_act_id;		//修改活动id
								}

								join = false;
								break;
							}

						}
						if( join ){
							json_arr[i][1].push(pro_arr);//对应供应商插入商品
						}
						break;
					}
				}
			}else{
				//插入新的供应商商品
				if(pro_yundian_id > -1){
					pro_arrs.push(pro_arr);
					sid_arr.push(pro_yundian_id,pro_arrs,pro_yundian_id);
					json_arr.push(sid_arr);
				}else{
					pro_arrs.push(pro_arr);
					sid_arr.push(supply_id,pro_arrs,-1);
					json_arr.push(sid_arr);
				}
			}
		}else{			//针对云店产品的数组
			var first_show = arr_sid.indexOf( pro_yundian_id );
			if( arr_sid.indexOf( pro_yundian_id ) > -1 && check_arr_sid[first_show] == pro_yundian_id){
				for( var i = 0; i < json_arr.length; i++){
					var sid = json_arr[i][0];
					if( sid == pro_yundian_id && json_arr[i][2] == pro_yundian_id){			//供应商产品或者平台产品的
						var pid_arr = json_arr[i][1];
						var join = true;
						var pid_num = 0;
						for( var j = 0; j < pid_arr.length; j++){
							var arr_pid = json_arr[i][1][j][0];//localStorage里的产品id
							if( arr_pid == pid ){
								pid_num = parseInt(pid_num) + parseInt(json_arr[i][1][j][1]);
							}
						}
						if( islimit ==1 && pid_num == can_buy_num ){
							join = false;
						}
						for( var j = 0; j < pid_arr.length; j++){
							var arr_pid = json_arr[i][1][j][0];//localStorage里的产品id
							var arr_pos = json_arr[i][1][j][2];//localStorage里的产品属性
							var arr_topic = json_arr[i][1][j][3];//localStorage里的电商直播id
							var arr_act_type = json_arr[i][1][j][5];//localStorage里的产品活动类型
							var arr_act_id = json_arr[i][1][j][6];//localStorage里的活动id
							if( arr_pid == pid && arr_pos == pos_arr && mb_topic == arr_topic && ((arr_act_type == pro_act_type && arr_act_id == pro_act_id) || (pro_act_type == -1 && pro_act_id == -1 && arr_act_type == undefined)) ){
								var arr_num = json_arr[i][1][j][1];//localStorage里的产品数量
								var count = 0;
								can_buy_num = parseInt(limit_num) - parseInt(pid_num) - parseInt(day_buy_num);
								count = parseInt(arr_num) + parseInt(num);
								json_arr[i][1][j][1] = count;//修改数量

								if (arr_act_type == undefined) {	//修改旧数据的活动类型和活动id
									json_arr[i][1][j][5] = pro_act_type;	//修改活动类型
									json_arr[i][1][j][6] = pro_act_id;		//修改活动id
								}

								join = false;
								break;
							}

						}
						if( join ){
							json_arr[i][1].push(pro_arr);//对应供应商插入商品
						}
						break;
					}
				}
			}else{
				//插入新的供应商商品
				if(pro_yundian_id > -1){
					pro_arrs.push(pro_arr);
					sid_arr.push(pro_yundian_id,pro_arrs,pro_yundian_id);
					json_arr.push(sid_arr);
				}else{
					pro_arrs.push(pro_arr);
					sid_arr.push(supply_id,pro_arrs,-1);
					json_arr.push(sid_arr);
				}
			}
		}
	}else{
		//插入新的供应商商品
		if(pro_yundian_id > -1){
				pro_arrs.push(pro_arr);
				sid_arr.push(pro_yundian_id,pro_arrs,pro_yundian_id);
				json_arr.push(sid_arr);
		}else{
			pro_arrs.push(pro_arr);
			sid_arr.push(supply_id,pro_arrs,-1);
			json_arr.push(sid_arr);
		}
	}
	if( user_id > 0 ){
		if(o_shop_id>0){ //订货系统门店购物车数据
			localStorage.setItem("cart_user_"+user_id+"_shop_"+o_shop_id, JSON.stringify(json_arr));
		}else{
			localStorage.setItem("cart_"+user_id,JSON.stringify(json_arr));
		}
	}else{
		localStorage.setItem("cart_visitor",JSON.stringify(json_arr));
	}


	//console.log(localStorage.getItem("cart_"+user_id));
	//location.href="order_cart.php";
	$(".sharebg").removeClass("sharebg-active");
	$(".sharebg").remove();
	$(".shangpin-dialog").hide();
	$("#share").show();

	showXiangouMsg("恭喜你，加入购物车成功");
	get_product_number(); //更新购物车商品数量

    var timestamp = Date.parse(new Date());//获取当前时间戳
	timestamp = timestamp/1000;
	localStorage.setItem("cart_time_"+user_id,timestamp);//设置加入购物车时间
	uploadItem(pro_arr);//上传实时购物车数据


}
/*加入购物车结束*/

/*
	上传实时购物车数据
*/
function uploadItem(pro_arr){
	var cart_data = localStorage.getItem("cart_"+user_id);
	var cart_time = localStorage.getItem("cart_time_"+user_id);
	if( cart_data == '' || cart_data == null ){
		return;
	}else{
		$.ajax({ 
		type: "post",
		url: "/shop/index.php/Home/H5Cart/h5_cart_data2",
		data: {customer_id:customer_id2,user_id:user_id,cart_data:cart_data,pro_arr:pro_arr,cart_time:cart_time},
		async: false,
		success: function (result) {
			
		}    
	});
	}
}

/*立即购买开始*/

function buyGood(){
	//拼团路线会改变下面的参数，所以每次都读备份的数据
	is_stockOut = is_stockOut_bak;
	pro_card_level = pro_card_level_bak;


	var pid 		= $("#pid").val();//产品id
	var num 		= $("#mount_count").val();//数量
	var supply_id 	= $("#supply_id").val();//供应商ID
	if(supply_id==''){supply_id = -1;}
	var stock 		= $("#stock").text();//库存
	var topic_id= $('#topic_id').val().trim();//商城直播的房间id
	var resource_id= $('#resource_id').val().trim();//商城直播的房间id

	var need_score	= $("#need_score").text();//购买单个需要的积分
	need_score = parseInt(num)*need_score;//购买所有需要的积分

	var checkLogin = checkUserLogin();
	if ( !checkLogin ) {
		return;
	}
	/*if( from_type == 0 &&  user_id < 0 ){
		document.location = "../mshop/login.php?customer_id="+customer_id;
		return;
	}*/
    if(pro_act_type==22){//积分兑换产品活动
        var enough_integral 		= 0;   //是否足够商城积分
    	var enough_store_integral 	= 0;   //是否足够门店积分
    	if( parseInt(num) > parseInt(stock) ){
    		alertAutoClose("库存不足");
			return;
   		}
        if( user_integral>(need_integral*num) ){
           	enough_integral = 1;
        }
        if( user_store_integral>(need_store_integral*num) ){
           	enough_store_integral = 1;
        }
        if(shop_integral_onoff == 1 && store_integral_onoff == 1){		//都开启了
        	if(enough_integral == 0 && enough_store_integral == 0){
        		alertAutoClose("商城积分和门店积分皆不足，不能购买兑换积分产品");
				return;
        	}
        }else if( shop_integral_onoff == 1 ){							//只开启商城积分
        	if(enough_integral == 0 ){
        		alertAutoClose("商城积分不足，不能购买兑换积分产品");
				return;
        	}
        }else{															//只开启门店积分
        	if( enough_store_integral == 0 ){
        		alertAutoClose("门店积分不足，不能购买兑换积分产品");
				return;
        	}
        }
    }

    // if(pro_act_type == -1){
    // 	if(isvp == 1){
    // 		var enough_vp 	= 0;   //是否足够vp
    // 		if( parseInt(vp_val)>parseInt(vp_score) ){
    //        		enough_vp = 1;
    //     	}
    //     	if(enough_vp == 0){
    //     		alertAutoClose("vp值不足，不能购买vp产品");
				// return;
    //     	}
    // 	}
    // }

	//拼团产品走拼团路线
	if( is_collage_product && groupBuyType == 2 ){	//拼团路线不走限购

		is_stockOut = 0;	//不需要自动下架
		pro_card_level = 0;	//不需要会员卡等级
		check_collage_info = check_collage(user_id,pid,activitie_id,num);
		if( check_collage_info['code'] > 0 ){
			if( check_collage_info['code'] == 40008 ){
				groupStock = check_collage_info['data'][0];
			}
			alertAutoClose(check_collage_info['msg']);
			return;
		}

	} else if(!is_limit_num()){		//是否超过限购数量
		return;
	}

	//走限购活动路线
	if(restricted_isout == 1){
		check_restricted_info = check_restricted(user_id,pid,pro_act_id,num);
		if( check_restricted_info['errcode'] != 0 ){
			alertAutoClose(check_restricted_info['errmsg']);
			return;
		}
	}

	if(is_wholesale == 1){
		var wholesale_num = $("#wholesale_num").val();
		var mount_count = $("#mount_count").val();
		if( parseInt(mount_count) <  parseInt(wholesale_num) ){
			alertAutoClose("您购买的数量少于产品最低批发数量");
			return;
		}
	}
	if( isvalid == false ){
		alertAutoClose("产品已下架！");
		return;
	}

	if( num == 0 ){
		alertAutoClose('数量必须大于0才能购买！');
		return;
	}

	var call_value = check_pos();//判断是否选择了属性
	if( call_value ){
		return;
	}

	if( need_score > 0 ){
		var call_value = check_score(need_score,shop_card_id);//判断积分是否足够
		if( call_value ){
			return;
		}
	}


	if( is_stockOut == 1 && pro_act_type != 22 ){ //积分兑换产品库存不做判断
		var call_value = check_storenum(pid);
		if( call_value ){
			return;
		}
	}
	if( pro_card_level == 1 && pro_card_level_id != -1){
		var call_value = check_cardLevel(pro_card_level_id,shop_card_id);
		if( call_value ){
			return;
		}
	}

	if( parseInt(stock) < parseInt(num) && pro_act_type != 22){ //积分兑换产品库存不做判断
		//alert('库存不足！');
		alertAutoClose("库存不足！");
		return;
	}
	if( isout == 1 ){
		//alert('商品已下架！');
		alertAutoClose("商品已下架！");
		return;
	}
	if( !yundian_isvalid ){
		alertAutoClose("店铺的产品已失效，不能进行购买");
		return;
	}
	//获取产品ID
	var pid = $('#pid').val();
	//获取数量
	var rcount = $('#mount_count').val();
	//----获取选择的属性ID
	var sel_pro_str = '';
	/*$.each($('.pos_div'),function(){
		self = $(this);
		if(self.hasClass('active')){
			//console.log(self);
			var sel_pros_id = self.attr('pos_id');
			sel_pro_str += sel_pros_id+'_';
		}

	});*/
	if(p_pro_str != ''){
		if( p_pro_str.indexOf('_') == -1 ){
			sel_pro_str = choose_arr[p_pro_str];
	    }else{
	    	for(p_index in p_pro_arr){
	    		sel_pro_str += choose_arr[p_pro_arr[p_index]]+'_';
	    	}
	    	sel_pro_str = sel_pro_str.substring(0, sel_pro_str.length - 1);
	    }
	}

	clear_local_Storage();//下单前清除订单页面的本地存储

	//----------跳转到结算页面

	//sel_pro_str = sel_pro_str.substr(0,sel_pro_str.length-1)
	//console.log(sel_pro_str);
	//----获取选择的属性ID

	//所选地区是否有货
	selectedPros = sel_pro_str;
	if ( is_aog ) {
		var _check_aog = check_aog();
		if( _check_aog != 1 ) {
			alertAutoClose('您所在地区暂时无货！');
			return;
		}
	}

	var delivery_time = '';
	//获取预配送时间
	if ( delivery_id > 0 ) {
		var preDeliveryDate = $('#preDeliveryDate').val(),
			preDeliveryTime = $('#preDeliveryTime').val(),
			delivery_time = preDeliveryDate+' '+preDeliveryTime;

	}


	//----转POST数据提交
	var post_object = [];
	//产品ID
	var post_data1 = new Array(1);
	post_data1['key'] = 'pid';
	post_data1['val'] = pid;
	//选择的属性
	var post_data2 = new Array(1);
	post_data2['key'] = 'sel_pros';
	post_data2['val'] = sel_pro_str;

	var post_data3 = new Array(1);
	post_data3['key'] = 'fromtype';
	post_data3['val'] = 1;
	//数量
	var post_data4 = new Array(1);
	post_data4['key'] = 'rcount';
	post_data4['val'] = rcount;
	//供应商ID
	var post_data5 = new Array(1);
	post_data5['key'] = 'supply_id';
	post_data5['val'] = supply_id;
	//是否符合首次推广奖励
	var post_data6 = new Array(1);
	post_data6['key'] = 'check_first_extend';
	post_data6['val'] = check_first_extend;
	//商城直播的房间id
	var post_data7 = new Array(1);
	if( resource_id > 0 ){
		post_data7['key'] = 'resource_id';
		post_data7['val'] = resource_id;
	}else{
		post_data7['key'] = 'topic_id';
		post_data7['val'] = topic_id;
	}

	//是否走拼团路线
	var post_data8 = new Array(1);
	post_data8['key'] = 'is_collage_product';
	post_data8['val'] = is_collage_product+'_'+groupBuyType+'_'+singlePrice+'_'+groupPrice+'_'+activitie_id+'_-1';	//是否走拼团路线，拼团标识_单独购买或团购_单独购买价格_团购价_活动id_团id
	//预配送时间
	var post_data9 = new Array(1);
	post_data9['key'] = 'delivery_time';
	post_data9['val'] = delivery_time;

	//产品是否参与了活动
	var post_data10 = new Array(1);
	post_data10['key'] = 'is_active_product';
	post_data10['val'] = pro_act_type+'_'+pro_act_id;//活动类型_活动编号

	//订货系统门店id
	var post_data11 = new Array(1);
	post_data11['key'] = 'o_shop_id';
	post_data11['val'] = o_shop_id;

	//产品所属的云店ID，-1：平台或供应商；大于0：所属的云店ID
	var post_data12 = new Array(1);
	post_data12['key'] = 'pro_yundian_id';
	post_data12['val'] = pro_yundian_id;

	//当前所在的云店ID，-1：平台或供应商；大于0：所属的云店ID
	var post_data13 = new Array(1);
	post_data13['key'] = 'yundian_id';
	post_data13['val'] = yundian_id;

	post_object.push(post_data1,post_data2,post_data3,post_data4,post_data5,post_data6,post_data7,post_data8,post_data9,post_data10,post_data11,post_data12,post_data13);


	Turn_Post(post_object);
	//----转POST数据提交
}
/*立即购买结束*/
/*得到购物车商品数量开始*/
function get_product_number(){
	/*var o_shop_id = localStorage.getItem("cart_user_"+user_id+"_shopid");
	if(o_shop_id==null || o_shop_id==''){
		o_shop_id=-1;
	}*/
	var toNum=0;
	if( user_id > 0 ){
		if(o_shop_id>0){
			var Lson = localStorage.getItem("cart_user_"+user_id+"_shop_"+o_shop_id);
		}else{
			var Lson = localStorage.getItem("cart_"+user_id);
		}
	}else{
		var Lson = localStorage.getItem("cart_visitor");
	}
	if(Lson && Lson!='null'){
		var data=JSON.parse(Lson);//获得数据
		if(data.length>0){
			for(i=0;i<data.length;i++){
				for(j=0;j<data[i][1].length;j++){
                    if(data[i][1][j].length>7){    
                        if(data[i][1][j][7] == -1){			//平台产品购物车数量+1
                            toNum+=parseInt(data[i][1][j][1]); //查出每件商品数量然后累加
                        }else if(data[i][1][j][7] == yundian_id && yundian_id > -1){		//非平台产品，仅仅在云店的才能显示
                            toNum+=parseInt(data[i][1][j][1]); //查出每件商品数量然后累加
                        }
                    }else{
                        toNum+=parseInt(data[i][1][j][1]);
                    }
				}
			}
		}
	}

	$('#badge-span').text(toNum);

	if(toNum==0){
		$('#badge-span').hide();
	}
	else if(toNum>99){
		$('#badge-span').show();
		$('#badge-span').text('99+');//仅作为显示，真实数量仍然为toNum;
	}
	else {
		$('#badge-span').show();
	}


}
/*得到购物车商品数量结束*/


/*有属性判断有没有选择属性开始*/
function check_pos(){
	var call_value	= false;
	var pos 		= $(".pos_div").parent(".small_pro_div");//查有多少个属性父级
	for( i = 0; i < pos.length ; i++ ){
		var active = pos.eq(i).find(".active").length;
		if( active < 1 ){
			var pos_name = pos.eq(i).attr("pos_name");
			//alert('请选择'+pos_name);
			alertAutoClose("请选择"+pos_name);
			call_value = true;
            break;
		}
	}
	return call_value;
}
/*有属性判断有没有选择属性结束*/
/*库存不足下架开始*/
function check_storenum(pid){
	var call_value = false;
	$.ajax({
        type: "post",
        url: "is_checkOut.php",
		async: false,
        data: { pid: pid,customer_id: customer_id},
        success: function (result) {
			if( 0 >= parseInt(result) ){
				//win_alert("商品已下架！");
				$(".sharebg").removeClass("sharebg-active");
				$(".sharebg").remove();
				$(".shangpin-dialog").hide();
				$("#share").show();
				alertAutoClose("商品已下架！");
				call_value = true;
			}
        }
    });

	return call_value;
}
/*库存不足下架结束*/
/*产品需要会员卡等级购买开始*/
function check_cardLevel(pro_card_level_id,shop_card_id){
	var call_value =false;
	$.ajax({
        type: "post",
        url: "is_card_level.php",
		async: false,
        data: { pro_card_level_id: pro_card_level_id,shop_card_id: shop_card_id,customer_id: customer_id,user_id:user_id},
        success: function (result) {
			//return result;
			if( 0 >= parseInt(result) ){
				//win_alert("您的会员级别不够！");
				$(".sharebg").removeClass("sharebg-active");
				$(".sharebg").remove();
				$(".shangpin-dialog").hide();
				$("#share").show();
				alertAutoClose("您的会员级别不够！");
				call_value = true;
			}
        }
    });
	return call_value;
}
/*产品需要会员卡等级购买结束*/

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
				$(".sharebg").removeClass("sharebg-active");
				$(".sharebg").remove();
				$(".shangpin-dialog").hide();
				$("#share").show();
				alertAutoClose("您的会员积分不够!");
				call_value = true;
			}
        }
    });
	return call_value;
}
/*判断积分是否足够结束*/


/*POST提交数据*/
function Turn_Post(object,strurl){
  //object:需要创建post数据一对数组 [key:val]

	/* 将GET方法改为POST ----start---*/
	var strurl = "order_form.php?customer_id="+customer_id;
    var objform = document.createElement('form');
	document.body.appendChild(objform);


	$.each(object,function(i,value){
		//console.log(value);
		var obj_p = document.createElement("input");
		obj_p.type = "hidden";
		objform.appendChild(obj_p);
		obj_p.value = value['val'];
		obj_p.name = value['key'];
	});

	objform.action = strurl;
	objform.method = "POST"
	objform.submit();
	/* 将GET方法改为POST ----end---*/
}
/*POST提交数据*/



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

/*计算佣金开始*/
function reckon_commision(){
	$.ajax({
        type: "post",
        url: "reckon_commision.php",
		async: false,
        data: {
				user_id: user_id,
				is_promoters: is_promoters,
				Plevel: Plevel,
				now_price: p_now_price,
				init_reward: init_reward,
				pro_reward: pro_reward,
				issell_model: issell_model,
				for_price: for_price,
				is_consume: is_consume,
				cost_price: cost_price,
				customer_id: customer_id}
			   ,
        success: function (result) {
			//showAlertMsg("提示","合计："+result+'(仅供参考)',"知道了");
            alertAutoClose("合计："+result+'(仅供参考)');
        },
		error : function() {
			alertAutoClose("网络错误");
		}
    });
}

/*计算佣金结束*/

//获取优惠券
function get_coupon(coupon_id,pid){
	var checkLogin = checkUserLogin();
	if ( !checkLogin ) {
		return;
	}

	$.ajax({
        type: "post",
        url: "coupon_page.php",
		async: true,
        data: { cid: coupon_id,customer_id: customer_id2,user_id: user_id,pid: pid,t: 1,op:'receive'},
        success: function (result) {
			console.log(result);
			var res = JSON.parse(result);
			if(res.code == '1000'){
				alertAutoClose("优惠券领取成功");
			}else if(res.code == '4003'){
				alertAutoClose("您已经领取过该优惠券了");
			}else if(res.code == '4001'){
				alertAutoClose("该优惠券被领光了");
			}else if(res.code == '4002'){
				alertAutoClose("您已经领取过该优惠券了");
			}else if(res.code == '4006'){
				alertAutoClose("优惠券领取异常，请重新领取");
			}else if(res.code == '4004'){
				alertAutoClose("你当前身份不能领取");
			}else if(res.code == '4005'){
				alertAutoClose("不在领取时间");
			}
        }
    });
}
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
//显示单品优惠劵
function show_coupon(){
	$("#coupon_div").slideToggle();
	$("#screen").show();
}
function close_coupon(){			//关闭优惠劵
	$("#coupon_div").hide();
	$("#screen").hide();
	$("#qrcode_div").hide();
	 $(".qrcode_div_bg").hide();
}

//拼团路线检测活动和产品有效性
function check_collage(user_id,pid,activitie_id,num){
	var returnVal;
	$.ajax({
		url: 'check_collage.php?customer_id='+customer_id,
		dataType: 'json',
		type: 'post',
		async: false,
		data: {
			user_id : user_id,
			pid : pid,
			num : num,
			activitie_id: activitie_id,
			group_id: -1,
			comefrom: 1
		},
		success: function(res){
			returnVal = res;
		}
	})
	return returnVal;
}

//判断所选地区是否有货
function check_aog() {
    var _is_available = 0;
    $.ajax({
        url: 'aog_area.class.php?customer_id=' + customer_id,
        dataType: 'json',
        type: 'post',
        async: false,
        data: {
            aog_p: aog_p,
            aog_c: aog_c,
            aog_a: aog_a,
            aog_d: aog_d,
            pid: pid,
            pros: selectedPros
        },
        success: function (res) {
            _is_available = res.is_available;
            if (res.is_available == 1) {
                $('.aog_date').text(res.aog_date);
                $('.is_available').text('有货');
                if (res.aog_date != '') {
                    $('.available_tip').show();
                } else {
                    $('.available_tip').hide();
                }
            } else {
                $('.is_available').text('无货');
                $('.available_tip').hide();
            }
        }
    })

    return _is_available;
}

//提单前检测限购条件
function check_restricted(user_id,product_id,act_id,rcount){
	var returnVal;
	$.ajax({
		url: 'check_restricted.php?customer_id='+customer_id,
		dataType: 'json',
		type: 'post',
		async: false,
		data: {
			user_id : user_id,
			product_id : product_id,
			rcount : rcount,
			act_id: act_id
		},
		success: function(res){
			returnVal = res;
		}
	})
	return returnVal;
}

//新限购活动，判断用户是否可以购买
function check_user_restricted(){
	//不能加入购物车
	if( is_buy_sale_model==0 && is_restricted == 1 && restricted_isout == 1 ){
		$("#joinCar").attr("disabled", true);
	}else if( is_buy_sale_model==1 && is_restricted == 1 && restricted_isout == 1 ){
		$("#another_joinCar").attr("disabled", true);
	}

	//超过限购次数，不允许点击
	if( buy_times >= purchase_times && purchase_times != -1 && is_restricted == 1 && restricted_isout == 1 ){
		$("#joinCar").attr("disabled", true);
		$("#sellDiv").attr("disabled", true);
		$("#buyDiv").attr("disabled", true);
		$("#singleBuy").attr("disabled", true);
		$("#another_joinCar").attr("disabled", true);
		$("#another_buyNow").attr("disabled", true);
	}
	//超过限购数量，不允许点击
	// if( buy_quantity >= quantity_purchased && quantity_purchased != -1 ){
		// $("#joinCar").attr("disabled", true);
		// $("#sellDiv").attr("disabled", true);
		// $("#buyDiv").attr("disabled", true);
		// $("#singleBuy").attr("disabled", true);
		// $("#another_joinCar").attr("disabled", true);
		// $("#another_buyNow").attr("disabled", true);
	// }
}

//活动倒计时
function GetActCountDown(buystart_time,buyend_time){
var startTime ;
        $.ajax({type:"HEAD",url:'ajax_get_servertime.php',complete:function(x){ startTime = new Date(x.getResponseHeader("Date")).getTime();}})//获取服务器时间
    var count=0;
    document.addEventListener("visibilitychange", function (e) { //解决锁屏导致倒计时不准确的bug
         $.ajax({type:"HEAD",url:'ajax_get_servertime.php',complete:function(x){ startTime = new Date(x.getResponseHeader("Date")).getTime();}})//获取服务器时间
         count=0;
    }, true);
	restrictedTime = setInterval(function(){
       count++;
       now_time=startTime+count*1000;
       now_time=now_time.toString().substring(0,10);
		if(is_display_time > 0){
			$(".shangpin-first-remain").show();
			if(display_time_count_down == 1 && display_time_range == 0){			//显示倒计时样式
				if( now_time < buystart_time){
					surplus_time = buystart_time - now_time;
					var nMS = surplus_time*1000-runtimes*1000;
					var nD  = Math.floor(nMS/(1000*60*60*24));
					var nH  = Math.floor(nMS/(1000*60*60))%24;
					var nM  = Math.floor(nMS/(1000*60)) % 60;
					var nS  = Math.floor(nMS/1000) % 60;
					$(".shangpin-first-remain").css("background", "#fc5b24");
					$(".shangpin-first-remain .information").css("background", "#bd451c");
					$(".shangpin-first-remain .unit").show();
					$(".shangpin-first-remain .information").show();
					$("#time_name").html("距开抢仅剩:");
					$("#day").html(nD);
					$("#hour").html(nH);
					$("#minute").html(nM);
					$("#second").html(nS);
				}else if(  buystart_time < now_time < buyend_time ){
					is_restricted = 1;
					surplus_time = buyend_time - now_time;

					$(".shangpin-first-remain").css("background", "#fc5b24");
					$(".shangpin-first-remain .information").css("background", "#bd451c");
					var nMS = surplus_time*1000-runtimes*1000;
					var nD  = Math.floor(nMS/(1000*60*60*24));
					var nH  = Math.floor(nMS/(1000*60*60))%24;
					var nM  = Math.floor(nMS/(1000*60)) % 60;
					var nS  = Math.floor(nMS/1000) % 60;
					$(".shangpin-first-remain .unit").show();
					$(".shangpin-first-remain .information").show();
					$("#time_name").html("距结束仅剩:");
					$("#day").html(nD);
					$("#hour").html(nH);
					$("#minute").html(nM);
					$("#second").html(nS);

					$("#sellDiv").attr("disabled", false);
					$("#joinCar").attr("disabled", false);
					$("#buyDiv").attr("disabled", false);
					$("#singleBuy").attr("disabled", false);
					$("#another_joinCar").attr("disabled", false);
					$("#another_buyNow").attr("disabled", false);
					check_user_restricted();
				}
			}else if(display_time_count_down == 0 && display_time_range == 1){		//显示时间范围
				if( now_time < buystart_time){
					$(".shangpin-first-remain").css("background", "#f4212b");
					$(".shangpin-first-remain .unit").hide();
					$(".shangpin-first-remain .information").hide();
					var act_time = '活动时间：<span class="information" style="background: #9c131a;font-size: 15px;padding: 5px 5px;">'+buystart_time_format+'</span>&nbsp;至&nbsp;'
								 + '<span class="information" style="background: #9c131a;font-size: 15px;padding: 5px 5px;">'+buyend_time_format+'</span>';
					$("#time_name").html(act_time);
				}else if(  buystart_time < now_time < buyend_time ){
					is_restricted = 1;
					surplus_time = buyend_time - now_time;

					$(".shangpin-first-remain").css("background", "#f4212b");
					$(".shangpin-first-remain .unit").hide();
					$(".shangpin-first-remain .information").hide();
					var act_time = '活动时间：<span class="information" style="background: #9c131a;font-size: 15px;padding: 5px 5px;">'+buystart_time_format+'</span>&nbsp;至&nbsp;'
								 + '<span class="information" style="background: #9c131a;font-size: 15px;padding: 5px 5px;">'+buyend_time_format+'</span>';
					$("#time_name").html(act_time);

					$("#sellDiv").attr("disabled", false);
					$("#joinCar").attr("disabled", false);
					$("#buyDiv").attr("disabled", false);
					$("#singleBuy").attr("disabled", false);
					$("#another_joinCar").attr("disabled", false);
					$("#another_buyNow").attr("disabled", false);
					check_user_restricted();
				}
			}else if(display_time_count_down == 1 && display_time_range == 1){
				if( now_time < buystart_time){
					surplus_time = buystart_time - now_time;
					if(surplus_time>2*86400 ){
						$(".shangpin-first-remain").css("background", "#f4212b");
						$(".shangpin-first-remain .unit").hide();
						$(".shangpin-first-remain .information").hide();
						var act_time = '活动时间：<span class="information" style="background: #9c131a;font-size: 15px;padding: 5px 5px;">'+buystart_time_format+'</span>&nbsp;至&nbsp;'
									 + '<span class="information" style="background: #9c131a;font-size: 15px;padding: 5px 5px;">'+buyend_time_format+'</span>';
						$("#time_name").html(act_time);
					}else{
						var nMS = surplus_time*1000-runtimes*1000;
						var nD  = Math.floor(nMS/(1000*60*60*24));
						var nH  = Math.floor(nMS/(1000*60*60))%24;
						var nM  = Math.floor(nMS/(1000*60)) % 60;
						var nS  = Math.floor(nMS/1000) % 60;
						$(".shangpin-first-remain").css("background", "#fc5b24");
						$(".shangpin-first-remain .information").css("background", "#bd451c");
						$(".shangpin-first-remain .unit").show();
						$(".shangpin-first-remain .information").show();
						$("#time_name").html("距开抢仅剩:");
						$("#day").html(nD);
						$("#hour").html(nH);
						$("#minute").html(nM);
						$("#second").html(nS);
					}

				}else if(  buystart_time < now_time < buyend_time ){
					is_restricted = 1;
					surplus_time = buyend_time - now_time;
					//大于两天，不倒计时
					if( surplus_time > 172800 ){
						$(".shangpin-first-remain").css("background", "#f4212b");
						$(".shangpin-first-remain .unit").hide();
						$(".shangpin-first-remain .information").hide();
						var act_time = '活动时间：<span class="information" style="background: #9c131a;font-size: 15px;padding: 5px 5px;">'+buystart_time_format+'</span>&nbsp;至&nbsp;'
									 + '<span class="information" style="background: #9c131a;font-size: 15px;padding: 5px 5px;">'+buyend_time_format+'</span>';
						$("#time_name").html(act_time);
					//	return false;
					}else{
						// if( surplus_time <= 60){
							// $(".shangpin-first-remain").css("background", "#e61529");

							// $(".shangpin-first-remain .information").css("background", "#9c131a");
						// }else{
							// $(".shangpin-first-remain").css("background", "#02B300");
							// $(".shangpin-first-remain .information").css("background", "#006000");
						// }
						$(".shangpin-first-remain").css("background", "#fc5b24");
						$(".shangpin-first-remain .information").css("background", "#bd451c");
						var nMS = surplus_time*1000-runtimes*1000;
						var nD  = Math.floor(nMS/(1000*60*60*24));
						var nH  = Math.floor(nMS/(1000*60*60))%24;
						var nM  = Math.floor(nMS/(1000*60)) % 60;
						var nS  = Math.floor(nMS/1000) % 60;
						$(".shangpin-first-remain .unit").show();
						$(".shangpin-first-remain .information").show();
						$("#time_name").html("距结束仅剩:");
						$("#day").html(nD);
						$("#hour").html(nH);
						$("#minute").html(nM);
						$("#second").html(nS);
					}
					$("#sellDiv").attr("disabled", false);
					$("#joinCar").attr("disabled", false);
					$("#buyDiv").attr("disabled", false);
					$("#singleBuy").attr("disabled", false);
					$("#another_joinCar").attr("disabled", false);
					$("#another_buyNow").attr("disabled", false);
					check_user_restricted();
				}
			}else{
				$(".shangpin-first-remain").hide();
			}
		}else{
			$(".shangpin-first-remain").hide();
		}
		/*
		$(".shangpin-first-remain").show();
			if( now_time < buystart_time){
				surplus_time = buystart_time - now_time;
				if( active_countdown == '' || active_countdown <=0 || surplus_time>active_countdown*86400 ){
					$(".shangpin-first-remain").css("background", "#f4212b");
					$(".shangpin-first-remain .unit").hide();
					$(".shangpin-first-remain .information").hide();
					var act_time = '时间：<span class="information" style="background: #9c131a;font-size: 15px;padding: 5px 5px;">'+buystart_time_format+'</span>&nbsp;至&nbsp;'
								 + '<span class="information" style="background: #9c131a;font-size: 15px;padding: 5px 5px;">'+buyend_time_format+'</span>';
					$("#time_name").html(act_time);
				}else{
					var nMS = surplus_time*1000-runtimes*1000;
					var nD  = Math.floor(nMS/(1000*60*60*24));
					var nH  = Math.floor(nMS/(1000*60*60))%24;
					var nM  = Math.floor(nMS/(1000*60)) % 60;
					var nS  = Math.floor(nMS/1000) % 60;
					$(".shangpin-first-remain").css("background", "#fc5b24");
					$(".shangpin-first-remain .information").css("background", "#bd451c");
					$(".shangpin-first-remain .unit").show();
					$(".shangpin-first-remain .information").show();
					$("#time_name").html("距开抢仅剩:");
					$("#day").html(nD);
					$("#hour").html(nH);
					$("#minute").html(nM);
					$("#second").html(nS);
				}

				// $("#joinCar").attr("disabled", true);
				// $("#sellDiv").attr("disabled", true);
				// $("#buyDiv").attr("disabled", true);
				// $("#singleBuy").attr("disabled", true);
				// $("#another_joinCar").attr("disabled", true);
				// $("#another_buyNow").attr("disabled", true);

			}else if( now_time > buyend_time ){
                console.log(now_time)
                console.log(buyend_time)

                //alert("hello")
				//活动已结束
				//location.reload();
			}else if(  buystart_time < now_time < buyend_time ){
				is_restricted = 1;
				surplus_time = buyend_time - now_time;
				//大于两天，不倒计时
				if( surplus_time > 172800 ){
					$(".shangpin-first-remain").css("background", "#f4212b");
					$(".shangpin-first-remain .unit").hide();
					$(".shangpin-first-remain .information").hide();
					var act_time = '时间：<span class="information" style="background: #9c131a;font-size: 15px;padding: 5px 5px;">'+buystart_time_format+'</span>&nbsp;至&nbsp;'
								 + '<span class="information" style="background: #9c131a;font-size: 15px;padding: 5px 5px;">'+buyend_time_format+'</span>';
					$("#time_name").html(act_time);
				//	return false;
				}else{
					// if( surplus_time <= 60){
						// $(".shangpin-first-remain").css("background", "#e61529");

						// $(".shangpin-first-remain .information").css("background", "#9c131a");
					// }else{
						// $(".shangpin-first-remain").css("background", "#02B300");
						// $(".shangpin-first-remain .information").css("background", "#006000");
					// }
					$(".shangpin-first-remain").css("background", "#fc5b24");
					$(".shangpin-first-remain .information").css("background", "#bd451c");
					var nMS = surplus_time*1000-runtimes*1000;
					var nD  = Math.floor(nMS/(1000*60*60*24));
					var nH  = Math.floor(nMS/(1000*60*60))%24;
					var nM  = Math.floor(nMS/(1000*60)) % 60;
					var nS  = Math.floor(nMS/1000) % 60;
					$(".shangpin-first-remain .unit").show();
					$(".shangpin-first-remain .information").show();
					$("#time_name").html("距结束仅剩:");
					$("#day").html(nD);
					$("#hour").html(nH);
					$("#minute").html(nM);
					$("#second").html(nS);
				}
				$("#sellDiv").attr("disabled", false);
				$("#joinCar").attr("disabled", false);
				$("#buyDiv").attr("disabled", false);
				$("#singleBuy").attr("disabled", false);
				$("#another_joinCar").attr("disabled", false);
				$("#another_buyNow").attr("disabled", false);
				check_user_restricted();
			}*/
		if(now_time==0){ //没有网络的情况下无法获取当前时间
			$(".shangpin-first-remain").css("background", "#3f3f3f");
			$("#time_name").html("您的网络异常");
			$("#day").html("");
			$("#hour").html("");
			$("#minute").html("");
			$("#second").html("");
			$("#sellDiv").attr("disabled", true);
			$("#joinCar").attr("disabled", true);
			$("#buyDiv").attr("disabled", true);
			$("#singleBuy").attr("disabled", true);
			$("#another_joinCar").attr("disabled", true);
				$("#another_buyNow").attr("disabled", true);
		}


},1000);
}
$("#buyDiv").click(function(){
	$(" #div_buyNow").width('100%');
})
