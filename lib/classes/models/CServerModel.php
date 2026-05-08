<?php

defined('RPATH_BASE') or die();
class CServerModel extends CModel
{
	public function __construct($name, $options=null)
	{
		$options['modname'] = 'server';
		parent::__construct($name, $options);
	}
	
	public function CServerModel($name, $options=null)
	{
		$this->__construct($name, $options);
	}

	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			case 'ts':
				$f['input_type'] = 'TIMESTAMP';
				$f['edit'] = false;
				break;
			case 'mode':
			case 'online':
			case 'status':
				$f['input_type'] = 'selector';
				break;
			case 'oid':
				$f['treemodel'] = 'org';	
				$f['searchable'] = 2;
				break;	
			//case 'ip':
			case 'version':
			case 'spid':
				$f['edit'] = false;			
				break;
			case 'os':
				$f['edit'] = false;
			case 'live_prefix':
			case 'vod_prefix':
			case 'sync_prefix':
			case 'rtmp_prefix':
			case 'lan_live_prefix':
			case 'lan_vod_prefix':
			case 'lan_sync_prefix':
			case 'lan_rtmp_prefix':
			case 'download_prefix':
			case 'lan_download_prefix':
				$f['show'] = false;				
				break;
			default:
				break;
		}
		return true;
	}
		
	public function formatForView(&$row, &$options = array())
	{
		parent::formatForView($row, $options);
	
		//status
		$row['_mode'] = $this->formatLabelColorForView($row['mode'], $row['_mode']);
		$row['_status'] = $this->formatLabelColorForView($row['status'], $row['_status']);
		$row['_online'] = $this->formatLabelColorForView($row['online'], $row['_online']);		
	}
	
	public function get($id)
	{
		$res = parent::get($id) ;
		if ($res) {//for old 
			$res['liverooturl'] = $res['live_prefix'];
			$res['lanliverooturl'] = $res['lan_live_prefix'];
			$res['vodrooturl'] = $res['vod_prefix'];
			
			$res['hlsrooturl'] = $res['liverooturl'];
			$res['lanhlsrooturl'] = $res['lanliverooturl'];
			
		}
		return $res;
	}
	
	
	//spid
	protected function newID(&$params=array())
	{
		$id = parent::newID($params);
		if (!isset($params['spid']))
			$params['spid'] = $this->newUUID($id);
		
		return $id;
	}
	
	
	public function updateStatus($id, $status, $version)
	{
		$ts = time();
		$_params = array();
		$_params['id'] = $id;
		$_params['status'] = $status;
		$_params['version'] = $version;
		$_params['ts'] = $ts;
		
		$res = $this->update($_params);
		
		return $res;
	}
}