<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('display', 'post', 'delete');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

// $storeid = intval($_GPC['storeid']);
// $store = pdo_get('storex_bases', array('weid' => $_W['uniacid'], 'id' => $storeid), array('id', 'title', 'store_type'));
$store = $_W['wn_storex']['store_info'];
$storeid = $store['id'];
if ($op == 'display') {
	load()->func('tpl');
	if (!empty($_GPC['displayorder'])) {
		foreach ($_GPC['displayorder'] as $id => $displayorder) {
			pdo_update('storex_categorys', array('displayorder' => $displayorder), array('id' => $id, 'weid' => $_W['uniacid']));
		}
		message('分类排序更新成功！', $this->createWebUrl('goods_category', array('op' => 'display', 'storeid' => $storeid)), 'success');
	}
	$children = array();
	$category = pdo_getall('storex_categorys', array('weid' => $_W['uniacid'], 'store_base_id' => $storeid), array(), '', array('store_base_id DESC', 'parentid ASC', 'displayorder DESC'));
	foreach ($category as $index => &$row_info) {
		$row_info['store_title'] = $store['title'];
		if (empty($row_info['store_title'])) {
			unset($category[$index]);
		}
		if (!empty($row_info['parentid'])) {
			if ($store['store_type'] != 1) {
				$children[$row_info['parentid']][] = $row_info;
			}
			unset($category[$index]);
		}
	}
	unset($row_info);
	include $this->template('store/category');
}

if ($op == 'post') {
	$parentid = intval($_GPC['parentid']);
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		$category = pdo_get('storex_categorys', array('id' => $id, 'weid' => $_W['uniacid']));
	} else {
		$category = array(
			'displayorder' => 0,
		);
	}
	if (!empty($parentid)) {
		$parent = pdo_get('storex_categorys', array('id' => $parentid), array('id', 'name', 'category_type'));
		if (empty($parent)) {
			message('抱歉，上级分类不存在或是已经被删除！', $this->createWebUrl('post'), 'error');
		}
	}
	if (checksubmit('submit')) {
		if (empty($storeid)) {
			message('请选择店铺', $this->createWebUrl('post'), 'error');
		}
		if (empty($_GPC['name'])) {
			message('抱歉，请输入分类名称！');
		}
		$category_type = empty($_GPC['category_type']) ? 2 : intval($_GPC['category_type']);
		if (!empty($parent)) {
			$category_type = $parent['category_type'];
		}
		$data = array(
			'weid' => $_W['uniacid'],
			'name' => $_GPC['name'],
			'enabled' => intval($_GPC['enabled']),
			'displayorder' => intval($_GPC['displayorder']),
			'isrecommand' => intval($_GPC['isrecommand']),
			'description' => $_GPC['description'],
			'parentid' => $parentid,
			'thumb' => $_GPC['thumb'],
			'category_type' => $category_type,
		);
		$data['store_base_id'] = $storeid;
		if (!empty($id)) {
			unset($data['parentid']);
			pdo_update('storex_categorys', $data, array('id' => $id, 'weid' => $_W['uniacid']));
			if ($category['id'] == $id && $data['category_type'] != $category['category_type'] && $stores[$storeid]['store_type'] == 1) {
				pdo_update('storex_categorys', array('category_type' => $data['category_type']), array('parentid' => $id, 'weid' => $_W['uniacid']));
				pdo_update('storex_room', array('is_house' => $data['category_type']), array('pcate' => $id, 'weid' => $_W['uniacid'], 'hotelid' => $storeid));
			}
			load()->func('file');
			file_delete($_GPC['thumb_old']);
		} else {
			pdo_insert('storex_categorys', $data);
			$id = pdo_insertid();
		}
		message('更新分类成功！', $this->createWebUrl('goods_category', array('op' => 'display', 'storeid' => $storeid)), 'success');
	}
	include $this->template('store/category');
}

if ($op == 'delete') {
	$id = intval($_GPC['id']);
	$category = pdo_get('storex_categorys', array('id' => $id, 'weid' => intval($_W['uniacid'])), array('id', 'parentid', 'store_base_id'));
	if (empty($category)) {
		message('抱歉，分类不存在或是已经被删除！', $this->createWebUrl('goods_category', array('op' => 'display', 'storeid' => $storeid)), 'error');
	}
	if ($store['store_type'] == 1 ) {
		if ($category['parentid'] == 0) {
			pdo_delete('storex_room', array('pcate' => $id, 'weid' => $_W['uniacid']));
			pdo_delete('storex_categorys', array('id' => $id, 'parentid' => $id), 'OR');
			message('分类删除成功！', $this->createWebUrl('goods_category', array('op' => 'display', 'storeid' => $storeid)), 'success');
		}
		pdo_delete('storex_room', array('ccate' => $id, 'weid' => $_W['uniacid']));
		pdo_delete('storex_categorys', array('id' => $id, 'weid' => $_W['uniacid']));
		message('分类删除成功！', $this->createWebUrl('goods_category', array('op' => 'display', 'storeid' => $storeid)), 'success');
	}
	if ($category['parentid'] == 0) {
		pdo_delete('storex_goods', array('pcate' => $id, 'weid' => $_W['uniacid']));
		pdo_delete('storex_categorys', array('id' => $id, 'parentid' => $id), 'OR');
		message('分类删除成功！', $this->createWebUrl('goods_category', array('op' => 'display', 'storeid' => $storeid)), 'success');
	}
	pdo_delete('storex_goods', array('ccate' => $id, 'weid' => $_W['uniacid']));
	pdo_delete('storex_categorys', array('id' => $id, 'weid' => $_W['uniacid']));
	message('分类删除成功！', $this->createWebUrl('goods_category', array('op' => 'display', 'storeid' => $storeid)), 'success');
}