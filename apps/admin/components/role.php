<?php

/**
 * @file
 *
 * @brief 
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class RoleComponent extends CRoleComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);		
	}
	
	function RoleComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
}