<?php

/**
 * @file
 *
 * @brief 
 * 
 * 组管理类
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CGroupModel extends CTableModel
{
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function GroupTable($name, $options=array())
	{
		$this->__construct($name, $options);
	}

	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		switch ($f['name']) {
			case 'type':
				$f['input_type'] = 'selector';
				//$f['show'] = false;					
				$f['edit'] = false;					
				break;
			default:
				break;
		}

		return true;
	}
	
	protected function _initFeild()
	{
		parent::_initFeild();
		
		$this->newField('privilege', array('input_type'=>'custom', 'format'=>'custom', 'sort'=>99, 'show'=>false, 'noclickedit'=>true, ));		
	}
	
	protected function getActions($row=array(), &$options=array())
	{
		$actions = $this->_default_actions;
		
		if ($row['type'] == 1) { //内置
			$actions['del']['enable'] = false;
		}
		
		return $actions;
	}
	
	
	public function formatCustomForView(&$row, $field, &$options = array())
	{
		$name = $field['name'];
		switch($name) {
			case 'privilege':
				$row['_privilege'] = $this->buildInputForPrivilege($field, $row, $options);
				break;
			default:
				break;
		}
	}
	
	public function formatForView(&$row, &$options = array())
	{
		$res =  parent::formatForView($row, $options);
		
		//previewUrl for Listview
		$row['previewUrl'] = $options['_dstroot']."/img/group.png";
		
	}
	
	
	
	public function get($id)	
	{
		$res = parent::get($id);
		if (!$res) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no id '$id'!");
			return false;
		}
				
		$m = Factory::GetModel('privilege2group');
		$params = array('gid'=>$id);
		$rdb = $m->select($params);
		
		$perms = array();
		foreach($rdb as $key =>$v) {
			$perms[$v['pid']] = $v['permision'];
		}
		$res['perms'] = $perms;
		$res['privilege'] = $perms;
		
		//var_dump($perms); exit;
		//$res['privilege'] = $this->buildInputForPrivilege($res, $this->_fields);
		
		return $res;	
	}


	
	

	protected function checkPrivilegeParams(&$params)
	{
		$privileges = $params["privileges"];		
		if (!$privileges) {
			//rlog(RC_LOG_DEBUG,__FILE__, __LINE__, "no privileges!");
			return false;
		}		
		$app = Factory::GetApp();
		
		$m = Factory::GetModel('privilege');
		$pids = array();

		$pp = array();		
		foreach($privileges as $key=>$v) {
			$pid = $v;
			$p = array();			
			$permisions = isset($params["permisions_$pid"])?$params["permisions_$pid"]:array();
			$permision = $m->getPermistionId($permisions);
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $permisions);
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $permision);
			
			$p['pid'] = $pid;
			$p['permision'] = $permision;
			
			$pids[$pid] = $p;
			
			//父节点自动
			$ppid = $app->getParentMenuPid($pid);	
			$pids[$ppid] = array('pid'=>$ppid, 'permision'=>1);			
		}
		
		$params['privileges'] = $pids;

		
		return true;
	}

		
	
	protected function updateRole($gid, $name)
	{
		$m = Factory::GetModel('group2role');
		$res = $m->getOne(array("gid"=>$gid));
		if (!$res) {
			$m2 = Factory::GetModel('role');
			$res2 = $m2->getOne(array("name"=>$name));
			if (!$res2) {//默认创建组，同时添加与组名相同的角色，并把组加入角色
				$params = array('name'=>$name, 'type'=>2);
				if (!$m2->set($params)) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'set role failed!');
					return false;
				}
				$rid = $params["id"];		
				
				//新角色，默认把用户组3加进去：
				$_params = array('rid'=>$rid, 'gid'=>3);
				$m->set($_params);
			} else {
				$rid = $res2['id'];				
			}			
			
			$_params = array('rid'=>$rid, 'gid'=>$gid);
			$m->set($_params);
		}
		return true;
	}
		
	public function set(&$params, &$options = array())
	{
		$id = isset($params[$this->_pkey])?$params[$this->_pkey]:0;
		!$id && $id = isset($params['id'])?$params['id']:0;
		
		$res = $this->get($id);
		if (!$res && !isset($params['type'])) {
			$params['type'] = 2;			
		}		
		
		$res = parent::set($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, 'set group failed!', $params);
			return $res;
		}

		$gid = $params['id'];	
			
		if (isset($params['privileges'])) {
			$this->checkPrivilegeParams($params);
			//privileges
			$m = Factory::GetModel('privilege2group');
			$m->clean(array('gid'=>$gid));		
			foreach($params['privileges'] as $key=>$v) {
				$v['gid'] = $gid;
				$res = $m->set($v);			
			}
		}

		//role	
		if (!isset($options['noupdaterole']))	
			$this->updateRole($gid, $params['name']);
			
		return $res;	
	}
	
	
	public function del($id, &$options=array())
	{
		
		$res = $this->get($id);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "invalid group id '$id'");
			return false;
		}
		
		if ($res['type'] == 1) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "cannot delete system group id '$id'");
			return false;
		}
		
		$res = parent::del($id, $options);
		if ($res) {
			$m = Factory::GetModel('group2role');
			$m->clean(array('gid'=>$id));
			
			$m = Factory::GetModel('privilege2group');
			$m->clean(array('gid'=>$id));
		}
		
		return $res;
	}


	protected function getTaskPermistions($tasks)
	{
		if (!$tasks)
			return false;
		$perms = array();
		$perms_str = '';
		foreach ($tasks	 as $key=>$v) {
			$perms_str .= $v; 
		} 
		for ($i=0; $i<strlen($perms_str); $i++) {
			$perms[$perms_str[$i]] = $perms_str[$i];
		}
		return $perms;
	}
	
	
	protected function getPrivilegeTaskCheckbox($menuitem, $perms=array(), $disabled='')
	{
		$checked = '';
		$pid = $menuitem['pid'];
		if (array_key_exists($pid, $perms) )
			$checked = 'checked';
		
		$i18nperms = get_i18n('perms');	
		$permid = isset($perms[$pid])?$perms[$pid]:0;
		$text = $menuitem['privtitle'];
		
		$privilege_checkbox = '';
		$privilege_checkbox .= "<li class='item' style='float: left; padding:5px;'>\n";
		$privilege_checkbox .= "<input type='checkbox' name='params[privileges][]' class='privilege2group' value='$pid' id='pid_$pid' $checked $disabled/> $text ";
		
		$m = Factory::GetModel('privilege');
		
		//子项：读、写、执行
		if (isset($menuitem['task']) && ($permisions = $this->getTaskPermistions($menuitem['task']))) { 
			$privilege_checkbox .= "<div class='permision' style='padding: 2px 5px 2px 22px;'>\n";
			$permistion_ids = $m->getPermistionIds();
			foreach($permisions  as $k=>$v) {					
				$permval = $permistion_ids[$v];
				$permtitle = $i18nperms[$v];
				if (!$permtitle)
					continue;
								
				$perm_checked = "";
				if ($permid & $permval)
					$perm_checked = "checked";
				$privilege_checkbox .= "<input type='checkbox' name='params[permisions_$pid][]' value='$v' class='pid_{$pid} permisionid' $perm_checked $disabled/> $permtitle ";
			}						
			$privilege_checkbox .= "</div>\n";
		}
		$privilege_checkbox .= "</li>\n";
		
		return $privilege_checkbox;
	}
	

	/**
     * 权限表
	 */
	protected function buildInputForPrivilege($field, $params, $options=array())
	{
		$perms = isset($params['perms'])?$params['perms']:array();
		
		$menus = Factory::GetApp()->getMenus();
		$disabled = isset($options['detail'])?'disabled':'';
		$checkalltitle = get_i18n('Check All');
		$privilege_checkbox = "<div class='privilege '>\n";
		$privilege_checkbox .= "<input type='checkbox' id='selectall' class='checkall' $disabled /> $checkalltitle ";
		foreach($menus as $key=>$v)
		{
			if ($v['parent']) //非顶层
				continue;
			$privilege_checkbox .= "<ul>";
			$privilege_checkbox .= "<div class='h4'>".$v['privtitle']."</div>\n";
			
			//检查一下顶层菜单项是否有task
			if ($v['task']) {
				$current_privilege_checkbox = $this->getPrivilegeTaskCheckbox($v, $perms, $disabled)	;
				$privilege_checkbox .= $current_privilege_checkbox;	
			}
			
			foreach($menus as $k2=>$v2) {
				if ($v2['parent'] != $key) //非当前子菜单
					continue;
				if ($v2['pid'] == '0')
					continue;
				
				$child_privilege_checkbox = $this->getPrivilegeTaskCheckbox($v2, $perms, $disabled)	;
				$privilege_checkbox .= $child_privilege_checkbox;				
			}
			$privilege_checkbox .= "<div class='clear'></div></ul>";
			
		}	
		
		$privilege_checkbox .= "</div>\n";		
		return $privilege_checkbox;
	}
	
	protected function  buildInputCustom(&$field, $params, &$options=array())
	{
		$name = $field['name'];
		
		switch($name) {
			case 'privilege':
				return $this->buildInputForPrivilege($field, $params,  $options);
			default:
				return $this->buildInputForText($field, $params, $options);
		}
	}
	
	
	public function createGroup($params, $privilege=array())
	{
		if (!$params || !isset($params['name']))
			return false;
		
		$res = $this->getOne(array('name'=>$params['name']));
		if (!$res) {
			$res = $this->set($params);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set group failed!");
				return false;
			}
			
			$res = $this->get($params['id']);			
		}
		
		$this->updateRole($res['id'], $res['name']);
		
		return $res;
	}
}