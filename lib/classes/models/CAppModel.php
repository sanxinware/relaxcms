<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CAppModel extends CModel  implements IAppModel
{
	
	public function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	public function CAppModel($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	
	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			case 'language':
			case 'install':
			case 'platform':
				$f['input_type'] = 'varmulticheckbox';
				$f['show'] = false;	
				break;
			case 'type':
				$f['searchable'] = 2;							
				$f['input_type'] = "selector";	
				break;				
			case 'id':
			case 'name':
				$f['searchable'] = true;	
				break;
			case 'rkey':
				$f['input_type'] = 'selector';	
				$f['selector'] = 'yesno';	
				break;
			
			case 'remote_version':
			case 'installdir':
			case 'copyright':
			case 'remote_download_url':
			case 'embeded':
			case 'logo':
			case 'language':
			case 'url':
				$f['show'] = false;	
				break;				
			case 'appid':
				$f['show'] = false;	
				$f['edit'] = false;	
				break;				
			case 'cuid':
				$f['input_type'] = 'UID';
				$f['readonly'] = true;
				$f['edit'] = false;				
				break;
			case 'uid':
				$f['input_type'] = 'model';
				$f['model'] = "user";	
				$f['default'] = true;	
				break;		
			case 'ctime':
				$f['readonly'] = true;
				$f['edit'] = false;
				//case 'taxis':
				$f['show'] = false;
				$f['input_type'] = 'TIMESTAMP';
				break;			
			case 'ts':
				$f['input_type'] = 'TIMESTAMP';
				$f['edit'] = false;
				$f['sortable'] = true;
				break;
			case 'status':
				$f['input_type'] = 'selector';
				//$f['edit'] = false;
				$f['sortable'] = true;
				break;
			default:
				break;
		}
		return true;
	}
	
	public function formatForView(&$row, &$options = array())
	{
		parent::formatForView($row, $options);
		if (empty($row['title'])) 
			$row['title'] = $row['name'];
		
	}
	
	protected function newID(&$params=array())
	{
		$id = parent::newID($params);
		if (!isset($params['appid']))
			$params['appid'] = md5($id.$params['name'].'_'.$params['type'].time());		
		return $id;
	}
}