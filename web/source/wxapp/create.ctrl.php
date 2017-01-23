<?php
/**
 * 创建小程序
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('module');
load()->model('wxapp');

$dos = array('post', 'getapps', 'getpackage');
$do = in_array($do, $dos) ? $do : 'post';
$_W['page']['title'] = '小程序 - 新建版本';

if($do == 'post') {
	$uniacid = intval($_GPC['uniacid']);
	$wxapp_version_info = pdo_get('wxapp_versions', array('uniacid' => $uniacid));
	if(!empty($_GPC['wxappval'])) {
		$submit_val = json_decode(ihtml_entity_decode($_GPC['wxappval']), true);
				$request_cloud_data = array();
		$version = ($submit_val['version0'] ? $submit_val['version0'] : 0) .'.'.($submit_val['version1'] ? $submit_val['version1'] : 0).'.'.($submit_val['version2'] ? $submit_val['version2'] : 0);
		$name = trim($submit_val['name']);
		$multi['uniacid'] = $uniacid;
		$multi['title'] = $name;
		$multi['styleid'] = 0;
		pdo_insert('site_multi', $multi);
		$multi_id = pdo_insertid();
		$bottom_menu = array();
		foreach ($submit_val['menus'] as $menu_val) {
			$menu_val['defaultImage'] = empty($menu_val['defaultImage']) ? $_W['siteroot'].'web/resource/images/bottom-default.png' : $menu_val['defaultImage'];
			$menu_val['selectedImage'] = empty($menu_val['selectedImage']) ? $_W['siteroot'].'web/resource/images/bottom-default.png' : $menu_val['selectedImage'];
			$bottom_menu[] = array(
					'pagePath' => 'we7/page/index/index',
					'iconPath' => $menu_val['defaultImage'],
					'selectedIconPath' => $menu_val['selectedImage'],
					'text' => $menu_val['name']
				);
		}

		$modules = array();
		foreach ($submit_val['modules'] as $module_val) {
			$modules[$module_val['module']] = $module_val['version'];
		}
										
		$request_cloud_data = array(
			'name' => $submit_val['name'],
			'modules' => $modules,
			'siteInfo' => array(
					'uniacid' => $uniacid,
					'acid' => $acid,
					'multiid'  => $multi_id,
					'version'  => $version,
					'siteroot' => $_W['siteroot'].'app/index.php'
				),
		);
		if($submit_val['showmenu']) {
			$request_cloud_data['tabBar'] = array(
				'color' => $submit_val['buttom']['color'],
				'selectedColor' => $submit_val['buttom']['selectedColor'],
				'borderStyle' => 'black',
				'backgroundColor' => $submit_val['buttom']['boundary'],
				'list' => $bottom_menu
			);
		}

		$wxapp_version['uniacid'] = $uniacid;
		$wxapp_version['multiid'] = $multi_id;
		$wxapp_version['version'] = $version;
		$wxapp_version['modules'] = json_encode($request_cloud_data['modules']);
		$wxapp_version['design_method'] = intval($submit_val['type']);
		$wxapp_version['quickmenu'] = json_encode($request_cloud_data['tabBar']);
		$wxapp_version['createtime'] = time();
		switch ($wxapp_version['design_method']) {
			case 1:
				$wxapp_version['template'] = intval($submit_val['template']);
				break;
			case 2:
				break;
			case 3:
				$wxapp_version['redirect'] = json_encode($submit_val['tomodule']);
				break;
		}
		pdo_insert('wxapp_versions', $wxapp_version);
		$versionid = pdo_insertid();
		
		message('小程序创建成功！跳转后请自行下载打包程序', url('wxapp/account/switch', array('uniacid' => $uniacid)));
	}
	template('wxapp/create-post');
}
if($do == 'getpackage') {
	$uniacid = $_W['uniacid'];
	$versionid = $_GPC['versionid'];
	if(empty($uniacid) || !is_numeric($uniacid) || empty($versionid) || !is_numeric($versionid)) {
		message('参数错误！');
	}
	$request_cloud_data = array();
	$account_wxapp_info = pdo_get('account_wxapp', array('uniacid' => $uniacid));
	$wxapp_version_info = pdo_get('wxapp_versions', array('uniacid' => $uniacid, 'id' => $versionid));
	$request_cloud_data['name'] = $account_wxapp_info['name'];
	$request_cloud_data['modules'] = json_decode($wxapp_version_info['modules'], true);
	$request_cloud_data['siteInfo'] = array(
			'uniacid' => $uniacid,
			'acid' => $account_wxapp_info['acid'],
			'multiid' => $wxapp_version_info['multiid'],
			'version' => $wxapp_version_info['version'],
			'siteroot' => $_W['siteroot'].'app/index.php'
		);
	$request_cloud_data['tabBar'] = json_decode($wxapp_version_info['quickmenu'], true);
	$result = wxapp_getpackage($request_cloud_data);

	if(is_error($result)) {
		message($result['message']);
	}else {
		header('content-type: application/zip');
		header('content-disposition: attachment; filename="'.$request_cloud_data['name'].'.zip"');
		echo $result;
	}
	exit;
}
if($do == 'getapps') {
	$apps = array();
	$apps = cache_load('packageapps');
	if(empty($apps)) {
		$module_list = uni_modules();
		foreach ($module_list as $key => $module) {
			if($module['type'] != 'system' && !empty($module['version'])) {
								if($module['issystem']) {
					$path = '../framework/builtin/' . $module['name'];
				} else {
					$path = '../addons/' . $module['name'];
				}
				$cion = $path . '/icon-custom.jpg';
				if(!file_exists($cion)) {
					$cion = $path . '/icon.jpg';
					if(!file_exists($cion)) {
						$cion = './resource/images/nopic-small.jpg';
					}
				}
								$m = module_entries($module['name'], array('home'));
				if(!empty($m['home'])) {
					foreach($m['home'] as $val) {
						$rst = array();
						if(isset($val['eid']) && !empty($val['eid'])) {
							$rst = module_entry($val['eid']);
							$rst['module_title'] = $module['title'];
							$rst['module_icon'] = $cion;
							$rst['version'] = $module['version'];
							$apps[] = $rst;
						}
					}	
				}
			}
		}
		cache_write('packageapps', $apps);				
	}
	message(error(0, $apps), '', 'ajax');
}