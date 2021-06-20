<?php
//dezend by http://www.yunlu99.com/
function isMobile()
{
	if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
		return true;
	}

	if (isset($_SERVER['HTTP_VIA'])) {
		return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
	}

	if (isset($_SERVER['HTTP_USER_AGENT'])) {
		$clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile');

		if (preg_match('/(' . implode('|', $clientkeywords) . ')/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
			return true;
		}
	}

	if (isset($_SERVER['HTTP_ACCEPT'])) {
		if (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))) {
			return true;
		}
	}

	return false;
}

function msg($msg, $time = 2, $url = '')
{
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>跳转提示</title>
    <meta name="viewport" content="initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no,target-densitydpi = medium-dpi">
    <style type="text/css">
        *{ padding: 0; margin: 0; }
        body{ background: #fff; font-family: "微软雅黑"; color: #333; font-size: 16px; }
    </style>
</head>
<body>
<div style="width: 100%;height: 1000px;position: fixed;top: 0;left: 0;background-color: rgba(0,0,0,0.35);">
    <div style="width: 320px;height: 180px;border-radius:3px;overflow:hidden;margin: auto;position: fixed;top: 0;left: 0;right: 0;bottom: 0;background-color: #fff;box-shadow: 0 0 15px #999;">
        <div style="width: 100%;height:50px;line-height: 50px;background-color: #cfcfcf;">
            <label style="margin-left:10px;color:#666;">พรอมต์</label>
            <label style="font-size: 12px;color:#888;float:right;display: block;margin-right: 10px;"><b id="wait">' . $time . '</b>秒后跳转</label>

            <div style="clear:both;"></div>
        </div>
        <div style="padding: 25px 10px;line-height: 30px;">
            <p style="background: url(\'/Public/pc/img/success.png\') no-repeat 0 -2px;;height: 32px;padding-left: 40px;">' . $msg . '</p>
            <p style="text-align: right;margin-top: 20px;font-size: 12px;">
                <a id="href" href="' . $url . '" style="display: inline-block;width: 80px;height: 30px;background-color: #888;color:#fff;text-align: center;text-decoration: none;">确定</a>
            </p>
        </div>
    </div>
</div>
<script type="text/javascript">
    (function(){
        var wait = document.getElementById("wait"),href = document.getElementById("href").href;
        var interval = setInterval(function(){
            var time = --wait.innerHTML;
            if(time <= 0) {
                location.href = href;
                clearInterval(interval);
            };
        }, 1000);
    })();
</script>
</body>
</html>';
	exit();
}
function dataarr($tree, $rootId = 0, $level = 0) {
	
    foreach($tree as $key=>$leaf) {  
        if($leaf['top'] == $rootId) {  
            echo str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level) .'名字：'. $leaf['name'].'--电话：'. $leaf['phone']. '下线<br/>';  
            foreach($tree as $l) {  
                if($l['top'] == $leaf['id']) {  
                    dataarr($tree, $leaf['id'], $level + 1);  
                    break;  
                }  
            }  
        }  
    }
	
}
function getValue($name, $type = 'str')
{
	$data = array(' ', '\'', '<', '>', '"', '&lt;', '&gt;', '&quot;', 'script', 'insert', 'delete', 'update', 'select', 'drop', 'exec', 'and', 'or', 'eval');

	if ($type == 'array') {
		$value = I($name);

		foreach ($value as $key => $i) {
			$value[$key] = str_ireplace($data, '', $i);
		}
	}
	else {
		$value = str_ireplace($data, '', I($name));

		switch ($type) {
		case 'str':
			$value = strval($value);
			break;

		case 'int':
			$value = intval($value);
			break;

		case 'float':
			$value = floatval($value);
			break;
		}
	}

	return $value;
}

function getContent($name)
{
	$data = array('script', 'insert', 'delete', 'update', 'select', 'drop', 'exec', 'and', 'or', 'eval');
	$value = I($name, '', 'htmlspecialchars');
	$value = str_ireplace($data, '', $value);
	return $value;
}

function getData($database, $type, $where = '', $limit = '', $order = 'id asc', $group = '', $join = '')
{
	if ($type == 'all') {
		$data = M($database)->where($where)->limit($limit)->order($order)->group($group);

		if($join){
			$data = $data->join($join);
		}
	}
	else {
		$data = M($database)->where($where)->limit($limit)->order($order);
	}

	if($type == 'all'){
		return $data->select();
	}else{
		return $data->find();
	}
}

function editData($database, $data, $where = '')
{
	$re = M($database)->where($where)->save($data);

	if ($re) {
		return true;
	}

	return false;
}

function addData($database, $data)
{
	$id = M($database)->add($data);
	return $id;
}

function delData($database, $where)
{
	$re = M($database)->where($where)->delete();

	if ($re) {
		return true;
	}

	return false;
}

function judge($str, $type)
{
	$char = '';

	if ($type == 'int') {
		$char = '/^\\d*$/';
	}
	else if ($type == 'email') {
		$char = '/([\\w\\-]+\\@[\\w\\-]+\\.[\\w\\-]+)/';
	}
	else if ($type == 'idcard') {
		$char = '/[0-9]{17}([0-9]|X)/';
	}
	else if ($type == 'name') {
		$char = '/^[\\x{4e00}-\\x{9fa5}]+[·•]?[\\x{4e00}-\\x{9fa5}]+$/u';
	}
	else if ($type == 'phone') {
		return true;
		$char = '/^1[3456789]{1}\\d{9}$/';
	}
	else if ($type == 'tel') {
		$char = '/(^(\\d{3,4}-)?\\d{7,8})$/';
	}
	else if ($type == 'date') {
		$char = '/^\\d{4}[\\-](0?[1-9]|1[012])[\\-](0?[1-9]|[12][0-9]|3[01])?$/';
	}
	else if ($type == 'time') {
		$char = '/^\\d{4}[\\-](0?[1-9]|1[012])[\\-](0?[1-9]|[12][0-9]|3[01])(\\s+(0?[0-9]|1[0-9]|2[0-3])\\:(0?[0-9]|[1-5][0-9])\\:(0?[0-9]|[1-5][0-9]))?$/';
	}
	else if ($type == 'exist') {
	}
	else {
		return false;
	}

	if (preg_match($char, $str)) {
		return true;
	}

	return false;
}

function addNumber($database, $field, $value, $where = '')
{
	$re = M($database)->where($where)->setInc($field, $value);
	return $re;
}

function minusNumber($database, $field, $value, $where = '')
{
	$re = M($database)->where($where)->setDec($field, $value);
	return $re;
}

function addFinance($uid, $money, $reason, $type = 1, $before)
{
	$user = getdata('user', 1, 'id=\'' . $uid . '\'');

	if (empty($user)) {
		return false;
	}

	$data = array('uid' => $uid, 'money' => $money, 'type' => $type, 'reason' => $reason, 'before' => $before ?: 0, 'time' => date('Y-m-d H:i:s'));
	if (empty($money) || $money <= 0 || empty($uid)) {
		return false;
	}

	if (adddata('finance', $data)) {
		return true;
	}

	return false;
}

function setNumber($database, $field, $value, $type = 1, $where = '')
{
	if ($type != 1) {
		$re = M($database)->where($where)->setDec($field, $value);
	}
	else {
		$re = M($database)->where($where)->setInc($field, $value);
	}

	return $re;
}

function setError($type, $max = 5, $end = 900)
{
	if (empty($_SESSION[$type])) {
		$data = array('num' => 1, 'time' => time());
		$_SESSION[$type] = $data;
		return true;
	}

	$data = $_SESSION[$type];
	$num = $data['num'];

	if ($max <= $num) {
		if (!empty($data['end'])) {
			if (time() <= $data['end']) {
				return false;
			}

			$data = array('num' => 1, 'time' => time());
			$_SESSION[$type] = $data;
			return true;
		}

		$_SESSION[$type]['end'] = time() + $end;
		return false;
	}

	$data = array('num' => $num + 1, 'time' => time());
	$_SESSION[$type] = $data;
	return true;
}

function isAdmin()
{
	if (empty($_SESSION['admin']) || empty($_SESSION['adminUser'])) {
		return false;
	}

	$account = $_SESSION['adminUser'];
	$user = $_SESSION['admin'];
	$admin = getdata('admin', 1, 'account=\'' . $account . '\'');
	if ($account == $user['account'] && $admin['password'] = $user['password']) {
		return true;
	}

	return false;
}

function getPage($database, $where, $num)
{
	$count = count(getdata($database, 'all', $where));
	$Page = new \Think\Page($count, $num);
	$Page->setConfig('header', '<span style="margin-left: 10px;">共<b>%TOTAL_ROW%</b>条记录&nbsp;第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</span>');
	$Page->setConfig('prev', '上一页');
	$Page->setConfig('next', '下一页');
	$Page->setConfig('first', '第一页');
	$Page->setConfig('theme', '<span>%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%</span>');
	return $Page;
}

function uploadImg($file, $path, $name = false)
{
	$upload = new \Think\Upload();
	$upload->maxSize = 3145728;
	$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
	$upload->rootPath = $path;
	$upload->savePath = '';
	$upload->subName = '';

	if ($name) {
		$upload->saveName = $name;
	}
	else {
		$upload->saveName = date('YmdHis');
	}

	$upload->replace = true;
	$info = $upload->uploadOne($file);

	if (!$info) {
		return $upload->getError();
	}

	return $info['savepath'] . $info['savename'];
}

function vpost($url, $data)
{
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
	curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	curl_setopt($curl, CURLOPT_HEADER, 0);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$tmpInfo = curl_exec($curl);

	if (curl_errno($curl)) {
		echo 'Errno' . curl_error($curl);
	}

	curl_close($curl);
	return $tmpInfo;
}

function getInfo($key = '')
{
	$info = getdata('info', 1);

	if (!empty($key)) {
		$info = $info[$key];
	}

	return $info;
}

function getReward($key = '')
{
	$reward = getdata('reward', 1);

	if (!empty($key)) {
		$reward = round($reward[$key], 2);
	}

	return $reward;
}

function exportExcel($fileName, $expTitle, $expCellName, $expTableData)
{
	ob_end_clean();
	$xlsTitle = iconv('utf-8', 'gb2312', $expTitle);
	$cellNum = count($expCellName);
	$dataNum = count($expTableData);
	require_once './Public/PHPExcel/PHPExcel.php';
	$objPHPExcel = new PHPExcel();
	$cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
	$i = 0;

	while ($i < $cellNum) {
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . '1', $expCellName[$i][1]);
		++$i;
	}

	$i = 0;

	while ($i < $dataNum) {
		$j = 0;

		while ($j < $cellNum) {
			$objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 2), $expTableData[$i][$expCellName[$j][0]]);
			++$j;
		}

		++$i;
	}
	header('pragma:public');
	header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
	header('Content-Disposition:attachment;filename=' . $fileName . '.xls');
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit();
}

function getDataSum($data, $where, $value)
{
	$data = getdata($data, 'all', $where);
	$sum = 0;

	foreach ($data as $d) {
		$sum += $d[$value];
	}

	return $sum;
}

?>
