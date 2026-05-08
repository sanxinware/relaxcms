<?php

/**
 * @file
 *
 * @brief 
 * 
 * 存储管理
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class StorageComponent extends CDTComponent
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function StorageComponent($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function authBasicRequestForStorage(&$options=array())
	{
		$m = $this->getModel();
		$res = $m->authBasicRequestForStorage($_REQUEST);
		showStatus($res?0:-1);
	}
}