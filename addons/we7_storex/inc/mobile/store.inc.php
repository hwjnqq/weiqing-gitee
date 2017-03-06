<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('store_list', 'store_detail');
$op = in_array($_GPC['op'], $ops) ? trim($_GPC['op']) : 'error';

check_params();
//获取店铺列表
if ($op == 'store_list') {
	$setting = pdo_get('storex_set', array('weid' => $_W['uniacid']), array('id', 'version'));
	if ($setting['version'] == 0) {//单店
		$limit = array(1,1);
	}
	$storex_bases = pdo_getall('storex_bases', array('weid' => $_W['uniacid'], 'status' => 1), array(), '', 'displayorder DESC', $limit);
	foreach ($storex_bases as $key => $info) {
		$storex_bases[$key]['thumb'] = tomedia($info['thumb']);
		$info['thumbs'] =  iunserializer($info['thumbs']);
		$storex_bases[$key]['timestart'] = date("G:i", $info['timestart']);
		$storex_bases[$key]['timeend'] = date("G:i", $info['timeend']);
		if (!empty($info['thumbs'])) {
			foreach ($info['thumbs'] as $k => $url) {
				$storex_bases[$key]['thumbs'][$k] = tomedia($url);
			}
		}
	}
	$store_list = array();
	$store_list['version'] = $setting['version'];
	$store_list['stores'] = $storex_bases;
	message(error(0, $store_list), '', 'ajax');
}
//获取某个店铺的详细信息
if ($op == 'store_detail') {
	$setting = pdo_get('storex_set', array('weid' => $_W['uniacid']));
	$store_id = intval($_GPC['store_id']);//店铺id
	$store_detail = pdo_get('storex_bases', array('weid' => $_W['uniacid'], 'id' => $store_id));
	if (empty($store_detail)) {
        message(error(-1, '店铺不存在'), '', 'ajax');
    } else {
        if ($store_detail['status'] == 0) {
            message(error(-1, '店铺已隐藏'), '', 'ajax');
        }
    }
	if (!empty($store_detail['store_info'])) {
		$store_detail['store_info'] = htmlspecialchars_decode($store_detail['store_info']);
	}
	$store_detail['thumb'] = tomedia($store_detail['thumb']);
	if (!empty($store_detail['thumbs'])) {
		$store_detail['thumbs'] =  iunserializer($store_detail['thumbs']);
		$store_detail['thumbs'] = format_url($store_detail['thumbs']);
	}
	if (!empty($store_detail['detail_thumbs'])) {
		$store_detail['detail_thumbs'] =  iunserializer($store_detail['detail_thumbs']);
		$store_detail['detail_thumbs'] = format_url($store_detail['detail_thumbs']);
	}
	if ($store_detail['store_type'] == 1) {
		$store_extend_info = pdo_get($store_detail['extend_table'], array('weid' => $_W['uniacid'], 'store_base_id' => $store_id));
		if (!empty($store_extend_info)) {
			unset($store_extend_info['id']);
			if (empty($store_extend_info['device'])) {
				$devices = array(
						array('isdel' => 0, 'value' => '有线上网'),
						array('isdel' => 0, 'isshow' => 0, 'value' => 'WIFI无线上网'),
						array('isdel' => 0, 'isshow' => 0, 'value' => '可提供早餐'),
						array('isdel' => 0, 'isshow' => 0, 'value' => '免费停车场'),
						array('isdel' => 0, 'isshow' => 0, 'value' => '会议室'),
						array('isdel' => 0, 'isshow' => 0, 'value' => '健身房'),
						array('isdel' => 0, 'isshow' => 0, 'value' => '游泳池')
				);
			} else {
				$store_extend_info['device'] = iunserializer($store_extend_info['device']);
			}
			$store_detail = array_merge($store_detail, $store_extend_info);
		}
	}
	$store_detail['version'] = $setting['version'];
	message(error(0, $store_detail), '', 'ajax');
}