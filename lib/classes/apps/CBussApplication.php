<?php

/**
 * 商用应用基类
 *
 */
class CBussApplication extends CMainApplication
{
		
	public function __construct($name, $options = array())
	{
		parent::__construct($name, $options);
	}
	
	public function CBussApplication($name, $options = array())
	{
		$this->__construct($name, $options);
	}	
	
	protected function checkLicense(&$aname, &$options=array())
	{
		$m = Factory::GetModel('license');
		$res = $m->checkLicenseExpired($this->_name, $msg);
		if (!$res) {
			$msg = "$msg<a href='$options[_basename]/help_license'>查看</a>";
			set_error($msg);
			
			//重定向到
			$aname='admin';
			$options['cname'] = 'help_license';
			return false;
		}	
		
		if ($msg) {
			$msg = "$msg <a href='$options[_basename]/help_license'>查看</a>";
			set_error($msg);
		}
				
		return $res;
	}
}