<?php

/**
 * @file
 *
 * @brief 
 *
 * ORG模型
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class COrgModel  extends CModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
		$this->_default_sort_field_mode = "asc";
		
	}
	
	public function COrgModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			case 'status':
				$f['input_type'] = "onoff";	
				break;
			case 'pid':
				$f['input_type'] = "treemodel";	
				break;
			case 'uids':
				$f['modelselector'] = "user";	
				break;			
			default:
				break;
		}
		
		return true;
	}
	
	protected function postSetOrg($params) 
	{
		$oid = $params['id'];
		
		$olddb = array();
		$newdb = array();
		$odb = array();
		
		if (!empty($params['__old'])) {
			$odb = explode(',', $params['__old']['uids']);
		}
		
		if (!empty($params['uids'])) {
			$newdb = explode(',', $params['uids']);
		}
		
		//更新
		$m = Factory::GetModel('user');
		foreach($odb as $uid) {
			if (!in_array($uid, $newdb)) {
				$_params = array();
				$_params['id'] = $uid;
				$_params['oid'] = 0;
				$m->update($_params);
			}
		}
		
		foreach($newdb as $uid) {
			$_params = array();
			$_params['id'] = $uid;
			$_params['oid'] = $oid;
			$m->update($_params);
		}
	}
	
	public function set(&$params, &$options=array())
	{
		$res = parent::set($params, $options);
		if ($res) {
			$this->postSetOrg($params);
		}
		
		return $res;
	}
	
	
	public function getInfoByUID($uid)
	{
		$m = Factory::GetModel('user');
		$res = $m->get($uid);
		$oid = $res['oid'];
		$oinfo = $this->get($oid);
		
		return $oinfo;
	}
	
	public function getMyOrginfo()
	{
		$oid = get_oid();
		return $this->get($oid);
	}	
	
	public function getSiteMenu(&$ioparams=array())
	{
		$orgdb = $this->gets();
		$_webroot = $ioparams['_webroot'];
		foreach ($orgdb as $key => &$v) {
			$v['url'] = $_webroot.'/list/org/'.$v['id'];
		}
		
		return $orgdb;
	}
}