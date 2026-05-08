<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CInstallation
{
	protected $_options;
	
	public function __construct($options= array())
	{
		$this->_options = $options;
	}	
	
	public function CInstallation($options= array()) 
	{
		$this->__construct($options);
	}
	
	protected function probeApps()
	{
		$apps = array();
		
		//编历目录
		$dir = RPATH_APPS;
		$udb = s_readdir($dir);
		
		$hdb = array('.svn');
		
		$tdb = array();
		if (!is_array($udb))
			$udb = array();
		
		$id = 1;
		foreach ($udb as $key=>$v) {
			if (in_array($v, $hdb))
				continue;
			
			$item = getAppInfo($v);
			if (!$item)
				continue;
			
			//name
			$item['id'] = $id ++;
			$item['dirname'] = $v;
			if (!isset($item['appname']))
				$item['appname'] = $v;
			if (isset($item['embeded']) && $item['embeded'])
				continue;
			
			$item['checked'] = 'checked';
			$item['readonly'] = '';
			
			if (array_key_exists($v, $apps)) {
				$item['checked'] = 'checked';
				if ($apps[$v]['readonly'])
					$item['readonly'] = 'readonly';
			}
			
			$tdb[$item['appname']] = $item;
		}
		
		return $tdb;
		
	}
	
	protected function doInstall($params)
	{
		$allapps = $this->probeApps();		
		
		$dbconfig["dbtype"]	= $params["dbtype"];
		$dbconfig["dbhost"]	= $params["dbhost"];
		$dbconfig["dbport"]	= isset($params["dbport"])?$params["dbport"]:'';
		$dbconfig["dbuser"]	= $params["dbuser"];
		$dbconfig["dbpassword"] = $params["dbpassword"];
		$dbconfig["dbname"]	 = $params["dbname"];
		$dbconfig["dbcharset"]	 = $params["dbcharset"];
		$dbconfig["prefix"]	 = $params["prefix"];
		
		set_default_dbconfig($dbconfig, $params["dbtype"], true);
		
		
		//默认连接数据库类型
		$cookie = randstr();				
		$syscfg['dbtype'] = $params["dbtype"];
		$syscfg['hash'] = substr(md5($cookie.time()), 3, 8);
		$syscfg['timediff'] = "8";
		$syscfg['timeformat'] = "Y-m-d H:i:s";
		$syscfg['timezone'] = "shanghai";
		$syscfg['count'] = 20;
		$syscfg['cookie'] = $cookie;
		$syscfg['title'] = isset($params['product_name'])?$params['product_name']:'';
		$syscfg['product_name'] = $params['product_name'];
		$syscfg['updatetype'] = $params['updatetype'];
		$syscfg['updateapi'] = $params['updateapi'];
		
		$mainappcfg = get_mainapp_config();
		if ($mainappcfg) {
			$syscfg['description'] = $mainappcfg['description'];
			$syscfg['copyright'] = $mainappcfg['copyright'];
			$syscfg['website'] = $mainappcfg['website'];
		}
		
		set_config($syscfg, true);
		$cf = get_config(true);
		
		//管理员配置				
		$manager["manager"] = $params["manager"];
		$manager["manager_pwd"] = $params["manager_pwd"];
		$manager["manager_email"] = $params["manager_email"];		
		set_manager($manager, true);
				
		$dbname = $params['dbname'];
		$newdbuser = 0;
		$exists_rewrite = 0;
		if (isset($params['newdbuser']))
			$newdbuser = intval($params['newdbuser']);
		if (isset($params['exists_rewrite']))
			$exists_rewrite = intval($params['exists_rewrite']);
		
		//是否要创建用户
		if ($newdbuser && $params['dbuser'] && $params['dbuser'] != 'root') {
			$tmp = $params;
			$tmp['dbuser'] = 'root';
			$tmp['dbpassword'] = trim($params['dbroot_password']);
			$tmp['dbname'] = 'mysql';
			$tmp['dbhost'] = $params['dbhost'];
			
			//连接
			$db = Factory::GetDBO($params['dbtype'], $tmp);
			if (!$db->is_connected()) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "connect database as root failed!");		
				return false;
			}
			
			//创建用户
			$dbuser = $params['dbuser'];
			$dbpassword = $params['dbpassword'];
			
			//库不存在，创建库
			$res = $db->db_create($dbname);
			
			//查一下用户是否存在
			$res = $db->createUser($params);									
		}
		
		//重置root.
		if ($params['dbroot_password_reset'] && $params['dbroot_password_reset'] == $params['dbroot_password_reset2']) {
			$tmp = $params;
			$tmp['dbuser'] = 'root';
			$tmp['dbpassword'] = $params['dbroot_password'];
			$tmp['dbname'] = 'mysql';
			
			//连接
			$db = Factory::GetDBO($params['dbtype'], $tmp);
			if (!$db->is_connected()) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "connect database as root failed!");		
				return false;
			}
			
			$dbroot_password = $params['dbroot_password_reset'];			
			//空重置root
			$db->changePassword('root', $dbpassword);
			$db->close();
		}
		
		//
		$_params = $params;
		$db = Factory::GetDBO($_params['dbtype'], $_params);
		if (!$db->is_connected()) {
			$res = $db->reconnect('mysql');
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no db connected!", $_params);		
				return false;
			}
		}
		
		if (!$db->db_exists($dbname)) {
			$res = $db->db_create($dbname);			
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "create db '$dbname' failed!");	
				return false;
			}
			$createdb_flag = true;
		}
		else
		{
			if ($exists_rewrite == 1) {
				$db->db_drop($dbname);	
				$res = $db->db_create($dbname);	
				if (!$res) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "create db '$dbname' failed!");	
					return false;
				}
				
				$createdb_flag = true;
			}
		}

		$db = Factory::GetDBO($params['dbtype'], $params);
		
		$res = $db->db_select($dbname);
		
		//cache
		$apps = is_array($params['apps'])?$params['apps']:explode(' ', $params['apps']);		
		$installapps = array();
		foreach ($apps as $key=>$name) {
			$app = Factory::GetApp($name);
			if (!$app) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "unknown app '$name'!");
			} else {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "install app '$name' ...");
				if (isset($allapps[$name])) {
					$installapps[$name] = $allapps[$name];
				} else {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "install app '$name' failed!");
				}
			}
		}
		cache_apps($installapps);	
		
		
		$mainapp = Factory::GetApp('admin');
		$mainapp->install();
		
		$apps = is_array($params['apps'])?$params['apps']:explode(' ', $params['apps']);		
		$installapps = array();
		foreach ($apps as $key=>$name) {
			$app = Factory::GetApp($name);
			if (!$app) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "unknown app '$name'!");
			} else {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "install app '$name' ...");
				if (($res = $app->install($options))) {					
					if (isset($allapps[$name]))
						$installapps[$name] = $allapps[$name];
				} else {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "install app '$name' failed!");
				}
			}
		}		
		//cache
		cache_apps($installapps);
		$mainapp->cache();		
		
		
		touch(RPATH_CONFIG.DS.'installed');
		
		return $res;
	}
	
	
	
	
	
	public function install($params)
	{
		$res = $this->doInstall($params);
		return $res;
	}
}