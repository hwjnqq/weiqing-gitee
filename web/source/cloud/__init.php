<?php
/**
 * 云服务相关
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if (in_array($action, array('sms', 'sms-sign'))) {
	define('FRAME', 'system');
}
if ($action == 'process') {
	define('FRAME', '');
} else {
	define('FRAME', 'site');
}

if(in_array($action, array('profile', 'device', 'callback', 'appstore', 'sms'))) {
	$do = $action;
	$action = 'redirect';
}
if($action == 'touch') {
	exit('success');
}