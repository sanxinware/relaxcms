<?php

/**
 * @file
 *
 * @brief 
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class SessionComponent extends CDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);		
	}	

	function SessionComponent($name, $options)
	{
		$this->__construct($name, $options);
	}

	
	protected function _init()
	{
		parent::_init();
		$this->_modname = 'session';
	}

	protected function init(&$options=array())
	{
		parent::init($options);
		$this->enableMenuItem('add', false);
	}
}