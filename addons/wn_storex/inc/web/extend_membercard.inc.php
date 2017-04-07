<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('card_setting', 'member_list', 'signmanage', 'noticemanage');
$op = in_array($_GPC['op'], $ops) ? trim($_GPC['op']) : 'signmanage';

if ($op == 'signmanage') {
	$url = $this->createWebUrl('signmanage', array('op' => 'sign-credit'));
	header("Location: {$url}");
	die;
}

if ($op == 'noticemanage') {
	$url = $this->createWebUrl('noticemanage', array('op' => 'list'));
	header("Location: {$url}");
	die;
}

// include $this->template('extend_membercard');