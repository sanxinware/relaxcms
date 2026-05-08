<?php
/**
 * @file
 *
 * @brief 
 * 最近访问模块
 *
 */
class LastaccessModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
		$this->_attribs['task'] = 'show';
	}
	
	function LastaccessModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	protected function show(&$options=array())
	{
		//访问历史
		$lasturl = '';
		
		$m = Factory::GetModel('history');
		$udb = $m->getHistory();		
		$menus = Factory::GetApp()->getMenus();		
		$hdb = array();
		if ($udb) {
			foreach ($udb as $key=>$v1) {
				if (isset( $menus[$v1['cname']])) {
					$v1['title'] = $menus[$v1['cname']]['title'];			
					$v1['url'] = $options['_basename'].'/'.$v1['cname'];
					$hdb[] = $v1;				
				}
			}
		}
		
		
		$this->assign('hisdb', $hdb);
	}	
}