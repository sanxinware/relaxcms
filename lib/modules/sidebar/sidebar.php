<?php
/**
 * @file
 *
 * @brief 
 * 菜单模块
 *
 */
class SidebarModule extends CMenuModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function SidebarModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}	
	
	protected function show(&$options = array())
	{
		
		$menus = isset($options['menus']) ? $options['menus']: parent::show($options);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $menus);					
		
		//默认展开菜单
		if (isset($options['use_default_component']) && $options['use_default_component']) {
			//找出默认展开菜单
				foreach ($menus as $key => &$v) {
				if (isset($v['open']) && $v['open']) {
					$v['active'] = true;
				} else if (isset($v['active']) && $v['active']) {
						//if (!isset($v['children'][$options['component']])) //当前主菜单不含活动项
							$v['active'] = false;
					}
				}
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $options['use_default_component']);
			
			$this->assign('menus', $menus);
		}		
	}
	
	
}