<?php

class my_data{
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
        //return $promoter_id;
        return ($promoter_id==0) ? 2 : 1;
    }








/**
 * 计算团队订单
 * @param  $user_id       -- 需要统计的用户id；
 * @param  $level         -- 级数（统计自己下面多少级）
 * @param  $consume       -- 身份 0：全部(包括粉丝)；1：普通推广员；2：代理；3：渠道；4：总代理；5：股东（最高级）
 * @param  $startime      -- 统计订单的时间段（开始时间）
 * @param  $endtime       -- 统计订单的时间段（结束时间）
 * @return $num           -- 返回返回一个数值
 * @author dongqiao//2016-10-15
 */
    public function CountTeamOrder($user_id,$level,$consume,$startime,$endtime){

        $generation = 1;//自己的代数
        $query      = "SELECT generation FROM weixin_users WHERE isvalid=TRUE AND id = $user_id LIMIT 1";
        $result     = _mysql_query($query) or die('Query failed owm_data_19 function CountTeamOrder: ' . mysql_error());
        while( $row = mysql_fetch_object($result)){
            $generation = $row->generation;
        }

        //商城订单
        $query = "  SELECT
                        COUNT(DISTINCT o.batchcode) AS num
                    FROM weixin_users AS w
                    left JOIN weixin_commonshop_orders AS o ON o.user_id=w.id ";

        //大礼包订单
        $query_lb = "SELECT
                        COUNT(DISTINCT k.batchcode) AS nums
                    FROM weixin_users AS w
                    LEFT JOIN package_order_t AS k ON k.user_id = w.id ";

        //添加等级//如果需要计算推广员或以上等级
        if(!empty($consume) && $consume > 0){
            $query = $query." LEFT JOIN promoters AS p ON w.id=p.user_id WHERE p.status = 1 AND p.isvalid = true AND ";
            $query_lb = $query_lb." LEFT JOIN promoters AS p ON w.id=p.user_id WHERE p.status = 1 AND p.isvalid = true AND ";
            //添加等级
            switch ($consume) {
                case '1':   //1：普通推广员
                    $query = $query;
                    $query_lb = $query_lb;
                    break;
                case '2':   //2：代理
                    $query .= " p.is_consume >= 1 AND ";
                    $query_lb .= " p.is_consume >= 1 AND ";
                    break;
                case '3':   //3：渠道
                    $query .= " p.is_consume >= 2 AND ";
                    $query_lb .= " p.is_consume >= 2 AND ";
                    break;
                case '4':   //4：总代理
                    $query .= " p.is_consume >= 3 AND ";
                    $query_lb .= " p.is_consume >= 3 AND ";
                    break;
                case '5':   //5：股东（最高级）
                    $query .= " p.is_consume >= 4 AND ";
                    $query_lb .= " p.is_consume >= 4 AND ";
                    break;
            }
        }else{
            $query .= " where ";
            $query_lb .= " where ";
        }



        $query .= "
                        w.isvalid = TRUE
                    AND o.status = 1
                    AND o.sendstatus = 2
                    AND o.isvalid = TRUE
                    AND MATCH(w.gflag) AGAINST(',".$user_id."') ";

        $query_lb .= "
                        w.isvalid = TRUE
                    AND k.isvalid = TRUE
                    AND k.status = TRUE
                    AND k.paystatus = 1
                    AND MATCH(w.gflag) AGAINST(',".$user_id."') ";

        if( !empty($level) ){
            $query .= " AND w.generation <= ".($generation+$level);
            $query_lb .= " AND w.generation <= ".($generation+$level);
        }
        //订单时间段（开始时间）
        if( !empty($startime) ){
            $query .= " AND o.createtime>= ".$startime;
            $query_lb .= " AND o.createtime>= ".$startime;
        }
        //订单时间段（结束时间）
        if( !empty($endtime) ){
            $query .= " AND o.createtime<= ".$endtime;
            $query_lb .= " AND o.createtime<= ".$endtime;
        }
        // echo $query;
        $num = 0;
        $result = _mysql_query($query) or die('Query failed owm_data_43 function CountTeamOrder: ' . mysql_error());
        $result_lb = _mysql_query($query_lb) or die('Query failed owm_data_43 function CountTeamOrder: ' . mysql_error());
        $row = mysql_fetch_object($result);
        $row_lb = mysql_fetch_object($result_lb);
        $num = round($row->num,2);
        $num_lb = round($row_lb->nums,2);

        if($num == '' || $num == NULL){
            $num = 0;
        }

        if($num_lb == '' || $num_lb == NULL){
            $num_lb = 0;
        }

        $num = $num + $num_lb;

        return $num;

    }

/**
 * 计算团队销售额
 * @param  $user_id       -- 需要统计的用户id；
 * @param  $level         -- 级数（统计自己下面多少级）
 * @param  $consume       -- 身份 0：全部(包括粉丝)；1：普通推广员；2：代理；3：渠道；4：总代理；5：股东（最高级）
 * @return $num           -- 返回返回一个数值
 * @author dongqiao//2016-10-15
 */

    public function CountTeamManaged($user_id,$level,$consume){

        $generation = 1;//自己的代数
        $query      = "SELECT generation FROM weixin_users WHERE isvalid=TRUE AND id = $user_id LIMIT 1";
        $result     = _mysql_query($query) or die('Query failed owm_data_94 function CountTeamManaged: ' . mysql_error());
        while( $row = mysql_fetch_object($result)){
            $generation = $row->generation;
        }

        $query = "  SELECT DISTINCT
                        SUM(my.total_money) as total_money
                    FROM
                        weixin_users AS us
                    RIGHT JOIN my_total_money AS my ON us.id = my.user_id ";

                    /* my_total_money中已经算上了大礼包金额了，这里不需要再统计一边 */
      /*   //大礼包总金额
        $query_lb = "SELECT DISTINCT
                        SUM(t.totalprice) as total_money
                    FROM
                        weixin_users AS us
                    RIGHT JOIN package_order_t AS t ON us.id = t.user_id"; */

        //如果要根据身份查询
        if( !empty($consume) && $consume > 0){
            $query .= " RIGHT JOIN promoters AS p ON us.id=p.user_id WHERE p.status = 1 AND p.isvalid = true AND ";
            $query_lb .= " RIGHT JOIN promoters AS p ON us.id=p.user_id WHERE p.status = 1 AND p.isvalid = true AND ";
            switch ($consume) {
                case '1':
                    $query = $query;
                    //$query_lb = $query_lb;
                    break;
                case '2':
                    $query .= " p.is_consume >= 1 AND ";
                    //$query_lb .= " p.is_consume >= 1 AND ";
                    break;
                case '3':
                    $query .= " p.is_consume >= 2 AND ";
                   // $query_lb .= " p.is_consume >= 2 AND ";
                    break;
                case '4':
                    $query .= " p.is_consume >= 3 AND ";
                    //$query_lb .= " p.is_consume >= 3 AND ";
                    break;
                case '5':
                    $query .= " p.is_consume >= 4 AND ";
                    //$query_lb .= " p.is_consume >= 4 AND ";
                    break;
            }
        }else{
            $query .= " WHERE ";
            //$query_lb .= " WHERE ";
        }

        $query .= "
                        us.isvalid = TRUE
                    AND my.isvalid = TRUE
                    AND MATCH (us.gflag) AGAINST (',".$user_id."') ";

        /* $query_lb .= "
                        us.isvalid = TRUE
                    AND t.isvalid = TRUE
                    AND t.status = TRUE
                    AND t.paystatus = 1
                    AND MATCH (us.gflag) AGAINST (',".$user_id."') "; */

        //如果要根据代数查
        if( !empty($level) ){
            $query .= " AND  us.generation <= ".($generation+$level);
            //$query_lb .= " AND  us.generation <= ".($generation+$level);
        }

        $num    = 0;

        $result = _mysql_query($query) or die('Query failed owm_data_137 function CountTeamManaged: ' . mysql_error());
        //$result_lb = _mysql_query($query_lb) or die('Query failed owm_data_137 function CountTeamManaged: ' . mysql_error());
        $row    = mysql_fetch_object($result);
        //$row_lb    = mysql_fetch_object($result_lb);
        $num    = round($row->total_money,2);
        //$num_lb    = round($row_lb->total_money,2);
        if($num == '' || $num == NULL){
            $num = 0;
        }

        /* if($num_lb == '' || $num_lb == NULL){
            $num_lb = 0;
        }
        // echo $num.','.$num_lb;
        $num = $num + $num_lb; */

        return $num;

    }



    /*
    方法说明:统计下属各级身份人数
    参数说明：int $customer_id       -> 商家id
              int $user_id          -> 用户id
              int $grade            -> 等级：0：粉丝(包含推广员、股东等)、1：推广员、2:代理 3:渠道 4:总代理 5:（股东一级，最高）股东
              int $algebra          -> 用户下面几级(不限级传-1)
    */
    public function numberOfSubordinate($customer_id,$user_id,$grade,$algebra){
        $count      = 0;//统计的人数
        $sql        = "select count(u.id) as pcount from weixin_users u ";
        $sql_grade  =   "";
        $sql_p      =   " right join promoters p on u.id=p.user_id where p.isvalid=true and p.status=1 and ";
        switch( $grade ){
            case 1:  //推广员
                $sql .= $sql_p;
                break;
            case 2:  //代理
                $sql .= $sql_p." p.is_consume>=1 and ";
                break;
            case 3:  //渠道
                $sql .= $sql_p." p.is_consume>=2 and ";
                break;
            case 4:  //总代理
                $sql .= $sql_p." p.is_consume>=3 and ";
                break;
            case 5:  //股东
                $sql .= $sql_p." p.is_consume>=4 and ";
                break;
            default:
                $sql .= " where ";
                break;
        }

        $sql    .= " match(u.gflag) against (',".$user_id.",') and u.customer_id=".$customer_id." and u.isvalid=true ";
        $generation = 1;//用户代数
        if( $algebra > 0 ){
            $query = "select generation from weixin_users where id='".$user_id."'";
            $result2 = _mysql_query($query);
            while( $row2 = mysql_fetch_object( $result2 ) ){
               $generation = $row2 -> generation;
            }
            $sql .= " and u.generation<=".($generation+$algebra);
        }
        //echo $sql;
        $result = _mysql_query($sql);
        while( $row = mysql_fetch_object( $result ) ){
           $count = $row -> pcount;
        }
        return $count;
    }

    public function showCashback($customer_id,$user_id,$cashback,$cashback_r,$p_now_price){
        /*
        方法说明:查询返现金额，判断是否显示
        参数说明：int $customer_id       -> 商家id
                  int $user_id          -> 用户id
                  double $cashback      -> 返现的固定金额（空传-1）
                  double $cashback_r    -> 返现的比例（空传-1）
                  double $p_now_price   -> 金额（空传-1）
        */
        $display    = 0;//是否显示开关
        $status     = 0;//是否是推广员
        $cashback_m = 0;//返现金额
        $is_promoter = 1;//只有推广员显示返现与购物币开关
        $is_division = 1;//返现与购物币显示开关
        $sql = "select is_division,is_promoter from weixin_commonshops_extend where isvalid=true and customer_id=".$customer_id." limit 1";
        $result = _mysql_query($sql) or die('showCashback Query failed1: ' . mysql_error());
        while ($row = mysql_fetch_object($result)) {
            $is_promoter = $row->is_promoter;
            $is_division = $row->is_division;
        }

        if( $is_division == 1 and  $is_promoter == 1 and $user_id > 0 ){
            $sql = "select status from promoters where isvalid=true and user_id='".$user_id."'";
            $result = _mysql_query($sql) or die('showCashback Query failed2: ' . mysql_error());
            while($row = mysql_fetch_object($result)){
                $status     = $row->status;
            }
            if( $status == 1 ){
                $display = 1;
            }
        }elseif( $is_division ==1 and $is_promoter == 0 ){
            $display = 1;
        }

        if( $display == 1 ){
            $query = "select cb_condition,cashback,cashback_r from weixin_commonshop_cashback where isvalid=true and customer_id=".$customer_id." limit 0,1";
            $result = _mysql_query($query) or die('L39: '.mysql_error());
            $cb_condition   = 0;//0固定金额;1产品价格按比例
            $cashback2      = 0;//固定金额
            $cashback_r2    = 0;//产品价格按比例
            while($row = mysql_fetch_object($result)){
                $cb_condition   = $row->cb_condition;
                $cashback2      = $row->cashback;
                $cashback_r2    = $row->cashback_r;
            }
            if( $cb_condition == 0 ){
                /* if( empty( $cashback ) ){
                    $cashback   = $cashback2;
                } */
                if( $cashback == -1 ){
                    $cashback   = $cashback2;
                }
                $cashback_m = $cashback;
            }elseif( $cb_condition == 1 ){
                /* if( empty( $cashback_r ) ){
                    $cashback_r = $cashback_r2;
                } */
                if( $cashback_r == -1 ){
                    $cashback_r     = $cashback_r2;
                }
                $cashback_m = $p_now_price * $cashback_r;
            }
            $cashback_m = round($cashback_m,2);
            /* if($cashback_m<=0){
                $display = 0;
            } */
        }
        //echo "aa=".$cashback_r;
        //echo "ff=".$cashback_m;
        $info = array();
        $info['display']    = $display;
        $info['cashback_m'] = $cashback_m;
        return $info;
    }

    public function my_total_profiy_money($customer_id,$user_id){
        /*
        方法说明:查询总待返佣金
        参数说明：int $customer_id -> 商家id
                  int $user_id     -> 用户id
        */
        $total_profiy = 0;//总返佣
        $query = "SELECT SUM(reward) as total_profiy FROM weixin_commonshop_order_promoters WHERE isvalid=true and customer_id=".$customer_id." and user_id=".$user_id." and paytype=0";
        $result= _mysql_query($query);//查询总返佣
        while($row=mysql_fetch_object($result)){
            $total_profiy = $row->total_profiy;
            $total_profiy = cut_num($total_profiy,2);//调用utitliy_fun的方法
        }
        return $total_profiy;
    }
    public function my_total_commission_money($customer_id,$user_id){
        /*
        方法说明:查询总返佣
        参数说明：int $customer_id -> 商家id
                  int $user_id     -> 用户id
        */
        $total_money = 0;//总返佣
        $query = "SELECT SUM(reward) as total_money FROM weixin_commonshop_order_promoters WHERE isvalid=true and customer_id=".$customer_id." and user_id=".$user_id."  and paytype in (1,3) ";
        $result= _mysql_query($query);//查询总返佣
        while($row=mysql_fetch_object($result)){
            $total_money = $row->total_money;
            $total_money = cut_num($total_money,2);//调用utitliy_fun的方法
        }
        return $total_money;
    }

    public function my_team_count($customer_id,$user_id){
        /*
        方法说明:查询团队总人数 
        参数说明：int $customer_id -> 商家id
                  int $user_id     -> 用户id
        */

        $pcount = 0;
        $query1 = "SELECT count(1) as pcount FROM weixin_users u left join promoters p on u.id=p.user_id where  u.isvalid=true and match(u.gflag) against (',".$user_id.",') and p.customer_id=".$customer_id." group by u.id";//团队总人数
        $result1 = _mysql_query($query1) or die('my_team_count() L21 Query failed: ' . mysql_error());
        while ($row = mysql_fetch_object($result1)) {
            $pcount = $row->pcount;
        }
        return $pcount;
    }





    public function completed_order($customer_id,$user_id){
            /*
            方法说明:个人已完成订单金额
            参数说明：$customer_id -> 商家id
                      $user_id     -> 用户id
            */
            $totalprice = 0;
            if( 0 < $customer_id and 0 < $user_id){
                $query="select sum(totalprice) as totalprice from weixin_commonshop_orders where  status=1 and isvalid=true and user_id=".$user_id;
                $result = _mysql_query($query) or die('w15 Query failed: ' . mysql_error());
                while ($row = mysql_fetch_object($result)) {
                   $totalprice = $row->totalprice;
                }
            }
            $totalprice = round($totalprice,2);
            return $totalprice;
    }

    public function cumulative_order($customer_id,$user_id){
            /*
            方法说明:个人累积订单金额
            参数说明：$customer_id -> 商家id
                      $user_id     -> 用户id

            条件:
            1:支付状态(paystatus)       : 已支付
            2:发货状态(sendstatus)      ：已发货或已收货
            3:退货状态(return_status)   : 0. 未退货；1：退货成功；-1：退货失败；2：同意退货；3：驳回请求；4：确认退货；5:用户已退货；
                                          6：商家确认收货；7：商家已发货；8：同意退款；9：驳回退款

            */
            $totalprice = 0;
            if( 0 < $customer_id and 0 < $user_id){
                $query="select sum(totalprice) as total_money from weixin_commonshop_orders where isvalid=true and customer_id=".$customer_id." and user_id =".$user_id." and paystatus=1  and sendstatus<3 and return_status in(0,3,9)";
                $result = _mysql_query($query) or die('w30 Query failed: ' . mysql_error());
                while ($row = mysql_fetch_object($result)) {
                   $totalprice = $row->total_money;//个人消费金额；
                }
            }

            $totalprice = round($totalprice,2);
            return $totalprice;
    }


    public function get_personal_data($customer_id,$user_id){
        /*
        此方法来获取个人基本信息
        参数说明：$customer_id -> 商家id
                  $user_id     -> 用户id
         */
        $account            = '';   //绑定的账号/手机
        $balance            = 0;    //钱包余额
        $weixin_name        = '';   //自己的名字
        $parent_name        = '';   //上级的名字
        $total_money        = 0;    //总消费金额
        $remain_score       = 0;    //会员卡积分
        $weixin_headimgurl  = '';   //微信头像
        $query = "SELECT u.weixin_name,
                         p.weixin_name as parent_name,
                         s.account,
                         u.weixin_headimgurl,
                         m.balance,
                         SUM(o.totalprice) AS total_money
                         FROM weixin_users u
                         LEFT JOIN weixin_users p
                         ON u.parent_id=p.id
                         LEFT JOIN system_user_t s
                         ON u.id=s.user_id
                         LEFT JOIN moneybag_t m
                         ON s.user_id=m.user_id
                         LEFT JOIN weixin_commonshop_orders o
                         ON m.user_id=o.user_id
                         WHERE o.isvalid=TRUE
                         AND o.sendstatus<3
                         AND o.return_status IN(0,3,9)
                         AND u.id=".$user_id;
        $result = _mysql_query($query);
        while( $row = mysql_fetch_object($result) ){
            $weixin_name        = $row->weixin_name;
            $parent_name        = $row->parent_name;
            $account            = $row->account;
            $weixin_headimgurl  = $row->weixin_headimgurl;
            $balance            = $row->balance;
            $total_money        = $row->total_money;
            if($balance==NULL){
                $balance = 0;
            }
            if($total_money==NULL){
                $total_money = 0;
            }
        }
        //查询会员卡积分
        $query = "SELECT remain_score FROM weixin_card_member_scores where card_member_id=(SELECT id FROM weixin_card_members WHERE user_id=".$user_id." AND card_id=(SELECT shop_card_id FROM weixin_commonshops WHERE customer_id=".$customer_id." limit 1))";
        $result = _mysql_query($query);
        while( $row = mysql_fetch_object($result) ){
            $remain_score = $row->remain_score;
        }

        $info = array();
        $info['account']                = $account;
        $info['balance']                = $balance;
        $info['weixin_name']            = $weixin_name;
        $info['parent_name']            = $parent_name;
        $info['total_money']            = $total_money;
        $info['remain_score']           = $remain_score;
        $info['remain_score']           = $remain_score;
        $info['weixin_headimgurl']      = $weixin_headimgurl;

        return $info;

    }

    public function myteam_data($user_id,$customer_id,$start,$end){
        /*
        此方法来获取个人团队基本信息
        参数说明：$customer_id -> 商家id
                  $user_id     -> 用户id
                  $start       ->读取数据头行
                  $end         ->读取数据尾行
         */

        $query = "SELECT distinct u.id,u.fromw,u.weixin_headimgurl,u.weixin_name,u.parent_id,u.createtime,p.isAgent,p.is_consume FROM weixin_users u left join promoters p on u.id=p.user_id where  u.isvalid=true and match(u.gflag) against (',".$user_id.",') and p.customer_id=".$customer_id." limit ".$start.",".$end;
        $result = _mysql_query($query) or die('Query failed1: ' . mysql_error());
        $p_id              = 0;//id
        $fromw             = 0;//来源
        $weixin_headimgurl = "";//头像
        $user_name         = "";//姓名
        $parent_id         = 0;//上级ID
        $sq_time           = "";//加入时间
        $isAgent           = 0;//是否区域代理
        $is_consume        = 0; //是否为股东
        $parent_name       = 0; //上级姓名
        $i                 = 0;//循环参数
        $array             = array();
        while ($row = mysql_fetch_object($result)) {
            $p_id              = $row->id;
            $fromw             = $row->fromw;
            $weixin_headimgurl = $row->weixin_headimgurl;
            $user_name         = $row->weixin_name;
            $parent_id         = $row->parent_id;
            $sq_time           = $row->createtime;
            $isAgent           = $row->isAgent;
            $is_consume        = $row->is_consume;
            $i++;

            $sql = "SELECT weixin_name from weixin_users where isvalid=true and id=".$parent_id;//获取上级姓名
            $result1 = _mysql_query($sql) or die('Query failed2: ' . mysql_error());
            while ($row1 = mysql_fetch_object($result1)) {
                $parent_name = $row1->weixin_name;
            }

            $info = array(//每一次循环把信息放进数组
                "id"=>$i,
                "p_id"=>$p_id,
                "fromw"=>$fromw,
                "weixin_headimgurl"=>$weixin_headimgurl,
                "user_name"=>$user_name,
                "parent_name"=>$parent_name,
                "sq_time"=>$sq_time,
                "isAgent"=>$isAgent,
                "is_consume"=>$is_consume
            );

            array_push($array,$info);//存入二维数组里
        }

        return $array;
    }

        public function Agent_name($customer_id){
            /*
            此方法来获取个人团队成员区域身份
            参数说明：$customer_id -> 商家id
            */
            $is_showcustomer = -1;  //是否显示区域代理名称
            $a_customer      = "";  //区代名称
            $c_customer      = "";  //市代名称
            $p_customer      = "";  //省代名称
            $is_diy_area     = -1;  //是否显示自定义名称
            $diy_customer    = "";  //自定义名称
            //我的团队成员区域身份
            $query = "select is_showcustomer,a_customer,c_customer,p_customer,is_diy_area,diy_customer from weixin_commonshop_team where isvalid=true and customer_id=".$customer_id;
            $result = _mysql_query($query) or die('Query failed2: ' . mysql_error());
            while ($row = mysql_fetch_object($result)) {
                $is_showcustomer = $row->is_showcustomer;
                $a_customer      = $row->a_customer;
                $c_customer      = $row->c_customer;
                $p_customer      = $row->p_customer;
                $is_diy_area     = $row->is_diy_area;
                $diy_customer    = $row->diy_customer;
            }

            $info = array();
            $info['is_showcustomer'] = $is_showcustomer;
            $info['a_customer']      = empty($a_customer)?"区代":$a_customer;
            $info['c_customer']      = empty($c_customer)?"市代":$c_customer;
            $info['p_customer']      = empty($p_customer)?"省代":$p_customer;
            $info['is_diy_area']     = $is_diy_area;
            $info['diy_customer']    = empty($diy_customer)?"自定义区代":$diy_customer;
            return $info;
        }

        public function promoter_name($customer_id){
        /*
        此方法来获取个人团队成员推广员身份
        参数说明：$customer_id -> 商家id
         */
        $query="select level,exp_name from weixin_commonshop_commisions where isvalid=true and customer_id=".$customer_id." order by level asc";
        $result = _mysql_query($query) or die('Query failed: ' . mysql_error());
        $exp_name = "";//推广员自定义名称
        $level     = -1;//等级
        $info = array();
        $level_arry = array("一级","二级","三级","四级","五级","六级","七级","八级");//若某等级自定义名字为空，则填充相应的等级
        $arr = array();
        while ($row = mysql_fetch_object($result)) {
            $level    = $row->level;
            $exp_name = $row->exp_name;
            if($exp_name==""){//若某等级自定义名字为空，则填充相应的等级
                $exp_name = $level_arry[$i];
            }
            $info[$level] = $exp_name;
        }
            return $info;
        }

        public function team_member($persion_id,$customer_id){
            /*
            此方法来获取团队成员资料
            参数说明：$persion_id -> 查询成员id
                      $customer_id -> 商家id
         */
            //$query = "SELECT u.name,u.weixin_name,us.weixin_name AS parent_name,u.phone,u.weixin_headimgurl,u.sex,u.qq,u.birthday,u.province,u.city,s.occupation,s.wechat_id,s.wechat_code FROM weixin_users u LEFT JOIN weixin_users us ON u.parent_id=us.id LEFT JOIN promoters p ON p.user_id=u.id LEFT JOIN system_user_t s ON u.id=s.user_id WHERE u.isvalid=true AND u.id=".$persion_id;

            $query ="SELECT u.name,u.weixin_name,u.phone,u.weixin_headimgurl,u.sex,u.qq,u.birthday,u.province,u.city,u.parent_id,
            s.occupation,u.wechat_id,s.wechat_code
            FROM weixin_users u
            left JOIN  weixin_users_extends s ON u.id=s.user_id
            WHERE u.isvalid=true  AND
             u.id=".$persion_id." limit 1";

            //echo $query;
             $result = _mysql_query($query) or die('Query failed1: ' . mysql_error());
                $name              = "";//姓名
                $sex               = "";//性别
                $phone             = "";//电话
                $qq                = "";//qq
                $birthday          = "";//生日
                $weixin_name       = "";//微信名
                $weixin_headimgurl = "";//微信头像
                $isAgent           = 0;//代理身份
                $is_consume        = -1;//是否为股东
                $ident_num         = 0;//身份数字
                $ident             = "";//身份名字
                $total_money       = 0;//消费金额
                $occupation        = "";//职业
                $p_id              = 0;//推广员id
                $wechat_id         = '';//微信号
                $wechat_code       = '';//微信二维码
                $parent_id         = '';//上级
                $commision_level   = '';//推广员等级
                while ($row = mysql_fetch_object($result)) {
                    $name              = $row->name;
                    $sex               = $row->sex;
                    $qq                = $row->qq;
                    $phone             = $row->phone;
                    $weixin_headimgurl = $row->weixin_headimgurl;
                    $weixin_name       = $row->weixin_name;
                    $birthday          = $row->birthday;
                    $province          = $row->province;
                    $parent_name       = $row->parent_name;
                    $city              = $row->city;
                    $occupation        = $row->occupation;
                    $wechat_id         = $row->wechat_id;
                    $wechat_code       = $row->wechat_code;
                    $parent_id         = $row->parent_id;



                    $sql1 = "SELECT id,isAgent,is_consume,commision_level from promoters where isvalid=true and user_id=".$persion_id." and customer_id=".$customer_id;
                    $result1 = _mysql_query($sql1) or die('Query failed2: ' . mysql_error());
                    while ($row1 = mysql_fetch_object($result1)) {
                        $p_id       = $row1->id;
                        $isAgent    = $row1->isAgent;
                        $is_consume = $row1->is_consume;
                        $commision_level = $row1->commision_level;
                        break;
                    }

                    $sql2 = "SELECT total_money FROM my_total_money WHERE isvalid=true AND user_id=".$persion_id." LIMIT 1";
                    $result2 = _mysql_query($sql2) or die('sql2 failed23: ' . mysql_error());
                    while( $row2 = mysql_fetch_object($result2) ){
                        $total_money = cut_num($row2->total_money,2);
                    }
                    if($total_money==''){
                        $total_money = 0.00;
                    }

                    //查询推荐人名字
                    $parent_name = '';
                    $sql3 = "select weixin_name from weixin_users where isvalid=true and id=".$parent_id."";
                    $result3 = _mysql_query($sql3) or die('Query failed3: ' . mysql_error());
                    while ($row3 = mysql_fetch_object($result3)) {
                        $parent_name        = $row3->weixin_name;
                        break;
                    }

                    //查钱包余额--------start
                }
                    $ident = array();
                    $ident_num = array();
                    if($p_id>0){
                        $exp_name = $this->promoter_name($customer_id);
                         $ident[1] = $exp_name[$commision_level];
                         $ident_num[1] = 2;
                        if($is_consume>0){
                            $query = "select a_name,b_name,c_name,d_name from weixin_commonshop_shareholder where isvalid=true and customer_id=".$customer_id;
                                $result = _mysql_query($query) or die('Query failed: ' . mysql_error());
                                require($_SERVER['DOCUMENT_ROOT'].'/weixinpl/mshop/shareholder_name.php');
                                $shareholder_name = new shareholder_name_class();
                                $s_name = $shareholder_name->s_name;
                                $a_name = "";//一级自定义名称
                                $b_name = "";//二级自定义名称
                                $c_name = "";//三级自定义名称
                                $d_name = "";//四级自定义名称
                                while ($row = mysql_fetch_object($result)) {
                                    $a_name = empty($row->a_name)?$s_name['a_name']:$row->a_name;
                                    $b_name = empty($row->b_name)?$s_name['b_name']:$row->b_name;
                                    $c_name = empty($row->c_name)?$s_name['c_name']:$row->c_name;
                                    $d_name = empty($row->d_name)?$s_name['d_name']:$row->d_name;
                                }
                            $ident_num[2] = 1;
                            if(1==$is_consume){
                                $ident[2] = $d_name;
                            }elseif(2==$is_consume){
                                $ident[2] = $c_name;
                            }elseif(3==$is_consume){
                                $ident[2] = $b_name;
                            }elseif(4==$is_consume){
                                $ident[2] = $a_name;
                            }
                        }
                        if(5==$isAgent||6==$isAgent||7==$isAgent||8==$isAgent){//区域代理
                             $ident[3] = "区域代理";
                             $ident_num[3] = 0;
                        }elseif(1==$isAgent){
                             $ident[3] = "代理商";
                             $ident_num[3] = 5;
                        }elseif(3==$isAgent){
                             $ident[3] = "供应商";
                             $ident_num[3] = 4;
                        }elseif(4==$isAgent){
                             $ident[3] = "技师";
                             $ident_num[3] = 3;
                        }
                    }
                if($qq == ""){
                    $qq = "未填写";
                }
                if($birthday == ""){
                    $birthday = "未填写";
                }

                
             //手机号码为空的时候，看看绑定的手机号码,用于打电话啊
            if ($phone=="" || $phone==0) {
               $que_phone ="select account from system_user_t where user_id=$persion_id";
                $result = _mysql_query($que_phone) or die('Query failed2: ' . mysql_error());
                while ($row = mysql_fetch_object($result)) {
                    $q_phone       = $row->account;
                }
              $phone = $q_phone;
            }

                

            $info = array();
            $info['name']              = $name;
            $info['sex']               = $sex;
            $info['phone']             = $phone;
            $info['qq']                = $qq;
            $info['weixin_headimgurl'] = $weixin_headimgurl;
            $info['weixin_name']       = $weixin_name;
            $info['birthday']          = $birthday;
            $info['isAgent']           = $isAgent;
            $info['province']          = $province;
            $info['city']              = $city;
            $info['is_consume']        = $is_consume;
            $info['parent_name']       = $parent_name;
            $info['total_money']       = $total_money;
            $info['occupation']        = $occupation;
            $info['wechat_id']         = $wechat_id;
            $info['wechat_code']       = $wechat_code;
            $info['ident']             = $ident;
            $info['ident_num']         = $ident_num;
            $info['p_id']              = $p_id;

            return $info;
        }



        /*
        * @功能  获取用户消费总额
        * @param  int   $user_id          ： 用户id
        * @return array $result           ： 返回信息
        */
    public function getUserTotalConsumption($user_id){
        _file_put_contents($_SERVER['DOCUMENT_ROOT']."/weixinpl/common/log/utlilty_getUserTotalConsumption_" . $today . ".txt", "\r\n============".date("Y-m-d H:i:s")."=============\r\n",FILE_APPEND);
        _file_put_contents($_SERVER['DOCUMENT_ROOT']."/weixinpl/common/log/utlilty_getUserTotalConsumption_utlilty_getUserTotalConsumption_" . $today . ".txt", "user_id=======".var_export($user_id,true)."\r\n",FILE_APPEND);
        $result = array();

        if($user_id<0 or !is_numeric($user_id)){
            $result["errcode"] = 50000;
            $result["errmsg"]  = 'error user_id';
            return  $result;
        }

        $result["errcode"]     = 0;
        $result["errmsg"]      = 'success';
        $result["money"]       = 0;

        //个人消费总额
        $consume_money = 0;
        $query_total = "SELECT total_money FROM my_total_money WHERE isvalid=true AND user_id=$user_id LIMIT 1";

        _file_put_contents($_SERVER['DOCUMENT_ROOT']."/weixinpl/common/log/utlilty_getUserTotalConsumption_" . $today . ".txt", "query_total=======".var_export($query_total,true)."\r\n",FILE_APPEND);

        $result_total = _mysql_query($query_total) or errorResult($result,50001);
        while ($row = mysql_fetch_object($result_total)) {
            $consume_money   = $row->total_money;
            $result["money"] = $consume_money;
        }
        //个人消费总额

        _file_put_contents($_SERVER['DOCUMENT_ROOT']."/weixinpl/common/log/utlilty_getUserTotalConsumption_" . $today . ".txt", "consume_money=======".var_export($consume_money,true)."\r\n",FILE_APPEND);


        return $result;
    }

    /**
     * 计算排除前N位的团队销售额
     * @param  $user_id       -- 需要统计的用户id；
     * @param  $n             -- 前N位（排除前N位）
     * @param  $level         -- 级数（统计自己下面多少级）（亲密度）
     * @param  $consume       -- 身份 0：全部(包括粉丝)；1：普通推广员；2：代理；3：渠道；4：总代理；5：股东（最高级）
     * @return $num           -- 返回返回一个数值
     * @author  djy//2017-11-23
     */

    public function CountENTeamManaged($user_id,$n,$level,$consume){

        $generation = 1;//自己的代数
        $query      = "SELECT generation FROM weixin_users WHERE isvalid=TRUE AND id = $user_id LIMIT 1";
        $result     = _mysql_query($query) or die('Query failed owm_data_94 function CountTeamManaged: ' . mysql_error());
        while( $row = mysql_fetch_object($result)){
            $generation = $row->generation;
        }

        $query = "  SELECT
                        my.total_money,my.user_id
                    FROM
                        weixin_users AS us
                    RIGHT JOIN my_total_money AS my ON us.id = my.user_id ";

        //如果要根据身份查询
        if( !empty($consume) && $consume > 0){
            $query .= " RIGHT JOIN promoters AS p ON us.id=p.user_id WHERE p.status = 1 AND p.isvalid = true AND ";
            switch ($consume) {
                case '1':
                    $query = $query;
                    break;
                case '2':
                    $query .= " p.is_consume >= 1 AND ";
                    break;
                case '3':
                    $query .= " p.is_consume >= 2 AND ";
                    break;
                case '4':
                    $query .= " p.is_consume >= 3 AND ";
                    break;
                case '5':
                    $query .= " p.is_consume >= 4 AND ";
                    break;
            }
        }else{
            $query .= " WHERE ";
        }

        $query .= "
                        us.isvalid = TRUE
                    AND my.isvalid = TRUE
                    AND MATCH (us.gflag) AGAINST (',".$user_id."') ";


        //如果要根据代数查
        if( !empty($level) ){
            $query .= " AND  us.generation <= ".($generation+$level);
        }

        $user_totalmoney_arr = array();//用户-销售额 数组

        //echo $query;
        //echo $query_lb;


        $result = _mysql_query($query) or die('Query failed owm_data_137 function CountTeamManaged: ' . mysql_error());
        while( $row = mysql_fetch_object($result)){
            $total_money = $row->total_money;
            $user_id1 = $row->user_id;

            $info = array(//每一次循环把信息放进数组
                "user_id"=>$user_id1,
                "total_money"=>$total_money
            );
            array_push($user_totalmoney_arr,$info);//存入二维数组里
        }

        /* 
        *
        *没有大礼包，不需要这段了
        *
        $user_totalmoney_arr2 = array();//用户-销售额 数组2
        $user_arr = array();//用户数组
        //数组合并，把user_id相同的数据，合并在一起
        foreach($user_totalmoney_arr AS $key => $value){

            if(in_array($value['user_id'],$user_arr)){//
                foreach($user_totalmoney_arr2 AS $key2 => $value2){//定位
                    if($value['user_id']==$value2['user_id']){
                        $user_totalmoney_arr2[$key2]['total_money'] += $value['total_money'];//同一个user，金额统计
                    }
                }
            }else{
                array_push($user_arr,$value['user_id']);
                array_push($user_totalmoney_arr2,$value);//没此用户，插入用户数据
            }


        } */


        //重新排序数组，按销售额倒序排列
        $sort = array(
               'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
               'field'     => 'total_money',       //排序字段
        );
       $arrSort = array();
       foreach($user_totalmoney_arr AS $uniqid => $row){
           foreach($row AS $key=>$value){
              $arrSort[$key][$uniqid] = $value;
           }
       }
       if($sort['direction']){
           array_multisort($arrSort[$sort['field']], constant($sort['direction']), $user_totalmoney_arr);
       }

       $num = 0;//排除前n位的销售额

       $arr_count = count($user_totalmoney_arr);

       if($arr_count==0 || $arr_count<$n){//数组为空，或数组少于要排除的数量
           $num = 0;
       }else{
           foreach($user_totalmoney_arr AS $k => $v){
               if($k>=$n){//前n位不统计
                  $num = $num + $v['total_money'];
               }
           }
       }
       return $num;

    }






}

?>
