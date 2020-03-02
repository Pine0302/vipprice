// init function
    $(function() {

// 事件监听器
    // 选择地址点击事件
    $(".content-header").click(function(){
        //alert("right arrow点击了");
        history.replaceState({},'','order_form.php?from_select_address='+Date.parse(new Date()));   //防止跳转到地址编辑页删除地址后，后退下单页仍显示已删除的收货地址,参数是无作用的，但必须要带
        location.href="my_address.php?customer_id="+customer_id_en+'&a_type=1';
    });

    // 立即支付按键点击事件
    $('.new_pay').click(function(){
        // return alert("o_shop_id: "+o_shop_id);
        // var sum_all_money = $('#sum_all_money').attr('sum_all_money');
        // if (sum_all_money > 0){
        //var delivery = localStorage.getItem('payondelivery_');
         $(this).unbind();
        if( is_collage_product == 1 && group_buy_type == 2 ){   //拼团没有限购限制
            new_subOrder(fromtype,1,"",new_jsonpCallback_saveorder);
        } else {
            

            new_check_limit();  //限购检测
        }
        // }else{
        //  new_check_limit(2,'deductible');    //限购检测
        // }

});

    //right arrow 点击事件
    $(".popup-memu-btn").click(function(){
        alert("right arrow 点击了");
    })

//快递 点击事件
    $(".half-box").click(function(){
        $(this).parent().unbind();
        var $sp_text=$(this).find("span").text();
        $(this).siblings().find('input').removeAttr('checked');
        $(this).find('input').prop('checked',true);
        $(this).parent().siblings('.center-content').find("span").text($sp_text);
    })

    $('.express-type').click(function(){
        $(this).find('.type-box').fadeToggle();
    })
    // //黑色蒙版点击收回支付选择div
    // $(".am-dimmer").click(function(){
    //  new_togglePan_down();
    // })

    });
// init function


/******************函数部分*********************/

    //隐藏选择支付div和蒙版
    function new_togglePan_down(){
     $(".am-dimmer").hide();
     $("#new_zhifuPannel").fadeOut();
    }
    //条件不足弹出提示
    function Tan_Wrong(obj){

        obj.addClass('wrong_red');//添加重新输入的红色框
    }


    //收起loading，收起选择支付，弹出警告
    function alert_warning(alert_str){
        closeLoading();                 //隐藏加载中遮层
        new_togglePan_down();                   //收起选择支付
        showAlertMsg("提示",alert_str,"知道了"); //弹出警告
    }

    //打开按钮
    function btn_on(obj){
        //console.log('btn_on');
        //console.log(obj);
        var slide_body = obj.find('.slide_body');
        var slide_block = obj.find('.slide_block');

        slide_block.css({
                left:20+"px",
                boxshadow:"0 1px 2px rgba(0,0,0,0.05), inset 0px 1px 3px rgba(0,0,0,0.1)"
        });
        slide_body.css({
                background:"#fd832f",
                boxShadow:"0 0 1px #fd832f"
            });
        obj.attr('open_val',1);
    }

    //关闭按钮
    function btn_off(obj){
        //console.log('btn_off');
        //console.log(obj);
        var slide_body = obj.find('.slide_body');
        var slide_block = obj.find('.slide_block');
        slide_block.css({
                left:0,
                boxshadow:"none"
        });
        slide_body.css({
                background:"none",
                boxShadow:"inset 0 0 0 0 #eee, 0 0 1px rgba(0,0,0,0.4)"
        });
        obj.attr('open_val',0);
    }


    //找人代付方法
    function anotherpay(batchcode){
            //---找人到付不支持积分产品---//
            var sum_all_supply_pros_need_score = $('.sum_all_supply_pros_need_score').val();
            if(parseFloat(sum_all_supply_pros_need_score)>0){

                   $('.is_payother').val(0);
                   alert_warning('找人代付不支持积分产品');
                   return false;
            }
            //---找人到付不支持积分产品---//


            //---找人代付不支持购买多种供应商产品---//
              var len =  $('.itemWrapper').length;
              console.log(len);
              if(len>2)
              {
                   $('.is_payother').val(0);
                   alert_warning('找人代付不支持购买多种店铺产品');
                   return false;
              }
              //---找人代付不支持购买多种供应商产品---//


             //---找人代付不与其他优惠结算--//
              var temp_sum = is_curr + is_select_card + is_select_coupon;   //其中有一个开启则提示
              if( temp_sum >0 ){
                  $('.is_payother').val(0);
                   alert_warning('找人代付不与其他优惠结算');
                   return false;
              }

             //---找人代付不与其他优惠结算---//

            //---找人代付 开始---//
            $('.pay_desc').show();
            $('.shadow').show();
            $('.pay_desc_btn').click(function(){        //点击找人代付确认事件

                $('.is_payother').val(1);
                $('.pay_desc').hide();
                $('.shadow').hide();
                var payother_desc = $('.payother_desc').val();
                if(payother_desc==""){
                    payother_desc = "蛋蛋的忧伤，钱不够了，你能不能帮我先垫付下";
                }
                var url = "payother_new.php?pay_batchcode="+batchcode+"&customer_id="+customer_id_en+"&payother_desc="+payother_desc;
                document.location = url;
            });
            $('.shadow').click(function(){
                $('.pay_desc').hide();
                $('.shadow').hide();
            });
    }

    //选择支付方式
    // function new_choose_pay_type(obj){
        // if( obj == 'deductible' ){
        //  var pay_type = "deductible";
        // }else{
        //  var pay_type = obj.data('value');
        // }
        // if( pay_type == 'card' || pay_type == 'moneybag' ){
        //  if( check_pay_password == -1 ){
        //      alert_warning("您还没有设置支付密码，请先去设置。");
        //  }
        // }
        // //alert(pay_type);
        // return;


        // if(pay_type == 'anotherpay'){        //找人代付


        //      //---找人到付不支持积分产品---//
        //      var sum_all_supply_pros_need_score = $('.sum_all_supply_pros_need_score').val();
        //      if(parseFloat(sum_all_supply_pros_need_score)>0){

        //             $('.is_payother').val(0);
        //             alert_warning('找人代付不支持积分产品');
        //             return false;
        //      }
        //      //---找人到付不支持积分产品---//


        //      //---找人代付不支持购买多种供应商产品---//
        //        var len =  $('.itemWrapper').length;
        //        console.log(len);
        //        if(len>2)
        //        {
        //             $('.is_payother').val(0);
        //             alert_warning('找人代付不支持购买多种店铺产品');
        //             return false;
        //        }
        //        //---找人代付不支持购买多种供应商产品---//


        //       //---找人代付不与其他优惠结算--//
        //        var temp_sum = is_curr + is_select_card + is_select_coupon;   //其中有一个开启则提示
        //        if( temp_sum >0 ){

        //            $('.is_payother').val(0);
        //             alert_warning('找人代付不与其他优惠结算');
        //             return false;
        //        }

        //       //---找人代付不与其他优惠结算---//

        //      //---找人代付 开始---//
        //      $('.pay_desc').show();
        //      $('.shadow').show();
        //      $('.pay_desc_btn').click(function(){        //点击找人代付确认事件

        //          $('.is_payother').val(1);
        //          $('.pay_desc').hide();
        //          $('.shadow').hide();
        //          new_subOrder(fromtype,2,pay_type,new_jsonpCallback_saveorder);

        //      });
        //      $('.shadow').click(function(){
        //          $('.pay_desc').hide();
        //          $('.shadow').hide();
        //      });


        //      //---找人代付 结束---//
        // }else{                           //其他支付方式
            // new_subOrder(fromtype,1,"deductible",new_jsonpCallback_saveorder);
        // }

    // }


function new_subOrder(fromtype,pay_immed,pay_type,callback){
  /*参数说明：
  @fromtype         ：1立即购买2购物车
  @pay_immed        ：pay_immed 1: 立即支付， 0：非立即支付 2：代付
  @pay_type         ：支付方式
  @callback         ：回调函数
  */
    //该产品是否是货到付款
    //var delivery_arr = new Array();
    //$(".payondelivery").each(function(i,o){
    //    delivery_arr[i] = $(this).attr("open_val");
   // })
    //delivery_arr = JSON.stringify(delivery_arr);
    //该产品是否是货到付款结束




    var is_go_on = true;                //提交数据到保存订单页面标识
    var identity_15     =/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/;//15位身份证正则式
    var identity_18     =/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/;//18位身份证正则式

  /*当购物车数据为空则返回*/
    //console.log(buy_array_json);

    if(buy_array_json == null || buy_array_json ==''){

            is_go_on = false;
            alert_warning('购物车数据已过期，请重新提交');
            return;

    }
  /*当购物车数据为空则返回*/


   /*当商城设置了身份证且产品设置了身份证购买，用户选择的地址未上传身份证附件，则提示*/

  var identity_str = $('.identity_str').val();
  var identity_arr = identity_str.split('_');
  var shop_identity     = identity_arr[0];          //商城身份证开关
  var is_uploadidentity = identity_arr[1];;         //是否设置上传身份证附件
  var shop_p_identity   = identity_arr[2];          //大于0则说明产品中是否要填写、上传身份证
  var shop_id_tf        = identity_arr[3];          //地址管理的身份证正反附件

  console.log(shop_id_tf);
    /*检测是否已经上传身份证附件*/

      if(shop_identity == 1 && is_uploadidentity == 1){
          if(shop_p_identity == 1){     //产品设置优先
              if(shop_id_tf<2){         //正反面为2
                    is_go_on = false;
                    alert_warning('请检查身份证管理是否已经上传身份证附件');
                    return;
                }
          }
      }
    /*检测是否已经上传身份证附件*/

  /*当商城设置了身份证且产品设置了身份证购买，用户选择的地址未上传身份证附件，则提示*/

    loading(100,1);                     //加载中遮层
    // alert(1);

    var is_payother = $('.is_payother').val();
    var payother_desc = $('.payother_desc').val();
    var sum_all_money = $('.sum_all_money').val();
    var all_pro_weight = $(".open_curr").val();
    //var user_open_curr = $('.open_curr').attr('open_val');
    var sum_ov = 0;
    $.each($('.open_curr'),function(){
        var ov = $(this).attr('open_val');
        sum_ov += parseFloat(ov);
    });
    //console.log(sum_ov);
    if(sum_ov>0){
       var user_open_curr = 1;
    }else{
       var user_open_curr = 0;
    }

    var aid_d = $('#information').data('id');   //未加密
    var aid = $('.aid').val();                  //已加密

    /*检测拼团有效性 start*/
    if( is_go_on ){
        if( is_collage_product_info != '' ){
            var collage_info = is_collage_product_info.split('_');
            if( collage_info[0] == 1 && collage_info[1] == 2 ){
                $.ajax({
                    url: 'check_collage.php?customer_id='+customer_id_en,
                    dateType: 'json',
                    type: 'post',
                    async: false,
                    data: {
                        user_id : user_id,
                        pid : pid,
                        num : rcount,
                        activitie_id : activitie_id,
                        group_id : group_id,
                        comefrom : 1
                    },
                    success: function(res){
                        res = eval('('+res+')');
                        if( res.code > 0 ){
                            is_go_on = false;
                            alert_warning(res.msg);
                            return;
                        }
                    }
                })
            }
        }
    }
    /*检测拼团有效性 end*/


    /*检查下单的产品中是否虚拟产品*/
    if(is_go_on){
        var itemMainDiv_len = $('.itemMainDiv').length;
        var sum_is_virtual = 0;             //累计虚拟产品数量
        if( itemMainDiv_len >0 ){
            $.each($('.itemMainDiv'),function(){
                var is_virtual = $(this).attr('is_virtual');
                sum_is_virtual += parseFloat(is_virtual);

            });


        }
    }
    //检查收货地址
    console.log('配送类型：'+location_info_exp);
    if(is_go_on){
        if(sum_is_virtual ==0 ){

            // if(aid_d == -1 && express_type == 0){
            if(aid_d == -1 && location_info_exp == 1){
                is_go_on = false;
                $(".spinner").hide();
                $(".sharebg-active").hide();
                alertAutoClose('收货地址不能为空');
                return;
            }
        }
    }
    /*检查下单的产品中是否虚拟产品*/


    /*-------会员卡折扣--------*/

    var select_card_id = $('.select_card_id').val();

    /*-------会员卡折扣--------*/

    /*-------优惠券--------*/


    // var select_coupon_id = 0;
    // if(is_select_coupon ==1 ){
         // select_coupon_id = $('.select_coupon_id').val();
    // }

    /*-------优惠券--------*/

    //var sendtime_id = $('.sendtime_id').val();
    var sendtime = $('.sendtime').val();


    /*-------获取购物币--------*/
    if(is_go_on){
            var curr_arr = new Array();//购物币数组
            var max_currr = $('.user_currency').attr('max_currr');
            //console.log(is_curr);
            if(is_curr_count >0){

            var user_curr_money    = 0;       //已填购物币数额

            //遍历购物币输入框
            $(".user_currency").each(function(){
                //获取标识
                //console.log(is_curr);
                var supply_id = $(this).attr('supply_id');
                console.log(supply_id);
                //有开启按钮才读取填入的购物币数量
                if(is_curr[supply_id]){
                    //计算已输入购物币总和
                    //console.log(product_id);
                    this_currency = $(this).val();
                    if(this_currency==null || this_currency==''){
                        this_currency = 0;
                    }
                    console.log(this_currency);
                    user_curr_money += parseFloat(this_currency);
                    var curr_array_temp = new Array(supply_id,this_currency);
                    curr_arr.push(curr_array_temp);
                }

            });

                if(parseFloat(user_curr_money) < 0 ){   //不能少于0
                    is_go_on = false;
                    //alert_warning("<?php echo defined('PAY_CURRENCY_NAME')? PAY_CURRENCY_NAME: '购物币'; ?>不能少于零");
                    return;
                }
                if(parseFloat(user_curr_money) > parseFloat(max_currr) ){
                    //Tan_Wrong($('.user_currency'));
                    is_go_on = false;
                    alert_warning("您输入的<?php echo defined('PAY_CURRENCY_NAME')? PAY_CURRENCY_NAME: '购物币'; ?>超出您的购物币余额！");
                    user_currency = $('.user_currency').val(0);
                    return;
                }

            }
    }
    /*-------获取购物币--------*/


    /*-------获取发票--------*/
    var invoice_arr = new Array();
    $('.invoice').each(function(){

        var thiss = $(this);
        var invoice_parent = thiss.parents('.itemWrapper');
        var invoice_supply_id = invoice_parent.attr('supply_id');
        var invoice_cont = thiss.val();
        if(invoice_cont ==''){
            invoice_cont = '个人发票';
        }
        var temp_arr = new Array(invoice_supply_id,invoice_cont);
        invoice_arr.push(temp_arr)

    });

    /*-------获取发票--------*/

    /*-------获取留言--------*/
    var remark_arr = new Array();
    $('.remark').each(function(){

        var thiss = $(this);
        var remark_parent = thiss.parents('.itemWrapper');
        var remark_supply_id = remark_parent.attr('supply_id');
        var remark_cont = thiss.val();
        var temp_arr = new Array(remark_supply_id,remark_cont);
        remark_arr.push(temp_arr)

    });

    /*-------获取留言--------*/

/*检查下单的产品中是否无配送，则不能下单*/
    if(is_go_on){
            //是否是货到付款
            var payondelivery = localStorage.getItem('payondelivery_'+user_id);
            if(sum_is_virtual==0){      //虚拟产品无需检查配送
                var itemMainDiv_len = $('.itemMainDiv').length;
                var out_express = 0;
                var temp_str = '';

                //判断货到付款是否使用了店铺
                for(i=0;i<is_express_arr.length;i++)
                {
                    if(is_express_arr[i] == -1)
                    {
                        express_ondelivery = -1;
                    }
                }
                if(express_ondelivery ==-1 && payondelivery == 1)
                {
                    $(".spinner").hide();
                    $(".sharebg-active").hide();
                    alertAutoClose(temp_str.substring(0,temp_str.length-1) +'产品没有适合您所在地的配送方式');
                    // showAlertMsg('提示',temp_str.substring(0,temp_str.length-1) +'产品没有适合您所在地的配送方式','知道了');
                    return;
                }

                if( itemMainDiv_len >0 ){
                    $.each($('.itemMainDiv'),function(){
                        var is_express = $(this).attr('is_express');
                        var pro_name = $(this).attr('pro_name');
                        var _obj = $(this).parent().parent().find('.white-list').find('.store');

                        if( is_express == -1){      //-1表示无配送方式，0表示有配送方式
                            is_go_on = false;
                            out_express ++;
                            console.log(payondelivery);
                            if ( is_collage_product != '1' && payondelivery != 1) {console.log(456789);
                                setTimeout(function(){_obj.trigger('click')},1500);
                            }

                            showAlertMsg('提示',"【"+pro_name+"】"+'产品没有适合您所在地的配送方式','知道了',function(){

                                if ( is_collage_product != '1' ) {
                                    _obj.trigger('click');
                                }
                            });

                             return false;
                            temp_str += "【"+pro_name+"】,";
                        }

                    });

                    if(out_express >0){
                           is_go_on = false;

                            showAlertMsg('提示',temp_str.substring(0,temp_str.length-1) +'产品没有适合您所在地的配送方式','知道了');
                           return;
                    }
                }


            }
    }
/*检查下单的产品中是否无配送，则不能下单*/

/*-------获取身份证--------*/
    if(is_go_on){
        var indentity_len = $('.indentity').length;
        var add_keyid = $('.add_keyid').val();

        if(indentity_len>0){
            var indentity_arr = new Array();
            $('.indentity').each(function(){

                var thiss = $(this);
                var Identity_parent = thiss.parents('.itemWrapper');
                var Identity_supply_id = Identity_parent.attr('supply_id');
                var indentity_cont = thiss.val();
                if(indentity_cont!=''){
                    if( !identity_15.test(indentity_cont) && !identity_18.test(indentity_cont) ){
                        is_go_on = false;
                        alert_warning('请填写正确的身份证号码');
                        thiss.focus();
                        return ;
                    }
                    var temp_arr = new Array(Identity_supply_id,indentity_cont);
                    indentity_arr.push(temp_arr);
                }else{
                    is_go_on = false;

                    Tan_Wrong(thiss);               //提醒填写身份证红框
                    thiss.focus();
                    alert_warning('请输入身份证');

                    return ;
                }


            });
        }
    }
    /*-------获取身份证--------*/
    /*---------获取必填信息--------*/
    if(is_go_on){

        if($('.info').length > 0){   //判断购买商品是否需要填写必填信息
            var info_object = "";
            var new_info_object_arr = "";
            var info_object_arr = new Array();
            info_object = localStorage.getItem('info_'+user_id);    //读取localStorage的数据
            info_object_arr = JSON.parse(info_object);              //json转数组

            $('.itemWrapper').each(function(){                      //遍历所有必填信息选项
                if($(this).find('.info').length > 0){
                    var ii = $(this).attr('ii');
                    var obj_ = $(this).find('.white-list').find('.info');

                    if(info_object_arr =="" || info_object_arr == null){    //所有必填信息是否为空
                        is_go_on = false;
                        alert_warning('请填写必填信息！');
                        showAlertMsg('提示','请填写必填信息！','知道了',function(){obj_.trigger('click');})
                        return false;
                    }else{
                        if(info_object_arr[ii]=="" || typeof(info_object_arr[ii])=="undefined" || info_object_arr[ii]==null){   //遍历必填信息是否为空
                            is_go_on = false;
                            showAlertMsg('提示','请填写必填信息！','知道了',function(){
                                obj_.trigger('click');
                                })
                            return false;
                        }else{

                        }
                    }

                }

            });
            if(info_object!=null){
                new_info_object_arr = disconnect_array(info_object_arr);    ////将多维数组装成二维数组结构
            }
            // console.log(new_info_object_arr);return;
        }




    }

    /*---------获取必填信息--------*/
    /*检查是否使用自定义区域*/
if(is_go_on){
    var diy_area_id = -1;
    if(is_diy_area >0 && default_diy_area_id_f>0){
        diy_area_id =  $('.diy_area_id').val();
        if( diy_check == 1 ){
        }else{
            showAlertMsg('提示','请选择配送位置！','知道了',function(){$('.diy_team').trigger('click');})
            is_go_on = false;
            return false;
        }
    }
}
    /*检查是否使用自定义区域*/

    /*---------获取门店--------*/

    var store_object = localStorage.getItem('store_'+user_id);  //读取localStorage的数据

    var store_object_arr = new Array();
    if(store_object==null || store_object==''){

    }else{
        store_object_arr = JSON.parse(store_object);            //json转数组
    }

    /*---------获取门店--------*/

    /*---------获取订货系统门店--------*/
    var orderingretail_store_object_arr = new Array();
    // if (is_orderingretail_store == 1)
    // {
        var orderingretail_store_object = localStorage.getItem('orderingretail_store_'+user_id);  //读取localStorage的数据

        if(orderingretail_store_object==null || orderingretail_store_object==''){
            // alert_warning('请选择门店！');
            // return;
        }else{
            orderingretail_store_object_arr = JSON.parse(orderingretail_store_object);            //json转数组
        }
    // }

    /*---------获取订货系统门店--------*/

    
    /*---------获取配送时间--------*/
    if( delivery_time !='' ){
        var delivery_time_arr = delivery_time.split('至');
        var delivery_time_arr_arr = delivery_time_arr[0].split(' ');
        delivery_time = delivery_time_arr[0]+'_'+delivery_time_arr_arr[0]+' '+delivery_time_arr[1];
    }
    /*---------获取配送时间--------*/


    /*---------获取优惠券--------*/
    if(is_select_coupon>0){
        var coupon_object = localStorage.getItem('coupon_'+user_id);    //读取localStorage的数据

        var coupon_object_arr = new Array();
        if(coupon_object==null || coupon_object==''){

        }else{
            coupon_object_arr = JSON.parse(coupon_object);          //json转数组
        }
    }
    /*---------获取优惠券--------*/

/*检查下单的税收产品中是否含有多种税收类型，则不能下单*/

    if(is_go_on){
                var itemMainDiv_len = $('.itemWrapper').length;
                var out_tax = 0;
                if( itemMainDiv_len >0 ){
                    $.each($('.itemWrapper'),function(){
                        var tax_code = $(this).attr('tax_code');

                        if( tax_code > 20000){      //产品含有多种税收，无法提交订单
                            out_tax ++;
                        }
                    });
                    if(out_tax >0){
                           is_go_on = false;
                           alert_warning('税收产品结算错误！');
                           return;
                    }
                }
    }

/*检查下单的税收产品中是否含有多种税收类型，则不能下单*/

/*判断用户会员卡余额是否足够*/
    // if(is_go_on){
    //   if(pay_type == 'card'){

    //     var check_money = $('#sum_all_money').attr('sum_all_money');
    //     var check_card_id = $('.select_card_id').val();
    //     $.ajax({
    //                    url: "select_cards.class.php?op=check_remain",
    //                    data:{

    //                         check_card_id   : check_card_id,
    //                         check_money     : check_money

    //                         },
    //                    type: "POST",
    //                    dataType:'json',
    //                    async: false,
    //                    success:function(result){

    //                        if(result.code == 10005){
    //                            is_go_on = false;
    //                             alert_warning('您的会员余额不足');
    //                            return false;
    //                        }else{

    //                        }


    //                    },
    //                    error:function(er){

    //                    }

    //         });
    //   }
    // }
  /*判断用户会员卡余额是否足够*/

  /*if(is_go_on){
        if(pay_type == '找人代付'){

                //---找人到付不支持积分产品---//
                var sum_all_supply_pros_need_score = $('.sum_all_supply_pros_need_score').val();
                if(parseFloat(sum_all_supply_pros_need_score)>0){

                       $('.is_payother').val(0);
                       is_go_on = false;
                       alert_warning('找人到付不支持积分产品');
                       return false;
                }
                //---找人到付不支持积分产品---//

                //---找人代付不支持购买多种供应商产品---//
                  var len =  $('.itemWrapper').length;
                  console.log(len);

                  if(len>2)
                  {
                       $('.is_payother').val(0);
                       is_go_on = false;
                       alert_warning('找人代付不支持购买多种店铺产品');
                       return false;
                  }
                  //---找人代付不支持购买多种供应商产品---//


                 //---找人代付不与其他优惠结算--//
                  var temp_sum = is_curr + is_select_card + is_select_coupon;   //其中有一个开启则提示
                  if( temp_sum >0 ){

                      $('.is_payother').val(0);
                        is_go_on = false;
                       alert_warning('找人代付不与其他优惠结算');
                       return false;
                  }


                //---找人代付不与其他优惠结算---//

            }

    }*/

    if(parseFloat(sum_all_money)<0){

       is_go_on = false;
       alert_warning('支付金额不能为负数');
       return false;
    }



  if(is_go_on){



    /*---------重组后的购物车数据 start--------*/
    var buy_array_json_new = new Array();
    var i = 0;  //累计数
    var is_has_store    = 1;   //是否选择了订货系统门店
   
    $.each(buy_array_json,function(keys,values){        //buy_array_json：从php页面获取的购物车数据
      /* console.log("this is what i want");
    console.log(values);

    console.log("this is what i want");*/
        /*
        temp_arr 数组说明：
        [0] 供应商ID

        [1] 该供应商下的每个产品数组[
                [0]     : 产品ID
                [1]     : 产品属性ID
                [2]     : 产品数量
                [3]     : 邮费
                [4]     : 必填信息
                [5]     : 产品属性字符串
                [6]     : 电商直播产品
                [8]     : 积分类型
                [9]     : 积分活动id
                [12]    : 云店ID
            ]
        [2] 备注数据
        [3] 身份证原始数据

        [4] 门店ID
        [5] 门店名称
        [6] 发票内容

        [7] 优惠券ID
        [8] 优惠券金额
        [9] 砍价活动数据
        [10] 众筹活动数据
        [11] 门店ID
        [12] 子门店ID
        [13] 快递/自提  1快递 2自提
        PS:后面数据请往后下标添加，不能修改原本的
        */
        var temp_arr = new Array();                     //重组后的新数组
        temp_arr[0] = keys;                             //供应商ID
        temp_arr[1] = values;                           //该供应商下的所有产品数组

        /*合并必填信息到产品数据*/
        if(info_object!=null){
            $.each(values,function(key,pro){                    //遍历每个产品并找出必填信息对应的数据，最后重组到里面
               
                var pro_id = pro[0];            //当前产品ID
                var pro_pros = pro[1];          //当前产品属性
                var compare_pro = pro_id+''+pro_pros;       //转字符串
                var info_arr = new Array();     //一个产品的必填信息
                $.each(new_info_object_arr,function(m,info_pro){        //遍历所有必填信息数组，并查找与当前产品的ID和属性相同的必填信息数据
                    var info_arr2 = new Array();

                    var info_pro_id = info_pro[0];  //必填信息的产品ID
                    var info_pro_pros = info_pro[1];//必填信息的产品属性
                    var compare_info = info_pro_id+''+info_pro_pros;


                    //当前产品与必填信息的产品信息相同，把必填信息加入到当前产品的数据中
                    if(compare_pro == compare_info ){

                        info_arr2[0] = info_pro[2][0];  //必填信息的名称
                        info_arr2[1] = info_pro[2][1];  //必填信息的内容
                        info_arr.push(info_arr2);       //找到匹配追加在数组里面
                        temp_arr[1][key][4] = info_arr; //把必填信息重组到当前产品的数据中
                    }

                });


            });

        }
        /*合并必填信息到产品数据*/
       
        
        /*检查是否支付金额为0*/
        $.each(values,function(key,pro){
            if( pro[8] == 22 || pro[8] == 24 ){
                temp_arr[1][key][8]   = 22;       //初始化为22
                is_int_exchange_order = 1;
                if( using_exchange_type == 2 ){
                    temp_arr[1][key][8] = 24;     //如果兑换类型换成门店
                }      
            }
        });
        /*检查是否支付金额为0*/


      

        /*合并备注数据到购物车数据*/
         $.each(remark_arr,function(k1,v1){
            if(v1==null || v1==''){
                    return true;        //跳出当前循环
                }
            if(v1[0] == keys){              //key是供应商的ID
                //console.log(v1);
                temp_arr[2] = v1[1];
            }

        });
        /*合并备注数据到购物车数据*/

        /*合并身份证数据到购物车数据*/

        // if(typeof(indentity_arr)!='undefined'){
        if(indentity_len>0){

            $.each(indentity_arr,function(k2,v2){
                if(v2==null || v2==''){
                    return true;        //跳出当前循环
                }
                if(v2[0] == keys){          //key是供应商的ID
                    //console.log(v2);
                    temp_arr[3] = v2[1];
                }

            });


        }
        /*合并身份证数据到购物车数据*/


        /*合并门店到产品数据*/
        //console.log(store_object);
        if(store_object!=null){
             $.each(store_object_arr,function(k3,v3){
                if(v3==null || v3==''){
                    return true;        //跳出当前循环
                }
                if(v3[0] == keys){      //key是供应商的ID
                    //console.log(v3);

                    var store_str = v3[2];      //门店名称
                    if(v3[1] == -1){
                         store_str = '免邮';
                    }

                    temp_arr[4] = v3[1];        //门店ID
                    temp_arr[5] = store_str;
                }

            });

        }
        /*合并门店到产品数据*/


        /*合并发票数据到购物车数据*/
         $.each(invoice_arr,function(k4,v4){
            if(v4==null || v4==''){
                    return true;        //跳出当前循环
                }
            if(v4[0] == keys){              //key是供应商的ID
                //console.log(v4);
                temp_arr[6] = v4[1];        //发票内容
            }

        });

        /*合并发票数据到购物车数据*/

        /*合并优惠券到产品数据*/
        if(coupon_object!=null){
             $.each(coupon_object_arr,function(k5,v5){
                if(v5==null || v5==''){
                    return true;        //跳出当前循环
                }
                if(v5[3] == keys){      //key是供应商的ID
                    //console.log(v5);
                    var coupon_str = v5[2];     //优惠券金额
                    temp_arr[7] = v5[0];        //优惠券ID
                    temp_arr[8] = coupon_str;
                }

            });

        }
        /*合并优惠券到产品数据*/

        //----------合并数据
        buy_array_json_new[i] = (temp_arr);
        //console.log(buy_array_json_new[i]);

        /*合并发票数据到购物车数据*/
         $.each(invoice_arr,function(k4,v4){
            if(v4==null || v4==''){
                    return true;        //跳出当前循环
                }
            if(v4[0] == keys){              //key是供应商的ID
                //console.log(v4);
                temp_arr[6] = v4[1];        //发票内容
            }

        });
        /*合并发票数据到购物车数据*/

        /*合并快递数据到购物车数据*/
        /* $.each(supply_express_json,function(k5,v5){
            if(v5==null || v5==''){
                    return true;        //跳出当前循环
                }
            if(k5 == keys){             //key是供应商的ID
                //console.log(v5);
                temp_arr[7] = v5;       //快递内容
            }

        }); */

        //temp_arr[7] = supply_express_json[keys];      //快递内容
        /*合并发票数据到购物车数据*/

        /*sz郑培强添加用于砍价活动--start*/
        if(form_bargain_sz_data){
            temp_arr[9] = form_bargain_sz_data;
        }
        /*sz郑培强添加用于砍价活动--end*/
        /*sz郑培强添加用于众筹活动--start*/
        if(form_crowdfund_sz_data){
            temp_arr[10] = form_crowdfund_sz_data;
        }
        /*sz郑培强添加用于众筹活动--end*/

        /* 订货系统门店 */
        console.log(orderingretail_store_object_arr);
        $.each(orderingretail_store_object_arr, function(k,v){
            if(v==null || v==''){
                return true;        //跳出当前循环
            }
            if(v[0] == keys){      //key是供应商的ID

                var store_str = v[3];      //门店名称

                if(v[1] == -1 && v[2] == -1 && v[4] == 2){
                    is_has_store = 0;
                }
                temp_arr[11] = v[1];        //门店ID
                temp_arr[12] = v[2];        //子门店ID
                temp_arr[13] = v[4];        //快递/自提  1快递 2自提
                if((temp_arr[12]>0) && temp_arr[13]==1){
                    is_has_store = 0;
                }
            }

        });

        /* 订货系统门店 */

        //----------合并数据
        buy_array_json_new[i] = (temp_arr);
        //console.log(buy_array_json_new[i]);

        i++;
    }) ;
    console.log(buy_array_json_new);
  //  return;
  
    if (!is_has_store)
    {
        alert_warning('请选择门店！');
        return;
    }

    /*---------重组后的购物车数据 end--------*/



    if(order_debug){
        console.log('购物车数据：');
        console.log(buy_array_json_new);
        console.log('购物币数据：');
        console.log(curr_arr);
    }
    // alert(buy_array_json_new);
	var is_exit = false;
    buy_array_json_new = JSON.stringify(buy_array_json_new);            //购物车数组转json
    curr_arr = JSON.stringify(curr_arr);//购物币数组转json
    console.log('666oop');
    console.log(buy_array_json_new);
  
      // buy_array_json_new = '[["194508",[["4627","","3",1,2,"","","","21","-1","","0"]],""],' +
      //     '["-1",[["5573","","6",1,2,"","","","21","-1","20","1"]],""]]'; //模拟数据
    //alert('68Q');
      $.ajax({
          url: "check_exchange_goods.php",
          data: {
              'json_data': buy_array_json_new,            //购物车数据
              'user_open_curr': user_open_curr,            //客户选择购物币开关
              'user_currency': curr_arr,                  //客户使用的购物币
              'is_select_card': is_select_card,        //使用会员卡开关
              'select_card_id': select_card_id,          //选择会员卡折扣ID
              'customer_id':customer_id,
              'user_id':user_id,
          },
          type: "POST",
          dataType: 'json',
          async: false,
          success: function (result) {
              console.log('result');
              console.log(result);
              //如果是门槛数组为空，则表示没有换购产品，否则开始进行相关的验证
              //money_ex_express 已经减去优惠券和购物币
              if(result['threshold_arr'] ){

                  //实付金额（包含了快递费和换购产品的价格）减去换购产品的金额必须大于0，因为换购产品不参与任何优惠（购物币、优惠券）
                  if(parseFloat(money_ex_express) - parseFloat(result['ex_price_count']) >= 0) {
                      //
                      //此判断为商城兑换积分时，支付金额为零，使用积分需要支付密码
                      if( is_check_password == 0 && (parseFloat(money_ex_express) - parseFloat(result['ex_price_count'])) == 0 && is_int_exchange_order == 1){

                            $(".spinner").hide();
                            $(".sharebg-active").hide();
                            check_has_password ();
                            is_exit = true;
                      }


                      if (user_open_curr) {
                          //购物币开关开启，则表示没有用会员卡
                      } else if (result['card_discount']) {
                          money_ex_express = result['card_discount'] * (parseFloat(money_ex_express)- parseFloat(result['ex_price_count']));
                      }
                      for (var i = 0; i < result['threshold_arr'].length; i++) {
                          console.log("门槛"+result['threshold_arr'][i]);
                          console.log("money_ex_express"+money_ex_express);
                          if ((parseFloat(money_ex_express) - parseFloat(result['ex_price_count']) ) < parseFloat(result['threshold_arr'][i])) {
                              showAlertMsgNoclose('提示','所选换购产品未达到换购门槛金额！请重新选购','知道了',callback_exit);
                              is_exit = true;
                          }
                      }
                  }else{
                      showAlertMsgNoclose('提示',"换购产品不能使用<?php echo defined('PAY_CURRENCY_NAME')? PAY_CURRENCY_NAME: '购物币'; ?>和优惠券",'知道了',callback_exit);
					  is_exit = true;
                  }
              }else if(result['status'] == 400){
                  showAlertMsgNoclose('提示',result['msg'],'知道了',callback_exit);
				  is_exit = true;
              }
          },
          error: function (er) {
              is_exit = true;
          }
      });
       
	   if(is_exit){
			return;
	   }
	   

    var local_id = customer_id+'_'+user_id+'_ex';
    //是否是货到付款
    var delivery = $(".payondelivery").attr("open_val");
    //测试用
    console.log(buy_array_json_new);
    console.log(curr_arr);
    

    setTimeout(function(){},2000);
      var confirm_msg='';//提示信息
      var pay_price=$("#sum_all_money").attr('sum_all_money');//实际需要支付金额
      if(parseFloat(pay_price)==0){
          if(parseFloat(user_curr_money)>0){
               confirm_msg="是否用<?php echo defined('PAY_CURRENCY_NAME')? PAY_CURRENCY_NAME: '购物币'; ?>全额支付";
          }else if(sum_all_supply_pros_need_score>0){
              confirm_msg="是否用积分抵扣支付";
          }
      }
      if(confirm_msg!=""){
          closeLoading();
          $("#gyuji2").show();
          showConfirmMsg02("提示",confirm_msg,"确定","取消",function(){
              loading(100,1);                     //加载中遮层
          $.ajax({
              url: "save_order_new.php",
              data: {

                  json_data: buy_array_json_new,    //购物车数据
                  pay_immed: pay_immed,             //pay_immed 1: 立即支付， 0：非立即支付 2:代付
                  pay_type: pay_type,
                  user_open_curr: user_open_curr,        //客户选择购物币开关
                  user_currency: curr_arr,         //客户使用的购物币
                  is_select_card: is_select_card,        //使用会员卡开关
                  select_card_id: select_card_id,        //选择会员卡折扣ID
                  industry_type: 'shop',
                  // select_coupon_id    :select_coupon_id,      //选择优惠券折扣ID
                  // sendtime_id      :sendtime_id,           //送货时间ID
                  sendtime: sendtime,              //送货具体时间
                  is_payother: is_payother,           //是否代付状态
                  payother_desc: payother_desc,         //代付描述
                  diy_area_id: diy_area_id,           //区域模式-自定义区域编号
                  sum_all_money: sum_all_money,         //产品总金额
                  all_pro_weight: all_pro_weight,        //产品总重量
                  aid: aid,                   //地址ID
                  check_first_extend: check_first_extend,    //是否符合首次推广奖励
                  extend_money: extend_money,          //首次推广奖励金额
                  delivery_time: delivery_time,         //配送时间
                  is_collage_product_info: is_collage_product_info, //是否走拼团路线，拼团标识_单独购买或团购_单独购买价格_团购价_活动id_团id
                  delivery_arr:payondelivery,                //是否货到付款
                  sum_shop_reward_en:sum_shop_reward_en,        //店铺奖励抵扣
                  sum_self_reward_en:sum_self_reward_en,     //3级奖励抵扣

                  yundian_id:yundian_id     //下单的云店ID
              },
              type: "POST",
              dataType: 'json',
              async: true,
              success: function (result) {
                  console.log("result1");
                  console.log(result);
                  localStorage.setItem('payondelivery_'+user_id,'');  //清除缓存数据
                  clearCookie('using_exchange_type_'+user_id,'',-1);
                  if (IS_REDIS && result.status == 1 && result.remark == 'MQ') {  //开启消息列队且无错误则运行订单消息列队方法
                      /*查询订单消息列队是否完成 start */
                      redis_delay(result, pay_type, fromtype);
                      /*查询订单消息列队是否完成 end */
                  } else {
                    localStorage.removeItem(local_id);
                      callback:callback(result, pay_type, fromtype); //带参回调
                  }
              },
              error: function (er) {
              }
          });
      },function(){closeLoading();$("#gyuji2").hide();return false;});
      }else {
            // var buy_array_json_new = '[["194508",[["4627","","1",1,2,"","","","21","0"]],[["4627","","1",1,2,"","","","21","0"]],""],["-1",[["5470","","1",1,2,"","","","21","0"]],""]]';
          // alert(buy_array_json_new)
          $.ajax({
              url: "save_order_new.php",
              data: {

                  json_data: buy_array_json_new,    //购物车数据
                  pay_immed: pay_immed,             //pay_immed 1: 立即支付， 0：非立即支付 2:代付
                  pay_type: pay_type,
                  user_open_curr: user_open_curr,        //客户选择购物币开关
                  user_currency: curr_arr,         //客户使用的购物币
                  is_select_card: is_select_card,        //使用会员卡开关
                  select_card_id: select_card_id,        //选择会员卡折扣ID
                  industry_type: 'shop',


                  // select_coupon_id    :select_coupon_id,      //选择优惠券折扣ID
                  // sendtime_id      :sendtime_id,           //送货时间ID
                  sendtime: sendtime,              //送货具体时间
                  is_payother: is_payother,           //是否代付状态
                  payother_desc: payother_desc,         //代付描述
                  diy_area_id: diy_area_id,           //区域模式-自定义区域编号
                  sum_all_money: sum_all_money,         //产品总金额
                  all_pro_weight: all_pro_weight,        //产品总重量
                  aid: aid,                   //地址ID
                  check_first_extend: check_first_extend,    //是否符合首次推广奖励
                  extend_money: extend_money,          //首次推广奖励金额
                  delivery_time: delivery_time,         //配送时间
                  is_collage_product_info: is_collage_product_info, //是否走拼团路线，拼团标识_单独购买或团购_单独购买价格_团购价_活动id_团id
                  delivery_arr:delivery,
                  sum_shop_reward_en:sum_shop_reward_en,        //店铺奖励抵扣
                  sum_self_reward_en:sum_self_reward_en,     //3级奖励抵扣

                  yundian_id:yundian_id     //下单的云店ID
              },
              type: "POST",
              dataType: 'json',
              async: true,
              success: function (result) {
                  console.log("result2");
                  console.log(result);
                  localStorage.setItem('payondelivery_'+user_id,'');  //清除缓存数据
                  clearCookie('using_exchange_type_'+user_id,'',-1);
                  if (IS_REDIS && result.status == 1 && result.remark == 'MQ') {  //开启消息列队且无错误则运行订单消息列队方法
                      /*查询订单消息列队是否完成 start */
                      redis_delay(result, pay_type, fromtype);
                      /*查询订单消息列队是否完成 end */
                  } else {
                        localStorage.removeItem(local_id);
                      callback:callback(result, pay_type, fromtype); //带参回调
                  }


              },
              error: function (er) {

              }

          });
      }
}
}

//将多维数组装成二维数组结构
function disconnect_array(array){


    var rtn_array = new Array();
    $.each(array,function(k1,v1){

        //console.log(typeof(v1));
        //console.log(v1);
        if((typeof(v1) !='undefined') && v1!= null ){
            $.each(v1,function(k2,v2){
                $.each(v2[2],function(k3,v3){
                    var temp = new Array(v2[0],v2[1],v3);
                    rtn_array.push(temp);
                });
            });
        }
    });
    return rtn_array;
}

//下单清除订单页面的本地存储
function clear_local_Storage(){
    /*
    修改须知：修改此方法记得同步到 order_cart.js product_detail.js

    */

    localStorage.removeItem('info_'+user_id);//清空必填信息内容

    localStorage.removeItem('store_'+user_id);//清空门店内容

    localStorage.removeItem('envent_'+user_id);//清空被动事件内容

    localStorage.removeItem('coupon_'+user_id);//清空优惠券内容

    localStorage.removeItem('curr_'+user_id);//清空购物币内容

    localStorage.removeItem('invoice_'+user_id);//清空发票内容

    localStorage.removeItem('remark_'+user_id);//清空备注内容

    localStorage.removeItem('coupon_'+user_id);//清空优惠券内容

    localStorage.removeItem('orderingretail_store_'+user_id);//清空订货系统门店

    clear_coupon();
}


function clear_coupon(){        //清空使用优惠券记录
    $.ajax({
        url: "clear_coupon_class.php",
        dataType: 'json',
        type: 'post',
        data:{'customer_id':customer_id,'user_id':user_id},
        success:function(result){

        }
    })
}
//保存订单的回调函数

function new_jsonpCallback_saveorder(result,pay_type,fromtype){
    // return alert("o_shop_id: "+o_shop_id);

/*参数说明：
@result         ：ajax返回的数据
@pay_type       :支付的方式
@pay_immed      :1: 立即支付， 0：非立即支付 2：代付
*/

    /*sz郑培强添加用于砍价活动--start*/
    // if(form_bargain_sz){
    //     result.price=form_bargain_sz;
    // }
    /*sz郑培强添加用于砍价活动--end*/
    /*sz郑培强添加用于众筹活动--start*/
    // if(form_crowdfund_sz){
    //     result.price=form_crowdfund_sz;
    // }
    /*sz郑培强添加用于众筹活动--end*/

    console.log(result);
    var status                  = result.status;
    var msg                     = result.msg;
    var price                   = result.price;
    var remark                  = result.remark;
    var batchcode               = result.batchcode;
    var batchcode_arr           = result.batchcode_arr;         //关联订单号
    var payother_desc_id        = result.payother_desc_id;
    is_collage_order        = result.is_collage_product;
    //
   if(status>1){
     alert_warning(msg);
       return false;
   }

    // console.log(result);return;

    //closeLoading();//关闭加载层
    //showAlertMsg("提示",msg,"知道了");
    //return;
    /*下单成功清除购物车对应产品开始*/
    if( fromtype == 2 ){
        var LS_arr = "";
        if(o_shop_id > 0){
            LS_arr = localStorage.getItem("cart_user_"+user_id+"_shop_"+o_shop_id);//购物车记录数据
        }else{
            LS_arr = localStorage.getItem("cart_"+user_id);//购物车记录数据
        }

        LS_arr          = eval(LS_arr);
        clean_cart      = eval(clean_cart);     //[["-1",["21158","","1","-1","","0","-1","-1"],"-1"]]
        var clean_len   = clean_cart.length;

        for( var i = 0; i < clean_len; i++ ){
            var shopid  = clean_cart[i][0];//提交数据的店铺id      //-1
            var p_arr   = clean_cart[i][1];//提交数据的产品数组      //["21158","","1","-1","","0","-1","-1"]
            var pid     = p_arr[0];
            var pos     = p_arr[1];

            if(LS_arr == null){
                LS_arr = [];
            }
            
            var LS_len  = LS_arr.length;
            var is_loop = true;
            for( var j = 0; j < LS_len; j++ ){
                var shop_por_arr    = LS_arr[j][1]; //购物车产品数组   //[["22113","1","","","0","-1","","1"],["22122","1","","","0","-1","","1"]]
                var por_arr_len     = shop_por_arr.length;
                for( var m = 0; m < por_arr_len; m++ ){             
                    var LS_pid  = shop_por_arr[m][0];//产品id
                    var LS_pos  = shop_por_arr[m][2];//产品属性
                    if( pid == LS_pid && pos == LS_pos ){
                        if( por_arr_len > 1 ){
                            LS_arr[j][1].splice(m,1);   //移除该产品
                            break;
                        }else{
                            LS_arr.splice(j,1); //移除该店铺
                            is_loop = false;
                            break;
                        }

                    }
                }
                if( !is_loop ){
                    break;
                }
            }
        }
        if(o_shop_id > 0){
            localStorage.setItem("cart_user_"+user_id+"_shop_"+o_shop_id, JSON.stringify(LS_arr));
        }else{
            localStorage.setItem("cart_"+user_id,JSON.stringify(LS_arr));
        }
        
        var timestamp = Date.parse(new Date());//获取当前时间戳
        timestamp = timestamp/1000;
        localStorage.setItem("cart_time_"+user_id,timestamp);//设置加入购物车时间
        uploadItem(LS_arr);//上传实时购物车数据
    }
    /*下单成功清除购物车对应产品结束*/

    /*下单清除订单页面的本地存储 start*/

    clear_local_Storage();


    /*下单清除订单页面的本地存储 end*/

    /*根据支付方式跳转开始*/
    //alert(price);
    //alert(result.payondelivery);
    if(status ==1 ){                //需要调用接口支付

        if( is_collage_product == 1 && group_buy_type == 2 ){
            history.replaceState({}, '', 'collageActivities/my_collages_record_list_view.php?customer_id' + customer_id_en);        //修改历史记录，支付后返回跳转到购物车
        } else {
            if(yundian_id > 0){
                history.replaceState({}, '', 'orderlist.php?customer_id' + customer_id_en + '&currtype=1&yundian='+yundian_id+''); 
            }else{
                history.replaceState({}, '', 'orderlist.php?customer_id' + customer_id_en + '&currtype=1');     //修改历史记录，支付后返回跳转到购物车
            }
        }

         //alert(result.payondelivery);
        if(result.payondelivery == 1)
        {
            //alert(333)
            //history.replaceState({}, '', 'orderlist_detail.php?customer_id' + customer_id_en + '&pay_batchcode='+batchcode);
            if(yundian_id > 0){
                document.location = 'orderlist_detail.php?customer_id' + customer_id_en + '&pay_batchcode='+batchcode+'&yundian='+yundian_id+'';
                return;
            }else{
                document.location = 'orderlist_detail.php?customer_id' + customer_id_en + '&pay_batchcode='+batchcode;
               return;
            }

        }

        closeLoading();
        /* showAlertMsg("提示",result.msg,"知道了",function(){
        showPayType(customer_id_en , industry_type , page_port , result.batchcode_arr ,result.price,user_id,batchcode);
       }); */

           var post_data = new Array(1);

           post_data['industry_type'] = 'shop';//行业类型 | crm 16242 - 2018-08-20
           
           post_data['batchcode_arr'] = result.batchcode_arr;//订单号集合
           if(result.batchcode_arr.length>1){
               post_data['is_merge'] = 1; //是和并支付
           }else{
               post_data['is_merge'] = 0;//不是和并支付
           }
           post_data['price'] = result.price;//支付金额
           post_data['user_id'] = user_id;
           post_data['pay_batchcode'] = batchcode;//支付订单号


           //判断是否支持找人代付
           var is_payother = 1;
           var is_payother_msg ="";
           //---找人到付不支持积分产品---//
           var sum_all_supply_pros_need_score = $('.sum_all_supply_pros_need_score').val();
           if(parseFloat(sum_all_supply_pros_need_score)>0){
               is_payother = 0;
               is_payother_msg = '找人代付不支付积分产品';
               $('.is_payother').val(0);
           }
           //---找人到付不支持积分产品---//


           //---找人代付不支持购买多种供应商产品---//
           var len =  $('.itemWrapper').length;
           console.log(len);
           if(len>2)
           {
               is_payother = 0;
               is_payother_msg = '找人代付不支持购买多种店铺产品';
               $('.is_payother').val(0);
           }
           //---找人代付不支持购买多种供应商产品---//


           //---找人代付不与其他优惠结算--//
           var temp_sum = is_curr + is_select_card + is_select_coupon;   //其中有一个开启则提示
           if( temp_sum >0 ){
               $('.is_payother').val(0);
               is_payother = 0;
               is_payother_msg = '找人代付不与其他优惠结算';
           }

           //---找人代付不与其他优惠结算---//
           post_data['is_payother'] = is_payother;
           post_data['is_payother_msg'] = is_payother_msg;

           post_data['yundian'] = yundian_id;

           var json = {};
           for( i in post_data ){
            json[i] = post_data[i];
           }
           var jsons = JSON.stringify(json);

           var post_data1 = new Array(1);
           post_data1['key'] = 'post_data';
           post_data1['val'] =  jsons;
           var post_object = [];
           post_object.push(post_data1);
           Turnpay_Post(post_object);


    }
}

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
            url: "/shop/index.php/Home/Cart/h5_cart_data2",
            data: {customer_id:customer_id,user_id:user_id,cart_data:cart_data,pro_arr:pro_arr,cart_time:cart_time},
            async: false,
            success: function (result) {
                
            }    
        });
        }
    }

/*POST提交数据*/
function Turnpay_Post(object,strurl){
    //object:需要创建post数据一对数组 [key:val]

    /* 将GET方法改为POST ----start---*/
    if( is_collage_product == 1 && group_buy_type == 2 ){
        var strurl = "../choose_paytype.php?customer_id="+customer_id_en;
    } else {
        var strurl = "choose_paytype.php?customer_id="+customer_id_en;
    }


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
    //alert(object);
     objform.submit();
    /* 将GET方法改为POST ----end---*/
}



function new_check_limit(){
        $.ajax({
        url: "limitbuy_class.php",
        dataType: 'json',
        type: 'post',
        data:{'customer_id':customer_id_en,'pid_str':allpid_str,'user_id':user_id,'pidcount_str':pidcount_str,'type':'2'},
        success:function(result){
            if(result.status == -1){
                showXiangouMsg(result.errmsg);
                return false;
            }
            if(result.limit == 1){
                showXiangouMsg('订单创建失败，商品列表中存在限购商品，您已购买，请重新下单，谢谢！','知道了');
                return false;
            }
            //if(type == 1){                //点击立即支付
            //      $.ajax({
            //          url: "ajax_show_payway.php",
            //          dataType: 'json',
            //          type: 'post',
            //          data:{'customer_id':customer_id_en,'industry_type':'shop',"page_port":page_port},
            //          success:function(result){
            //              var content="";
            //              if(result.errcode==0){
            //              content+='<div class="list-one popup-menu-title">';
            //              content+='<span class="sub">选择支付方式</span></div>';
            //              for(i=0;i<result.datalist.length;i++){
            //              content+='<div class="line"></div>';
            //              content+='<div class = "new_popup-menu-row" data-value="'+result.datalist[i].pay_type+'" onclick="new_popup(this);">';
            //              content+='<img src="'+result.datalist[i].icon+'">';
            //              content+='<div class="newdiv"><p class="newfont">'+result.datalist[i].pay_name+'</p>';
            //              content+='<p class="newzhifup">'+result.datalist[i].description+'</p></div>';
            //              content+='</div>';
            //              }
            //              content+='</div>';
            //          }
            //          $("#new_zhifuPannel").html(content);
            //          $(".am-dimmer").show();
            //          $("#new_zhifuPannel").fadeIn();
            //      }
            //  });
            //}else if(type == 2){      //点击支付方式
                new_subOrder(fromtype,1,"",new_jsonpCallback_saveorder);
            //}
        }
    });
}




//循环等待消息队列结果 等待60秒
var delay_i = 0;
var redis_reslt = '';
function redis_delay(result,pay_type,fromtype){

    redis_reslt = result;
    var batchcode   = '';
    var msg         = '';
    console.log(redis_reslt);
    if(redis_reslt!=null && redis_reslt!=''){
         batchcode  = redis_reslt.batchcode;
         msg        = redis_reslt.msg;
    }
    delay_i++;
    if(delay_i<60){
        //操作
        console.log(delay_i);
        var pass = check_redis_result(batchcode);
        console.log(pass);
        if(pass.code == 1 ){       //1等待中 2完成 3错误
                show_loading();
        }else if(pass.code == 2 ){ //2完成
                //hide_loading();
                new_jsonpCallback_saveorder(redis_reslt,pay_type,fromtype); //继续执行回调函数
                return false;
        }else if(pass.code == 3){   //3错误
                alert_warning('订单创建失败！');
                if( is_collage_product == 1 && group_buy_type == 2 ){
                    var url = "collageActivities/my_collages_record_list_view.php?customer_id="+customer_id_en;                            //跳转到我的订单，不停留在订单页面
                    history.replaceState({}, '', 'collageActivities/my_collages_record_list_view.php?customer_id' + customer_id_en);        //修改历史记录，支付后返回跳转到购物车
                } else {
                    var url = "orderlist.php?customer_id="+customer_id_en+"&currtype=1";                            //跳转到我的订单，不停留在订单页面
                    history.replaceState({}, '', 'orderlist.php?customer_id' + customer_id_en + '&currtype=1');     //修改历史记录，支付后返回跳转到购物车
                }

                setTimeout(function(){                  //最后跳转到支付
                    document.location = url;
                },1000);
                return false;
        }
        setTimeout(function(){ //继续
                redis_delay(result,pay_type,fromtype);
        },1000);
    }else{  //超出时间

    }
}


//查询消息列队是否完成
function check_redis_result(batchcode){
    var ajax_data ;
    $.ajax({
               url: "save_order_redis_mq_statu.php?batchcode="+batchcode,
               data:{
                   batchcode:batchcode,
                   },
               type: "POST",
               dataType:'json',
               async: false,      //true异步 false同步
               success:function(data){
               console.log(data);
                ajax_data =  data;
               },
               error:function(er){

               }
    });
    return ajax_data;
}

//显示等待中
function show_loading(){
    $('#loading').toggle();
}

function callback_exit(){
    return ;
}

function clearCookie(name) {    
    setCookie(name, "", -1);    
}  
function setCookie(cname, cvalue, exdays) {  
    var d = new Date();  
    d.setTime(d.getTime() + (exdays*24*60*60*1000));  
    var expires = "expires="+d.toUTCString();  
    document.cookie = cname + "=" + cvalue + "; " + expires;  
}  

/*
//隐藏等待中
function show_loading(){
    $('#loading').hide();
}
*/

//拼团说明
$('.collage_explain').click(function(){
    $('.explain').toggle(500);
})
$('.detBtn').click(function(){
    $('.explain').toggle(500);
})
/******************函数部分*********************/
