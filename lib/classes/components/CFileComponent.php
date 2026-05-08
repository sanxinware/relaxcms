<?php

class CFileComponent extends CUIComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CFileComponent($name, $options)
	{
		$this->__construct($name, $options);
	}	
	
	protected function init(&$options=array())
	{
		parent::init($options);	
		//$this->enableJSCSS(array('jquery_fileupload', 'tileupload', 'videojs'), true);
		$this->enableJSCSS(array( 'video'), true);	
	}
		
	protected function doUpload(&$options=array(), $return_if_exists=false)
	{
		$options['model'] = isset($_REQUEST['model'])?$_REQUEST['model']:'';
		$options['mid'] = isset($_REQUEST['mid'])?$_REQUEST['mid']:0;
		$options['pid'] = isset($_REQUEST['pid'])?$_REQUEST['pid']:0;
		
		$m = Factory::GetModel('file');
		//$m = $this->getModel();
		$fdb = $m->upload($options, $return_if_exists);
		if (!$fdb) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "upload failed!");
			return false;
		}
		
		$baseurl = $options['_base'];		
		$_fdb = array();
		foreach ($fdb as $key=>$v) {
			//no opath
			$m->formatForViewUrl($v, $options);		
			/*
			$v['url'] = $baseurl.'?id='.$v['id'];
			$v['previewUrl'] = $baseurl.'/preview/'.$v['id'];
			$v['deleteUrl'] = $baseurl.'/delete/'.$v['id'];
			$v['downloadUrl'] = $baseurl.'/download/'.$v['id'];*/
			
			$item = array();
			$item['name'] = $v['name'];		
			$item['filename'] = $v['filename'];
			$item['url'] = $v['url'];
			$item['previewUrl'] = $v['previewUrl'];
			//$item['deleteUrl'] = $v['deleteUrl'];
			$item['downloadUrl'] = $v['downloadUrl'];
			$item['size'] = $v['size'];
			$item['oid'] = $v['oid'];
			$item['upload'] =1;
			
			
			
			$_fdb[] = $item;
		}
		
		return $_fdb;
	}
	
	protected function upload(&$options=array())
	{
		$viewcontent = isset($_REQUEST['viewcontent'])?$_REQUEST['viewcontent']:0;
		if ($viewcontent == 1) {
			$m = Factory::GetModel('file');
			$fdb = $m->filecontent($options);			
		} else {
			
			$fdb = $this->doUpload($options);
		}
		/*if (!$fdb) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "upload failed!");
			$fdb = array();
		}
		CJson::encodedPrint(array('files' => $fdb));		
		exit;*/		
		if (!$fdb) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "WARNING: upload failed!", $fdb);
		}
		showStatus($fdb?0:-1, $fdb);
	}
	
	//uploadFile
	protected function uploadFile(&$options=array())
	{
		$fdb = $this->doUpload($options, true);
		showStatus($fdb?0:-1, $fdb);
	}
	
	protected function doBigUpload(&$options=array())
	{
		$options['pid'] = isset($_REQUEST['pid'])?$_REQUEST['pid']:0;		
		$m = Factory::GetModel('file');		
		$res = $m->upload($options);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $res);
		
		return $res;
	}
	
	protected function bigupload(&$options=array())
	{
		$fileinfo = $this->doBigUpload($options);
		showStatus($fileinfo?0:-1);
	}
	
	protected function probFID($options)
	{
		$id = $this->_id;		
		if (!$id) {
			$m = Factory::GetModel('file');
			foreach ($options['vpath'] as $key=>$v) {
				if (is_numeric($v)) {
					$id = intval($v);
					break;
				} else if (is_md5($v)) {
						$fileinfo = $m->getOne(array('fileid'=>$v));
						if ($fileinfo) {
							$id = $fileinfo['id'];
						}					
				}
			}			
		}	
		
		return $id;
	}
	
	protected function preview(&$options=array(), $fid=0, $large=0, $width=0, $height=0)
	{
		$large = $this->requestInt('l');		
		!$large && $large = $this->requestInt('large');
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		$width = $this->requestInt('x', $large?1920:640);
		$height = $this->requestInt('y', $large?1080:360);
		
		//rlog('$this->_id='.$this->_id);
		//rlog($options['vpath']);
		if (!$fid) {
			$fid = $this->probFID($options);
			if (!$fid) 	
				exit('error');			
		}
		
		$m = Factory::GetModel('file');	
		//$m = $this->getModel();
		
		$m->preview( $fid, $width, $height);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		
		exit;
	}
	
	protected function spreview(&$options=array(), $fid=0)
	{
		if (!$fid) {
			$fid = $this->probFID($options);
			if (!$fid) 	
				exit('error');			
		}
			
		$m = Factory::GetModel('file');	
		$m->preview($fid, 128, 72, 'spreview');
	}
	
	protected function lpreview(&$options=array(), $fid=0)
	{
		if (!$fid) {
			$fid = $this->probFID($options);
			if (!$fid) 	
				exit('error');
			
		}
							
		$m = Factory::GetModel('file');	
		$m->preview( $fid, 1920, 1080, 'lpreview');
	}
	
	protected function getStorageModel()
	{
		return Factory::GetModel('storage');
	}
	
	protected function getFileModel()
	{
		return Factory::GetModel('file');
	}
	
	protected function downloadFile($fid, &$options=array())
	{
		if (!$fid)
			exit('error');		
		$f = Factory::GetModel('file');	
		$f->download($fid, $options);
		exit;
	}
	
	protected function download(&$options=array(), $fid=0)
	{
		if (!$fid) {
			$fid = $this->probFID($options);
			if (!$fid) 	
				exit('error');			
		}
		
		$m = Factory::GetModel('file');
		$res = $m->download($fid, $options);	
		
		exit;
	}
	
	
	
	protected function downloadshare(&$options=array())
	{
		$m = $this->getModel();
		$res = $m->downloadshare($this->_id, $options);
		
		showStatus($res?0:-1);
	}	
	
	protected function checkDownloading(&$options=array())
	{
		$m = $this->getModel();
		$res = $m->checkDownloading($this->_id, $options);
		
		showStatus($res?0:-1, array('refresh'=>1));
	}	
	
	
	protected function read($id, &$options=array())
	{
		$m = $this->getFileModel();
		$res = $m->read($id, $options);
		exit;	
	}
	
	
	protected function probFIDByPath($options)
	{
		$vpath = $options['vpath'];
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $vpath);
		
		
		$nr = count($vpath);
		
		$m = Factory::GetModel('file');
		
		$pid = 0;
		$pathinfo = array();
		for($i=0; $i<$nr; $i++) {			
			$name = $vpath[$i];
			$fileinfo = $m->getOne(array('pid'=>$pid, 'name'=>$name));
			if ($fileinfo) {
				if ($fileinfo['isdir'])				
					$pid = $fileinfo['id'];
										
				$pathinfo[] = $fileinfo;			
			} else if ($pid > 0) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no name '$name'!");
			}
		}
		if (!$pathinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no path!", $vpath);
			return false;
		}
		$fileinfo = end($pathinfo);
		
		if ($fileinfo['isdir']) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "the path is dir!", $fileinfo);
			return false;
		}		
		
		return $fileinfo['id'];
		
	}
	
	protected function f(&$options=array())
	{
		//http://localhost/rc4/lib/themes/system/php/t.php/file/62/0/3.mp4
		$vpath = $options['vpath'];
		$action = '';
		$id = 0;	
		$w = 0;
		$h = 0;
		$l = 0;
		
		$fid = $this->probFID($options);
		if (!$fid) 	{
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $options['vpath']);
			if (!($fid = $this->probFIDByPath($options))) {
				exit('NOT Found!');
			}
		}
			
		foreach ($vpath as $key=>$v) {
			
			if (method_exists($this, $v)){
				if ($v != __FUNCTION__)
					$action = $v;
				continue;
			}
			
			if (!$w && is_numeric($v)) {
				$n = intval($v);
				if ($n <= 2)
					$l = $n;
				else 
					$w = $n;
				continue;
			}
			
			if (!$h && is_numeric($v)) {
				$h = intval($v);
				continue;
			}
		}
		
		if ($action && $action != 'show') {
			$res = $this->$action($options, $fid, $l, $w, $h);
		} else {
			$res = $this->read($fid, $options);
		}
		
		return $res;
	}
	
	protected function getDir($pid, $params=array(), &$options=array() )
	{
		return array();
	}
	
	
	public function jstree(&$options=array())
	{
		$pid = $_REQUEST["parent"];
		
		$params = array();
		
		$data = array();
		if ($pid == '#') {
			$data[] = array(
					'id'=>0,
					'text'=>'顶层根目录',
					'icon'=> "fa fa-folder icon-lg icon-state-warning",
					'children'=>true,
					'state'=>array("disabled"=>false, "opened"=>true, "selected"=>true)
					);
			
		} else {
			$pid = intval($pid);
			
			$fdb = $this->getDir($pid, $params, $options);
			foreach ($fdb as $key => $v) {
				
				$v["text"] = $v['name'];
				$v["icon"] = "fa fa-folder icon-lg icon-state-warning";
				$v["children"] = $v['hasChildren'];
				//$v["type"] = "root";
				//if ($v['pid'] == 0)
				//	$v["state"] = array("disabled"=>false, "opened"=>true, "selected"=>true);
				
				$data[] = $v;
			}
		}
		
		header('Content-type: text/json');
		header('Content-type: application/json');
		echo json_encode($data);
		
		exit;
	}
	
	protected function selectdir(&$options)
	{
		$options['dlg'] = 1;
		$this->setTpl("selectdir");
	}
	
	protected function getFileModelName()
	{
		return 'file';
	}
	
	protected function selectfile(&$options=array())
	{
		$options['dlg'] = 1;
		$this->request('type', -1);
		$modname = $this->getFileModelName();
		$this->assign('modname', $modname);
		
		$this->setTpl("selectfile");
	}
	
	protected function fsselect(&$options=array())
	{
		$this->selectfile($options);
	}
	
	protected function setgallery(&$options=array())
	{
		//&$options=array()
		$m = Factory::GetModel('file2model');
		
		$modinfo = $m->getModelInfo();
		
		if ($this->_sbt) {
			$this->getParams($params);
			
			//
			$params['modname'] = $this->_modname;
			$params['mid'] = $this->_modname;
			
			$res = $m->set($params, $options);
			
			$data = array();
			if (isset($options['data']))
				$data = $options['data'];
			
			showStatus($res, $data);
			
			return $res;
		}
		
		$modname = $modinfo['name'];		
		$table_id = 'mod_table_'.$modname;
		$this->assign('table_id', $table_id);
		
		$params = $m->get($this->_id);
		$tname = $this->_task;
		if ($this->_id == 0){		
			$fields = $m->getFieldsForInputAdd($params, $options);
		} else {
			$fields = $m->getFieldsForInputEdit($params, $options);
		}
		if (!$fields) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "WARNING: getFieldsForInput$tname failed!", $tname, $modinfo);
			$fields = array();
		}
		
		$this->assign('fields', $fields);		
		
		//$this->initTools('edit');
		$mi18n  = get_i18n('mod_'.$tablename);
		$this->assign('mi18n', $mi18n);
		
		$this->initTools($tname);
		$this->setColumns($fields, false);
		$this->setTpl('dt_edit');
		
		return $fields;
	}
	
	protected function fileselectorForSetGallery(&$options=array())
	{
		$options['task'] = 'fileselectorForSetGallery';
		return $this->setgallery($options);
	}
	
	
	protected function fileselectorForSelected(&$options=array())
	{
		$mid = $this->requestInt('mid');
		$name = $this->request('name');
		$aids = $this->request('aids');
		$m = Factory::GetModel($this->_modname);		
		$res = $m->getGalleryForSelected($mid, $name, $aids, $options);		
		if (!$res)
			showStatus(-1);
		else
			showStatus(0, $res);
		
		exit;
	}
	
	
	protected function fileselector(&$options=array())
	{
		$pdb = $options['vpath'];
		//selected
		foreach ($pdb as $key=>$v) {
			switch ($v) {
				case 'selected':
					return $this->fileselectorForSelected($options);				
				//setgallery
				case 'setgallery':
					return $this->fileselectorForSetGallery($options);			
				
				default:
					break;
			}
		}		
		return $this->selectfile($options);
	}
	
	
	protected function gallery(&$options=array())
	{
		$pdb = $options['vpath'];
		//selected
		foreach ($pdb as $key=>$v) {
			switch ($v) {
				case 'selected':
					return $this->fileselectorForSelected($options);				
				//setgallery
				case 'setgallery':
					return $this->fileselectorForSetGallery($options);			
				
				default:
					break;
			}
		}		
		return $this->selectfile($options);
	}
	
	
	
	
	protected function onImportFile($params, &$options=array())
	{
		return true;
	}
	
	protected function doImportFile($tfinfo, &$options=array())
	{
		if (!$tfinfo)
			return false;
		
		$tmpfile = $tfinfo['tmp_name'];
		
		$ext = s_fileext($tfinfo['name']);
		$newfile = RPATH_CACHE.DS.md5($tfinfo['name'].time()).'.'.$ext;
		copy($tmpfile, $newfile);
		
		$params = $tfinfo;
		$params['newfile'] = $newfile;
		
		$res = $this->onImportFile($params, $options);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call onImportFile failed!");
		}
		
		unlink($newfile);
		return $res;
		
	}
	
	protected function import(&$options=array())
	{
		$res = false;
		foreach ($_FILES as $key => $v) {
			
			if (is_array($v['name'])) {
				
				$nr = count($v['name']);				
				for($i=0; $i<$nr; $i++) {
					$params = array();					
					$params['name'] = $v['name'][$i];
					$params['type'] = $v['type'][$i];
					$params['tmp_name'] = $v['tmp_name'][$i];
					$params['error'] = $v['error'][$i];
					$params['size'] = $v['size'][$i];
					
					$res = $this->doImportFile($params, $options);
					if (!$res) {
						rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call doImportFile failed!");
						break;
					}
				}
			} else {
				$res = $this->doImportFile($v, $options);
			}
		}
		
		showStatus($res?0:-1, $res);
	}
	
	
	protected function share(&$options)
	{
		$ids = $this->request('id');
		$iddb = explode(',', $ids);
		
		$m = Factory::GetModel('file');
		foreach ($iddb as $key=>$id) {
			$res = $m->onoffShare($id);
		}
		showStatus($res?0:-1);
	}
}