<?php



class SepPayService extends \index\Pay\PayService
{
    private $mch_id = '933933802';

    protected $payment = 'sepPay';

    private $app_key = 'VJK800TPNSTXM11IXR1KHXJELZPRLYJF';

    protected $api = 'https://pay.sepropay.com/sepro/pay/web';

    private $pay_type = 320;

    //异步通知接口url->用作于接收成功支付后回调请求
    private $callback_url = URL. 'pay/sep_callback';

    private $callback_cash_url = URL. 'pay/tk_cash_callback';

    //支付成功后自动跳转url
    private $success_url = URL.'user/recharge_record.html';

    //版本号
    private $version = 'v1.0';

    public function buildData($order)
    {
        $data = [
            'mch_id'       => $this->mch_id,
            'notify_url'   => $this->callback_url,
            'page_url'     => $this->success_url,
            'mch_order_no' => $order['orderId'],
            'pay_type'     => $this->pay_type,
            'trade_amount'       => sprintf("%.2f",$order['money']),
            'order_date'   => date('Y-m-d H:i:s'),
            'goods_name'   => '充值交易',
            'sign_type'    => 'MD5'
        ];

        return $data;
    }

    public function buildCashData($withdrawal)
    {
        $this->api = 'https://api.thkingz.com/withdrawal/creatWithdrawal';

        $data = [
            'appid'        => $this->appid,
            'account'      => $withdrawal['account'],
            'mch_order_no' => $withdrawal['out_trade_no'],
            'money'       => sprintf("%.2f",$withdrawal['money']),
            'callback' => $this->callback_cash_url,
            'bank_id'      => $withdrawal['bank_id'],
            'bank_type'      => 2,
            'name'      => $withdrawal['name'],
            'remark'      => 'tk',
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
        unset($data['sign_type']);

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

        return $sign;
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

    public function html_post($url, $data = array())
    {
        extract($data);
        echo "<html>
<head>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
	<title>前往支付中....</title>
</head>
<body>
<form action='{$this->api}' method='post' id='frmSubmit'>
    <input type='hidden' name='sign' value='{$sign}' />
    <input type='hidden' name='mch_id' value='{$mch_id}' />
    <input type='hidden' name='sign_type' value='{$sign_type}' />	
    <input type='hidden' name='notify_url' value='{$notify_url}' />
    <input type='hidden' name='page_url' value='{$page_url}' />				
    <input type='hidden' name='mch_order_no' value='{$mch_order_no}' />	
    <input type='hidden' name='pay_type' value='{$pay_type}' />	
    <input type='hidden' name='trade_amount' value='{$trade_amount}' />
    <input type='hidden' name='order_date' value='{$order_date}' /></br>	
    <input type='hidden' name='goods_name' value='{$goods_name}' />
</form>
<script type='text/javascript'>
document.getElementById('frmSubmit').submit();
</script>
</body>
</html>";
    }
}