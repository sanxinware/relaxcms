<?php
/**
 * @file
 *
 * @brief 
 * 个人资料模块
 *
 */
class ProfileModule extends CLoginModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
		$this->_attribs['task'] = 'show';
	}
	
	function ProfileModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
		
	protected function show(&$options=array())
	{
		parent::show($options);
		
		//当前用户
		$userinfo = get_userinfo();
		if ($userinfo) {			
			$avator =$userinfo['avatar'];
			//$userinfo['avatar'] = $avator?( is_url($avator)?$avator:$options['_dataroot']."/avatar/$avator"):$options['_dstroot']."/img/avatar.png";			
		}

		$has_my_info = hasPrivilegeOf('my_info');
		$has_my_password = hasPrivilegeOf('my_password');
		
		$this->assign('userinfo', $userinfo);
		$this->assign('has_my_info', $has_my_info);
		$this->assign('has_my_password', $has_my_password);


	}	
}