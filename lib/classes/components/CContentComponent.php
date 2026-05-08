<?php
/***
 * @file
 * 
 * 内容管理
 * 
 * */

defined( 'RMAGIC' ) or die( 'Restricted access' );

class CContentComponent extends CPubComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function CContentComponent($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	protected function show(&$options=array())
	{
		//$this->enableMenuItem('taxis');
		
		$res = parent::show($options);
		
		return $res;
	}
	
	
	protected function probCID()
	{
		$cid = $this->_id;					
		!$cid && $cid = isset($_SESSION['__cookie_last_cid'])?intval($_SESSION['__cookie_last_cid']):0;
		if (!$cid) {
			$m = Factory::GetModel('catalog');
			$cid = $m->get_first_cid();
		}
		return $cid;		
	}
	
	
	
	protected function getPlayUrl($fileinfo)
	{
		$m = $this->getStorageModel();
		$storageinfo = $m->get($fileinfo['sid']);
		$playurl = $storageinfo['path'].'/'.$fileinfo['path'];
		
		$convert_id = $fileinfo['convert_id'];
		$m2 = $this->getFileModel();
		$convertfileinfo = $m2->get($convert_id);
		if ($convertfileinfo) {
			$playurl = $storageinfo['path'].'/'.$convertfileinfo['path'];
		} 
		
		return $playurl;		
	}
	
	
	protected function probIDS(&$params, &$options=array())
	{
		$cid = $this->probCID();
		$params['cid'] =$cid;
		
		
		$_fids = $this->request('aids');
		//if (!$_fids)
		//	return $this->proLiveID($params, $options);
		
		$fids = explode(',', $_fids);
		
		$m = Factory::GetModel('file');
		$fdb = array();		
		$aids = array();
		
		foreach ($fids as $key=>$fid) {
			$fileinfo = $m->getForViewForUse($fid, $options);
			if ($fileinfo) {				
				//$photourl = $fileinfo['previewUrl']; 
				if ($m->is_image($fileinfo)) { //图片
					$photourl = $fileinfo['previewUrl'];
					$aids[] = $fileinfo['fileid'];
				} else if ($m->is_video($fileinfo)) {
					$videourl = $fileinfo['playurl'];
					$photourl = $fileinfo['_snapImageUrl'];
					$aids[] = $fileinfo['fileid'];
				}  else {
					$aids[] = $fileinfo['fileid'];
					if ($m->is_dir($fileinfo)) {
							$photourl = $options['_dstroot']."/img/dir.png";		
					}
				}
				$fdb[] = $fileinfo;
			} else {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING:no fid '$fid' for ues!");
			}
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $aids, $_fids);
		
		
		$params['name'] = $fileinfo['name'];
		$params['photo'] = $photourl;
		$params['video'] = $videourl;
		$params['aids'] = implode(',', $aids);
		$params['_fdb'] = $fdb;
		
		$params['from_model_name'] = 'file';
		$params['from_model_id'] = $_fids;
		
	}
	
	
	
	
	protected function initParams(&$params, &$options=array())
	{
		$res = parent::initParams($params, $options);
		
		$emid = $this->request('emid');
		if ($emid) {
			$modinfo = $this->deModelInfo($emid);
			if ($modinfo) {
				$params['modname'] = $modinfo['modname'];
				$params['mid'] = $modinfo['mid'];
				if (isset($modinfo['cid'])) {
					$params['id'] = $modinfo['cid'];
				}
				//fdb3 模型
				$m = Factory::GetModel('content');
				$fdb3 = $m->getModelFieldsForInput($params, $options);
				$this->assign('fdb3', $fdb3);
			}			
		}
				
		//$mid = isset($_SESSION['__cookie_last_mid'])?intval($_SESSION['__cookie_last_mid']):0;
		//$params['mid'] = $mid;
				
		//$this->assign('mid', $mid);		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		return $res;
		
	}
	protected function initParamsForAdd(&$params, &$options=array())
	{
		parent::initParamsForAdd($params, $options);
		$this->probIDS($params, $options);	

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
	}
	
	protected function initParamsForEdit(&$params, &$options=array())
	{
		parent::initParamsForEdit($params, $options);
		
		//fdb3 模型
		if (isset($params['modname'])) {
			$m = Factory::GetModel('content');
			$fdb3 = $m->getModelFieldsForInput($params, $options);
			$this->assign('fdb3', $fdb3);			
		}		
	}
	
	protected function postSubmitParams(&$params, &$options=array())
	{
		$_SESSION['__cookie_last_cid'] = $params['cid'];
		//$_SESSION['__cookie_last_mid'] = $params['mid'];
	}
	
	
	//内容发布
	protected function pub(&$options=array())
	{
		$emid = $this->request('emid');
		if (!$emid) 
			return false;
		$params = $this->deModelInfo($emid);
		$cid = isset($params['cid'])?$params['cid']:0;
		$this->_id = $cid;
		if ($cid > 0) {
			$this->edit($options);
		} else {
			$this->add($options);
		}
	}	
	
	
	//发布
	protected function release(&$options=array())
	{
		$tid = get_var("id","");		
		if (is_array($tid))
			$tid = implode(",", $tid);
		
		//发布状态更新
		$m = Factory::GetModel('content');
		$res = $m->release($tid);
				
		showStatus($res?0:-1);
	}
	
	//取消发布
	protected function unrelease(&$options=array())
	{
		$tid = get_var("id","");		
		if (is_array($tid))
			$tid = implode(",", $tid);
		
		//发布状态更新
		$m = Factory::GetModel('content');
		$res = $m->unrelease($tid);
				
		showStatus($res?0:-1);
	}
		
	//切换内容模型
	protected function switchmodel(&$options=array())
	{
		$id = $this->_id;
		$mid = $this->requestInt('mid');
		$m = Factory::GetModel('content');
		
		$fields = $m->getModelFieldsForInput($id, $mid, $params, $options);
		if (!$fields) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "WARNING: getModelFieldsForInput failed!");
			$fields = array();
		}
		$this->setColumns($fields, false);
		
		$this->setTpl('site_content_edit_model');
	}	


	protected function move(&$options=array())
	{
		//btSelectItem
		//bootrstrap params
		$params = array();
		$this->getParams($params);

		$btSelectItem = $params['btSelectItem'];
		if (!$btSelectItem) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no btSelectItem!");
			showStatus(-1);
		}
		if (!is_array($btSelectItem)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "Invalid btSelectItem!", $btSelectItem);
			showStatus(-1);
		}

		
		$id = intval($params['cid']);
		if (!$id) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no id!");
			showStatus(-1);
		}

		$m = Factory::GetModel('content');
		$res = $m->moveTo($btSelectItem, $id);

		showStatus($res?0:-1);
	}
	
	protected function doEdit($modname, &$options=array())
	{
		parent::doEdit($modname, $options);
		$this->setTpl('dt_edit');
	}
	
	
	protected function getDir($pid, $params=array(), &$options=array() )
	{
		$m = $this->getModel();
		$fdb = $m->getDir($pid, $params, $options);
		
		return $fdb;
		
	}
	
	protected function moveto(&$options=array() )
	{
		$params = array();
		$this->getParams($params);
		
		$m = $this->getModel();
		$res = $m->moveto($params);
		showStatus($res?0:-1);
	}
	
	protected function copyto(&$options=array())
	{
		$params = array();
		$this->getParams($params);
		
		$m = $this->getModel();
		$res = $m->copyto($params);
		showStatus($res?0:-1);
	}
	
}