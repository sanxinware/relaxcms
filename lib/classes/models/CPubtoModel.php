<?php

/**
 * @file
 *
 * @brief 
 *
 * 发布到模型
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CPubtoModel  extends CModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function CPubtoModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	
	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			case 'etype':
			case 'status':
				$f['input_type']='selector';
				break;
			case 'etime':
				$f['input_type']='DATETIME';
				break;
			case 'ts':
				$f['input_type']='TIMESTAMP';
				break;
			default:
				break;
		}
		
		return true;
	}
	
	
	public function formatForView(&$row, &$options = array())
	{
		parent::formatForView($row, $options);
		
		//format name
		$status = $row['status'];
		$statusColor = $status;
		
		switch($status) {
			case 1:
				$statusColor = 0;
			case 3:
				$statusColor = 1;
				break;
			default:
				break;
		}
		
		//status
		$row['_status'] = $this->formatLabelColorForView($statusColor, $row['_status']);
		
	}
	
	public function setPubtoInfo(&$params, $options=array())
	{
		if (empty($params['tuuid'])) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no TUUID!", $params);
			return false;
		}
		
		$pubtoinfo = $this->getOne(array('tuuid'=>$params['tuuid']));
		if ($pubtoinfo) {
			$params['id'] = $pubtoinfo['id'];
		}
		
		$res = $this->set($params, $options);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "WARNING: call set failed!");
			return false;
		}
		
		return $res;
	}
		
	
	public function pubtoForModel(&$pubtoinfo, $cinfo, $params, $options=array())
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "TODO...", $pubtoinfo);
		return true;
	}
	
	
	public function postPubtoForModel(&$pubtoinfo, $cinfo, $params, $options=array())
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "TODO...");
		return true;
	}
	
	
	public function undoPubtoForModel(&$pubtoinfo, $cinfo, $params, $options=array())
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "TODO...", $pubtoinfo);
		return true;
	}
	
	
}