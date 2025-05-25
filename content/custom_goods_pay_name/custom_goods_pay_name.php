<?php
function custom_goods_pay_name(&$goods, $order) {
    $plugin_path = ROOT_PATH . "content/custom_goods_pay_name/";
    $info = include_once "{$plugin_path}setting.php";
    $timestamp = time();
    $name = str_replace('<$out_trade_no>', $order['out_trade_no'], $info['name']);
    $name = str_replace('<$date>', date('Y-m-d', $timestamp), $name);
    $name = str_replace('<$time>', date('H:i:s', $timestamp), $name);
    $goods['name'] = empty($name) ? '自定义支付名称' : $name;
}

addAction('goods_pay_before', 'custom_goods_pay_name');



?>