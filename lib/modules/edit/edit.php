<?php
/**
 * @file
 *
 * @brief 
 *
 * Edit 模块
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class EditModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function EditModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	protected function show(&$options=array())
	{
		$modname = isset($this->_attribs['modname'])?$this->_attribs['modname']:'';
		$id = isset($this->_attribs['id'])?intval($this->_attribs['id']):0;
		$col = isset($this->_attribs['col'])?intval($this->_attribs['col']):2;

		$m = Factory::GetModel($modname);

		$params = $m->get($id);
		$fields = $m->getFieldsForInputEdit($params, $options);
		if (!$fields) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "WARNING: getFieldsForInput$tname failed!", $tname, $modinfo);
			$fields = array();
		}
		
		$this->assign('fields', $fields);		
		$this->assign('params', $params);
				
		//columns
		$column_width = 12/$col; 
		$fdb = array();
		
		$i = 0; 
		foreach($fields as $key=>$v) { 
			if (!$v['edit']) 
				continue;  			
			$fdb[$i++] = $v; 
		}
		
		$this->assign('column_width', $column_width);
		$this->assign('fdb', $fdb);
		$this->assign('nr_field', $i);
		$this->assign('columns', $col);

		
		return true;
	}	
}