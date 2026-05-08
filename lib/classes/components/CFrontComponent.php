<?php

class CFrontComponent extends CFileComponent
{
	protected $_uuid = '';
	protected $_cid = 0;
	protected $_tid = 0;
	protected $_tinfo = null;
	
	function __construct($name, $options)
	{
		parent::__construct($name, $options);		
	}
	
	function CFrontComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	
	protected function init(&$options=array())
	{
		parent::init($options);
		
		//videojs
		$this->enableJSCSS('video');
		
		
		
		//homeconfig
		/*if (is_model('home_config')) {
			$m = Factory::GetModel('home_config');
			$homecfg = $m->getParams();
			$this->assign('homecfg', $homecfg);
		}*/
	}
	
	
		
	protected function preTask(&$options=array())
	{
		$_client = $options['_client'];
		$_useragent = $options['_useragent'];
		
		$webid = $_client.'_'.$_useragent;
		
		$m = Factory::GetModel('webclient');
		$this->_tinfo = $m->getInfoByID($webid, $options);		
	}	
	
	protected function postTask(&$options=array())
	{
		$scf = Factory::GetSiteConfiguration();
		
		//tpl
		$m = Factory::GetModel('site_template');
		$tpl = isset($scf['template'])?$scf['template']:'default';
		$tplinfo = $m->get($tpl);
		if ($tplinfo) {
			$this->loadPortlet($tplinfo);
		}
	}	
	
	protected function setCfgTplDir($tplname, &$options=array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN ... tplname=$tplname ");

		if (($pos = strpos($tplname, '/')) !== false) {
			$dirname = substr($tplname, 0, $pos);
			$tplname = substr($tplname, $pos+1);

			$cfg_tdir = RPATH_TEMPLATES.DS.$dirname;
			$options['cfg_tdir'] = $cfg_tdir;
		}

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT. tplname=$tplname");
		return $tplname;
	}
		
	protected function loadTemplate(&$options = array())
	{
		$scf = Factory::GetSiteConfiguration();
		
		if (isset($scf['lang'])) {
			$options['lang'] = $scf['lang'];
			$options['_lang'] = $scf['lang'];
		}
				
		/*$tplname = $scf['template'];		
		$tdir = $this->_ptdir.DS.$tplname;
		if (!is_dir($tdir)){
			$tdir = RPATH_TEMPLATES.DS.$tplname;
			if (!is_dir($tdir)){
				$tdir = $this->_default_tdir;
			}
		}			
		$options['tdir'] = $tdir;*/
	
		//LOGO
		empty($scf['logo']) && $scf['logo'] = $options['_dstroot'].'/img/logo.png';
		$this->assign('scf', $scf);		
		$this->assign('uuid', $this->_uuid);
		$this->assign('cid', $this->_cid);
		$this->assign('tid', $this->_tid);
		
		$res = parent::loadTemplate($options);

		
		return $res;
	}
	
	protected function vplay(&$options=array())
	{
		$this->enableJSCSS('videojs');
		$this->_template = 'dt_vplay';		
		
		$id = $this->_id;
		if (!$id) {
			show_error('str_params_error');
			return false;
		}
		
		$fields = array();
		
		$m = Factory::GetModel('content');		
		$params = $m->getForView($id, $options);		
		if (!$params) {
			show_error('str_notfound_error');
			return false;
		}
		
		//mimetype
		if (strstr($params['playurl'], "m3u8")) {
			$params['mimetype'] = "application/x-mpegURL";
		} else {
			$params['mimetype'] = "video/mp4";
		}
		$m->incHits($id);
		
		$this->assign('params', $params);
	}
	
	protected function initModuleAttribs($name, &$attribs, $options=array())
	{
		//TODO...
		//$attribs['dlg'] = 1;
		
		if ($this->_cid > 0)
			$attribs['cid'] = $this->_cid;
			
		if ($this->_tid > 0)
			$attribs['tid'] = $this->_tid;
	}
	
	protected function detail(&$options=array())
	{
		//http://localhost/rc4/content/5
		/*$tid = $this->_id;
		
		!$tid && $tid = isset($options['id'])?$options['id']:0;
		!$tid && show_error('parameter error');
		
		$this->_tid = $tid;
		
		$m = Factory::GetModel('content');
		$res = $m->get($tid);
		!$res && show_error('NOT FOUND CONTENT!');
		
		//link
		if ($res['link'])
			redirect($res['link']);
		
		$cid = $this->_cid = $res['cid'];	
		$m->incHits($tid);	
		
		$this->assign('tid', $tid);
		$this->assign('_content_title', $res['title']);
		
		$options['_url'] = $options['_weburl'].'/content/'.$tid;
		
		$m2 = Factory::GetModel('catalog');
		$catalog = $m2->get($cid);
		
		$tplname = !empty($res['tpl_content'])?$res['tpl_content']:($catalog["tpl_content"]? $catalog["tpl_content"] : "content");
		
		$this->setTpl($tplname);
		$this->assign("params", $res);*/
		return $this->show($options);
	}

	
	protected function read($id, &$options=array())
	{
		$m = $this->getFileModel();
		$res = $m->share($id, $options);
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
		$res = $m->download($fid, $options, true);	
		
		exit;
	}
	
	protected function upload(&$options=array())
	{
		$m = Factory::GetModel('file');
		$res = $m->upload($options);
		showStatus($res?0:-1, $res);
	}	
	
	protected function delete(&$options=array())
	{
		showStatus(-1);
	}
}