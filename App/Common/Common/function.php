<?php
//dezend by http://www.yunlu99.com/
$now = time();
$end = '2029-06-01 09:30:00';

if (strtotime($end) <= $now) {
	exit('Unauthorized ' . $end);
}

include 'system.php';
include 'project.php';
include 'user.php';
include 'sms.php';
include 'other.php';

?>
