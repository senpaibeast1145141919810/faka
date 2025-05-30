<?php

namespace app\index\controller;

use app\common\controller\IndexCommon;
use hehe\Verify;
use think\Db;


class Index extends IndexCommon {

    protected $layout = 'default';

    protected $noNeedRight = ['*'];
    protected $noNeedLogin = ['*'];



    public function _initialize() {

        parent::_initialize();

        if(!$this->request->isPjax()){
        }

    }


    public function index() {

        $category_id = $this->request->has('category_id') ? $this->request->param('category_id') : false;
        $category = Db::name('goods_category')->order('weigh desc, id desc')->select();
        if($category_id){
            foreach($category as $key => $val){
                if($category_id == $val['id']){
                    $category[$key]['active'] = true;
                    break;
                }
            }
        }else{
            foreach($category as $key => $val){
                if($val['pid'] == 0){
                    $category_id = $val['id'];
                    $category[$key]['active'] = true;
                    break;
                }
            }
        }
        try {
            $goods_category = db::name('goods_category')->field('id, name')->where(['id' => $category_id])->whereOr(['pid' => $category_id])->order('weigh desc, id desc')->select();
            $category_ids = array_column($goods_category, 'id');
            $category_ids = implode(',', $category_ids);
            $model_goods = new \app\index\model\Goods();
            $goods = $model_goods->with(['sku'])
                ->field('id, category_id, cover, name, is_sku, type, stock, sales, invented_sales')
                ->where("`category_id` in ({$category_ids})")
                ->where(['shelf' => 1])
                ->order('weigh desc')
                ->select();
        }catch (\Exception $e){

            $path = ROOT_PATH . 'application/index/view/error.html';
            $title = "需要添加商品分类才可以访问";
            $content = "当前站点未添加商品分类，请管理人员前往后台面板 - 商品管理 - 商品分类 - 添加分类后继续访问";
            include_once $path;die;
        }




        foreach($goods_category as $key => $val){
            $goods_category[$key]['goods'] = [];
            foreach($goods as $v){
                if($val['id'] == $v['category_id']){
                    $goods_category[$key]['goods'][] = $this->indexClGd($v, $this->agency);
                }
            }
        }


        $blog = db::name('blog')->whereNull('deletetime')->order('weigh desc, id desc')->limit(6)->select();

        $this->assign([
            'category' => $category,
            'goods_category' => $goods_category,
            'blog' => $blog
        ]);
        return view($this->template . 'index');
    }





    public function goods(){
        $id = $this->request->param('id');
        $model_goods = new \app\index\model\Goods();
		$goods = $model_goods->with(['sku'])->where(['id' => $id])->find()->toArray();
        $category = db::name('goods_category')->where(['id' => $goods['category_id']])->find();
		$agency = $this->userAgency();
		$goods = $this->goodsDetail($goods, $agency);

//        echo '<pre>'; print_r($goods);die;
        $pay_list = getPayList($this->plugin);


        $this->assign([
            'category' => $category,
            'goods' => $goods,
            'pay_list' => $pay_list
        ]);
        return view($this->template . 'goods');
    }




    /**
     * 处理商品列表信息
     */
    public function indexClGd($goods, $agency){
        if($goods['is_sku'] == 0){
            $price = json_decode($goods['sku'][0]['price'], true);
            $goods['crossed_price'] = sprintf('%.2f', $price['crossed_price']);
            $price['sale_price'] = sprintf('%.2f', $price['sale_price']);
            if(empty($this->user['agency_id'])){
                $goods['init_price'] = sprintf('%.2f', $price['sale_price']);
            }else{
                if(Verify::isEmpty($price['agency_price_' . $this->user['agency_id']])){
                    $goods['init_price'] = sprintf('%.2f', $price['sale_price'] * ($agency[$this->user['agency_id']] / 10));
                }else{
                    $goods['init_price'] = $price['agency_price_' . $this->user['agency_id']];
                }
            }

        }
        if($goods['is_sku'] == 1){
            $sku = json_decode($goods['sku'][0]['price'], true);
//            echo '<pre>'; print_r($sku); print_r($agency);die;
//            echo 0.00 * (8/10);die;
            $sku['sale_price'] = sprintf('%.2f', $sku['sale_price']);
            if(empty($this->user['agency_id'])){
                $goods['init_price'] = sprintf('%.2f', $sku['sale_price']);
            }else{
                if(Verify::isEmpty($sku['agency_price_' . $this->user['agency_id']])){
                    if(isset($agency[$this->user['agency_id']])){
                        $goods['init_price'] = sprintf('%.2f', $sku['sale_price'] * ($agency[$this->user['agency_id']] / 10));
                    }else{
                        $goods['init_price'] = $sku['sale_price'];
                    }
                }else{
                    $goods['init_price'] = $sku['agency_price_' . $this->user['agency_id']];
                }
            }
            $goods['crossed_price'] = sprintf('%.2f', $sku['crossed_price']);
        }
        return $goods;
    }






}
