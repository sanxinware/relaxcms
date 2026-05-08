<?php

defined( 'RMAGIC' ) or die( 'Restricted access' );

class CMainComponent extends CDTComponent
{
	function __construct($name, $option)
	{
		parent::__construct($name, $option);
	}
	
	function CMainComponent($name, $option)
	{
		$this->__construct($name, $option);
	}	


	protected function show(&$options=array())
	{
		parent::show($options);
		//当前用户
		$myinfo = get_userinfo();
		if ($myinfo) {			
			$avator =$myinfo['avatar'];
			$myinfo['avatar'] = $avator?( is_url($avator)?$avator:$options['_dataroot']."/avatar/$avator"):$options['_dstroot']."/img/avatar.png";			
		}
		
		$this->assign('myinfo', $myinfo);

		return true;
	}
}
