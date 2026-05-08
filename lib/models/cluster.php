<?php

/**
 * @file
 *
 * @brief 
 * 
 * 集群模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class ClusterModel extends CModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function ClusterModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}

	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		switch ($f['name']) {
			case 'flags':
				$f['input_type'] = 'multicheckbox';
				$f['show'] = false;
				
				break;
			case 'is_local':
			case 'online':
				$f['edit'] = false;
			case 'is_default':
			case 'is_master':
				$f['input_type'] = 'yesno';
				break;
			case 'ts':
				$f['input_type'] = 'TIMESTAMP';
			case 'ssid_expired':
			case 'ssid':
				$f['show'] = false;					
				$f['edit'] = false;					
				break;
			case 'status':
				$f['input_type'] = 'selector';
				break;
			default:
				break;
		}

		return true;
	}
	
	public function formatForView(&$row, &$options=array())
	{
		$res =  parent::formatForView($row, $options);

		$row['_status'] = $this->formatLabelColorForView($row['status'], $row['_status']);
		$row['_online'] = $this->formatLabelColorForView($row['online'], $row['_online']);
		
	}
	
	//
	protected function checkOnline(&$params)
	{
		$apiurl = $params['apiurl'];		
		$apitoken = $params['apitoken'];		
		$apisecret = $params['apisecret'];	
		
		
		$tokenUrl = $apiurl.'/getToken';
		
		$_params = array();
		$_params['token'] = $apitoken;
		$_params['ts'] = time();
		$_params['sign'] = sign($apisecret, $_params);
		$res = curlRequestForGetHeader($tokenUrl, $_params);
		
		$oparams = json_decode($res, true);
		$ssid = $oparams['data'];
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "ssid=$ssid");
		
		$url = $apiurl.'/hello';
		$_params = array();
		$_params['ssid'] = $ssid;
	
		$res = curlPOST($url, $_params);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $res);
		
		if ($res && ($res2 = json_decode($res, true)) && $res2['status'] >= 0 ) {
			$params['online'] = 1;
			$params['ssid'] = $ssid;
		}
				
		return $res;		
	}
	
	protected function checkParams(&$params, &$options=array())
	{
		$res = parent::checkParams($params, $options);
		if ($res) {
			$this->checkOnline($params);	
			$cf = get_config();
			if ($params['apitoken'] == $cf['apiAccessKey'])
				$params['is_local'] = 1;		
		}
		
		return $res;
	}
	
	
	public function postCluster($params)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		if (!isset($params['fn'])) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, 'no fn!', $params);
			return false;
		}
		
		if (!isset($params['modname'])) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, 'no modname!', $params);
			return false;
		}
		
		if (!isset($params['params'])) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, 'no params!', $params);
			return false;
		}
		
		$_params = unserialize(base64_decode($params['params']));
		if (!$_params) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, 'invalid $_params!', $_params);
			return false;
		}
		
		//add __SYNCCLUSTERPOSTTAG
		$modname = $params['modname'];
		$fn = $params['fn'];
		$m = Factory::GetModel($modname);
		$res = $m->recvPostCluster($fn, $_params);
		
		return $res;
	}
	
}
