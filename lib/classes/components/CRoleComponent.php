<?php

/**
 * @file
 *
 * @brief 
 *  用户角色管理
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CRoleComponent extends CDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
		
	}
	
	function CRoleComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function _init()
	{
		parent::_init();
		$this->_modname = 'role';
	}
}