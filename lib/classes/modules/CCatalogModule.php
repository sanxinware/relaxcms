<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CCatalogModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function CCatalogModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	protected function getList($pid=0, $tree=0, $flags=0, $options)
	{
		$m =  Factory::GetModel('catalog');
		$udb = $m->menu($pid, $tree, $flags, $options);
		array_sort_by_field($menu, 'taxis');
		
		return $udb;
	}

	protected function show(&$options = array())
	{
		$flags = isset($this->_attribs['flags'])?intval($this->_attribs['flags']):3;
		$cid = isset($this->_attribs['cid'])?$this->_attribs['cid']:0;
		$pid = isset($this->_attribs['pid'])?$this->_attribs['pid']:0;
		$tree = isset($this->_attribs['tree'])?true:0;
		$class = isset($this->_attribs['class'])?$this->_attribs['class']:'';
		

		$homepage = array(
			'active'=>'open active',
			'class'=>'dropdown-toggle',
			'icon'=>'fa-home',
		);

		
		$menu = $this->getList($pid, $tree, $flags, $options);
		
		
		$udb = array();
		foreach($menu as $key=>$v) {
			if ($v['id'] == $cid) {
				$v['active'] = 'active open';
				$homepage['active'] = '';
			} else {
				$v['active'] = '';
			}
			//tartget
			if (is_start_with($v['target'], '#') && $cid == 0) {
				$v['url'] = $v['target'];
			}
			$udb[] = $v;
		}
		
		$this->assign('homepage', $homepage);	
		$this->set_var('udb', $udb);
		$this->assign('class', $class);	
	
		return $udb;
	}	
}