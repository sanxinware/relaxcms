<?php
/**
 * @file
 *
 * @brief 
 * 
 * CClusterModel
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CClusterModel extends CModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function CClusterModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	//检查请求头是否有'tag'
	protected function isClusterPost($options)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN", $params);
		if (isset($options['__SYNCCLUSTERPOSTTAG']))
			return true;
			
		return false;
	}
	
	
	protected function sendPostCluster($cinfo, $fn, $params)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN");
		$_params = array();
		$_params['modname'] = $this->_name;
		$_params['fn'] = $fn;
		$_params['params'] = base64_encode(serialize($params));
		$_params['cookie'] = $cinfo['ssid'];
				
		$url = $cinfo['apiurl'].'/postCluster';
		$res = curlPOST($url, $_params);
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT");
		return $res;		
	}
	
	protected function prepareSyncCluster_set( &$params, $options=array())
	{
		if (!isset($params['uuid'])) {
			$info = $this->get($params['id']);
			if (!$info) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no info!");
				return false;
			}
			if (!isset($info['uuid'])) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no uuid!");
				return false;
			}
			$params['uuid'] = $info['uuid'];
		}
		return true;
	}
	
	protected function prepareSyncCluster_update( &$params, $options=array())
	{
		return $this->prepareSyncCluster_set($params, $options);
	}
	
	public function prepareSyncCluster($fn, &$params, $options=array())
	{
		$aliasFn = 'prepareSyncCluster_'.$fn;
		if (method_exists($this, $aliasFn)) {
			$res = $this->$aliasFn($params, $options);	
			if (!$res) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: call '$aliasFn' failed!");
			}	
		}			
	}
	
	protected function doSyncCluster($fn, $params, $options=array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN");
		
		if ($this->isClusterPost($options)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "is cluster POST skip!");
			return false;
		}
		
		
		$m = Factory::GetModel('cluster');	
		$sdb = $m->gets();
		
		$activeClusterNodedb = array();
		foreach ($sdb as $key=>$v) {
			//skip localhost
			if ($v['is_local'])
				continue;
			if ($v['status'] != 1)
				continue;
			
			$activeClusterNodedb[] = $v;
		}
		
		$res = true;
		$nr_success = 0;
		if ($activeClusterNodedb) {
			//准备params
			$this->prepareSyncCluster($fn, $params, $options);
			foreach ($activeClusterNodedb as $key=>$v) {			
				$res = $this->sendPostCluster($v, $fn, $params);
				if (!$res) { //cluster
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call doPostCluster failed", $fn, $params);
				}
				
				$nr_success ++;
			}
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT $nr_success");
		
		return $res;
	}

	protected function syncCluster($fn, $params, $options=array())
	{
		$res = $this->doSyncCluster($fn, $params, $options);
		return $res;
	}
	
	public function set(&$params, &$options=array())
	{
		$res = parent::set($params, $options);
		
		if ($res) {
			//集群处理
			if (!isset($params['__old']) && !isset($options['__nocluster']))
				$this->syncCluster(__FUNCTION__, $params, $options);
		} 
		
		return $res;
	}
	
	public function del($id, &$options=array())
	{
		$old = parent::del($id, $options);
		
		//集群同步处理
		if ($old && !isset($options['__nocluster'])) {
			$this->syncCluster(__FUNCTION__, $old, $options);
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, ".... after syncCluster ... $id ...");
		}
		
		return $old;
	}
	
	public function update(&$params=array(), &$options=array())
	{
		$res = parent::update($params, $options);
		if ($res && !isset($options['__nocluster'])) {
			$this->syncCluster(__FUNCTION__, $params, $options);
		}
		return $res;
	}
	
	
	////////////////////////////////////////// recv ///////////////////////
	protected function processSyncCluster_set($params, $options=array())
	{	
		//TODO...
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN ...$fn ", $params);
		
		//预处理
		$oldinfo = $this->getOne(array('uuid'=>$params['uuid']));
		if ($oldinfo) {
			$params['id'] = $oldinfo['id'];
		} else {
			unset($params['id']);
		}
		
		//处理
		$res = $this->set($params, $options);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call set failed!");
			return false;
		}
		
		return $res;
	}
	
	protected function processSyncCluster_del($params, &$options=array())
	{
		$oldinfo = $this->getOne(array('uuid'=>$params['uuid']));
		if (!$oldinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no uuid!", $params);
			return false;
		}
		
		$res = $this->del($oldinfo['id'], $options);
		
		return $res;
	}
	
	protected function processSyncCluster($fn, $params, &$options=array())
	{
		$aliasFn = 'processSyncCluster_'.$fn;
		if (method_exists($this, $aliasFn)) {
			$res = $this->$aliasFn($params, $options);	
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call '$aliasFn' failed!");
				return false;
			}	
		} else {
			$res = $this->$fn($params, $options);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call '$fn' failed!");
				return false;
			}						
		}		
		
		return $res;	
	}
	
	public function recvPostCluster($fn, $params, $options=array())
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN");
		$options['__SYNCCLUSTERPOSTTAG'] = 1;
		if (!method_exists($this, $fn)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no method '$fn'!");
			return false;
		}
		
		//解码 _references
		$params['_references'] = isset($params['_references'])?unserialize($params['_references']):array();
		
		//处理
		$res = $this->processSyncCluster($fn, $params, $options);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call recvPostClusterForModel failed!", $fn);
			return false;
		}
		
					
		return $res;
	}
}
