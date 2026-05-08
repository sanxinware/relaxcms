<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CListviewModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function CListviewModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	

	public function show(&$options=array())
	{
		$res = parent::show($options);
		
		$modname = $this->getModelName();
		
		//$id = isset($this->_attribs['id'])?$this->_attribs['id']:0;
		
		$vmask = isset($this->_attribs['vmask'])?$this->_attribs['vmask']:0;
		$keyword = isset($this->_attribs['keyword'])?$this->_attribs['keyword']:'';
		$notabhead = isset($this->_attribs['notabhead'])?intval($this->_attribs['notabhead']):0;
		$col = isset($this->_attribs['col'])?$this->_attribs['col']:6;
		$nosidebar = isset($this->_attribs['nosidebar'])?intval($this->_attribs['nosidebar']):false;	
		$defaultviewtype = isset($this->_attribs['defaultviewtype'])?intval($this->_attribs['defaultviewtype']):1;	
		//defaultviewtype
		$hidefields = isset($this->_attribs['hidefields'])?$this->_attribs['hidefields']:'';
		$hidden_filter_fields = array();
		foreach ($this->_attribs as $key=>$v) {
			if (is_start_with($key, '__')) {
				$_key = ltrim($key, '__');
				$hidden_filter_fields[$_key] = $v;		
			}
		}
		
		
		if ($col >12 || $col < 1)		
			$col = 6;
		
		$params['__keyword'] = $keyword;
		
		$m = Factory::GetModel($modname);
		$sfdb = $m->getFieldsForSearch($params, $options);
		$modinfo = $m->getModelInfo();
		$pkey = $modinfo['pkey'];
		$this->assign('pkey', $fields[$pkey]);
		
		//format vmask
		$_vmask = 0;
		if (is_numeric($vmask)) {
			$_vmask = intval($vmask);
		} else {
			$tdb = explode('|', $vmask);
			foreach ($tdb as $key => $v) {
				switch ($v) {
					case 'large':
						$_vmask |= 0x1;
						break;
					case 'listimg':
						$_vmask |= 0x2;
						break;
					case 'detail':
						$_vmask |= 0x4;
						break;					
					default:
						# code...
						break;
				}
			}
		}
		
		$this->assign('vmask', $_vmask);
		$this->assign('defaultviewtype', $defaultviewtype);
		
		$this->assign('sfdb', $sfdb);
		$this->assign('col', $col);
		$this->assign('viewname', 'view'.$cid);
		$this->assign('_keyword', $keyword);
		$this->assign('modname', $modname);
		$this->assign('notabhead', $notabhead);
		$this->assign('nosidebar', $nosidebar?"nosidebar":"");
		
		$this->assign('hidefields', $hidefields);
		$this->assign('hidden_filter_fields', $hidden_filter_fields);		
		
		//$this->assign('tablename', $modname);
		//$mi18n  = get_i18n('mod_'.$modname);
		//$this->assign('mi18n', $mi18n);
		//$mi18n[modelname]
		//!$table_title && $table_title = $mi18n['modelname'];
		//$this->assign('table_title', $table_title);
		
	}	
}