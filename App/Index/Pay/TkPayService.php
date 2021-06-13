<?php
/**
 * Created by PhpStorm.
 * User: joy
 * Date: 2021/6/14
 * Time: 0:07
 */

include 'App/Index/Pay/PayService.php';

class TkPayService extends \index\Pay\PayService
{
    private $appid = '1039312';

    protected $payment = 'tkPay';

    private $app_key = 'xwKjTbFk8v86kotC8R46iWB1BB7xAWw0';

    protected $api = 'https://api.thkingz.com/index/unifiedorder';

    private $pay_type = 'ThreeScbCode';

    //异步通知接口url->用作于接收成功支付后回调请求
    private $callback_url = URL. 'pay/tk_callback';

    //支付成功后自动跳转url
    private $success_url = URL.'user/recharge_record.html';

    //支付失败或者超时后跳转url
    private $error_url = URL.'user/recharge_record.html';

    //版本号
    private $version = 'v1.0';

    public function buildData($order)
    {

        $data = [
            'appid'        => $this->appid,
            'pay_type'     => $this->pay_type,
            'out_trade_no' => $order['orderId'],
            'amount'       => sprintf("%.2f",$order['money']),
            'callback_url' => $this->callback_url,
            'success_url'  => $this->success_url,
            'error_url'    => $this->error_url,
            'version'      => $this->version,
            'out_uid'      => $order['uid'],
        ];

        return $data;
    }

    /**
     * @Note  生成签名
     * @param $data     参与签名的参数
     * @return string
     */
    public function getSign($data)
    {
        $secret = $this->app_key;
        // 去空
        $data = array_filter($data);

        //签名步骤一：按字典序排序参数
        ksort($data);
        $string_a = http_build_query($data);
        $string_a = urldecode($string_a);

        //签名步骤二：在string后加入mch_key
        $string_sign_temp = $string_a . "&key=" . $secret;

        //签名步骤三：MD5加密
        $sign = md5($string_sign_temp);

        // 签名步骤四：所有字符转为大写
        $result = strtoupper($sign);

        return $result;
    }

    public function buildOrderId($data){
        return $data['out_trade_no'];
    }

    /**
     * @Note   验证签名
     * @param $data
     * @param $orderStatus
     * @return bool
     */
    public function verifySign($data) {
        // 验证参数中是否有签名
        if (!isset($data['sign']) || !$data['sign']) {
            return false;
        }
        // 要验证的签名串
        $sign = $data['sign'];
        unset($data['sign']);
        // 生成新的签名、验证传过来的签名
        $sign2 = $this->getSign($data);

        if ($sign != $sign2) {
            return false;
        }
        return true;
    }

    public function curl_post($url, $data = array())
    {
        extract($data);
        echo "<html>
<head>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
	<title>前往支付中....</title>
</head>
<body>
<form action='{$this->api}' method='post' id='frmSubmit'>
    <input type='hidden' name='appid' value='{$appid}' />
    <input type='hidden' name='pay_type' value='{$pay_type}'/>
    <input type='hidden' name='out_trade_no' value='{$out_trade_no}'/>
    <input type='hidden' name='sign' value='{$sign}'/>
    <input type='hidden' name='callback_url' value='{$callback_url}' />
    <input type='hidden' name='success_url' value='{$success_url}' />
    <input type='hidden' name='error_url' value='{$error_url}' />
    <input type='hidden' name='amount' value='{$amount}' />
    <input type='hidden' name='version' value='{$version}' />
    <input type='hidden' name='out_uid' value='{$out_uid}' />
</form>
<script type='text/javascript'>
document.getElementById('frmSubmit').submit();
</script>
</body>
</html>";
    }
}