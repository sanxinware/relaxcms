<?php

defined('RPATH_BASE') or die();
class CContent2orgModel extends CContent2modelModel
{
	public function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	public function CContent2orgModel($name, $options=null)
	{
		$this->__construct($name, $options);
	}

	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		switch ($f['name']) {
			case 'cid':
				$f['model'] = 'content';
				break;
			case 'oid':
				$f['model'] = 'org';
				break;
			case 'status':
				$f['selector'] = 'content_status';
				break;
			default:
				break;
		}
		return true;
	}
	
	
	public function undoPubtoForModel($content2orginfo)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...");
		
		$id = $content2orginfo['id'];
		$cid = $content2orginfo['cid'];
		$target_oid = $content2orginfo['oid'];
		
		$m = Factory::GetModel('content');
		
		$cinfo = $m->get($cid);
		if (!$cinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no cid '$cid'!");
			return false;
		}
		
						
		$fdb = array();		
		$m2 = Factory::GetModel('file');		
		$fileinfo = $m2->getFileInfoByUrl($cinfo['video']);
		if ($fileinfo) {
			$fdb[$fid] =  $fileinfo;
		}
				
		
		$adb = $m->getAidsdbByInfo($cinfo);
		foreach($adb as $key=>$v) {
			if ($v['isdir']) {//目录
				$pid = $v['id'];
				$udb2 = $m2->gets("where pid=$pid and (type=1 or type=2) and status=1");
				foreach ($udb2 as $k2=>$v2) {
					$fdb[$v2['id']] = $v2;
				}
			} else if ($m2->is_av($v) && $v['status'] == 1){ //VIDEO OR AUDIO
					$fdb[$v['id']] = $v;
				}
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $fdb);
		
		$m3 = Factory::GetModel('file2org');
		foreach ($fdb as $key=>$v) {
			$fid = $v['id'];
			
			$res = $m3->unpubFile2OrgByOid($fid, $target_oid);
			if (!$res) {
				break;
			}
		}	
		
		//全部成功
		if ($res) {
			$this->del($id);
		}	
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT.");
		return $res;
	}
}