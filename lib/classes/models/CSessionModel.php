<?php

/**
 * @file
 *
 * @brief 
 * 
 * 会话表管理
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CSessionModel extends CTableModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function SessionModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}

	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			case 'login_type':
				$f['input_type'] = "selector";	
				break;			
			case 'uid':
				$f['input_type'] = "UID";	
				break;			
			case 'ts':
			case 'login_ts':
				$f['input_type'] = 'TIMESTAMP';
				break;
			default:
				break;
		}
		return true;
	}

	protected function _init()
	{
		parent::_init();

		$this->_default_actions['edit']['enable'] = false;
	}
		
	protected function writeLog($level, $action, $status, $oldParams=array(), $newParams=array(), $mid=0)
	{
		return false;
	}	
	
	
	public function timer()
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN..");
		
		$cf = get_config();
		$session_timeout = $cf['session_timeout'];
		if (!$session_timeout) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: session timeout checking closed!");
			return false;
		}
		
		$_params = array();
		$_params['__orderby'] = array('ts'=>'asc');
		$_params['limit'] = 100;
		
		$now = time();
		$udb = $this->gets($_params);		
		foreach ($udb as $key=>$v) {
			$cktime = $v['cktime'];
			if ($cktime <= 0) {
				$cktime = $cf['session_timeout'] > 0?$cf['session_timeout']:600;
			}			
			$delta = $now - $v['ts'];
			
			if ($delta > $cktime) {
				$this->del($v['id']);
			}
		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT..");
	}
}