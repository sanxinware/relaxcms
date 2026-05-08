<?php
/**
 * @file
 *
 * @brief 
 * 导航
 *
 * Copyright (c), 2025, relaxcms.com
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


define ('CTF_CHECKED',	0x1);
define ('CTF_RELEASE',	0x2);
define ('CTF_NAV',		0x4);
define ('CTF_EMPTY',	0x8);
define ('CTF_HOME',		0x10);

//flags
define ('NAV_FLAGS_MAINMENU',		0x1);
define ('NAV_FLAGS_TOPMENU',		0x2);
define ('NAV_FLAGS_SITEMAP',		0x4);
define ('NAV_FLAGS_LEFTMENU',		0x8);
define ('NAV_FLAGS_BOTTOMMENU',		0x10);
define ('NAV_FLAGS_BOTTOMNAV',		0x20);


//type
define ('NAV_TYPE_DEFAULT',			0);
define ('NAV_TYPE_LINKMODEL',		1);
define ('NAV_TYPE_LINKMODELTREE',	2);
define ('NAV_TYPE_LINK',			3);


//sytle
define ('NAV_STYLE_DEFAULT',		0);
define ('NAV_STYLE_DROPDOWN',		1);
define ('NAV_STYLE_DROPDOWNFULL',	2);
define ('NAV_STYLE_DROPDOWNDTAB',	3);
define ('NAV_STYLE_MENU',			4);


class CNavModel extends CModel
{
	protected $_navdb = array();
	protected $_position;
	protected $_cachefile = 'nav.php';
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
		$this->_default_sort_field_mode = 'asc';		
		$this->_cachefile = RPATH_CACHE.DS.'nav.php';
	}
		
	public function CNavModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}

	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			case 'style':
			case 'type':
				$f['input_type'] = 'selector';
				break;			
			case 'description':
				$f['show'] = false;
				break;			
			case 'photo':
				$f['input_type'] = 'image';
				$f['show'] = false;
				break;
			case 'pid':
				$f['input_type'] = 'treemodel';
				$f['show'] = false;		
				break;
			case 'modname':
				$f['input_type'] = 'selector';
				$f['onchange'] = true;				
				break;
			case 'mid':
				$f['input_type'] = 'treemodel';
				$f['dynmodname'] = 'modname';
				$f['default'] = true;				
				break;
			case 'cuid':
				$f['readonly']= true;
			case 'uid':
				$f['input_type']="UID";
				$f['show'] = false;
				$f['edit'] = false;
				break;
			case 'ctime':
				$f['readonly']= true;
			case 'ts':
				$f['input_type'] = "TIMESTAMP";
				$f['show'] = false;
				$f['edit'] = false;
				break;
			case 'oid':				
			case 'depth':
			case 'cached':
				$f['edit'] = false;		
				
				$f['show'] = false;		
				break;
			case 'status':
				$f['input_type'] = 'ONOFF';
				$f['edit'] = false;		
				break;
			case 'flags':
				$f['input_type'] = 'varmulticheckbox';
				break;			
			case 'taxis':
				$f['input_type'] = 'sort';
				break;			
			
			default:
				break;
		}
		return true;
	}
	
	
	protected function buildInputSelectorForQueryResult($name, $params, &$options=array())
	{
		//$("#mySelect").empty().append('<option value="1">新的选项1</option><option value="2">新的选项2</option><option value="3">新的选项3</option>');
		
		$modname = $params[$name];
		
		
		//values
		$res = "
					if (!_.isUndefined(res.data.options)) {
				$('#param_mid').empty().append(res.data.options);
					}
					";	
		
		return $res;
	}
	
	public function queryForInputModname($name, $oldval, $params=array(), &$options=array())
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "TODO..., name=$name, oldval=$oldval", $params);
		//$res = array('value'=>$tdb[$pos]);
		$modname = $params[$name];
		
		$default_title = i18n('Default');
		$allOptions = "<option value='0'>$default_title</option>";
		
		$_udb = array();
		if ($modname) {
			$m = Factory::GetModel($modname);
			$udb = $m->gets();
			
			$pid = 0;			
			$default_value = 0;
			$depth= 0;
			
			$treeOptions = '';
			
			$m->treeOption($depth, $treeOptions, $udb, $default_value, $pid);
			
			$allOptions .= $treeOptions;
			
		} 
		
		$_udb['options'] = $allOptions;
		return $_udb;
	}
		
	protected function getSelectorDataForModname($params, $field, &$options=array())
	{
		$mdb = Factory::getModInfoList();
		
		
		$ddb = array(' '=> i18n('Default'));
		foreach ($mdb as $key=>$v) {
			if ($v['type'] == 1) {
				$ddb[$key] = $v['title'];
			}
		}
		
		return $ddb;
	}
	protected function getSelectorData($params, $field, &$options=array())
	{
		$name = $field['name'];
		
		if ($name == 'modname') {
			$ddb = $this->getSelectorDataForModname($params, $field, $options);
		} else {
			$ddb = parent::getSelectorData($params, $field, $options);		
		}
		
		return $ddb;
	}
		
	
	public function formatForView(&$row, &$options = array())
	{
		parent::formatForView($row, $options);
		
		//$row['_taxis'] = "<input type='text' name='params[taxis][$row[id]]' value='$row[taxis]' class='form-control input-xsmall' />";
		
		$id = $row['id'];
		
		//status
		//$row['status'] = $this->formatLabelColorForView($row['_status'], $row['status']);
				
		//fixed photo
		$photo = $row['photo'];
		if (!$photo) {
			$row['_photo'] = $options['_dstroot'].'/img/nopic.jpg';
		} else {
			$row['_photo'] = $photo;
		}
		
		$photoUrl = $row['_photo'];
		if (is_url($photoUrl)) {
			$row['photoUrl'] = $photoUrl;
		} else {
			$row['photoUrl'] = $options['_rooturl'].s_hslash($photoUrl);
		}
		//for listview
		$row['previewUrl'] = $row['photoUrl'];
		
		//url 本页菜单
		$path = !empty($options['_webroot'])?$options['_webroot']:'/';
		if (!empty($row['link']) && (is_url($row['link']) || is_start_with($row['link'], '#')|| is_start_with($row['link'], $path))) {
			$url = $row['link'];
		} else { 
			$m = Factory::GetModel('site_config');
			$scf = $m->getParams();
			if ($scf['htmlpub'] == 1) {
				$url = $options['_webroot'].'/'.$scf['shtml_uri_base'].'/'.$row['uuid']."/".$scf['index_shtml_name'];
			} else {
				$url = $options['base'].'/list/'.$row['uuid'];//$options['_webroot'].'/'.$scf['index_script_name']."?c=list&id=".$row['id'];
			}
		}
		
		/*if ($row['flags'] & CTF_EMPTY)	{		
			$url = '#';
		}*/
		
		
		//$url = is_url($row['link'])?$row['link']:((is_start_with($row['link'], '#'))?$row['link']:$options['_webroot']."/list/$id");
		$row['url'] = $url; //($row['flags'] & CTF_NAV)?$url:$options['_basename'].'#param_catalog_'.$id;	
		
		//fixed title
		$row['title'] = $row['name'];
		
		
		//modname, mid		
			
	}
	
	
	public function getActions($row=array(), &$options=array())
	{
		$dlg = $options['dlg'];
		$id = $row[$this->_pkey];
		
		$defOpt = parent::getActions($row, $options);		
		$item = array(
				'name'=>'add',
				'action'=>'alink',
				'title'=>'添加子目录',
				'icon'=>'fa fa-plus-square',
				'url'=>$options['_base'].'/add?dlg='.$dlg.'&id='.$id,
				);
		$defOpt[] = $item;
		
		return $defOpt;		
	}
	
	
	protected function fetchModelOne($navinfo, &$_navdb, $options=array())
	{
		$modname = $navinfo['modname'];
		$mid = $navinfo['mid'];
		if (!$modname) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no modname '$modname'!");
			return false;
		}
		
		
		$m = Factory::GetModel($modname);
		
		$info = $m->getForView($mid, $options);
		if (!$info) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no mid '$mid'!");
			return false;
		}
		
		$uuid = $info['uuid'];
		$_navdb[$uuid] = $info;
		
		return true;
		
	}
	
	protected function fetchModelTree(&$navinfo, &$_navdb, $options=array())
	{
		$modname = $navinfo['modname'];
		$mid = $navinfo['mid'];
		if (!$modname) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no modname '$modname'!");
			return false;
		}
		
		$linkUUID = $navinfo['uuid'];
		
		$m = Factory::GetModel($modname);		
		$linkinfo = $m->get($mid);
		if ($linkinfo) {
			$linkUUID = $linkinfo['uuid'];
			$navinfo['uuid'] = $linkUUID;
		}
		
		$pid = $linkinfo?$linkinfo['id']:0;	
		if ($mid > 0) {	
			$udb = $m->getAllChildren($mid);
		} else {
			$udb = $m->gets();
		}
		
		if (!$udb) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no children for mid '$mid'!");
			return false;
		}
		
		$linktreedb = array();
		foreach ($udb as $key=>$v) {
			$linktreedb[$v['id']] = $v;			
		}
		
		foreach ($linktreedb as $key=>$v) {
			$uuid = $v['uuid'];
			
			$puuid = $v['pid'] == $pid?$navinfo['uuid']:$linktreedb[$v['pid']]['uuid'];			
			$v['puuid'] = $puuid;
			
			//status
			$v['status'] = $navinfo['status'];
			$v['modname'] = $navinfo['modname'];
			$v['mid'] = $v['id'];
			
			$_navdb[$uuid] = $v;
		}
		
		
		return $linkUUID;
	}
	
	public function cache($options=array())
	{
		$udb = $this->gets();
		
		$navdb = array();
		foreach ($udb as $key=>$v) {
			$navdb[$v['id']] = $v;
		}
				
		$_navdb = array();
		foreach ($navdb as $key=>$v) {
			//$this->formatForView($v, $options);
			$uuid = $v['uuid'];
			$pid = $v['pid'];
			
			$puuid = $navdb[$pid]['uuid'];
			
			$v['puuid'] = $puuid;
			
			$type = $v['type'];
			switch($type) {
				case NAV_TYPE_LINKMODEL:
					//$this->fetchLinkModel($v, $_navdb, $options);
					break;
				case NAV_TYPE_LINKMODELTREE:
					$uuid = $this->fetchModelTree($v, $_navdb, $options);
					break;
			}
			
			$_navdb[$uuid] = $v;
		}
		
		array_sort_by_field($_navdb, 'taxis');
				
		cache_array('navdb', $_navdb, $this->_cachefile);
		
		return true;
	}
	
	
	protected function setCatalogForI18n($params)
	{
		$res = true;
		
		$cid = $params['id'];
		
		$sitelanguage = get_i18n('sel_sitelanguage');
		
		$m2 = Factory::GetModel('catalog2i18n');		
		
		$i18ndb = array();
		foreach ($sitelanguage as $key => $v) {
			if ($key == 'zh_CN')//skip default
				continue;
			
			$_params = array();
			$res = $m2->getOne(array('cid'=>$cid, 'i18n'=>$key));
			if ($res) {
				$_params['id'] = $res['id'];
			}
			
			$_params['cid'] = $cid;
			$_params['i18n'] = $key;
			
			$_params['name'] = isset($params['name_'.$key])?$params['name_'.$key]:'';
			$_params['description'] = isset($params['description_'.$key])?$params['description_'.$key]:'';
			
			$res = $m2->set($_params);
		}
		
		return $res;
	}
	
	protected function checkParams(&$params, &$options=array())
	{
		$res = parent::checkParams($params, $options);
		if (!isset($params['status'])) {
			$params['status'] = 1;			
		}
		
		return $res;
	}
	
	public function set(&$params, &$options=array())
	{
		$res = parent::set($params, $options);
		if ($res) {
			$this->setCatalogForI18n($params);
			$this->cache($options);
		}
		
		return $res;
	}
	
	public function onoffStatus($id, $name, $new)
	{
		$this->cache();
	}
	
	
	public function taxis($params, $options=array())
	{
		$res = parent::taxis($params);
		if (!$res)
			return false;
			
		$this->cache($options);

		return true;
	}
	public function sortswap($id, $id2,  $field, $dir, $up, &$options=array())
	{
		$res = parent::sortswap($id, $id2, $field, $dir, $up, $options);
		if ($res) {
			$this->cache($options);
		}
		return $res;
	}
	
	public function cacheNav($options=array())
	{
		return $this->cache($options);
	}
	
	protected function initNav()
	{
		if (!file_exists($this->_cachefile)) {
			$this->cache();
		}

		$navdb = cache_array('navdb', null, $this->_cachefile);
		$this->_navdb = $navdb;
	}
	
	
	public function navdb()
	{
		if (!$this->_navdb)
			$this->initNav();

		return $this->_navdb;
	}
	
	public function get_navdb()
	{
		return $this->navdb();
	}
	
	public function getNavByUUID($uuid, &$options=array())
	{
		$navdb = $this->navdb();
		
		$row =  isset($navdb[$uuid])?$navdb[$uuid]:array();
		
		$this->formatForView($row, $options);
		
		return $row;
		
	}
	
	public function getForViewByUUID($uuid, &$options=array())
	{
		return $this->getNavByUUID($uuid, $options);
	}
	
	
	public function getFirstChildForView($uuid, &$options=array())
	{
		$navdb = $this->navdb();
		foreach ($navdb as $key=>$v) {
			if ($v['puuid'] == $uuid) {
				$this->formatForView($v, $options);
				return $v;
			}
		}
		return array();		
	}
	
	
	//树型目录栏
	protected function createNavTree($uuid='', $flags=0, $options=array())
	{
		$menu = array();
		
		$navdb = $this->navdb();
		
		foreach ($navdb as $key=>$v)
		{
			if ($v['status'] != 1)  //发布
				continue;
			
			if ($v['puuid'] != $uuid) 
				continue;
				
			if ($flags > 0 && ($v['flags'] & $flags) != $flags) {
				continue;
			}

			$this->formatForView($v, $options);
			
			$menu[$key] = $v;
		}

		foreach ($menu as $key=>&$v2) {		//submenu 不过滤flags	
			$v2['submenu'] = $this->createNavTree($v2['uuid'], 0, $options);
		}
		
		return $menu;
		
	}
	
	public function tree($uuid='', $flags=0, $options=array())
	{
		if (is_numeric($uuid)) {
			$navinfo = $this->get($uuid);
			if ($navinfo) {
				$uuid = $navinfo['uuid'];
			}
		} 
		
		$navdb = $this->navdb();
		
		//更新active
		if ($uuid) {
			$puuid = $uuid;
			while ($puuid) {
				
				$this->_navdb[$puuid]['active'] = 'active'; 
				
				$puuid = $navdb[$puuid]['puuid'];
			}
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $this->_navdb);
				
		$tree = $this->createNavTree('', $flags, $options);		
		
		return $tree;
	}
	
	
	//目录栏
	public function menu($puuid='', $tree=true, $flags=0, $options=array())
	{
		if ($tree)
			return $this->createNavTree($puuid, $flags, $options);
		
		$navdb = $this->navdb();

		$menu = array();			
		foreach ($navdb as $key=>$v)
		{
			if ($v['status'] != 1)  //发布
				continue;	
				
			if ($v['puuid'] != $puuid)
				continue;
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $v,$flags);
			if ($flags > 0 && (intval($v['flags']) & $flags) != $flags) 
				continue;
			
			$this->formatForView($v, $options);			
			$menu[$key] = $v;
		}
		return $menu;
	}
	
	
	
	public function getSiteMenu()
	{
		$menus = $this->createNavTree();
		return $menus;
	}
	
	
	
	public function _find_parent($cid, $options, &$position, $type=1)
	{
		$catalog = $this->getCatalog();
		
		$cinfo = $catalog[$cid];
		$this->formatForView($cinfo, $options);

		if ($cinfo['flags'] & CTF_EMPTY) {
			$position = " <li>  ".$cinfo['name']." </li> " . $position;
		} else {
			$curl = $cinfo['url'];
			if ($type == 1) {
				$position = " <li> <a href='".$curl."' > ".$cinfo['name']."</a>  </li> " . $position;
			} else {
				$position = "  > <a href='".$curl."' > ".$cinfo['name']."</a> " . $position;
			}
		}
		
		$pid = $cinfo['pid'];
		if ($pid)
		{
			$this->_find_parent($pid, $options, $position, $type);
		}
	}
	
	//所有子结点
	public function childs($cid, $exclude_not_search=true) 
	{
		$res = "";		
		if (!$cid)
			return false;
		
		foreach ($this->_catalog as $key=>$v) {
			if ($v['pid'] == $cid) {
				if ($exclude_not_search && !is_search($v['flags']))
					continue;
				$res = $v['cid'];	
				if ($cid = $this->childs($v['cid'], $exclude_not_search)) {
					$res .= ','.$cid;
				}			
			}
		}		
		return $res;
	}
	
	//所有当前及祖先结点
	public function parents($uuid, &$parentdb=array()) 
	{
		$res = "";		
		if (!$uuid)
			return true;
			
		$navdb = $this->navdb();
		
		$navinfo = $navdb[$uuid];
		$parentdb[] = $navinfo;
		
		return $this->parents($navinfo['puuid'], $parentdb);
	}
	
	public function getPosition($uuid, $options) 
	{
		$this->parents($uuid, $parentdb);
		
		if (!$parentdb)
			return '';
			
			
		$nr = count($parentdb);
		
		$pos = '';
		for($i=$nr-1; $i>=0; $i--) {
			$navinfo = $parentdb[$i];
			$this->formatForView($navinfo, $options);
			$url = $navinfo['url'];
			$pos .= "<li>  <a href='$url'>$navinfo[name]</a> </li>";
		}
		
		return $pos;
	}
	
	public function position($uuid, $options=array())
	{
		return $this->getPosition($uuid, $options, 0);
	}
	
	public function get_first_uuid()
	{
		$navdb = $this->navdb();
		
		foreach ($navdb as $key=>$v) {
			return $v['uuid'];
		}
		return 0;
	}
		
	
	public function getMenu($flags=NAV_FLAGS_MAINMENU, $limit=0)
	{
		$navdb = $this->navdb();
		
		$mmdb = array();
		$i=0; 
		foreach ($navdb as $key=>$v) {
			
			if ($v['status'] != 1)
				continue;
			
			$_flags = intval($v['flags']);
			if (($_flags&$flags) != $flags) {//审，发，主菜单
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "skip flags $_flags!");
				continue;		
			}
					
			$mmdb[$key] = $v;			
			
			if ($limit > 0 && ++$i>=$limit)
				break;
		}
		return $mmdb;
	}
	
	
	public function getMainMenuForView($options=array())
	{
		$mmdb = $this->getMenu();		
		foreach ($mmdb as $key=>&$v) {
			$this->formatForView($v, $options);			
		}
		return $mmdb;
	}
	
	public function mck($id, $flagsMask, $fieldname='', $options=array())
	{
		$res = parent::mck($id, $flagsMask, $fieldname);		
		if ($res) {
			$this->cache($options);
		}
		return $res;
	}


	public function createNav($params)
	{
		$_params = array();
		$_params['name'] = $params['name'];

		$res = $this->getOne($_params);
		if ($res)
			return $res['id'];
		
		$res = $this->set($params);

		return $params['id'];
	}
	
	public function getFieldsForDetail($params=array(), &$options=array())
	{
		$this->_fields[$this->_pkey]['edit'] = true;
		$fdb = parent::getFieldsForDetail($params, $options);	
		return $fdb;
	}
	
	public function del($id, &$options=array())
	{
		$old = parent::del($id, $options);
		if ($old) {
			$this->delChildren($id, $options, false);			
		}
		
		return $old;
	}
	
	
	public function syncNavByUUID($params)
	{
		$uuid = $params['uuid'];
		if (!$uuid)
			return false;
			
		$_params = array();
		$_params['name'] = $params['name'];
		$_params['description'] = $params['description'];
		$_params['uuid'] = $params['uuid'];
		
		$navinfo = $this->getOne(array('uuid'=>$uuid));
		if ($navinfo) {
			$_params['id'] = $navinfo['id'];
		}
		
		$res = $this->set($_params);
		
		return $res;
	}
}
