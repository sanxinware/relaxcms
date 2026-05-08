<?php

class AdminApplication extends CMainApplication
{
	public function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
		
	public function AdminApplication($name, $options=null)
	{
		$this->__construct($name, $options);
	}

	public function getDashbordInfo(&$options=array())
	{
		$params = array();
		$m = Factory::GetModel('session');
		$nr_user = $m->getCount();		
				
		$cname = 'session';
		$params[] = array(
				'name'=>'当前在线',
				'cname'=>$cname,
				'class'=>'blue-soft',
				'icon'=>'fa fa-users',
				'number'=>"$nr_user",
				'uname'=>'人',
				'desc'=>'',				
				'url'=>$options['_basename'].'/'.$cname,
				'sort'=>1,	
				'level'=>2,			
				);
		return $params ;
	}
	
		
	/**
	 *
	 * @return mixed This is the return value description
	 *
	 */
	protected function checkStartComponent(&$options=array())
	{
		$default_component = $this->getDefaultComponent();
		
		if ($options['component'] != $default_component)
			return false;
			
		$cf = get_config();
		if (!empty($cf['default_component']) && $this->hasPrivilegeOf($cf['default_component'])) {
			$cname = $cf['default_component'];
			$options['component'] = $cname;			 
			if (!$this->isComponent($cname)) {
				$menus = $this->getMenus();
				if (isset($menus[$cname])) {
					$appname = $menus[$cname]['app'];
					if ($appname != $this->_name) {
						$options['aname'] = $appname;
						$options['_aname'] = $appname;
						$options['cname'] = $cname;	
						$options['componentinfo'] = $menus[$cname];	
						$this->setRunApp($appname);
					}
				}
			}	
		}
		
		return true;
	}
	
		
	protected function initDefaultRoleGroup()
	{
		$privdb = $this->getMenusPids();
		if (!is_array($privdb))
			$privdb = array();
			
		$m = Factory::GetModel('group');
		
		$name = $this->i18n('str_sysadmin_group');
		$params = array('id' =>1, 'name'=>$name , 'type'=>1);
		$m->set($params);
		
		$name = $this->i18n('str_admin_group');
		$params = array('id' =>2, 'name'=>$name , 'type'=>1);
		$m->set($params);
		
		$name = $this->i18n('str_user_group');
		$params = array('id' =>3, 'name'=>$name , 'type'=>1);
		$m->set($params);
		
		$m1 = Factory::GetModel('privilege2group');
		$m1->clean();
		
		foreach ($privdb as $key=>$v) {
			if (isset($v['pid']))
				$pid = $v['pid'];
			else 
				$pid = 0;
			if (isset($v['permision']))
				$permision = $v['permision'];
			else 
				$permision = 0;
			
			$params = array('pid'=>$pid, 'gid'=>1, 'permision'=>$permision);
			$m1->set($params);
			
			if (!$v['level'] || ($v['level'] & LEVEL_ADMIN)) {
				$params = array('pid'=>$pid, 'gid'=>2, 'permision'=>$permision);
				$m1->set($params);
			}	
			
			if (!$v['level'] || ($v['level'] & LEVEL_USER)) {
				$params = array('pid'=>$pid, 'gid'=>3, 'permision'=>$permision);
				$m1->set($params);
			}			
		}
		
		$m2 = Factory::GetModel('role');
		$role = $this->i18n('str_role_sysadmin');
		$params = array('id' =>1, 'name'=>$role , 'type'=>1);
		$m2->set($params);
		
		$role = $this->i18n('str_role_admin');
		$params = array('id' =>2, 'name'=>$role , 'type'=>1);
		$m2->set($params);
		
		$role = $this->i18n('str_role_user');
		$params = array('id' =>3, 'name'=>$role , 'type'=>1);
		$m2->set($params);
		
		$m3 = Factory::GetModel('group2role');
		$params = array('gid' =>1, 'rid'=>1);
		$m3->set($params);
		$params = array('gid' =>3, 'rid'=>1);
		$m3->set($params);
		
		$params = array('gid' =>2, 'rid'=>2);
		$m3->set($params);
		$params = array('gid' =>3, 'rid'=>2);
		$m3->set($params);
		
		$params = array('gid' =>3, 'rid'=>3);
		$m3->set($params);
		
		return true;
	}
	
	public function install($options=array())
	{
		$res1 = false;
		$db = Factory::GetDBO();		
		$sql = RPATH_DATABASE.DS.'sql'.DS."create_table.sql";
		if (file_exists($sql)) {
			if (!($res1 = $db->import($sql))) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING: call import '$sql' error.");				
			}
		}
		
		$res2 = parent::install($options);
		if (!$res2)  {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "WARNING: call install failed!");
		}
		
		$this->init($options);
		
		$res3 = $this->initDefaultRoleGroup();		
		return $res1 || $res2 || $res3;
	}
	
	public function localwebservice($options=array())
	{		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		
		$m = Factory::GetModel('file');
		$m->timerProcess();

		$cf = get_config();
		
		$timeout = isset($cf['seccodetimeout'])?$cf['seccodetimeout']:300;
		if ($this->check_localwebservice_timeout($timeout)) {
			$m = Factory::GetModel('user_seccode');
			$m->timer();
		}

		$timeout = isset($cf['session_timeout'])?$cf['session_timeout']:600;
		if ($this->check_localwebservice_timeout($timeout)) {
			$m = Factory::GetModel('session');
			$m->timer();
		}
		
		
		if ($this->check_localwebservice_timeout(3)) {
			$m = Factory::GetModel('pub');
			$m->timerProcess();
		}
			
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		return true;
	}
}