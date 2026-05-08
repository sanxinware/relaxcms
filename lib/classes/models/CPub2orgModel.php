<?php

defined('RPATH_BASE') or die();

class CPub2orgModel extends CModel
{
	public function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	public function CPub2orgModel($name, $options=null)
	{
		$this->__construct($name, $options);
	}

	protected function _init_field(&$f)
	{
		parent::_init_field($f);
		
		switch ($f['name']) {
			case 'id':
				$f['searchable'] = true;	
				break;				
			case 'oid':
				$f['input_type'] = 'model';
				$f['model'] = 'org';
				$f['edit'] = false;	
				break;								
			default:
				break;
		}
		return true;
	}
}