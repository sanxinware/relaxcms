<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CCatalogModelEx extends CCatalogModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function CCatalogModelEx($name, $options=array())
	{
		$this->__construct($name, $options);
	}
		
	public function syncSetCatalog($params, $options)
	{
		$info = $this->get($params['id']);
		if (!$info) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no id!");
			return false;		
		}
			
		$uuid = $info['uuid'];			
		$m = Factory::GetModel('catalog');
		$cataloginfo = $m->getOne(array('uuid'=>$uuid));
		if ($cataloginfo) {
			$params['id'] = $cataloginfo['id'];
		} else {
			unset($params['id']);	
		}
		
		//pid
		if ($params['pid'] > 0) {
			$pinfo = $this->get($params['pid']);			
			if ($pinfo) {
				$pcataloginfo = $m->getOne(array('uuid'=>$pinfo['uuid']));	
				if ($pcataloginfo) {
					$params['pid'] = $pcataloginfo['id'];
				}
			}
		}
		
		$res = $m->set($params);
		
		return $res;
	}
	
	public function set(&$params, &$options=array())
	{
		$res = parent::set($params, $options);
		if ($res) {
			$this->syncSetCatalog($params, $options);
		}
		return $res;
	}
	
	
	
	protected function syncDelCatalog($params, $options=array())
	{
		//uuid
		$m = Factory::GetModel('catalog');
		$uuid = $params['uuid'];
		if (!$uuid)
			return false;
		
		$cataloginfo = $m->getOne(array('uuid'=>$uuid));
		if (!$cataloginfo) {
			return false;
		}
		
		
		$res = $m->del($cataloginfo['id'], $options);
		
		return $res;
	}
	
	public function del($id, &$options=array())
	{
		$res = parent::del($id, $options);
		if ($res) {
			$this->syncDelCatalog($res, $options);
		}
		return $res;
	}
}
