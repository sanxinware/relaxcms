<?php

/**
 * @file
 *
 * @brief 
 *  重置密码
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class MyResetpasswordComponent extends CMyPasswordComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function MyResetpasswordComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	public function show(&$options=array())
	{
		$sign = $this->request('sign');		
		$res = parent::show($options);
		
		$this->assign('sign', $sign);
		return $res;		
	}
	
	protected function resetpassword(&$options=array())
	{
		$sign = $this->request('sign');		
		$m = Factory::GetModel('user');		
		$this->getParams($params);
		$res = $m->resetPasswordBySign($sign, $params);
		
		showStatus($res?0:-1);
	}
	
}
