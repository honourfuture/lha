<?php
//dezend by http://www.yunlu99.com/
function getArticleType($tid)
{
	$data = getData('article_type', 1, 'id=\'' . $tid . '\'');
	return $data['name'];
}

function sendSms($phone, $code, $msg)
{
	if (smsStatus($code) == 0) {
		return reSmsCode('001');
	}

	$sms = getData('sms', 1, 'code=\'' . $code . '\'');

	if (empty($sms)) {
		return reSmsCode('002');
	}

	$name = getInfo('smsname');
	if (empty($name) || $name == '无') {
		return reSmsCode('003');
	}

	$before = date('Y-m-d H:i:s', strtotime('-1 day'));
	$beforeSms = getData('sms_list', 'all', 'phone=\'' . $phone . '\' AND time > \'' . $before . '\' AND code LIKE \'000#%\'');

	if (10 <= count($beforeSms)) {
		return reSmsCode('004');
	}

	$sign = '【' . $name . '】';
	$msg = str_replace('【', '[', $msg);
	$msg = str_replace('】', ']', $msg);
	$smsMsg = str_replace('###', $msg, $sign . $sms['msg']);
	//$apikey = getInfo('smskey');
	//$url = 'https://sms.yunpian.com/v2/sms/single_send.json';
	$encoded_text = urlencode($smsMsg);
	//$mobile = urlencode($phone);
	//$post_string = 'apikey=' . $apikey . '&text=' . $encoded_text . '&mobile=' . $mobile;
	//$msg = vpost($url, $post_string);
//	$msg = json_decode($msg, true);
	
	
    $id = 'lc168168';
    $pwd = '318bc882b01a6aac5806';
    $to = $phone;
    $encoded_text = urlencode($smsMsg);

    $url="http://utf8.api.smschinese.cn/?Uid={$id}&Key={$pwd}&smsMob={$to}&smsText={$encoded_text}";
	
	$msg = file_get_contents($url);	

	if ($msg) {
		$recode = '000';
	}
	else if (0 < $msg['code']) {
		$recode = '004';
	}
	else {
		if ($msg['code'] < 0 && -50 < $msg['code']) {
			$recode = '005';
		}
		else if ($msg['code'] == -50) {
			$recode = '006';
		}
		else {
			$recode = '009';
		}
	}

	$data = array('phone' => $phone, 'msg' => $smsMsg, 'code' => $recode . '#' . smsErrorCode($msg['code']), 'time' => date('Y-m-d H:i:s'));
	addData('sms_list', $data);
	return reSmsCode($recode);
}

function reSmsCode($code)
{
	$data = array('code' => $code, 'msg' => '未知');

	switch ($code) {
	case '000':
		$data['msg'] = '发送成功';
		break;

	case '001':
		$data['msg'] = '平台未启用短信通知';
		break;

	case '002':
		$data['msg'] = '平台未设置该模板';
		break;

	case '003':
		$data['msg'] = '平台未设置签名';
		break;

	case '004':
		$data['msg'] = '操作过于频繁';
		break;

	case '005':
		$data['msg'] = '短信权限不足';
		break;

	case '006':
		$data['msg'] = '短信接口调用失败';
		break;

	default:
		$data['code'] = '009';
		$data['msg'] = '未知错误';
	}

	return $data;
}

function smsErrorCode($code)
{
	$data = array(0 => '调用成功', 1 => '请求参数缺失', 2 => '请求参数格式错误', 3 => '账户余额不足', 4 => '关键词屏蔽', 5 => '未自动匹配到合适的模板', 6 => '添加模板失败', 7 => '模板不可用', 8 => '同一手机号30秒内重复', 9 => '同一手机号5分钟内重复', 10 => '手机号防骚扰名单过滤', 11 => '接口不支持GET方式调用', 12 => '接口不支持POST方式调用', 13 => '营销短信暂停发送', 14 => '解码失败', 15 => '签名不匹配', 16 => '签名格式不正确', 17 => '24小时内同一手机号发送次数超过限制', 18 => '签名校验失败', 19 => '请求已失效', 20 => '不支持的国家地区', 21 => '解密失败', 22 => '1小时内同一手机号发送次数超过限制', 23 => '发往模板支持的国家', 24 => '添加告警设置失败', 25 => '手机号和内容个数不匹配', 26 => '流量包错误', 27 => '未开通金额计费', 28 => '运营商错误', 33 => '同一个手机号同一验证码模板每30秒只能发送一条', 34 => '签名创建失败', 40 => '未开启白名单', 43 => '一天内同一手机号发送次数超过限制', 48 => '参数长度超过限制', -1 => '非法的apikey', -2 => 'API没有权限', -3 => 'IP没有权限', -4 => '访问频率超限', -7 => 'HTTP请求错误', -8 => '不支持流量业务', -50 => '不支持流量业务', -51 => '系统繁忙', -52 => '充值失败', -53 => '提交短信失败', -54 => '记录已存在', -55 => '记录不存在', -57 => '用户开通过固定签名功能，但签名未设置');
	return $data[$code];
}

function sProjectList()
{
	$title = getValue('title');
	$type = getValue('type', 'int');
	$where = '1';

	if (!empty($title)) {
		$where .= ' AND title LIKE \'%' . $title . '%\'';
	}

	if (!empty($type)) {
		$where .= ' AND type = \'' . $type . '\'';
	}

	return $where;
}
function project_class()
{
	$title = getValue('name');
	
	$where = '1';

	if (!empty($title)) {
		$where .= ' AND title LIKE \'%' . $title . '%\'';
	}


	return $where;
}
function sProjectAlreay()
{
	$title = getValue('title');
	$type = getValue('type', 'int');
	$phone = getValue('phone');
	$start = getValue('start');
	$end = getValue('end');
	$where = '1';

	if (!empty($title)) {
		$where .= ' AND title LIKE \'%' . $title . '%\'';
	}

	if (!empty($type)) {
		$where .= ' AND type1 = \'' . $type . '\'';
	}

	if (!empty($phone)) {
		$uid = getUserId($phone);
		$where .= ' AND uid = \'' . $uid . '\'';
	}

	if (!empty($start)) {
		$start .= ' 00:00:00';
		$where .= ' AND time >= \'' . $start . '\'';
	}

	if (!empty($end)) {
		$end .= ' 23:59:59';
		$where .= ' AND time <= \'' . $end . '\'';
	}

	return $where;
}

function sProjectReturn()
{
	$start = getValue('start');
	$end = getValue('end');
	$where = 'pay1 > 0';

	if (!empty($start)) {
		$start .= ' 00:00:00';
		$where .= ' AND time1 >= \'' . $start . '\'';
	}

	if (!empty($end)) {
		$end .= ' 23:59:59';
		$where .= ' AND time1 <= \'' . $end . '\'';
	}

	return $where;
}

function sFinancePayment()
{
	$phone = getValue('phone');
	$start = getValue('start');
	$end = getValue('end');
	$orderid = getValue('orderid');
	$pay = getValue('pay');
	$where = '1';

	if (!empty($phone)) {
		$uid = getUserId($phone);
		$where .= ' AND uid = \'' . $uid . '\'';
	}

	if (!empty($pay)) {
		$where .= ' AND type = \'' . $pay . '\'';
	}

	if (!empty($start)) {
		$start .= ' 00:00:00';
		$where .= ' AND time >= \'' . $start . '\'';
	}

	if (!empty($end)) {
		$end .= ' 23:59:59';
		$where .= ' AND time <= \'' . $end . '\'';
	}

	if (!empty($orderid)) {
		$where .= ' AND orderid LIKE \'%' . $orderid . '%\'';
	}

	return $where;
}

function sFinanceInvoice()
{
	$phone = getValue('phone');
	$start = getValue('start');
	$end = getValue('end');
	$status = getValue('status', 'int');
	$where = '1';

	if (!empty($phone)) {
		$uid = getUserId($phone);
		$where .= ' AND uid = \'' . $uid . '\'';
	}

	if (!empty($start)) {
		$start .= ' 00:00:00';
		$where .= ' AND time >= \'' . $start . '\'';
	}

	if (!empty($end)) {
		$end .= ' 23:59:59';
		$where .= ' AND time <= \'' . $end . '\'';
	}

	if (!empty($status)) {
		--$status;
		$where .= ' AND status = \'' . $status . '\'';
	}

	return $where;
}

function sFinanceStream()
{
	$phone = getValue('phone');
	$start = getValue('start');
	$end = getValue('end');
	$type = getValue('type', 'int');
	$min = getValue('min', 'float');
	$max = getValue('max', 'float');
	$where = '1';

	if (!empty($phone)) {
		$uid = getUserId($phone);
		$where .= ' AND uid = \'' . $uid . '\'';
	}

	if (!empty($start)) {
		$start .= ' 00:00:00';
		$where .= ' AND time >= \'' . $start . '\'';
	}

	if (!empty($end)) {
		$end .= ' 23:59:59';
		$where .= ' AND time <= \'' . $end . '\'';
	}

	if (!empty($type)) {
		$where .= ' AND type = \'' . $type . '\'';
	}

	if (!empty($min)) {
		$where .= ' AND money >= \'' . $min . '\'';
	}

	if (!empty($max)) {
		$where .= ' AND money <= \'' . $max . '\'';
	}

	return $where;
}

function sUserList()
{
	$phone = getValue('phone');
	$name = getValue('name');
	$member = getValue('member', 'int');
	$online = getValue('online', 'int');
	$where = '1';

	if (!empty($phone)) {
		$where .= ' AND phone = \'' . $phone . '\'';
	}

	if (!empty($name)) {
		$where .= ' AND name = \'' . $name . '\'';
	}

	if (!empty($member)) {
		$where .= ' AND member = \'' . $member . '\'';
	}

	if (!empty($online)) {
		$time = time() - 300;
		$where .= ' AND logintime >= \'' . $time . '\'';
	}

	return $where;
}

function sUserRelation()
{
	$phone = getValue('phone');
	$type = getValue('type', 'int');
	$where = '1';

	if (!empty($phone)) {
		if ($type == 1) {
			$uid = getUserId($phone);
			$tid = getUserField($uid, 'top');
			$where .= ' AND id = \'' . $tid . '\'';
		}
		else {
			$uid = getUserId($phone);
			$where .= ' AND top = \'' . $uid . '\'';
		}
	}

	return $where;
}

function sArticleList()
{
	$title = getValue('title');
	$type = getValue('type', 'int');
	$start = getValue('start');
	$end = getValue('end');
	$where = '1';

	if (!empty($title)) {
		$where .= ' AND title LIKE \'%' . $title . '%\'';
	}

	if (!empty($type)) {
		$where .= ' AND type = \'' . $type . '\'';
	}

	if (!empty($start)) {
		$start .= ' 00:00:00';
		$where .= ' AND time >= \'' . $start . '\'';
	}

	if (!empty($end)) {
		$end .= ' 23:59:59';
		$where .= ' AND time <= \'' . $end . '\'';
	}

	return $where;
}

function sSystemBanner()
{
	$type = getValue('type', 'int');
	$where = '1';

	if (!empty($type)) {
		$where .= ' AND type = \'' . $type . '\'';
	}

	return $where;
}

function setTxtWatermark($type = 'wechat')
{
	$info = 'qr_' . $type;
	$txt = getInfo($info);
	$txt = '';
	$strlen = ceil(strlen($txt) / 69);
	$image = new \Think\Image();
	$i = 0;

	while ($i < $strlen) {
		$sub = mb_substr($txt, $i * 23, 23, 'utf-8');
		$top = -220 + $i * 40;

		if ($i == 0) {
			$path = './Public/uploads/qr_' . $type . '_bg.png';
		}
		else {
			$path = './Public/uploads/qr_' . $type . '.png';
		}

		$savePath = './Public/uploads/qr_' . $type . '.png';
		$image->open($path)->text($sub, './Public/uploads/msyh.ttc', 22, '#ffffff', \Think\Image::IMAGE_WATER_SOUTHWEST, array(40, $top))->save($savePath);
		++$i;
	}
}

function setImgWatermark($type = 'wechat')
{
	$img = './Public/uploads/qr_' . $type . '_bg.png';
	$qrcode = './Public/uploads/qr_' . $type . '_img.png';
	$path = './Public/uploads/pay_' . $type . '.png';
	$image = new \Think\Image();
	$image->open($img)->water($qrcode, array(175, 310), 100)->save($path);
}

function setQrcode($type = 'wechat')
{
	Vendor('phpqrcode.phpqrcode');
	$object = new QRcode();
	$level = 10;
	$size = 18;
	$errorCorrectionLevel = intval($level);
	$matrixPointSize = intval($size);

	if ($type == 'wechat') {
		$txt = getInfo('qr_wechat_img');
		$path = './Public/uploads/qr_' . $type . '_img.png';
	}
	else {
		$txt = getInfo('qr_alipay_img');
		$path = './Public/uploads/qr_' . $type . '_img.png';
	}

	$object->png($txt, $path, $errorCorrectionLevel, $matrixPointSize, 2);
	$image = new \Think\Image();
	$image->open($path);
	$image->thumb(400, 400)->save($path);
}

function findCityByIp($ip)
{
	$data = file_get_contents('http://ip.taobao.com/service/getIpInfo.php?ip=' . $ip);
	return json_decode($data, $assoc = true);
}

function get_pic($pic_url){
    return  $url = "http://" . $_SERVER['SERVER_NAME'] . "/Uploads".$pic_url;
}
function isTemplate($name, $type)
{
	if ($type == 'index') {
		switch ($name) {
		case 'one':
			return 'index';
		case 'two':
			return 'index2';
		case 'three':
			return 'index3';
		case 'four':
			return 'index4';
		case 'five':
			return 'index5';
		default:
			return 'index';
		}
	}

	if ($type == 'lists') {
		switch ($name) {
		case 'one':
			return 'lists';
		case 'two':
			return 'lists2';
		case 'three':
			return 'lists3';
		case 'four':
			return 'lists4';
		case 'five':
			return 'lists5';
		default:
			return 'lists';
		}
	}
}

?>
