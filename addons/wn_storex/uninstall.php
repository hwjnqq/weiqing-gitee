<?php 
$wn_storex_table = array(
	'storex_hotel',
	'storex_brand',
	'storex_business',
	'storex_comment',
	'storex_comment_clerk',
	'storex_member',
	'storex_order',
	'storex_reply',
	'storex_room',
	'storex_room_price',
	'storex_set',
	'storex_code',
	'storex_bases',
	'storex_categorys',
	'storex_goods',
	'storex_clerk',
	'storex_notices',
	'storex_notices_unread',
	'storex_sign_record',
	'storex_sign_set',
	'storex_mc_card',
	'storex_mc_card_members',
	'storex_mc_card_record',
	'storex_mc_member_property',
	'storex_activity_exchange',
	'storex_coupon',
	'storex_coupon_activity',
	'storex_coupon_record',
	'storex_coupon_store',
	'storex_activity_stores',
	'storex_activity_clerks',
	'storex_paycenter_order',
	'storex_users_permission',
	'storex_activity_clerk_menu',
	'storex_refund_logs',
	'storex_sales',
	'storex_homepage',
	'storex_order_logs',
	'storex_member_level',
	'storex_goods_extend',
	'storex_market',
	'storex_agent_apply',
	'storex_agent_level',
	'storex_agent_log',
	'storex_agent_apply_log',
	'storex_room_assign',
	'storex_sales_package',
	'storex_tags',
	'storex_admin_logs',
	'storex_blast_message',
	'storex_blast_user',
	'storex_blast_stat',
	'storex_clerk_pay',
	'storex_wxcard_reply',
);
foreach ($wn_storex_table as $table){
	if(pdo_tableexists($table)){
		pdo_query("DROP TABLE " .tablename($table));
	}
}
?>