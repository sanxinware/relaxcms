<?php
/**
 * @file
 *
 * @brief 
 * 选内容
 *
 */
class SelectcontentModule extends CListviewModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function SelectcontentModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	public function show(&$options = array())
	{
		parent::show($options);
		
		$cuid = isset($this->_attribs['cuid'])?intval($this->_attribs['cuid']):0;	
		$type = isset($this->_attribs['type'])?intval($this->_attribs['type']):'';
		$singleselect = isset($this->_attribs['singleselect'])?intval($this->_attribs['singleselect']):0;
		$nooptmenu = isset($this->_attribs['nooptmenu'])?intval($this->_attribs['nooptmenu']):1;
		
		$modname = $this->getModelName();
		
		if ($type < 0)
			$type = '';
		$this->assign('table_id', $modname);
		$this->assign('nosidebar',"nosidebar");	
		$this->assign('type',$type);
		$this->assign('singleselect',$singleselect);
		$this->assign('nooptmenu',$nooptmenu);
		//$this->assign('modname',$modname);
	}
}
