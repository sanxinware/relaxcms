<?php

class CDTComponent extends CFileComponent
{
	protected $_default_vmask = 5;
	protected $_default_viewtype = 4;
	
		
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CDTComponent($name, $options)
	{
		$this->__construct($name, $options);
	}	
	
	protected function init(&$options=array())
	{
		parent::init($options);
		$options['_dlg'] = $options['dlg'] = $this->requestInt('dlg');
	}
	
	protected function enableMenuItem($midb, $enable=true)
	{
		$m = $this->getModel();
		$m->enableMenuItem($midb, $enable);
	}
	
	
	protected function disableMenuItemAll()
	{
		$m = $this->getModel();
		$m->disableMenuItemAll();
	}
	
	protected function setMenuItem($name, $key, $val)
	{
		$m = $this->getModel();
		$m->setMenuItem($name, $key, $val);
	}
	
	protected function addMenuItem($item)
	{
		$m = $this->getModel();
		$m->addMenuItem($item);
	}
	
	protected function getTools($activetminame, $options)
	{
		//$tmidb = $this->_tmi_tools;
		$m = $this->getModel();
		$tmidb = $m->getActionsForTools($activetminame, $options);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $tmidb);
		
		$_tools = array();
		foreach ($tmidb as $key => $v) {
			if (!$v['enable'])
				continue;
			
			//check privilege
			if (!hasPrivilegeOf($this->_name, $v['name']))
				continue;
			
			$item = $v;
			
			//title				
			$item['title'] = i18n($item['title']);
			
			if (!isset($item['class'])) {
				$item['class'] = 'btn-primary';
			}
			if (!isset($item['msg'])) {
				$item['msg'] = '';
			}
			if (!isset($item['sort'])) {
				$item['sort'] = 255;
			}
			
			$_tools[$key] = $item;
		}
		
		array_sort_by_field($_tools, "sort", false);
		
		return $_tools;
	}
	
		
	protected function initTools($activetminame, $options = array())
	{
		$_tools = $this->getTools($activetminame, $options);
		
		$this->assign('toolmenuitems', $_tools);
		if (isset($_tools[$activetminame]))
			$this->assign('activetmi', $_tools[$activetminame]);
			
		$hasAdd = isset($_tools['add'])?true:false;		
		
		$this->assign('hasAdd', $hasAdd);
		
	}
	
	protected $_treeview = false;
	protected $_cardview = false;
	protected function enableCardView($enable=true)
	{
		$this->_cardview = $enable;
	}
	
	protected function initParamsForShow(&$params, &$options=array())
	{
		return true;
	}
	

	
	
	protected function show(&$options=array())
	{
		$this->getParams($params);
		$this->initParamsForShow($params, $options);
		
		parent::show($options);
		
		$m = $this->getModel();
		$modinfo = $m->getModelInfo();
				
		$fdb = $m->getFieldsForTable($params, $options);
		$sfdb = $m->getFieldsForSearch($params, $options);
		
		//变量
		$modname = $modinfo['name'];
		$table_id = 'mod_table_'.$modname;
		
		$treeview = $this->requestBool('treeview', $this->_treeview);
		$vmask = $this->requestInt('vmask', $this->_default_vmask);
		$viewtype = $this->requestInt('defaultviewtype', $this->_default_viewtype);
		
		//$showToggle = $this->requestBool('showToggle', $cardview);
		//$pageSize = $this->requestInt('pageSize', $cardview?12:10);
		//$this->assign('pageList', $cardview?"[12, 24, 60, 120]":"[10, 20, 50, 100, 500,1000]");
		//$this->assign('pageList', "[10, 20, 50, 100]");
		
		//$this->assign('pageList', "[10, 20, 50, 100]");
		$this->assign('pkey', $modinfo['pkey']);		
		$this->assign('fdb', $fdb);		
		$this->assign('sfdb', $sfdb);	
		$this->assign('table_id', $table_id);	
		$this->assign('_modname', $this->_modname);
		//keyword
		//_keyword
		$query = $this->request('query');
		$this->assign('_keyword', $query);
		
		
		//sort 
		$default_sort_field = isset($options['sort'])?$options['sort']:$modinfo['pkey'];
		$this->assign('default_sort_field', $default_sort_field);
		$this->assign('disabledeleteall', isset($options['disabledeleteall'])?"true":"false");
		$default_sort_field_order = isset($options['sort_order'])?$options['sort_order']:$modinfo['default_sort_field_mode'];
		$this->assign('default_sort_field_order', $default_sort_field_order);
		
		$this->initTools('show', $options);
		$this->initRequestParams();
		
			
		//模板
		$this->setTpl('dt_show');
		return true;
	}
	
	protected function getCol()
	{
		return 2;
	}
	
	protected function setColumns($fields, $isdetail=true)
	{
		//columns
		$columns = $this->getCol();
		$column_width = 12/$columns; 
		$fdb = array();
		$fullfdb = array();
		
		$i = 0; 
		foreach($fields as $key=>$v) { 
			if ($isdetail && !$v['detail']) 
				continue;  
			if (!$isdetail && !$v['edit']) 
				continue;  
			if (isset($v['full']) && $v['full']) {
				$fullfdb[] = $v;
			} else {
				$fdb[$i++] = $v; 
			}
		}
		
		$this->assign('column_width', $column_width);
		$this->assign('fdb', $fdb);
		$this->assign('fullfdb', $fullfdb);
		$this->assign('nr_field', $i);
		$this->assign('columns', $columns);
		$this->assign('nr_column', $columns);
		
		return $fdb;
	}
	
	
	protected function detailForModel($modname, $params, &$options=array())
	{
		$id = $params['id'];
		
		$m = Factory::GetModel($modname);
		$options['detail'] = true;
		$params = $m->getForView($id, $options);
		
		
		$tablename = $modname;
		$table_id = 'mod_table_'.$tablename;
		
		$fields = $m->getFieldsForDetail($params, $options);
		
		$mi18n  = get_i18n('mod_'.$tablename);
		
		//columns
		$this->setColumns($fields);
		
		$this->setTpl('dt_detail');
		
		$this->initTools('detail', $options);
		
		
		$this->assign('fields', $fields);		
		$this->assign('mi18n', $mi18n);
		$this->assign('edit', false);
		$this->assign('table_id', $table_id);
		$this->assign('params', $params);
		$this->assign('modname', $modname);
		return $params;
	}
	
	protected function initParamsForDetail(&$params, &$options=array())
	{
		return true;
	}
	
	/**
	 * 详细
	 *
	 * @param mixed $options This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function detail(&$options=array())
	{
		$id = $this->get_id();
		
		$m = $this->getModel();
		$options['detail'] = true;
		
		$params = $m->getForView($id, $options);
		
		$this->initParamsForDetail($params, $options);
		
				
		$tablename = $this->_modname;
		$table_id = 'mod_table_'.$tablename;
		
		$fields = $m->getFieldsForDetail($params, $options);
		
		$mi18n  = get_i18n('mod_'.$tablename);
		
		//columns
		$this->setColumns($fields);
		
		$this->setTpl('dt_detail');
		
		$this->initTools('detail', $options);
		
		
		$this->assign('fields', $fields);		
		$this->assign('mi18n', $mi18n);
		$this->assign('edit', false);
		$this->assign('table_id', $table_id);
		$this->assign('params', $params);
		
		return $params;
	}
	
	
	protected function initParams(&$params, &$options=array())
	{
		return true;		
	}
	
	
	protected function initParamsForAdd(&$params, &$options=array())
	{
		return true;
	}
	
	
	protected function initParamsForEdit(&$params, &$options=array())
	{
		$m = $this->getModel();
		$id = $this->get_id();		
		$_params = $m->get($id);
		
		$params = is_array($params)?array_merge($params, $_params):$_params;
		
		return $params;
	}
	
	
	protected function prepSubmitEditParams(&$params, &$options=array())
	{
		return true;		
	}
	
	protected function prepSubmitAddParams(&$params, &$options=array())
	{
		unset($params['id']);
		return true;		
	}
	
	
	protected function postSubmitParams(&$params, &$options=array())
	{
		if (isset($options['_dlg']) && $options['_dlg'] == 1) {
			$data = isset($options['data'])?$options['data']:array();
			$data['row'] = $params;
			
			$options['data'] = $data;
		}
		
		return true;
	}
	
	protected function addForModel($modname, &$options=array())
	{
		$m = Factory::GetModel($modname);		
		if ($this->_sbt) {
			
			$this->getParams($params);
			$this->prepSubmitAddParams($params, $options);
			
			$res = $m->set($params, $options);
			
			$data = array();
			if ($res)				
				$this->postSubmitParams($params, $options);
			if (isset($options['data']))
				$data = $options['data'];
			
			
			showStatus($res, $data);
			
			return $res;
		}
		
		
		$table_id = 'mod_table_'.$modname;
		$this->assign('table_id', $table_id);
		
		$this->initParams($params, $options);
		$tname = $this->_task;
		$initTaskName = "initParamsFor".ucfirst($tname);
		if (method_exists($this, $initTaskName))
			$this->$initTaskName($params, $options);

		$fields = $m->getFieldsForInputAdd($params, $options);
		if (!$fields) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "WARNING: getFieldsForInput$tname failed!", $tname, $modinfo);
			$fields = array();
		}
		
		$this->assign('fields', $fields);		
		
		//$this->initTools('edit', $options);
		$mi18n  = get_i18n('mod_'.$tablename);
		$this->assign('mi18n', $mi18n);
		$this->assign('params', $params);
		
		$this->initTools($tname, $options);
		$this->setColumns($fields, false);
		$this->setTpl('dt_add');
		
		return $fields;		
	}
	
	protected function add(&$options=array())
	{
		$modname = $this->getModelName();		
		$res = $this->addForModel($modname, $options);	
		return $res;		
	}
	
	protected function editForModel($modname, &$options=array())
	{
		$m = Factory::GetModel($modname);		
		
		if ($this->_sbt) {
			$this->getParams($params);
			
			$this->prepSubmitEditParams($params, $options);
			
			$res = $m->set($params, $options);
			
			$data = array();
			if (isset($options['data']))
				$data = $options['data'];
			if ($res)				
				$this->postSubmitParams($params, $options);
			
			showStatus($res, $data);
			
			return $res;
		}
		
		$table_id = 'mod_table_'.$modname;
		$this->assign('table_id', $table_id);
		
		$this->initParams($params, $options);
		$tname = $this->_task;
		$initTaskName = "initParamsFor".ucfirst($tname);
		if (method_exists($this, $initTaskName))
			$this->$initTaskName($params, $options);

		$this->initParamsForEdit($params, $options);
		$fields = $m->getFieldsForInputEdit($params, $options);
		if (!$fields) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "WARNING: getFieldsForInput$tname failed!", $tname, $modinfo);
			$fields = array();
		}
		
		$this->assign('fields', $fields);		
		$this->assign('params', $params);
		
		$mi18n  = get_i18n('mod_'.$tablename);
		$this->assign('mi18n', $mi18n);
		
		$this->initTools($tname, $options);
		$this->setColumns($fields, false);
		$this->setTpl('dt_edit');
		
		return $fields;
	}
	
	
	protected function edit(&$options=array())
	{
		$modname = $this->getModelName();
		$res = $this->editForModel($modname, $options);		
		return $res;		
	}	
	
	
	protected function doEdit($modname, &$options=array())
	{
		$this->_modname = $modname;
		$this->assign('modname', $modname);
		$this->edit($options);		
	}
	
	protected function del(&$options=array())
	{
		$ids = $this->request('id');		
		if (!$ids)
			showStatus(-1);
		
		if (!is_array($ids)) 
			$ids = explode(',', $ids);
		
		$m = $this->getModel();
		foreach ($ids as $key => $id) {
			$id = intval($id);
			$res = $m->del($id, $options);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "del failed! id=$id!");
				break;
			}
		}
		
		showStatus($res?0:-1);		
		return $res;
	}
	
	protected function delete(&$options=array())
	{
		return $this->del($options);
	}
	
	protected function mck(&$options=array())
	{
		$id = $this->_id;
		$fieldname = $this->request('fieldname');
		$key = get_int('key');				
		$mask = 0x1 << $key;
		
		$m = $this->getModel();		
		$res = $m->mck($id, $mask, $fieldname, $options);
		
		showStatus($res?0:-1);
	}
		
	protected function onoff(&$options=array())
	{
		$id = $this->_id;
		$modname = $this->request('modname');
		$field = $this->request('field');
		
		$m = Factory::GetModel($modname);
		$res = $m->onoff($id, $field, $options);
			
		showStatus($res?0:-1);
	}
	
	
	
	protected function clickedit(&$options=array())
	{
		$id = $this->_id;
		$modname = $this->request('modname');
		$field = $this->request('field');
		$value = $this->request('value');
		
		$m = Factory::GetModel($modname);
		$res = $m->clickedit($id, $field, $value, $options);
		
		showStatus($res?0:-1, $res);
	}
	
	//对调排序行
	protected function sortswap(&$options=array())
	{
		$id = $this->_id;
		$id2 = $this->requestInt('id2');
		$up = $this->requestInt('up');
		$modname = $this->request('modname');
		$field = $this->request('field');
		$dir = $this->request('dir');
		
		$m = Factory::GetModel($modname);
		$res = $m->sortswap($id, $id2, $field, $dir, $up, $options);
		
		showStatus($res?0:-1, $options['data']);
	}
	
	/**
	 * autocomplete 自动补全请求处理
	 *
	 * @param mixed $options This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function autocomplete(&$options=array())
	{
		$query = $_REQUEST["q"];
		
		$this->getParams($params);
		
		$modname = $options['vpath'][0];
		$fieldname = $options['vpath'][1];
		
		
		if (!isset($params['__keyword'])) {
			$params['__keyword'] = $query;
		}
				
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'modname='.$modname.', fieldname='.$fieldname);
		
		$m = $this->getModel();
		$udb = $m->select($params, $options);
		
		$results = array();		
		foreach($udb as $key=>$v) {
			$desc = $v['name'].'|'.$v['ano'];
			$results[] = array(
				"value" => $v['name'],
					"desc" => $desc,
				//"img" => "http://relaxcms.com/50/50/?" . (rand(1, 10000) . rand(1, 10000)),
				"tokens" => array($query, $query . rand(1, 10))
			);
		}
		echo json_encode($results);
		exit;

	}
	
		
	/**
	 * select2me ajax query
	 *
	 * @param mixed $options This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function select2me(&$options=array())
	{
		$query = $_REQUEST["q"];
			
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'modname='.$modname.', fieldname='.$fieldname);
		$m = $this->getModel();
		$res = $m->selectForSelect2me($query, $options);
		echo json_encode($res);
		exit;
		
	}
	
	
	protected function listview(&$options=array())
	{
		$cf = get_config();
		$default_page_size = $cf['page_size'];
		if ($default_page_size <= 0)
			$default_page_size = 10;
		
		$pretask = isset($_REQUEST['pretask'])?$_REQUEST['pretask']:'show';
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page_size = isset($_REQUEST['page_size'])?intval($_REQUEST['page_size']):$default_page_size;
		$order = $this->request('order');
		$dir = $this->request('dir');
		$treeview = isset($_REQUEST['treeview'])?intval($_REQUEST['treeview']):0;
		$pid = isset($_REQUEST['pid'])?intval($_REQUEST['pid']):($treeview == 1?0:-1);
		$wsize = isset($_REQUEST['wsize'])?intval($_REQUEST['wsize']):0;
		
		$params = $this->getParams(); //isset($_REQUEST['params'])?$_REQUEST['params']:array();	
		if (!empty($params['__keyword'])) {
			$treeview = 0;
			$pid = -1;
		}
				
		$params['page'] = $page;
		$params['page_size'] = $page_size;
		if ($wsize > 0)
			$params['__wsize'] = $wsize;
		
		//$params['dir'] = $dir;
		if ($order)
			$params['__orderby'] = array($order=>$dir);
			
		$params['treeview'] = $treeview;
		if ($pid >= 0)
			$params['pid'] = $pid;
		
		$fromcall = "initParamsFor$pretask";
		if (method_exists($this, $fromcall))
			$this->$fromcall($params, $options);
		
				
		$m = $this->getModel();
		$res = $m->selectForListview($params, $options);
			
		showStatus($res?0:-1, $res);
	}
	
	protected function vplay(&$options=array())
	{
		$this->setTpl('dt_vplay');	
		
		$id = $this->probID($options);
		if (!$id) {
			show_error('str_params_error');
			return false;
		}
		
		$m = $this->getModel();		
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
		
		$this->assign('params', $params);
	}
	
	
	//更新排序与列表页
	protected function taxis(&$options=array())
	{
		$params = get_var('params');
		$m = Factory::GetModel($this->_modname);
		$res = $m->taxis($params);		
		showStatus($res?0:-1);		
	}
	
	protected function map(&$options=array())
	{
		$this->setTpl('map');
	}
	
	
	protected function enModelInfo($params)
	{
		return base64_encode(serialize($params));
	}
	
	protected function deModelInfo($modinfo)
	{
		return unserialize(base64_decode($modinfo));
	}
	
	protected function pubto(&$options=array())
	{
		$id = $this->_id;
		$this->setTpl('pubto');
		
		$m = $this->getModel();
		if ($this->_sbt) {
			$this->getParams($params);
			$res = $m->pubto($params, $options);
			
			showStatus($res?0:-1);
		}
		
		
		$params = $m->getPubtoForView($id, $options);
		$this->setParams($params);
		
		//$pubtodb = $m->getPubtoPlatform($id);		
		//$this->assign('pubtodb', $pubtodb);
		
		$fields = $m->getFieldsForInputEdit($params, $options);
		if (!$fields) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "WARNING: getFieldsForInput$tname failed!", $tname, $modinfo);
			$fields = array();
		}
		
		$this->assign('fields', $fields);	
		
		//etime
		$etimehidden = $params['etype'] == 1?'':'hidden';
		$this->assign('etimehidden', $etimehidden);	
		
		
		
	}
}