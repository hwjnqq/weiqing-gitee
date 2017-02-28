<?php

defined('IN_IA') or exit('Access Denied');
include IA_ROOT . '/addons/we7_storex/function/function.php';
global $_W, $_GPC;
// paycenter_check_login();
$ops = array('display', 'post', 'delete', 'category_list', 'goods_list', 'more_goods');
$op = in_array($_GPC['op'], $ops) ? trim($_GPC['op']) : 'display';

check_params($op);

//获取店铺分类
if ($op == 'category_list'){
	$store_id = $_GPC['id'];//店铺id
	$data = pdo_getall('store_categorys', array('weid' => $_W['uniacid'], 'store_base_id' => $store_id, 'enabled' => 1), array('id', 'name'), '', 'displayorder DESC');
	if(!empty($data)){
		foreach ($data as $val){
			$store_categorys[$val['id']] = $val['name'];
		}
	}
	message(error(0, $store_categorys), '', 'ajax');
}
//i=281&c=entry&do=category&m=we7_storex&op=goods_list&id=6&first_id=10&
//获取一级分类下的二级分类以及商品
if ($op == 'goods_list'){
	$store_id = intval($_GPC['id']);//店铺id
	$store_info = get_store_info();
	if(empty($store_info)){
		message(error(-1, '店铺不存在'), '', 'ajax');
	}
	$first_id = intval($_GPC['first_id']);//一级分类id
	$first_class = pdo_get('store_categorys', array('weid' => $_W['uniacid'],'store_base_id' => $store_id, 'id' => $first_id));
	if(empty($first_class)){
		message(error(-1, '分类不存在'), '', 'ajax');
	}
	$sub_class = get_sub_class();//获取二级分类
	//存在二级分类就找其下的商品
	$fields = array('id', 'title', 'thumb', 'oprice', 'cprice', 'sold_num');
	$list = array();
	$goods = array();
	if(!empty($sub_class)){
		$goods = $sub_class;
		$list['have_subclass'] = 1;
		$condition = array('weid' => $_W['uniacid'], 'pcate' => $first_id, 'status' => 1);
		if($store_info['store_type'] == 1){//酒店
			$condition['hotelid'] = $store_id;
			$fields[] = 'hotelid';
			foreach ($sub_class as $key => $sub_classinfo){
				$condition['ccate'] = $sub_classinfo['id'];
				$goods_list = get_store_goods('hotel2_room', $condition, $fields);
				if(!empty($goods_list)){
					$goods[$key]['store_goods'] = array_slice($goods_list, 0, 2);
					$goods[$key]['total'] = count($goods_list);
				}
			}
		}else{
			$fields[] = 'store_base_id';
			$condition['store_base_id'] = $store_id;
			foreach ($sub_class as $key => $sub_classinfo){
				$condition['ccate'] = $sub_classinfo['id'];
				$goods_list = get_store_goods('store_goods', $condition, $fields);
				if(!empty($goods_list)){
					$goods[$key]['store_goods'] = array_slice($goods_list, 0, 2);
					$goods[$key]['total'] = count($goods_list);
				}
			}
		}
	}else{
		$list['have_subclass'] = 0;
		$condition = array('weid' => $_W['uniacid'], 'pcate' => $first_id, 'status' => 1);
		if($store_info['store_type'] == 1){
			$fields[] = 'hotelid';
			$condition['hotelid'] = $store_id;
			$goods_list = get_store_goods('hotel2_room', $condition, $fields);
			if(!empty($goods_list)){
				$goods['store_goods'] = array_slice($goods_list, 0, 2);
				$goods['total'] = count($goods_list);
			}
		}else{
			$fields[] = 'store_base_id';
			$condition['store_base_id'] = $store_id;
			$goods_list = get_store_goods('store_goods', $condition, $fields);
			if(!empty($goods_list)){
				$goods['store_goods'] = array_slice($goods_list, 0, 2);
				$goods['total'] = count($goods_list);
			}
		}
	}
	$list['list'] = $goods;
	message(error(0, $list), '', 'ajax');
}

//获取更多的商品信息
if ($op == 'more_goods'){
	$store_id = intval($_GPC['id']);//店铺id
	$sub_classid = intval($_GPC['sub_id']);//二级id
	$store_bases = pdo_get('store_bases', array('id' => $store_id), array('id', 'store_type', 'status'));
	if(empty($store_bases)){
		message(error(-1, '店铺不存在'), '', 'ajax');
	}else{
		if($store_bases['status'] == 0){
			message(error(-1, '管理员将该店铺设置为隐藏，请联系管理员'), '', 'ajax');
		}
	}
	if($store_bases['store_type'] == 1){
		$goods_list = pdo_getall('hotel2_room', array('ccate' => $sub_classid, 'hotelid' => $store_bases['id']));
	}else{
		$goods_list = pdo_getall('store_goods', array('ccate' => $sub_classid, 'store_base_id' => $store_bases['id']));
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 2;
	$list = array();
	$total = count($goods_list);
	if ($total <= $psize) {
		$list['list'] = $goods_list;
	} else {
		// 需要分页
		if($pindex > 0) {
			$list_array = array_chunk($goods_list, $psize, true);
			if(!empty($list_array[($pindex-1)])){
				foreach ($list_array[($pindex-1)] as $val){
					$list['list'][] = $val;
				}
			}
		} else {
			$list['list'] = $goods_list;
		}
	}
	$list['psize'] = $psize;
	$list['result'] = 1;
	$page_array = get_page_array($total, $pindex, $psize);
	ob_start();
	$list['code'] = ob_get_contents();
	ob_clean();
	$list['total'] = $total;
	$list['isshow'] = $page_array['isshow'];
	if ($page_array['isshow'] == 1) {
		$list['nindex'] = $page_array['nindex'];
	}
	message(error(0, $list), '', 'ajax');
}

