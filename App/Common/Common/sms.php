<?php
//短信开关
function smsStatus($code){
    //所有短信开关，1代表开启，0代表关闭
    $regSms = 1;//注册短信开关
    $investSms = 0;//投资成功短信开关
    $incomeSms = 0;//收益到账短信开关
    $forgetSms = 1;//找回密码短信开关
    $rechargeYesSms = 0;//充值成功短信开关
    $rechargeNoSms = 0;//充值失败短信开关
    $cashYesSms = 1;//提现成功短信开关
    $cashNoSms = 0;//提现失败短信开关
    $data = array(
        "18001" => $regSms?:0,
        "18002" => $investSms?:0,
        "18003" => $incomeSms?:0,
        "18004" => $forgetSms?:0,
        "18005" => $rechargeYesSms?:0,
        "18006" => $rechargeNoSms?:0,
        "18007" => $cashYesSms?:0,
        "18008" => $cashNoSms?:0,
    );
    return $data[$code];
}
//跳转微信链接开关
function gotoWechatPay($money){
    $status = 0;//开启站外跳转
    if($status == 1){
        $url = "http://jump.ui879.com/WeChat/?k=d6e34c038b17072bcf0d524db7690d2&i=1147";//站外微信支付链接
    }
    else{
        $url = U("User/scan","type=wechat&money=".$money);//扫码链接
    }
    header("Location:".$url);
}
//跳转支付宝链接开关
function gotoAlipay($money){
    $status = 0;//开启站外跳转
    if($status == 1){
        $url = "https://qr.alipay.com/tsx02511d0qtd2poqlmria0";//站外支付宝支付链接
    }
    else{
        $url = U("User/scan","type=alipay&money=".$money);//扫码链接
    }
    header("Location:".$url);
}
//阿里云接口API
function getAliyunApi(){
    //实名接口地址：https://market.aliyun.com/products/56928004/cmapi014760.html
    //银行卡接口地址：https://market.aliyun.com/products/56928004/cmapi012976.html
    return "3adbeea0fc0b47d0bf7159a8449a9ca3";
}