<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/app/source/utility/style.ctrl.php : v b53c8ba00893 : 2014/06/16 12:17:57 : RenChao $
 */
defined('IN_IA') or exit('Access Denied');

load()->model('app');
app_update_today_visit($_GPC['m']);