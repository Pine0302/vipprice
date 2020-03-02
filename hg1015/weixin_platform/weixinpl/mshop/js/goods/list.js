
	//搜索数据
	var search_data = {	
	
		'searchKey'  	  	: search_keyword,	//关键词
		'type_id'    		: '',   //分类ID		
		'curCostMin' 		:'',	//最低价
		'curCostMax' 		: '',	//最高价
		'curScoreMin' 		:'',	//最低积分
		'curScoreMax' 		: '',	//最高积分
		'supply_id'  		: '',	//供应商ID	
		'op_sort'    		: 'default_d',	//排序	
		'search_from'   	: 1	,	//search_from 1平台的分类 2品牌代理商分类
		'brand_typeid'   	: brand_typeid	,	//品牌供应商分类ID	
		'tid'   			: tid,	//分类页过来的ID			
		'is_shaixuan'   	: 0,	//0搜索 1筛选	
		'isnew'   			: 0,	//新品上市	
		'ishot'   			: 0,	//热卖产品	
		'isvp'   			: 0,	//VP产品
		'like_op'   		: like_op,	//猜你喜欢动作 cartlike,morelike
		'like_pid'   		: like_pid,	//猜你喜欢产品ID
		'isscore'   		: 0,		//积分专区
		'isqueue'   		: 0,		//队列活动专区
		'user_level'		: user_level,	
		'user_id'		    : user_id,
        
         'act_str'          :'jifen',   //积分业务
         'is_privilege'      : is_privilege,   //特权专区
         
		    
	}

	var downFlag = false; 		//是否已经加载全部
    var pageNum = 0;			//起始编号
	var isLock = false; 		//锁定：0未锁，1上锁
    var winWidth = $(window).width();
    var winheight = $(window).height();
    var limit_num  = 16; 	//加载数据的数目(数据库方面)
	var pinterestObj=document.getElementById("pinterestList");// 包含所有商品的标签
	//搜索点击事件
	$("#tvKeyword").bind('search',function(){
		$('#search_btn').click();
	})
	$('#search_btn').click(function(){
		
		var search_keyword = $('#tvKeyword').val();
		search_keyword = search_keyword.trim();
		search_data.searchKey = search_keyword;
		search_data.search_from = $('#search_from').val();
		search_data.user_level = user_level;
		search_data.supply_id = supply_id;
		//_reset();
		
		if(supply_id<0 || search_data.search_from==1 )
		{
			reset_searchdata();//调用清空参数函数
			search_data.searchKey=search_keyword;// 为了搜索全站产品，重新赋值
		}

		//【队列活动专区】 队列活动专区只搜索活动产品 2018/4/19
		var isqueue = $('#isqueue').val();
		if( isqueue == '1' )
		{
			search_data.isqueue = '1';
		}
		//【队列活动专区】 end
		
		search_ajax_data(search_data,'search',get_search_data,1);

		$("#search_none").css("display","none");
	});
	//scroll事件检查加载
	addEvent(window, "scroll", function(){
	if (document.body.scrollHeight-getViewPortSize().y <= getScrollOffsets().y+2){
		if(!downFlag){//如果没有全部加载完毕，显示loading图标
			if(pinterest_current>=pinterest_totalItem){//一次数据加载完毕
				search_ajax_data(search_data,'search',get_search_data,0);//默认加载数据
			}else{
						pinterestInit(pinterestObj,true);
					}
			$('#pinterestList').addClass('pinterestUl_loading');
			$('#pinterestDone').hide();
			$('#pinterestMore').show();
		}else {
			pinterDone();
		}
		}
	});
	function search_btn(){
		
		if(s_n ==-1){
			var search_keyword = $('#tvKeyword').val();		
			search_keyword = search_keyword.trim();
			search_data.searchKey = search_keyword;
			//_reset();
			search_ajax_data(search_data,'search',get_search_data,1);
		}
		
		
	}
	
	function reset_searchdata(){ //清空所有参数
		
		search_data = {	
		
			'searchKey'  	  	: '',	//关键词
			'type_id'    		: '',   //分类ID		
			'curCostMin' 		:'',	//最低价
			'curCostMax' 		: '',	//最高价
			'curScoreMin' 		:'',	//最低积分
			'curScoreMax' 		: '',	//最高积分			
			'op_sort'    		: 'default_d',	//排序	
			'search_from'   	: 1	,	//search_from 1平台的分类 2品牌代理商分类
			'brand_typeid'   	: ''	,	//品牌供应商分类ID	
			'tid'   			: '',	//分类页过来的ID			
			'is_shaixuan'   	: 0,	//0搜索 1筛选	
			'isnew'   			: 0,	//新品上市	
			'ishot'   			: 0,	//热卖产品	
			'isvp'   			: 0,	//VP产品
			'like_op'   		: '',	//猜你喜欢动作 cartlike,morelike
			'like_pid'   		: '',	//猜你喜欢产品ID
			'isscore'   		: 0,		//积分专区
			'isqueue'   		: 0,		//队列活动专区
			'user_level'		: user_level	
				
		    
		}
	}
	
	function search_type(){ //搜索分类
		var search_type = $('#tid').val();	
		search_data.selChildCtgr  = search_type;
		//_reset();
		search_ajax_data(search_data,'search',get_search_data,1);
	}
		
	function _reset(){	//重置加载参数
		 downFlag	 = false;			// 重置加载全部为false
		 pageNum 	 = 0;				// 重置起始页码
		 isLock 	 = false; 			// 重置继续加载为false
		 $(window).scrollTop(0);	//重置返回顶部
	}	

	$(function() {
		//排序点击事件
		$(".sort-flds").unbind("click").bind("click", function(){
			$(this).toggleClass('plus');
			$(this).siblings().children().children("img").attr("src",'./images/list_image/tagbg_item_down.png');
			$(this).siblings().attr("value", 0);/* 0表示向下 1表示向上*/
			$(this).addClass("sort-select").siblings().removeClass("sort-select");/* 字体*/
			$("#sortsel").find(".am-header-icon-custom").attr("src","./images/list_image/tagbg_item5.png"); /*修复筛选图标 */
			if ($(this).hasClass('plus')) { 
				$(this).attr("value", 1);
				$(this).find("img").attr("src", "./"+$images_skin+"/list_image/tagbg_item_up-select.png");

			}else{
				 $(this).attr("value", 0);
				 $(this).find("img").attr("src", "./"+$images_skin+"/list_image/tagbg_item_down-select.png");
			}
		});
	/************************排序************************/
		//默认排序start
		$('#sortDef').click(function(){
			//if(search_data.searchKey=='' && search_data.is_shaixuan==0)return;	//无搜索内容则return
			var value = $(this).attr('value');
			if(value==1){
				search_data.op_sort = 'default_a';
			}else{
				search_data.op_sort = 'default_d';
			}
			//_reset();
			search_ajax_data(search_data,'search',get_search_data,1);
		});
		//默认排序end
		//销量排序start
		$('#sortSaleNum').click(function(){
			//if(search_data.searchKey=='' && search_data.is_shaixuan==0)return;
			var value = $(this).attr('value');
			if(value==1){
				search_data.op_sort = 'sell_a';
			}else{
				search_data.op_sort = 'sell_d';
			}
			//_reset();
			 search_ajax_data(search_data,'search',get_search_data,1);
		});
		//销量排序end		
		//价格排序start		
		$('#sortCost').click(function(){
			//if(search_data.searchKey=='' && search_data.is_shaixuan==0)return;
			var value = $(this).attr('value');
			if(value==1){
				search_data.op_sort = 'price_a';
			}else{
				search_data.op_sort = 'price_d';
			}
			//_reset();
			search_ajax_data(search_data,'search',get_search_data,1);
		});
		//价格排序end
		//积分排序start		
		$('#sortScore').click(function(){
			//if(search_data.searchKey=='' && search_data.is_shaixuan==0)return;
			var value = $(this).attr('value');
			if(value==1){
				search_data.op_sort = 'score_a';
			}else{
				search_data.op_sort = 'score_d';
			}
			//_reset();
			search_ajax_data(search_data,'search',get_search_data,1);
		});
		//积分排序end		
		//时间排序start	
		$('#sortTime').click(function(){
			//if(search_data.searchKey=='' && search_data.is_shaixuan==0)return;
			var value = $(this).attr('value');
			if(value==1){
				search_data.op_sort = 'time_a';
			}else{
				search_data.op_sort = 'time_d';
			}
			//_reset();
			search_ajax_data(search_data,'search',get_search_data,1);
			
		});
		//时间排序end	



	/************************排序************************/
	
	
		//排序点击事件
		
		$('.current_cls').hide(); // 筛选1-2 (Hide)
		
		load_input_parameter(search_data);//自动加载其他表单参数
		loadDataKind(); // 获取分类 
		loadDataPrice(); // 获取价格
		loadDataScore(); // 获取价格
		
		if(s_n ==-1){ 		//-1搜索关键词			
			search_btn();			
		}else if(s_n ==-2){ //-2分类关键词
			search_type();
		}else{
			//console.log(search_data);
			
			search_ajax_data(search_data,'search',get_search_data,0);//默认加载数据
		}
		

		//滑动加载数据（显示的数据高度必须大于窗口高度才会触发）
		// $(window).scroll(function() {	
			
		// 	var scrollTop = $(window).scrollTop(); 			//滑动距离 
		// 	var scrollHeight = $(document).height();  		//内容的高度
		// 	var windowHeight = $(window).height();			//窗口高度
				
		// 	if (scrollTop + windowHeight >= scrollHeight) {		//当滑动距离+内容的高度 > 窗口的高度 = 则加载数据
								
		// 		 search_ajax_data(search_data,'search',get_search_data,0);//默认加载数据	
					
		// 	} 
		// });
		
	});
    
	
	
	/***********************函数部分************************/
	
	
    function loadDataKind(){// 获取分类  
    	
    	
         search_ajax_data(search_data,'get_type',loadDataKind_data,1);
          
        
    }
	
	function loadDataKind_data(result){
	//	console.log(result);
		var content1 = "";
	   $.each(result,function(i,value){	 							//一级
	  	// console.log(value)
			content1 += '<div class="city-li">';
		   	if(value.is_privilege==1){
		   		content1 +='<img src="images/special.png" class="privilege_one"/>';
		   	}
		   	content1 += '<a class="city-li-a"  is_privilege='+value.is_privilege+' pCtgrId='+value.pt_id+' href="#" onclick="currentCityCilck(this);return false;">'+value.pt_name+'</a></div>';
						
				content1 += '	 <ul class="current_cls">';
				content1 += ('    	<div class="dib w330" pCtgrId='+value.pt_id+' pCtgrName='+value.pt_name+'>');
				//默认添加父类(【队列活动】不添加父类2018/4/19)
				if (value.pt_id != 0) {
					content1 += '<div class="t-size" ctgrId='+value.pt_id+'><b></b>'+value.pt_name+'</div>';
				}
				if(value.child_types.length>0){	
					var  child_types = value.child_types;
					//二级分类
					$.each(child_types,function(k,val){			

						content1 += '<div class="t-size" ctgrId='+val.pc_id+'><b></b>'+val.pc_name+'</div>';
						 
					});
				}		
				content1 += '       </div>';
				content1 += '    </ul>';
			
            content1 += ' <div class="line"></div>';
				
		   }); 

		
		$("#white-kind").html(content1); 

		// $(function () {
		// 	function sco(){
		// 		var scrollTopHHH=localStorage.scrollTopHH;
		// 		if (scrollTopHHH) {
		// 			$("html,body").animate({ scrollTop: scrollTopHHH }, 500);
		// 			localStorage.scrollTopHH='';
		// 		}
		// 	}
		// 	sco();
		// });
		//先加载数据在监听事件
		$("#cai").click(function(){
			$(this).toggleClass("sel");
		})

		$(".selBtn").click(function(){
			$(this).toggleClass("sel");
		})
		
		$(".t-size").click(function(){
			$(".t-size").removeClass("sel");
			$(this).addClass("sel");
			confirmChildOpt(0);
		})	
       
        
	}
    
    function loadDataPrice(){// 获取价格
    	
    	var content2 = "";
        
        for (var i=0; i<6; i++) {
        	content2 += '<div class="list-one" onclick="clickCostItem(this);">';
        	content2 += ('    	<div class="cost-title" minVal='+i*10+' maxVal = '+i*100+'><span >'+
			(OOF_P==2 ? i*10+OOF_S : OOF_S+i*10)
			+' - '+
			(OOF_P==2 ? i*100+OOF_S : OOF_S+i*100)
			+'</span></div>');
        	content2 += '</div>';
        	content2 += '<div class="line"></div>';
        }
        
        $("#white-price").html(content2);   
    }
	
	function loadDataScore(){// 获取积分
    	
    	var content2 = "";
        
        for (var i=0; i<6; i++) {
        	content2 += '<div class="list-one" onclick="clickScoreItem(this);">';
        	content2 += ('    	<div class="cost-title" minVal='+i*10+' maxVal = '+i*100+'><span >'+i*10+'积分 - '+i*100+'积分</span></div>');
        	content2 += '</div>';
        	content2 += '<div class="line"></div>';
        }
        
        $("#white-score").html(content2);   
    }
    
   
	function search_ajax_data(_data,_type,callback,is_reset){
	/*
	函数说明：ajax获取数据
	
	*/	
		if(localStorage.pro_index>15){ 
			 limit_num=parseInt(localStorage.pro_index)+5;
			 pinterest_perBlock=parseInt(localStorage.pro_index)+5;
		}
		if(is_reset){
			_reset();
		}
		
		if (isLock ==false &&  downFlag ==false){			//上锁或者数据加载完毕则不继续加载数据
		
			isLock=true;
					

        var curCostMin    = _data.curCostMin; // 选择的最小价格
        var curCostMax    = _data.curCostMax; // 选择的最大价格  
		var curScoreMin   = _data.curScoreMin; // 选择的最小积分
        var curScoreMax   = _data.curScoreMax; // 选择的最大积分		
        var searchKey     = _data.searchKey; // 关键字	
        var supply_id     = _data.supply_id; // 供应商   
		var brand_typeid  = _data.brand_typeid; // 品牌供应商分类ID
		var tid  		  = _data.tid; // 分类页过来的ID
        var op_sort       = _data.op_sort; // 排序类型 
        var search_from   = _data.search_from; // 1为平台，2为店铺
        var isnew      	  = _data.isnew; // 新品上市
        var ishot      	  = _data.ishot; // 热卖产品
        var isvp      	  = _data.isvp; // VP产品
		var type_id       = _data.type_id; // 分类ID
		var like_op       = _data.like_op; // 猜你喜欢操作
		var like_pid      = _data.like_pid; // 猜你喜欢产品ID
		var isscore       = _data.isscore; // 新品上市
		var isqueue       = _data.isqueue; // 队列活动
		var user_level    = _data.user_level;
		var user_id       = _data.user_id;
        var is_privilege  = _data.is_privilege;
        
        /*==============活动===========*/
		var act_str       = _data.act_str;
        
		
		 $.ajax({
				   url: "/weixinpl/mshop/search.class.php?customer_id="+customer_id+"&op="+_type+"",
				   data:{
						   type_id		:type_id,
						   curCostMin   :curCostMin,
						   curCostMax   :curCostMax,
						   curScoreMin   :curScoreMin,
						   curScoreMax  :curScoreMax,
						   searchKey    :searchKey,
						   supply_id    :supply_id,
						   brand_typeid :brand_typeid,
						   tid 			:tid,
						   op_sort      :op_sort,
						   search_from  :search_from,
						   isnew     	:isnew,
						   ishot     	:ishot,
						   isvp     	:isvp,
						   like_op     	:like_op,
						   like_pid     :like_pid,
						   isscore     	:isscore,
						   isqueue     	:isqueue,
						   pageNum     	:pageNum,
						   limit_num    :limit_num,
						   user_level   :user_level,
						   user_id      :user_id,
                           
                             /*==============活动===========*/
						   act_str      :act_str,
						   is_privilege :is_privilege
						},
				   type: "POST",
				   dataType:'json',
				   async: true,     
				   success:function(result){			  
					//console.log(_data);
					//console.log(result);
					
					callback:callback(result,is_reset,_data); //带参回调

				   },
				   error:function(er){
						   
				   }
			   

		});
		
		}
	}
	//获取搜索产品数据
	function get_search_data(result,is_reset,_data){
			
			if(is_reset){
				_reset();
			}
			
				var zekou = 0;
				var cashback = 0;
				var k = 0;
				var content = '';
				var content2 = '';
				var list_tempid = $("#list_tempid").val();
				var isOpenSales = $("#isOpenSales").val();
				var isshowdiscount = $("#isshowdiscount").val();
				var isvp_switch = $("#isvp_switch").val();				
				var isscore=$("#isscore").val();
				var isqueue=$("#isqueue").val();
				var is_showOriginal=$("#is_show_original").val();
				//var is_division_show = $("#is_division_show").val();
			
								
				isLock = false;
				//console.log(result);
				
				if(result=="" && pageNum==0){ //假如没有返回数据就搜索所有产品
					//
					var is_privilege_type = $("#is_privilege_type").val();
					//console.log("user_level="+user_level+"/is_privilege_link="+is_privilege_link+"/is_privilege_type="+is_privilege_type);
					if( user_level == -1 && is_privilege_link == 1 && is_privilege_type == 1 ){
						$(".search_none_tips").html("抱歉，您还不是推广员，赶紧去成为推广员吧");
						$("#gonglue").show();
					}else{
						$(".search_none_tips").html("抱歉，没有找到你想要的商品，为您推荐以下商品:");
						$("#gonglue").hide();
					}
					$("#search_none").css("display","block");
					search_data.searchKey="";
					search_data.type_id="";
					search_data.tid="";
					search_data.curCostMax="";
					search_data.curCostMin="";
					search_data.curScoreMax="";
					search_data.curScoreMin="";
					search_data.brand_typeid="";
					search_ajax_data(search_data,'search',get_search_data,0);  
					search_data.tid=$("#tid").val();
					search_data.type_id = $('#selChildCtgr').val();
					search_data.searchKey=$('#tvKeyword').val();
				}
				
			//console.log(search_data);
				$.each(result,function(i,val){

					var rtn_data = val;
				/*	console.log('i want to know');
					console.log(OOF_P);
					console.log(OOF_S);
					console.log(list_tempid);
					console.log('these');*/
					if(list_tempid==1){/////////////////////模板1
							content += '	<div class="list" onclick="gotoProductDetail('+rtn_data.pro_id+');" id="artwork_'+rtn_data.pro_id+'">';
							content += '    	<div class="listImg" >';
							content += '        	<a class="pinterest_img">';
							
							//content += 
							content += '<img  style="height:96px;" src="/weixinpl/mshop/images/loading.gif" data-original="'+rtn_data.default_imgurl+'?x-oss-process=image/resize,w_200'+'" class="ori " id="artwork_img_'+rtn_data.pro_id+'"> </a>';
                            if(rtn_data.is_privilege==1){
								 content += ' <img src="images/special.png" class="list-special-img" style="width:25px;" /> ';
							}
							content += '    	</div>';
							content += '    	<div class="listImgBlow">';
							content += '        	<div class="listTitle">';
						
                           
						//是否品牌商					
						if(rtn_data.isbrand<0){
							css_text_indent = 'style=text-indent:0px!important;';
							content += '            <div class="pinterest_title"'+css_text_indent+' >'+rtn_data.pro_name+'</div>';
						}else{
							content += '            <div class="pinterest_title" >'+rtn_data.pro_name+'</div>';
							content += '            <div class="listTitleMark" ><span type="text" class="am-btn am-btn-danger am-radius" >品牌</span></div>';
						}
						//if(rtn_data.isbrand>0){//是品牌供应商
						//	content += '            	<div class="listTitleMark" ><span type="text" class="am-btn am-btn-danger am-radius" >品牌</span></div>';
						//}
							content += '        	</div>';
							content += '        	<div class="middleinDiv">'; 
							content += '            	<div class="productMoney">'+
							(OOF_P==2 ? rtn_data.now_price+OOF_S : OOF_S+rtn_data.now_price)
							+'';

						if(rtn_data.orgin_price>0 && is_showOriginal>0){
							content += '					<span class="g_price">'+
							(OOF_P==2 ? rtn_data.orgin_price+OOF_S : OOF_S+rtn_data.orgin_price)
							+'</span>';
						}
						if(rtn_data.vip_price>0 ){
							content += ' <span class="v_price" style="font-size:80%">'+"VIP价:"+rtn_data.vip_price+'</span>';  //VIP价格
						}
							

							content += '				</div>';
							content += '        	</div>';
							content += '        	<div class="tag-div">';
						//折扣计算
						if((rtn_data.discount>0 && rtn_data.discount<10) && isshowdiscount>0){
							content += '        		<span type="text" class="am-btn am-btn-danger am-radius" >'+rtn_data.discount+'折</span>';
						}	
						//是否VP
						if(rtn_data.isvp>0 && isvp_switch>0){
							content += '        		<span type="text" class="am-btn am-btn-secondary am-radius" >VP:'+rtn_data.vp_score+'</span>';
						}	
						//是否返现
						if(rtn_data.display>0 && rtn_data.is_cashback > 0  && rtn_data.pro_cash_money > 0){
							content += '        		<span type="text" class="am-btn am-btn-warning am-radius" >赠'+
							(OOF_P==2 ? rtn_data.pro_cash_money+OOF_S : OOF_S+rtn_data.pro_cash_money)
							+'</span>';
					
						}	
                        
                        //是否购物币抵扣
						if(rtn_data.isOpenCurrency>0 && rtn_data.currency_price > 0 ){
							content += '        		<span type="text" class="am-btn am-btn-warning am-radius" style="background-color: #8c53ab; border-color: #8c53ab;">抵'+
							(OOF_P==2 ? rtn_data.currency_price+OOF_S : OOF_S+rtn_data.currency_price)
							+'</span>';
					
						}
                        
						//是否返购物币
						if(rtn_data.display>0 && rtn_data.back_currency>0){
							content += '        		<span type="text" class="am-btn am-btn-warning am-radius" >'+rtn_data.custom+
							(OOF_P==2 ? rtn_data.back_currency+OOF_S : OOF_S+rtn_data.back_currency)
							+'</span>';
						}
						//是否税收产品
						if( rtn_data.tax_name != 1 ){
							content += '        		<button class="btn-shui"><div class="test5"><span>税</span></div><div style="display:inline-block">'+rtn_data.tax_name+'</div></button>';
						}
						if(rtn_data.is_free_shipping==1){
							content += '        	<span class="list_mail" style="color: #F37B1D;	font-size: 8px;	background-color: #FFFFFF;	padding: 1px 6px;	border-radius:4px;    border:1px solid #F37B1D;">包邮</span>';
						}
							content += '        	</div>';
							content += '        	<div class="BottominDiv">';
						//是否免邮

						if(isOpenSales >0){						
							content += '        		<span class="s_rightstr" >已售'+rtn_data.show_sell_count+'</span>';
						}
							content += '       	 	</div>';
							content += '     	</div>';
							content += ' 	</div>';
						
						
					}else if(list_tempid==2){/////////////////////模板2
							content += ('<li class="pinterestLi" onclick="gotoProductDetail('+rtn_data.pro_id+');" id="artwork_'+rtn_data.pro_id+'" >');
							
                           

							content += '    <a class="pinterest_img">';
							content += ('        <img class="pinterestImg pinterestImg11 style2-img" src="/weixinpl/mshop/images/loading.gif" data-original="'+rtn_data.default_imgurl+'?x-oss-process=image/resize,w_640'+'" class="ori" id="artwork_img_'+rtn_data.pro_id+'">');
							content += '    </a>';
							if(rtn_data.is_privilege==1){
								 content += ' <img src="images/special.png" class="list-special-img" style="width:25px;" /> ';
							}
							content += '    <div class="comments-link">';
							content += '        <div class="listTitle">';
                            
						//是否品牌商	
						if(rtn_data.isbrand<0){
							css_text_indent = 'style=text-indent:0px!important;';
							content += '            <div class="pinterest_title"'+css_text_indent+' >'+rtn_data.pro_name+'</div>';
						}else{
							content += '            <div class="pinterest_title" >'+rtn_data.pro_name+'</div>';
							content += '            <div class="listTitleMark" ><span type="text" class="am-btn am-btn-danger am-radius topRstr" >品牌</span></div>';
						}
							content += '        </div>';
							content += '        <div class="tag-div">';
						//折扣计算
						if((rtn_data.discount>0 && rtn_data.discount<10) && isshowdiscount>0){
							content += '        	<span type="text" class="am-btn am-btn-danger am-radius" >'+rtn_data.discount+'折</span>';
						}	
						//是否VP
						if(rtn_data.isvp>0  && isvp_switch>0){	
							content += '        	<span type="text" class="am-btn am-btn-secondary am-radius" >VP:'+rtn_data.vp_score+'</span>';
						}	
						//是否返现
						if(rtn_data.display>0 && rtn_data.is_cashback > 0  && rtn_data.pro_cash_money > 0){
							content += '        	<span type="text" class="am-btn am-btn-warning am-radius" >赠'+
							(OOF_P==2 ? rtn_data.pro_cash_money+OOF_S : OOF_S+rtn_data.pro_cash_money)
							+'</span>';				
						}
                        
                        //是否购物币抵扣
						if(rtn_data.isOpenCurrency>0 && rtn_data.currency_price > 0 ){
							content += '        		<span type="text" class="am-btn am-btn-warning am-radius" style="background-color: #8c53ab; border-color: #8c53ab;">抵'+
							(OOF_P==2 ? rtn_data.currency_price+OOF_S : OOF_S+rtn_data.currency_price)
							+'</span>';
					
						}
                        
						//是否返购物币
						if(rtn_data.display>0 && rtn_data.back_currency>0){
							content += '        		<span type="text" class="am-btn am-btn-warning am-radius" >'+rtn_data.custom+
							(OOF_P==2 ? rtn_data.back_currency+OOF_S : OOF_S+rtn_data.back_currency)
							+'</span>';
						}
						//是否税收产品
						if( rtn_data.tax_name != 1 ){
							content += '        		<button class="btn-shui"><div class="test5"><span>税</span></div><div style="display:inline-block">'+rtn_data.tax_name+'</div></button>';
						}		
							content += '        </div>';
							content += '        <div class="BottominDiv">';
						//是否免邮
						if(rtn_data.is_free_shipping==1){
							content += '        	<span class="list_mail" style="color: #F37B1D;	font-size: 8px;	background-color: #FFFFFF;	padding: 1px 6px;	border-radius:4px;    border:1px solid #F37B1D;">包邮</span>';
						}
						
						if(isOpenSales >0){
							content += '        	<span class="list_marktxt_b">已售'+rtn_data.show_sell_count+'</span>';
						}						
						if(isscore==0){//非积分专区
							content += '        	<span class="s_rightstr"><div class="productMoney">'+
							(OOF_P==2 ? rtn_data.now_price+OOF_S : OOF_S+rtn_data.now_price)
							+'';						

							if(rtn_data.orgin_price>0 && is_showOriginal>0){
								content += '<span class="g_price">'+
								(OOF_P==2 ? rtn_data.orgin_price+OOF_S : OOF_S+rtn_data.orgin_price)
								+'</span>';
							}						
							content += '</div></span>';



						}else{//积分专区
							content += '        	<span class="s_rightstr" style="width:100%"><div class="productMoney">';
							if(rtn_data.now_price>0){
								content += '			'+
								(OOF_P==2 ? rtn_data.now_price+OOF_S : OOF_S+rtn_data.now_price)
								+'+';
							}
							content += '			'+rtn_data.need_score+'积分';

							if(rtn_data.orgin_price>0 && is_showOriginal>0){
								content += '<span class="g_price">价值'+
								(OOF_P==2 ? rtn_data.orgin_price+OOF_S : OOF_S+rtn_data.orgin_price)
								+'</span>';
							}	
							content += '</div></span>';
													
						}
						
							content += '        </div>';
							content += '        <div style="clear:both"></div>';
							content += '     </div><!-- .comments-link -->';
							content += ' </li>';
					}else if(list_tempid==3){/////////////////////模板3
							content += ('<li class="pinterestLi" onclick="gotoProductDetail('+rtn_data.pro_id+');" id="artwork_'+rtn_data.pro_id+'" >');
                            
							content += '    <a class="pinterest_img">';
							content += ('        <img class="pinterest_img_sty style3-img" src="/weixinpl/mshop/images/loading.gif" data-original="'+rtn_data.default_imgurl+'?x-oss-process=image/resize,w_400'+'" class="ori" id="artwork_img_'+rtn_data.pro_id+'">');
							content += '    </a>';
							if(rtn_data.is_privilege==1){
								content += '<img src="images/special.png" class="list-special-img" style="width:25px;"/>';
							}
                            
							content += '    <div class="comments-link">';
							content += '        <div class="listTitle">';
						//是否品牌商	

						if(rtn_data.isbrand<0){
							css_text_indent = 'style=text-indent:0px!important;';
							content += '            <div class="pinterest_title"'+css_text_indent+' >'+rtn_data.pro_name+'</div>';
						}else{
							content += '            <div class="pinterest_title" >'+rtn_data.pro_name+'</div>';
							content += '            <div class="listTitleMark" ><span type="text" class="am-btn am-btn-danger am-radius topRstr" >品牌</span></div>';
						}
							content += '        </div>';
							content += '        <div class="middleinDivTop">';
							content += '            <div class="productMoney">'+
							(OOF_P==2 ? rtn_data.now_price+OOF_S : OOF_S+rtn_data.now_price)
							+'';

						if(rtn_data.orgin_price>0 && is_showOriginal>0){
							content += '				<span class="g_price">'+
							(OOF_P==2 ? rtn_data.orgin_price+OOF_S : OOF_S+rtn_data.orgin_price)
							+'</span>';
						}

						if(rtn_data.vip_price>0 ){
								content += ' <span class="v_price" style="font-size:80%">'+"VIP价:"+rtn_data.vip_price+'</span>';  //VIP价格
						}

					
							content += '			</div>';
							content += '        </div>';
							content += '        <div class="tag-div">';
						//折扣计算
						if((rtn_data.discount>0 && rtn_data.discount<10) && isshowdiscount>0){
							content += '        	<span type="text" class="am-btn am-btn-danger am-radius" >'+rtn_data.discount+'折</span>';
						}		
						//是否VP
						if(rtn_data.isvp>0  && isvp_switch>0){	
							content += '        	<span type="text" class="am-btn am-btn-secondary am-radius" >VP:'+rtn_data.vp_score+'</span>';
						}	
						//是否返现
						if(rtn_data.display>0 && rtn_data.is_cashback > 0  && rtn_data.pro_cash_money > 0){
							content += '        	<span type="text" class="am-btn am-btn-warning am-radius" >赠'+
							(OOF_P==2 ? rtn_data.pro_cash_money+OOF_S : OOF_S+rtn_data.pro_cash_money)
							+'</span>';
						}
                        
                        //是否购物币抵扣
						if(rtn_data.isOpenCurrency>0 && rtn_data.currency_price > 0 ){
							content += '        		<span type="text" class="am-btn am-btn-warning am-radius" style="background-color: #8c53ab; border-color: #8c53ab;">抵'+
							(OOF_P==2 ? rtn_data.currency_price+OOF_S : OOF_S+rtn_data.currency_price)
							+'</span>';
					
						}
                        
						//是否返购物币
						if(rtn_data.display>0 && rtn_data.back_currency>0){
							content += '        		<span type="text" class="am-btn am-btn-warning am-radius" >'+rtn_data.custom+
							(OOF_P==2 ? rtn_data.back_currency+OOF_S : OOF_S+rtn_data.back_currency)
							+'</span>';
						}
						//是否税收产品
						if( rtn_data.tax_name != 1 ){
							content += '        		<button class="btn-shui"><div class="test5"><span>税</span></div><div style="display:inline-block">'+rtn_data.tax_name+'</div></button>';
						}
						//是否免邮
						if(rtn_data.is_free_shipping==1){
							content += '        	<span class="list_mail" style="color: #F37B1D;	font-size: 8px;	background-color: #FFFFFF;	padding: 1px 6px;	border-radius:4px;    border:1px solid #F37B1D;">包邮</span>';
						}
							content += '        </div>';
							content += '        <div class="BottominDiv">';
						if(isOpenSales >0){		
							content += '        	<span class="s_rightstr">已售'+rtn_data.show_sell_count+'</span>';
						}
							content += '        </div>';
							content += '        <div style="clear:both"></div>';
							content += '     </div><!-- .comments-link -->';
							content += ' </li>';
					}else if(list_tempid==4){/////////////////////模板4
							content += ('<li class="pinterestLi" onclick="gotoProductDetail('+rtn_data.pro_id+');" id="artwork_'+rtn_data.pro_id+'" >');
							
                            
							content += '    <a class="pinterest_img">';
							content += ('        <img class="pinterest_img_sty" src="/weixinpl/mshop/images/loading.gif" data-original="'+rtn_data.default_imgurl+'?x-oss-process=image/resize,w_400'+'" class="ori" id="artwork_img_'+rtn_data.pro_id+'">');
							content += '    </a>';
							if(rtn_data.is_privilege==1){
								content += ' <img src="images/special.png" class="list-special-img" style="width:25px;" />';	
							}
							content += '    <div class="comments-link">';
							content += '        <div class="listTitle">';

						//是否品牌商	

						if(rtn_data.isbrand<0){
							css_text_indent = 'style=text-indent:0px!important;';
							content += '            <div class="pinterest_title"'+css_text_indent+' >'+rtn_data.pro_name+'</div>';
						}else{
							content += '            <div class="pinterest_title" >'+rtn_data.pro_name+'</div>';
							content += '            <div class="listTitleMark" ><span type="text" class="am-btn am-btn-danger am-radius" >品牌</span></div>';
						}
						//content += content2;
						//是否品牌商
							content += '        </div>';
							content += '        <div class="middleinDivTop">';	
							content += '            <div class="productMoney">'+
							(OOF_P==2 ? rtn_data.now_price+OOF_S : OOF_S+rtn_data.now_price)
							+'';	
							content += '			</div>';


						if(rtn_data.orgin_price>0 && is_showOriginal>0){
							content += '				<span class="g_price">'+
							(OOF_P==2 ? rtn_data.orgin_price+OOF_S : OOF_S+rtn_data.orgin_price)
							+'</span>';
						}

							content += '        </div>'; 
							// content += '        <div class="tag-div">';
						//折扣计算
						// if(rtn_data.discount>0 && isshowdiscount>0){						
						// 	content += '        	<span type="text" class="am-btn am-btn-danger am-radius" >'+rtn_data.discount+'折</span>';
						// }
						//折扣计算
						//是否VP
						// if(rtn_data.isvp>0  && isvp_switch>0){	
						// 	content += '        	<span type="text" class="am-btn am-btn-secondary am-radius" >VP:'+rtn_data.vp_score+'</span>';
						// }
						//是否VP
						//是否返现
						// if(rtn_data.is_cashback > 0 ){			
						// 	if(rtn_data.pro_cash_money > 0){
						// 	content += '        	<span type="text" class="am-btn am-btn-warning am-radius" >返￥'+rtn_data.pro_cash_money+'</span>';
						// 	}
						// }
						//是否返现	
						// 	content += '        </div>';
						// 	content += '        <div class="BottominDiv">';
						// //是否免邮
						// if(rtn_data.is_free_shipping==1){			
						// 	content += '        	<span class="list_mail" >包邮</span>';
						// }
						// //是否免邮
						// if(isOpenSales >0){		
						// 	content += '        	<span class="s_rightstr">已售'+rtn_data.show_sell_count+'</span>';
						// }
						// 	content += '        </div>';
							content += '        <div style="clear:both"></div>';
							content += '     </div><!-- .comments-link -->';
							content += ' </li>';
            			//四号模板不需要以上内容
					}	
					else if(list_tempid==5){/////////////////////模板5         828新增
						
						//content += ' <div class="con_display" style="padding-top:0px;padding-bottom:0px">';
						//content += ' 	<div class="floor clearfix" style="padding:0 5px;position:relative">';
						content += ' 		<div class="pro-box pro-box-sec"> ';
						content += ' 			<div class="img-box">  ';
						content += ' 			<img src="/weixinpl/mshop/images/loading.gif" data-original="/wsy_prod/up/1/3243/Product/product/1521770099193.png?x-oss-process=image/resize,w_400" src="/wsy_prod/up/1/3243/Product/product/1521770099193.png?x-oss-process=image/resize,w_400" style="display: block;"> ';
						content += ' 			</div>';
						content += ' 			<p class="goods-title">笑话</p>';
						content += ' 			<div class="text-round-box">';
						content += ' 				<span class="text-round" style="color:undefined;background-image:url();"></span>';
						content += ' 			</div> ';
						content += ' 			<span class="goods-price">¥0.00</span><span class="old-price">¥200.00</span> <span class="sale">已售 0</span> ';
						content += ' 		</div>';
						//content += ' 	</div>';
						//content += ' </div>';
					}
						k++;  
				});	 
				if(list_tempid.toString() != "5") {
					if(pageNum == 0){
						$("#pinterestList").html(content);
						pinterestInit(pinterestObj);
					}else{
						$("#pinterestList").append(content);
						pinterestInit(pinterestObj,true);                
					} 
				} else {
					if(pageNum == 0){
						$("#pinterestList").html(content);
					}else{
						$("#pinterestList").append(content);           
					} 
				}
				

				pageNum	+=	k;
				if(k<limit_num){			//当返回的数据量小于设定加载量，则说明已经加载全部
					downFlag = true;
				}else{
					downFlag = false;
				}
				var scrollTopHHH=localStorage.scrollTopHH;
				if (scrollTopHHH) { //如果有记录scrolltop。返回到滚动处
					var startTime = new Date().getTime();
					var setScroll= setInterval(function(){ 
						$(window).scrollTop(scrollTopHHH);
						if(new Date().getTime() - startTime > 2000 ||$(window).scrollTop()==scrollTopHHH){
							clearInterval(setScroll);
							return;
						}
					 },100);
					 	localStorage.scrollTopHH=""; //返回后清空记录的内容，设置数据回正常。
					 	localStorage.pro_index="";
					 	limit_num=16;
			 			pinterest_perBlock=16;

				}
				// firstimgurl=document.getElementById("pinterestList").getElementsByTagName("img")[0].src;
				// if(imgUrl==""){//分享设置使用了默认，就使用分类列表的第一张图片作为分享图片
				// 	imgUrl=firstimgurl;
				// }

				$('.style2-img').height($('#pinterestList').width()/2);//模板二图片强制2:1
				$('.style3-img').height($('.pinterestLi').width());//模板三图片强制1:1
				
				 save_shareUrl(_data);//保存分享链接
	}	
		
  
  function save_shareUrl(_data){
	  // console.log(_data);
	  /*分享链接参数---start*/
	   if(s_n>0){
			share_url += '&s_n='+s_n;
		}
		if(searchpage>0){
			share_url += '&searchpage='+searchpage;
		}		
	   $.each(_data,function(i,val){
		   if( val!='' ){
			   share_url += '&'+i+'='+val;
		   }	   
	   });	
	   // console.log(share_url);
	 /*分享链接参数---end*/ 
		new_share(debug,appId,timestamp,nonceStr,signature,share_url,title,desc,imgUrl,share_type);
  }
    
    //Jump to 商品详细
    function gotoProductDetail(prodID){
    	var scrollTopH=$(window).scrollTop();
    	var pro_index=$("#artwork_"+prodID).index(); //跳转商品详情时记录跳转前的参数。
    	localStorage.scrollTopHH=scrollTopH;
    	localStorage.pro_index=pro_index; //商品顺序
         window.location.href="/weixinpl/mshop/product_detail.php?customer_id="+customer_id+"&pid="+prodID;
    }		
	//重新加载参数
	function load_input_parameter(_data){
		
		
		//重新修改搜索类型
		var type_tys_input = $('#search_from').val();
		if(type_tys_input>1){
			search_from = type_tys_input;
			_data.search_from = search_from;
		}
		
		//重新修改供应商ID
		var supply_id_input = $('#supply_id').val();
		if(supply_id_input>0){
			supply_id = supply_id_input;
			_data.supply_id = supply_id;
		}
		//新品上市
		var isnew = $('#isnew').val();	
		_data.isnew = isnew;
		
		//热卖产品
		var ishot = $('#ishot').val();
		_data.ishot = ishot;
		
		//VP产品
		var isvp = $('#isvp').val();
		_data.isvp = isvp;
		
		//积分专区
		var isscore = $('#isscore').val();
		_data.isscore = isscore;

		//队列活动专区
		var isqueue = $('#isqueue').val();
		_data.isqueue = isqueue;
		
		//分类ID
		if(type_id!=''){
			_data.type_id = type_id;
			$('#curParentCtgr').val(type_id);
			$('#curChildCtgr').val(type_id);
			$('#curParentCtgr').attr('name',type_name);
			$('#curChildCtgr').attr('name',type_name);
		}
				
		//最低价格
		if(curCostMin!=''){
			_data.curCostMin = curCostMin;
			$("#curCostMin").val(curCostMin);
		}
				
		//最高价格
		if(curCostMax!=''){
			_data.curCostMax = curCostMax;
			$("#curCostMax").val(curCostMax);
		}
				
		//最低积分
		if(curScoreMin!=''){
			_data.curScoreMin = curScoreMin;
			$("#curScoreMin").val(curScoreMin);
		}
				
		//最高积分
		if(curScoreMax!=''){
			_data.curScoreMax = curScoreMax;
			$("#curScoreMax").val(curScoreMax);
		}
				
		//关键字
		if(searchKey!=''){
			_data.searchKey = searchKey;
			$('#tvKeyword').val(searchKey)
		}
	
		//排序
		if(op_sort!=''){
			_data.op_sort = op_sort;
		}				
	}

/******************筛选*******************/
	
    ///////////////////////////////////////////////////// 筛选1-1 Start
    var areaType= 1;
	var industry = 2;
	var select = 3;

    var showSearch = function(id){ // 筛选 Btn Click !
		//弹出
		$('#leftmask').show();
		$('#seardiv').toggle('slow');
		$('#seardiv').css("top:0");
		
		if(id==areaType){
			SelectArea(1); // 筛选1-1
		}else if(id==industry){
			SelectCtgr(); // 筛选1-2
		}else if(id==select){
			SelectCost();
			SelectScore();
		}
		
		$('#toolardiv').hide("slow");
        $("#ctgrTitle").removeClass('list_color_'+$color);
	};
	
	var hideall = function(){
		$('#areadiv').hide();
		$('#industrydiv').hide();
		$('#modiv').hide();
		
	};

	function SelectArea(isFirst){ // 筛选1-1
		hideall();
		$('#areadiv').show("fast");
		
		if (!isFirst) return;
		// init
		$("#selParentCtgr").val($("#curParentCtgr").val());
		$("#selChildCtgr").val($("#curChildCtgr").val());
		$("#selParentCtgr").attr("name", $("#curParentCtgr").attr("name"));
		$("#selChildCtgr").attr("name", $("#curChildCtgr").attr("name"));
		$("#selCostMin").val($("#curCostMin").val());
		$("#selCostMax").val($("#curCostMax").val());
		$("#selScoreMin").val($("#curScoreMin").val());
		$("#selScoreMax").val($("#curScoreMax").val());
		
		
		$("#isAllClear").val('0');
		if ($("#selParentCtgr").val() == '-1'){
			$("#ctgrTitle").text("全部");
		} else {
			$("#ctgrTitle").text($("#selParentCtgr").attr("name") + "(" + $("#selChildCtgr").attr("name") + ")");
		}
		
		if ($("#selCostMax").val() == '0'){
			$("#costTitle").text("全部");
		} else {
			$("#costTitle").text("" + 
			(OOF_P==2 ? $("#selCostMin").val()+OOF_S : OOF_S+$("#selCostMin").val())
			+ " - " + 
			(OOF_P==2 ? $("#selCostMax").val()+OOF_S : OOF_S+$("#selCostMax").val())
			);
		}

		if ($("#selScoreMax").val() == '0'){ //积分专区
			$("#scoreTitle").text("全部");
		} else {
			$("#scoreTitle").text("" + $("#selScoreMin").val() + "积分"+" - " + $("#selScoreMax").val()+"积分");
		}
	};

	var SelectCtgr = function(){ // 筛选1-2
		hideall();
		$('#industrydiv').show("fast");
		
		// init ctgr list
		$(".t-size.sel").removeClass("sel");
		var selCurItem = $(".t-size[ctgrId=" + $("#selChildCtgr").val() + "]");
		selCurItem.addClass("sel");
		var dom = $("a[pCtgrId=" + $("#selParentCtgr").val() + "]");
		$(".city-li-b").removeClass('city-li-b');
		$(".current_cls").hide();
		$(dom).parent().next().show();
		$(dom).addClass('city-li-b');
	};

	var SelectCost = function(){
		hideall();
		$('#modiv').show("fast");
		
		// init cost option
		if ($("#selCostMax").val() == '0'){
			$("#selCostTempTitle").text("全部");
		} else {
			$("#selCostTempTitle").text("已选择：" + 
			(OOF_P==2 ? $("#selCostMin").val()+OOF_S : OOF_S+$("#selCostMin").val())
			+ " - " +
			(OOF_P==2 ? $("#selCostMax").val()+OOF_S : OOF_S+$("#selCostMax").val())
			);
		}
		$("#costCustMin").val("");
		$("#costCustMax").val("");
	};
	
	var SelectScore = function(){//积分
		hideall();
		$('#modiv').show("fast");
		
		// init cost option
		if ($("#selScoreMax").val() == '0'){
			$("#selScoreTempTitle").text("全部");
		} else {
			$("#selScoreTempTitle").text("已选择：" + $("#selScoreMin").val() + "积分"+" - "+ $("#selScoreMax").val()+"积分");
		}
		$("#scoreCustMin").val("");
		$("#scoreCustMax").val("");
	};
	
	$(document).ready(function(){
		$("#leftmask").bind("click",function(){
			$("#seardiv").hide("slow");
			$("#leftmask").hide("slow");
		});
	});
	
	function popClose() { // Close
        $("#seardiv").hide("slow");
		$("#leftmask").hide("slow");
    }
	///////////////////////////////////////////////////// 筛选1-1 End
	///////////////////////////////////////////////////// 筛选1-2 Start
	
	function currentCityCilck(dom){  // 分类选择
		$(".city-li-b").removeClass('city-li-b');
		if ($(dom).parent().next().css("display") == "none") {
			$(".current_cls").hide();
			$(dom).parent().next().show();
			$(dom).addClass('city-li-b');
		}
		else {
			$(".current_cls").hide();
			$(dom).parent().next().hide();
		}
	}
		
	$(document).ready(function() {
		$("#cai").click(function(){
			$(this).toggleClass("sel");
		})

		$(".selBtn").click(function(){
			$(this).toggleClass("sel");
		})
		
		$(".t-size").click(function(){
			$(".t-size").removeClass("sel");
			$(this).addClass("sel");
			confirmChildOpt(0);
		})
		
		//模板2、3图片的高度

	})
	
	////////////////////////////// 筛选1-2 End
	
	function popClearClose(optType){  // 清除选项
		if (optType == 0){ // ctgr
			$("#ctgrTitle").text("全部");
			$("#ctgrTitle").removeClass('list_color_'+$color);
			$("#costTitle").text("全部");
			$("#costTitle").removeClass('list_color_'+$color);
			$("#scoreTitle").text("全部");
			$("#selCostTempTitle").text("全部");
			$("#selCostTempTitle").removeClass('list_color_'+$color);
			$("#selScoreTempTitle").text("全部");
			$("#costCustMin").val("");
			$("#costCustMax").val("");
			$("#selCostMin").val("0");
			$("#selCostMax").val("0");
			$("#selScoreMin").val("0");
			$("#selScoreMax").val("0");
			$("#isAllClear").val('1');
		} else if (optType == 1){ // cost
			$("#selCostTempTitle").text("全部");
			$("#selScoreTempTitle").text("全部");
			$("#selCostTempTitle").removeClass('list_color_'+$color);
			$("#costCustMin").val("");
			$("#costCustMax").val("");
			$("#selCostMin").val("0");
			$("#selCostMax").val("0");
			$("#selScoreMin").val("0");
			$("#selScoreMax").val("0");
			$("#costTitle").text("全部");
			$("#costTitle").removeClass('list_color_'+$color);
			$("#scoreTitle").text("全部");
		}
	}
    
    function confirmChildOpt(optType){
    	if (optType == 0){ // category
    		var selChildItem = $(".t-size.sel");
	    	if (selChildItem.length == 0){
	    		alert("请选择分类！");
	    		return;
	    	} else {
	    		var selChildCtgrId = $(selChildItem).attr("ctgrId");
	    		var selChildCtgrName = $(selChildItem).text();
	    		var selParentCtgrId = $(selChildItem).parent().attr("pCtgrId");
	    		var selParentCtgrName = $(selChildItem).parent().attr("pCtgrName");
	    		
	    		$("#selParentCtgr").val(selParentCtgrId);
	    		$("#selParentCtgr").attr("name", selParentCtgrName);
	    		$("#selChildCtgr").val(selChildCtgrId);
	    		$("#selChildCtgr").attr("name", selChildCtgrName);
	    		
	    		$("#ctgrTitle").text(selParentCtgrName + "(" + selChildCtgrName + ")");
	    		$("#ctgrTitle").addClass('list_color_'+$color);
	    	}
    	} else if (optType == 1){ // cost
    		
    	}
    }
    
    // final option
    function confirmOpt(){			//筛选搜索
    	if ($("#isAllClear").val() == '1'){
    		$("#isAllClear").val(0);
    		$("#selParentCtgr").val("-1");
			$("#selChildCtgr").val("-1");
			$("#selCostMin").val("0");
			$("#selCostMax").val("0");
			$("#selScoreMin").val("0");
			$("#selScoreMax").val("0");
    	}
    	$("#curParentCtgr").val($("#selParentCtgr").val());
		$("#curChildCtgr").val($("#selChildCtgr").val());
		$("#curParentCtgr").attr("name", $("#selParentCtgr").attr("name"));
		$("#curChildCtgr").attr("name", $("#selChildCtgr").attr("name"));
		$("#curCostMin").val($("#selCostMin").val());
		$("#curCostMax").val($("#selCostMax").val());
		$("#curScoreMin").val($("#selScoreMin").val());
		$("#curScoreMax").val($("#selScoreMax").val());
    	//searchData(0);
		//var selParentType = $('#selParentCtgr').val();	//父类分类ID
		var selChildCtgr = $('#selChildCtgr').val();	//选择的分类ID	
		var curCostMin = $('#selCostMin').val();		//最低价	
		var curCostMax = $('#selCostMax').val();		//最高价
		var curScoreMin = $('#selScoreMin').val();		//最低积分	
		var curScoreMax = $('#selScoreMax').val();		//最高积分
		//console.log(selParentType);
		//console.log(selChildCtgr);
		
		
		
		/*if(selParentType!=''&&selChildCtgr!=''){
			search_data.selChildCtgr  = selChildCtgr;
		}else{
			search_data.selParentType = selParentType;
		}*/
		search_data.type_id = selChildCtgr;
		search_data.curCostMin    = curCostMin;
		search_data.curCostMax    = curCostMax;
		search_data.curScoreMin   = curScoreMin;
		search_data.curScoreMax   = curScoreMax;
		search_data.is_shaixuan	  = 1;	//改为筛选
		//_reset();	
		search_is_privilege(selChildCtgr);
		$("#pro_type_id").val(selChildCtgr);
		search_ajax_data(search_data,'search',get_search_data,1);	
		$("#search_none").css("display","none");
    	popClose();//收起筛选层
    }

    function search_is_privilege(id){
    	$.ajax({
    		url:"/weixinpl/mshop/search.class.php?op=seach_pro_type",
    		type:"post",
    		dataType:"json",
    		data:{type_id:id},
    		success:function(data){
    			$("#is_privilege_type").val(data);
    		}
    	})
    }
    
    // custom cost setting 
    function confirmCostCust(){ // 金额自定义
    	if ($.trim($("#costCustMin").val()) == '' || $.trim($("#costCustMax").val()) == ''){
			showAlertMsg ("提示：","价格不能为空，请输入！","知道了");    	
    		return;
    	}
    	if ($.trim($("#costCustMin").val()) < 0 || $.trim($("#costCustMax").val()) < 0 ){
    		showAlertMsg ("提示：","价格不能为负数，请再输入！","知道了");
    		return;
    	}
    	if (parseFloat($.trim($("#costCustMin").val())) != $.trim($("#costCustMin").val()) || parseFloat($.trim($("#costCustMax").val())) != $.trim($("#costCustMax").val()) || $.trim($("#costCustMax").val()) < 0){
			showAlertMsg ("提示：","请输入正确的价格！！","知道了");
    		return;
    	}
    	
    	$("#selCostTempTitle").text("已选择：" + 
		(OOF_P==2 ? $("#costCustMin").val()+OOF_S : OOF_S+$("#costCustMin").val())
		+ " - " +
		(OOF_P==2 ? $("#costCustMax").val()+OOF_S : OOF_S+$("#costCustMax").val())
		).addClass('list_color_'+$color);
    	$("#selCostMin").val($("#costCustMin").val());
		$("#selCostMax").val($("#costCustMax").val());
		$("#costTitle").text("" + 
		(OOF_P==2 ? $("#costCustMin").val()+OOF_S : OOF_S+$("#costCustMin").val())
		+ " - " +
		(OOF_P==2 ? $("#costCustMax").val()+OOF_S : OOF_S+$("#costCustMax").val())
		).addClass('list_color_'+$color);
    }
	
	function confirmScoreCust(){ // 积分自定义
    	if ($.trim($("#scoreCustMin").val()) == '' || $.trim($("#scoreCustMax").val()) == ''){
			showAlertMsg ("提示：","积分不能为空，请输入！","知道了");
    		return;
    	}
    	if ($.trim($("#scoreCustMin").val()) < 0 || $.trim($("#scoreCustMax").val()) < 0 ){
			showAlertMsg ("提示：","积分不能为负数，请再输入！","知道了");
    		return;
    	}
    	if (parseFloat($.trim($("#scoreCustMin").val())) != $.trim($("#scoreCustMin").val()) || parseFloat($.trim($("#scoreCustMax").val())) != $.trim($("#scoreCustMax").val()) || $.trim($("#scoreCustMax").val()) < 0){
			showAlertMsg ("提示：","请输入正确的积分！","知道了");
    		return;
    	}
    	
    	$("#selScoreTempTitle").text("已选择：" + $("#scoreCustMin").val() +"积分" + " - " + $("#scoreCustMax").val() +"积分");
    	$("#selScoreMin").val($("#scoreCustMin").val());
		$("#selScoreMax").val($("#scoreCustMax").val());
		$("#scoreTitle").text("" + $("#scoreCustMin").val() +"积分" + " - " + $("#scoreCustMax").val() +"积分");
    }
    // click cost item
    function clickCostItem(obj){
    	$("#selCostTempTitle").text("已选择：" + $(obj).children("div").children("span").text()).addClass('list_color_'+$color);
    	$("#selCostMin").val($(obj).children("div").attr("minVal"));
		$("#selCostMax").val($(obj).children("div").attr("maxVal"));
		$("#costTitle").text($(obj).children("div").children("span").text()).addClass('list_color_'+$color);
    }
	// click score item
	function clickScoreItem(obj){
    	$("#selScoreTempTitle").text("已选择：" + $(obj).children("div").children("span").text());
    	$("#selScoreMin").val($(obj).children("div").attr("minVal"));
		$("#selScoreMax").val($(obj).children("div").attr("maxVal"));
		$("#scoreTitle").text($(obj).children("div").children("span").text());
    }
/******************筛选*******************/

/***********************函数部分************************/
