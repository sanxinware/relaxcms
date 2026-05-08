<?php
/**
 * @file
 *
 * @brief 
 * 目录
 *
 * Copyright (c), 2014, relaxcms.com
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


define ('CTF_CHECKED',	0x1);
define ('CTF_RELEASE',	0x2);
define ('CTF_NAV',		0x4);
define ('CTF_EMPTY',	0x8);
define ('CTF_HOME',		0x10);


//type
define ('CATALOG_TYPE_DEFAULT',			0);
define ('CATALOG_TYPE_LINKMODEL',		1);
define ('CATALOG_TYPE_LINKMODELTREE',	2);
define ('CATALOG_TYPE_LINK',			3);

//sytle
define ('CATALOG_STYLE_DEFAULT',		0);
define ('CATALOG_STYLE_DROPDOWN',		1);
define ('CATALOG_STYLE_DROPDOWNFULL',	2);
define ('CATALOG_STYLE_DROPDOWNDTAB',	3);
define ('CATALOG_STYLE_MENU',			4);


class CCatalogModel extends CPubModel
{
	protected $_catalogdb = array();
	protected $_cachefile = 'catalogdb.php';
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
		$this->_default_sort_field_mode = 'asc';
		$this->_cachefile = RPATH_CACHE.DS.'catalogdb.php';
		
	}
		
	public function CCatalogModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}

	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {				
			
			case 'description':
				$f['input_type'] = 'sneditor';
				$f['show'] = false;
				$f['height'] = 100;
				break;			
			case 'photo':
				$f['input_type'] = 'image';
				$f['show'] = false;
				break;
			case 'icon':
				$f['input_type'] = 'icon';
				$f['show'] = false;
				break;
			case 'pid':
				$f['input_type'] = 'treemodel';
				$f['show'] = false;		
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
			case 'type':				
			case 'metakeyword':				
			case 'metadescrip':				
			case 'modname':				
			case 'mid':				
			case 'link':				
			case 'target':				
			case 'style':				
			case 'class':				
			case 'tpl_list':				
			case 'tpl_content':				
			case 'tpl_list_root':				
			case 'tpl_content_root':				
			case 'class':				
			case 'viewmode':				
			case 'viewtype':				
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
				//$f['vid'] = 6;
				//$f['show'] = false;
				break;			
			case 'taxis':
				$f['input_type'] = 'sort';
				$f['edit'] = false;	
				break;			
			
			default:
				break;
		}
		return true;
	}
	protected function newID(&$params=array())
	{
		$id = parent::newID($params);
		if (!isset($params['taxis']))
			$params['taxis'] = $id;
		
		return $id;
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
				$url = $options['_webroot'].'/'.$scf['shtml_uri_base'].'/'.$row['id']."/".$scf['index_shtml_name'];
			} else {
				$url = $options['base'].'/list/'.$row['id'];//$options['_webroot'].'/'.$scf['index_script_name']."?c=list&id=".$row['id'];
			}
		}
		
		/*if ($row['flags'] & CTF_EMPTY)	{		
			$url = '#';
		}*/
		
		
		//$url = is_url($row['link'])?$row['link']:((is_start_with($row['link'], '#'))?$row['link']:$options['_webroot']."/list/$id");
		$row['url'] = $url; //($row['flags'] & CTF_NAV)?$url:$options['_basename'].'#param_catalog_'.$id;	
		
		//fixed title
		$row['title'] = $row['name'];
			
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
	
	public function cache($options=array())
	{
		$udb = $this->gets();
		array_sort_by_field($udb, 'taxis');
		
		$catalogdb = array();
		foreach ($udb as $key=>$v) {
			$catalogdb[$v['id']] = $v;
		}
		
		cache_array('catalogdb', $catalogdb, $this->_cachefile);		
		return true;
		
	}
	
	protected function initCatalog()
	{
		if (!file_exists($this->_cachefile)) {
			$this->cache();
		}

		$catalogdb = cache_array('catalogdb', null, $this->_cachefile);
		$this->_catalogdb = $catalogdb;
	}
	
	
	public function getCatalog()
	{
		if (!$this->_catalogdb)
			$this->initCatalog();
		return $this->_catalogdb;
	}
	
	public function catalogdb()
	{
		return $this->getCatalog();
	}
	
	public function get_catalogdb()
	{
		return $this->getCatalog();
	}
	
	
	public function getCatalogById($id, &$options=array())
	{
		$catalog = $this->getCatalog();
		
		$row =  isset($catalog[$id])?$catalog[$id]:array();
		
		$this->formatForView($row, $options);
		
		return $row;
		
	}
	
	public function getFirstChildForView($id, &$options=array())
	{
		$catalog = $this->getCatalog();
		foreach ($catalog as $key=>$v) {
			if ($v['pid'] == $id) {
				$this->formatForView($v, $options);
				return $v;
			}
		}
		return array();		
	}
	
	
	//树型目录栏
	protected function makeTreeCatalog($cid, $flags=0, $options=array())
	{
		$menu = array();
		
		$catalogdb = $this->getCatalog();
		
		foreach ($catalogdb as $key=>$c)
		{
			if ($c['status'] != 1)  //发布
				continue;
			
			if ($c['pid'] != $cid) 
				continue;
				
			if ($flags > 0 && ($c['flags'] & $flags) != $flags)
				continue;
				
			$this->formatForView($c, $options);
			
			$menu[$key] = $c;
		}
		
		foreach ($menu as $key=>&$v) {			
			$v['submenu'] = $this->makeTreeCatalog($v['id'], $flags, $options);
		}
		
		return $menu;
		
	}
	
	
	//目录栏
	public function menu($pid=false, $tree=true, $flags=0, $options=array())
	{
		if ($tree)
			return $this->makeTreeCatalog($pid, $flags, $options);
		
		$catalogdb = $this->getCatalog();
		
		$menu = array();			
		foreach ($catalogdb as $key=>$v)
		{
			if ($v['status'] != 1)  //发布
				continue;	
				
			if ($pid !== false && $v['pid'] != $pid)
				continue;
			
			if ($flags > 0 && (intval($v['flags']) & $flags) != $flags) 
				continue;
			
			$this->formatForView($v, $options);			
			$menu[$key] = $v;
		}
		return $menu;
	}
	
	protected function getLinkModelTreeList($cataloginfo, $options)
	{
		$m = Factory::GetModel($cataloginfo['modname']);
		$udb = $m->gets();
		$baseurl = $cataloginfo['url'].'/bug';
		$mdb = array();
		foreach ($udb as $key=>$v) {
			$m->formatForView($v, $options);
			
			$v['status'] = 1;
			$v['url'] = $baseurl."?cid=$v[id]";
			
			$mdb[] = $v;
		}		
		
		return $mdb;
		
	}
	
	
	//树型目录栏
	protected function createCatalogTree($catalogdb, $id=0, $flags=0, $options=array())
	{
		$menu = array();
		
		foreach ($catalogdb as $key=>$v)
		{
			if ($v['status'] != 1)  //发布
				continue;
			
			if ($v['pid'] != $id) 
				continue;
			
			if ($flags > 0 && ($v['flags'] & $flags) != $flags) {
				continue;
			}
			$menu[$key] = $v;
		}
		
		foreach ($menu as $key=>&$v2) {		//submenu 不过滤flags	
			if ($v2['type'] == CATALOG_TYPE_LINKMODELTREE) {
				$modcatalogdb = $this->getLinkModelTreeList($v2, $options);
				$submenu= $this->createCatalogTree($modcatalogdb, 0, 0, $options);	
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,$modcatalogdb);exit;	
			} else {
				$submenu= $this->createCatalogTree($catalogdb, $v2['id'], 0, $options);
			}
			
			$v2['submenu'] = $submenu;
		}
		
		return $menu;
		
	}
	
		
	
	public function subTree($pid=0, $activeId='', $flags=0, $options=array(), &$activCatalogInfo=array())
	{
		$catalogdb = $this->catalogdb();
		if (is_numeric($activeId)) {
			$activCatalogInfo = $catalogdb[$activeId];
			if ($activCatalogInfo) {
				$activeId = $activCatalogInfo['id'];
			} else {
				$activeId = 0;
			}
		} 
		
		
		//更新active
		if ($activeId) {
			$activePId = $activeId;
			while ($activePId) {				
				$catalogdb[$activePId]['active'] = 'active';
				$activePId = $catalogdb[$activePId]['pid'];				
			}
		}
		
		//format
		foreach ($catalogdb as $key=>&$v) {
			$this->formatForView($v, $options);			
		}
		
		$tree = $this->createCatalogTree($catalogdb, $pid, $flags, $options);		
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $tree);		
		
		return $tree;
	}
	
	public function tree($pid=0, $activeId='', $flags=0, $options=array(), &$activCatalogInfo=array())
	{
		return $this->subTree($pid, $activeId, $flags, $options, $activCatalogInfo);		
	}
	
	
	public function allTree($pid=0, $activeId='', $flags=0, $options=array(), &$activCatalogInfo=array())
	{
		return $this->subTree(0, $activeId, $flags, $options, $activCatalogInfo);		
	}
	
	//生成树型结构	
	public function tree__unused()
	{
		$this->_select_cate = '';
		$this->_tree($this->_catalog);
		return $this->_select_cate;
	}
	
	public function _tree2__unused($catedb, $cid='')
	{
		if ($catedb == null) return;
		
		foreach ($catedb as $key=>$cate)
		{
			if ($cate['link'] != '') continue;
			
			if ($cid=='' && $cate['pid'] != 0)
			{
				continue;
			}
			elseif ($cid && $cate['pid'] != $cid)
			{
				continue;
			}
			
			$add = '';
			if ($cate['depth'] == 0 && $cate['pid'] == 0)
			{
				$add = "&raquo;";
			}
			else
			{
				
				$repeatnum = ($cate['depth']-1);
				$str = "&nbsp;&nbsp;";
				for($i=0; $i<$repeatnum; $i++)
				{
					$str .= "&nbsp;&nbsp;";
				}
				$add = $str.'|--';
				//$add .= str_repeat('&nbsp;&nbsp;', $repeatnum);		
				//$add .= str_repeat('--', $repeatnum);		
			}
			
			$disabled = '';
			
			$this->_select_cate .= "<option value='$cate[cid]' $disabled>$add$cate[title]</option>";
			if(count($catedb) == 0)
			{
				return ;
			}
			$this->_tree($catedb, $cate['cid']);
		}
	}

	public function digest($sb, &$options=array(), $limit=0)
	{
		$mask = 0x1 << $sb;
		
		return $this->getDigest($sb,  $limit, $options);
	}
	
	
	
	public function getDigest($flags, $limit=0, &$options=array())
	{
		$i=0;
		$cdb = array();
		$catalog = $this->getCatalog();	
		
		foreach ($catalog as $key=>$v)
		{
			if (($flags & $v['flags']) !== $flags) 
				continue;
			
			$v['direct'] = $i%2 == 0?"left":"right";
			$v['i'] = $i++;

			//fixed for view
			$this->formatForView($v, $options);

			$cdb[$key] = $v;			

			if ($limit > 0 && $i >= $limit)
				break;
		}
		
		return $cdb;
	}


	public function getSubCatalog($pid, $limit, &$options=array())
	{
		$i=0;
		$arr = array();

		foreach ($this->_catalog as $key=>$v)
		{
			if ($v['pid'] != $pid) continue;
			
			$v['direct'] = $i%2 == 0?"left":"right";
			$v['i'] = $i++;

			//fixed photo
			$photo = $v['photo'];
			if (!$photo) {
				$v['photo'] = $options['_dstroot'].'/img/nopic.jpg';
			}

			$photoUrl = $v['photo'];
			if (is_url($photoUrl)) {
				$v['photoUrl'] = $photoUrl;
			} else {
				$v['photoUrl'] = $options['_rooturl'].s_hslash($photoUrl);
			}

			//url
			$v['url'] = $options['_webroot'].'/list/'.$v['id'];

			$arr[] = $v;			

			if ($i >= $limit)
				break;
		}
		
		return $arr;
	}
	
		
	//位置
	public function position($cid, $options=array(), $type=1)
	{
		$position = "";
		if ($cid)
			$this->_find_parent($cid, $options, $position, $type);
		
		return $position;
	}	
	
	
	public function nav($cid, $options=array())
	{
		return $this->getPosition($cid, $options, 0);
	}
	
	public function _find_parent($cid, $options, &$position, $type=1)
	{
		$catalog = $this->getCatalog();
		
		$cinfo = $catalog[$cid];
		if (!$cinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING : UNKNOWN cid '$cid'!");
		}
		
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
	public function parents($cid, &$parentdb=array()) 
	{
		$res = "";		
		if (!$cid)
			return true;
		$catalogdb = $this->getCatalog();
		
		$cataloginfo = $catalogdb[$cid];
		$parentdb[] = $cataloginfo;
		
		$cid = $cataloginfo['pid'];		
		return $this->parents($cid, $parentdb);
	}
	
	public function getPosition($cid, $options) 
	{
		$this->parents($cid, $parentdb);
		
		if (!$parentdb)
			return '';
			
			
		$nr = count($parentdb);
		
		$pos = '';
		for($i=$nr-1; $i>=0; $i--) {
			$cataloginfo = $parentdb[$i];
			$pos .= "<i class='fa fa-angle-right'></i> <a href='#'>$cataloginfo[name]</a> ";
		}
		
		return $pos;
	}
	
	
	public function get_first_cid()
	{
		$catalogdb = $this->getCatalog();
		
		foreach ($catalogdb as $key=>$v) {
			return $v['id'];
		}
		return 0;
	}
	
	
	public function get_catalog()
	{
		$file = RPATH_CACHE.DS."catalog.php";
		if (file_exists($file)) {
			require $file;
			return $catalog;
		}				
		return array();
	}


	public function getSiteMenu()
	{
		$menus = $this->makeTreeCatalog(0);
		return $menus;
	}

	public function getMainmenu($flags=4, $limit=0)
	{
		$flags |= 3;
		
		$catalog = $this->getCatalog();
		
		$mmdb = array();
		$i=0; 
		foreach ($catalog as $key=>$v) {
			
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
		$mmdb = $this->getMainmenu();		
		foreach ($mmdb as $key=>&$v) {
			$this->formatForView($v, $options);			
		}
		return $mmdb;
	}
	
	

	public function mck($id, $flagsMask, $fieldname='', $options=array())
	{
		$res = parent::mck($id, $flagsMask, $fieldname, $options);		
		if ($res) {
			$this->cache();
		}
		return $res;
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
	
	public function set(&$params, &$options=array())
	{
		$res = parent::set($params, $options);
		if ($res) {
			$this->setCatalogForI18n($params);
			$this->cache($options);
		}
		
		return $res;
	}
	
	public function clickedit($id, $name, $value, $options=array())
	{
		$res = parent::clickedit($id, $name, $value, $options);
		if ($res) {
			$this->cache($options);
		}
		return $res;
	}
	
	public function taxis($params)
	{
		$res = parent::taxis($params);
		if (!$res)
			return false;
		
		$this->cache($this->_name, 'order by taxis asc');
		
		return true;
	}
	

	public function createCatalog($params)
	{
		$_params = array();
		$_params['name'] = $params['name'];

		$res = $this->getOne($_params);
		if ($res)
			return $res['id'];
		
		$res = $this->set($params);

		return $params['id'];
	}
	
	public function getFieldsForI18n($id, &$options=array())
	{
		$scf = get_site_config();
		if (!$scf['i18n_enable']) {
			return array();
		}
		$params = $this->get($id);
		$sitelanguage = get_i18n('sel_sitelanguage');

		$m2 = Factory::GetModel('catalog2i18n');

		$field_name = $this->_fields['name'];
		$field_desc = $this->_fields['description'];

		$i18ndb = array();
		foreach ($sitelanguage as $key => $v) {
			if ($key == 'zh_CN')//skip default
				continue;

			$item = array();
			$item['langname'] = $key;
			$item['langtitle'] = $v;

			$name = $params['name'];
			$description = $params['description'];
			$res = $m2->getOne(array('cid'=>$id, 'i18n'=>$key));
			if ($res) {
				$name = $res['name'];
				$description = $res['description'];
			}


			$item['name'] = $name;
			$item['description'] = $description;
			
			//edit
			$_name = 'name_'.$key;
			$field_name['name'] = $_name;
			$item[$_name] = $name;
			$item['name_input'] = $this->buildInput($field_name, $item, $options);

			$_name = 'description_'.$key;
			$field_desc['name'] = $_name;
			$item[$_name] = $description;
			$item['description_input'] = $this->buildInput($field_desc, $item, $options);


			$i18ndb[$key] = $item;
		}

		return $i18ndb;
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
			$this->cache();
		}
		
		return $old;
	}
	
	
	public function syncToCatalogByUUID($params)
	{
		$uuid = $params['uuid'];
		if (!$uuid)
			return false;
			
		$_params = array();
		$_params['name'] = $params['name'];
		$_params['description'] = $params['description'];
		$_params['uuid'] = $params['uuid'];
		
		$cataloginfo = $this->getOne(array('uuid'=>$uuid));
		if ($cataloginfo) {
			$_params['id'] = $cataloginfo['id'];
		}
		
		$res = $this->set($_params);
		
		return $res;
	}
	
	public function onoffStatus($id, $name, $new, $options=array())
	{
		$this->cache($options);
		
		return true;
	}
}
