<?php

/**
 * @file
 *
 * @brief 
 * 
 * 我的模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CMyModel extends CModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function CMyModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function getActions($row=array(), &$options=array())
	{
		$defactions = parent::getActions($row, $options);
		
		unset($defactions['edit']);
		unset($defactions['del']);
		
		return  $defactions;
	}
		
	public function select($params=array(), &$options=array())
	{
		$uid = get_uid();
		$params['cuid'] = $uid;
		
		$res = parent::select($params, $options);
		return $res;
	}
}
