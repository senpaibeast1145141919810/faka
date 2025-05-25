<?php

use think\Db;
use hehe\Verify;

/**
 * 显示商品默认价格
 */
function goodsPrice($price_json, $user = null, $agency){
    $result = json_decode($price_json, true);
    if(empty($user) || !isset($result['agency_price_' . $user['agency_id']])){
        $price = empty($result['sale_price']) ? '0.00' : $result['sale_price'];
    }else{
//        echo '<pre>'; print_r($result);die;
        if(Verify::isEmpty($result['agency_price_' . $user['agency_id']])){
            $price = sprintf('%.2f', (float)$result['sale_price'] * ((float)$agency[$user['agency_id']] / 10));
        }else{
            $price = $result['agency_price_' . $user['agency_id']];
        }
    }
    return $price;
}

/**
 * 获取商品分类
 */
function getGoodsCategory(){
    $result = db::name('goods_category')->field('id, name')->order('weigh desc, id desc')->select();
    return $result;
}

/**
 * 获取全部商品
 */
function getGoodsAll(){
    $result = db::name('goods')->alias('g')
        ->field('g.id, g.category_id, g.name, g.cover, g.stock, g.sales, s.price, g.invented_sales')
        ->join('sku s', 'g.id=s.goods_id')
        ->where(['g.shelf' => 1])
        ->group('s.goods_id')
        ->order('g.weigh desc, g.id desc')
        ->select();
    return $result;
}