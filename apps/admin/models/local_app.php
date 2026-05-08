<?php

/**
 * @file
 *
 * @brief 
 * 
 * 本地App管理类
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class Local_appModel extends CClientAppModel
{
	public function __construct($name, $options=array())
	{
		$options['modname'] = 'app';
		parent::__construct($name, $options);
		
		$this->_default_sort_field_name = 'name';
		$this->_default_sort_field_mode = 'asc';
	}
	
	public function Local_appModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			case 'appname':
			case 'appversion':
				$f['show'] = false;	
				break;
			case 'vtype':
				$f['searchable'] = 2;							
				$f['show'] = true;
				break;	
			case 'type':
				$f['searchable'] = 0;	
				$f['show'] = false;	
				break;				
				
				
				break;	
		}
	}

	public function select($params=array(), &$options=array())
	{
		$params['remote'] = 0;
		$udb = parent::select($params, $options);
		return $udb;
	}

	protected function getActions($row=array(), &$options=array())
	{
		$actions = parent::getActions($row, $options);
		
		//在本未安装
		if ($row['installed'] != 1 && $row['local'] == 1 ) {
			$action = array(
					'name'=>'install',
					'icon'=>'fa fa-wrench',
					'title'=>'安装',
					'class'=>'btn-primary',
					'action'=>'button',
					'msg'=>'确定安装吗？',
					'enable'=>true,
					);
			$actions[$action['name']] = $action;
		} 
		
		if ($row['installed'] == 1 || empty($row['peeravid'])) { 
			unset($actions['del']);			
		}  
	
		return $actions;
	}
	
}