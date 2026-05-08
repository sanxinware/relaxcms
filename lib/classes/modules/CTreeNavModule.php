<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CTreeNavModule extends CNavModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function CTreeNavModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	protected function getMenus($activeId, $flags, $options, &$activCatalogInfo)
	{
		$pid = isset($this->_attribs['cid'])?intval($this->_attribs['cid']): 0;
		$m = Factory::GetModel('catalog');
		$menus = $m->tree($pid, $activeId, $flags, $options, $activCatalogInfo);
		
		return $menus;
	}
}