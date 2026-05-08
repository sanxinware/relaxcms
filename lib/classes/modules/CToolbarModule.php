<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CToolbarModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function CToolbarModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}

	protected function show(&$options = array())
	{
		$flags = isset($this->_attribs['flags'])?intval($this->_attribs['flags']):7;
		$cid = isset($this->_attribs['cid'])?$this->_attribs['cid']:0;
		$tree = isset($this->_attribs['tree'])?true:0;
		$class = isset($this->_attribs['class'])?$this->_attribs['class']:'';
		

		$homepage = array(
			'active'=>'open active',
			'class'=>'dropdown-toggle',
			'icon'=>'fa-home',
		);

		
		$m = Factory::GetModel('catalog');
		$menu = $m->menu(0, $tree, $flags, $options);
		array_sort_by_field($menu, 'taxis');
		
		foreach($menu as $key=>&$v) {
			if ($v['id'] == $cid || (!empty($v['link']) && isset($options['link']) && $options['link'] == $v['link'])) {
				$v['active'] = 'active open';
				$homepage['active'] = '';
			} else {
				$v['active'] = '';
			}
			//tartget
			if (is_start_with($v['target'], '#') && $cid == 0) {
				$v['url'] = $v['target'];
			}
		}
		
		$this->assign('homepage', $homepage);	
		$this->set_var('udb', $menu);
		$this->assign('class', $class);	
	
		return true;
	}	
}