<?php
//dezend by http://www.yunlu99.com/
function getUserMember($member)
{
	$member = getData('user_member', 1, 'id=\'' . $member . '\'');

	if (empty($member)) {
		return '普通会员';
	}

	return $member['name'];
}
function getUsername($member)
{
	$member = getData('user', 1, 'id=\'' . $member . '\'');


	return $member['name'];
}
function getUserStatus($uid)
{
	$user = getData('user', 1, 'id = \'' . $uid . '\'');
	$logintime = $user['logintime'];
	$now = time();

	if ($now < $logintime + 300) {
		return 1;
	}

	return 0;
}

function getUserOnlineNum()
{
	$now = time() - 300;
	$user = getData('user', 'all', 'logintime >= \'' . $now . '\'');
	return count($user);
}

function getUserPhone($uid)
{
	$user = getData('user', 1, 'id = \'' . $uid . '\'');
	return $user['phone'];
}
//获取父级账户
function getUserTop($uid){
    $user = getData('user', 1, 'id = \'' . $uid . '\'');
    $top  =getData('user', 1, 'id = \'' . $user['top'] . '\'');
    if($top['phone']){
        return $top['phone'];
    }else{
        return "无";
    }
    
}

//获取最上级推荐人
/*function getUserFather($uid){

   $user=M('user')->where('id',$uid)->field('id,top,phone')->find();
   
   if($user['top']!='0') return self::getUserFather($user['top']);
   
   return $user['phone'];
    
}*/
//获取最上级推荐人
function getUserFather($uid){
    $user=M('user')->field('id,top,phone')->select();
    
    foreach($user as $v ){
        if($v['id'] == $uid){
            if($v['top'] != 0){
                return getUserFather($v['top'] );
            }else{
                return $v['phone'];
            }
        }
    }
    
    
}

function getUserHidePhone($uid)
{
	$user = getData('user', 1, 'id = \'' . $uid . '\'');
	$first = substr($user['phone'], 0, 4);
	$end = substr($user['phone'], strlen($user['phone']) - 4, 4);
	return $first . '****' . $end;
}

function getUserId($phone)
{
	$user = getData('user', 1, 'phone = \'' . $phone . '\'');
	return $user['id'];
}

function getUserField($uid, $field)
{
	$user = getData('user', 1, 'id = \'' . $uid . '\'');
	return $user[$field] ?: 0;
}

function isLogin()
{
	$uid = $_SESSION['uid'];
	$user = getData('user', 1, 'id = \'' . $uid . '\'');
	if ($user['clock'] == 1 || empty($uid) || empty($user)) {
		return false;
	}

	editData('user', array('logintime' => time()), 'id = \'' . $uid . '\'');
	return true;
}

function getPayName($pay)
{
	switch ($pay) {
		case 'wechat':
			return '微信扫码';
		case 'alipay':
			return '支付宝扫码';
		case 'bank':
			return '银行入款';
		case 'online_wechat':
			return '微信在线扫吗支付（吗支付）';
		case 'online_alipay':
			return '支付宝在线扫吗支付（吗支付）';
		case 'wechat_scan':
			return '微信在线扫码支付';
		case 'tk':
			return 'tk';

		default:
	}

	return $pay;
}

function setRechargeRebate($tid, $money)
{
	$reward = getData('reward', 1);
	$rebate = round($reward['recharge'] * $money / 100, 2);

	if (0 < $rebate) {
		addFinance($tid, $rebate, '推荐会员充值' . $money . '元，获得佣金' . $rebate . '元', 1, getuserfield($tid, 'money'));
		setNumber('user', 'money', $rebate, 1, 'id=\'' . $tid . '\'');
		setNumber('user', 'income', $rebate, 1, 'id=\'' . $tid . '\'');
	}
}

function getBankFirst($account)
{
	return substr($account, 0, 4);
}

function getBankEnd($account)
{
	return substr($account, strlen($account) - 4, 4);
}

function setUserMember($uid, $value)
{
	$member = getData('user_member', 1, 'value <= \'' . $value . '\'', '', 'value desc');

	if (empty($member)) {
		$mid = 0;
	}
	else {
		$mid = $member['id'];
	}

	editData('user', array('member' => $mid), 'id=\'' . $uid . '\'');
	return $mid;
}

function getUserUnPrincipal($uid)
{
	$now = date('Y-m-d H:i:s');
	$invest = getData('invest', 'all', 'DATE_ADD(time,INTERVAL day day) >= \'' . $now . '\' AND uid = \'' . $uid . '\'');
	$money = 0;

	foreach ($invest as $i) {
		$money += $i['money'];
	}

	return round($money, 2);
}

function getUserPrincipal($uid)
{
	$now = date('Y-m-d H:i:s');
	$invest = getData('invest', 'all', 'DATE_ADD(time,INTERVAL day day) < \'' . $now . '\' AND uid = \'' . $uid . '\'');
	$money = 0;

	foreach ($invest as $i) {
		$money += $i['money'];
	}

	return round($money, 2);
}

function getUserUnIncome($uid)
{
	$invest = getData('invest_list', 'all', 'uid = \'' . $uid . '\' AND pay1 > 0 AND status = 0');
	$money = 0;

	foreach ($invest as $i) {
		$money += $i['pay1'];
	}

	$money = $money - getuserunprincipal($uid);
	return round($money, 2);
}

function getUserIncome($uid)
{
	$invest = getData('invest_list', 'all', 'uid = \'' . $uid . '\' AND pay1 > 0 AND status = 1');
	$money = 0;

	foreach ($invest as $i) {
		$money += $i['pay1'];
	}

	$money = $money - getuserprincipal($uid);
	return round($money, 2);
}

function getUserInvestMoney($uid)
{
	$invest = getData('invest', 'all', 'uid = \'' . $uid . '\'');
	$money = 0;

	foreach ($invest as $i) {
		$money += $i['money'];
	}

	return round($money, 2);
}

function getUserCashMoney($uid)
{
	$cash = getData('cash', 'all', 'uid = \'' . $uid . '\' AND status = 1');
	$money = 0;

	foreach ($cash as $c) {
		$money += $c['money'];
	}

	return $money;
}

function getUserRechargeMoney($uid)
{
	$recharge = getData('recharge', 'all', 'uid = \'' . $uid . '\' AND status = 1');
	$money = 0;

	foreach ($recharge as $r) {
		$money += $r['money'];
	}

	return $money;
}

?>
