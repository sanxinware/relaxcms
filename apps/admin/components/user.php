<?php
/**
 * @file
 *
 * @brief 
 *  用户管理
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class UserComponent extends CUserComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function UserComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
		
	protected function checkParams(&$params=array())
	{
		$res = parent::checkParams($params);
		$loginmode = get_common_checkbox_value('user_login_mode');		
		$params['loginmode'] = $loginmode;				
		return $res;
	}
	
	
	protected function detail(&$options=array())
	{
		$m = Factory::GetModel('user');
		if ($m->isLocked($this->_id)) {			
			$this->addMenuItem(
					array(
						'name'=>'unlock',
						'icon'=>'fa fa-unlock',
						'title'=>'解锁',
						'class'=>'btn-danger',
						'action'=>'submit',
						'sort'=>12,
						'tmask'=>array('detail'),
						)
					);
		}
				
		$res = parent::detail($options);
		
		return $res;
	}
	
	protected function unlock(&$options=array())
	{
		$m = Factory::GetModel('user');
		$res = $m->unLock($this->_id);
		showStatus($res?0:-1);
	}	
	
	protected function authBasicRequest(&$options=array())
	{
		$m = Factory::GetModel('user');
		$res = $m->authBasicRequest($_REQUEST);
		showStatus($res?0:-1);
	}	
	
}