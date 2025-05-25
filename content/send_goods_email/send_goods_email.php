<?php

use app\common\library\Email as Mail;

function send_goods_email($order, $deliver) {
    
    $plugin_path = ROOT_PATH . "content/send_goods_email/";
    $info = include_once "{$plugin_path}setting.php";
    
    $info['mail_verify_type'] = empty($info['mail_verify_type']) ? 0 : strtolower($info['mail_verify_type']);
    
    if($info['mail_verify_type'] == 'tls') $info['mail_verify_type'] = 1;
    if($info['mail_verify_type'] == 'ssl') $info['mail_verify_type'] = 2;
    
    // print_r($info);die;
    
    if(!empty($order['email'])) {
        $html = <<<html
<meta charset="UTF-8">
<title>imap</title>
<style>
    @media screen and (min-width: 700px) {
      .bottomErweima {
        display: block !important;
      }
      #btn {
        display: none !important;
      }
      .footer {
        display: none !important;
      }
    }
    /* vivo手机width: 980px 同时 aspect-ratio小于1的,处于700px-1000px的手机*/
    @media screen and (min-width: 700px) and (max-width: 1000px) and (max-aspect-ratio:1/1){
      .bottomErweima {
        display: none !important;
      }
      #btn {
        display: block !important;
      }
      .footer {
        display: block !important;
      }
    }
  </style>
  <div id="email-box" style="max-width: 550px;margin: 0 auto;">
    <div class="email_container">
      <div class="head" style="background: #f3f3f3;">
        <span class="content" style="font-size: 14px;color: #000000;line-height: 26px;display: block;padding: 40px 20px;">
html;

        if(empty($deliver)){
            $html .= "下单成功，请等待订单发货";
        }else{
            $html .= "您购买的商品《{$order['goods_name']}》发货内容如下";
        }

        $html .= <<<html
        </span>
      </div>
       <div class="part" style="color: #000000;text-align: center;margin-top: 40px;">
        <h2 style="font-size: 20px;margin-bottom: 14px;margin-top: 0;">
html;


        foreach ($deliver as $val) {
            $html .= $val['content'] . "<br>";
        }

        $html .= "</h2>";

        $html .= '<p style="margin-bottom: 0;font-size: 13px;color: #8E8E93;">订单编号：' . $order['out_trade_no'] . '</p>';

        $html .= '<p style="margin-bottom: 0;font-size: 13px;color: #8E8E93;">电子邮箱：' . $order['email'] . '</p>';

        if (!empty($order['password'])) {
            $html .= '<p style="margin-bottom: 0;font-size: 13px;color: #8E8E93;">查单密码：' . $order['password'] . '</p>';
        }

        $html .= <<<html
      </div>
  </div>
  </div>
  <style type="text/css">.qmbox style, .qmbox script, .qmbox head, .qmbox link, .qmbox meta {display: none !important;}</style>

html;


        $email = new Mail($info);
        $result = $email->to($order['email'])->subject("订单通知")->message($html)->send();
        
        // var_dump($email->getError());
        
        // var_dump($result);die;
        
    }
}

addAction('send_goods', 'send_goods_email');





?>