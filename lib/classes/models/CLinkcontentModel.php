<?php

defined('RPATH_BASE') or die();
class CLinkContentModel extends CContentModel
{
	public function __construct($name, $options=null)
	{
		$options['modname'] = 'content';
		parent::__construct($name, $options);
	}
	
	public function CLinkContentModel($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			case 'oid':	
				$f['show'] = true;
				break;	
			case 'flags':				
			case 'taxis':
				$f['show'] = false;
				break;			
			default:
				break;
		}
		return true;
	}
	
	

	public function selectForListview(&$params, &$options=array())
	{
		$params['filterfieldcfg'] = array('id'=>true,'name'=>true);

		$data = parent::selectForListview($params, $options);
		$data['hasOptmenu'] = false;

		return $data;
	}
}