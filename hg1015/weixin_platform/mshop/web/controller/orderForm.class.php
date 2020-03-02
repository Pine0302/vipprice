<?php

class orderForm
{
    //数据库类
    public $db;
    //拼团方法类
    public $collageActivities;
    //满赠活动类
    public $exchange_m;
    //支付方式类
    public $show_pay_way;
    //下单拓展类
    public $orderFormExtend;
    
    public $customer_id;
    public $customer_id_en;
    public $user_id;
    public $fromuser;
    //下单方式，1 立即购买，2 购物车结算
    public $fromtype;
    //客户端类型
    public $from_type;
    
    //返回数据数组，键名对应变量名
    public $returnArr;
    private $configutil;
    
    //请求的参数
    private $requestData;
    
    function __construct($customer_id, $user_id, $fromuser, $from_type)
    {
        
        $this->customer_id      = $customer_id;
        $this->customer_id_en   = passport_encrypt((string)$customer_id);
        $this->user_id          = $user_id;
        $this->fromuser         = $fromuser;
        $this->from_type        = $from_type;
        $this->configutil       = new ConfigUtility();
        //假如没有post数据和session数据，则跳转到购物车
        if ( $_POST['pid'] == '' && $_POST['pro_arr'] == '' && $_SESSION['bug_post_data_' . $this->user_id] == '' )
        {
            echo "<script>location.href='order_cart.php?customer_id={$this->customer_id_en}';</script>";
            exit;

        }
        
        //微信端下单，fromuser为空则不能下单
        if ( $this->from_type == 1 && $this->fromuser == '' )
        {
            echo "	<script>
                        alert('未知错误！没有获取到个人信息！');
                        location.href='order_cart.php?customer_id={$this->customer_id_en}';
                    </script>";
            exit;
        }
        
        require_once ROOT_DIR . 'mp/database.php';
        $this->db = \DB::getInstance();
        
        $this->collageActivities = new collageActivities($this->customer_id);
        $this->exchange_m = new model_exchange($this->customer_id);
        $this->show_pay_way = new show_pay_way($this->customer_id);
        
        require_once ROOT_DIR . 'mshop/web/controller/orderFormExtend.class.php';
        
        $this->configutil = new ConfigUtility();
    }
    
    //主函数
    public function main()
    {
        
        if ( !empty($_POST["fromtype"]) )
        {
            $this->fromtype = $this->configutil->splash_new($_POST["fromtype"]);
            $_SESSION['fromtype'] = $this->fromtype;
        } elseif ( !empty($_SESSION['fromtype']) ) {
            $this->fromtype = $_SESSION['fromtype'];
        }
        
        //实例化下单拓展类
        $this->orderFormExtend = new \orderFormExtend($this->customer_id, $this->user_id, $this->fromtype);
        
        //获取订单数据
        $orderData = $this->getOrderData();
        extract($orderData);
       
        //商城设置
        $shop_setting = $this->shop_setting();
        
        //用户数据
        $user_data = $this->user_data();
        //收货地址
        $address = $this->getAddress();
        
        //自定义区域
        $diy_area = $this->diy_area($address['location_p'], $address['location_c'], $address['location_a'], $address['address']);
        
        //拼团活动
        $collageActivities = $this->collage($is_collage_product_info);
        
        //满赠活动
        $this->exchange($price);
        
        //是否首次奖励产品
        $is_first_extend = $this->is_first_extend($pid);
        if ( !$is_first_extend ) $orderData['check_first_extend'] = 0;
        
        //是否虚拟产品
        $is_virtual = $this->is_virtual($pid);
        $orderData['is_virtual'] = $is_virtual;
        
        //如果是虚拟产品则关闭下面开关
        if ( $is_virtual )
        {
            $shop_setting['is_identity'] 		= 0;
            $shop_setting['is_uploadidentity'] 	= 0;
            $shop_setting['sendstyle_express'] 	= 0;
            $shop_setting['sendstyle_pickup'] 	= 0;
            $diy_area['is_diy_area']            = 0;
        }
        
        if ( $this->fromtype == 1 )     //立即购买
        {
            //电商直播参数
            $mb_str = '';
            if ( $resource_id > 0 )
            {
                $mb_str = 'resourceid_' . $resource_id;
            } elseif ( $topic_id > 0 ) {
                $mb_str = 'topicid_' . $topic_id;
            }
            
            if ( !empty($_POST['pid']) )
            {
                if($form_crowdfund_sz){
                    $bug_post_data = $_SESSION['bug_post_data_' . $this->user_id] = array($supply_id, array($pid, $prvalues, $rcount, $mb_str, $is_collage_product_info, $act_type, $act_id, -1, false, $form_crowdfund_sz,$pid_yundian_arr[$pid]));
                }else{
                    $bug_post_data = $_SESSION['bug_post_data_' . $this->user_id] = array($supply_id, array($pid, $prvalues, $rcount, $mb_str, $is_collage_product_info, $act_type, $act_id, -1, false, $form_bargain_sz,$pid_yundian_arr[$pid]));
                }
                
            } else {
                $bug_post_data = $_SESSION['bug_post_data_' . $this->user_id];
            }
            //满赠产品数据
            $exchange_list = count(json_decode($_SESSION['exchange_' . $this->user_id])) ? $_SESSION['exchange_' . $this->user_id] : $_REQUEST['exchange'];
            
            // var_dump($bug_post_data);

            //如果有满赠产品，则转换为购物车数据结构
            if ( $exchange_list )
            {
                $bug_post_data_new[] = $bug_post_data;
                $bug_post_data = array_merge($bug_post_data_new , json_decode($exchange_list));
                $buy_array = $this->orderFormExtend->remake(2, $bug_post_data, 1);
                $buy_array_add_express = $this->orderFormExtend->remake(2, $bug_post_data, 2);
            } else {
                
                $buy_array = $this->orderFormExtend->remake(1, $bug_post_data, 1);
                $buy_array_add_express = $this->orderFormExtend->remake(1, $bug_post_data, 2);
            }
            
            // var_dump($buy_array);
            // var_dump($buy_array_add_express);
        } elseif ( $this->fromtype == 2 ) {    //购物车结算
            
            if ( empty($pro_arr) )
            {
                $bug_post_data = $_SESSION['bug_post_data_' . $this->user_id];
                $orderData['clean_cart'] = json_encode( $bug_post_data );
            } else {
                $bug_post_data = $_SESSION['bug_post_data_' . $this->user_id] = $pro_arr;
            }

            //满赠产品数据
            $exchange_list = count(json_decode($_SESSION['exchange_' . $this->user_id])) ? $_SESSION['exchange_' . $this->user_id] : $_REQUEST['exchange'];
            
            if( $exchange_list ) {
                $bug_post_data = array_merge($bug_post_data, json_decode($exchange_list));
            }
            
            $buy_array = $this->orderFormExtend->remake(2, $bug_post_data, 1);
            $buy_array_add_express = $this->orderFormExtend->remake(2, $bug_post_data, 2);
            // var_dump($buy_array);
            // var_dump($buy_array_add_express);
        }
        
        //重组订单数据
        $extend_data = array(
            'is_collage_product'    => $collageActivities['is_collage_product'],
            'group_buy_type'        => $collageActivities['group_buy_type'],
            'group_price'           => $collageActivities['group_price'],
            'currency_percentage_t' => $shop_setting['currency_percentage_t'],
            'shop_name'             => $shop_setting['shop_name'],
            'issell'                => $shop_setting['issell'],
            'issell_model'          => $shop_setting['issell_model'],
            'init_reward'           => $shop_setting['init_reward'],
            'location_p'            => $address['location_p'],
            'location_c'            => $address['location_c'],
            'location_a'            => $address['location_a'],
            'promoter_isvalid'      => $user_data['promoter_isvalid'],
            'user_commision_level'  => $user_data['user_commision_level'],
            'user_is_consume'       => $user_data['user_is_consume'],
        );
        // var_dump($buy_array);
        // var_dump($buy_array_add_express);
        // var_dump($extend_data);
        $remake_order_data = $this->orderFormExtend->remake_order_data($buy_array, $buy_array_add_express, $extend_data);
        // var_dump($remake_order_data);
        //满赠活动
        foreach ( $remake_order_data['del_exchange'] as $k => $v )
        {
            array_splice($buy_array_add_express, $v, 1);
        }
        $remake_order_data['buy_all_data_json'] = json_encode($remake_order_data['buy_all_data']);
        $remake_order_data['supply_express_json'] = json_encode($remake_order_data['supply_express']);
        $remake_order_data['buy_array_add_express_json'] = json_encode($buy_array_add_express);
        // var_dump($buy_array_add_express);
        //货到付款
        $delivery_on = $this->pay_on_delivery($remake_order_data['buy_all_data']);
        
        //合并所有数据到一个数组
        $extend_return = array(
            'fromtype' => $this->fromtype,
            'delivery_on' => $delivery_on
        );
        //var_dump($orderData);
        $return = array_merge($orderData, $user_data, $shop_setting, $address, $diy_area, $collageActivities, $remake_order_data, $extend_return);
        
        return $return;

    }
    
    //获取订单数据
    public function getOrderData()
    {
        $return = array(
            'pro_arr' => [],
            'pid_yundian_arr' => [],    //产品所属云店  $pid_yundian_arr[$pid] => $yundian_id  -1：平台或供应商；大于0：所属的云店ID
            'clean_cart' => '',
            'pid' => 0,
            'prvalues' => '',
            'rcount' => 1,
            'supply_id' => -1,
            'o_shop_id' => -1,
            'yundian_id' => -1,         //产品下单的云店ID
            'topic_id' => 0,
            'resource_id' => 0,
            'is_collage_product_info' => '',
            'check_first_extend' => 0,
            'delivery_time' => '',
            'sendtime_id' => '-1',
            'rtn_sendtime_array' => '',
            'diy_area_id' => -1,
            'rtn_diy_area_array' => '',
            'form_bargain_sz' => '',
            'form_bargain_sz_data' => '',
            'form_crowdfund_sz' => '',
            'form_crowdfund_sz_data' => '',
            'price' => '',
            'act_type' => -1,
            'act_id' => -1,
            'only_type' => -1,
            'is_pickup' => 0,  
        );
        
        if ( $_POST['op'] && $_POST['op'] == "check_delivery" )
        {
            $pid = $_POST['pid'];
            if ( count($pid) > 1 )
            {
                $pid = implode(',', $pid);
            }
            $sql = "SELECT id FROM " . WSY_SHOP . ".pay_on_delivery_products_t WHERE isvalid=1 AND pid IN ($pid)";
            $res = $this->db->getOne($sql);

            if ( $res )
            {
                die(json_encode(array("status"=>"error",'msg'=>'该产品不支持货到付款')));
            }
            die(json_encode(array("status"=>"ok",'msg'=>'该产品支持货到付款')));
        }
        
        $pid_arr = array(); //用于判断自提产品    2018.2.24

        // var_dump($_POST["pro_arr"]);

        if ( !empty($_POST["pro_arr"]) )
        {
            $pro_arr  	= $_POST["pro_arr"]; 	//不使用防注入
            $return['clean_cart'] = $pro_arr;   //记录购物车传来的数据，用于清除购物车记录
            $return['pro_arr'] = json_decode($pro_arr);

            foreach(json_decode($pro_arr) as $key=>$value){
                $pid_arr[] = $value[1][0];
                $pid_yundian_arr[$value[1][0]] = $value[2];    //记录购物车传进来的云店id；-1：平台或供应商；大于0：所属的云店ID
            }
        }
        
        if ( !empty($_REQUEST["pid"]) )
        {
            $return['pid'] = $_SESSION['pid_' . $this->customer_id] = $this->configutil->splash_new($_REQUEST["pid"]);
            $pid_arr[]     = $return['pid'];
            $pid_yundian_arr[$return['pid']] = $_POST["pro_yundian_id"];//记录购物车传进来的云店id；-1：平台或供应商；大于0：所属的云店ID
        } elseif ( !empty($_SESSION['pid_' . $this->customer_id]) ) {
            $return['pid'] = $_SESSION['pid_' . $this->customer_id];
            $pid_arr[]     = $return['pid'];
            $pid_yundian_arr[$return['pid']] = $_POST["pro_yundian_id"];  //记录购物车传进来的云店id；-1：平台或供应商；大于0：所属的云店ID
        }
        
        //产品所属的云店ID
        if ( !empty($pid_yundian_arr) )
        {
            $return['pid_yundian_arr'] = $pid_yundian_arr;
        }

        //产品所属的云店ID
        if ( !empty($_POST['yundian_id']) )
        {
            $return['yundian_id'] = $_POST['yundian_id'];
            $_SESSION['yundian_id_' . $this->user_id] = $return['yundian_id'];      //将云店ID存进session，防止跳转到其他页面时丢失云店ID
        }else{
            if(!empty($_SESSION['yundian_id_' . $this->user_id])){
                $return['yundian_id'] = $_SESSION['yundian_id_' . $this->user_id];
            }else{
                $return['yundian_id'] = -1;
            }
        }

        //所选的属性ID
        if ( !empty($_POST["sel_pros"]) )
        {
            $return['prvalues'] = $this->configutil->splash_new($_POST["sel_pros"]);
        }
        
        //数量
        if ( !empty($_POST["rcount"]) )
        {
            $return['rcount'] = (int)($this->configutil->splash_new($_POST["rcount"]));
        }
        
        //供应商id
        if ( !empty($_POST["supply_id"]) )
        {
            $return['supply_id'] = $this->configutil->splash_new($_POST["supply_id"]);
        }

        //获取订货系统门店id
        if ( !empty($_POST['o_shop_id']) && $_POST['o_shop_id'] > 0 )
        {
            $return['o_shop_id'] = $_SESSION['o_shop_id_' . $this->user_id] = $this->configutil->splash_new($_POST['o_shop_id']);
        } elseif ( !empty($_SESSION['o_shop_id_' . $this->user_id]) ){
            $return['o_shop_id'] = $_SESSION['o_shop_id_' . $this->user_id];
        }
        
        //商城直播那边传过来的话题id
        if ( !empty($_POST['topic_id']) && is_numeric($_POST['topic_id']) && $_POST['topic_id']>0 )
        {
            $return['topic_id'] = $this->configutil->splash_new($_POST["topic_id"]) ? $this->configutil->splash_new($_POST["topic_id"]) : 0;
        }
        
        //商城直播那边传过来的资源id
        if ( !empty($_POST['resource_id']) && is_numeric($_POST['resource_id']) && $_POST['resource_id']>0 )
        {
            $return['resource_id'] = $this->configutil->splash_new($_POST["resource_id"]) ? $this->configutil->splash_new($_POST["resource_id"]) : 0;
        }
        
        //是否走拼团路线，拼团标识_单独购买或团购_单独购买价格_团购价_活动id_团id
        if ( !empty($_POST['is_collage_product']) )
        {
            $return['is_collage_product_info'] = $_SESSION['is_collage_product_' . $this->user_id] = $this->configutil->splash_new($_POST["is_collage_product"]);
        } elseif ( !empty($_SESSION['is_collage_product_' . $this->user_id]) ) {
            $return['is_collage_product_info'] = $_SESSION['is_collage_product_' . $this->user_id];
        }
        
        //是否符合首次推广奖励
        if ( !empty($_POST["check_first_extend"]) )
        {
            $return['check_first_extend'] = $_SESSION['check_first_extend_' . $this->user_id] = $this->configutil->splash_new($_POST["check_first_extend"]);
        } elseif ( !empty($_SESSION['check_first_extend_' . $this->user_id]) ) {
            $return['check_first_extend'] = $_SESSION['check_first_extend_' . $this->user_id];
        }
        
        //预配送时间
        if ( !empty($_POST["delivery_time"]) )
        {
            $return['delivery_time'] = $_SESSION['delivery_time_' . $this->user_id] = $this->configutil->splash_new($_POST["delivery_time"]);
        } elseif ( !empty($_SESSION['delivery_time_' . $this->user_id]) ) {
            $return['delivery_time'] = $_SESSION['delivery_time_' . $this->user_id];
        }
        
        //选择送货时间返回的id和信息
        if ( !empty($_POST["sendtime_id"]) )
        {
            $return['sendtime_id'] = $_SESSION['sendtime_id_' . $this->user_id] = $this->configutil->splash_new($_POST["sendtime_id"]);
            $return['rtn_sendtime_array'] = $_SESSION['rtn_sendtime_array_' . $this->user_id] = explode(',', $this->configutil->splash_new($_POST["rtn_sendtime_array"]));

        } else {
            $return['sendtime_id']          = $_SESSION['sendtime_id_' . $this->user_id];
            $return['rtn_sendtime_array']   = $_SESSION['rtn_sendtime_array_' . $this->user_id];
        }
        
        //选择自定义区域返回的id和信息
        if ( !empty($_POST["diy_area_id"]) )
        {
            $return['diy_area_id'] = $_SESSION['diy_area_id_' . $this->user_id] = $this->configutil->splash_new($_POST["diy_area_id"]);
            $return['rtn_diy_area_array'] = $_SESSION['rtn_diy_area_array_' . $this->user_id] = explode(',', $this->configutil->splash_new($_POST["rtn_diy_area_array"]));
        } else {
            $return['diy_area_id']        = $_SESSION['diy_area_id_' . $this->user_id];
            $return['rtn_diy_area_array'] = $_SESSION['rtn_diy_area_array_' . $this->user_id];
        }
        
        //砍价活动数据
        if ( !empty($_POST['form_bargain_sz']) )
        {
            $return['form_bargain_sz'] = $_SESSION['form_bargain_sz'] = $_POST['form_bargain_sz'];
            $return['form_bargain_sz_data'] = $_SESSION['form_bargain_sz_data'] = $_POST['form_bargain_sz_data'];
        } elseif ( !empty($_SESSION['form_bargain_sz']) ) {
            $return['form_bargain_sz'] = $_SESSION['form_bargain_sz'];
            $return['form_bargain_sz_data'] = $_SESSION['form_bargain_sz_data'];
        }
        
        //众筹活动数据
        if ( !empty($_POST['form_crowdfund_sz_data']) )
        {
            $return['form_crowdfund_sz'] = $_SESSION['form_crowdfund_sz'] = $_POST['form_crowdfund_sz'];
            $return['form_crowdfund_sz_data'] = $_SESSION['form_crowdfund_sz_data'] = $_POST['form_crowdfund_sz_data'];
        } elseif ( !empty($_SESSION['form_crowdfund_sz_data']) ) {
            $return['form_crowdfund_sz'] = $_SESSION['form_crowdfund_sz'];
            $return['form_crowdfund_sz_data'] = $_SESSION['form_crowdfund_sz_data'];
        }
        
        //满赠换购活动
        if ( !empty($_REQUEST['platform_price']) )
        {
            $return['price'] = $_REQUEST['platform_price'];
        }
        // 非地址页面||优惠券页面进入清除$_SESSION
        if ( !$_REQUEST['aid'] && !$_REQUEST['cid'] && !$_REQUEST['eid'] )
        {
            unset($_SESSION['exchange_' . $this->user_id]);
        }
        
        /**	$is_active_product 活动产品字符串 类型拼接活动id  $act_type_$act_id
            $pro_act_id		活动编号
            $pro_act_type	10~19 拼团
            $pro_act_type	20~29 积分   21商城送积分 22兑换活动  23门店送积分
            $pro_act_type	30~39 限购
            $pro_act_type	40~49 砍价
            $pro_act_type	50~59 众筹

        **/

        if ( !empty($_POST['is_active_product']) )
        {
            $is_active_product = $_POST['is_active_product'];
            $active_product = explode('_', $is_active_product);
            $return['act_type'] = $active_product[0]; 
            $return['act_id'] = $active_product[1];
            
            if( $active_product[0] == 22 && (int)$active_product[1] > 0 ){  
                
                $sql = "SELECT only_type FROM " . WSY_SHOP . ".integral_activity WHERE isvalid=1 AND status =1 AND act_id=".$active_product[1];
                
                $return['only_type'] = $this->db->getOne($sql);
                
            }
        }
        
        //判断自提产品 2018.2.24
        if(!empty($pid_arr)){
            $sql = "SELECT sendstyle_pickup FROM " . DB_NAME . ".weixin_commonshops WHERE isvalid=true AND customer_id='{$this->customer_id}'";
            $sendstyle_pickup = $this->db->getOne($sql);
            $return['is_pickup'] = $this->check_is_pickup($sendstyle_pickup,$pid_arr);
        }

        return $this->requestData = $return;
        
    }
    
    //商城设置 
    public function shop_setting()
{
    $return = array(
        //支付方式开关
        'is_alipay' => false,           //支付宝支付开关
        'is_weipay' => false,           //微信支付开关
        'is_tenpay' => false,           //财付通开关
        'is_allinpay' => false,         //通联支付开关
        'is_payChange' => false,        //零钱支付开关
        'is_pay' => false,              //暂不支付开关
        'isdelivery' => false,          //代付开关
        'iscard' => false,              //会员卡支付开关
        'isshop' => false,              //到店支付开关
        'is_payother' => false,         //找人代付开关
        'is_paypal' => false,           //paypal支付开关
        'isOpenCurrency' => false,      //购物币支付开关
        'is_yeepay' => false,           //易宝支付开关
        'is_jdpay' => false,            //京东支付开关

        //商城基本设置
        'shop_name' => '',              //商城名称
        'issell' => 0,                  //是否开启分佣
        'issell_model' => 1,            //复购开关
        'init_reward' => 0,             //分佣比例
        'is_identity' => 0,             //是否开启身份证验证
        'is_uploadidentity' => 0,       //是否上传身份证附件
        'is_coupon' => 0,               //是否开启优惠券
        'sendstyle_express' => 1,       //是否开启配送方式快递
        'sendstyle_pickup' => 0,        //是否开启配送方式自提
        'shop_card_id' => -1,           //商城所用到的会员卡
        'is_ban_use_coupon_currency' => 0,//是否禁止同时使用购物币和优惠券
        'total_is_Pinformation' => 0,   //必填信息总开关
        'is_shop_deductible' => 0,      //是否使用店铺奖励抵扣
        'is_extension_deductible' => 0, //是否使用推广奖励抵扣
        'isOpenCurrency' => 0,          //购物币开关
        'custom' => '购物币',           //购物币自定义名称
        'currency_percentage_t' => 1,   //全局购物币抵扣比例
        'ispay_on'          => 0,                //货到付款开关
        'shop_integral_onoff'  => 0,    //商城积分开关
        'store_integral_onoff' => 0,    //门店积分开关
        'using_exchange_type'  => 1,     //正在使用的兑换类型 1商城  2门店
        'is_orderingretail_store' => 0, //是否显示订货系统门店
        'is_orderingretail' => 0,       //是否开启订货系统
        'integral_only_type'   => -1,    //兑换活动限定类型   -1不限 1商城 2门店
        'default_distribution_type' => 0, //默认配送方式（商城提交订单页），快递0，自提1
    );

    //查询支付方式开关
    $sql = "SELECT is_alipay, is_tenpay, is_weipay, is_pay, is_payChange, is_allinpay, isdelivery, iscard, isshop, is_payother, is_paypal, is_yeepay, is_jdpay FROM " . DB_NAME . ".customers WHERE isvalid=true AND id='{$this->customer_id}'";
    $res = $this->db->getRow($sql);
    foreach ( $res as $key => $val )
    {
        $return[$key] = $val;
    }

    //商城基本设置
    $sql = "SELECT name AS shop_name, issell, init_reward, is_identity, is_ban_use_coupon_currency, is_coupon, sendstyle_express, sendstyle_pickup, shop_card_id, is_uploadidentity, issell_model FROM " . DB_NAME . ".weixin_commonshops WHERE isvalid=true AND customer_id='{$this->customer_id}'";
    $res = $this->db->getRow($sql);
    foreach ( $res as $key => $val )
    {
        $return[$key] = $val;
    }

    //商城基本设置拓展表
    $sql = "SELECT is_Pinformation AS total_is_Pinformation, is_shop_deductible, is_extension_deductible, is_pay_on_delivery AS ispay_on FROM " . WSY_SHOP . ".weixin_commonshops_extend WHERE isvalid=true AND customer_id='{$this->customer_id}'";
    $res = $this->db->getRow($sql);
    foreach ( $res as $key => $val )
    {
        $return[$key] = $val;
    }

    //不开启复购则取消3级分销奖励和店铺奖励抵扣
    if ( $return['issell_model'] != 2 )
    {
        $return['is_shop_deductible'] = 0;
        $return['is_extension_deductible'] = 0;
    }

    //购物币开关
    $sql = "SELECT isOpen AS isOpenCurrency, custom FROM " . WSY_SHOP . ".weixin_commonshop_currency WHERE customer_id='{$this->customer_id}'";
    $res = $this->db->getRow($sql);
    foreach ( $res as $key => $val )
    {

        $return[$key] = $val;
    }

    //全局购物币折扣比例
    $sql = "SELECT percentage FROM " . WSY_SHOP . ".currency_percentage_t WHERE isvalid=true AND type=1 AND customer_id='{$this->customer_id}'";
    $res = $this->db->getOne($sql);
    //  if ( $res ) $return['currency_percentage_t'] = $res;
    $return['currency_percentage_t'] = $res;

    //查询是否开启商城积分 或者 门店积分
    $shop_integral_onoff  = 0;			//商城
    $store_integral_onoff = 0;			//门店
    $using_exchange_type  = 1;			//正在使用的兑换类型 1商城  2门店
    $sql = "SELECT shop_onoff AS shop_integral_onoff,store_json ,basic_json, store_onoff AS store_integral_onoff FROM " . WSY_SHOP . ".integral_setting WHERE cust_id='{$this->customer_id}'";
    $res = $this->db->getRow($sql);
    foreach ( $res as $key => $val )
    {
        $return[$key] = $val;
    }
    $shop_set 	= json_decode($return["basic_json"],TRUE);
    $store_set  = json_decode($return["store_json"],TRUE);

    $shop_int_name  = !empty($shop_set['integral_name'])?  $shop_set['integral_name']  :'商城积分';
    $store_int_name = !empty($store_set['integral_name'])? $store_set['integral_name'] :'门店积分';

    $return['shop_int_name'] = $shop_int_name;
    $return['store_int_name'] = $store_int_name;



    if ( !empty($_COOKIE['using_exchange_type_' . $this->user_id]) )
    {
        $return['using_exchange_type'] = ($_COOKIE['using_exchange_type_' . $this->user_id]);
    }

    //避免旧记录出错 强行转换
    if ( $return['shop_integral_onoff'] == 0 && $return['using_exchange_type'] == 1 )
    {
        $return['using_exchange_type'] = 2;
    }else if( $return['store_integral_onoff'] == 0 && $return['using_exchange_type'] == 2 ){
        $return['using_exchange_type'] = 1;
    }

    //积分3.0  该兑换活动，限定某种兑换类型  only_type数据库记录为商城1 门店2 默认-1

    if( $this->requestData['only_type'] == 1 || $this->requestData['only_type'] == 2){
        $return['using_exchange_type'] = $this->requestData['only_type'];
        $return['integral_only_type']  = $this->requestData['only_type'];
        if( ($only_type == 1 && $return['shop_integral_onoff'] == 0) || ($only_type == 2 && $return['store_integral_onoff'] == 0) ){         //活动错误
            echo "<script>
                        alert('该兑换活动产品有误');
                        window.location.replace('".Protocol.$_SERVER['HTTP_HOST']."/weixinpl/mshop/order_cart.php?customer_id={$this->customer_id_en}');
                        history.go(0);
                     </script>";
            exit;
        }
    }

    //是否开启订货系统门店模式
    $sql = "SELECT a.isopen_shop, b.isopen_proxy,b.default_distribution_type FROM " . WSY_DH . ".orderingretail_shop_setting a 
                LEFT JOIN orderingretail_setting b ON a.customer_id = b.customer_id 
                WHERE a.customer_id='{$this->customer_id}' AND b.isvalid = TRUE";
    $res = $this->db->getRow($sql);
    $return['is_orderingretail'] = $res["isopen_proxy"] ? 1 : 0;
    if ($return['is_orderingretail']){
        $return['is_orderingretail_store'] = $res["isopen_shop"] ? 1 : 0;
        $return['default_distribution_type'] = $res["default_distribution_type"] ? 1 : 0;
    }

    return $return;

}

    //用户数据
    public function user_data()
    {
        $return = array(
            'user_currency' => 0,                           //用户购物币数量
            'check_pay_password' => -1,                     //是否已设置了支付密码，1：是，-1：否
            'promoter_id' => -1,
            'promoter_isvalid' => 0,                        //推广员身份是否有效，1：有效，0：无效
            'user_is_consume' => 0,                         //店铺身份等级
            'user_commision_level' => 0,                    //推广员身份等级
            'term_of_validity' => '3000-01-01 00:00:00',    //推广员身份有效期
            'usingStr' => '',                               //正在使用的优惠券
        );
        
        //推广员信息
        $sql = "SELECT id AS promoter_id, is_consume AS user_is_consume, commision_level AS user_commision_level, term_of_validity FROM " . WSY_PUB . ".promoters WHERE user_id ='{$this->user_id}' AND customer_id='{$this->customer_id}' AND isvalid=true AND status=1";
        $res = $this->db->getRow($sql);
        foreach ( $res as $key => $val )
        {
            $return[$key] = $val;
        }
        
        if ( $return['promoter_id'] > 0 && strtotime($return['term_of_validity']) >= time() )
        {
            $return['promoter_isvalid'] = 1;
        }
        
        //用户购物币
        $return['user_currency'] = $this->get_currency();
        
        //查询正在使用的优惠券
        $sql = "SELECT couponusers_id FROM " . DB_NAME . ".weixin_commonshop_coupon_using WHERE isvalid=true AND customer_id='{$this->customer_id}' AND user_id='{$this->user_id}'";
        $res = $this->db->getAll($sql);
        $usingStr = '';
        foreach ( $res as $val )
        {
            $usingStr .= $val['couponusers_id'] . ",";
        }
        if ( $usingStr ) $return['usingStr'] = substr($usingStr, 0, strlen($usingStr)-1);
        
        //是否已经设置了支付密码
        $return['check_pay_password'] = $this->show_pay_way->check_pay_password($this->customer_id, $this->user_id);
        
        return $return;
    }
    
    //获取收货地址
    public function getAddress()
    {
        $return = array(
            'add_keyid' => -1,
            'is_change_address' => 0,
        );
        
        if ( !empty($_GET["aid"]) )
        {
            $add_keyid = $this->configutil->splash_new($_GET["aid"]);
            $_SESSION['aid_' . $this->user_id] = $add_keyid;
            $return['is_change_address'] = 1;
            
        } elseif ( !empty($_SESSION['aid_' . $this->user_id]) ) {
            $add_keyid = $_SESSION['aid_' . $this->user_id];
            
        }
        
        //查询地址，没有地址id则查默认地址
        if ( $add_keyid > 0 )
        {
            $sql = "SELECT id, name, phone, address, location_p, location_c, location_a FROM " . WSY_SHOP . ".weixin_commonshop_addresses WHERE isvalid=true AND user_id='{$this->user_id}' AND id='{$add_keyid}'";
            
        } else {
            $sql = "SELECT id, name, phone, address, location_p, location_c, location_a FROM " . WSY_SHOP . ".weixin_commonshop_addresses WHERE isvalid=true AND user_id='{$this->user_id}' AND is_default=1";
        }
        
        if ( $res = $this->db->getRow($sql) )
        {
            $address = array(
                'add_keyid'     => $res['id'],
                'add_name'      => htmlspecialchars($res['name']),  //收货人名字
                'add_phone'     => $res['phone'],   //收货人电话
                'address'       => htmlspecialchars(str_replace(array("\r\n", "\r", "\n"), '', $res['address']), ENT_QUOTES),
                'location_p'    => $res['location_p'],  //省
                'location_c'    => $res['location_c'],  //市
                'location_a'    => $res['location_a'],  //区
                'address_str'   => "{$res['location_p']} {$res['location_c']} {$res['location_a']} {$res['address']}",  //详细地址  
                'address_detail'=> addslashes(str_replace(array("\r\n", "\n"), '', "{$res['location_p']}{$res['location_c']}{$res['location_a']}{$res['address']}"))
            );
            
            //8.2.2_P2版本对地址库进行了更新，需要判断以前添加的区域是否需要更新，然后提示用户重新设置，否则将影响区域奖励模式
            $area_arr = [];
            $sql = "SELECT ID FROM " . WSY_PUB . ".address WHERE Name='{$address['location_p']}' AND LevelType=1 limit 1";
            if ( !$this->db->getOne($sql) )
            {
                $area_arr[] = $address['location_p'];
            }
            
            $sql = "SELECT ID FROM " . WSY_PUB . ".address WHERE Name='{$address['location_c']}' AND LevelType=2 limit 1";
            if ( !$this->db->getOne($sql) )
            {
                $area_arr[] = $address['location_c'];
            }
            
            $sql = "SELECT ID FROM " . WSY_PUB . ".address WHERE Name='{$address['location_a']}' AND LevelType=3 limit 1";
            if ( !$this->db->getOne($sql) )
            {
                $area_arr[] = $address['location_a'];
            }
            
            $address['area_str'] = implode(',', $area_arr);
            
            
            //获取身份证信息
            $sql = "SELECT identity, identityimgt, identityimgf FROM " . DB_NAME . ".weixin_commonshop_identity_info WHERE isvalid=true AND user_id='{$this->user_id}' AND name='{$address['add_name']}'";
            
            $res = $this->db->getRow($sql);
            $identity = array(
                'identity'      => $res['identity'],        //收货人身份证
                'identityimgt'  => $res['identityimgt'],    //收货人身份证正面图片
                'identityimgf'  => $res['identityimgf']     //收货人身份证反面图片
            );
            // echo $sql;

            $identity['sum_ID_tf'] = ((empty($identity['identityimgt'])) ? 0 : 1)	+  ((empty($identity['identityimgf']) ? 0 : 1));

            $return = array_merge($return, $address, $identity);
        }
        
        return $return;
        
    }
    
    //自定义区域
    public function diy_area($location_p='', $location_c='', $location_a='', $address)
    {
        $return = array(
            'is_diy_area' => 0,
            'default_diy_area_id' => -1,
            'default_diy_area_id_f' => -1,
            'default_diy_areaname' => '',
            'default_diy_areaname_f' => '',
        );
        
        $sql = "SELECT is_diy_area FROM " . WSY_SHOP . ".weixin_commonshop_team WHERE isvalid=true AND customer_id='{$this->customer_id}'";
        $is_diy_area = $this->db->getOne($sql);
        
        if ( $is_diy_area && !empty($location_p) )
        {
            $return['is_diy_area'] = 1;
            $diy_areaname_length = 0;
            
            $sql = "SELECT id, areaname FROM " . WSY_USER . ".weixin_commonshop_team_area WHERE isvalid=true AND customer_id='{$this->customer_id}' AND grade=3 AND all_areaname like '{$location_p}{$location_c}{$location_a}%'";
            $res = $this->db->getAll($sql);
            foreach ( $res as $key => $val )
            {
                $return['default_diy_area_id_f'] = $val['id'];
                $return['default_diy_areaname_f'] = $val['areaname'];
                
                //条件1：收货地址含有自定义地址；条件2：取自定义地址长度最长的
				if ( strpos($return['default_diy_areaname_f'], $address) !== false && strlen($return['default_diy_areaname_f']) > $diy_areaname_length )
                {
					$return['default_diy_area_id'] = $return['default_diy_area_id_f'];
					$return['default_diy_areaname'] = $return['default_diy_areaname_f'];
					$diy_areaname_length = strlen($return['default_diy_areaname_f']);
				}
            }
        }
        
        return $return;
    }
    
    //获取用户购物币数量
    public function get_currency()
    {
        $sql = "SELECT currency FROM " . WSY_SHOP . ".weixin_commonshop_user_currency WHERE isvalid=true AND customer_id='{$this->customer_id}' AND user_id='{$this->user_id}'";
        $currency = $this->db->getOne($sql);
        
        if ( !$currency ) $currency = 0;
        return $currency = cut_num($currency, 2);
    }
    
    //是否虚拟产品
    public function is_virtual($pid)
    {
        $is_virtual = 0;
        
        if ( !empty($pid) && $pid > 0 )
        {
            $sql = "SELECT is_virtual FROM " . WSY_PROD . ".weixin_commonshop_products WHERE id='{$pid}'";
            $is_virtual = $this->db->getOne($sql);
            
            $_SESSION['is_virtual_' . $this->user_id] = $is_virtual;
        } elseif ( !empty($_SESSION['is_virtual_' . $this->user_id]) ) {
            $is_virtual = $_SESSION['is_virtual_' . $this->user_id];
        }
        
        if ( $is_virtual )
        {
            return 1;
        } else {
            return 0;
        }
        
    }
    
    //是否首次奖励产品
    public function is_first_extend($pid)
    {
        
        $sql = "SELECT is_first_extend FROM " . WSY_PROD . ".weixin_commonshop_products WHERE id='{$pid}'";
        
        if ( $this->db->getOne($sql) )
        {
            return 1;
        } else {
            return 0;
        }
    }
    
    //拼团活动
    public function collage($is_collage_product_info)
    {
        $return = array(
            'is_collage_product' => 0,  //拼团标识
            'group_buy_type' => 0,      //1：单独购买，2：团购
            'single_price' => 0,        //单独购买价格
            'group_price' => 0,         //团购价
            'activitie_id' => -1,       //活动id
            'group_id' => -1,           //团id
            'is_use_free_coupon' => 0,  //是否可以使用团长免单券，1是，0否
            'if_bbt' => 0,              //是否抱抱团，1是，0否
            'if_bbt_curr_use' => 0,     //抱抱团团长首次开团能否使用购物币，1可以，0不可以
            'join_bbt_times' => 0,      //用户开团次数（同一个抱抱团活动）
            'collage_type' => 0,        //拼团类型
            'shopcode_onoff' => 0,      //拼团购物币开关
            'shopcode_limit' => 1,      //拼团能用购物币的身份，1-仅团长 2-仅团员 3-团长和团员
            'shopcode_precent' => 0,    //拼团购物币抵购比例
            'coupon_onoff' => 0,        //拼团优惠券开关
        );
        if ( $is_collage_product_info )
        {
            $collage_product = explode("_", $is_collage_product_info);
            $return['is_collage_product'] = $collage_product[0];
            
            //拼团产品
            if ( $return['is_collage_product'] > 0 )
            {
                $return['group_buy_type']   = $collage_product[1];
                $return['single_price']     = $collage_product[2];
                $return['group_price']      = $collage_product[3];
                $return['activitie_id']     = $collage_product[4];
                $return['group_id']         = $collage_product[5];
                
                //只有开团才能使用团长免单券
                if ( $return['group_buy_type'] == 2 && $return['group_id'] < 0 )
                {
                    $return['is_use_free_coupon'] = 1;
                }
                
                //查找拼团后台设置
                $condition = array(
                    'customer_id' => $this->customer_id,
                    'isvalid' => true,
                    'id' => $return['activitie_id']
                );
                $field = " type, if_curr_pay, shopcode_onoff, shopcode_limit, shopcode_precent, coupon_onoff ";
                $activity_info = $this->collageActivities->getActivitiesMes($condition, $field)['data'][0];
                $return['shopcode_onoff']   = $activity_info['shopcode_onoff'];
                $return['shopcode_limit']   = $activity_info['shopcode_limit'];
                $return['shopcode_precent'] = $activity_info['shopcode_precent'];
                $return['coupon_onoff']     = $activity_info['coupon_onoff'];
                $return['collage_type']     = $activity_info['type'];
                
                //抱抱团
                if ( $return['collage_type'] == 5 )
                {
                    $return['if_bbt'] = 1;
                    //用户用户开团次数
                    $condition = " ccot.activitie_id='{$return['activitie_id']}' AND ccot.user_id='{$this->user_id}' AND ccot.customer_id='{$this->customer_id}' AND ccot.is_head=1 AND ccot.isvalid=true ";
                    $field = " count(ccot.id) as join_bbt_times ";
                    $user_ccot_info = $this->collageActivities->get_crew_order($condition, $field)['batchcode'][0];
                    $return['join_bbt_times'] = $user_ccot_info['join_bbt_times'];
                    
                    //用户首次参与该抱抱团活动
                    if ( $return['join_bbt_times'] == 0 )
                    {
                        //抱抱团设置：团长首次开团可使用购物币
                        if ( $return['group_buy_type'] == 2 && $return['group_id'] < 0 && $return['collage_type'] == 5 && $activity_info['if_curr_pay'] == 2 )
                        {
                            $return['if_bbt_curr_use'] = 1;
                        }
                    }
                }
            }
        }
        
        return $return;
    }
    
    //满赠活动
    public function exchange($price)
    {
        if ( $price == '' )
        {
            return;
        }
        
        $exchange_list = count(json_decode($_SESSION['exchange_' . $this->user_id])) ? $_SESSION['exchange_' . $this->user_id] : $_REQUEST['exchange'];
        $exchange_list = json_decode($exchange_list);
        if ( count($exchange_list) > 0 )
        {
            foreach ( $exchange_list as $key => $value )
            {
                $pid = $value[1][0];
                $exchange_id = $value[1][8];
                $threshold = $this->exchange_m->get_exchange_threshold($exchange_id);
                if ( $price < $threshold )
                {
                    $del[] = $key;
                }
            }
            
            foreach ( $del as $key => $value )
            {
                $exchange_lists = array_splice($exchange_list, $value+1, 1);
            }
            
            $_REQUEST['exchange'] = json_encode($exchange_lists);
            $_SESSION['exchange_' . $this->user_id] = json_encode($exchange_lists);
        }
    }
    
    //货到付款
    public function pay_on_delivery($buy_all_data)
    {
        $pids = [];
        foreach ( $buy_all_data as $key => $val )
        {
            foreach ( $val as $k => $v )
            {
                if ( $v['pid'] )
                {
                    $pids[] = $v['pid'];
                }
            }
        }
        
        $sql = "SELECT id FROM " . WSY_SHOP . ".pay_on_delivery_products_t WHERE pid IN(" . implode(',', $pids) . ") AND isvalid=1 LIMIT " . count($pids);
        $res = $this->db->getAll($sql);
         //array_unique防止同一个产品的不同属性一起购买时，不同货到付款
        if ( count($res) == count(array_unique($pids) ) )
        {
            return 1;
        } else {
            return 0;
        }
    }

    //自提产品判断     2018.2.24
    public function check_is_pickup($sendstyle_pickup,$pid_arr){
        if($sendstyle_pickup==1 && !empty($pid_arr)){
            
            $sql = "select is_pickup from weixin_commonshop_products where isvalid=1 and id IN(" . implode(',', $pid_arr) . ") ";
            $res = $this->db->getAll($sql);

            foreach($res as $key=>$value){
                if(in_array(0,$value)){
                    return 0;
                }
            }

            return 1;
        }else{
            return 0;
        }
    }

}

