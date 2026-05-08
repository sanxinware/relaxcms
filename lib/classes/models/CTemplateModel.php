<?php
/**
 * @file
 *
 * @brief 
 * 
 * template model
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

define( 'TPL_TYPE_ROOT', 0);
define( 'TPL_TYPE_CONTENT', 1);
define( 'TPL_TYPE_CATALOG', 2);

class CTemplateModel extends CTableModel
{
	protected $_templates = array();
	protected $_i18nmap = array();
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function CTemplateModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function _init_field(&$f)
	{
		switch ($f['name']) {
			case 'type':
				$f['input_type'] = "selector";
				break;
			case 'isdir':
				$f['input_type'] = "yesno";
				break;
			case 'status':
				$f['input_type'] = "onoff";
				//$f['show'] = false;
				break;
			case 'pid':
				$f['input_type'] = "treemodel";
				break;
			default:
				break;
		}
		return true;
	}


	protected function getActions($row=array(), &$options=array())
	{
		$actions = parent::getActions($row, $options);
		
		if (!$row['isdir']) {
			unset($actions['install']);
		}
		return $actions;
	}

	private function get_all_templates()
	{
		$templates = array();
		
		$dir = RPATH_TEMPLATES;
		$udb = s_readdir($dir);
		
		$hdb = array('.svn');	
		$id = 1;	
		foreach ($udb as $key=>$v) {
			
			if (in_array($v, $hdb))
				continue;
			
			$cfile = $dir.DS.$v.DS.'config.php';
			if (file_exists($cfile)) {
				require $cfile;				
				$appcfg['id'] = $id++;
				$templates[$appcfg['name']] = $appcfg;
			}
		}
		
		return $templates;
	}
	
	
	protected function getcfg($name)
	{
		$tcfginfo = array();		
		$cfile = RPATH_TEMPLATES.DS.$name.DS.'config.php';
		if (file_exists($cfile)) {
			require $cfile;				
			$tcfginfo = $appcfg;
		}
		return $tcfginfo;
	}
	
	protected function getTplcfg($name)
	{
		$tcfginfo = array();		
		$cfile = RPATH_TEMPLATES.DS.$name.DS.'tplcfg.php';
		if (file_exists($cfile)) {
			require $cfile;				
			$tcfginfo = $tplcfg;
		}
		return $tcfginfo;
	}
	
			
	public function getTpls($params=array(), &$options=array())
	{
		$udb = $this->get_all_templates();
		
		$tpldb = $this->get_tpls();
		
		foreach ($tpldb as $key=>$v) {
			if (isset($v['enable']) && $v['enable'] == true) {
				$udb[$key]['checked'] = "checked";
			} else {
				$udb[$key]['checked'] = "";
			}
		}
		
		return $udb;
	}
	
	protected function initDigestForTpl($digests)
	{
		$m = Factory::GetModel('var');
		foreach ($digests as $key=>$v) {
			$params = array(
				'id'=>$key,
				'title'=>$v
				);
			$m->set($params);
		}
	}
	
	protected function formatkResourceSrc($src, $dirname, $options, &$typename='')
	{
		$src = trim($src);
		if (!$src)
			return $src;
		
		if (is_url($src))
			return $src;
		
		
		$resurl = $options['resurl'];
		$_dstroot = $options['_dstroot'];
		$_theroot = $options['_theroot'];
		
		$dname = dirname($src);
		if (is_start_with($dname,'../../static/')) {
			$_src = 'img'.str_replace($dname, '', $src);
			$_src1 = str_replace('../../static/', '', $src);
			$src = $_dstroot.'/'.$_src;	
			$file = RPATH_DIST.DS.$_src;
			
			if (!file_exists($file)) {				
				if (file_exists(RPATH_DIST.DS.$_src1)) {
					$file = RPATH_DIST.DS.$_src1;
					$src = $_dstroot.'/'.$_src1;	
				}
			}
			
		} elseif (is_start_with($dname,'../')) {//上级目录
			
			//eg : ../lem/img/p1.jpg
			$_src = $dirname.'/img'.str_replace($dname, '', $src);
			$_src1 = str_replace('../', '', $src);
			
			$src = $_theroot.'/'.$_src;	
			$file = RPATH_STATIC_THEMES.DS.$_src;
			if (!file_exists($file)) {				
				if (file_exists(RPATH_STATIC_THEMES.DS.$_src1)) {
					$file = RPATH_STATIC_THEMES.DS.$_src1;
					$src = $_theroot.'/'.$_src1;	
				}
			}		
			
		} else {
			$_src = $src;
			$src = $_theroot.'/'.$dirname.'/'.$_src;		
			$file = RPATH_STATIC_THEMES.DS.$dirname.DS.$_src;
			
		}	
		
		//检查文件是否存在，如果不存在，检查使用resurl前缀
		if (!file_exists($file) && $resurl) {
			$src = $resurl.'/'.$_src;	
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "WARNING : no file '$file', use resurl '$src'!");			
		}		
				
		$extname = s_fileext($src);
		if (CFileType::isVideo($extname)) {
			$typename = 'video';
		} else if (CFileType::isImage($extname)) {
				$typename = 'photo';
			}
				
		return $src;
	}

	protected function fixedForLocal($cfginfo, $dirname, &$params, $options)
	{
		$resurl = isset($cfginfo['resurl'])?$cfginfo['resurl']:'';
		$options['resurl'] = $resurl;
		
		if (isset($params['photo'])) {
			$params['photo'] = $this->formatkResourceSrc($params['photo'], $dirname, $options);			
		}
		
		if (isset($params['icon'])) {
			$params['icon'] = $this->formatkResourceSrc($params['icon'], $dirname, $options);			
		}
		
		//check data_url
		if (isset($params['data-url'])) {
			$src = $this->formatkResourceSrc($params['data-url'], $dirname, $options, $typename);	
			if ($typename)
				$params[$typename] = $src;
		}
		
		//content
		if (isset($params['content'])) {
			$params['content'] = str_replace("\n", "<p>", $params['content']);
		}
		if (isset($params['description'])) {
			$params['description'] = str_replace("\n", "<p>", $params['description']);
		}
		
		$logo = isset($params['logo'])?$params['logo']:'';
		if ($logo && !is_url($logo) && !is_start_slash($logo)) {
			$logo = $options['_theroot'].'/'.$dirname.'/'.$logo;
			$params['logo'] = $logo;
		}
		
		//link
		if (isset($params['link'])) {
			$params['link'] = str_replace('/rc', $options['_webroot'], $params['link']);
		}
		
		//fixed img in content
		//img src
		$data = $params['content'];
		if ($data) {
			$matches = array();
			$res = preg_match_all("/src\b\s*=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?/i", $data, $matches);
			if ($res && count($matches[1]) > 0) {
				$olddb = array();
				$newdb = array();
				$_dstroot = $options['_dstroot'];
				$_theroot = $options['_theroot'];
				for($i=0; $i<count($matches[1]); $i++) {
					$src = $old = $matches[1][$i];
					$ext = s_fileext($src);
					switch($ext) {
						case 'js':
							break;
						default:
							$file = RPATH_STATIC_THEMES.DS.$dirname.DS.$src;
							if (file_exists($file)) {
								
								$src = $this->formatkResourceSrc($src, $dirname, $options, $typename);	
								if ($typename && empty($params[$typename]))
									$params[$typename] = $src;	
									
								/*
								$dname = dirname($src);
								if (is_start_with($dname,'../../static/')) {
									$src = $_dstroot.'/'.str_replace($dname, '', $src);	
								} else {
									$src = $_theroot.'/'.$dirname.'/'.$src;		
								}*/					
							} else {
								rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: no file '$file'!");
							}				
							break;
					}
					if (!in_array($src, $newdb)) {
						$olddb[] = $old;			
						$newdb[] = $src;	
					}
				}
				$data = str_replace($olddb, $newdb, $data);
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $data, $olddb, $newdb);
				
				$params['content'] = $data;
			}
		}
		
	}
	
	
	protected function parseCatalogFlags($_flags)
	{
		$flags = is_numeric($_flags)?intval($_flags):0;
		
		if (!$flags) {
			if (!is_array($_flags)) {
				$_flags = explode('|', $_flags);
			} 
			
			$m3 = Factory::GetModel('var');			
			foreach ($_flags as $key=>$v) {
				$res = $m3->getMaskByTitle('catalog_flags', $v);
				if ($res)
					$flags |= $res;
				
			}
		}
		
		
		return $flags;
	}
	
	
	protected function initCatalogSingle($cfginfo, $tplcfg, $dirname, $params, $options)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'IN ... ', $params);
		
		$name = trim($params['name']);
		if (!$name) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no name!");
			return false;
		}
		/*if (isset($params['cid'])) {
			$params['id'] = $params['cid'];
			$params['taxis'] = $params['cid'];
		}*/
		$modname = $params['_modname']; 
				
		$m = Factory::GetModel($modname);
		if (($res = $m->getOne(array('name'=>$name)))) {
			$params['id'] = $res['id'];
		} else {
			if (isset($params['cid']) && is_numeric($params['cid'])) {
				$params['id'] = $params['cid'];
			}
		}
		
		$tpl_list = !empty($params['tpl_list'])?$dirname.'/'.$params['tpl_list']:'';
		$tpl_content  = !empty($params['tpl_content'])?$dirname.'/'.$params['tpl_content']:'';
		
		$pid = 0;
		
		
		$flags = 3;
		/*if (!isset($params['pid'])) {
			$flags |= 4;			
		}*/
		if (isset($params['_nav'])) {
			$flags |= 4;			
		}
		
		
		if ($params['flags'])
			$flags |= $this->parseCatalogFlags($params['flags']);

		//ifxed photo
		$this->fixedForLocal($cfginfo, $dirname, $params, $options);
		
		$params['tpl_list'] = $tpl_list;
		$params['tpl_content'] = $tpl_content;
		$params['pid'] = $pid;
		
		$params['flags'] = $flags;
		$params['status'] = 1;
		
		if (!($res = $m->set($params))) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set catalog failed!");
			return false;
		}
		//href
		$href = $params['href'];
		if (isset($tplcfg[$href])) {
			$pageinfo = $tplcfg[$href];
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$pageinfo ... ', $pageinfo);
		}
		
		
		return $params['id'];		
	}
	
	
	protected function initCatalogSingleForI18n($lang, $cinfo, $params, $options)
	{
		$name = trim($params['name']);
		if (!$name) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no name!");
			return false;
		}
		
		if (!isset($cinfo['id'])) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no cid!");
			return false;
		}
		
		$cid = $cinfo['id'];
		
				
		$params['cid'] = $cid;
		$params['i18n'] = $lang;
		
		$m = Factory::GetModel('catalog2i18n');
		if (($res = $m->getOne(array('cid'=>$cid, 'i18n'=>$lang)))) {
			$params['id'] = $res['id'];
		}
		
		if (!($res = $m->set($params))) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set catalog2i18n failed!");
			return false;
		}
		
		return $res;		
	}
	
	
	protected function initCatalogPID(&$catalogdb)
	{
		foreach ($catalogdb as $key=>&$v) {
			if (isset($v['pid'])) {
				$modname = $v['_modname'];
				$m = Factory::GetModel($modname);
				
				if (is_numeric($v['pid'])) {
					$res = $m->get($v['pid']);
				} else {
					$res = $m->getOne(array('name'=>$v['pid']));
				}
				if ($res) {
					$pid = $res['id'];
					$cid = $v['id'];
					
					$_params = array('pid'=>$pid, 'id'=>$cid);
					$m->update($_params);
					
					$v['pid'] = $pid;
				}
			}
		}
	}
	
	protected function initCatalog($cfginfo, $tplcfg, $dirname, $options, &$cdb=array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'IN');
		
		$catalogdb = get_cache_array('catalogdb', RPATH_TEMPLATES.DS.$dirname.DS.'catalogdb.php');
		if (!$catalogdb) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'no catalogdb failed!');
			return false;
		}
		
		$res = false;
		$taxis = 0;
		foreach ($catalogdb as $key=>$v) {
			//taxis
			$taxis += 2;
			$v['taxis'] = $taxis;
			
			
			$id = $this->initCatalogSingle($cfginfo, $tplcfg, $dirname, $v, $options);
			if (!$id) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "call initCatalogSingle failed!");
				continue;
			} 
			
			$v['id'] = $id;			
			//$v['cid'] = $cid;	
				
			if (is_array($v['i18n']) ) {	
				foreach ($v['i18n'] as $k2=>$v2) {
					$res = $this->initCatalogSingleForI18n($k2, $v, $v2, $options);
				}
			}
			
			$cdb[] = $v;
		}
		//pid
		$this->initCatalogPID($cdb);
			
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'OUT');
		return $res;		
	}
	
	protected function initContentSingleOfCatalog($tplcfg, $params)
	{
		$cid = isset($params['cid'])?$params['cid']:(isset($params['catalog'])?$params['catalog']:'');	
		
		if (!$cid)
			return 0;				
		
		if (is_numeric($cid)) {
			$cid = intval($cid);
		} else {
			if (is_start_with($cid,'$'))
				return 0;
				
			$m1 = Factory::GetModel('catalog');
			$cinfo = $m1->getOne(array('name'=>$cid));
			if ($cinfo) {
				$cid =	$cinfo['id'];
			} else { //创建
				$_params = array('name'=>$cid);
				if (!($res = $m1->set($_params))) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set catalog failed!");
					return 0;
				}				
				$cid = $_params['id'];
			}
		}
		
				
		//fixed tpl_content
		if (isset($params['tpl_content']) && $cid > 0) {
			$_params = array();
			$_params['id'] = $cid;
			$_params['tpl_content'] =  $params['tpl_content'];			
			$m1->update($_params);
		}
		
		//viewtype
		if (isset($params['viewtype']) && $cid > 0) {
			$_params = array();
			$_params['id'] = $cid;
			$_params['viewtype'] =  $params['viewtype'];			
			$m1->update($_params);
		}
		
		
		if (isset($params['pid']) && $cid > 0) {
			$_params = array();
			$_params['id'] = $cid;
			
			$pid = is_numeric($params['pid'])?intval($pid):0;
			if ($pid == 0) {
				$pinfo = $m1->getOne(array('name'=>$params['pid']));
				$pid = $pinfo['id'];
			}
			if ($pid > 0) {
				$_params['pid'] = $pid;			
				$m1->update($_params);
			}
		}		
		
		return $cid;
				
	}
	
	protected function parseContentFlags($_flags)
	{
		$flags = is_numeric($_flags)?intval($_flags):0;
		
		if (!$flags) {
			if (!is_array($_flags)) {
				$_flags = explode('|', $_flags);
			} 
			
			$m3 = Factory::GetModel('var');			
			foreach ($_flags as $key=>$v) {
				$res = $m3->getMaskByTitle('content_flags', $v);
				if ($res)
					$flags |= $res;
				
			}
		}
		
		
		return $flags;
	}
	
	protected function initContentSingle($cfginfo, $tplcfg, $dirname, $params, $options, $catalogdb)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'IN');
		
		$cid = $this->initContentSingleOfCatalog($tplcfg, $params);
		
		//tpl_content
		if (!empty($params['tplname'])) {
			$params['tpl_content'] = $dirname.'/'.$params['tplname'];
		}
		
		//FIXED cid
		$m2 = Factory::GetModel('content');
		
		$_params = array('name'=>$params['name']);
		if ($cid > 0) {
			$_params['cid'] = $cid;
		}
		
		$info = $m2->getOne($_params);
		if ($info) {
			$params['id'] = $info['id'];
		}
		
		$flags = 3; //默认		
		if ($params['flags'])
			$flags |= $this->parseContentFlags($params['flags']);
		
				
		$params['cid'] = $cid;		
		$params['status'] = 1;
		$params['flags'] = $flags; 
		//$params['content'] = isset($params['detail'])?$params['detail']:'';
		
		$params['content'] = html_entity_decode($params['content'], ENT_QUOTES);
		
		$this->fixedForLocal($cfginfo, $dirname, $params, $options);
		//初始不同步
		$options['__nocluster'] = true;		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);		
		if (!($res = $m2->set($params, $options))) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'set content failed!');
		}
		
		//href
		$href = $params['href'];
		if (isset($tplcfg[$href])) {
			$pageinfo = $tplcfg[$href];
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$pageinfo ...', $pageinfo);			
		}
			
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'OUT');
		
		return $res;
	}
	
	
	protected function initContent($cfginfo, $tplcfg, $dirname, $options, $catalogdb)
	{
		//$catalogdb = get_cache_array('catalogdb', RPATH_TEMPLATES.DS.$dirname.DS.'catalogdb.php');
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $catalogdb);
		
		$contentdb = get_cache_array('contentdb', RPATH_TEMPLATES.DS.$dirname.DS.'contentdb.php');
		if (!$contentdb) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'no contentdb failed!');
			return false;
		}
		
		//通常时间倒序
		$nr = count($contentdb);
		$taxis = 2*$nr;
		$ts = time();
		$_contentdb = array();
		foreach ($contentdb as $key=>$v) {
			$v['taxis'] = $taxis;
			$v['ts'] = $ts --;
			
			$_contentdb[] = $v;
			
			$taxis -= 2;
		}
		
		array_sort_by_field($_contentdb, 'taxis');
		
		$res = false;
		foreach ($_contentdb as $key=>$v) {
			$res = $this->initContentSingle($cfginfo, $tplcfg, $dirname, $v, $options, $catalogdb);
		}	
		return $res;		
	}
	
				
	protected function setTplModule($params)	
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		
		$m = Factory::GetModel('module');
		$res = $m->getOne(array('mid'=>$params['mid']));
		if ($res)
			$params['id'] = $res['id'];
			
		//
		//$content = $params['content'];
		//str_replace('\'', '"', $content);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $content);
		
		$params['content'] = htmlspecialchars($params['content']);
		
		//cid
		if (is_start_with($params['cid'], '$'))
			$params['cid'] = 0;
			
		//处理cid
		$cid = isset($params['cid'])?$params['cid']:0; //可能是个名称
		//处理FLAGS
		$flags = $params['flags']; //可能是个名称
		
		$res = $m->set($params);										
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set TPL module failed!", $params);
			return false;
		}

		if (!is_numeric($cid) && !is_start_with($cid, '$')) {
			$m2 = Factory::GetModel('catalog');
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "cid", $cid);
			$cid = $m2->createCatalog(array('name'=>$cid));
		}
		
		if (!is_numeric($flags)) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'flags='.$flags, $params);
			$m3 = Factory::GetModel('var');
			$res = $m3->getMaskByTitle('content_flags', $flags);
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "############## flags=$flags, res=".$res.',mid='.$params['id']);
			if ($res)
				$flags = $res;
		}

		//参数
		$_params = array();
		
		$_params['maxnum'] = $params['num'];		
		$_params['num'] = 0;		
		$_params['cid'] = $cid;		
		$_params['tags'] = $params['tags'];		
		$_params['flags'] = ($flags > 0)?$flags|3: 3;
		
		$res = $m->setModuleParams($params['id'], $_params);		
		
		return $res;
	}
	
	protected function parseTemplateFile($tplfile, $tinfo, $options)
	{
		//rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, $tplfile);
		
		$res = parseTplFile($tplfile);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "parse TPL file '$tplfile' failed!");
			return false;
		}
		
		$nr_success = 0;
		
		//links
		if (isset($res['links'])) {
			foreach ($res['links']['mdb'] as $key=>$v) {
				$params = $v;
				
				$res2 = $this->setTplModule($params);
				if (!$res2) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set TPL module failed!", $params);
					continue;
				}
				$nr_success ++;
			}
		}
		
		//modules		
		if (isset($res['modules'])) {
			foreach ($res['modules']['mdb'] as $key=>$v) {
				$params = $v;
				$res3 = $this->setTplModule($params);
				if (!$res3) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set TPL module failed!", $params);
					continue;
				}
				$nr_success ++;
			}
		}
		
		return true;
	}
	
	
	protected function setTplVarOne($dirname, $_params, $options)
	{
		$m1 = Factory::GetModel('templatevar');
		
		//src
		$oldsrc = isset($_params['src'])?$_params['src']:'';
		if ($oldsrc && !is_url($oldsrc) && !is_start_slash($oldsrc)) {
			$src = $options['_theroot'].'/'.$dirname.'/'.$oldsrc;
			$_params['src'] = $src;
			//src
			$_params['content'] = str_replace($oldsrc, $src, $_params['content'] );				
		}
		
		$_params['content'] = htmlspecialchars($_params['content']);
		//vid
		$res = $m1->getOne(array('vid'=>$_params['vid']));
		if ($res) {
			$_params['id'] = $res['id'];
		}
		
		//$_params['value'] = $v['text'];
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $_params);
		$res = $m1->set($_params);
		
		
		$_vid = $_params['id'];
		
		
		$tid = $_params['tid'];
		
		//处理页面引用
		$m2 = Factory::GetModel('template2var');
		
		$_params = array();
		$_params['tid'] = $tid;
		$_params['vid'] = $_vid;
		$res = $m2->set($_params);
				
		//处理多语种
		if (isset($v['i18n'])) {
			$m3 = Factory::GetModel('templatevar2i18n');
			foreach ($v['i18n'] as $k2=>$v2) {
				$_params2 = $v2;
				$_params2['lang'] = $k2;
				$_params2['vid'] = $_vid;
				$res = $m3->set($_params2);
			}
		}
		
		
		return true;
	}
	
	protected function setTplVarForSiteConfig($scfvarname, $params)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $scfvarname, $params);
		return true;
	}
	
	protected function setTplSiteConfigVar($dirname, $name, $lang, $scfvardb, &$allscfvardb, $options)
	{
		//$scfvardb
		
		$_scfvardb = array();
		
		foreach ($scfvardb as $key=>$v) {
			$val = $v['val'];
			if (is_start_with($val, 'img/')) { //fixed img
				$val = $options['_theroot'].'/'.$dirname.'/'.$val;						
			}
			
			//键值对varname[] 数组合并
			$pos = strpos($key, '[');
			if ($pos !== false) {
				$key = substr($key, 0, $pos);
				
				if (isset($_scfvardb[$key]))
					$item = explode(',', $_scfvardb[$key]['val']);
				else 
					$item = array();
					
				$item[] = $val;
				
				$val = implode(',', $item);				
			} 
			
			$v['val'] = $val;
			
			$_scfvardb[$key] = $v;
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $_scfvardb);
		
		$allscfvardb = array_merge($allscfvardb, $_scfvardb);
		
	}
	
	
	protected function setTplVar($dirname, $name, $lang, $vardb, $options)
	{
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $vardb);
				
		$res = false;
		$m = Factory::GetModel('template');	
		//dirname
		$dirinfo = $m->getOne(array('name'=>$dirname, 'pid'=>0));
		if (!$dirinfo) {
			return false;
		}
		
		$dir_tid = $dirinfo['id'];
		
		$tinfo = $m->getOne(array('name'=>$name, 'pid'=>$dir_tid));
		if (!$tinfo) {
			return false;
		}
		$tid = $tinfo['id'];
		
		foreach ($vardb as $key=>$v) {
			
			$varname = $v['name'];
			
			// __scf_<SITE_CONFIG_FIELD_NAME>";
			$prefix = strtolower(substr($varname, 0, 7));
			switch ($prefix) {
				case "__scf_"://变量
					$res = $this->setTplVarForSiteConfig(substr($varname, 7), $v);
				default:
					$_params = $v;
					$_params['dirname'] = $dirname;
					$_params['tplname'] = $name;
					$_params['i18n'] = $lang;
					
					$_params['did'] = $dir_tid;
					$_params['tid'] = $tid;
								
					$res = $this->setTplVarOne($dirname, $_params, $options);
					break;
				
			}
		}
		
		return $res;
	}
		
	
	protected function setTplFile($type, $name, $filename, $dirname, $tinfo, &$scfvardb=array(), $options=array())
	{
		$tplcfg = $this->getTplcfg($dirname);
		
		$itemkey = $name.'.html';
		if (!isset($tplcfg[$itemkey])) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "skip NOT tpl '$filename'!");
			return true;
		}
		
		
		//支持的语种列表
		$sitelanguage = get_i18n('sel_sitelanguage');
		
		//属模板文件	
			
		//tplcfg
		$tplcfginfo = $tplcfg[$itemkey];
		$is_default = true;
		
		//lang
		$lang = 'zh_CN';
		if (($pos = strrpos($name, '-')) != false) {
			$endname = substr($name, $pos+1);
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $endname, $sitelanguage);
			if (isset($sitelanguage[$endname])) {
				$lang = $endname;
				//名称去掉语种后缀
				$name = substr($name, 0, $pos);	
				$is_default = false;				
			}				
		}
		
		//默认语种入库可配置
		if ($is_default) {
			$title = $tplcfginfo['title'];
			$description = $tplcfginfo['description'];
			
			//template file
			$params = array();
			$params['name'] = $dirname.'/'.$name;
			$params['title'] = $title;
			$params['description'] = $description;
			$params['filename'] = $filename;
			$params['pid'] = $tinfo['id'];
			$params['isdir'] = 0;
			$params['type'] = $type;			
			
			$res = $this->getOne(array('filename'=>$filename, 'pid'=>$tinfo['id']));
			if ($res) {
				$params['id'] = $res['id'];
			}
			$res = $this->set($params);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set template failed!", $params);
				return false;
			}
		}
		
		$m = Factory::GetModel('template2i18n');
		$i18ndb = isset($tplcfginfo['i18n'])?$tplcfginfo['i18n']:array();
		foreach ($i18ndb as $key=>$v) {
			$_params = $v;
			$_params['dirname'] = $dirname;
			$_params['tplname'] = $name;
			$_params['i18n'] = $lang;
			$_params['content'] = htmlspecialchars($_params['content']);
			//$_params['value'] = $v['text'];
			
			$m->set($_params);
		}
		
		//站点变量
		if (isset($tplcfginfo['scfvardb']))
			$this->setTplSiteConfigVar($dirname, $name, $lang, $tplcfginfo['scfvardb'], $scfvardb, $options);
		
		//变量
		if (isset($tplcfginfo['vardb']))
			$this->setTplVar($dirname, $name, $lang, $tplcfginfo['vardb'], $options);
		
		
									
		return true;
			
	}
	
	
	protected function initTplTypes($tplname, $catalogdb, &$tpltypesdb=array())
	{
		$contentdb = get_cache_array('contentdb', RPATH_TEMPLATES.DS.$tplname.DS.'contentdb.php');
		if (!$contentdb) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'no contentdb failed!');
			return false;
		}
		
		foreach ($contentdb as $key=>$v) {
			$tplfilename = s_filename2name($v['href']);
			$tpltypesdb[$tplfilename] = TPL_TYPE_CONTENT;
			
			$ttype = intval($v['ttype']);
			if (($ttype & TPL_TYPE_CONTENT) == TPL_TYPE_CONTENT) {
				$tpltypesdb[$tplfilename] |= TPL_TYPE_CONTENT;
			}
		}	
		
		//check catatlog, eg: 'href'=>'list.html',
		foreach ($catalogdb as $key=>$v) {
			$tplfilename = s_filename2name($v['href']);
			
			if (isset($tpltypesdb[$tplfilename]))			
				$tpltypesdb[$tplfilename] |= TPL_TYPE_CATALOG;
			else 
			
				
			$ttype = intval($v['ttype']);
			if (($ttype & TPL_TYPE_CATALOG) == TPL_TYPE_CATALOG) {
				$tpltypesdb[$tplfilename] |= TPL_TYPE_CATALOG;
			}
		}	
		return true;		
	}
	
				
	protected function doInstall($tinfo, &$options=array())
	{
		$dirname = $tinfo['appname'];
		
		$appcfg = $this->getcfg($dirname);
		$tplcfg = $this->getTplcfg($dirname);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $tinfo, $tcfg);
		
		$tinfo['tcfg'] = $appcfg;
		$tpldir = RPATH_TEMPLATES.DS.$dirname;
		if (!is_dir($tpldir)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no TPL dir '$tpldir'!");
			return false;
		}
		$tinfo['tpldir'] = $tpldir;
		
		//入表
		$params = array();
		$params['name'] = $dirname;
		$params['title'] = $appcfg['title'];
		$params['description'] = $tinfo['description'];
		$params['filename'] = $dirname;
		$params['pid'] = 0;
		$params['isdir'] = 1;
		
		$res = $this->getOne(array('filename'=>$dirname, 'pid'=>0));
		if ($res) {
			$params['id'] = $res['id'];
		}
				
		$res = $this->set($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set template failed!", $params);
			return false;
		}		
		$tinfo['id'] = $params['id'];
		
		//安装CATALOG
		$cdb = array();
		$this->initCatalog($appcfg, $tplcfg, $dirname, $options, $cdb);
		
		$this->initTplTypes($dirname, $cdb, $tpltypes);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $cdb, $tpltypes);exit;
		
		
		$udb = s_readdir($tpldir, "files");		
		$res = false;
		$nr_total = 0;	
		$nr_errors = 0;	
		$nr_success = 0;	
		
		$hdb = array('.svn');
		
		$scfvardb = array();				
		
		foreach ($udb as $key=>$v) {
			$filename = $v;
			if (in_array($filename, $hdb))
				continue;				
			$tplfile = $tpldir.DS.$filename;
			if (is_dir($tplfile))
				continue;
				
			$name = $filename;			
			$extname = s_extname($name);
			if ($extname != 'htm')
				continue;
			
			//类型
			$type = 0;
			if (isset($tpltypes[$name]))
				$type |= $tpltypes[$name];
			
			$res = $this->setTplFile($type, $name, $v, $dirname, $tinfo, $scfvardb, $options);
			if (!$res) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: call setTplFile failed!");
			}
			
			$nr_total ++;				
			$res2 = $this->parseTemplateFile($tplfile, $tinfo, $options);
			if (!$res2) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "parse template file '$tplfile' failed!");
				$nr_errors ++;
				continue;
			}
			
			$nr_success ++;
		}
		
		//安装CONTENT
		$this->initContent($appcfg, $tplcfg, $dirname, $options, $cdb);
		
		//val
		$m = Factory::GetModel('site_config');
		$scf = $m->getParams();	
		
		//scf var
		foreach ($scfvardb as $key=>$v) {
			$scf[$key] = $v['val'];
		}
		
		//title
		if ($scf['title'] != $appcfg['title'] && $scf['template'] != $dirname) {
			$scf['title'] = $appcfg['title'];
		}
		//template
		$scf['template'] = $dirname;		
		$m->set($scf);
		
		//SCF变量
		$scfvarfile = RPATH_CONFIG.DS.'scfvar.php';
		cache_array('scfvar', $scfvardb, $scfvarfile);	
		
		//cache
		$m = Factory::GetModel('catalog');
		$m->cache($options);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$nr_success='.$nr_success,'$nr_errors='.$nr_errors);
		return true;
	}
	
	public function install($tinfo, &$options=array())
	{
		$res = $this->doInstall($tinfo, $options);
		
		return $res;
	}
	
	
	public function setup($id, &$options=array())
	{
		$res = $this->get($id);
		if (!$res) {
			return false;
		}
		
		$tinfo = $res;
		$tinfo['appname'] = $tinfo['name'];
		$res = $this->doInstall($tinfo, $options);
		
		return $res;
	}
	
	public function get_tpls($key=null)
	{
		$params = array(/*'status'=>1,*/'pid'=>0);
		$udb = $this->selectForView($params);
		return $udb;
	}
	
	
	public function preview($name)
	{
		$tinfo = $this->getOne(array('name'=>$name));
		
		if (!empty($tinfo['preview'])) {
			exit($tinfo['preview']);
		}
			
		$imgtypes = array('png', 'jpg', 'gif');
		foreach ($imgtypes as $key=>$v) {
			$file = RPATH_TEMPLATES.DS.$name.DS.'preview.'.$v;
			if (file_exists($file)) {
				header("Content-Type: image/".$v);
				$res = readfile($file);
				break;
			}
		}
		exit;
	}
		
	public function get_tpl_select($tpl)
	{
		$res = "";
		$defaultTitle = i18n('Default');
		$res .= "<option value='default'>$defaultTitle</option>";
		$udb = $this->get_tpls();
		foreach ($udb as $key=>$v) {
			$name = $v['name'];
			$selected = $name == $tpl ? 'selected': '';
			$res .= "<option value='$name' $selected>$v[title]</option>";
		}		
		return $res;
	}
		
	
	public function get_child_template_select($child, $root, $tpl, $permit_select_index=false)
	{
		!$root && $root = 'default';
		$res = "";
		$templates = $this->get_tpls();
		$template= $templates[$root];	
		if ($permit_select_index) {
			$childs = $template['index'];
			if ($childs) {
				foreach ($childs as $key=>$v) {
					$selected = $key == $tpl ? 'selected': '';
					$res .= "<option value='$key' $selected>$v</option>";
				}
			}
		}
		
		$childs = $template[$child];
		if ($childs) {
			foreach ($childs as $key=>$v) {
				$selected = $key == $tpl ? 'selected': '';
				$res .= "<option value='$key' $selected>$v</option>";
			}
		}		
		return $res;
	}
	
	
	protected function unstallCatalog($tinfo)
	{
		$name = $tinfo['name'];
		$catalogdb = get_cache_array('catalogdb', RPATH_TEMPLATES.DS.$name.DS.'catalogdb.php');
		if (!$catalogdb) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'no catalogdb failed!');
			return false;
		}
		
		$res = true;
		foreach ($catalogdb as $key=>$v) {
			$m = Factory::GetModel($v['_modname']);			
			$name = $v['name'];
			$cataloginfo = $m->getOne(array('name'=>$name));
			if ($cataloginfo) {
				$res = $m->del($cataloginfo['id']);
			}
		}

		return $res;
	}
	
	protected function unstallContent($tinfo)
	{
		//	
		$name = $tinfo['name'];
		$res = true;
		$contentdb = get_cache_array('contentdb', RPATH_TEMPLATES.DS.$name.DS.'contentdb.php');
		if (!$contentdb) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, 'no contentdb failed!name=$name');
			return false;
		}	
		
		$m = Factory::GetModel('content');
		foreach ($contentdb as $key=>$v) {
			$name = $v['name'];
			$cinfo = $m->getOne(array('name'=>$name));
			if ($cinfo) {
				$res = $m->del($cinfo['id']);
			} else {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no name '#$name#'!");
			}
		}
		
		return $res;
	}
	
	
	protected function doUninstall($tinfo, $dropall=false)
	{
		$res = true;
		
		$name = $tinfo['appname'];
						
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN ...");		
		$res = $this->getOne(array('filename'=>$name, 'pid'=>0));
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no name '$name'!");
			return true;
		}
		
		//子模板文件
		$id = $res['id'];
		//$res1 = $this->delete(array('pid' => $id));
		
		//clean template2i18n
		//$m = Factory::GetModel('template2i18n');
		//$m->delete(array('dirname'=>$res['name']));
		
		//clean catalog and content
		if ($dropall) {
			$this->unstallContent($tinfo);
			$this->unstallCatalog($tinfo);			
		}
		
		$res2 = $this->del($id);
		
		return $res1 || $res2;
	}
	
	public function uninstall($tinfo, $dropall=false)
	{
		$res = $this->doUninstall($tinfo, $dropall);
		return $res;
	}

	
	
	
	
	protected function initI18nMap($dirname, $lang)
	{
		$m = Factory::GetModel('template2i18n');
		$udb = $m->gets(array('dirname'=>$dirname));
		
		
		//所有中文字串
		$def_i18ndb = array();
		foreach ($udb as $key=>$v) {
			if ($v['i18n'] == 'zh_CN') {
				$def_i18ndb[$v['name']] = $v;
			}
		}
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $def_i18ndb);
		
		//中文转换为各语言表
		$idb = array();
		foreach ($def_i18ndb as $key=>$v) {
			$value = $v['value'];
			foreach($udb as $k3=>$v3) {
				if ($v3['name'] == $v['name'] && $v3['i18n'] == $lang) {
					$value = $v3['value'];
				}
			}			
			$idb[$key] = $value;
		}	
		
		
		$this->_i18nmap[$dirname][$lang] = $idb;
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $this->_i18nmap);	
		
		return $idb;
	}
	
	
	protected function getI18nMap($dirname, $toLang)
	{
		if (!is_array($this->_i18nmap[$dirname]))
			$this->_i18nmap[$dirname] = array();
		if (!isset($this->_i18nmap[$dirname][$toLang]))
			$this->initI18nMap($dirname, $toLang);
		
		return $this->_i18nmap[$dirname][$toLang];
	}
	
	
	
	public function i18n($iid, $text, $toLang)
	{
		$m = Factory::GetModel('template2i18n');
		$res = $m->getOne(array('iid'=>$iid));
		if (!$res)
			return $text;
		
		$name = $res['name'];
		$dirname = $res['dirname'];
		
		$i18nmap = $this->getI18nMap($dirname, $toLang);
		
		
		$text = isset($i18nmap[$name])?$i18nmap[$name]:$text;
		
		return $text;
		
	}
	
	
	
	protected function delTemplateVar($tid)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN... tid '$tid'!");
		$m = Factory::GetModel('templatevar');
		$udb = $m->gets(array('tid'=>$tid));		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN2... ", $udb);
		
		foreach ($udb as $key=>$v) {
			$m->del($v['id']);
		}
		
		
		$m2 = Factory::GetModel('template2var');		
		$m2->delete(array('tid'=>$tid));
		
	}
	
	protected function delTemplate($old)
	{
		$id = $old['id'];
		$pid = $old['pid'];
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN1... pid=$pid", $old);
		
		if ($pid == 0) {//删除目录
			$udb =  $this->gets(array('pid'=>$id));
			
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN3... ", $udb);
			
			foreach ($udb as $key=>$v) {
				$this->del($v['id']);
			}
		} 
		
		$this->delTemplateVar($id);		
	}
	
	public function del($id, &$options=array())
	{
		$res = parent::del($id, $options);
		if ($res) {
			$this->delTemplate($res);			
		}
		return $res;
	}
	
	
	public function onoffStatus($id, $name, $new, $options=array())
	{
		$this->cache($options);
		
		return true;
	}
}
