<?php

$link = mysql_connect(DB_HOST, DB_USER, DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
_mysql_query("SET NAMES UTF8");
require_once('../customer_id_decrypt.php');
require_once('../proxy_info.php');
require_once('../common/utility.php');
require_once('../common/common_ext.php');
require_once('../common/common_from.php');
require_once('select_skin.php');
require_once($_SERVER["DOCUMENT_ROOT"]."/weixinpl/common/common_ext.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/mshop/web/model/restricted_purchase.php");   //限购业务
require_once($_SERVER["DOCUMENT_ROOT"]."/weixinpl/function_model/collageActivities.php"); //拼团业务
require_once($_SERVER["DOCUMENT_ROOT"]."/weixinpl/mshop/pre_delivery.class.php"); //预配送类
require_once($_SERVER['DOCUMENT_ROOT'] . '/mshop/web/model/integral.php');  //积分业务

#1. 接收get/post参数
    $pid = (int)i2get("pid", -1);   //产品id
    $exp_user_id = i2get("exp_user_id", -1); //分享人user_id
    $o_shop_id = i2get("shop_id", -1);     //订货系统门店id
    $topic_id = i2get("topic_id", 0);   //商城直播传的话题id
    $resource_id = i2get("resource_id", 0);//商城直播传的资源id
    $pro_act_type = i2get("pro_act_type", -1);   //产品活动类型
    $flag_is_godefault = i2get("flag_is_godefault", 0); //单品页传过来的标识
    $is_collage_from = i2get("is_collage_from", 0); //是否来自拼团
    $limit_id = i2get("limit_id", 0);    //从限时活动传来的活动id
    $yundian = i2get("yundian",-2); //云店id：-1时为平台;大于0为云店 备注：云店中的平台产品参数为-2(即没有yundian参数)时，查SESSION
    $now_time = date("Y-m-d H:i:s");
    $user_id = $user_id ?: -1;
    if (!($pid > 0)){
        exit("产品id异常");
    }
    if($yundian == -2){
       $session_yundian = $_SESSION['yundian_'.$customer_id];//-1时平台产品;大于0时为云店产品
       if(empty($session_yundian)){
         $session_yundian = -1;
       }
       $yundian = $session_yundian;
    }
    $_SESSION['yundian_'.$customer_id] = $yundian;
    $PDM = new product_detail_model($customer_id, $user_id, $pid);    //产品详情逻辑层
    /*清除确认订单页面的session*/
    $_SESSION['bug_post_data_' . $user_id] = '';            //清除购物车数据
    $_SESSION['sendtime_id_' . $user_id] = '';            //清除送货时间
    $_SESSION['rtn_sendtime_array_' . $user_id] = '';
    $_SESSION['a_type_' . $user_id] = -1;                    //清除选择地址的session
    $_SESSION['diy_area_id_' . $user_id] = '';            //清除自定义区域
    $_SESSION['rtn_diy_area_array_' . $user_id] = '';
    $_SESSION['check_first_extend_' . $user_id] = '';
    $_SESSION['is_virtual_' . $user_id] = '';
    $_SESSION['is_collage_product_' . $user_id] = '';
    $_SESSION['is_check_password_' . $user_id] = '';            //清除密码输入记录
    $_SESSION['delivery_time_' . $user_id] = '';            //预配送时间
    $_SESSION['o_shop_id_' . $user_id] = '';          //订货系统门店id
    //郑培强添加用于众筹活动 砍价活动
    $_SESSION['form_bargain_sz']='';
    $_SESSION['form_bargain_sz_data']='';
    $_SESSION['form_crowdfund_sz']='';
    $_SESSION['form_crowdfund_sz_data']='';

    if($exp_user_id>0){
        $_SESSION['exp_user_id_' . $customer_id]=$exp_user_id;
    }
    /**
     * @var $linkurl 自身推广出去带自己的推广码
     * @var $new_baseurl 新商城图片显示
     */
    define("InviteUrl", Protocol . $_SERVER["HTTP_HOST"] . "/weixinpl/common_shop/jiushop/forward.php?type=2&customer_id=");
    $linkurl_new = InviteUrl . $customer_id_en . "&pid=" . $pid . "&exp_user_id=" . passport_encrypt((string)$user_id);//改名 2018-3-14 因下面代码linkurl变量重复
    $new_baseurl = Protocol . $_SERVER["HTTP_HOST"]; //新商城图片显示

#2. 检查是否货到付款、积分产品
    $result = $PDM->check_pay_delivery($pid); //判断是否是货到付款产品
    $payondelivery_id = $result["id"];
    //自动查找产品是否是积分活动产品
    $result = $PDM->integral_data($pro_act_type);
    $shop_int_name       = $result["shop_int_name"];
    $store_int_name      = $result["store_int_name"];
    $integrl_start_time  = $result["istart_time"];
    $integrl_end_time    = $result["iend_time"];
    $integral_result     = $result["integral_result"];
    $integral_act_type   = $result["integral_act_type"];
    $pro_act_type        = $result["pro_act_type"] ?: $pro_act_type;
    $int_act_id          = $result["pro_act_id"] ?: -1;
    $shop_integral_onoff = $result["shop_integral_onoff"];
    $store_integral_onoff= $result["store_integral_onoff"];
    $is_coupon  = $result["is_coupon"]; //是否开启优惠券 1：开启 0:关闭

#3. 获取商城设置
    $result = $PDM->commonshop_setting();   //商城设置
    extract($result);
    $issell = $result["issell"];    //是否开启分销
    $pro_card_level = $result["pro_card_level"];    //购买产品需要会员卡开关
    $shop_card_id = $result["shop_card_id"];    //分销会员卡
    $is_showdiscuss = $result["is_showdiscuss"];
    $isOpenSales = $result["isOpenSales"];
    $advisory_flag = $result["advisory_flag"];
    $brand_supply_name = $result["name"];
    $advisory_telephone = $result["advisory_telephone"];
    $init_reward = $result["init_reward"];
    $issell_model = $result["issell_model"];
    $is_identity = $result["is_identity"];
    $isshowdiscount = $result["isshowdiscount"];
    $shop_id = $result["shop_id"];               //微商城id唯一标识
    $is_godefault = $result["is_godefault"];    //是否开启单品页
    $PDM->godefault($is_godefault,$flag_is_godefault,$exp_user_id, $is_collage_from,$pro_act_type);  //TODO：积分产品/是否单品页
    /**
     * @var $is_pay_on_delivery 货到付款是否开启
     * @var $is_openOrderMessage 商城订单提示开关
     * @var $barcodes
     * @var $commonshop_logo
     *
     * 佣金计算器
     * @var $is_commission_cal 佣金计算器显示开关
     * @var $shop_jump_type 进入店铺跳转类型：0：店铺首页；1：店铺全部产品
     *
     * 图片是否自动轮播
     * @var $is_product_shuf 是否自动轮播 0否，1是
     * @var $is_show_original 是否显示原价 0否，1是
     * @var $is_stockOut 是否库存不足自动下架
     * @var $is_buy_sale_model 是否是买卖模式，0：默认模式，1：买卖模式
     */
    $result = $PDM->extend_data(); //商城额外参数
    extract($result);
    $is_commission_cal = $result["is_commission_cal"];
    $commission_cal_content = $result["commission_cal_content"] ?: "查看可赚佣金";
    $is_product_shuf = $result["is_product_shuf"];
    $is_show_original = $result["is_show_original"];
    $is_stockOut = $result["is_stockOut"];
    $is_buy_sale_model = $result["product_detail_model"];
    $barcodes = $result["barcodes"];
    $commonshop_logo = $result["logo"];


#4. 获取产品详情
    /**
     * 初始化--star
     * @var $is_supply_id 供应商ID
     * @var $pro_reward 产品分佣比例
     * @var $isvp 是否为vp产品
     * @var $is_QR 是否二维码产品 0:否 1:是
     * @var $isout 上架下架, 1:下架 0:上架
     * @var $name 产品名
     * @var $weight 产品重量
     * @var $unit 单位
     * @var $type_id 所以分类id
     * @var $issnapup 是否抢购，1：是，0：否
     * @var $vp_score 单个vp值
     * @var $introduce 简短介绍
     * @var $meu_level 中评数
     * @var $bad_level 差评数
     * @var $sell_count 销售量
     * @var $is_virtual 虚拟产品
     * @var $good_level 好评数
     * @var $freight_id 运费模板ID
     * @var $p_storenum 库存
     * @var $is_invoice 是否开启发票
     * @var $propertyids 属性id
     * @var $for_price 成本价
     * @var $cost_price 供货价
     * @var $p_now_price 现价
     * @var $description 详细介绍
     * @var $p_need_score 购买产品需要的积分
     * @var $pis_identity 产品是否需要身份证购买开关
     * @var $p_orgin_price 原价
     * @var $donation_rate 单品捐赠比率
     * @var $is_currency 是否购物币产品，0：不是 1：是
     * @var $back_currency 购物币返佣金额
     * @var $pro_card_level 购买产品需要会员卡开关
     * @var $nowprice_title 现价的自定义名称
     * @var $specifications 产品规格
     * @var $default_imgurl 封面图片
     * @var $show_sell_count 虚拟销售量
     * @var $is_free_shipping 是否包邮，1是，0否
     * @var $customer_service 售后保障
     * @var $pro_card_level_id 购买产品需要会员卡等级
     * @var $is_guess_you_like 是否是猜您喜欢开关：0否1是
     * @var $define_share_image 自定义分先图片 产品分享图片
     * @var $buystart_time 商品抢购开始时间
     * @var $countdown_time 商品抢购倒计时(结束时间)
     * @var $product_voice 商品语音链接
     * @var $product_vedio 商品视频链接
     * @var $shop_card_id 分销会员卡
     * @var $brand_supply_name 品牌供应商店铺名
     * @var $brand_tel 品牌供应商店铺电话
     * @var $brand_logo 品牌供应商店铺logo
     * @var $advisory_flag 咨询电话开关
     * @var $is_showdiscuss 评论开关
     * @var $isOpenSales 商城产品销量开关
     * @var $init_reward 商城分佣比例
     * @var $cashback_r 返现金额（产品价格按比例）
     * @var $cashback 返现金额（固定金额）
     * @var $isvalid
     * @var $issell 是否开启分销
     * @var $issell_model 是否开启分销
     * @var $tax_type 1普通产品，2跨境零售，3直邮 ..
     * @var $islimit 是否限购 0否，1是
     * @var $limit_num 限购数量
     * @var $is_first_extend 是否首次推广奖励产品
     * @var $privilege_level 特权专区等级 0:粉丝；1：推广员；2：青铜(代理)；3：白银(渠道)；4：黄金(总代)；5：白金(股东)
     * @var $link_coupons 是否关联优惠券
     * @var $delivery_name 预配送活动名称
     * @var $delivery_id 预配送时间设置id
     * @var $delivery_time 配送时间段
     * @var $earliest_hour 最早配送时间所加的小时
     * @var $latest_hour 最晚配送时间所加的小时
     * @var $custom_date 自定义日期
     * @var $delivery_limit 自选配送限制，0：早晚时间设置，1：自定义日期
     * @var $pre_delivery_time 预配送数据
     * @var $is_restricted 是否正在参与限购活动
     * @var $remark 备注
     * @var $short_introduce_color 简短介绍颜色
     * @var $currency_price 可抵扣购物币金额
     * @var $$imgUrl 分享图标
     * @var $pro_discount 折扣率
     * @var $pro_yundian_id 云店店主id * 
     */

    $query = "SELECT id,isvp,pro_reward,isvalid,name,is_QR,weight,type_id,cashback,cashback_r,cost_price,for_price,vp_score,issnapup,storenum,meu_level,bad_level,now_price,introduce,short_introduce_color,remark,is_invoice,is_virtual,freight_id,unit,isout,sell_count,need_score,good_level,orgin_price,description,propertyids,is_identity,is_supply_id,donation_rate,is_currency,back_currency,product_vedio,product_voice,buystart_time,default_imgurl,nowprice_title,countdown_time,specifications,show_sell_count,customer_service,is_free_shipping,pro_card_level_id,is_guess_you_like,define_share_image,tax_type,islimit,limit_num,is_first_extend,link_package,link_package_img,privilege_level,link_coupons,yundian_id FROM weixin_commonshop_products WHERE id='{$pid}'";
    $res = _mysql_query($query);
    $result = array();
    while ($row = mysql_fetch_object($res)) {
        $result[] = $row;
    }
    foreach ($result as $row) {
        $isvalid = $row->isvalid;
        $pro_reward = $row->pro_reward;
        $is_supply_id = $row->is_supply_id;
        $isvp = $row->isvp;
        $isout = $row->isout;
        $for_price = $row->for_price;
        $cost_price = $row->cost_price;
        $is_QR = $row->is_QR;
        $name = $row->name;
        $unit = $row->unit;
        $cashback = $row->cashback;
        $cashback_r = $row->cashback_r;
        $weight = $row->weight;
        $meu_level = $row->meu_level;
        $bad_level = $row->bad_level;
        $good_level = $row->good_level;
        $introduce = $row->introduce;
        $short_introduce_color = $row->short_introduce_color;
        $remark = $row->remark;
        $p_storenum = $row->storenum;
        $sell_count = $row->sell_count;
        $p_need_score = $row->need_score;
        $p_now_price = $row->now_price;
        $description = $row->description;
        $propertyids = $row->propertyids;
        $p_orgin_price = $row->orgin_price;
        $default_imgurl = $row->default_imgurl;
        $show_sell_count = $row->show_sell_count;
        $define_share_image = $row->define_share_image;
        $nowprice_title = $row->nowprice_title;
        $pro_card_level_id = $row->pro_card_level_id;
        $pis_identity = $row->is_identity;
        $donation_rate = $row->donation_rate;
        $is_invoice = $row->is_invoice;
        $vp_score = $row->vp_score;
        $freight_id = $row->freight_id;
        $is_virtual = $row->is_virtual;
        $type_id = $row->type_id;
        $specifications = $row->specifications;
        $customer_service = $row->customer_service;
        $is_free_shipping = $row->is_free_shipping;
        $is_currency = $row->is_currency;
        $back_currency = $row->back_currency;
        $issnapup = $row->issnapup;
        $buystart_time = $row->buystart_time;
        $countdown_time = $row->countdown_time;
        $product_voice = $row->product_voice;
        $product_vedio = $row->product_vedio;
        $is_guess_you_like = $row->is_guess_you_like;
        $tax_type = $row->tax_type;
        $islimit = $row->islimit;
        $limit_num = $row->limit_num;
        $is_first_extend = $row->is_first_extend;
        $privilege_level = $row->privilege_level;
        $link_package = $row->link_package;
        $link_package_img = substr($link_package_img, 6, strlen($row->link_package_img));
        $link_coupons = $row->link_coupons;
        $pro_yundian_id = $row->yundian_id;
        switch ($tax_type) {
            case '1':$tax_name = "普通产品";break;
            case '2':$tax_name = "跨境零售";break;
            case '3':$tax_name = "国内代发";break;
            case '4':$tax_name = "海外集货";break;
            case '5':$tax_name = "海外直邮";break;
            default:;
        }

        $prodcut_name = $name;

        //简短介绍替换换行
        $introduce = str_replace("\r", "", $introduce);
        $introduce = str_replace("\n", "", $introduce);

        $result = $PDM->pre_delivery();   //查询是否预配送产品
        $delivery_id = $result["delivery_id"];
        $delivery_name = $result["delivery_name"];
        $pre_delivery_time = $result["pre_delivery_time"];

        //备注json转数组
        $remark = json_decode($remark);
        $remark_color = $remark[0] -> color;
        $remarks = $remark[0] -> concent;
        $total_sales = $sell_count + $show_sell_count; //虚拟销售量+销售量
        $description = $PDM->lazy_load_replace($description);
    }

    //hzq
    $now_prices = $p_now_price;                         //用于防止限购活动改价

    //备注李庆伟添加
    //3d素材
    $query_threed = 'SELECT imgurl,3d_link as threed_link  FROM weixin_commonshop_product_imgs where product_id='.$pid.' and isvalid=1';
    $result_threed = _mysql_query($query_threed) or die('Query_threed failed: ' . mysql_error());
    while ($row = mysql_fetch_object($result_threed)) {
       $imgurl = $row->imgurl;
       $d_link = $row->threed_link;
       $new_imgurl[]['imgurl'] = $row->imgurl;
    }
    //3d素材标签
    $query_tag = 'SELECT is_open,is_show_tag  FROM '.WSY_PROD.'.3d_model_setting where customer_id='.$customer_id;
    $result_tag = _mysql_query($query_tag) or die('Query_tag failed: ' . mysql_error());
    $is_open_threed =0;
    $is_show_tag_threed =0;
    while ($row = mysql_fetch_object($result_tag)) {
        $is_open_threed = $row->is_open;
        $is_show_tag_threed = $row->is_show_tag;
    }
    //3d素材标签结束 李庆伟添加结束
    $imgUrl = $default_imgurl ? $new_baseurl . $default_imgurl : $pp_imgurl; // 分享图标


#5. 获取产品相关活动
    //vp值  start
    $vp_val = 0;
    $vp_query = "select my_vpscore from weixin_user_vp where isvalid = true and customer_id = ".$customer_id." and user_id = ".$user_id;
    $vp_result= _mysql_query($vp_query) or die('Query failed vp: ' . mysql_error());
    while( $vp_row = mysql_fetch_object($vp_result) ){
        $vp_val = $vp_row->my_vpscore;
        if($vp_val==NULL){
            $vp_val = 0;
        }
    }

    /**
     * 新的限购活动 start
     * @var $restricted_isout 参与限购 0:待发布 1:根据开始时间与活动时间进行判断，(已发布，进行中，已结束) 2:中止
     * @var $restricted_price 活动价格
     * @var $purchase_times 限购次数 -1为不限
     * @var $quantity_purchased 限购数量 -1时则不限
     * @var $buy_times 已购买次数
     * @var $buy_quantity 已购买总数
     * @var $restricted_id WSY_SHOP.weixin_commonshop_restricted_purchase表id
     * @var $restricted_product_id WSY_SHOP.weixin_commonshop_restricted_purchase_products表id
     * @var $restricted_result 限购活动信息
     * @var $is_display_time 是否显示倒计时
     * @var $display_time_count_down 倒数显示
     * @var $display_time_range 范围显示
     * @var $pro_discount 折扣率
     */
    $result = $PDM->restricted_purchase($limit_id);
    $restricted_isout = $result["restricted_isout"];
    $restricted_price = $result["restricted_price"];
    $purchase_times = $result["purchase_times"];
    $quantity_purchased = $result["quantity_purchased"];
    $buy_times = $result["buy_times"];
    $buy_quantity = $result["buy_quantity"];
    $restricted_id = $result["restricted_id"];
    $restricted_product_id = $result["restricted_product_id"];
    $restricted_result = $result["restricted_result"];
    $is_display_time = $result["is_display_time"];
    $display_time_count_down = $result["display_time_count_down"];
    $display_time_range = $result["display_time_range"];
    $is_restricted           = $result["is_restricted"] ?: 0;
    $issnapup                = $result["issnapup"] ?: $issnapup;
    $p_now_price             = $result["p_now_price"] ?: $p_now_price;
    $show_now_price          = $result["show_now_price"] ?: $p_now_price;
    $istart_time             = $result["buystart_time"] ?: $buystart_time;
    $iend_time               = $result["countdown_time"] ?: $countdown_time;
    $pro_discount            = round((($p_now_price / $p_orgin_price) * 10), 1);    //折扣率

    if($restricted_isout != 0 && $restricted_id != -1)
    {
        $pro_act_type = 31;
    }   


    $currency_price = $PDM->currency_percentage($p_now_price);  //查找购物币抵扣比例
    /**
     * 拼团数据 start
     * 产品活动优先级
     * 当产品要参与某个活动的时候，要把其他活动结束掉
     * @var $is_collage_product 参与拼团 0否 1是
     * @var $integral_type 参与积分 0否 1是
     * @var $restricted_isout 参与限购 0否 1是
     * @var $singlePrice 统一单独购买价格
     * @var $groupPrice 团购价
     * @var $groupStock 团库存
     * @var $explain
     * @var $groups
     * @var $linkurl
     * @var $is_check_priority 是否要进行活动优先级判断 0：不要 1：要
     * @var $active_countdown 开始时间倒数
     * @var $pro_act_id
     **/
    $result = $PDM->collage_data($is_collage_from);  //拼团暂时还是入口控制
    extract($result);
    $integral_type = $result["integral_type"];
    //$restricted_isout = $result["restricted_isout"];//collage_data方法中根本就没有返回restricted_isout，导致restricted_isout为空！
    $collage_product_info = $result["collage_product_info"];
    $explain = $result["explain"] ?: [];
    $groups = $result["groups"];
    $linkurl = $linkurl_new . $result["linkurl"];
    $is_collage_product = $result["is_collage_product"] ?: 0;
    $singlePrice = $result["singlePrice"];
    $groupPrice = $result["groupPrice"] ?: 0;
    $groupStock = $result["groupStock"] ?: 0;

    $is_check_priority = 1;    //是否要进行活动优先级判断 0：不要 1：要
    $active_countdown = 0;    //开始时间倒数
    //如果参与拼团,拼团自己带参数进来玩，优先级最高，只玩拼团，其他活动全部结束掉，并且不执行优先级比较
    if ($is_collage_product) {
        $integral_type = 0;    //结束积分活动
        $restricted_isout = 0;    //结束限购
        $is_check_priority = 0;    //活动专区进来，不执行优先级比较
    } else {
        $result = $PDM->check_priority($pro_act_type,$restricted_isout,$integral_act_type,$restricted_id,$int_act_id);    //
        if ($result["restricted_isout"] || $result["restricted_isout"] == 0){
            $restricted_isout = $result["restricted_isout"];
        }
        if ($result["is_check_priority"] || $result["is_check_priority"] == 0){
            $is_check_priority = $result["is_check_priority"];
        }

        if ($result["pro_act_id"] || $result["pro_act_id"] == 0){
            $pro_act_id = $result["pro_act_id"];
        }
        if ($result["active_countdown"] || $result["active_countdown"] == 0){
            $active_countdown = $result["active_countdown"];
        }
        if ($result["is_collage_product"] || $result["is_collage_product"] == 0){
            $is_collage_product = $result["is_collage_product"];
        }
        if ($result["pro_act_type"] || $result["pro_act_type"] == 0){
            $pro_act_type = $result["pro_act_type"];
        }
        if ($result["integral_type"] || $result["integral_type"] == 0){
            $integral_type = $result["integral_type"];
        }
    }

    //hzq add
    if($restricted_isout == 0){
        $show_now_price = $now_prices;
    }
    // var_dump($pro_act_id);

    /*** 产品活动优先级 ***/

    $result = $PDM->renewal_product();  //TODO：查询是否续费活动产品
    $renewal_id = $result["renewal_id"];
    $renewal_time = $result["renewal_time"];


#5. 获取产品评论、属性、用户身份
    //----根据评论表查询评论数 start
    $evaluate_count = $PDM->evaluate_count();   //TODO：查询评论数
    $good_level = $evaluate_count["good_level"];
    $meu_level = $evaluate_count["meu_level"];
    $bad_level = $evaluate_count["bad_level"];
    $total_evaluate = $evaluate_count['total_evaluate'];
    //----根据评论表查询评论数 end


    /**
     * 判断当前身份是否可以购买
     * @var $is_allow_buy 0:不可购买，1：可购买
     */
    $result = $PDM->check_promoters($privilege_level);   //TODO:判断当前身份
    $is_allow_buy = $result["is_allow_buy"];
    $Plevel = $result["commision_level"];
    $is_consume = $result["is_consume"];
    $is_promoters = $result["is_promoters"];

    $result = $PDM->storenum_count($o_shop_id, $pro_act_type,$pro_act_id);  //TODO:计算属性的库存
    $p_storenum = $result["p_storenum"] ?: $p_storenum;
    $total_sales = $result["total_sales"] ?: $total_sales;
    // var_dump($p_storenum);

    $check_first_extend = $PDM->promoter_reward($exp_user_id,$is_first_extend);   //检测是否符合首次推广奖励

    /*属性开始*/
    $proLst = new ArrayList();
    $propertyarr = explode("_", $propertyids);
    $pcount = count($propertyarr);
    for ($i = 0; $i < $pcount; $i++) {
        $property_id = $propertyarr[$i];
        $proLst -> Add($property_id);
    }
    $default_pids = "";
    $proHash = new HashTable();
    /*属性结束*/

    $result = $PDM->charitable($donation_rate,$p_now_price);    //慈善公益
    $is_charitable = $result["is_charitable"];  //慈善开关
    $charitable_price = $result["charitable_price"];

    /*返现金额开始*/
    require_once('../common/own_data.php');
    $info = new my_data();//own_data.php my_data类
    $showAndCashback = $info -> showCashback($customer_id, $user_id, $cashback, $cashback_r, $p_now_price);

    /**
     * 判断产品是否为品牌供应商产品 start
     * @var $isbrand_supply 是否品牌供应商 1:是品牌供应商，0不是
     * @var $pro_num 品牌供应商店铺产品总数
     * @var $collect_num 品牌供应商收藏总数
     * @var $comment_num 品牌供应商评论总数
     * @var $is_admin_closed 平台是否开启商家店铺
     * @var $is_owner_closed 供应商是否开启商家店铺
     */

    $result = $PDM->supply_data($is_supply_id,$commonshop_logo);
	if($is_supply_id > 0){ //修复报障ID 14603 lml 20180522
		$isbrand_supply = $result["isbrand_supply"];
		$advisory_telephone = $result["advisory_telephone"];
		$advisory_flag = $result["advisory_flag"];
		$is_admin_closed = $result["is_admin_closed"];
		$is_owner_closed = $result["is_owner_closed"];
		$brand_supply_name = $result["brand_supply_name"];
		$collect_num = $result["collect_num"];
		$comment_num = $result["comment_num"];
		$pro_num = $result["pro_num"];
        //品牌供应商收藏总数加上虚拟粉丝数 crm 15589
        $fans_nums_sql = "select virtual_fans_flag,virtual_fans_nums from weixin_commonshop_applysupplys where isvalid=true and user_id='".$is_supply_id."'";
        $fans_nums_res = mysql_find($fans_nums_sql);
        if ($fans_nums_res['virtual_fans_flag'] == 1) {
            $collect_num = (float)$collect_num+(float)$fans_nums_res['virtual_fans_nums'];
        }
	}
	$brand_logo = $result["brand_logo"];

    /**
     * 属性数据
     * $
     */
    $result = $PDM->prodis_data($propertyids,$default_imgurl);
    //extract($result);
    $is_wholesale = $result["is_wholesale"];
    $proids_arr = $result["proids_arr"];
    $attr_parent_id = $result["attr_parent_id"];
    $attr_img_str = $result["attr_img_str"];
    $attr_img_array = $result["attr_img_array"];

    /*判断产品是否为品牌供应商产品结束*/
    $visit = new CommonUtiliy();
    $visit->user_visit_pro($pid, $user_id, $customer_id, 1);//产品足迹方法

    $result = $PDM->currency_setting(); //查询是否开启购物币抵扣
    $isOpenCurrency = $result["isOpen"] ?: 0;
    $custom = $result["custom"];


#6. 获取产品相关优惠券、用户积分
    /**
     * 优惠券
     * $coupon_id       优惠券ID
     * $coupon_title    优惠券标题
     * $NeedMoney       优惠券使用限额
     * $MaxMoney        优惠券优惠金额
     * $CanGetNum       每天可领取数量
     * $user_scene      优惠券类型
     * $MoneyType       领取金额类型
     * $Days            截止使用天数
     * $DaysType        截止时间类型 0:截止天数,1:截止日期
     */
    $result = $PDM->coupon_data($link_coupons);
    $coupon_array = $result["coupon_array"];
    $result_coupon_num = $result["result_coupon_num"];
    $result = $PDM->check_buy_count();  // 获取当天当前用户当前商品购买数量
    $count_commodity = $result["count"];

    //用户积分
    $result = $PDM->user_integral();
    $user_integral = $result["integral"];
    $user_store_integral = $result["store_integral"];

    $data = array(
        "pro_act_type" => $pro_act_type,
        "restricted_isout" => $restricted_isout,
        "restricted_price" => $restricted_price,
        "is_restricted" => $is_restricted,
        "o_shop_id" => $o_shop_id
    );
    $proids_data = $PDM->product_data($data);
    $aog_area = $PDM->aog_area();   //获取所有四级区域
    $proimg_data = $PDM->proimg_data(); //获取产品图片数据
    $aog_data = $PDM->aog_data();   //预配送数据
    $is_aog = $aog_data["is_aog"];
    if ($is_aog){
        $is_available = $aog_data["is_available"];
        $aog_p = $aog_data["aog_p"];
        $aog_c = $aog_data["aog_c"];
        $aog_a = $aog_data["aog_a"];
        $aog_d = $aog_data["aog_d"];
        $aog_date = $aog_data["aog_date"];
        $is_available_str = $aog_data["is_available_str"];
    }
    //获取属性父ID链
    $p_pro_str = '';
    if( !empty($proids_data[0]['old_proids']) ){
        $p_pro_str = $PDM->get_pros_index($proids_data[0]['old_proids']);

    }

    $oof_before = OOF_P != 2 ? OOF_S : "";
    $oof_after = OOF_P == 2 ? OOF_S : "";


#7. 其它
    //小能客服系统
    $xiaoneng_id = $is_supply_id;
    require_once('supply_chat.php');    //供应商客服方法
    //检查用户从哪里进  0:网页 1:微信 2:APP 3:支付宝
    $CF = new check_from();
    $from_type = $CF->check_where($customer_id);
    //如果是自营产品判断云店是否过期
    $yundian_isvalid = true;
    $yundian_name = '未知商家';
    if($yundian == $pro_yundian_id && $yundian != -1){
        $yundian_msg = $PDM->yundian_isvalid($yundian);
        if(!$yundian_msg){
            $yundian_isvalid = false;
        }else{
            if(strtotime($yundian_msg['expire_time']) < time()){
                $yundian_isvalid = false;
                $yundian_name = $yundian_msg['weixin_name'];
            }else{
                $yundian_isvalid = true;
            }
        }
        $is_buy_sale_model = 0;
    }
    //防止查看 其他云店 的自营产品
    if($yundian != $pro_yundian_id && $pro_yundian_id > 0){
        exit("云店没有该自营产品");
    }
    $linkurl .= "&yundian=".$yundian;//分享链接带上云店参数 -1 平台 其他 云店
    require('./product_detail_template.html');
    class product_detail_model{
        public $customer_id;
        public $customer_id_en;
        public $user_id;
        public $pid;
        public $now_time;
        private $debug;
        private $debug_type;

        public function __construct($customer_id,$user_id,$pid){
            $this->customer_id = (int)passport_decrypt((string)$customer_id);
            $this->customer_id_en = passport_encrypt(passport_decrypt($customer_id));
            $this->user_id = $user_id;
            $this->pid = $pid;
            $this->now_time = date("Y-m-d H:i:s");
            $this->debug = true;
            $this->debug_type = "log";
        }

        /*
		 * 懒加载图片正则替换
		 */
		public function lazy_load_replace($html = '') {
			//去除img标签原有的class属性
			$html = preg_replace ( "/<img(.*)class=([\'\"])(.*)([\'\"])(.*)(\/?>)/isU", "<img\\1\\5\\6", $html );
			//如果img标签中含有data-original属性则去掉
			$html = preg_replace ( "/<img(.*)data-original=([\'\"])(.*)([\'\"])(.*)(\/?>)/isU", "<img\\1\\5\\6", $html );
			//将原有src换成同一张图片，然后真实图片地址放在data-original属性中，并且每个img标签加上lazy这个class，标记为懒加载图片
			$html = preg_replace ( "/<img(.*) src=([\'\"])(.*)([\'\"])(.*)(\/?>)/isU", '<img\\1 src="/weixinpl/mshop/images/loading.gif" data-original="\\3" class="lazy"\\5\\6', $html );
			return $html;
		}

		
        /**
         * 服务器端 console
         * @param string $log_content
         * @param string $log_level
         */
        public function console_php($log_content = "",$log_level = "DEBUG"){
            include_once ($_SERVER['DOCUMENT_ROOT'].'/weixinpl/common/ChromePhp.php');
            $debugInfo = debug_backtrace();
            \ChromePhp::group("{$this->func} --- LINE:{$debugInfo[0]['line']}");
            \ChromePhp::warn("{$log_level} --- LINE:{$debugInfo[0]['line']} --- func:{$debugInfo[1]['function']}");
            \ChromePhp::log($log_content);
            \ChromePhp::groupEnd();
        }



        public function is_debugger($log_name = "product_detail",$hax_time = 1,$is_clean = FILE_APPEND){
            if ($this->debug == true){
                $debugInfo = debug_backtrace();
                $line = $debugInfo[1]['line'];
                $log_content = "function: ".$debugInfo[1]['function'] . "\n variable: " .json_encode($debugInfo[1]['args']);
                if ($this->debug_type == "log"){
                    $log_name = "{$log_name}_".date("Ymd").".log";
                    $log_time = $hax_time ? "\n--- LINE:{$line} ----- ".date("Y-m-d H:i:s")." ----- URL:{$_SERVER['PHP_SELF']} ---------\n" : "";
                    _file_put_contents($log_name,"{$log_time}{$log_content}\n",$is_clean);
                }else{
                    $this->console_js($log_content);
                }
            }
        }
        //日志记录 - 方法版v3
        public function log_insert($log_name,$log_content,$hax_time = 1,$is_clean = FILE_APPEND){
            $debugInfo = debug_backtrace();
            $line = $debugInfo[0]['line'];
            $log_name = "{$log_name}_".date("Ymd").".log";
            $log_time = $hax_time ? "\n--- LINE:{$line} ----- ".date("Y-m-d H:i:s")." ----- URL:{$_SERVER['PHP_SELF']} ---------\n" : "";
            _file_put_contents($log_name,"{$log_time}{$log_content}\n",$is_clean);
        }

        public function console_js($log = "",$is_array = 0){
            if ($is_array){
                $log = json_encode($log);
                echo '<script>console.log(JSON.parse(\''.$log.'\'));</script>';
            }else{
                echo '<script>console.log(\''.$log.'\');</script>';
            }

        }

        //数组 转 对象
        function array_to_object($arr) {
            if (gettype($arr) != 'array') {
                return;
            }
            foreach ($arr as $k => $v) {
                if (gettype($v) == 'array' || getType($v) == 'object') {
                    $arr[$k] = (object)array_to_object($v);
                }
            }
            return (object)$arr;
        }

        //对象 转 数组
        function object_to_array($obj) {
            $obj = (array)$obj;
            foreach ($obj as $k => $v) {
                if (gettype($v) == 'resource') {
                    return;
                }
                if (gettype($v) == 'object' || gettype($v) == 'array') {
                    $obj[$k] = (array)object_to_array($v);
                }
            }
            return $obj;
        }

        /**
         * 判断当前身份是否可以购买，判断身份，查询是否推广员，是否拥有股东身份
         * @param $privilege_level  特权专区等级 0:粉丝；1：推广员；2：青铜(代理)；3：白银(渠道)；4：黄金(总代)；5：白金(股东)
         * @return array
         */
        public function check_promoters($privilege_level){
            $this->is_debugger();
            $user_level = -1;    //-1:粉丝,0:全部
            $Plevel = -1;
            $is_consume = -1;
            $is_promoters = -1;
            if ($this->user_id > 0) {
                //查询是否推广员，是否拥有股东身份
                $query = "SELECT id,is_consume,commision_level FROM promoters WHERE isvalid=true AND customer_id = '{$this->customer_id}' AND user_id = '{$this->user_id}' AND status = 1 LIMIT 1";
                $result = mysql_find($query);
                if ($result){
                    if ($result['is_consume'] > 0){   //股东
                        $user_level = $result['is_consume'] + 1;
                    }elseif ($result['is_consume'] <= 0){ //普通推广员
                        $user_level = 1;
                    }
                    $is_promoters = 1;
                }
                $Plevel = $result["commision_level"];
                $is_consume = $result["is_consume"];
            }


            $is_allow_buy = 0;//0:不可购买，1：可购买
            if ($privilege_level != "" || $privilege_level != NULL) {
                $info = explode("_", $privilege_level);
                if (in_array($user_level, $info)) {
                    $is_allow_buy = 1;
                }
            } else {
                $is_allow_buy = 1;
            }
            $result["is_allow_buy"] = $is_allow_buy;
            $result["Plevel"] = $Plevel;
            $result["is_consume"] = $is_consume;
            $result["is_promoters"] = $is_promoters;
            return $result;
        }

        /**
         * 判断是否是货到付款产品
         * @return array
         */
        public function check_pay_delivery(){
            $this->is_debugger();
            $sql = "select id from pay_on_delivery_products_t where pid = '{$this->pid}' and isvalid = 1 and customer_id = '{$this->customer_id}'";
            $result = mysql_find($sql);
            return $result;
        }

        /**
         * 查询是否预配送产品
         * @return mixed
         */
        public function pre_delivery(){
            $this->is_debugger();
            $result["delivery_id"] = -1;
            $result["delivery_name"] = '配送时间';
            $result["pre_delivery_time"] = [];
            $query_delivery = "SELECT p.delivery_id, a.delivery_name FROM weixin_commonshop_pre_delivery_product_relation p
                               INNER JOIN weixin_commonshop_pre_delivery a ON p.delivery_id=a.id
                               WHERE p.pid='{$this->pid}'
                               AND p.isvalid=TRUE
                               AND p.customer_id='{$this->customer_id}'
                               AND a.isvalid=TRUE";
            $result_delivery = mysql_find($query_delivery);
            if ($result_delivery){
                $result["delivery_id"] = $result_delivery["delivery_id"];
                $result["delivery_name"] = $result_delivery["delivery_name"];
            }

            if ($result["delivery_id"] > 0) {
                $preDeliveryTime = new preDeliveryTime($this->customer_id);
                $result["pre_delivery_time"] = $preDeliveryTime -> getDeliveryTime($result["delivery_id"]);
                ksort($result["pre_delivery_time"]['delivery_time']);    //日期排序
            }
            return $result;
        }

        /**
         * 商城设置
         * @return array
         */
        public function commonshop_setting(){
            $this->is_debugger();
            $query = "select id,is_godefault,is_coupon,is_cashback,name,init_reward,issell_model,advisory_telephone,advisory_flag,pro_card_level,shop_card_id,isshowdiscount,is_identity,is_showdiscuss,isOpenSales,issell from weixin_commonshops where isvalid=true and customer_id='{$this->customer_id}'";
            $result = mysql_find($query);
            return $result;
        }

        /**
         * 是否单品页
         * @param $is_godefault
         * @param $flag_is_godefault 单品页传过来的标识
         * @param $exp_user_id
         * @param $is_collage_from 是否来自拼团
         * @param $integral_type 积分活动类型
         */
        public function godefault($is_godefault,$flag_is_godefault,$exp_user_id,$is_collage_from,$integral_type){
            $this->is_debugger();
            /**
             * @var $is_godefault 是否单品页
             * @var $is_coupon 是否开启优惠券 1：开启 0:关闭
             * @var $is_cashback 是否开启消费奖励
             */
            if ($is_godefault == 1 && $flag_is_godefault == 0) { //进去单品页
                header("Location: ../common_shop/jiushop/detail_default.php?pid={$this->pid}&customer_id={$this->customer_id_en}&exp_user_id={$exp_user_id}&is_collage_from={$is_collage_from}&pro_act_type={$integral_type}");
                exit();
            }
        }

        /**
         * 评论数
         * @return mixed
         */
        public function evaluate_count(){
            $this->is_debugger();
            $query_good_level = "select count(id) as good from weixin_commonshop_product_evaluations
                             where customer_id='{$this->customer_id}'
                             and product_id='{$this->pid}'
                             and status=1
                             and (type=1 or type=4)
                             and level=1
                             and isvalid=true";
            $result_good_level = mysql_find($query_good_level);  // type=4 是虚拟评论
            $result["good_level"] = $result_good_level ? $result_good_level["good"] : 0;

            $query_meu_level = "select count(id) as meu from weixin_commonshop_product_evaluations
                            where customer_id='{$this->customer_id}'
                            and product_id='{$this->pid}'
                            and status=1
                            and (type=1 or type=4)
                            and level=2
                            and isvalid=true";
            $result_meu_level = mysql_find($query_meu_level);
            $result['meu_level'] = $result_meu_level ? $result_meu_level["meu"] : 0;

            $query_bad_level = "select count(id) as bad from weixin_commonshop_product_evaluations
                            where customer_id='{$this->customer_id}'
                            and product_id='{$this->pid}'
                            and status=1
                            and (type=1 or type=4)
                            and level=3
                            and isvalid=true";
            $result_bad_level = mysql_find($query_bad_level);
            $result['bad_level'] = $result_bad_level ? $result_bad_level["bad"] : 0;
            $result['total_evaluate'] = $result["good_level"] + $result['meu_level'] + $result['bad_level'];    //评论总数
            return $result;
        }


        /**
         * 新限购活动
         * @return mixed
         */
        public function restricted_purchase($limit_id=""){
            $this->is_debugger();
            $restricted_purchase = new model_restricted_purchase();
            $result["restricted_isout"] = 0;
            $result["restricted_price"] = 0.01;
            $result["purchase_times"] = -1;
            $result["quantity_purchased"] = -1;
            $result["buy_times"] = 0;
            $result["buy_quantity"] = 0;
            $result["restricted_id"] = -1;
            $result["restricted_product_id"] = -1;
            $result["display_time_count_down"] = 1;
            $result["is_display_time"] = 1;
            $result["display_time_range"] = 1;

            $rp_data["user_id"] = $this->user_id;
            $rp_data["customer_id"] = $this->customer_id;
            $rp_data["product_id"] = $this->pid;
            if($limit_id != ""){
                $rp_data["restricted_id"] = $limit_id;
            }

            $restricted_result = $restricted_purchase -> findRestrictedPurchase($rp_data);
            if ($restricted_result["errcode"] == 0 && !empty($restricted_result["data"])) {
                $result["buystart_time"]            = $restricted_result["data"]["time_start"];
                $result["countdown_time"]           = $restricted_result["data"]["time_end"];
                $result["restricted_isout"]         = $restricted_result["data"]["isout"];
                $result["purchase_times"]           = $restricted_result["data"]["purchase_times"];
                $result["quantity_purchased"]       = $restricted_result["data"]["quantity_purchased"];
                $result["restricted_price"]         = $restricted_result["data"]["price"];
                $result["restricted_id"]            = $restricted_result["data"]["restricted_id"];
                $result["restricted_product_id"]    = $restricted_result["data"]["restricted_product_id"];
                $result["is_display_time"]          = $restricted_result["data"]["is_display_time"];
                $result["display_time_count_down"]  = $restricted_result["data"]["display_time_count_down"];
                $result["display_time_range"]       = $restricted_result["data"]["display_time_range"];

                if ($result["restricted_isout"] == 1) {
                    if ($this->now_time > $result["countdown_time"]) {
                        $result["restricted_isout"] = 2;
                    }
                }

                if ($result["restricted_isout"] == 1 && ($this->now_time >= $result["buystart_time"]) && ($this->now_time <= $result["countdown_time"])) {
                    $result["is_restricted"] = 1;        //产品正在参与限购活动
                }

                if ($result["restricted_isout"] == 1 && $result["is_restricted"] == 1) {
                    $result["issnapup"] = 0;        //把旧的抢购活动结束掉
                    $result["p_now_price"] = $result["restricted_price"];    //现价改为抢购价
                    $result["show_now_price"] = $result["p_now_price"];

                    $rp_data["restricted_id"] = $result["restricted_id"];
                    $purchase_result = $restricted_purchase -> findUserRestrictedPurchase($rp_data);    //用户购买统计
                    if ($purchase_result["errcode"] == 0 && !empty($purchase_result["data"])) {
                        $result["buy_times"] = $purchase_result["data"]["buy_times"];
                        $result["buy_quantity"] = $purchase_result["data"]["buy_quantity"];
                    }
                }
            }

            return $result;
        }


        /**
         * 查找购物币抵扣比例
         * @param $p_now_price
         * @return int|string|产品购物币抵扣比例|全局购物币抵扣比例
         */
        public function currency_percentage($p_now_price){
            $this->is_debugger();
            /**
             * @var $percentage 全局购物币抵扣比例
             * @var $currency_percentage 产品购物币抵扣比例
             */

            /* 查找全局购物币抵扣比例 */
            $query = "select percentage from currency_percentage_t where isvalid=true and type=1 and customer_id='{$this->customer_id}' limit 0,1";
            $currency_set = mysql_find($query);
            $percentage = $currency_set["percentage"] ?: 0;

            /* 查找产品购物币抵扣比例 */
            $query = "select currency_percentage from commonshop_product_discount_t where isvalid=true and pid='{$this->pid}' limit 0,1";
            $currency_pro_set = mysql_find($query);
            //$currency_percentage = count($currency_pro_set) > 0 ? $currency_pro_set["currency_percentage"] : $percentage;//默认全局
            $currency_percentage =  $percentage;

            if(count($currency_pro_set) > 0 && $currency_pro_set["currency_percentage"] != -1)
            {
                $currency_percentage = $currency_pro_set["currency_percentage"];
            } 

            $currency_price = $currency_percentage * $p_now_price;
            $currency_price = bcadd($currency_price, 0, 2);//保留两位小数，不四舍五入
            return $currency_price;
        }

        /**
         * 查询是否开启购物币抵扣
         * @return array
         */
        public function currency_setting(){
            $this->is_debugger();
            $query = "SELECT isOpen,custom FROM weixin_commonshop_currency WHERE customer_id='{$this->customer_id}'";
            $result = mysql_find($query);
            return $result;
        }



        public function collage_data($is_collage_from){
            $this->is_debugger();
            if($is_collage_from == 1){
                $collageActivities = new collageActivities($this->customer_id);   //拼团业务
                $condition = array(
                    'cgpt.pid' => $this->pid,
                    'cgpt.isvalid' => true,
                    'cgpt.status' => 1,
                    'cat.status' => 2,
                    'cat.isvalid' => true,
                    'wcp.isvalid' => true,
                    'ae.isvalid' => true,
                    'ae.customer_id' => $this->customer_id,
                    'cat.customer_id' => $this->customer_id
                );
                $filed = " cgpt.activitie_id,cgpt.price,cgpt.success_num,cgpt.stock,cgpt.alone_onoff as p_alone_onoff,cat.start_time,cat.end_time,cat.user_level,cat.number,cat.type,cat.group_size,cat.alone_onoff,ae.type_name ";
                $result["collage_product_info"] = $collageActivities -> get_recommendation_product_system($condition, $filed)['data'][0];

                if ($result["collage_product_info"]) {
                    $result["linkurl"] .= "&is_collage_from=1";
                    $result["is_collage_product"] = 1;
                    $result["singlePrice"] = 0;    //统一单独购买价格
                    $result["groupPrice"] = $result["collage_product_info"]['price'];    //团购价
                    $result["groupStock"] = $result["collage_product_info"]['stock'];    //团购库存
                    //获取团说明
                    //$type_str = $result["collage_product_info"]['type_name'];
                    //$type_explain = $result["collage_product_info"]['type_name'] . '说明';
                    $get_explain = $collageActivities -> getExplain($this->customer_id)['content'];
                    foreach ($get_explain as $k => $v) {
                        if ($v['type'] == $result["collage_product_info"]['type']) {
                            $result["explain"] = $get_explain[$k];
                            break;
                        }
                    }

                    //获取团推荐设置
                    $group_set = $collageActivities -> getGroupRecommendation($this->customer_id);
                    if ($group_set && $group_set['is_open'] == 1) {
                        //获取推荐的团
                        $group_type = explode('_', $group_set['type']);
                        $condition2 = array('cgot.status' => 1,'cat.status' =>  2, 'cgot.type' => $group_type, 'cgot.customer_id' => $this->customer_id, 'cgot.isvalid' => true, 'wu.isvalid' => true, 'cgot.endtime' => ' UNIX_TIMESTAMP(cgot.endtime)>=' . time(),'cat.end_time' => ' UNIX_TIMESTAMP(cat.end_time)>='.time());
                        if ($group_set['num'] > 0) {
                            $condition2['limit'] = " LIMIT " . $group_set['num'];
                        }
                        if ($group_set['sort_type'] == 2) {
                            $condition2['cgot.pid'] = $this->pid;
                            $condition2['cgot.activitie_id'] = $result["collage_product_info"]['activitie_id'];
                        }

                        if ($group_set['sort'] == 1) {
                            $condition2['order_by'] = " ORDER BY cgot.createtime ASC ";
                        } else {
                            $condition2['order_by'] = " ORDER BY cgot.createtime DESC ";
                        }
                        $filed2 = " cgot.id,cgot.success_num,cgot.join_num,cgot.endtime,cat.end_time,wu.weixin_name,wu.name,wu.weixin_headimgurl,wu.province ";
                        $result["groups"] = $collageActivities -> get_group_recommendation($condition2, $filed2)['data'];
                    }
                }
            }else{
                 $result = array('collage_product_info'=>'','explain'=>'','groups'=>'','is_collage_product'=>0,'singlePrice'=>0,'groupPrice'=>0,'groupStock'=>0);
            }
            return $result;
        }


        /**
         * 查询是否续费活动产品
         * @return int
         */
        public function renewal_product() {
            $this->is_debugger();
            $query_renewal = "SELECT pr.id,pr.renew_time
                      FROM promoter_renewal AS pr
                      INNER JOIN promoter_renewal_products AS prp ON prp.renewal_id=pr.id
                      WHERE pr.isout=0 AND pr.isvalid=true AND pr.customer_id='{$this->customer_id}' AND prp.product_id='{$this->pid}' AND pr.isvalid=true  AND prp.isvalid=true ";
            $result_renewal = mysql_find($query_renewal);
            $result["renewal_id"] = $result_renewal["id"] ?: -1;
            $result["renewal_time"] = $result_renewal["renew_time"] ?: 0;
            return $result;
        }

        /**
         * 计算属性的库存
         * @param $o_shop_id    订货系统门店id
         * @param $pro_act_type 活动类型
         * @param $pro_act_type 活动id
         * @return int
         */
        public function storenum_count($o_shop_id,$pro_act_type,$pro_act_id){
            $this->is_debugger();
            $query_storenum = "select id,sum(storenum) as p_storenum from weixin_commonshop_product_prices where product_id='{$this->pid}'";
            $result_storenum = mysql_find($query_storenum);
            $p_storenum = 0;
            $total_sales = 0;
            if ($result_storenum){
                $p_storenum = $result_storenum["p_storenum"];
            }

            //查询订货系统门店和子门店库存  2017-11-08 lj
            if ($o_shop_id > 0) {
                $query_or_pro = "select sum(opp.store_count-opp.freeze_count) as p_storenum from orderingretail_proxy_product as opp
                         RIGHT JOIN orderingretail_shop as os on os.proxy_id=opp.proxy_id
                         where os.id='{$o_shop_id}'
                         and opp.customer_id='{$this->customer_id}'
                         and opp.product_id='{$this->pid}'";
                $result_or_pro = mysql_find($query_or_pro);
                $p_storenum = $result_or_pro["p_storenum"] ?: 0;
            }elseif ($pro_act_type == 22) {
                //积分兑换产品的库存
                $query_integral_stock = "select stock from " . WSY_SHOP . ".integral_exchange_product orderingretail_shop where product_id='{$this->pid}'";

                if(!empty($pro_act_id)){        //积分兑换的活动ID
                    $query_integral_stock .= "and act_id=".$pro_act_id;
                }

                $result_integral_stock = mysql_find($query_integral_stock);
                $p_storenum = $result_integral_stock["stock"] ?: 0;

                //积分兑换产品的销量
                $query_integral_sales = "select sales_volume from " . WSY_SHOP . ".integral_stat_product orderingretail_shop where product_id='{$this->pid}'";

                if(!empty($pro_act_id)){        //积分兑换的活动ID
                    $query_integral_stock .= "and act_id=".$pro_act_id;
                }

                $result_integral_sales = mysql_find($query_integral_sales);
                $total_sales = $result_integral_sales["sales_volume"] ?: 0;
            }
            $result = array(
                "p_storenum" => $p_storenum,
                "total_sales" => $total_sales
            );
            return $result;

        }

        /**
         * 优惠券
         * @param $link_coupons
         * @return array
         */
        public function coupon_data($link_coupons){
            $this->is_debugger();
            if ($link_coupons != -1) {
                $query_coupon = "select id,title,NeedMoney,MaxMoney,CanGetNum,Days,DaysType,user_scene,MoneyType,class_type,startline,connected_id from weixin_commonshop_coupons where isvalid=true and is_open=true and customer_id={$this->customer_id} and (DaysType=0 or (DaysType=1 and Days>='" . date("Y-m-d H:i:s") . "')) and id in ({$link_coupons}) and getStartTime<='".date("Y-m-d H:i:s")."' and getEndTime>'".date("Y-m-d H:i:s")."' order by connected_id desc, createtime desc";
                $result_coupon = mysql_select($query_coupon);
                foreach ($result_coupon as $k => $v){
                    $coupon_isvalid = true;    //可用优惠券
                    //有效期
                    $nowtime = "";
                    if (strtotime($v["startline"]) > time()) {
                        $nowtime = date('Y/m/d', strtotime($v["startline"]));
                    }
                    if ($v["DaysType"] == 1){
                        $endtime = $v["Days"];
                    }else{
                        $endtime = date('Y/m/d', strtotime("+{$v["Days"]} day"));
                    }

                    //使用商品
                    $remark = "仅限购买平台商品使用";
                    if ($v["user_scene"] == 1 && !empty($v["connected_id"])) {
                        $cou_product_sql = "select name from weixin_commonshop_products where id in({$v["connected_id"]}) and isvalid = 1";
                        $result = mysql_select($cou_product_sql);
                        $cou_product_name = "";
                        foreach($result as $key => $v){
                            $cou_product_name .= ",".$v["name"];
                        }
                        $cou_product_name = substr($cou_product_name,1);
                        $remark = "仅限购买商品【{$cou_product_name}】使用";
                        if (empty($result)) {        //查询该产品失败
                            $coupon_isvalid = false;
                        }
                    } elseif (empty($v["connected_id"])) {
                        $coupon_isvalid = false;
                    }

                    $result_coupon[$k]["coupon_isvalid"] = $coupon_isvalid;
                    $result_coupon[$k]["remark"] = $remark;
                    $result_coupon[$k]["nowtime"] = $nowtime;
                    $result_coupon[$k]["endtime"] = $endtime;
                }
                $result = array(
                    "coupon_array" => $result_coupon,
                    "result_coupon_num" => count($result_coupon)
                );
                return $result;
            }
        }

        /**
         * 商城设置额外参数
         * @return array
         */
        public function extend_data(){
            $this->is_debugger();
            $query = "select is_product_shuf,is_show_original,is_stockOut,product_detail_model,is_openOrderMessage,is_pay_on_delivery,is_commission_cal,barcodes,logo,commission_cal_content,shop_jump_type from weixin_commonshops_extend where isvalid=true and customer_id = {$this->customer_id}";
            $result = mysql_find($query);
            return $result;
        }

        /**
         * 获取当天当前用户当前商品购买数量
         * @return array
         */
        public function check_buy_count(){
            $this->is_debugger();
            $sql = "SELECT count(rcount) count from weixin_commonshop_orders
                    where createtime like '%".date('Y-m-d')."%'
                    and customer_id={$this->customer_id}
                    and isvalid=true
                    and pid={$this->pid}
                    and user_id={$this->user_id} and status <> '-1' ";
            $result = mysql_find($sql);
            return $result;
        }

        /**
         * 用户积分
         * @return array
         */
        public function user_integral(){
            $this->is_debugger();
            $sql = "SELECT integral,store_integral FROM moneybag_t WHERE customer_id='{$this->customer_id}' AND user_id='{$this->user_id}' AND isvalid=TRUE";
            $result = mysql_find($sql);
            return $result;
        }

        /**
         * 慈善公益
         * @param $donation_rate
         * @param $p_now_price
         * @return array
         */
        public function charitable($donation_rate,$p_now_price){
            $this->is_debugger();
            /**
             * @var $is_charitable 慈善开关
             * @var $charitable_propotion 慈善公益最低分配率
             */
            $query = "select is_charitable,charitable_propotion from charitable_set_t where isvalid=true and customer_id='{$this->customer_id}'";
            $result = mysql_find($query);
            $result["is_charitable"] = $result["is_charitable"] ?: 0;
            $result["charitable_propotion"] = $result["charitable_propotion"] ?: 0;

            $result["charitable_price"] = 0;
            if ($result["is_charitable"] == 1 && $donation_rate < $result["charitable_propotion"]) {
                $donation_rate = $result["charitable_propotion"];
            }
            $result["charitable_price"] = $donation_rate * $p_now_price;
            $result["charitable_price"] = round($result["charitable_price"], 2);
            return $result;
        }

        /**
         * 供货商产品数据
         * @param $is_supply_id
         * @param $commonshop_logo
         * @return array
         */
        public function supply_data($is_supply_id,$commonshop_logo){
            $this->is_debugger();
            $result["brand_logo"] = "images/dianpu2.png";
            if ($is_supply_id > 0) {
                $sql = "select isbrand_supply,shopName,advisory_telephone,advisory_flag,is_admin_closed,is_owner_closed from weixin_commonshop_applysupplys
                    where isvalid=true
                    and user_id='{$is_supply_id}' limit 0,1";
                $result = mysql_find($sql);
                $result["brand_supply_name"] = $result["shopName"] ?: 0;
                $result["advisory_telephone"] = $result["advisory_telephone"] ?: 0;

                if ($result["isbrand_supply"]) {
                    $sql = "select brand_logo,brand_supply_name,comment_num,collect_num,pro_num from weixin_commonshop_brand_supplys
                        where isvalid = true
                        and brand_status = 1
                        and user_id = '{$is_supply_id}'
                        and customer_id = '{$this->customer_id}' limit 0,1";
                    $result_data = mysql_find($sql);
                    $result["brand_logo"] = $result_data["brand_logo"];
                    $result["brand_supply_name"] = $result_data["brand_supply_name"];
                    $result["collect_num"] = $result_data["collect_num"];
                    $result["comment_num"] = $result_data["comment_num"];

                    $all_query = "select count(1) as pcount from weixin_commonshop_products
                                  where isvalid = true
                                  and isout = false
                                  and customer_id = '{$this->customer_id}' and is_supply_id = '{$is_supply_id}'";
                    $all_result = mysql_find($all_query);
                    $result["pro_num"] = $all_result["pcount"] ?: 0;

                    $query_s = "select count(1) as scount from weixin_user_collect
                                where isvalid=true
                                and collect_id = '{$is_supply_id}'
                                and customer_id = '{$this->customer_id}'
                                and collect_type = 2
                                and user_id = '{$this->user_id}'";
                    $result_s = mysql_find($query_s);
                    $result["scount"] = $result_s["scount"] ?: 0;     //是否已收藏：0否，1是
                }

            } elseif ($commonshop_logo){//非供应商产品展示商城logo
                $result["brand_logo"] = $commonshop_logo;
            }

            if($result["brand_logo"] == false)
            {
                $result["brand_logo"] = "images/dianpu2.png";
            }
            return $result;
        }


        /**
         * 检测是否符合首次推广奖励
         * @param $exp_user_id
         * @param $is_first_extend
         * @return int
         */
        public function promoter_reward($exp_user_id,$is_first_extend){
            $this->is_debugger();
            $check_first_extend = 0;
            if ($this->user_id > 0) {
                $query_parent = "select parent_id,weixin_name from weixin_users where id = '{$this->user_id}' and customer_id = '{$this->customer_id}' and isvalid = true";
                $result_parent = mysql_find($query_parent);
                $my_parent_id = $result_parent["parent_id"] ?: -1;

                //上级是否推广员
                $query_parent_p = "select count(1) as p_is_promoter from promoters
                                   where user_id = '{$my_parent_id}'
                                   and status = 1
                                   and isvalid = true
                                   and customer_id = '{$this->customer_id}'";
                $result_parent_p = mysql_find($query_parent_p);
                $p_is_promoter = $result_parent_p["p_is_promoter"] ?: 0;

                //上级是否已获得该产品的首次推广奖励
                $query_extend = "select id from weixin_commonshop_extend_logs
                                 where user_id = '{$my_parent_id}'
                                 and p_id = '{$this->pid}'
                                 and customer_id = '{$this->customer_id}'
                                 and isvalid = true";
                $result_extend = mysql_find($query_extend);
                $extend_id = $result_extend["id"] ?: -1;

                //条件：1、从分享页面打开。2、非自己打开分享页面。3、首次推广奖励产品。4、分享人id匹配上级id。5、上级没有获得该产品的首次推广奖励。6、上级是推广员 (在下单页面需要再验证产品是否为首次推广奖励产品)
                if ($exp_user_id > 0 && $exp_user_id != $this->user_id && $is_first_extend == 1 && $my_parent_id == $exp_user_id && $extend_id < 0 && $p_is_promoter > 0) {
                    $check_first_extend = 1;
                }
            }
            return $check_first_extend;
        }

        /**
         * 属性数据
         * @param $propertyids
         * @param $default_imgurl
         * @return array
         */
        public function prodis_data($propertyids,$default_imgurl){
            $this->is_debugger();
            //查询主属性是否有图片
            $attr_parent_id = -1;
            $sql_attr_img = "select wxcpai.attr_id,wxcp.name,wxcpai.img,wxcp.parent_id from weixin_commonshop_product_attrimg wxcpai
                     inner join weixin_commonshop_pros wxcp on wxcpai.attr_id = wxcp.id
                     where wxcpai.customer_id='{$this->customer_id}'
                     and wxcpai.pro_id='{$this->pid}'
                     and wxcpai.status=1";
            $result_attr_img = mysql_select($sql_attr_img);

            $attr_index = 0;
            $attr_img_str[] = array("attr" => "默认图","img" => $default_imgurl);
            $attr_img_array = [];
            foreach ($result_attr_img as $k => $v) {
                $attr_parent_id = $v["parent_id"];
                $temp_img = $v["img"];
                $temp_attr_id = $v["attr_id"];
                $temp_name = $v["name"];
                if (!empty($temp_img) && !empty($temp_attr_id)) { //'0':{'attr':'默认图','img':default_imgurl}
                    $attr_index++;
                    $attr_img_str[] = array("attr" => $temp_name,"img" => $temp_img);
                    $attr_img_array[$temp_attr_id] = array('img' => $temp_img, 'index' => $attr_index);
                }
            }
            $attr_img_str = json_encode($attr_img_str);

            $sql = "SELECT wholesale_childid FROM weixin_commonshop_product_extend WHERE pid = '{$this->pid}'";
            $result = mysql_find($sql);
            $is_wholesale = 0;
            if ($result) {
                $propertyids .= "_".$result["wholesale_childid"];
                $is_wholesale = 1;
            }

            $proids = explode("_", $propertyids);
            $proids_arr = array();
            if ($propertyids){
                $i = 1;
                foreach ($proids as $k => $v){
                    $sql = "SELECT name,parent_id,parent_name,wholesale_num FROM weixin_commonshop_pros WHERE id = '{$v}'";
                    $result = mysql_find($sql);
                    if (!$proids_arr[$result['parent_id']]){
                        $proids_arr[$result['parent_id']]["name"] = $result["parent_name"];
                    }
                    $proids_arr[$result['parent_id']]["data"][$v]["name"] = $result["name"];
                    if ($attr_img_array[$v]){
                        $proids_arr[$result['parent_id']]["parent_attr_img"] = 1;
                        $proids_arr[$result['parent_id']]["data"][$v]["img"] = $attr_img_array[$v]["img"];
                        $proids_arr[$result['parent_id']]["data"][$v]["index"] = $attr_img_array[$v]["index"];
                    }
                    if ($is_wholesale == 1){
                        $proids_arr[$result['parent_id']]["data"][$v]["wholesale_num"] = $result["wholesale_num"];
                        //echo ' <input type="hidden" name="wholesale_num" id="wholesale_num" value="'.$result["wholesale_num"].'">';
                    }
                    $i++;
                }
            }
            $result["is_wholesale"] = $is_wholesale;
            $result["proids_arr"] = $proids_arr;
            $result["attr_parent_id"] = $attr_parent_id;
            $result["attr_img_str"] = $attr_img_str;
            $result["attr_img_array"] = $attr_img_array;
            return $result;
        }


        /**
         * 查询产品价格库存数据
         * @param $data
         * @return array
         */
        public function product_data($data){
            $this->is_debugger();
            $pro_act_type = $data["pro_act_type"];
            $restricted_isout = $data["restricted_isout"];
            $restricted_price = $data["restricted_price"];
            $is_restricted = $data["is_restricted"];
            $o_shop_id = $data["o_shop_id"];
            $result = [];
            $query = "select proids,orgin_price,now_price,storenum,need_score,weight,unit from weixin_commonshop_product_prices where product_id='{$this->pid}'";
            $result_proids = mysql_select($query);
            foreach ($result_proids as $k => $v) {
                $proids = $v["proids"] ?: "";
                $orgin_price = $v["orgin_price"] ?: 0;
                $now_price = $v["now_price"] ?: 0;
                $storenum = $v["storenum"] ?: 0;
                $unit = $v["unit"] ?: "";
                $need_score = $v["need_score"] ?: 0;
                $pro_weight = $v["weight"] ?: 0;
                //订货系统门店或者子门店读取门店库存   订货系统4.0加  2017-11-08 lj
                if ($o_shop_id > -1) {
                    $query_or_pro = "select opp.proids,(opp.store_count-opp.freeze_count) as store_count from orderingretail_proxy_product as opp
                                 RIGHT JOIN orderingretail_shop as os on os.proxy_id=opp.proxy_id
                                 where os.id='{$o_shop_id}'
                                 and opp.customer_id='{$this->customer_id}'
                                 and opp.product_id='{$this->pid}'
                                 and opp.proids='{$proids}' limit 1";
                    $result_or_pro = mysql_find($query_or_pro);
                    $storenum = $result_or_pro["store_count"] ?: 0;
                }
                //根据活动类型改变产品属性价格
                switch ($pro_act_type) {
                    //限购
                    case 31:
                        if ($restricted_isout == 1 && $is_restricted == 1) {
                            $now_price = $restricted_price;
                        }
                        break;
                }
                $proids_arr = explode("_", $proids);
                asort($proids_arr);
                $proids_str = implode("_", $proids_arr);
    //            foreach ($proids_arr as $k => $v) {
    //                $proids_str .= "_" . $v;
    //            }
    //            $proids_str = substr($proids_str, 1);
                $result[] = array(
                    "proids"    => $proids_str,
                    "info"      => "{$orgin_price}_{$now_price}_{$storenum}_{$need_score}_{$unit}_{$pro_weight}",
                    'old_proids'=> $proids
                );
            }

            return $result;
        }

        //获取所有四级区域
        public function aog_area(){
            $this->is_debugger();
            $aog_area = [];
            $query_all_area = "SELECT pros, province, city, area, diy_area FROM aog_products_pros_areas WHERE pid='{$this->pid}' AND isvalid=true";
            $result_all_area = mysql_select($query_all_area);
            foreach($result_all_area as $k => $v){
                $pros = $v["pros"] ?: -1;
                $aog_area[$pros][$v["province"] . $v["city"] . $v["area"]][] = $v["diy_area"];
            }
            return $aog_area;
        }


        /**
         * 获取产品图片数据
         * @return array
         */
        public function proimg_data(){
            $this->is_debugger();
            $query = "SELECT id,imgurl FROM weixin_commonshop_product_imgs where  isvalid=true and product_id='{$this->pid}'";
            $result = mysql_select($query);
            return $result;
        }


        /**
         * 预配送数据
         */
        public function aog_data(){
            $this->is_debugger();
            $query_aog = "SELECT id FROM aog_products_t WHERE pid='{$this->pid}' AND customer_id='{$this->customer_id}' AND isvalid=true";
            $result_aog = mysql_find($query_aog);
            $result["is_aog"] = 0;
            if ($result_aog) {
                $result["is_aog"] = 1;
                $is_available = 0;    //是否有货
                $aog_p = "";
                $aog_c = "";
                $aog_a = "";
                $aog_d = "";
                $aog_date = '';
                //获取默认收货地址
                if ($this->user_id > 0) {
                    $query_default = "SELECT location_p, location_c, location_a, address FROM weixin_commonshop_addresses WHERE user_id='{$this->user_id}' AND is_default=1 AND isvalid=true";

                    $result_default = mysql_find($query_default);
                    $aog_p = $result_default['location_p'];
                    $aog_c = $result_default['location_c'];
                    $aog_a = $result_default['location_a'];
                    $aog_d = $result_default['address'];
                }
                if (!$aog_p) {
                    //没有默认收货地址则默认北京市
                    $aog_p = '北京市';
                    $aog_c = '北京市';
                    $aog_a = '东城区';
                    $query_aog_area = "SELECT is_available, hours_time, province, city, area FROM aog_products_pros_areas WHERE pid='{$this->pid}' AND pros='' AND province='{$aog_p}' AND city='{$aog_c}' AND area='{$aog_a}' AND isvalid=true LIMIT 1";
                    $result_aog_area = mysql_find($query_aog_area);
                } else {
                    $query_aog_area = "SELECT is_available, hours_time FROM aog_products_pros_areas WHERE pid='{$this->pid}' AND pros='' AND isvalid=true AND province='{$aog_p}' AND city='{$aog_c}' AND area='{$aog_a}' AND diy_area='{$aog_d}'";
                    $result_aog_area = mysql_find($query_aog_area);
                }
                if ($result_aog_area) {
                    $is_available = $result_aog_area["is_available"];
                    $aog_date = date('m月d日', strtotime('+' . $result_aog_area['hours_time'] . ' hours'));
                }
                $is_available_str = $is_available ? '有货' : '无货';
                $result["is_available"] = $is_available;
                $result["aog_p"] = $aog_p;
                $result["aog_c"] = $aog_c;
                $result["aog_a"] = $aog_a;
                $result["aog_d"] = $aog_d;
                $result["aog_date"] = $aog_date;
                $result["is_available_str"] = $is_available_str;
            }
            return $result;
        }

        /**
         * 产品活动优先级判断
         * @param $pro_act_type
         * @param $restricted_isout
         * @param $integral_act_type
         * @param $restricted_id
         * @return mixed
         */
        public function check_priority($pro_act_type,$restricted_isout,$integral_act_type,$restricted_id,$int_act_id){
            $this->is_debugger();
            //如果pro_act_type大于0，则是从活动专区进来，优先级最高
            $is_check_priority = 1;
            $pro_act_id = "";
            $active_countdown = "";
            $is_collage_product = "";
            $integral_type = "";
            switch ($pro_act_type) {
                case 21:
                    $integral_type     = $integral_act_type;
                    $restricted_isout  = 0;    //结束限购
                    $is_check_priority = 0;    //活动专区进来，不执行优先级比较
                    break;
                case 22:
                    $integral_type     = $integral_act_type;
                    $restricted_isout  = 0;    //结束限购
                    $is_check_priority = 0;    //活动专区进来，不执行优先级比较
                    $pro_act_id        = $int_act_id;
                    break;
                case 31:
                    $integral_type = 0;    //结束积分活动
                    $is_check_priority = 0;    //活动专区进来，不执行优先级比较
                    $pro_act_id = $restricted_id;
                    break;
            }

            /***************** 如果活动自己带参数进来玩的，并且要求活动优先级最高写在上面，并且要把其他活动结束掉  ***********************/
            $active_arr['customer_id'] = $this->customer_id;
            //如果参与限购
            if ($restricted_isout == 1) {
                $active_arr['restricted'] = 1;
                $active_arr['second_type'] = 1;
            }
            //如果参与积分
            if ($integral_act_type > 0) {
                $active_arr['integral'] = 1;
                $active_arr['second_type'] = $integral_act_type;
            }
            if ($is_check_priority == 1) {

                require_once($_SERVER['DOCUMENT_ROOT'] . '/mshop/web/model/activity_set.php');
                $activity_set = new model_activity_set();
                $result_act = $activity_set -> getProductActivity($active_arr);    //方法里面得出当前产品优先参与的活动

                if ($result_act['errcode'] == 0 && !empty($result_act['data']['activity'])) {
                    /**
                     * $pro_act_id        活动编号
                     * $pro_act_type    10~19 拼团（拼团自己带参数进来玩，优先级最高，这里的类型没用的）
                     * $pro_act_type    20~29 积分
                     * $pro_act_type    30~39 限购
                     * $pro_act_type    40~49 砍价
                     * $pro_act_type    50~59 众筹
                     **/
                    $activity_info = $result_act['data']['activity'];    //活动信息
                    $active_countdown = $activity_info['count_down'];
                    switch ($activity_info['activity_type']) {
                        //拼团
                        case 'collage':
                            $integral_type    = 0;    //结束积分活动
                            $restricted_isout = 0;    //结束限购
                            break;
                        //积分
                        case 'integral':
                            $integral_type      = $integral_act_type;
                            $is_collage_product = 0;    //结束拼团
                            $restricted_isout   = 0;    //结束限购
                            $pro_act_id         = $int_act_id;
                            $pro_act_type       = 20 + $integral_act_type;
                            break;
                        //限购
                        case 'restricted':
                            $is_collage_product = 0;    //结束拼团
                            $integral_type = 0;    //结束积分活动
                            $pro_act_id = $restricted_id;
                            $pro_act_type = 31;
                            break;
                    }

                }
            }
            $result["restricted_isout"] = $restricted_isout;
            $result["is_check_priority"] = $is_check_priority;
            $result["pro_act_id"] = $pro_act_id;
            $result["active_countdown"] = $active_countdown;
            $result["is_collage_product"] = $is_collage_product;
            $result["pro_act_type"] = $pro_act_type;
            $result["integral_type"] = $integral_type;

            return $result;
        }

        /**
         * 积分产品，自动查找产品是否是积分活动产品
         * @param $pro_act_type
         * @return array
         */
        public function integral_data($pro_act_type){

            $this->is_debugger();
            //查询是否开启商城积分 或者 门店积分
            $integral_setting = "SELECT shop_onoff, store_onoff,basic_json,store_json FROM " . WSY_SHOP . ".integral_setting WHERE cust_id='{$this->customer_id}'";
            $res_integral_setting = mysql_find($integral_setting);

            $shop_set   = json_decode($res_integral_setting["basic_json"],TRUE);
            $store_set  = json_decode($res_integral_setting["store_json"],TRUE);

            $integral_type = 1;
            if (!(is_numeric($pro_act_type) && $pro_act_type > 0)) {
                $pro_act_type = -1;
            } elseif ($pro_act_type == 21) {
                //$integral_type = 1; //默认为 1
            } elseif ($pro_act_type == 22) {
                $integral_type = 2;
            }
            $model_integral = new model_integral();
            $check_integral_data['p_id'] = $this->pid;
            $check_integral_data['cust_id'] = $this->customer_id;

            $check_integral_data['come_type'] = $integral_type;   //如果是入口，就只找相应活动

            /*$data2['cust_id']     = $this->customer_id;
            $integral_name_res      = $model_integral->get_integral_name($data2);
            if($integral_name_res['errcode']==0 && $integral_name_res['integral_name']!=''){
                $integral_name = $integral_name_res['integral_name'];
            }else{
                $integral_name = '商城积分';
            }*/

            $integral_pro_result = $model_integral -> check_product_integral_activity($check_integral_data);
            $act_type = $integral_pro_result['act_type'];
            $act_id = $integral_pro_result['act_id'];

            $pro_act_id = 0;
            $integral_act_type = 0;
            if ($act_type > 0) {
                $pro_act_id = $act_id;
            }
            if ($act_type == 0) {
                $integral_act_type = 1;        //商城常量积分
            } elseif ($act_type == 1) {
                $integral_act_type = 1;        //商城赠送积分
            } elseif ($act_type == 2) {
                $integral_act_type = 2;        //兑换积分
            }

            $cal_integral_data['type'] = $integral_type;            //类型 1赠送积分 2兑换积分
            $cal_integral_data['integral_type'] = 0;        //是否是门店积分 0不 1是
            $cal_integral_data['pro_json'] = json_encode(array(array('pid' => $this->pid, 'num' => 1, 'pros_id' => '', 'act_id' => $act_id))); //产品信息 json
            $cal_integral_data['customer_id'] = $this->customer_id;        //商家id
            $integral_result = $model_integral -> cal_product_integral($cal_integral_data);

            //如果兑换活动有误
            if ($integral_type == 2 && $integral_result['data'][$this->pid]['isvalid'] == 0) {
                $cal_integral_data['type'] = $integral_type = 1;            //类型 1赠送积分 2兑换积分
                $pro_act_type    = 21;                                      //活动类型 21赠送积分 22兑换积分
                $integral_result = $model_integral -> cal_product_integral($cal_integral_data);
            }

            $istart_time = "";
            $iend_time = "";
            if ($integral_type == 2) {
                $queryi = "select start_time,end_time from " . WSY_SHOP . ".integral_activity where isvalid=1 and act_id='{$act_id}'";
                $result = mysql_find($queryi);
                $istart_time = $result["start_time"];
                $iend_time = $result["end_time"];
            }

            $result = array(
                "shop_int_name" => $shop_set['integral_name'] ?: "商城积分",
                "store_int_name" => $store_set['integral_name'] ?: "门店积分",
                "istart_time" => $istart_time,
                "iend_time" => $iend_time,
                "integral_result" => $integral_result,
                "integral_act_type" => $integral_act_type,
                "pro_act_type" => $pro_act_type,
                "pro_act_id" => $pro_act_id,
                "shop_integral_onoff" => $res_integral_setting["shop_onoff"],   //商城
                "store_integral_onoff" => $res_integral_setting["store_onoff"]  //门店
            );

            return $result;
        }

         /**
         * 获取该子属性的父属性排序
         * @return array
         */
        public function get_pros_index($proids){
            $l_proids_arr = explode('_', $proids);
            $l_pro_p_arr  = [];
            foreach ($l_proids_arr as $l_key => $l_value) {
                $query_pros  = "SELECT parent_id FROM weixin_commonshop_pros WHERE isvalid=TRUE AND id ='".$l_value."'";
                $result_pros = _mysql_query($query_pros) or die('Query_pros failed:'.mysql_error());
                while ($row_pros = mysql_fetch_object($result_pros)) {
                    $l_pro_p_arr[] = $row_pros->parent_id;;
                }
            }
            $p_pro_str = implode('_', $l_pro_p_arr);
            return $p_pro_str;
        }
        /**
         * 检查云店是否有效
         * @return array
         */
        public function yundian_isvalid($yundian){
            $time = date('Y-m-d H:i:s',time());
            $sql = "SELECT k.id,k.expire_time,u.weixin_name FROM ".WSY_USER.".weixin_yundian_keeper k inner join ".WSY_USER.".weixin_users u on u.id = k.user_id WHERE k.isvalid = true AND k.customer_id='{$this->customer_id}' AND k.id='{$yundian}'  AND status = 1";
            $result = mysql_find($sql);
            return $result;
        }

    }