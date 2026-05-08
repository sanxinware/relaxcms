<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CRemoteAppModel extends CClientAppModel
{

	public function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	public function CRemoteAppModel($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	
	public function select($params=array(), &$options=array())
	{
		$params['remote'] = 1;

		$udb = parent::select($params, $options);

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);

		/*$udb = $this->loadAppFromRemote($params, $options);

		foreach($udb as $key=>&$v) {
			$v['remote_version'] = $v['version'];

			//检查一下是否
			$ainfo = $this->getOne(array('name'=>$v['name']));
			if ($ainfo) {
				$v['installed'] = $ainfo['installed'];
			}
		}*/

		return $udb;
	}

	protected function fixedAppInfoForLocal(&$appinfo)
	{
		//installed
		$m = Factory::GetModel('app');
		$info = $m->getOne(array('name'=>$appinfo['name'])) ;

		if ($info) {
			$appinfo['installed'] = $info['installed'];
			$appinfo['local'] = $info['local'];
		}
	}

	public function get2($id)
	{
		$appinfo = array();

		$cf = get_config();			
		$updatetype = intval($cf['updatetype']);
		if ($updatetype == 1) {		
			$apiurl = $cf['updateapi'].'/getAppDetail?id='.$id;				
			
			$_params = array('id'=>$id);	
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $_params);

			$data = requestSAPI($apiurl, array('params'=>$_params));	
			
			if ($data) {
				$res2 = CJson::decode($data);
				if ($res2) {
					$appinfo = $res2['data'];	
					
					//fixed for local
					$this->fixedAppInfoForLocal($appinfo);
				} else {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "invalid data '$data'!apiurl=$apiurl", $_params);
				}
			} 
		}
		
		return $appinfo;
	}

		
	public function formatForView2(&$row, &$options = array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...");

		$res = parent::formatForView($row, $options);


		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...",$row);
		return $res;
	}
}