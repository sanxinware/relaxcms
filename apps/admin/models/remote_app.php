<?php

/**
 * @file
 *
 * @brief 
 * 
 * 远程App管理类
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class Remote_appModel extends CRemoteAppModel
{
	public function __construct($name, $options=array())
	{
		$options['modname'] = 'app';
		parent::__construct($name, $options);
	}
	
	public function Remote_appModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function getActions($row=array(), &$options=array())
	{
		$actions = parent::getActions($row, $options);
		
		unset($actions['del']);
		
		
		
		
		return $actions;
	}
	
	private function updateRemoteVersion($vinfo)
	{
		//url
		$url = $vinfo['url'];
		$data = curlGET($url);
		if (!$data) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call curlGET from '$url' failed!");
			return false;
		}
		$dir = RPATH_CACHE.DS."upgrade";
		if (!is_dir($dir))
			mkdir($dir);
		$pfile = $dir.DS."update.lz";
		
		$res = s_write($pfile, $data);
		
		$up = Factory::GetUpgrade();		
		$res = $up->upgrade($pfile);
		if (!$data) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call upgrade failed!");
			return false;
		}
		
		return $res;
	}
	
	
	public function checkRemoteVersionUpdate($update, &$options=array())
	{
		//version
		$cf = get_config();
		if ($cf['updatetype'] != 1) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "updateapi forbidden!");
			return false;
		}
			
		$verapiurl = $cf['updateapi'].'/getLastVersion';
		$params = get_sysinfo();
		
		//product_id
		$res = requestSAPI($verapiurl, array('params'=>$params));
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $res);
		$data = array();
		if ($res) {
			$res2 = CJson::decode($res);
			$data = $res2['data'];
			if ($data) {
				if ($update) { //升级
					$res = $this->updateRemoteVersion($data);
					$data['status'] = $res?2:-1;
				} 			
			} else {
				$res = false;
			}
		}
		
		$options['data'] = $data;
				
		return $res;
	}

}