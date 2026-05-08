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
		switch ($f['name']) {
			case 'status':
				$f['input_type']='selector';
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
		
		$res = $this->update($_params);
		
		
		return $res;
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
		
		$f2oinfo = $this->getBy("where fid=$fid and oid=$oid and sid=$sid");
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
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "invalid file2org status! status=".$status.", expected status=3 COPY OK!" );
			}					
		} else {
			//一个机构可有多个存储设备，选择主设备
			
			$newf2o = array(
					'fid'=>$fid,
					'oid'=>$oid,
					'sid'=>$sid,
					);						
			$res = $this->set($newf2o);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set file2org failed!");		
			}				
		}

		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT");
		
		return false;
	}
	protected function doUnpubFile2OrgByOid($f2oinfo)
	{
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
				
		return $res;
		
	}
	
	//把已经同步的文件置为已删除状态
	public function unpubFile2OrgByOid($id, $oid)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...$id/$oid");
		
		//查找一条需要待镜像的文件
		$f2oinfo = $this->getBy("where fid=$id and oid=$oid");
		if (!$f2oinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no file2org info!");
			return false;
		}
		
		$res = $this->doUnpubFile2OrgByOid($f2oinfo);			
		
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT...");
		
		return $res;
	}
	
	
	
}
