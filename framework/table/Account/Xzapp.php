<?php
/**
 * [WeEngine System] Copyright (c) 2014 W7.CC
 */
namespace We7\Table\Account;

class Xzapp extends \We7Table {
	protected $tableName = 'account_xzapp';
	protected $primaryKey = 'acid';
	protected $field = array(
		'acid',
		'uniacid',
		'name',
	);
	protected $default = array(
		'acid' => '',
		'uniacid' => '',
		'name' => '',
	);

	public function getByAcid($acid) {
		return $this->query->where('acid' , $acid)->get();
	}
}