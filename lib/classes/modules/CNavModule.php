<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CNavModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function CNavModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	protected function getMenus($activeId, $flags, $options, &$activCatalogInfo)
	{
		$m = Factory::GetModel('catalog');
		$menus = $m->tree(0, $activeId, $flags, $options, $activCatalogInfo);
		return $menus;
	}

	protected function show(&$options = array())
	{
		$flags = isset($this->_attribs['flags'])?intval($this->_attribs['flags']):7;
		$cid = isset($this->_attribs['cid'])?$this->_attribs['cid']:0;
		$tree = isset($this->_attribs['tree'])?true:0;
		$class = isset($this->_attribs['class'])?$this->_attribs['class']:'';

		$activCatalogInfo = array();
		
		$menus =$this->getMenus($cid, $flags, $options, $activCatalogInfo);
		
		
		$m = Factory::GetModel('catalog');
		$position = $m->position($cid, $options);		
		$this->assign('position', $position);
		
		$homeactive = $activCatalogInfo?'':'active';
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "cid=$cid", $homeactive, $activCatalogInfo); 
		
		$this->assign('activCatalogInfo', $activCatalogInfo);	
		$this->assign('homeactive', $homeactive);	
		$this->assign('udb', $menus);
	
		return $menus;
	}	
}