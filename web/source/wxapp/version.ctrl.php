<?php
/**
 * 小程序欢迎页
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('wxapp');
load()->model('welcome');

$dos = array('display', 'home', 'get_daily_visittrend');
$do = in_array($do, $dos) ? $do : 'display';
$_W['page']['title'] = '小程序 - 管理';

$uniacid = intval($_GPC['uniacid']);
$version_id = intval($_GPC['version_id']);
if (!empty($uniacid)) {
	$wxapp_info = wxapp_fetch($uniacid);
}
if (!empty($version_id)) {
	$version_info = wxapp_version($version_id);
	$wxapp_info = wxapp_fetch($version_info['uniacid']);
}

if ($do == 'display') {
	$wxapp_version_list = wxapp_version_all($uniacid);
	template('wxapp/version-display');
}

if ($do == 'home') {
	$last_uniacid = uni_account_last_switch();
	if (empty($last_uniacid)) {
		itoast('', url('wxapp/display'), 'info');
	}
	if (!empty($last_uniacid) && $last_uniacid != $_W['uniacid']) {
		wxapp_switch($last_uniacid,  url('wxapp/display/home'));
	}
	if ($version_info['design_method'] == WXAPP_TEMPLATE) {
		$version_site_info = wxapp_site_info($version_info['multiid']);
	}
	$role = permission_account_user_role($_W['uid'], $wxapp_info['uniacid']);

	$notices = welcome_notices_get();
	template('wxapp/version-home');
}

if ($do == 'get_daily_visittrend') {
	wxapp_update_daily_visittrend();
	//昨日指标
	$yesterday = date('Ymd', strtotime('-1 days'));
	$yesterday_stat = pdo_get('wxapp_general_analysis', array('uniacid' => $_W['uniacid'], 'type' => '2', 'ref_date' => $yesterday));
	if (empty($yesterday_stat)) {
		$yesterday_stat = array('session_cnt' => 0, 'visit_pv' => 0, 'visit_uv' => 0, 'visit_uv_new' => 0);
	}
	iajax(0, array('yesterday' => $yesterday_stat), '');
}