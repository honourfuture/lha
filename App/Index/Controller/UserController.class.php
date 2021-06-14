<?php
//dezend by http://www.yunlu99.com/
namespace Index\Controller;
include 'App/Index/Pay/TkPayService.php';
class UserController extends \Think\Controller
{
	public function _initialize()
	{
		if (!isLogin()) {
			header('Location:' . U('mobile/login'));
		}

		$uid = $_SESSION['uid'];
		$user = getData('user', 1, 'id = \'' . $uid . '\'');
		$this->assign('uid', $uid);
		$this->assign('user', $user);
	}

	public function index()
	{
		$this->display();
	}

	public function fund()
	{
		$uid = $_SESSION['uid'];
		$finance = getData('finance', 'all', 'uid=\'' . $uid . '\'', '', 'id desc');
		$this->assign('finance', $finance);
		$this->display();
	}

	public function certification()
	{
		if ($_POST) {
			$name = getValue('name');
			$idcard = getValue('idcard');
			$uid = $_SESSION['uid'];
			$user = getData('user', 1, 'idcard = \'' . $idcard . '\' AND id <> \'' . $uid . '\'');

			if (!empty($user)) {
				msg('身份证号码已存在，请勿重复注册！');
			}

			$host = 'http://idcard.market.alicloudapi.com';
			$path = '/lianzhuo/idcard';
			$method = 'GET';
			$appcode = 'ku03toiyjj4wnskp3d81p6migg0vem26';
			$headers = array();
			array_push($headers, 'Authorization:APPCODE ' . $appcode);
			$querys = 'cardno=' . $idcard . '&name=' . $name;
			$url = $host . $path . '?' . $querys;
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_FAILONERROR, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, false);

			if (1 == strpos('$' . $host, 'https://')) {
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			}

			$re = curl_exec($curl);
			$resp = json_decode($re, true);

			if ($resp['resp']['code'] == '5') {
				msg('姓名和身份证号码不匹配！');
			}

			if ($resp['resp']['code'] == '14') {
				msg('无此身份证号码！');
			}

			if ($resp['resp']['code'] == '96') {
				msg('网络繁忙，请稍后重试！');
			}

	
			$data = array('name' => $name, 'idcard' => $idcard, 'auth' => 1);

			if (editData('user', $data, 'id = \'' . $uid . '\'')) {
				msg('认证成功！');
			}
			else {
				msg('认证失败！');
			}
		}
		else {
			$this->display();
		}
	}

	public function pwd_login()
	{
		if ($_POST) {
			$uid = $_SESSION['uid'];
			$user = getData('user', 1, 'id = \'' . $uid . '\'');
			$oldpwd = getValue('oldpwd');
			$pwd = getValue('pwd');
			$pwd2 = getValue('pwd2');

			if ($user['password'] != md5($oldpwd)) {
				msg('原登录密码错误！');
			}

			if (strlen($pwd) < 6 || 16 < strlen($pwd)) {
				msg('请输入6-16位密码！');
			}

			if (strlen($pwd2) < 6 || 16 < strlen($pwd2)) {
				msg('请再次输入6-16位密码！');
			}

			if ($pwd != $pwd2) {
				msg('两次密码不一致！');
			}

			if (editData('user', array('password' => md5($pwd)), 'id=\'' . $uid . '\'')) {
				msg('修改成功！');
			}
			else {
				msg('修改失败！');
			}
		}
		else {
			$this->display();
		}
	}

	public function pwd_pay()
	{
		if ($_POST) {
			$uid = $_SESSION['uid'];
			$user = getData('user', 1, 'id = \'' . $uid . '\'');
			$oldpwd = getValue('oldpwd');
			$pwd = getValue('pwd');
			$pwd2 = getValue('pwd2');

			if ($user['password2'] != md5($oldpwd)) {
				msg('原交易密码错误！');
			}

			if (strlen($pwd) < 6 || 16 < strlen($pwd)) {
				msg('请输入6-16位密码！');
			}

			if (strlen($pwd2) < 6 || 16 < strlen($pwd2)) {
				msg('请再次输入6-16位密码！');
			}

			if ($pwd != $pwd2) {
				msg('两次密码不一致！');
			}

			if (editData('user', array('password2' => md5($pwd)), 'id=\'' . $uid . '\'')) {
				msg('修改成功！');
			}
			else {
				msg('修改失败！');
			}
		}
		else {
			$this->display();
		}
	}

	public function recharge()
	{
		if ($_POST) {

			$uid = $_SESSION['uid'];
			$money = getValue('money', 'float');
			$type = getValue('type', 'str');

			if ($money < 200) {
				msg('小于最低充值金额200元！');
			}
			if ($type == 'bank') {
						
					$v = getData('info');
						$where['status'] = '1';
						$where['uid'] = $uid;
							$recharge = M("recharge"); // 实例化User对象
							$tixian = $recharge->where($where)->sum('money');
					
						$tixiana = $tixian ? $tixian : 0 ;
				
						if($v['icar'] > $tixiana){
							msg('您的充值金额没有达到 '.$v['icar'].'查看不了银行信息');
							
						}	
				}
						
			$orderid = 'PAY' . time() . rand(100, 999);
			
			$data = array('orderid' => $orderid, 'uid' => $uid, 'money' => $money, 'type' => getPayName($type), 'status' => 0, 'time' => date('Y-m-d H:i:s'), 'time2' => '0000-00-00 00:00:00');

			if (addData('recharge', $data)) {
				$order = [
					'orderId' => $orderid,
					'uid' => $uid,
					'money' => $money,
				];
				if ($type == 'tk') {
					$tkPay = new \TkPayService();
					$tkPay->pay($order);
				}else if ($type == 'wechat') {
					gotoWechatPay($money);
				}
				else if ($type == 'alipay') {
					gotoAlipay($money);
				}
				else if ($type == 'bank') {
						
						header('Location:' . U('bank', 'money=' . $money . '&orderid=' . $orderid));
				}
				else if ($type == 'online_wechat') {
					header("location:/codepay/codepay.php?user=".$uid."&price=".$money."&type=3&orderid=".$orderid);
				}
				else if ($type == 'wechat_scan') {
					$token = md5($money . '#' . $uid . '#token' . $orderid);
					$online = getData('online', 1);
					$url = $online['domain'] . '/index.php/Scan/pay/money/' . $money . '/uid/' . $uid . '/orderid/' . $orderid . '/token/' . $token . '.html';
					$this->assign('url', $url);
					$this->assign('out_trade_no', $orderid);
					$this->display('weipay');
				}
				else {
					if ($type == 'online_alipay') {
							header("location:/codepay/codepay.php?user=".$uid."&price=".$money."&type=1&orderid=".$orderid);
						exit('zhifub');
					}
				}
			}
			else {
				msg('系统繁忙，暂时无法充值！');
			}
		}
		else {
			$startTime = strtotime(date('Y-m-d 09:00:00'));
			$endTime = strtotime(date('Y-m-d 18:00:00'));

			$open = 0;
			$time = time();
			if($time >= $startTime && $time <= $endTime){
				$open = 1;
			}
			$this->assign('open', $open);
			$this->display();
		}
	}

	public function scan()
	{
		$type = getValue('type');
		$money = getValue('money', 'float');

		if ($type == 'wechat') {
			$qr = getInfo('qr_wechat_img');
		}
		else {
			$qr = getInfo('qr_alipay_img');
		}

		$this->assign('qrcode', $qr);
		$this->assign('money', $money);
		$this->display();
	}

	public function bank()
	{
		$orderid = getValue('orderid');

		if (empty($orderid)) {
			msg('参数有误！', 2, U('person'));
		}

		if ($_POST) {
			$name = getValue('name');
			$reason = getValue('reason');
			$data = array('reason' => '付款人：' . $name . '<br/>转账附言：' . $reason);

			if (editData('recharge', $data, 'orderid=\'' . $orderid . '\'')) {
				msg('提交成功，等待入账！', 2, U('person'));
			}
			else {
				msg('提交失败！', 2, U('person'));
			}
		}
		else {
			$money = getValue('money', 'float');
			$this->assign('money', $money);
			$this->assign('orderid', $orderid);
			$this->display();
		}
	}

	public function add_card()
	{
		$uid = $_SESSION['uid'];

		if ($_POST) {
			$user = getData('user', 1, 'id = \'' . $uid . '\'');
			$idcard = $user['idcard'];

			if ($user['auth'] != 1) {
				msg('请认证后再添加银行卡！', 2, U('User/certification'));
			}

			$name = $user['name'];
			$bankId = getValue('bankId');
			$account = getValue('account');

			$bank = getData('banks', 1, 'id=\'' . $bankId . '\'');
			$name = $bank['bank_name'];
			if (empty($bank)) {
				msg('请输入所属银行！');
			}

			if (empty($account)) {
				msg('请输入银行卡号！');
			}

			$bank1 = getData('bank', 1, 'account=\'' . $account . '\'');

			if (!empty($bank1)) {
				msg('银行卡号已存在，请勿重复添加！');
			}

			header('Content-type:text/html; charset=utf-8');
		
				$data = array('uid' => $uid, 'bank' => $name, 'account' => $account, 'bank_id' => $bankId);

				if (addData('bank', $data)) {
					msg('添加成功！');
				}
				else {
					msg('添加失败！');
				}

		 }else {
			$bank = getData('bank', 'all', 'uid = \'' . $uid . '\'');
			$banks = getData('banks', 'all');
			$this->assign('bank', $bank);
			$this->assign('banks', $banks);
			$this->assign('uid', $uid);
			$this->display();
		}
	}

	public function del_card()
	{
		$id = getValue('id', 'int');

		if (delData('bank', 'id=\'' . $id . '\'')) {
			msg('删除成功！', 2, U('add_card'));
		}
		else {
			msg('删除失败！', 2, U('add_card'));
		}
	}

	public function cash()
	{
		if ($_POST) {
			$pwd = getValue('pwd');
			$bid = getValue('bank');
			$money = getValue('money');
			$uid = $_SESSION['uid'];
			$user = getData('user', 1, 'id = \'' . $uid . '\'');
			$bank = getData('bank', 1, 'id = \'' . $bid . '\'');
			$invest = getData('invest', 1, 'uid = \'' . $uid . '\'');
			$zong = $user['money'] - $user['dongjiemoney'];
			$orderid = 'CASH' . time() . rand(100, 999);

			if ($zong < $money) {
				msg('您的余额被冻结（'.$user['dongjiemoney'].'），请联系管理员', 2, U('user/recharge'));
			}
			if ($user['password2'] != md5($pwd)) {
				msg('交易密码不正确！');
			}

			if ($user['money'] < $money) {
				msg('提现金额大于会员余额！');
			}

			if ($user['auth'] != 1) {
				msg('请认证后再进行提现！', 2, U('User/certification'));
			}

			if ($bank['uid'] != $uid || empty($bank)) {
				msg('提现银行卡暂时不能使用！');
			}

			if (empty($invest)) {
				msg('未投资不能提现！');
			}
			
			$v = getData('info', '1');
			$msg = (explode(",",$v['allowable']));
			if($msg['1'] == 0){
				$tixianri = '日';
				
			}else{
				$tixianri = $msg['1'];
			}
			
		    $time = time();
			$msg22 = 9;
			$msg33 = 15;
			$err_msg = '请在'.$msg22.':05-'.$msg33.':00 提交申请!';
			$week = date('w', $time);
			$hour = date('H', $time);
			if($week < $msg['0'] || $week >= $msg['1']){
				msg($err_msg);
			}
			if($hour < $msg22 || $hour > $msg33){
				msg($err_msg);
			}
			
					
			$cash = M("cash"); // 实例化User对象
			$uid = $_SESSION['uid'];
			
			$today=(date('Y-m-d 00:00:00'));
			
			$data['time'] = array('egt',$today);
			$data['uid'] = $uid;
			//$data['status'] = '1';
			//$userCount = $cash->where($data)->count("id");
			
			$cashcount=$cash->where($data)->count();
			
			if($v['withdrawals']>$cashcount){
			    $data = array('uid' => $uid, 'name' => $user['name'], 'bid' => $bid, 'bank' => $bank['bank'], 'account' => $bank['account'], 'money' => $money,'sjmoney' => $money, 'status' => 0, 'time' => date('Y-m-d H:i:s'), 'time2' => '0000-00-00 00:00:00', 'order_id' => $orderid);
			}else{
			    $data = array('uid' => $uid, 'name' => $user['name'], 'bid' => $bid, 'bank' => $bank['bank'], 'account' => $bank['account'], 'money' => $money,'sjmoney' => $money-$money*$v['charged'], 'status' => 0, 'time' => date('Y-m-d H:i:s'), 'time2' => '0000-00-00 00:00:00', 'order_id' => $orderid);
			}
			$data = array('uid' => $uid, 'name' => $user['name'], 'bid' => $bid, 'bank' => $bank['bank'], 'account' => $bank['account'], 'money' => $money,'sjmoney' => $money-100, 'status' => 0, 'time' => date('Y-m-d H:i:s'), 'time2' => '0000-00-00 00:00:00', 'order_id' => $orderid);
            if (addData('cash', $data)) {
				//$Charge = ($money * $v['charged']) + $money;
				addFinance($uid, $Charge, '余额提现' . $money . '元手续费:'.$money*$v['charged'].'', 2, getUserField($uid, 'money'));
				//setNumber('user', 'money', $money*$v['charged'], 2, 'id=\'' . $uid . '\'');
				msg('提现成功！');
			}
			else {
				msg('提现失败！');
			}
	        
			/*if($userCount >= $v['withdrawals']){
				if (addData('cash', $data)) {
					$Charge = ($money * $v['charged']) + $money;
					addFinance($uid, $Charge, '余额提现' . $money . '元手续费:'.$Charge.'', 2, getUserField($uid, 'money'));
					setNumber('user', 'money', $Charge, 2, 'id=\'' . $uid . '\'');
					msg('提现成功！');
				}
				else {
					msg('提现失败！');
				}
				//echo $err_msg;exit;
			}else{
				if (addData('cash', $data)) {
					addFinance($uid, $money, '余额提现' . $money . '元', 2, getUserField($uid, 'money'));
					setNumber('user', 'money', $money, 2, 'id=\'' . $uid . '\'');
					msg('提现成功！');
				}
				else {
					msg('提现失败！');
				}
				
			}*/
			
		}else {
			$open = 0;

			$startTime = strtotime(date('Y-m-d 09:05:00'));
			$endTime = strtotime(date('Y-m-d 15:00:00'));

			$time = time();
			if($time >= $startTime && $time <= $endTime){
				$open = 1;
			}

			if(date('N') >5){
				$open = 0;
			}


			$uid = $_SESSION['uid'];
			$bank = getData('bank', 'all', 'uid = \'' . $uid . '\'');
			$this->assign('bank', $bank);
			$this->assign('open', $open);
			$this->display();
		}
	}

	public function interest()
	{
		$uid = $_SESSION['uid'];
		$invest = getData('invest_list', 'all', 'uid=\'' . $uid . '\' AND status = 1 AND pay1 <> 0', '', 'time2 desc');
		$this->assign('invest', $invest);
		$this->display();
	}

	public function invest()
	{
		$uid = $_SESSION['uid'];
		$invest = getData('invest', 'all', 'uid = \'' . $uid . '\'', '', 'id desc');
		$this->assign('invest', $invest);
		$this->display();
	}

	public function details()
	{
		$uid = $_SESSION['uid'];
		$id = getValue('id', 'int');

		if (empty($id)) {
			msg('参数缺失！', 2, U('invest'));
		}

		$invest = getData('invest', 1, 'id=\'' . $id . '\'');
		$list = getData('invest_list', 'all', 'uid = \'' . $uid . '\' AND iid = \'' . $id . '\'', '', 'id asc');
		if (empty($list) || empty($invest)) {
			msg('系统繁忙！', 2, U('invest'));
		}

		$this->assign('invest', $invest);
		$this->assign('list', $list);
		$this->display();
	}

	public function contract()
	{
		$uid = $_SESSION['uid'];
		$id = getValue('id', 'int');

		if (empty($id)) {
			msg('参数缺失！', 2, U('invest'));
		}

		$invest = getData('invest', 1, 'id=\'' . $id . '\'');
		$list = getData('invest_list', 'all', 'uid = \'' . $uid . '\' AND iid = \'' . $id . '\'', '', 'id desc');
		if (empty($list) || empty($invest)) {
			msg('系统繁忙！', 2, U('invest'));
		}

		$this->assign('invest', $invest);
		$this->assign('list', $list);
		$this->display();
	}

	public function recharge_record()
	{
		$uid = $_SESSION['uid'];
		$recharge = getData('recharge', 'all', 'uid = \'' . $uid . '\'', '', 'id desc');
		$this->assign('recharge', $recharge);
		$this->display();
	}

	public function cash_record()
	{
		$uid = $_SESSION['uid'];
		$cash = getData('cash', 'all', 'uid = \'' . $uid . '\'', '', 'id desc');
		$this->assign('cash', $cash);
		$this->display();
	}

	public function recommend()
	{
		$uid = $_SESSION['uid'];
		$secondUsers = $thirdUsers = [];
		$firstUsers = getData('user', 'all', 'top = \'' . $uid . '\'', '', 'id desc');

		if($firstUsers){
			$firstUserIds = array_column($firstUsers, 'id');
			$firstUserIds = implode(',', $firstUserIds);
			$secondUsers = getData('user', 'all', "top in ({$firstUserIds})", '', 'id desc');

			if($secondUsers){
				$secondUserIds = array_column($secondUsers, 'id');
				$secondUserIds = implode(',', $secondUserIds);
				$thirdUsers = getData('user', 'all', "top in ({$secondUserIds})", '', 'id desc');
			}
		}

		$this->assign('firstUsers', $firstUsers);
		$this->assign('secondUsers', $secondUsers);
		$this->assign('thirdUsers', $thirdUsers);
		$this->display();
	}

	public function success_hall()
	{
		$uid = $_SESSION['uid'];
		$subUsers = getData(
			'user',
			'all',
			'top = \'' . $uid . '\'', '',
			'user.id desc',
			'user.id',
			'invest_list ON user.id = invest_list.uid'
		);

		$userMembers = getData('user_member', 'all', '', '', 'id asc');
		$userRecords = getData('user_records', 'all', 'user_id = \'' . $uid . '\'', '', 'id asc');

		$userKeyRecords = array();
		foreach ($userRecords as $record) {
			$userKeyRecords[$record['user_member_id']] = $record;
		}


		$countSubUsers = count($subUsers);
		foreach ($userMembers as &$member){
			$member['hasSubUser'] = 0;
			$member['isReceive'] = 0;
			$member['allowReceive'] = 0;

			$countSubUsers = $countSubUsers - $member['value'] ? $countSubUsers - $member['value'] : 0;
			if($countSubUsers >= $member['value']){
				$member['hasSubUser'] = $member['value'];
			}else if($countSubUsers <= 0){
				$num = $countSubUsers + $member['value'];
				$member['hasSubUser'] = $num > 0 ? $num : 0;
			}

			if(isset($userKeyRecords[$member['id']]) || $member['value'] == 0){
				$member['isReceive'] = 1;
				$member['allowReceive'] = 1;
			}

			if($member['value'] != $member['hasSubUser']){
				$member['allowReceive'] = 1;
			}
		}

		$this->assign('userMembers', $userMembers);
		$this->assign('subUsers', $subUsers);
		$this->display();
	}

	public function tuiguang()
	{
		
		$uid = $_SESSION['uid'];
		
			$recharge = M("recharge"); // 实例化User对象
	
		$cash = M("cash"); // 实例化User对象
		  	$finance = M("user"); // 实例化User对象
			$results = $finance->order('time desc')->select();
			
			
	
	
		//$where = sUserList(); 
		
		if(isset($_GET["reg_ip"])){
			if(trim($_GET["reg_ip"])!=""){
				$where.=" and reg_ip like  '%".trim($_GET["reg_ip"])."%'";
			}
		}
		$wherea['status'] = '1';
		$where['status'] = '1';
		$result = $this->GetTeamMember($results,$uid);//所有分销
			//dump($result);exit;
			$arraya ='';
	foreach($result as $key=>$value){
				$arraya .= ($value['id']).',';
			$wherea['uid'] =$value['id'];
			$result[$key]['sum'] = $recharge->where($wherea)->sum('money');
			$result[$key]['tixiansum'] = $cash->where($wherea)->sum('money');
				 
		}
		
	$arraaa = explode(',', $arraya);  
    $arraaa = array_unique($arraaa);//内置数组去重算法  
    $data = implode(',', $arraaa);  
    $data = trim($data,',');//trim — 去除字符串首尾处的空白字符（或者其他字符）,假如不使用，后面会多个逗号  
	
		  
			$where['uid'] = array("in",$data);
			
			$tixian = $recharge->where($where)->sum('money');
			$chongzhi = $cash->where($where)->sum('money');

		$this->assign('results', $results);
		$this->assign('tixian', $tixian);
		$this->assign('chongzhi', $chongzhi);
		$this->assign('uid', $uid);
		$this->assign('results', $result);
			
		$this->display();
	}
		public function GetTeamMember($members, $mid) {
		$Teams=array();//最终结果
		$mids=array($mid);//第一次执行时候的用户id
		do {
			$othermids=array();
			$state=false;
			foreach ($mids as $valueone) {
				foreach ($members as $key => $valuetwo) {
					if($valuetwo['top']==$valueone){
						$Teams[]=$valuetwo;//找到我的下级立即添加到最终结果中
						$othermids[]=$valuetwo['id'];//将我的下级id保存起来用来下轮循环他的下级
						array_splice($members,$key,1);//从所有会员中删除他
						$state=true;   
					}
				}          
			}
			$mids=$othermids;//foreach中找到的我的下级集合,用来下次循环
		} while ($state==true);
	 
		return $Teams;
	}
	public function zhannei()//站内消息
	{
		$uid = $_SESSION['uid'];
		$invest = getData('station', 'all', 'uid=\'' . $uid . '\'', '', 'time desc');
		//var_dump($invest);
		$this->assign('invest', $invest);
		$this->display();
	}

	public function query()
	{
		$orderid = getValue('orderid');
		$order = getData('recharge', 1, 'orderid=\'' . $orderid . '\'');
		if ($order['status'] != 1 || empty($order)) {
			$this->ajaxReturn(0);
		}
		else {
			$this->ajaxReturn(1);
		}
	}

	public function logout()
	{
		$_SESSION['uid'] = '';
		msg('已退出登录！', 2, U('mobile/index'));
	}
	

	public function yuebaozhuanru()
	{
		$this->display();
	}
	public function addzhuanru(){
		$User = M("User"); // 实例化User对象
		$yuebao = M("yuebao"); // 实例化User对象

		$money = getValue('money');
		$uid = $_SESSION['uid'];

		$res = getData('user', 1, 'id = \'' . $uid . '\'');
			if($res['money'] < $money){
				msg('金额大于余额', 2, U('yuebaozhuanru'));
				
			}
			$result = $User->where(['id'=>$uid])->setDec('money',$money); // 用户的积分减5
			if ($result) {
					$data = array('uid' => $uid, 'paypal' => $money, 'time' => time());
							if(!getData('yuebao', 1, 'uid = \'' . $uid . '\'')){
								if (addData('yuebao', $data)) {
										msg('转入成功！', 2, U('yuebaozhuanru'));
									}
									else {
										msg('转入失败！', 2, U('yuebaozhuanru'));
									}
													
							}else{
								if ($yuebao->where(['uid'=>$uid])->setInc('paypal',$money)) {
									msg('转入成功！', 2, U('yuebaozhuanru'));
								} 
								else {
									msg('转入失败！', 2, U('yuebaozhuanru'));
								} 
							}
					
				
			}else {
				msg('参数错误', 2, U('yuebaozhuanru'));
			}	
		
	}
	public function yuebaozhuanchu(){
			    $uid = $_SESSION['uid'];
			$res = getData('yuebao', 1, 'uid = \'' . $uid . '\'');
			$this->assign('res', $res);
			$this->display();
		
	}
	public function addzhuanchu(){
		$User = M("User"); // 实例化User对象
		$yuebao = M("yuebao"); // 实例化User对象
		$money = getValue('money');
		$uid = $_SESSION['uid'];
		
			$res = getData('yuebao', 1, 'uid = \'' . $uid . '\'');
			if($res['paypal'] < $money){
				msg('金额大于余额', 2, U('yuebaozhuanchu'));
				
			}
		
		
		$result = $yuebao->where(['uid'=>$uid])->setDec('paypal',$money); // 用户的积分减5
		if($result){
			if($User->where(['id'=>$uid])->setInc('money',$money)){
				msg('转出成功！', 2, U('yuebaozhuanchu'));
				
			}else{
				msg('错误请联系开发者', 2, U('yuebaozhuanchu'));
			}
				
			
		}else{
				msg('错误请联系开发者', 2, U('yuebaozhuanchu'));
			
		}
		
	}
	
	
	//余额宝
	public function yuebao(){
	    $uid = $_SESSION['uid'];
	    $yeblist=getData('yuebao', 1, 'uid = \'' . $uid . '\'');
	    $this->assign('yeblist',$yeblist);
	    $today=strtotime( date("Y-m-d") );
			
		$data['time'] = array('egt',$today);
		$data['uid'] = $uid;
	    $daysy=M('yeb_sy')->where($data)->sum('sy');
	    $sy   =M('yeb_sy')->where('id',$uid)->sum('sy');
	    
	    $this->assign('daysy',$daysy);
	    $this->assign('sy',$sy);
	    $this->display();
	}
	//定时检测有没有当前时间获取收益的
	public function sy(){
	    $v = getData('info');
	    $yeblist=getData('yuebao');
	    //获取当前时间
	    $hour=date("H:i");
	    foreach($yeblist as $k=>$v){
	        
	        if($hour==date("H:i",$v['time'])){
	            $data = array('uid' => $uid, 'paypal' => $v['paypal'], 'time' => time(),'sy'=>$v['paypal']*$v['interest_rate']);
	            if(addData('yuebao', $data)){
	                M("user")->where(['uid'=>$v['uid']])->setInc('money',$v['paypal']*$v['interest_rate']);
	                echo "获取收益成功";
	            }else{
	                echo "获取收益失败";
	            }
	        }
	    }
	}
	
	//转入余额宝
	public function zryuebao(){
	    $v = getData('info');
	    $this->assign('v',$v);
	    if(IS_POST){
	        $money = getValue('money');
		    $uid = $_SESSION['uid'];
		    $result = M("user")->where(['id'=>$uid])->setDec('money',$money); // 用户的余额减去
			if ($result) {
			    
			    $data = array('uid' => $uid, 'paypal' => $money, 'time' => time());
			    if(!getData('yuebao', 1, 'uid = \'' . $uid . '\'')){
				    if (addData('yuebao', $data)) {
				        
						msg('转入成功！', 2, U('zryuebao'));
					}
					else {
						msg('转入失败！', 2, U('zryuebao'));
					}
										
				}else{
					if (M("user")->where(['uid'=>$uid])->setInc('paypal',$money)) {
						msg('转入成功！', 2, U('zryuebao'));
					} 
					else {
						msg('转入失败！', 2, U('zryuebao'));
					} 
				}
			    
			}
		    
		    
	    }
	    
	    
	    $this->display();
	}
	
	
	//转出余额宝
	public function zcyuebao(){
	    $User = M("User"); // 实例化User对象
		$yuebao = M("yuebao"); // 实例化User对象
		$money = getValue('money');
		$uid = $_SESSION['uid'];
		
		$res = getData('yuebao', 1, 'uid = \'' . $uid . '\'');
		if($res['paypal'] < $money){
			msg('金额大于余额', 2, U('zcyuebao'));
			
		}
		$result = $yuebao->where(['uid'=>$uid])->setDec('paypal',$money); // 用户的积分减5
		if($result){
			if($User->where(['id'=>$uid])->setInc('money',$money)){
				msg('转出成功！', 2, U('zcyuebao'));
				
			}else{
				msg('错误请联系开发者', 2, U('zcyuebao'));
			}
				
			
		}else{
				msg('错误请联系开发者', 2, U('zcyuebao'));
			
		}
	    $this->display();
	}
	
	

}

?>
