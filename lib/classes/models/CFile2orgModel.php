<?php

/**
 * @file
 *
 * @brief 
 * 
 * 文件引用模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

//array('0'=>'待同步','1'=>'调度中', '2'=>'同步中', '3'=>'已同步', '4'=>'待删除', '5'=>'已删除')
define ('SS_DEFAULT',  0);
define ('SS_SCHEDULE', 1);
define ('SS_SYNCING',  2);
define ('SS_SYNCED',   3);
define ('SS_DELETING', 4);
define ('SS_DELETED',  5);

class CFile2orgModel extends CPub2orgModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);		
	}
		
	public function CFile2orgModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			case 'ts':
				$f['input_type']='TIMESTAMP';
				break;
			case 'status':
				$f['input_type']='selector';
				break;
			case 'oid':
				$f['model']='org';
				break;
			case 'sid':
				$f['model']='storage';
				break;
			case 'fid':
			case 'fieldname':
			case 'modname':
			case 'mid':
			case 'num':
				$f['edit'] = false;
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
	
	public function setStatus($id, $status, $lsize=0)
	{

		$_params = array();
		
		$_params['id'] = $id;
		$_params['status'] = $status;
		
		if ($lsize > 0)
			$_params['lsize'] = $lsize;
		
		$_params['last_ts'] = time();
		
		if ($status == SS_DELETED) {
			$_params['used'] = 0;
		}
		
		$res = $this->update($_params);
		
		
		
		return $res;
	}

	
	public function setFile2Org($pid, $oid, $fileinfo)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN, pid=$pid, oid=$oid");
		
		$fid = $fileinfo['id'];
		$type = $fileinfo['type'];
		
		$m0 = Factory::GetModel('file');
		//if ($type != 1) { //非视频
		if (!$m0->is_av($fileinfo)) { 
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: not video type of id '$fid'!");
			return false;
		}
		
		//原文件所有ID
		$org_oid = $fileinfo['oid'];
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "org_oid=$org_oid,oid=$oid");
		//if ($org_oid != $oid) { //文件复制
		//查询本地镜像是否存在
		$m2 = Factory::GetModel('storage');
		$sdb = $m2->getStorageListByOID($oid, $defaultStorageInfo);
		
		$res = true;
		$m3 = Factory::GetModel('file2org2pub');
		
		$_params2 = array();
		$_params2['pubid'] = $pid;
		
		foreach ($sdb as $key=>$v) {
			$sid = $v['id'];
			
			//一个机构可有多个存储设备			
			$_params = array(
					'fid'=>$fid,
					'sid'=>$sid,
					'oid'=>$oid,
					'pid'=>$pid,
					);
			
			$file2org2pubinfo = array();
			
			$info = $this->getOne(array('fid'=>$fid, 'sid'=>$sid));
			if ($info) {
				$_params['id'] = $info['id'];
				if ($info['status'] > 3)
					$_params['status'] = 0; //回到待同步状态
				$used = $info['used'];
				
				$_params2['f2oid'] = $info['id'];
				$file2org2pubinfo = $m3->getOne($_params2);
				if (!$file2org2pubinfo) { //说明发布关联过
					$used ++ ;
				}
			} else {
				$used = 1;
			}
			
			$_params['used'] = $used;
			$res = $this->set($_params);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set file2org failed!", $_params);
				break;		
			}
			
			//
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $file2org2pubinfo);
			
			
			//发布关联表
			if (!$file2org2pubinfo) {
				unset($_params2['id']);
				$_params2['f2oid'] = $_params['id'];			
				$m3->set($_params2);
			}
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT");
		
		return $res;
	}
	
	public function fsyncNotify($params, $options=array())
	{
		rlog(RC_LOG_DEBUG,__FILE__,__LINE__, __FUNCTION__, "IN...", $params);
		
		$id = intval($params['id']);
		$status = intval($params['status']);
		$lsize = intval($params['lsize']);
		
		$f2oinfo = $this->get($id);
		if (!$f2oinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no file2org id '$id'");
			return false;
		}
		
		//更新状态
		$this->setStatus($id, $status, $lsize);
		if ($status == 5) {
			rlog(RC_LOG_INFO, __FILE__, __LINE__, __FUNCTION__, "sync delete '$id' done!");
		}
		
		$oid = $f2oinfo['oid'];
		$fid = $f2oinfo['fid'];
		$m1 = Factory::GetModel('file');
		$fileinfo = $m1->get($fid);
		if (!$fileinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no file of id '$fid'");
			$m->del($id);
			return false;
		}
		
		$src = $fileinfo['opath'];
		
		//dst
		//更新发布: playUrl
		/*$sid = $f2oinfo['sid'];
		$m2 = Factory::GetModel('storage');
		$dststorageinfo = $m2->get($sid);
		if (!$dststorageinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no storage of id '$sid'");
			$m->del($id);
			return false;
		}		
		*/
		//$playUrl = $dststorageinfo['vodrooturl'].'/'.$fileinfo['path'];
		//$playUrl = $dststorageinfo['lanvodrooturl'].'/'.$fileinfo['path'];
		//$m3 = Factory::GetModel('pub2org');
		//$m3->updatePlayUrlByFidOid($fid, $oid, $playUrl);
		
		$m3 = Factory::GetModel('file2org2pub');
		$f2o2pubdb = $m3->gets(array('f2oid'=>$id));
		
		rlog(RC_LOG_DEBUG,__FILE__,__LINE__, __FUNCTION__, "IN2...", $f2o2pubdb);
		$m4 = Factory::GetModel('pubto');
		
		foreach ($f2o2pubdb as $key=>$v) {
			$pid = $v['pubid'];
			
			//pid
			$_params = array();
			$_params['id'] = $pid;
			$_params['status'] = $status;
			$m4->update($_params);
		}
		rlog(RC_LOG_DEBUG,__FILE__,__LINE__, __FUNCTION__, "OUT.");
		
		return true;
	}
	
	
	public function getLocalPlayUrl($oid, $fileinfo)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN");
		
		$fid = $fileinfo['id'];
		$type = $fileinfo['type'];
		
		$m0 = Factory::GetModel('file');
		//if ($type != 1) { //非视频
		if (!$m0->is_av($fileinfo)) { 
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "not video type of id '$fid'!");
			return false;
		}
		
		//原文件所有ID
		$org_oid = $fileinfo['oid'];
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "org_oid=$org_oid,oid=$oid");
		//if ($org_oid != $oid) { //文件复制
		//查询本地镜像是否存在
		$m2 = Factory::GetModel('storage');
		$storageinfo = $m2->getStorageInfoByOID($oid);		
		$sid = $storageinfo['id'];
		
		$f2oinfo = $this->getBy("where fid=$fid and sid=$sid");
		if ($f2oinfo) { //mountdir
			$sid = $f2oinfo['sid'];
			//检查状态
			$status = $f2oinfo['status'];
			if ($status == 3) {//复制完毕，播放本地
				if ($storageinfo) {
					$vodrooturl = $storageinfo['vodrooturl'];
					return $vodrooturl.'/'.$fileinfo['path'];
				}
			} else{
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "invalid file2org status! status=".$status.", expected status=3 COPY OK!" );
			}					
		} else {
			//一个机构可有多个存储设备，选择主设备			
			$newf2o = array(
					'fid'=>$fid,
					'sid'=>$sid,
					'oid'=>$oid,
					);						
			$res = $this->set($newf2o);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set file2org failed!");		
			}				
		}

		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT");
		
		return false;
	}
	
	
	//把已经同步的文件置为已删除状态
	public function unpubFile2OrgByOid($f2oinfo)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...");
		if ($f2oinfo['used'] > 1) {
			$this->dec($f2oinfo['id'], 'used');
			return true;
		}
		
		//查找一条需要待镜像的文件
		$res = true;		
		$status = intval($f2oinfo['status']);
		switch($status) { //1:待同步，/2：正在同步，/3：已同步,/4:待删除,/5:已删除
			case 1: 
			case 5: 
				//直接删除
				$res = $this->del($f2oinfo['id']);
				break;
			case 2: 
			case 3: 
				$res = $this->setStatus($f2oinfo['id'], 4);
				if (!$res)
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "update file2org status=4 failed!", $v);
				break;
			default:
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "unknown status=$status !!");
				$res = $this->del($f2oinfo['id']);
				break;
		}
		
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT...");
		
		return $res;
	}
	
	
	
}
