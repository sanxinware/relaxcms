<?php
/**
 * @file
 *
 * @brief 
 * 导航
 *
 * Copyright (c), 2025, relaxcms.com
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class NavModel extends CNavModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function NavModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}
