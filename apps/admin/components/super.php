<?php

/**
 * @file
 *
 * @brief 
 *
 * Copyright (c), 2014, relaxcms.com
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class SuperComponent extends CUIComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function SuperComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	public function show(&$options=array())
	{	
		//$this->enableJSCSS(array( 'crypto', 'encrypt'), true);
		
		$pkey = md5(time());
		$this->assignSession('__aeskey', $pkey);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$__aeskey='.$pkey);
		
		$this->assign('pkey', $pkey);		
		
		$manager = get_manager();
			
		$userinfo = get_userinfo();
		$this->assign('newsupername', $userinfo['name']);		
		$this->assign('params', $manager);		
	}
	
	
	protected function doChangeSuper(&$options=array())
	{
		$userinfo = get_userinfo();
		$this->getParams($params);
		
		$password = $params['password'];
		$newpassword = $params['newpassword'];
		$newpassword2 = $params['newpassword2'];
		
		if (!$password) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no password!");
			return false;
		}
		
		if (!$newpassword ) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no newpassword!");
			return false;
		}
		
		if ($newpassword != $newpassword2) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "newpassword error!");
			return false;
		}
		
		//���³�ʼ����Ա	
		$m = Factory::GetAdmin();		
		$res = $m->changeSuper($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "change super failed!");
			return false;
		}
		
		return $res;
	}
	
	
	//�༭
	protected function changesuper(&$options=array())
	{
		$res = $this->doChangeSuper($options);
		showStatus($res?0:-1) ;
	}
}
