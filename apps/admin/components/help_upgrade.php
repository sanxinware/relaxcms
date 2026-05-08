<?php

/**
 * @file
 *
 * @brief 
 *   升级
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class HelpUpgradeComponent extends CUpgradeComponent
{
	protected $_packagefile;
	public function __construct($name, $options)
	{
		parent::__construct($name, $options);
		$this->_packagefile = RPATH_CACHE.DS.'update.lz';
	}
	
	public function HelpUpgradeComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function show(&$options = array())
	{
		parent::show($options);
		$this->initActiveTab(2);
		
		$this->assign('sys_product_version', get_product_version());
		
		
		$cf = get_config();
		$sys_updateapi = ($cf['updatetype'] == 1)?$cf['updateapi']:'已禁用';
		$this->assign('sys_updateapi', $sys_updateapi); 
	}
	
	
	
	protected function checkAppVersion($appname, $appver)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$appver='.$appver);
		
		$dir = RPATH_DOWNLOAD;
		$fdb = s_readdir(RPATH_DOWNLOAD, "files");
		if (!$fdb) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no download files!");
			return false;
		}
		
		$newdb = array();
			
		foreach ($fdb as $key=>$v) {
			//eg: icloudclient-5.0.1.exe
			list($name, $ver) = explode('-', $v, 3);
			if ($name != $appname) {
				continue;
			}
			
			//version
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $name, $ver);	
			
			$_vdb = array();
			$vdb = explode('.', $ver);
			foreach ($vdb as $k2=>$v2) {
				if (is_numeric($v2)) {
					$_vdb[] = $v2;
				} else {
					break;
				}
			}
			
			if (!$_vdb)
				continue;
			
			$nowver = implode('.', $_vdb);
			if (($dver = compareAppVersion($nowver, $appver)) > 0) {
				$verinfo = parseVersionId($nowver);
				$newdb[$nowver] = array('verid'=>$verinfo['version_id'], 'name'=>$v, 'dver'=>$dver, 'file'=>$dir.DS.$v);
			}				
		}
		if (!$newdb)
			return false;
			
		//version
		array_sort_by_field($newdb,'verid');
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $newdb);	
		
		
		$lastver = array_pop($newdb);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $lastver);
		
		return $lastver;
		
	}
	
	
	protected function checkICloudClientVersion(&$options=array())
	{
		$name = 'icloudclient';
		$appver = trim($_REQUEST['v']);
		$newverinfo = $this->checkAppVersion($name, $appver);
		if (!$newverinfo) {
			showStatus($res);
			return false;
		} 
		
		$setupfile = $newverinfo['file'];
		header("Content-Disposition:attachment;filename=$name");
		readfile($setupfile);
		exit;
	}
	
	
	
	/**
	 * checkRemoteVersion 检查远程是否有可升级版本
	 *
	 * @param mixed $options This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function checkRemoteVersion(&$options=array())
	{
		$update = $this->requestInt('update');
		
		$m = Factory::GetModel('remote_app');
		$res = $m->checkRemoteVersionUpdate($update, $options);
		
		$data = isset($options['data'])?$options['data']:array();
		showStatus($res?0:-1, $data);
		
		
	}
}