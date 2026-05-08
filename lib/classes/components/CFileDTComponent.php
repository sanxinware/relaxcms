<?php

class CFileDTComponent extends CDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CFileDTComponent($name, $options)
	{
		$this->__construct($name, $options);
	}	
	
	
	protected function show(&$options=array())
	{
		parent::show($options);
		$this->setTpl('file');		
	}
	
	
	
	protected function selectForFileView($modname, $params, &$options=array())
	{
		$m = Factory::GetModel('file');
		$modinfo = $m->getModelInfo();
		
		$pid = isset($params['pid'])?$params['pid']:0;
		$positions = $m->getPostions($pid);
		
		//$this->setFilter($params);
		$rows = $m->selectForView($params, $options);	
		$fdb = array();
		foreach ($modinfo['fdb'] as $key=>$v) {
			if (!$v['show'])
				continue;
			$fdb[$key] = $v;
		}
		
		$data = array(
				'fileview'=> array(
					'name'=>$this->_name,
					'fields'=>$fdb,
					'pkey'=>'id',
					'sbt' => "$options[sbt]",
					'positions'=>$positions,
					'total'=>$params['total'],
					'page'=>$params['page'],
					'page_size'=>$params['page_size'],
					'num'=>$params['nr_row'],
					'sort'=>$params['order'],
					'order'=>$params['dir'],
					'rows'=>$params['rows']
					)
				);
				
		return $data;		
	}
	
	
	protected function fileview(&$options=array())
	{
		$params = array();
		$this->getParams($params);
		
		//默认页面大小
		$cf = get_config();
		$default_page_size = $cf['page_size'];
		if ($default_page_size <= 0) 
			$default_page_size = 8;
		
		
		$page = $this->request('page', 1);
		$page_size = $this->request('page_size', $default_page_size);
		$sort = $this->request('sort', '');
		$dir = $this->request('order', '');
		
		$params['page'] = $page;
		$params['page_size'] = $page_size;
		if ($sort)
			$params['order'] = $sort;
		if ($dir)
			$params['dir'] = $dir;
			
		
		
		
		$data = $this->selectForFileView('file', $params, $options);
						
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $data);
		
		showStatus(0, $data);

	}	
	
	protected function fileinfo(&$options=array())
	{
		$fid = $this->_id;
		if (!$fid) 	
			exit('error');		
			
		$m = Factory::GetModel('file');	
		$fileinfo = $m->getFileInfo($fid, $options);
		
		showStatus($fileinfo?0:-1, $fileinfo);
	}
	

	protected function setdir(&$options=array())
	{
		$this->getParams($params);		
		$m = Factory::GetModel('file');
		$res = $m->newDirectory($params, $options);
		
		showStatus($res?0:-1);		
	}
	
	
	protected function newdir(&$options=array())
	{
		$this->setdir($options);
	}
	
	protected function newfile(&$options=array())
	{
		$this->getParams($params);		
		$m = Factory::GetModel('file');
		$res = $m->newTxtFile($params, $options);
		
		showStatus($res?0:-1);	
	}
	
	
	protected function getDir($pid, $params=array(), &$options=array() )
	{
		$m = Factory::GetModel('file');
		$fdb = $m->getSubDir($pid, $params, $options);
		
		return $fdb;
		
	}
	
	protected function setImg2text($options = array())
	{
		$id = $this->_id;
		
		$m = Factory::GetModel('file2model');
		
		$params = $m->get($this->_id);
		
		$val = $params['description'];
				
		$this->enableJSCSS('ckeditor');
		
		$name = 'img2text';
		
		$simpleToolBar = "toolbar: [
				[ 'Paste', 'Copy', 'Cut'],
				],";
		
		$var_repconfig = "var repconfig = {
				$simpleToolBar
				toolbarCanCollapse:false,removePlugins:'elementspath',height:'320',  }; ";
		
		$id = "param_$name";
		$res =  "<textarea name='params[$name]' id='$id' class='ckeditor form-control' rows='6' >$val</textarea>";
		
		$res .= "<script> if (typeof(CKEDITOR) != 'undefined') { $var_repconfig CKEDITOR.replace('$id', repconfig);}</script>";
		
		$this->assign('img2text_content', $res);
		
		$this->setTpl('file_img2text');
	}
	
	protected function parseImg2text(&$options=array())
	{
		$id = $this->_id;
		$content = $this->request('content');
		
		$m = Factory::GetModel('file2model');
		
		$params=array();
		$params['id'] = $id;
		$params['description'] = $content;
		
		$m->update($params);
		
		
		$m2 = $this->getModel();
		$res = $m2->parseImg2text($content);
		
		showStatus($res?0:-1, $res);
	}
	
	
	protected function pubcontent(&$options=array())
	{
		$id = $this->request('id');
		$url = $options['_basename'].'/site_content/add/?aids='.$id.'&r='.time();
		redirect($url);
	}
	
	
	protected function filecontent(&$options=array())
	{
		$m = Factory::GetModel('file');
		$fdb = $m->filecontent($options);
		if (!$fdb) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "filecontent failed!");
			showStatus(-1);
		}
		
		CJson::encodedPrint(array('files' => $fdb));		
		exit;		
	}
	
	protected function moveto(&$options=array())
	{
		$params = array();
		$this->getParams($params);
		
		$m = Factory::GetModel('file');
		$res = $m->moveto($params, $options);
		
		showStatus($res?0:-1);
		
	}
	
	
	protected function copyto(&$options=array())
	{
		$params = array();
		$this->getParams($params);
		
		$m = Factory::GetModel('file');
		$res = $m->copyto($params, $options);
		
		showStatus($res?0:-1);
		
	}
	
}