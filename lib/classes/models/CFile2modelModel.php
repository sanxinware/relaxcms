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
class CFile2modelModel extends CTableModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);		
	}
		
	public function CFile2modelModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function _initFieldEx(&$f)
	{
		switch ($f['name']) {
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
	
	
	public function setFile2ModelByUrl($modname, $mid, $url, $shared=false)
	{
		$m = Factory::GetModel('file');
		$fileinfo = $m->getFileInfoByUrl($url);
		if ($fileinfo) {
			$fid = $fileinfo['id'];
			
			$_params = array();
			
			$_params['num'] = 1;
			$_params['fid'] = $fid;
			$_params['modname'] = $modname;
			$_params['mid'] = $mid;			
			$this->set($_params);
			
			if ($shared) {
				$m->inc($fid, 'shared');
			}
		}
	}
	
	public function trigger($event, $finfo=array())
	{
		
		$params = array();
		$params['fid'] = $finfo['id'];
		$udb = $this->select($params);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN ... ", $udb);
		foreach ($udb as $key=>$v) {
			$modname = $v['modname'];
			$mid = $v['mid'];
			
			$m = Factory::GetModel($modname);
			$m->trigger($event, array('id'=>$mid));
		}
	}
	
	
	public function addFile2Model($fid, $fieldname, $modname, $mid, $params=array())
	{
		$m = Factory::GetModel('file');
		
		$finfo = $m->get($fid);
		if (!$finfo)
			return false;
		
		$oldinfo = $this->getOne(array('fid'=>$fid, 'modname'=>$modname, 'mid'=>$mid, 'fieldname'=>$fieldname));
			
		$params['fid'] = $fid;
		$params['modname'] = $modname;
		$params['fieldname'] = $fieldname;
		$params['mid'] = $mid;
		
		if ($oldinfo) {
			$id = $oldinfo['id'];
			$params['id'] = $id;				
			$res = $this->update($params);	
		} else {
			$res = $this->set($params);
			if ($res) {
				$m->inc($fid, 'used');
			}
		}
				
		return $res;
	}
	
	public function setFile2Model($fid, $fieldname, $modname, $mid, $params=array())
	{
		$oldinfo = $this->getOne(array('modname'=>$modname, 'mid'=>$mid, 'fieldname'=>$fieldname));
		if ($oldinfo) {
			$m = Factory::GetModel('file');
			if ($oldinfo['fid'] != $fid) { //替换
				$m->dec($oldinfo['fid'], 'used');
				
				$_params = array('');
				$_params['id'] = $oldinfo['id'];
				$_params['fid'] = $fid;		
				$this->update($_params);	
				
				$m->inc($fid, 'used');			
			} 
			return true;
		} 
		
		$res = $this->addFile2Model($fid, $fieldname, $modname, $mid);	
			
		return $res;
	}
	
	
	public function delFile2Model($id)
	{
		$old = $this->del($id);
		if ($old) {
			$m = Factory::GetModel('file');
			$m->dec($old['fid'], 'used');
		}
		return $old;
	}
	
	public function delFile2ModelByModelField($fieldname, $modname, $mid)
	{
		$udb = $this->select(array('modname'=>$modname, 'mid'=>$mid, 'fieldname'=>$fieldname));
		foreach ($udb as $key=>$v) {
			$this->delFile2Model($v['id']);
		}
	}
	
	public function delFile2ModelByModel($modname, $mid)
	{
		$udb = $this->select(array('modname'=>$modname, 'mid'=>$mid));
		foreach ($udb as $key=>$v) {
			$this->delFile2Model($v['id']);
		}
	}
	
	public function shareFile2Model($shared, $modname, $mid)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...$shared, $modname, $mid");
		$_params = array('modname'=>$modname, 'mid'=>$mid);
		$udb = $this->select($_params);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $udb, $_params);
		
		$m = Factory::GetModel('file');
		foreach ($udb as $key=>$v) {
			$m->shareFile($v['fid'], $shared);
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT.");
	}
}
