<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('display');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

if ($op == 'display') {
	$id = intval($_GPC['id']);
	if (checksubmit('submit')) {
		$data = array(
			'weid' => $_W['uniacid'],
			'location_p' => $_GPC['district']['province'],
			'location_c' => $_GPC['district']['city'],
			'location_a' => $_GPC['district']['district'],
			'version' => $_GPC['version'],
			'user' => $_GPC['user'],
			'reg' => $_GPC['reg'],
			'regcontent' => $_GPC['regcontent'],
			'bind' => $_GPC['bind'],
			'ordertype' => $_GPC['ordertype'],
			'paytype1' => $_GPC['paytype1'],
			'paytype2' => $_GPC['paytype2'],
			'paytype3' => $_GPC['paytype3'],
			'is_unify' => $_GPC['is_unify'],
			'tel' => $_GPC['tel'],
			'refund' => intval($_GPC['refund']),
			'email' => $_GPC['email'],
			'mobile' => $_GPC['mobile'],
			'smscode' => $_GPC['smscode'],
			'nickname' => trim($_GPC['nickname']),
			'location' => $_GPC['location'],
			'credit_pw' => !empty($_GPC['credit_pw_mode']) ? intval($_GPC['credit_pw']) : 2,
			'credit_pw_mode' => iserializer($_GPC['credit_pw_mode']),
		);
		if ($data['template'] && $data['templateid'] == '') {
			message('请输入模板ID', referer(), 'error');
		}
		if (!empty($id)) {
			pdo_update('storex_set', $data, array('id' => $id));
		} else {
			pdo_insert('storex_set', $data);
		}
		$cachekey = "wn_storex_set:{$_W['uniacid']}";
		cache_delete($cachekey);
		$set = get_storex_set();
		itoast('保存设置成功!', referer(), 'success');
	}
	$set = get_storex_set();
	include $this->template('hotelset');
}