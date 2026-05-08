<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


class CContentModelEx extends CContentModel
{
	public function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	public function CContentModelEx($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			case 'flags':	
				//$f['selector'] = 'content_flags';
				break;
			default:
				break;	
			
		}
	}
	
	
	protected function triggerContent($id, $edit=false)
	{
		return true;
	}
	
	protected function getModelCatalogInfo($params)
	{
		$cid = $params['cid'];
		if (!$cid) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no cid!");
			return false;
		}
		
		$modname = $this->_fields['cid']['model'];
		$m = Factory::GetModel($modname);
		$cataloginfo = $m->get($cid);
		
		return $cataloginfo;
	}
	
	protected function prepareCatalogForContent(&$params)
	{
		$cataloginfo = $this->getModelCatalogInfo($params);
		if (!$cataloginfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no cataloginfo!");
			return false;
		}
		
		$uuid = $cataloginfo['uuid'];
		$m = Factory::GetModel('catalog');
		$cataloginfo2 = $m->getOne(array('uuid'=>$uuid));
		if ($cataloginfo2) {
			$params['cid'] = $cataloginfo2['id'];
		}
		
		return true;
	}
	
	
	/*protected function syncSetContent($params, $options=array())
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN ...");
		
		//CID, 
		$this->prepareCatalogForContent($params);
			
		//uuid
		$info = $this->get($params['id']);
		
		$m = Factory::GetModel('content');
		$uuid = $info['uuid'];
		if (!$uuid) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no uuid '$uuid'!");
			return false;
		}
		
		$mid = $params['id'];		
		$contentinfo = $m->getOne(array('uuid'=>$uuid));
		if ($contentinfo) {
			$params['id'] = $contentinfo['id'];
		} else {
			unset($params['id']);			
		}
		
		
		$res = $m->set($params, $options);
		if ($res) {
			$modname = $this->_name;		
			$content_id = $params['id'];		
			
			$m2 = Factory::GetModel('content2model');
			$m2cinfo = $m2->getOne(array('cid'=>$content_id));
			if (!$m2cinfo) { //关联
				$_params = array();
				$_params['modname'] = $modname;
				$_params['mid'] = $mid;
				$_params['cid'] = $content_id;			
				$res = $m2->set($_params);				
				if (!$res) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set content2model failed!", $_params);
					return false;
				}
			}
		}
		
		return $res;
	}
	
	
	
	
	public function set(&$params, &$options=array())
	{
		$res = parent::set($params, $options);
		if ($res) {
			$this->syncSetContent($params, $options);
		}
		return $res;
	}
	*/
	
	protected function syncDelContent($params, $options=array())
	{
		//uuid
		$m = Factory::GetModel('content');
		$uuid = $params['uuid'];
		if (!$uuid) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no uuid!");
			return false;
		}
		
		$contentinfo = $m->getOne(array('uuid'=>$uuid));
		if (!$contentinfo) {
			return false;
		}
		
		
		$res = $m->del($contentinfo['id'], $options);
		
		if ($res) {
			/*$m2 = Factory::GetModel('content2model');
			$m2cinfo = $m2->getOne(array('cid'=>$contentinfo['id']));
			if ($m2cinfo) { //关联
				$res2 = $m2->del($m2cinfo['id']);				
				if (!$res2) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "del content2model failed!", $m2cinfo);
					return false;
				}
			}*/
		}
		
		return $res;
	}
	
	
	public function del($id, &$options=array())
	{
		$res = parent::del($id, $options);
		if ($res) {
			$this->syncDelContent($res, $options);
		}
		return $res;
	}
	
	
	public function getPubtoForView($id, $options=array())
	{
		$params = parent::getPubtoForView($id, $options);
		
		
		$info = $this->get($id);
		if (!$info) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no id '$id'!");
			return false;
		}
		
		$_params = array();
		$m = Factory::GetModel('content');
		$contentinfo = $m->getOne(array('uuid'=>$info['uuid']));
		if ($contentinfo) {
			$_params['flags'] = $contentinfo['flags'];
			$_params['cid'] = $contentinfo['cid'];
		}
		
		$fields = $m->getFieldsForInputEdit($_params, $options);
		$params['_flags'] = $fields['flags']['input'] ;
		
		//_cid
		$params['_cid'] = $fields['cid']['input'] ;
		
		
		return $params;
	}
	
	protected function syncSetContent(&$params, &$options=array())
	{
		$id = $params['id'];
		$info = $this->get($id);
		if (!$info) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no id '$id'!");
			return false;
		}
		
		$_params = $info;
		$m = Factory::GetModel('content');
		$contentinfo = $m->getOne(array('uuid'=>$info['uuid']));
		if ($contentinfo) {
			$_params['id'] = $contentinfo['id'];
		} else {
			unset($_params['id']);	
		}
		
		
		//同步
		//CID, 
		$this->prepareCatalogForContent($_params);
		
		if (isset($params['cid']) && $params['cid'] > 0) {
			$_params['cid'] = $params['cid'];
		}
		
		$res = $m->set($_params, $options);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set set failed!", $_params);
			return false;
		}
		$content_id = $_params['id'];
		
		$m2 = Factory::GetModel('content2model');
		$m2cinfo = $m2->getOne(array('cid'=>$content_id));
		if (!$m2cinfo) { //关联
			$_params2 = array();
			$_params2['modname'] = $this->_modname;
			$_params2['mid'] = $id;
			$_params2['cid'] = $content_id;			
			
			$res2 = $m2->set($_params2);				
			if (!$res2) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set content2model failed!", $_params2);
				return false;
			}
		}		
		$params['id'] = $content_id;
		
		return true;
		
	}
	
	protected function syncCluster($fn, $params, $options=array())
	{
		return true;
	}
	
	public function pubto($params, &$options=array())
	{
		$res = $this->syncSetContent($params, $options);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call  pubtoContent failed '$id'!");
			return false;
		}
		
		
		$m = Factory::GetModel('content');
		$contentinfo = $m->get($params['id']);
		if (!$contentinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no contentinfo!");
			return false;
		}
		
		$res = $m->pubto($params, $options);
		if ($res) {
			$newcontentinfo = $m->get($params['id']);
			$uuid = $newcontentinfo['uuid'];
			$info = $this->getOne(array('uuid'=>$uuid));
			if ($info) {
				//状态同步
				$_params = array();
				$_params['id'] = $info['id'];
				$_params['status'] = $newcontentinfo['status'];
				$this->update($_params);
			}
		}
		
		return $res;
	}
	
	
	protected function formatContentUrl(&$row, &$options = array())
	{
		$m2 = Factory::GetModel('content2model');
		$m2cinfo = $m2->getOne(array('modname'=>$this->_modname, 'mid'=>$row['id']));
		if ($m2cinfo) {
			$row['url'] = $options['_webroot'].'/content/'.$m2cinfo['cid'];
		} else {
			$row['url'] = $options['_base'].'/detail/'.$row['id'];
		}
	}
	
}