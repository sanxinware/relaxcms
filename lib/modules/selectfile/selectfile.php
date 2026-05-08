<?php
/**
 * @file
 *
 * @brief 
 * 选文件
 *
 */
class SelectfileModule extends CListviewModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function SelectfileModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	public function show(&$options = array())
	{
		parent::show($options);
		
		//最大上传限制
		$uploadmaxsize = get_upload_max_filesize();
		$uploadmaxsize -= 4*1024*1024; //减小4M
		$this->assign('uploadmaxsize', $uploadmaxsize);
		
		
		$cuid = isset($this->_attribs['cuid'])?intval($this->_attribs['cuid']):0;	
		$type = isset($this->_attribs['type'])?intval($this->_attribs['type']):'';
		$singleselect = isset($this->_attribs['singleselect'])?intval($this->_attribs['singleselect']):0;
		$nooptmenu = isset($this->_attribs['nooptmenu'])?intval($this->_attribs['nooptmenu']):1;
		
		$modname = isset($this->_attribs['modname'])?$this->_attribs['modname']:'file';
		
		if ($type < 0)
			$type = '';
		$this->assign('nosidebar',"nosidebar");	
		$this->assign('type',$type);
		$this->assign('singleselect',$singleselect);
		$this->assign('nooptmenu',$nooptmenu);
		$this->assign('modname',$modname);
	}
}
