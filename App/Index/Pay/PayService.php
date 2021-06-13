<?php
/**
 * Created by PhpStorm.
 * User: joy
 * Date: 2021/6/14
 * Time: 0:07
 */

namespace index\Pay;
class PayService
{
    public $uri = URL;

    protected $api = '';

    protected $payment = '';

    public function pay($order)
    {
        $this->_logger('order', $order);

        $data = $this->buildData($order);

        $this->_logger('data', $data);

        $sign = $this->getSign($data);

        $data['sign'] = $sign;

        $this->_logger('send', $data);

        $post = $this->curl_post($this->api, $data);
    }

    public function callback($data)
    {
        $isVerify = $this->verifySign($data);
        if(!$isVerify){
            $this->_logger('验签失败', $data);
        }

        $orderId = $this->buildOrderId($data);

        $this->success($orderId);
    }

    protected function buildOrderId($data){}

    public function success($orderId)
    {
        $recharge = getData('recharge', 1, 'orderid=\'' . $orderId . '\'');

        if (empty($recharge)) {
            $this->_logger('recharge参数缺失', $recharge);
        }
        else if ($recharge['status'] == '0') {
            $money = $recharge['money'];
            $uid = $recharge['uid'];
            $type = $recharge['type'];

            if (editData('recharge', array('status' => 1, 'time2' => date('Y-m-d H:i:s')), 'id = \'' . $orderId . '\'')) {
                addFinance($uid, $money, $type . '入款' . $money . '元', 1, getUserField($uid, 'money'));
                setNumber('user', 'money', $money, 1, 'id=\'' . $uid . '\'');
                $tid = getUserField($uid, 'top');
                setRechargeRebate($tid, $money);
                sendSms(getUserPhone($uid), '18005', $money);
                $this->_logger('入款成功', $recharge);
            }
            else {
                $this->_logger('入款失败', $recharge);
            }
        }
        else {
            $this->_logger('重复入款', $recharge);
        }
    }

    protected function verifySign($data){}

    protected function buildData($order){}

    protected function getSign($data){}

    public function curl_post($url , $data=array()){

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        // POST数据

        curl_setopt($ch, CURLOPT_POST, 1);

        // 把post的变量加上

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $output = curl_exec($ch);

        curl_close($ch);

        return $output;
    }

    public function _logger($message, $info = [])
    {
        $file = C('LOG_PATH').$this->payment.'_'.date('y_m_d').'.log';

        file_put_contents($file, date('Y-m-d H:i:s').'：'.$message.':'. json_encode($info, JSON_UNESCAPED_UNICODE)."\r\n\r\n", FILE_APPEND);
    }


}