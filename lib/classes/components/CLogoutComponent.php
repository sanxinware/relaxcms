<?php
/**
 * @file
 *
 * @brief 
 *  登录基类
 *
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CLogoutComponent extends CComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	protected function init(&$options = array())
	{
		$options['task'] = 'logout';			
	}
	
	public function logout(&$options = array())
	{
		Factory::GetApp()->logout();	
		redirect($options['_basename']);
	}
}