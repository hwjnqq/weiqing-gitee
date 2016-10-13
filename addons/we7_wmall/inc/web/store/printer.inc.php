<?php
/**
 * 超级外卖模块微站定义
 * @author strday
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$_W['page']['title'] = '打印机管理-' . $_W['wmall']['module']['name'];
mload()->model('store');
mload()->model('print');

$store = store_check();
$sid = $store['id'];
$do = 'printer';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';

if($op == 'list') {
	$data = pdo_fetchall('SELECT * FROM ' . tablename('tiny_wmall_printer') . ' WHERE uniacid = :uniacid AND sid = :sid', array(':uniacid' => $_W['uniacid'], ':sid' => $sid));
	if(!empty($data)) {
		foreach($data as &$da) {
			if(!empty($da['print_no']) && !empty($da['key'])) {
				$da['status_cn'] = print_query_printer_status($da['type'], $da['print_no'], $da['key'], $da['member_code']);
			} else {
				$da['status_cn'] = '未知';
			}
		}
	}
	$types = print_printer_types();
	include $this->template('store/printer');
} 

if($op == 'post') {
	$id = intval($_GPC['id']);
	if($id > 0) {
		$item = pdo_fetch('SELECT * FROM ' . tablename('tiny_wmall_printer') . ' WHERE uniacid = :uniacid AND id = :id', array(':uniacid' => $_W['uniacid'], ':id' => $id));
	} 
	if(empty($item)) {
		$item = array('status' => 1, 'print_nums' => 1, 'type' => 'feie');
	}
	if(checksubmit('submit')) {
		$data['type'] = trim($_GPC['type']);
		$data['status'] = intval($_GPC['status']); 
		$data['name'] = !empty($_GPC['name']) ? trim($_GPC['name']) : message('打印机名称不能为空', '', 'error');
		$data['print_no'] = !empty($_GPC['print_no']) ? trim($_GPC['print_no']) : message('机器号不能为空', '', 'error');
		$data['key'] = trim($_GPC['key']);
		$data['member_code'] = trim($_GPC['member_code']);
		$data['print_nums'] = intval($_GPC['print_nums']) ? intval($_GPC['print_nums']) : 1;
		if(!empty($_GPC['qrcode_link']) && (strexists($_GPC['qrcode_link'], 'http://') || strexists($_GPC['qrcode_link'], 'https://'))) {
			$data['qrcode_link'] = trim($_GPC['qrcode_link']);
		}
		$data['print_header'] = trim($_GPC['print_header']);
		$data['print_footer'] = trim($_GPC['print_footer']);
		$data['uniacid'] = $_W['uniacid'];
		$data['sid'] = $sid;
		if(!empty($item) && $id) {
			pdo_update('tiny_wmall_printer', $data, array('uniacid' => $_W['uniacid'], 'id' => $id));
		} else {
			pdo_insert('tiny_wmall_printer', $data);
		}
		message('更新打印机设置成功', $this->createWebUrl('printer', array('op' => 'list')), 'success');
	}
	include $this->template('store/printer');
} 

if($op == 'del') {
	$id = intval($_GPC['id']);
	pdo_delete('tiny_wmall_printer', array('uniacid' => $_W['uniacid'], 'id' => $id));
	message('删除打印机成功', referer(), 'success');
} 

if($op == 'log_del') {
	$id = intval($_GPC['id']);
	pdo_delete('tiny_wmall_order_print_log', array('uniacid' => $_W['uniacid'], 'id' => $id));
	message('删除打印记录成功', referer(), 'success');
} 

if($op == 'log') {
	$id = intval($_GPC['id']);
	$item = pdo_fetch('SELECT * FROM ' . tablename('tiny_wmall_printer') . ' WHERE uniacid = :uniacid AND id = :id', array(':uniacid' => $_W['uniacid'], ':id' => $id));
	if(empty($item)) {
		message('打印机不存在或已删除', $this->createWebUrl('print', array('op' => 'list')), 'success');
	}
	if(!empty($item['print_no']) && !empty($item['key'])) {
		$wprint = new wprint();
		$status = $wprint->QueryPrinterStatus($item['print_no'], $item['key']);
		if(is_error($status)) {
			$status = '查询打印机状态失败。请刷新页面重试';
		}
	}
	$condition = ' WHERE a.uniacid = :aid AND a.sid = :sid AND a.pid = :pid';
	$params[':aid'] = $_W['uniacid']; 
	$params[':sid'] = $sid; 
	$params[':pid'] = $id; 
	if(!empty($_GPC['oid'])) {
		$oid = trim($_GPC['oid']);
		$condition .= ' AND a.oid = :oid';
		$params[':oid'] = $oid; 
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;

	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('tiny_wmall_order_print_log') . ' AS a ' . $condition, $params);
	$data = pdo_fetchall('SELECT a.*,b.username,b.mobile FROM ' . tablename('tiny_wmall_order_print_log') . ' AS a LEFT JOIN' . tablename('tiny_wmall_order') . ' AS b ON a.oid = b.id' . $condition . ' ORDER BY addtime DESC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, $params);
	$pager = pagination($total, $pindex, $psize);
	$types = print_printer_types();
	include $this->template('store/printer');
} 