<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CMenuModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
		$this->_attribs['task'] = 'show';
	}
	
	function CMenuModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}

	protected function show(&$options = array())
	{
		$app = Factory::GetApp();
		$activeComponent = $options['component'];
		$menus = $app->getCurrentMenuTree($activeComponent, $options);
		$naviId = $activeComponent;
		if ($naviId == 'main') {
			$naviId = '';
		}
		
		
		$_menus = array();
		foreach ($menus as $key=>$v) {			
			if ($v['children']) {
				$_menus[$key] = $v;
			}
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$activeComponent='.$activeComponent, $_menus);

		$this->assign("menus", $_menus);
		$this->assign('naviId', $naviId);
		
				
		return $_menus;
	}	
}