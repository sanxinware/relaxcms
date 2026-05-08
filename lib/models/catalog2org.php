<?php

defined('RPATH_BASE') or die();
class Catalog2orgModel extends CModel
{
	public function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	public function Catalog2orgModel($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		switch ($f['name']) {
			case 'cid':
				$f['model'] = 'catalog';
				break;
			case 'oid':
				$f['model'] = 'org';
				break;
			default:
				break;
		}
		return true;
	}
}