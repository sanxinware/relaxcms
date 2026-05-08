<?php

/**
 * @file
 *
 * @brief 
 * 
 * App管理类
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class AppModel extends CClientAppModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function AppModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}