<?php

/**
 * @file
 *
 * @brief 
 *  消息
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CMsgComponent extends CPubComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CMsgComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function _init()
	{
		$this->_default_vmask = 4;
	}
	
	protected function initParamsForAdd(&$params, &$options=array())
	{
		parent::initParamsForAdd($params, $options);
		$params['mnum'] = 1;
		$params['_start_time'] = "00:00:00";
		$params['_end_time'] = "23:59:59";
	}
}
