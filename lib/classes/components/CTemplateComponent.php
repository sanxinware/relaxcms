<?php

/**
 * @file
 *
 * @brief 
 *  消息
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CTemplateComponent extends CTreeDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CTemplateComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
}
