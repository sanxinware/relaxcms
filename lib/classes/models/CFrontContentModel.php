<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CFrontContentModel extends CContentModel
{
	public function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	public function CFrontContentModel($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	
	
	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			case 'cid':
				$f['searchable'] = false;
				$f['show'] = false;
				break;
			case 'description':
			case 'status':
			case 'taxis':
			case 'flags':
				$f['show'] = false;
				break;
			default:
				break;	
		}
		return true;
	}
	
	protected function _initActions()
	{
		parent::_initActions();
		
		$this->_default_actions['edit']['enable'] = false;
		$this->_default_actions['del']['enable'] = false;
	}
	
	public function getInfoByID($webid, &$options=array())
	{
		$_client = $options['_client'];
		$_useragent = $options['_useragent'];
		
		$tid = md5($webid);
		$tinfo = $this->getBy("where tid='$tid'");
		if (!$tinfo) {
			
			$m = Factory::GetModel('tmconfig');
			if (!method_exists($m, 'getConfig'))
				return false;
			
			$tmcfg = $m->getConfig();
			if ($tmcfg['savewebclient'] != 1) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING:no save webclient");
				return false;
			}
			
			$tinfo = array();
			$tinfo['tid'] = $tid;
			$tinfo['type'] = 2;
			$tinfo['name'] = "WEB".$_client;		
			$tinfo['systeminfo'] = $webid;
			
			$tinfo['ip'] = $_client;
			$tinfo['online'] = 1; 
			$tinfo['last_access_time'] = time();
			
			$res = $this->set($tinfo);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call set failed!",$tinfo);
			}
		} else { 
			$tinfo['ip'] = $_client;
			$tinfo['last_access_time'] = time();
			$tinfo['last_access_id'] = 0; 
			$tinfo['online'] = 1;
			$res = $this->set($tinfo);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call set failed!", $tinfo);
			}			
		}	
		
		return $tinfo;
	}
	
	protected function formatForViewForContent2Model(&$row, &$options=array())
	{
		if (isset($row['modname']))
			return true;
		
		
		$id = $row['id'];		
		$m = Factory::GetModel('content2model');
		$res2 = $m->getOne(array('cid'=>$id));
		if ($res2) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $res2);
			
			$modname = $res2['modname'];
			$mid = $res2['mid'];				
			$m3 = Factory::GetModel($modname);
			$options['detail'] = true;
			$res3 = $m3->getForView($mid, $options);
			if ($res3) {
				foreach ($res3 as $key=>$v) {
					if (!isset($row[$key]))
						$row[$key] = $v;
				}
				
				//modname
				$row['modname'] = $modname;
				$row['mid'] = $mid;
				$row['modinfo'] = $res3;
			}
		}
	}
	
	
	protected function formatForViewForFrontend(&$row, &$options=array())
	{
		//content2model
		$this->formatForViewForContent2Model($row, $options);
		
		$m = Factory::GetModel('catalog');
		$catalog = $m->getCatalogById($row['cid'], $options);
		
		
		if ($catalog)
			$row['listurl'] = $options['_webroot']."/list/$catalog[id]";
		
		$_content = stripslashes($row['content']);
		
		$matches = array();
		
		$m = Factory::GetModel('file');
		$res = preg_match_all("/src\b\s*=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?/i", $_content, $matches);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $matches);		
		
		if ($res && count($matches[1]) > 0) {
			
			foreach ($matches[1] as $key=>$v) {
				//fid
				$fileinfo = $m->getFileInfoForViewByUrl($v, $options);
				
				$old = array();
				$new = array();
				
				$old[] = $v;
				if ($fileinfo) {
					if ($m->is_image($fileinfo)) {
						$new[] = $fileinfo['previewUrl'];
					}
					if ($m->is_av($fileinfo)) { 
						$new[] = $fileinfo['playurl'];
					}
				} else {
					$new[] = $v;
				}				
			}
			$_content = str_replace($old, $new, $_content);	
		}
		
		
		
		$pattern = "/\[attach\](\d+)\[\/attach\]/";		
		$_content = $this->formatAttachForView($pattern, $_content, $options);
		
		$pattern = "/\[attach\]([0-9a-fA-F]{32})\[\/attach\]/";
		$_content = $this->formatAttachForView($pattern, $_content, $options);
		
		
		$row['content'] = $_content;		
		
		$time_format = isset($options['time_format'])?$options['time_format']:'Y-m-d';
		$maxlen = isset($options['maxlen'])?$options['maxlen']:128;
		
		$row['title'] = $row['name'];
		$row['show_time'] = tformat($row['ctime'], $time_format);
		$row['subtitle'] = $row['title'];
		if ($maxlen > 0) {
			$row['subtitle'] = utf8_substr($row['subtitle'], 0, $maxlen);			
		}
		
		$row['longtime'] = tformat_timelong($row['ts']);
		
		
	}
	
	
	public function get_content_shtml_name($id)
	{
		$m = Factory::GetModel('site_config');
		$scf = $m->getParams();
		
		$index_shtml_name = $scf['index_shtml_name'];
		$ext = s_fileext($index_shtml_name);
		
		return "content_$id.$ext";		
	}
	
	protected function formatContentUrl(&$row, &$options = array())
	{
		$m = Factory::GetModel('site_config');
		$scf = $m->getParams();
		
		$shtml_filename = $this->get_content_shtml_name($row['id']);
		
		if (!empty($row['link'])) { //Á´½Ó
			$row['url'] = $row['link'];
			$row['target'] = "_blank";
		} elseif ($scf['htmlpub'] == 1) { //¾²Ì¬»º´æ
			$row['url'] = $options['_webroot'].'/'.$scf['shtml_uri_base'].'/'.$row['cid'].'/'.$shtml_filename;
		} else {
			$row['url'] = $options['_webroot'].'/'.$scf['index_script_name']."/content/$row[id]";
		}
		
		//$row['listurl'] = $catalog[$res['cid']]['url'];
		//$url = $options['_webroot'].'/content/'.$id;
		//$row['url'] = $url;
	}
	
	public function formatForView(&$row, &$options = array())
	{
		$res = parent::formatForView($row, $options);
		
		$this->formatForViewForFrontend($row, $options);
		
	}
	public function select($params=array(), &$options=array())
	{
		$params['status'] = 1;
		return parent::select($params, $options);
	}
	

	
}