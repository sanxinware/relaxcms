<?php

/**
 * @file
 *
 * @brief 
 *  系统变量管理
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class VarComponent extends CVarComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function VarComponent($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	
	
}