<?php

/*
* 详情页性能优化
* 1. 如果存在模板缓存就直接使用，否则重新缓存
* 2. 每次操作 商城系统 -> 商城设置 -> 个性化设置 -> 【首页模板/首页装修/自定义模板】 三个模块的表单提交时，清除模板缓存文件
* $Author: 痴心绝对 $
* 2018-04-8  $
*/

header("Content-type: text/html; charset=utf-8");
require('../config.php');
require('./product_detail_model.php');

