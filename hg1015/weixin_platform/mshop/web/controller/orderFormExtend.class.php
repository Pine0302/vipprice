<?php

//下单拓展类
class orderFormExtend
{
    //数据库类
    public $db;
    
    public $customer_id;
    public $user_id;
    public $fromtype;
    /***********************pine************************************/
    public $checkIsPromoter;
    /***********************pine************************************/

    function __construct($customer_id, $user_id, $fromtype)
    {
        $this->customer_id = $customer_id;
        $this->user_id = $user_id;
        $this->fromtype = $fromtype;
        /***********************pine************************************/
        $this->checkIsPromoter = $this->checkIsPromoter($user_id);
        /***********************pine************************************/
        require_once ROOT_DIR . 'mp/database.php';
        $this->db = \DB::getInstance();
    }

    
    //重组数组
    public function remake($fromtype, $data, $type)
    {
        if ( $fromtype == 1 )   //立即购买
        {
            if ( $type == 1 )   //自定义键名
            {
                $return[$data[0]][] = array(
                    'pid'               => $data[1][0],
                    'prvalues'          => $data[1][1],
                    'rcount'            => $data[1][2],
                    'live_room_id'      => $data[1][3],
                    'is_collage_product'=> $data[1][4],
                    'act_type'          => $data[1][5],
                    'act_id'            => $data[1][6],
                    'exchange_id'       => $data[1][7],
                    'exchange'          => $data[1][8],
                    'form_bargain_sz'   => $data[1][9],
                    // 'form_crowdfund_sz'   => $data[1][10],
                    'pro_yundian_id'    => $data[1][10],
                );
            } else {
                $return[$data[0]][] = array($data[1][0], $data[1][1], $data[1][2], 1, 2, '', $data[1][3], '', $data[1][5], $data[1][6], $data[1][7], $data[1][8],$data[1][10]);//价格，属性，数量，随便填外面会赋值，随便填，随便填，商城直播房间id，随便填，活动类型，活动编号，赠送活动编号，是否为赠送商品，所属的云店ID
            }
                
            
        } else {    //购物车结算
            foreach ( $data as $key => $val )
            {
                if ( $type == 1 )   //自定义键名
                {
                    $return[$val[0]][] = array(
                        'pid'           => $val[1][0],
                        'prvalues'      => $val[1][1],
                        'rcount'        => $val[1][2],
                        'live_room_id'  => $val[1][4],
                        'act_type'      => $val[1][6],
                        'act_id'        => $val[1][7],
                        'exchange_id'   => $val[1][8],
                        'exchange'      => $val[1][9],
                        'pro_yundian_id' => $val[2],
                    );
                } else {
                    $return[$val[0]][] = array($val[1][0], $val[1][1], $val[1][2], 1, 2, 3, $val[1][4], '', $val[1][6], $val[1][7], $val[1][8], $val[1][9],$val[2]);    //价格，属性，数量，随便填外面会赋值，随便填，随便填，商城直播房间id，随便填，活动类型，活动编号，赠送活动编号，是否为赠送商品 ，所属的云店ID
                }
                
            }
            
        }
        
        return $return;
    }
    
    //重组订单数据
    public function remake_order_data($buy_data, &$buy_data_express, $extend_data)
    {
        $return = [];
        $del_exchange = [];
        $curr_arr = [];
        $platform_price = 0;
        
        /*佣金抵扣第一步 开始*/
        $model_selfbuy_reward = new model_selfbuy_reward();
        $model_selfbuy_reward->selfbuycal_new_first($this->customer_id);
        $user_self_reward = 0;   //自购奖励金额
        $user_shareholder_reward = 0;   //店铺奖励金额
        /*佣金抵扣第一步 结束*/
        
        foreach ( $buy_data as $supply_id => $products )
        {
            $supply_order_currency = 0; //单张订单可抵扣购物币数量
            $tax_i = 0 ;
            $tax_sum_allproduct_price = 0;  //初始化每个供应商产品总价
            $selfreward = 0;
            $sum_curr = 0;                  //每个供应商的可抵扣购物币
            $supply_order_data = array();
            $express_supply_array = array();    //每个供应商所有产品的运费规则
            $supply_express_price = 0;          //每个供应商的运费
            $temp_revenue = array();        //行邮税数组
            foreach ( $products as $key => $product_info )
            {

                //单个产品信息数组
                $product_data_per = [];
                $product_data_per['pid'] = $product_info['pid'];
                $product_data_per['is_supply_id'] = $supply_id;
                $product_data_per['rcount'] = $product_info['rcount'];
                $product_data_per['prvalues'] = $product_info['prvalues'];
                $product_data_per['pro_yundian_id'] = $product_info['pro_yundian_id'];
                
                //判断产品是否云店产品，防止跳过满赠活动产品检测
                $is_yundian = $this->get_product($product_info['pid'])['yundian_id'];
                if(empty($is_yundian)){
                    $is_yundian = -1;
                }
                //满赠活动产品检测
                if ( $product_info['exchange'] && $is_yundian == -1)
                {
                    $sql = "SELECT 
                                ep.storenum, ep.exchange_price, ep.num_per_person, ep.num_per_time, p.name, p.description, p.default_imgurl, p.id, p.propertyids, e.threshold 
                            FROM " . WSY_MARK . ".weixin_commonshop_exchange_products AS ep 
                            LEFT JOIN " . WSY_PROD . ".weixin_commonshop_products AS p ON p.id=ep.pid 
                            LEFT JOIN " . WSY_MARK . ".weixin_commonshop_exchange AS e ON e.id=ep.exchange_id 
                            WHERE 
                                ep.pid='{$product_info['pid']}' AND ep.isvalid=true AND p.isvalid=true AND ep.exchange_id='{$product_info['exchange_id']}'";
                    $res = $this->db->getRow($sql);
                    $now_exchange_price = $res['exchange_price'];
                    $threshold = $res['threshold'];
                    
                    if( $threshold > $platform_price ){
                        $del_exchange[] = $supply_id ;
                        continue;
                    }
                    $product_data_per['exchange']   = true;     
                    $product_data_per['threshold']  = $threshold;   
                    $max_threshold = $threshold > $max_threshold ? $threshold : $max_threshold;
                    
                }
                
                $supply_info = [];  //供应商信息
                $product_pros = []; //产品属性信息
                $pros_name_str = '';
                
                //获取产品信息
                $product_data = $this->get_product($product_info['pid']);
               // var_dump($product_info);
             //  var_dump($product_data);
                /**************************pine****************************************/
                if(($this->checkIsPromoter==1)&&(!empty($product_data['vip_price']))) {
                  //  $product_data['now_price']      = $product_info['vip_price'];    
                    $product_data['now_price']      = $product_data['vip_price'];    
                }else{
                  //   $product_data['now_price']      = $product_info['now_price']; 
                     $product_data['now_price']      = $product_data['now_price']; 
                }
                /**************************pine****************************************/
               
                //产品属性
                if ( !empty($product_info['prvalues']) )
                {
                    $pros_arr = explode('_', $product_info['prvalues']);
                    foreach ( $pros_arr as $_key => $_val )
                    {
                        if ( $_val == '' ) continue;
                        
                        $sql = "SELECT name, parent_id FROM " . WSY_PROD . ".weixin_commonshop_pros WHERE isvalid=true AND id='{$_val}'";
                        $res = $this->db->getRow($sql);
                        
                        $sql = "SELECT name FROM " . WSY_PROD . ".weixin_commonshop_pros WHERE isvalid=true AND id='{$res['parent_id']}'";
                        $res_p = $this->db->getRow($sql);
                        
                        $pros_name_str .= $res_p['name'] . ':' . $pros_v['parent_id'] . '  ';
                        $product_pros[] = array(
                            'child_id' => $_val,
                            'child_name' => $res['name'],
                            'child_parent_id' => $res['parent_id'],
                            'pro_parent_name' => $res_p['name']
                        );
                    }
                    
                    //属性产品信息
                    $sql = "SELECT weight, now_price, vip_price, need_score, cost_price, for_price FROM " . WSY_PROD . ".weixin_commonshop_product_prices WHERE product_id='{$product_info['pid']}' AND proids='{$product_info['prvalues']}'";
                    $res = $this->db->getRow($sql);
                    
                    if ( $res )
                    {
                        $product_data['weight']         = $res['weight'];
                        /**************************pine****************************************/
                        if(($this->checkIsPromoter==1)&&(!empty($res['vip_price']))) {
                            $product_data['now_price']      = $res['vip_price'];    
                        }else{
                             $product_data['now_price']      = $res['now_price']; 
                        }
                        /**************************pine****************************************/
                        $product_data['pros_need_score']= $res['need_score'];
                        $product_data['cost_price']     = $res['cost_price'];
                        $product_data['for_price']      = $res['for_price'];
                    }
                    
                }
                
                $product_data_per['pros'] = $product_pros;
                $product_data_per['pros_name_str'] = $pros_name_str;
                $buy_data_express[$supply_id][$key]['5'] = $pros_name_str;
                
                //计算重量
                $allWeights = $product_data['weight'] * $product_info['rcount'];
                $product_data_per['allWeights'] = $allWeights;
                
                //计算价格，不同活动产品现价不一样
                //满赠活动
                if ( $product_info['exchange'] ) $product_data['now_price'] = $now_exchange_price;
                //拼团活动
                if ( $extend_data['is_collage_product'] == 1 && $extend_data['group_buy_type'] == 2 ) $product_data['now_price'] = $extend_data['group_price'];
                //砍价活动
                if ( $product_info['form_bargain_sz'] ) $product_data['now_price'] = $product_info['form_bargain_sz'];
                //众筹活动
                if ( $_SESSION['form_crowdfund_sz_data'] ) {
                    if($_SESSION['form_crowdfund_sz']){
                        $product_data['now_price'] = $_SESSION['form_crowdfund_sz'];
                    }else{
                        $product_data['now_price'] = 0;
                    }
                }
                //限购活动
                if ( $product_info['act_type'] == 31 ) {
                    require_once($_SERVER['DOCUMENT_ROOT'].'/mshop/web/model/restricted_purchase.php');
                    $restricted_purchase = new \model_restricted_purchase();

                    $restricted_data = array(
                        'product_id' => $product_info['pid'],
                        'restricted_id' => $product_info['act_id'],
                        'customer_id' => $this->customer_id
                    );

                    $restricted_result = $restricted_purchase->findRestrictedPurchase($restricted_data);
                    
                    $now_time = date("Y-m-d H:i:s");
                    
                    //判断产品的限购的活动是否正在进行中
                    if ( $restricted_result['data']['isout'] == 1 && ($now_time >= $restricted_result['data']['time_start']) && ($now_time <= $restricted_result['data']['time_end']) )
                    {
                        //修改产品活动价格
                        $product_data['now_price'] = $restricted_result['data']['price'];
                        
                    }
                }
                
                $product_data_per = array_merge($product_data_per, $product_data);
                
                //总价
                $product_data_per['totalprice'] = round($product_info['rcount'] * $product_data['now_price'], 2);
                
                //计算产品可抵扣购物币数量，满赠产品不能抵扣购物币
                if ( $product_info['exchange'] == false && $product_info['pro_yundian_id'] == -1)
                {
                    //产品抵扣比例
                    $sql = "SELECT currency_percentage FROM " . WSY_PROD . ".commonshop_product_discount_t WHERE isvalid=true AND pid='{$product_info['pid']}'";
                    $res = $this->db->getOne($sql);
                    if ( !$res && $res != 0) $res = -1;
                    
                    $product_data['currency_percentage'] = $res < 0 ? $extend_data['currency_percentage_t'] : $res;
                    if ( $product_info['act_type'] != 22 )  //兑换产品不在这里计算
                    {
                        $sum_curr += $this->product_currency($extend_data['currency_percentage_t'], $res, $product_data_per['totalprice']);
                        $sum_curr = cut_num($sum_curr, 2);
                    }
                    
                }
                
                //供应商信息
                $shop_name = '';
                $brand_logo = '';
                $supply_apply_id = 0;
                $isbrand_supply = 0;
                if ( $product_data['is_supply_id'] > 0 && $product_info['pro_yundian_id'] == -1)    //供应商产品
                {
                    $sql = "SELECT id, shopName, isbrand_supply FROM " . WSY_SHOP . ".weixin_commonshop_applysupplys WHERE user_id='{$product_data['is_supply_id']}' AND isvalid=true";
                    $res = $this->db->getRow($sql);
                    $supply_apply_id= $res['id'];
                    $shop_name      = $res['shopName'];
                    $isbrand_supply = $res['isbrand_supply'];
                    
                    if ( $isbrand_supply )  //品牌供应商
                    {
                        $sql = "SELECT brand_name, brand_logo FROM " . WSY_SHOP . ".weixin_commonshop_brand_supplys WHERE user_id='{$product_data['is_supply_id']}' AND customer_id='{$this->customer_id}' AND isvalid=true";
                        $res = $this->db->getRow($sql);
                        $shop_name = $res['brand_name'];
                        $brand_logo = $res['brand_logo'];
                    }
                    
                }
                else if ( $product_info['pro_yundian_id'] != -1 && !empty($product_info['pro_yundian_id']) &&$product_info['pro_yundian_id'] >0)    //云店自营产品
                {
                    $sql = 'SELECT wyk.id,wyk.realname,wyk.contact_name,wyk.kepper_img,wu.weixin_name FROM '.WSY_USER.'.weixin_yundian_keeper as wyk left join '.WSY_USER.'.weixin_users as wu on wyk.user_id = wu.id where wyk.customer_id='.$this->customer_id.' and wyk.id ='.$product_info['pro_yundian_id'];
                    $res = $this->db->getRow($sql);

                    $supply_apply_id = $res['id'];

                    if(!empty($res['kepper_img'])){
                        $brand_logo = $res['kepper_img'];
                    }
                    
                    if(!empty($res['weixin_name'])){
                        $shop_name = $res['weixin_name']."的店铺";
                    }else if(!empty($res['contact_name'])){
                        $shop_name = $res['contact_name']."的店铺";
                    }else{
                        $shop_name = $res['realname']."的店铺";
                    }
                    
                }
                 else {    //平台产品
                    $shop_name = $extend_data['shop_name'];
                    $platform_price += $product_data['now_price'] * $product_info['rcount'];
                }
                $supply_info = array(
                    'supply_apply_id' => $supply_apply_id,
                    'shop_name' => $shop_name,
                    'brand_logo' => $brand_logo,
                    'isbrand_supply' => $isbrand_supply
                );
                
                $product_data_per['supply'] = $supply_info;
                
                //非免邮产品和非虚拟产品则计算运费
                if ( !$product_data['is_free_shipping'] && !$product_data['is_virtual'] && $product_info['pro_yundian_id'] == -1)
                {
                    $pro_express_temp = $this->pro_express_template($product_data['freight_id'], $product_data['express_type'], $product_data_per['totalprice'], $supply_id, $extend_data['location_p'], $extend_data['location_c'], $extend_data['location_a'], 1);
                    
                    $tem_id = $pro_express_temp[0];         //运费模板
                    $temp_product_express = array($tem_id, $product_data['weight'], $product_info['rcount'], $product_data_per['totalprice'], $product_data['express_type']);
                    
                    array_push($express_supply_array, $temp_product_express);
                }
                
                            
                /* 活动产品业务逻辑start */
                if ( $product_info['act_type'] == 22 )  //兑换产品
                {
                    //是兑换产品(满增活动)
                    $platform_price = $platform_price + $integral_result['data']['money_t'] - ( $product_data['now_price'] * $product_info['rcount'] );
                    $shop_integral_data = array(
                        'pid' => $product_info['pid'],
                        'rcount' => $product_info['rcount'],
                        'act_id' => $product_info['act_id'],
                        'percentage' => $product_info['pid'],
                        'currency_percentage_t' => $extend_data['currency_percentage_t'],
                        'currency_percentage' => $product_data['currency_percentage']
                    );
                    $return = array(
                        'product_data_per' => &$product_data_per,
                        'sum_curr' => &$sum_curr,
                        'platform_price' => &$platform_price
                    );
                    $integral_result = $this->shop_integral($shop_integral_data, $return);
                } elseif( $values['act_type'] == 31 ) {     //限购产品
                    $product_data_per['integral_p']         = 0;                                    
                    $product_data_per['integral_t']         = 0;
                    $product_data_per['store_integral_p']   = 0;                                    
                    $product_data_per['store_integral_t']   = 0;
                    $product_data_per['money_p']            = $product_data['now_price'];
                    $product_data_per['money_t']            = $product_data_per['totalprice'];
                } else {    //不是活动产品
                    $product_data_per['integral_p']         = 0;                                    
                    $product_data_per['integral_t']         = 0;
                    $product_data_per['store_integral_p']   = 0;                                    
                    $product_data_per['store_integral_t']   = 0;
                    $product_data_per['money_p']            = 0;
                    $product_data_per['money_t']            = 0;
                
                }
                /* 活动产品业务逻辑end */

                //获取行邮税类型名称
                $product_data_per['tax_name'] = get_tax_name($product_data['tax_type']);
                if ( $tax_i == 0 )
                {
                    $tax_compare_tax_type = $product_data['tax_type'];
                }
                
                if ( $product_data['tax_type'] > 1 )
                {
                    
                    if ( $tax_i == 0 || ($tax_i > 0 && $tax_compare_tax_type == $product_data['tax_type']) )
                    {
                        $pro_data = array(          
                            'pid' => $product_info['pid'],
                            'rcount' => $product_info['rcount'],
                            'pro_totalprice' => $product_data_per['totalprice'],
                            'tax_type' => $product_data['tax_type']
                        );                  
                            
                        array_push($temp_revenue, $pro_data);
                        
                    } else {    //当出现不同的税收类型则不计算税收
                        $temp_revenue = 'different_tax_type';
                    }   
                }
                
                $tax_i ++;
                $tax_sum_allproduct_price += $product_data_per['totalprice'];   //计算每个供应商的产品总价 
                
                /* 复购抵扣业务逻辑start */
                if ( $extend_data['issell'] == 1 && $extend_data['issell_model'] == 2 && $product_info['pro_yundian_id'] == -1)
                {
                    //计算订单佣金
                    $allcost_price      =  $product_data['cost_price'] * $product_info['rcount'];               //总供货价
                    $allfor_price       =  $product_data['for_price'] * $product_info['rcount'];                //总成本价
                    $cost_sell_price    =  $product_data_per['totalprice'] - $allcost_price;                    //最多能分的佣金， 总价减去总供货价
                    //没有产品比例就拿全局
                    if ( $product_data['pro_reward'] == -1 )
                    {
                        $product_data['pro_reward'] = $extend_data['init_reward'];
                    }
                    $s_t_consume_score  = $product_data['pro_reward'] * ($product_data_per['totalprice'] - $allfor_price);  //计算订单返佣总金：（总价-总成本价）*比例
                    if ( $s_t_consume_score > $cost_sell_price )
                    {
                        $s_t_consume_score = $cost_sell_price;                          //如果佣金大于最多能分的佣金，则以最多能分的佣金为准
                    }
                    
                    if ( $s_t_consume_score < 0 )
                    {
                        $s_t_consume_score = 0;
                    } else {                         
                        $s_t_consume_score = bcadd($s_t_consume_score, 0, 2);   //截取2位小数                     
                    }

                    $selfreward += $s_t_consume_score;
                }
                            
                /* 复购抵扣业务逻辑end */
                
                array_push($supply_order_data, $product_data_per);
            }
            
            /*复购抵扣业务逻辑start*/
            if ( $extend_data['issell'] == 1 && $extend_data['issell_model'] == 2 && $extend_data['promoter_isvalid'] == 1 )
            {
                if ( $selfreward <= 0 )
                {
                    $user_self_reward = 0;
                    $user_shareholder_reward = 0;
                } else {
                    $self_reward_data = [
                        'batchcode' => '',
                        'Plevel' => $extend_data['user_commision_level'],
                        'reward' => $selfreward,
                        'is_consume' => $extend_data['user_is_consume'],
                        'customer_id' => $this->customer_id,
                        'user_id' => $this->user_id,
                        'supply_id' => $supply_id,
                        'totalprice' => 0
                    ];
                    
                    $self_reward = $model_selfbuy_reward->selfbuycal_new_second($self_reward_data);
                    $user_self_reward       = $self_reward['data']['O_8reward'];
                    $user_shareholder_reward= $self_reward['data']['shareholder'];
                }
            
            }
            /*复购抵扣业务逻辑end*/
            
            $order_data[$supply_id] = $supply_order_data;
            
            $rtn_express_tem_arr = $this->pro_express_new($express_supply_array, $extend_data['location_p'], $extend_data['location_c'], $extend_data['location_a'], $supply_id);   //获取供应商产品的的最优快递
            
            if( $rtn_express_tem_arr != 'failed' )
            {   
                $shop = new shopMessage_Utlity();       
                //计算单个供应商的所有运费
                $supply_express_price = $shop->New_change_freight_direct($rtn_express_tem_arr, $this->customer_id, $supply_id);     
            }
            $new_supply_express[$supply_id][] = $rtn_express_tem_arr;                   //方便外部JS调用，用于合并购物车数据
            $order_data[$supply_id]['supply_express'][0] = 'no_use';
            $order_data[$supply_id]['supply_express'][1] = $supply_express_price;
            
            /****** 计算每个供应商的行邮税 start *****/
            $express_sum_totalprice = $tax_sum_allproduct_price +  $supply_express_price;
            $tax_money = 0;
            
            if ( $temp_revenue == 'different_tax_type' )
            {
                $tax_msg = "产品含有多种税收，无法提交订单";
                $tax_code = 24002;
            } elseif ( !empty($temp_revenue) ) {
                $user_id = $this->user_id;
                $customer_id = $this->customer_id;
                $fromtype = $this->fromtype;
                
                $tax = tax_pro_totalpirce($supply_express_price, $tax_sum_allproduct_price, $express_sum_totalprice, $temp_revenue, 1, '');
                $tax_money  = $tax[0];
                $tax_code   = $tax[2];
                $tax_msg    = $tax[3];
            }
            
            $order_data[$supply_id]['tax'][0] = 'no_use';
            $order_data[$supply_id]['tax'][1] = $tax_money;
            $order_data[$supply_id]['tax'][2] = $tax_code;
            $order_data[$supply_id]['tax'][3] = $tax_msg;
            /****** 计算每个供应商的行邮税 end *****/
            
            /****** 计算每个供应商的可抵扣购物币 start *****/
            $curr_arr[$supply_id] = $sum_curr;
            $order_data[$supply_id]['tax']['sum_curr'] = $sum_curr;
            
            /****** 计算每个供应商的可抵扣购物币 end *****/
            $order_data[$supply_id]['self_reward'][0]                          = 'no_use';
            $order_data[$supply_id]['self_reward']['Dreward']                  = $user_self_reward;          
            $order_data[$supply_id]['self_reward']['user_shareholder_reward']  = $user_shareholder_reward;
            $order_data[$supply_id]['self_reward']['new_supply_express']       = $new_supply_express;
        }
        //var_dump($order_data);exit;
        
        $return['buy_all_data'] = $order_data;
        $return['supply_express'] = $new_supply_express;
        $return['del_exchange'] = $del_exchange;
        $return['curr_arr'] = $curr_arr;
        $return['platform_price'] = $platform_price;
        
        return $return;
    }
    
    //获取产品信息
    public function get_product($pid)
    {
        $sql = "SELECT
                    name AS product_name, description AS product_description, orgin_price, now_price,vip_price, type_id, is_supply_id, weight, is_QR, default_imgurl AS imgurl, is_Pinformation, freight_id, express_type, pro_discount, is_identity AS p_is_identity, isvp, vp_score, is_virtual, need_score AS pros_need_score, is_free_shipping, donation_rate, is_invoice, tax_type, for_price, cost_price, pro_reward,yundian_id
                FROM " . WSY_PROD . ".weixin_commonshop_products WHERE id='{$pid}'";

        $res = $this->db->getRow($sql);
        
        return $res;
    }
    
    
    /*
     * 计算购物币抵扣
     * @param  $t  int  全局比例
     * @param  $p  int  产品比例
     * @param  $price  int  价格
     **/
    public function product_currency($t, $p, $price)
    {
        if( $p < 0 ){   //-1表示使用全局比例
            $p = $t;
        }
        
        $currency = $price * $p;  
        
        return $currency;
    }
    
    /*
     * 查找产品的快递模板或规则  PS:以首件费用最低为准
     * @param  $freight_id  int  产品绑定的快递模板
     * @param  $express_type  int  邮费计费方式:0没有选择，1按件数，2按按重量
     * @param  $totalprice  int  价格
     * @param  $is_supply_id  int  供应商ID
     * @param  $location_p  int  省
     * @param  $location_c  int  市
     * @param  $location_a  int  区
     * @param  $type  int  1返回快递模板 2返回快递规则ID
     **/
    public function pro_express_template($freight_id, $express_type, $totalprice, $is_supply_id=-1, $location_p, $location_c, $location_a, $type=2,$p_weight,$p_num)
    {
        $tem_id = -1;   //运费模板ID
        $select_express_id = -1;
        $pro_express_data = array(
            'select_express_id' => -1,
            'is_express' => -1,         //-1表示无配送方式，0表示有配送方式                
            'remark' => 'no_use'        //该运费模板未添加运费规则  
        );
        
        if ( $freight_id > 0 )  //大于0则选择了运费模板
        {
            $tem_id = $freight_id;
        } else {                //小于0则使用默认模板
            $sql = "SELECT id FROM " . WSY_SHOP . ".express_template_t WHERE isvalid=true AND is_default=1 AND customer_id='{$this->customer_id}' AND supply_id='{$is_supply_id}'";
            $res = $this->db->getOne($sql);
            
            if ( $res ) $tem_id = $res;
            
        }
        
        if ( $type == 1 ) //返回快递模板
        {
            return array($tem_id);
        } else {    //返回快递规则ID
            if ( $tem_id > 0 )
            {
                $sql = "SELECT express_id FROM " . WSY_SHOP . ".express_relation_t WHERE tem_id='{$tem_id}' AND customer_id='{$this->customer_id}' AND isvalid=true";
                $res = $this->db->getAll($sql);
                foreach ( $res as $val )
                {
                    $express_ids[] = $val['express_id'];
                }
                
                if ( $express_ids )
                {
                    $express_ids = implode(',', $express_ids);
                    $express_type_sql = '';
                    if ( $express_type > 0 )    //当选择了计费类型则假如条件中
                    {
                        $express_type_sql = " AND type=".$express_type." ";
                    }
                    //精确到区，在运费规则中找出最优的运费规则
                    if ( $is_supply_id > 0 )
                    {
                        $sql = "SELECT 
                                    id
                                FROM " . WSY_SHOP . ".weixin_expresses_supply 
                                WHERE 
                                    isvalid=true AND customer_id='{$this->customer_id}' AND ((is_include=0 AND (region LIKE '%".$location_p."%' AND (city_area LIKE '%".$location_c.'_'.$location_a."%' OR city_area='')) ) OR (is_include=1 AND ((region NOT LIKE '%".$location_p."%' and city_area='') OR (region NOT LIKE '%".$location_p."%' AND city_area NOT LIKE '%".$location_c.'_'.$location_a."%') OR (region LIKE '%".$location_p."%' AND city_area NOT LIKE '%".$location_c.'_'.$location_a."%' AND city_area!=''))) OR region='') AND cost<=".$totalprice." AND supply_id=".$is_supply_id.$express_type_sql." AND id IN(".$express_ids.") ";
                    } else {
                        $sql = "SELECT 
                                    id
                                FROM " . WSY_SHOP . ".weixin_expresses 
                                WHERE 
                                    isvalid=true AND customer_id='{$this->customer_id}' AND ((is_include=0 AND (region LIKE '%".$location_p."%' AND (city_area LIKE '%".$location_c.'_'.$location_a."%' OR city_area='')) ) OR (is_include=1 AND ((region NOT LIKE '%".$location_p."%' AND city_area='') OR (region NOT LIKE '%".$location_p."%' AND city_area NOT LIKE '%".$location_c.'_'.$location_a."%') OR (region LIKE '%".$location_p."%' AND city_area NOT LIKE '%".$location_c.'_'.$location_a."%' AND city_area!=''))) OR region='') AND cost<=".$totalprice." ".$express_type_sql." AND id IN(".$express_ids.") ";
                    }
					
                    /*new 在运费规则中找出最优的运费规则 start  修复CRM13210*/
                    
                    $supply_express_price_arr = array();//数组存储格式：数组[快递规则ID]：对应的邮费
                    
                    $res_all = $this->db->getAll($sql);
                    
                    if($res_all){
                        
                        foreach($res_all as $v){
                        
                           $new_rtn_express_tem_arr = array();//数组存储格式：数组[快递规则ID]：array(累计重量，累计件数)
                           $res_arr = array();
                           
                           $new_rtn_express_tem_arr[$v['id']] = array($p_weight,$p_num);
                            
                           array_push($res_arr, $new_rtn_express_tem_arr);
                           
                           $shop = new shopMessage_Utlity();        
                    
                           $supply_express_price = $shop->New_change_freight_direct($res_arr, $this->customer_id, $is_supply_id);

                           $supply_express_price_arr[$v['id']]   = $supply_express_price;      
                        }  
                    
                            /*找出运费最低邮费的规则*/
                    
                        $select_express_id = array_search(min($supply_express_price_arr),$supply_express_price_arr); 
                    }
                    
                    
                    /*new 在运费规则中找出最优的运费规则 end*/
                    
                    //$res = $this->db->getOne($sql);
                    //if ( $res ) $select_express_id = $res;
                    
                    if ( $select_express_id > 0 )
                    {
                        $pro_express_data = array(
                            'select_express_id' => $select_express_id,
                            'is_express' => 0,                              //-1表示无配送方式，0表示有配送方式                
                            'remark' => 'ok'                                //该运费模板未添加运费规则  
                        );
                    }else{  //无合适快递规则适用
                        $pro_express_data = array(
                            'select_express_id' => $select_express_id,
                            'is_express' => -1,                             //-1表示无配送方式，0表示有配送方式                
                            'remark '=> 'no_fit_express_rule_select'        //无合适运费规则适用
                        );
                    }
                    
                } else {    //运费模板没有快递规则
                    $pro_express_data = array(
                        'select_express_id' => -1,
                        'is_express' => -1,                             //-1表示无配送方式，0表示有配送方式                
                        'remark' => 'no_express_rule'                   //无合适运费规则适用
                    );
                }
            }
        }
        
        return array($tem_id, $select_express_id, $pro_express_data);
    }
    
    //产品的最优快递规则（累加按重，累计按件，累加产品金额）
    public function pro_express_new($express_array, $location_p, $location_c, $location_a, $is_supply_id){
    /*函数说明：最终返回各种不同的快递规则ID为key的数组
    express_array：[运费模板ID,单品重量,数量,产品总金额,邮费计费方式]

    */
        $express_array_count    = count($express_array);
        $tel_arr    = array();//组合成新的数组
        for ( $i = 0; $i < $express_array_count; $i++ )
        {
            $tem_id         = $express_array[$i][0];
            $Pweight        = $express_array[$i][1];
            $Pnum           = $express_array[$i][2];
            $totalprice     = $express_array[$i][3];
            $express_type   = $express_array[$i][4];
            
            if( array_key_exists( $tem_id,$tel_arr ) ){     //累加同一个模板下的重量，数量，产品金额                   
                $tel_arr[$tem_id][$express_type][0] = $tel_arr[$tem_id][$express_type][0] +( $Pweight * $Pnum );
                $tel_arr[$tem_id][$express_type][1] = $tel_arr[$tem_id][$express_type][1] + $Pnum;
                $tel_arr[$tem_id][$express_type][2] = $tel_arr[$tem_id][$express_type][2] + $totalprice;                                    
            }else{                                          //只计算一个模板下的重量，数量，产品金额
                $tel_arr[$tem_id][$express_type][0] = $Pweight * $Pnum ;
                $tel_arr[$tem_id][$express_type][1] = $Pnum;
                $tel_arr[$tem_id][$express_type][2] = $totalprice;          
            }                       
        }
        
        $result = array();
        //选出模板下的适用的最优快递规则
        foreach ( $tel_arr as $t_id => $tem_arr )   //遍历不同的运费模板
        {
            $tel_arr_tem_id = $t_id;    //模板ID      
            foreach ( $tem_arr as $key => $value )    //遍历同一个运费模板的不同模板类型
            {
                $rtn_array = array(); 
                $tel_arr_express_type       = $key;     
                $tel_arr_express_weight     = (float)$value[0];
                $tel_arr_express_num        = (float)$value[1];
                $tel_arr_express_totalprice = (float)$value[2];
                
                //计算出统计后的（按重，按件，金额）快递规则ID   
                $pro_express_template_data = $this->pro_express_template($tel_arr_tem_id, $tel_arr_express_type, $tel_arr_express_totalprice, $is_supply_id, $location_p, $location_c, $location_a,2,$tel_arr_express_weight,$tel_arr_express_num);
                
                $select_express_id = $pro_express_template_data[1];
                
                if ( $select_express_id > 0 )
                {
                    //数组格式：数组[快递规则ID]：array(累计重量，累计件数)
                    $rtn_array[$select_express_id] = array($tel_arr_express_weight, $tel_arr_express_num);                              
                } else {
                    //当有一个数组找不到快递规则，则退出循环并返回
                    $rtn_array = 'failed';          
                    return $rtn_array;  
                }
                
                array_push($result, $rtn_array);
                
            }
        }
        
        return $result;                      
        /*----配送方式----*/
    }
    
    //商城积分活动产品
    public function shop_integral($data, &$return_data)
    {
        require_once($_SERVER['DOCUMENT_ROOT'].'/mshop/web/model/integral.php');
        $model_integral = new \model_integral();
        
        extract($data);
        
        $integral_data  = [
            'pid' => $pid,
            'num' => $rcount,
            'type' => 2,
            'act_id' => $act_id,
            'customer_id' => $this->customer_id,
        ];
        
        $integral_result = $model_integral->cal_product_integral_per($integral_data);
        
        //单间产品的商城积分
        $integral_p = $integral_result['data']['integral_p'];
        //需要支付的商城积分     
        $integral_t = $integral_result['data']['integral_t'];   
        //单间产品的门店积分
        $store_integral_p = $integral_result['data']['store_integral_p'];
        //需要支付的门店积分     
        $store_integral_t = $integral_result['data']['store_integral_t'];   
        //单件产品需要支付的现价
        $money_p = $integral_result['data']['money_p']; 
        //需要支付的总金额（现价*数量）
        $money_t = $integral_result['data']['money_t']; 
        //满赠活动平台商品总价统计
        $return_data['platform_price'] += $integral_result['data']['money_t'];
        
        $return_data['product_data_per']['integral_p']      = $integral_p;                                  
        $return_data['product_data_per']['integral_t']      = $integral_t;
        $return_data['product_data_per']['store_integral_p']= $store_integral_p;                                    
        $return_data['product_data_per']['store_integral_t']= $store_integral_t;
        $return_data['product_data_per']['money_p']         = $money_p;
        $return_data['product_data_per']['money_t']         = $money_t;
        //重新覆盖产品现价
        $return_data['product_data_per']['now_price']       = $money_p;
        $return_data['product_data_per']['totalprice']      = $money_t;

        //重新计算购物币
        if ( $exchange == false )
        {
            $product_curr = $this->product_currency($currency_percentage_t, $currency_percentage, $money_t);
            $return_data['sum_curr'] +=  $product_curr;  
            $return_data['sum_curr']  =  cut_num($return_data['sum_curr'], 2);
        }
        return $integral_result;
    }


    /***********************pine************************************/
            //检测用户是否是推广员
    public function checkIsPromoter($user_id){
        $now = date("Y-m-d H:i:s");
        $query = "SELECT id,
                 isAgent,
                 is_consume,
                 status,
                 commision_level,
                 exp_map_url,
                 term_of_validity
          FROM promoters p 
          WHERE isvalid=TRUE AND status=1 AND user_id=".$user_id." and term_of_validity > '".$now."' LIMIT 1";
        $result = _mysql_query($query) or die('Query failed89: ' . mysql_error());
        $promoter_id = 0;
        while( $row = mysql_fetch_object($result) ){
            $promoter_id        = $row->id;
            $isAgent            = $row->isAgent;
            $is_consume         = $row->is_consume;
            $status             = $row->status;
            $exp_map_url        = $row->exp_map_url;    //推广二维码
            $commision_level    = $row->commision_level;
            $term_of_validity   = $row->term_of_validity;   //推广员有效期
            switch ($is_consume) {
                    case '0':
                        $pro_name = $exp_name;
                    break;

                    case '1':
                        $pro_name = $d_name;
                    break;

                    case '2':
                        $pro_name = $c_name;
                    break;

                    case '3':
                        $pro_name = $b_name;
                    break;

                    case '4':
                        $pro_name = $a_name;
                    break;
            }
        }
       
        return ($promoter_id==0) ? 2 : 1;
    }
    /***********************pine************************************/



}