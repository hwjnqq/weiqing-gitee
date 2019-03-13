<?php

/**
 * 工单系统
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');
load()->classs('cloudapi');
load()->model('message');

if($do == 'display') { //系统工单
	$message_id = intval($_GPC['message_id']);
	message_notice_read($message_id);
	$siteurl = $_W['siteroot'];
	$cloud = new CloudApi();
	$uuid = $_GPC['uuid'];
	$data = $cloud->get('system','workorder', array('do'=>'siteworkorder'), 'json');
	if(is_error($data)) {
		itoast('无权限进入工单系统');
	}
	$iframe_url = $data['data']['url'].'&from='.urlencode($siteurl).'&uuid='.$uuid;
	template('system/workorder');
}

if($do == 'module') { //模块工单

	$name = trim($_GPC['name']);
	if(empty($name)){
		itoast('模块参数错误');
	}
	$module = module_fetch($name);
	if(!$module) {
		itoast('未找到模块');
	}
	$module_name = $name;
	$module_version = $module['version'];
	$issystem = $module['issystem'];
	$module_type = 'module';
	$from = urlencode($_W['siteroot']);
	$param = http_build_query(compact('module_name', 'module_version', 'module_type', 'from'));
	$cloud = new CloudApi();
	$data = $cloud->get('system','workorder', array('do'=>'siteworkorder'), 'json');
	if(is_error($data)) {
		echo json_encode(array('errno'=>0, 'message'=>'无权限进入工单系统'));
		exit;
	}
	$iframe_url = $data['data']['url'] . '&' . $param;
	echo json_encode(array('errno'=>0, 'message'=>'', 'data'=>array('url'=>$iframe_url)));
	exit;
}




