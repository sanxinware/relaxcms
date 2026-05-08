<?php

/**
 * @file
 *
 * @brief 
 *  操作日志
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class LogComponent extends CLogComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function LogComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function detail(&$options=array())
	{
		$this->enableMenuItem('edit', false);
		parent::detail($options);
	}
}
