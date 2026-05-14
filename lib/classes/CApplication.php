<?php
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

define( 'REQ_FLAG_1_A', 0x1);
define( 'REQ_FLAG_1_C', 0x2);
define( 'REQ_FLAG_1_T', 0x4);

define( 'REQ_FLAG_2_A', 0x8);
define( 'REQ_FLAG_2_C', 0x10);
define( 'REQ_FLAG_2_T', 0x20);

define( 'REQ_FLAG_3_A', 0x40);
define( 'REQ_FLAG_3_C', 0x80);
define( 'REQ_FLAG_3_T', 0x100);


/**
 * @name

 * 应用基类
 *
 * Copyright (c), 2024, relaxcms.com
 */
class CApplication extends CObject
{
	protected $_name = null;
	//! 应用类型
	protected $_apptype = null;

	protected $_options = array();
	
	
	/**
	 * 系统内部名称（version.php定义）
	 *
	 * @var mixed 
	 *
	 */
	protected $_sys_name = null;
	/**
	 * 版本标识
	 *
	 * @var mixed 
	 *
	 */
	protected $_sys_version = null;
	
	//基于RC构建的不同产品，名称与版本不同，升级RC是一样，特分离RC版本与软件产品版本：
	protected $_product_name = null;
	protected $_product_version = null;
	
	//! 配置
	protected $_appcfg = array();
	//! 默认配置文件
	protected $_appcfgfile = null;
	
	protected $_cfgloaded = false;
	//! 动态可配置运行配置
	//protected $_cfg = array();
	
	/**
	 * 默认app 所有目录
	 *
	 * @var mixed 
	 *
	 */
	protected $_default_appdir;
	
	
	/**
	 * 默认app模板目录
	 *
	 * @var mixed 
	 *
	 */
	protected $_default_tdir;
	
	
	
	/**
	 * 当前会话
	 *
	 * @var mixed 
	 *
	 */
	protected $_session = null;
	//! _islogin
	protected $_islogin = false;
	
	/**
	 * 引导APP所在目录
	 * 
	 */
	protected $_appdir;
	/** 执行 APP 所在目录, eg: a=<APPNAME> */
	protected $_rundir;
	
	
	/**
	 * 默认缓存目录
	 *
	 * @var mixed 
	 *
	 */
	protected $_cachedir = '';
	
	
	protected $_appbase = null;
	
	protected $_thename = null;
	protected $_tplname = null;
	
	protected $_aname = null;
		
	//指定APP
	protected $_appdb = null;
	protected $_app = null;
	protected $_appinfo = null;
	
	
	//国际化
	protected $_i18ns = array();
	protected $_lang='zh_CN';
	
	protected $_activeComponent = null;
	//菜单
	protected $_menus = array();
	protected $_default_menus = array();
	protected $_allmenus = array();
	protected $_modinfodb = array();
	
	
	/**
	 * $_default_flags_mask 用户登录掩码
	 *
	 * 默认用户登录掩码，只能用户flags在掩码内才允登录
	 * @var mixed 
	 *
	 */
	protected $_default_flags_mask = 0xffff;
	
	
	/**
	 * This is variable _default_level_mask description
	 *
	 * 默认左菜单栏掩码，菜单项的过滤使用，level小于此项值才显示
	 *  
	 * @var mixed 
	 *
	 */
	protected $_default_level_mask = 0xffff;
	
	
	/**
	 * 全局请求对象（参数解析过滤
	 *
	 * @var mixed 
	 *
	 */
	protected $_request;
	
	/** 已经缓存的APP菜单 */
	protected $_appmenus = array();
	
	/** 已经缓存的API接口 */
	protected $_apidb = array();
	
	
	public function __construct($name, $options = array())
	{
		$this->_name	= $name;
		$this->_options	 = $options;		
		$this->_appdir = $options['appdir'];
		$this->_rundir = $options['appdir'];
		$this->_cachedir = RPATH_CACHE.DS.$name;
		
		$this->_init();	
	}

	public function CApplication($name, $options = array())
	{
		$this->__construct($name, $options);
	}
	
	
	/* ========================================================
	 * 初始化
	 * =======================================================*/
	protected function _init()
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN appname=".$this->_name);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		return false;
	}
	
	/**
	 * 单实例创建
	 *
	 * @param mixed $name 类名
	 * @param mixed $options 配置选项信息
	 * @return mixed This is the return value description
	 *
	 */
	static function GetInstance($name, $options = array())
	{
		static $instances;
		$cname = strtolower($name);
		$classname = ucfirst($name)."Application";
		
		if (!isset( $instances )) 
			$instances = array();
		
		if (empty($instances[$name]))	{
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "################# app '$name' ####################");
			
			if (file_exists(RPATH_APPS.DS.$name.DS.$name.".php")) {
				$appdir = RPATH_APPS.DS.$name;
				$file = $appdir.DS.$name.".php";
			} else {				
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__,"no app file '$name'!");
				return null;
			}
			
			$options['appdir'] = $appdir;
		
			require_once $file;
			
			$instance = new $classname ($cname, $options);				
			$instances[$name] = &$instance;
		}
		
		return $instances[$name];
	}

	/* ========================================================
	 * Utility functions
	 * =======================================================*/
	
	public function getAppName()
	{
		return $this->_name;
	}
	
	public function getAppdir()
	{
		return $this->_appdir;
	}
	
	public function getRundir()
	{
		return $this->_rundir;
	}
	
	public function getOptions()
	{
		return $this->_options;
	}
	
	
	
	protected function isApp($name)
	{
		if (!$this->_appdb)
			$this->_appdb = Factory::GetApps();
		
		if (isset($this->_appdb[$name]))
			return true;
		return false;
	}
	
	//菜单中未记录，但是一个组件文件，如：<list>.php
	protected function isComponent($cname)
	{
		$res = (is_file($this->_rundir.DS.'components'.DS.$cname.'.php'))?true:false;
			
		return $res;
	}
	
	protected function initApi()
	{
		$file = $this->_cachedir.DS."api.php";		
		if (is_file($file)) {
			require $file;
			if (isset($apidb)) {
				$this->_apidb = $apidb;
			}
		}		
	}
	
	protected function isApi($name)
	{
		if (!$this->_apidb)
			$this->initApi();
		
		if (isset($this->_apidb[$name]))
			return true;
		return false;
	}
	
	protected function getDefaultComponent()
	{
		return 'main';
	}
	
	public function setMsg($level, $msg) 
	{
		$this->setMessage($msg, $level);
	}
	
	public function setLastMsg($key, $msg) 
	{
		$this->_activeComponent->assign('sys_message', $msg);		
		$this->_activeComponent->assign('sys_status', 'failed');
	}	
	
	
	//检查权限, 越权操作，直接退出
	public function hasPrivilegeOf($component, $task='')
	{
		return false;
	}
	
	
	/**
	 * 获取错误信息，以HTML的div格式返回
	 *
	 * @return mixed This is the return value description
	 *
	 */
	public function get_error_html()
	{
		if ($this->_errors == null) {
			return false;
		}
		
		$res = "<div class='errstr'><ul>";		
		foreach ($this->_errors as $key=>$v) {
			$res .= "<li>$v</li>";			
		}				
		$res .= "</ul></div>";		
		return $res;
	}
	
	
	/**
	 * 显示系统消息
	 *
	 * @param mixed $msg This is a description
	 * @param mixed $backurl This is a description
	 * @param mixed $target This is a description
	 * @param mixed $ext This is a description
	 * @param mixed $type This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function showMessage($msg, $backurl=null, $target="_self", $ext=null, $type="error")
	{
		$msg = i18n($msg);
		
		$options['msg_text'] = $msg;
		$options['msg_backurl'] =  $backurl;
		$options['msg_target' ] = $target;
		$options['msg_ext' ] = $ext;
		$options['msg_type' ] = $type;
		
		if ($type == 'error') {
			$options['msg_alert_type' ] = 'danger';
			$options['msg_alert_btn' ] = 'red';
			$options['msg_title'] = i18n('str_failed');
			$status = -1;
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, $msg);
			
		} else {
			$options['msg_alert_type' ] = 'success';
			$options['msg_alert_btn' ] = 'green';	
			$options['msg_title'] = i18n('str_success');
			$status = 0;
		}
		if ($this->_oname == 'json') 
			showStatus($status);
		
		$this->_activeComponent->setMessage($options);
	}
	
	
	/**
	 * 查询运行时间
	 *
	 * @return mixed This is the return value description
	 *
	 */
	public function get_spends()
	{
		return time() - $this->_ts;
	}
	
	/**
	 * 重定向
	 *
	 * @param mixed $url This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function redirect( $url)
	{
		if (headers_sent()) {
			echo "<script>document.location.href='$url';</script>\n";
		} else {
			//@ob_end_clean(); // clear output buffer
			header( 'HTTP/1.1 301 Moved Permanently' );
			header( 'Location: ' . $url );
		}
		
		$this->close();
	}
	
	
	/**
	 * 关闭，退出
	 *
	 * @param mixed $code This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function close( $code = 0 ) 
	{
		exit($code);
	}
	
	public function getActiveComponent()
	{
		return $this->_activeComponent;
	}
	
	public function getAppbase()
	{
		return $this->_appbase;
	}
	
	public function getWebroot()
	{
		return $this->_request->getWebroot();
	}
	
	public function getWeburl()
	{
		return $this->_request->getWeburl();
	}
	
	public function localwebservice($localservicecfg)
	{
		return false;
	}
	
	public function getDashbordInfo(&$options=array())
	{
		return false;
	}
	
	public function getMyInfo(&$options=array())
	{
		return false;
	}	

	public function getMyMainBoardInfo(&$options=array())
	{
		$udb = $this->getDashbordInfo($options);

		$ddb = array();
		if ($udb){
			foreach ($udb as $key => $v) {
				if (isset($v['level']) && ($v['level'] & 1)) {
					$ddb[] = $v;
				}
			}
		}

		return $ddb;
	}	
	
	/* ==============================================================================================
	 * I18N functions
	 *	
	 * ============================================================================================*/
	
	protected function fixI18nSelAndModelInfo($lang, &$alli18n)
	{
		$deflang = 'zh_CN';
		if ($lang == $deflang) 
			return ;
		
		$deffile = $this->_cachedir.DS."i18n".DS.$deflang.".php";
		require_once($deffile);
		$defi18ns = $i18n;
		
		foreach ($defi18ns as $key=>$v) {
			//'sel_ or 'mod_'
			if (is_start_with($key, 'sel_') || is_start_with($key, 'mod_')) {
				if (!isset($alli18n[$key])) {
					$alli18n[$key] = $v;
				}
			}
		}				
	}
	
	protected function cacheI18nForModInfo($lang, $alli18n)
	{
		
		$modinfodb =array();
		
		
		foreach ($alli18n as $key=>$v) {
			//'mod_'
			if (is_start_with($key, 'mod_')) {
				$name = str_replace('mod_', '', $key);
				
				//modinfo
				if (isset($v['modinfo'])) {
					$modinfo = $v['modinfo'];
					unset($v['modinfo']);
				} else {
					$modinfo = array();
				}
				
				//old: modelname
				if (isset($v['modelname'])) {
					$title = $v['modelname'];
					unset($v['modelname']);
				} else {
					$title = $name;
				}
								
				if (!isset($modinfo['name'])) 
					$modinfo['name'] = $name;
				
				if (!isset($modinfo['title'])) 
					$modinfo['title'] = $title;
				
				//fields
				$modinfo['fields'] = $v;
				
				$modinfodb[$name] = $modinfo;
			}
		}		
		
		$cache = Factory::GetCache();
		$modinfoi18nfile = $this->_cachedir.DS."i18n".DS.$lang."-modinfo.php";
		
		$cache->cache_array("i18nmodinfodb", $modinfodb, $modinfoi18nfile);		
	}
	
	public function getModInfoList()
	{
		if (!$this->_modinfodb) {
			$modinfoi18nfile = $this->_cachedir.DS."i18n".DS.$this->_lang."-modinfo.php";	
			if (file_exists($modinfoi18nfile)) {
				require $modinfoi18nfile;
				$this->_modinfodb =  $i18nmodinfodb;
			}	
		}
		return $this->_modinfodb;
	}
	
	private function cacheI18n($lang)
	{
		//Local APP
		$app_i18n_array = array();
		
		$app_i18n_file = $this->_appdir.DS."i18n".DS.$lang.DS."i18n.php";
		if (file_exists($app_i18n_file)) {
			require $app_i18n_file;
			$app_i18n_array = $i18n;
		}
		
		//全局
		$g_i18n_file = array();
		$g_i18n_file = RPATH_I18N.DS.$lang.DS."i18n.php";
		if (file_exists($g_i18n_file)) {
			require $g_i18n_file;
			$g_i18n_array = $i18n;
		}
		
		
		//合并
		$alli18n = $app_i18n_array;		
		if (isset($g_i18n_array))
			$alli18n = array_merge($alli18n, $g_i18n_array);		
				
		//merge apps
		$apps = Factory::GetApps();
		if ($apps) {
			foreach ($apps as $key=>$v) {
				$file =RPATH_APPS.DS.$key.DS."i18n".DS.$lang.DS."i18n.php";
				if (file_exists($file)) {
					require $file;
					$alli18n = array_merge($alli18n, $i18n);
				}				
			}
		}
		
		
		$tdir = $this->_cachedir.DS."i18n";
		if (!is_dir($tdir))
			s_mkdir($tdir);
			
		//fixed for sel and mod
		$this->fixI18nSelAndModelInfo($lang, $alli18n);
		
		$cache = Factory::GetCache();
		$file = $tdir.DS.$lang.".php";
		$cache->cache_array("i18n", $alli18n, $file);
		
		//cache i18nModInfo
		$this->cacheI18nForModInfo($lang, $alli18n);
				
		$this->_i18ns = $alli18n;		
	}
	
	protected function initI81n()
	{
		$lang = $this->_lang;	
		$file = $this->_cachedir.DS."i18n".DS.$lang.".php";
		if (!file_exists($file)) {
			$deflang = 'zh_CN';
			if ($lang != $deflang) {//默认
				$deffile = $this->_cachedir.DS."i18n".DS.$deflang.".php";
				if (!file_exists($deffile)) {
					$this->cacheI18n($deflang);
				}				
			}
			$this->cacheI18n($lang);
		}
		elseif (file_exists($file)) {
			require_once($file);
			$this->_i18ns = $i18n;					
		}
	}
	
	public function getI18n()
	{
		if (!$this->_i18ns) 
			$this->initI81n();
		return $this->_i18ns;
	}
	
	public function i18n($fmtstr, $default='')
	{
		$lang = $this->getI18n();
		$str = $fmtstr;
		if ($str && !empty($lang[$str])) {
			$str = $lang[$str];
			$args = func_get_args();
			if (count($args) > 1) {
				$phrase = array_shift($args);
				$str = vsprintf($str, $args);		
			}
		} else if ($default) {
				$str = $default;
			}	
		return $str;
	}
	
	
	protected function initAppcfg()
	{
		//static config
		$appcfgfile = $this->_appdir.DS.'config.php';
		if (file_exists($appcfgfile)) {
			require($appcfgfile);
		} else {
			$appcfg = array(
					'copyright'=>'RC',
					'description'=>'RELAXCMS',
					'website'=>'https://www.relaxcms.com',
					);
			
		}
		
		$appcfg['appname'] = $this->_name;
				
		$this->_appcfg = $appcfg;
	}
		
	public function getAppcfg()
	{
		if (!$this->_appcfg) 
			$this->initAppcfg();
		return $this->_appcfg;
	}
	
	
	protected function initVersion()
	{
		//static config
		$verfile = RPATH_LIB.DS.'version.php';
		if (file_exists($verfile)) {
			require($verfile);
			$this->_sys_name = SYS_NAME;
			$this->_sys_version = SYS_VERSION;
		} else {
			$this->_sys_version = "0.0.1";
			$this->_sys_name = "relaxcms";
		}
		
		//
		$productfile = RPATH_ROOT.DS.'version.php';
		if (file_exists($productfile)) {
			require($productfile);			
			$product_name = PRODUCT_NAME;
			$product_version = PRODUCT_VERSION;
			$product_fullname = PRODUCT_FULLNAME;
			$product_model = PRODUCT_MODEL;
			
			if ($product_fullname == "PRODUCT_FULLNAME") {
				$product_fullname = $product_name;
			}
			if ($product_model == "PRODUCT_MODEL") {
				$product_model = '';
			}		
		} else {
			$product_name = $this->_sys_name;
			$product_version = $this->_sys_version;
			$product_fullname = '';
			$product_model = '';			
		}
		
		$this->_product_name = $product_name;
		$this->_product_version = $product_version;
		$this->_product_fullname = $product_fullname;
		$this->_product_model = $product_model;
	}
	
	public function getMainAppConfig()
	{
		$appcfg = array();
		$productfile = RPATH_ROOT.DS.'version.php';
		if (file_exists($productfile)) {
			require($productfile);			
			$product_name = PRODUCT_NAME;
			$mainappname = strtolower($product_name);
			$app = Factory::GetApp($mainappname);
			if ($app) {
				$appcfg = $app->getAppcfg();
			}
		}		
		if (!$appcfg) {
			$appcfg = $this->getAppcfg();	
		}
		return $appcfg;
	}
	
	public function getSysName()
	{
		if (!$this->_sys_version) 
			$this->initVersion();
		return $this->_sys_name;
	}
	
	public function getSysVersion()
	{
		if (!$this->_sys_version) 
			$this->initVersion();
		return $this->_sys_version;
	}
	
	public function getVersion()
	{
		return $this->getSysVersion();
	}
	
	public function getProductVersion()
	{
		if (!$this->_product_version) 
			$this->initVersion();
		return $this->_product_version;
	}
	
	public function getProductName()
	{
		if (!$this->_product_version) 
			$this->initVersion();
		return $this->_product_name;
	}
	
	public function getProductFullname()
	{
		if (!$this->_product_version) 
			$this->initVersion();
		return $this->_product_fullname;
	}
	
	public function getProductModel()
	{
		if (!$this->_product_version) 
			$this->initVersion();
		return $this->_product_model;
	}
	
	/* ==============================================================================================
	 *  MENU functions
	 *	
	 * ============================================================================================*/
	protected function registerPrivilege($menu)
	{
		if (!$menu)
			return false;
		if (!isset($menu['component']))
			return false;
		
		$m = Factory::GetModel('privilege');
		$res = $m->getOne(array('component'=>$menu['component']));
		if ($res)
			return $res['id'];
		else {
			$params = array(
					'name' => $menu['name'],
					'component' => $menu['component']
					);
			
			$res = $m->set($params);
			if (!$res) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "set privilege failed!");
				return false;
			}
			
			return $params['id'];
		}
	}
	
	protected function initAppMenus()
	{
		$file = $this->_appdir.DS.'includes'.DS."menus.php";
		if (is_file($file)) {
			require $file;
		} else {
			$menus = array();
		}
		$this->_appmenus = $menus;	
	}
		
	public function getAppMenus()
	{
		if (!$this->_appmenus)	{
			$this->initAppMenus();
		}
		return $this->_appmenus;
	}
	
	public function getAppMenusPids()
	{
		$appmenus = $this->getAppMenus();
		
		$m = Factory::GetModel('privilege');
		foreach ($appmenus as $key=>&$v) {
			$pid = $this->registerPrivilege($v);
			if ($pid) {
				$v['pid'] = $pid;				
				$permid = $this->getPermistionId($v['task']);	
				$level = 0;
				if (isset($v['level']))
					$level = intval($v['level']);	
				$v['permision'] = $permid;	
				$v['level'] = $level;	
			}
		}
		
		return $appmenus;
	}
	
	
	
	protected function initMenus()
	{
		return false;
	}
		
	public function getMenus()
	{
		if (!$this->_menus) 
			$this->initMenus();
		return $this->_menus;
	}	
	public function getSiteMenu(&$options=array())
	{
		return false;
	}
	protected function getMenusInfo($name) 
	{
		$menus = $this->getMenus();
		return $menus[$name];
	}
	
	public function getParentMenuPid($pid)
	{
		$parent = '';
		$menus = $this->getMenus();
		foreach ($menus as $key=>$v) {
			if ($v['pid'] == $pid) {
				$parent = $v['parent'];
				break;
			}
		}
		
		if (!$parent)
			return 0;
		if (!isset($menus[$parent]))
			return 0;
		return $menus[$parent]['pid'];
	}
	
	protected function getPermistionId($permisions) 
	{
		$m = Factory::GetModel('privilege');
		return $m->getPermistionId($permisions);		
	}
	
	protected function getPermistionIdByName($name) 
	{
		$m = Factory::GetModel('privilege');
		return $m->getPermistionIdByName($name);		
	}
	
	protected function isPermistionPublic($name) 
	{
		$m = Factory::GetModel('privilege');
		return $m->isPermistionPublic($name);		
	}
	
	
	public function getMenusPids()	
	{
		$pids = array();		
		$menus = $this->getMenus();
		
		foreach($menus as $key=>$v)
		{
			$pid = $v['pid'];
			if (!$pid)
				continue;
			if (!isset($v['task']))
				continue;
			
			$permid = $this->getPermistionId($v['task']);	
			$level = 0;
			if (isset($v['level']))
				$level = intval($v['level']);			
			
			$pids[$pid] = array('pid'=>$pid, 'permision'=>$permid, 'level'=>$level);		
			//parent
			if ($v['parent']) {
				if (isset($menus[$v['parent']])) {
					$ppid = $menus[$v['parent']]['pid'];
					if (!isset($pids[$ppid])) {
						//rlog($pids);
					}
					$pids [$pid]['parent'] = $pids[$ppid];
				}
			} 	
		}
		return $pids;	
	}
	
	
	/**
	 * 返回子菜单
	 *
	 */
	protected function getSubMenus($menus, $mkey)
	{
		$mdb = array();
		$m = $menus[$mkey];
		
		$parent = $m['name'];
				
		foreach ($menus as $key=>$v) {
			if (empty($v['parent']))
				continue;
				
			if ($v['parent'] != $parent )
				continue;
			$mdb[$key] = $v;
		}		
		return $mdb;
	}
	
	
	/**
	 * 返回顶层菜单
	 *
	 */
	protected function getTopMenus($menus)
	{
		$mdb = array();
		foreach ($menus as $key=>$v) {
			if (!empty($v['parent']))
				continue;
			$mdb[$key] = $v;
		}		
		return $mdb;
	}
	
	/**
	 * 合并所有apps菜单，按用户权限返回用户菜单
	 *
	 * @param mixed $key This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function filterMenus($menus, $pkey='all', $ifexclude=false)
	{
		$cf = get_config();
		
		$_mdb = array();		
		$mdb = array();
		
		if (!$pkey) {
			$mdb = $this->getTopMenus($menus);				
		} else if ($pkey == 'all') {
			$mdb = $menus;
		} else {
			$mdb = $this->getSubMenus($menus, $pkey);
		}
		
		$ss = $this->getSession();
			
		//过滤
		foreach ($mdb as $key=>$v) {
			$name = $v['name'];
			$pid = $v['pid'];
			//$level = intval($v['level']);
			
			if (isset($v['hidden']) && $v['hidden'])
				continue;
			if (isset($v['level']) && ($v['level'] & $this->_default_level_mask) === 0) {
				continue;
			}
			
			if ($ifexclude && isset($v['is_exclude']) && $v['is_exclude'])
				continue;				
				
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN2 ....");
			if ($ss->hasPrivilegeOf($pid, 0)){//菜单项权限
				$_mdb[$key] = $v;				
			}  else {
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $v);
			}
				
		}

		return $_mdb;
	}
	
	
	public function getCurrentMenuTree($activeItem='', $options=array())
	{
		
		$allmenus = $this->getMenus();
		
		$menus = $this->filterMenus($allmenus, '');	
		
		//安位置过滤
		if (isset($options['pos'])) {
			$mdb = array();
			$pos = $options['pos'];
			foreach ($menus as $key=>$v) {
				if (!$v['pos']) 
					continue;
				$pdb = explode(',', $v['pos']);
				if (in_array($pos, $pdb))
					$mdb[$key] = $v;
			}
			$menus = $mdb;
		}
		if (isset($options['keys']) && is_array($options['keys'])) {
			$mdb = array();
			$keys = $options['keys'];
			foreach ($menus as $key=>$v) {
				if (in_array($key, $keys))
					$mdb[$key] = $v;
			}
			$menus = $mdb;
		}

		
		//排序
		array_sort_by_field($menus, "sort", false);				

		foreach ($menus as $key=>&$v) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'key='.$key);
		
			//子菜单
			$submenus =  $this->filterMenus($allmenus, $key);
			
			//排序
			array_sort_by_field($submenus, "sort", false);				
			
			if ($activeItem && isset($submenus[$activeItem])) { //活动项
				$v['active'] = true;
				$submenus[$activeItem]['active'] = true;				
			}
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$key='.$key.',$activeItem='.$activeItem, $submenus);
			
			$v['children'] = $submenus;	
		}		
		return $menus;		
	}
	
	
	protected function switchIfTop($component, $tname='')
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN component=".$component);
		$menus = $this->getMenus();
		
		$foundCom = "";
		$m = array();
		if (isset($menus[$component]))
			$m = $menus[$component];
		if (!$m || $m['parent'] || ($m['task'] && $m['task'][$tname]))
			return $component;
			
		foreach($menus as $k=>$v) {
			if ($v['parent'] == $component) {
				if ($v['hidden'])
					continue;
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "########## IN2 component=".$component);	
				if (!$this->hasPrivilegeOf($v['component'])) 
					continue;
				if (!$foundCom)
					$foundCom = $v['component'];
				if ($v['is_default']) {
					$foundCom = $v['component'];
					break;
				}
			}
		}	
		if (!$foundCom)
			$foundCom = $component;
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "foundCom=".$foundCom);
		
		return $foundCom;
	}
	/* ==============================================================================================
	 * cache functions
	 *	
	 * ============================================================================================*/
	public function cacheMenus($lang='')
	{
		return false;
	}
	
	public function cache()
	{
		$this->cacheI18n($this->_lang);	
		$this->cacheMenus($this->_lang);	
		$this->cacheModels();	
	}
	
	
	/* ==============================================================================================
	 * session functions
	 *	
	 * ============================================================================================*/
	protected function initSession()
	{
		$this->_session = Factory::GetUser();
	}
	
	protected function getSession()
	{
		if (!$this->_session) 
			$this->initSession();
		return $this->_session;
	}
	
	public function getUserInfo()
	{
		$ss = $this->getSession();
		if (!$ss)
			return false;
			
		$res = $ss->getCurrentUserInfo();
		return $res;
	}
	
	
	/* ==============================================================================================
	 * service functions
	 *	
	 * ============================================================================================*/
	
	protected function get_localwebservice_last_timestamp($timeout)
	{
		if (file_exists(RPATH_CACHE.DS."cach_localwebservice_last_timestamp_".$this->_name.".$timeout"))
			return file_get_contents(RPATH_CACHE.DS."cach_localwebservice_last_timestamp_".$this->_name.".$timeout");
		else 
			return 0;
	}
	
	protected function set_localwebservice_last_timestamp($timeout, $ts)
	{
		file_put_contents(RPATH_CACHE.DS."cach_localwebservice_last_timestamp_".$this->_name.".$timeout", $ts);
	}
	
	
	protected function check_localwebservice_timeout($timeout)
	{
		
		$last_ts = intval($this->get_localwebservice_last_timestamp($timeout));
		$ts = time();		
		if ($ts - $last_ts < $timeout) {//执行一次
			return false;
		} 		
		$this->set_localwebservice_last_timestamp($timeout, $ts);
		return true;
	}
	
	
	/* ========================================================
	 * install and uninstall functions
	 * =======================================================*/
	
	////////////////////////////////// app protect methods ///////////////////////////////////////////
	public function install($options=array())
	{
		return true;
	}
	
	public function installForUpdate($options=array())
	{
		return true;
	}
		
	public function uninstall()
	{
		return true;
	}
	
	public function isLogin()
	{
		$ss = $this->getSession();
		if (!$ss)
			return false;
		
		$res = $ss->isLogin();
		
		return $res;
	}
	
	
	public function login($params=array(), &$options=array())
	{
		$ss = $this->getSession();
		if (!$ss) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no session!");		
			return false;
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $this->_name,  $ss);
		//if (!$ss->isLogin()) {
		if (($res = $ss->login($params, $options)) !== true) {		
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "user login failed! res=$res");		
				return $res;
			}
		//}
		return true;
	}
	
	
	public function logout()
	{
		$ss = $this->getSession();
		if (!$ss)
			return false;
		
		
		$ss->logout();
		
		setMsg("str_user_logout_ok");		
	}
	
	
	/* ========================================================
	 * run functions
	 * =======================================================*/
	protected function setRunApp($aname)
	{
		if (!$aname)
			return false;
			
		$this->_aname = $aname;
		$this->_rundir = RPATH_APPS.DS.$aname;
		
		return true;
	}
	
	protected function checkLicense(&$aname, &$options=array())
	{
		if (!$aname)
			return false;
			
		if ($aname != $this->_name) {
			$app = Factory::GetApp($aname);
			$app->checkLicense($aname, $options);
		}
		
		return false;
	}
	
	protected function checkIfApp($name)
	{
		if (!$name)
			return false;
			
		if ($name == $this->_name) //是自己，eg：index, admin
			return true;
			
		return $this->isApp($name);
	}
	
	protected function checkIfComponentOf($name, &$appname='')
	{
		if (!$name)
			return false;
			
		if ($appname == $this->_name) {
			$res = (is_file(RPATH_APPS.DS.$appname.DS.'components'.DS.$name.'.php'))?true:false;
			if ($res) {	//是组件
				return true;
			}		
		}
		
		$menus = $this->getMenus();
		if (isset($menus[$name])) {
			$m = $menus[$name];
			if (!empty($m['path']) && file_exists($m['path'])) {
				//if (isset($m['app']) && $this->isApp($m['app']) ) {
					$appname = $m['app'];
				//}
				
				//是组件			
				return true;
			}
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT  ... NOT component!! name=".$name.", appname=$appname");
		
		return false;
	}
	protected function checkIfTask($name, $componentinfo)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...", $name, $componentinfo);
		if (!$name || !$componentinfo)
			return false;
			
		return $name == $componentinfo['name'] || isset($componentinfo['task'][$name])?true:false;
	}
	
	
	protected function checkIfApi($name, &$cname='api', &$aname='', $force=true)
	{
		if (!$cname || $cname == 'api' || $force) {
			$isapi = $this->isApi($name);
			if ($isapi) {
				$cname = $this->_apidb[$name]['cname'];
				$aname = $this->_apidb[$name]['aname'];	
				return true;				
			}	
		}
		
		return false;
	}
	

	
	/**
	 * initAppComponent
	 *
	 * @param mixed $app This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function initAppComponent(&$options=array(), $rewrite_enable=false)
	{
		$menus = $this->getMenus();
		
		$tname = $options['tname'];	
		$oname = $options['oname'];	
		
		$vpath = isset($options['vpath'])?$options['vpath']:array();
		
		//rlog(RC_LOG_DEBUG, __FILE__,__LINE__, __FUNCTION__, $options);
		
		$newvpath = array();
		
		//试着探测是否为:/<APP>/<COMPONENT>/<TASK>
		$i = 0;
		$isapi = false;
		$cinfo = array();
		$reqFlags = 0;
				
		//check appname
		/*$appName = $options['appname'];
		if ($this->checkIfApp($appName)) {
			$aname = $appName;		
			//rlog(RC_LOG_DEBUG, __FILE__,__LINE__, __FUNCTION__, "appname '$appName' is app!!");	
			$reqFlags |= REQ_FLAG_1_A;
		}
		//检查是否为组件, eg: /rc/content?id=1
		if ($this->checkIfComponentOf($appName, $aname)) { //应用与组件名同名eg: /index/index
			$cname = $appName;
			$reqFlags |= REQ_FLAG_1_C;
			//rlog(RC_LOG_DEBUG, __FILE__,__LINE__, __FUNCTION__, "appname '$appName' is component##");	
		}
		
		//?c=list&id=1
		if (!empty($options['cname']))
			$cname = $options['cname'];
		//rlog(RC_LOG_DEBUG, __FILE__,__LINE__, __FUNCTION__, "cname '$cname',aname=$aname #################### ");	
		if ($reqFlags == 0) { 
			if ($this->checkIfApi($appName, $cname, $aname)) {
				$tname = $appName;
				$isapi = true;
				//$newvpath[] = $appName;
				//rlog(RC_LOG_DEBUG, __FILE__,__LINE__, __FUNCTION__, "appname '$appName' is API ###");	
				
				$reqFlags |= REQ_FLAG_1_T;
			}
		}*/
		
		$aname = $this->checkIfApp($options['aname'])?$options['aname']:$this->_name;
		
		$nr = count($vpath);		
		for($i=0; $i<$nr; $i++) {
			$val = trim($vpath[$i]);
			if ($val === "") // "0"?
				continue;
				
			$found = false;
			if ($i < 3) {				
				if ($i === 0) {
					if ($nr > 1) {//第1个不是组件，或超过2项，则检						
						if ($this->checkIfApp($val)) {//eg: /rc/index/index/
							$aname = $val;
							$found = true;	
							$reqFlags |= REQ_FLAG_2_A;		
						}				
						if ($this->checkIfComponentOf($val, $aname)) { //应用与组件名同名eg: /rc/index/index
							$cname = $val;
							$found = true;
							$reqFlags |= REQ_FLAG_2_C;		
						}
					} elseif($this->checkIfComponentOf($val, $aname)) { // eg: index.php/a
						$cname = $val;
						$found = true;
						$reqFlags |= REQ_FLAG_2_C;	
					} 
				} else if ($i == 1) {
					$aname2 = $aname;
					if ($this->checkIfComponentOf($val, $aname2)) { //应用与组件名同名eg: /rc/login/login
						$found = true;
							
						if (($reqFlags&REQ_FLAG_2_C) == REQ_FLAG_2_C && $this->checkIfTask($val, $menus[$cname])) {//eg: /login/login 第二个可能是[任务]名称
							$newvpath[] = $val;								
						} else {
								rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "## WARNING: old cname=$cname, new cname='$val'!", $reqFlags);
								
							$cname = $val;	
							$aname = $aname2;								
							$reqFlags |= REQ_FLAG_3_C;					
						}
					}
						
				}
				
				//
				if ($this->checkIfApi($val, $cname, $aname, !$options['rewrite'])) {
					$tname = $val;
					$found = true;
					$isapi = true;
					//$newvpath[] = $val;
				} else if ($nr == 1 && (($reqFlags&REQ_FLAG_1_C) != REQ_FLAG_1_C)) {
						if ($this->checkIfComponentOf($val, $aname)) { 
						
							//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "old cname=$cname, new cname='$val'!");
							$cname = $val;
							$found = true;
							$reqFlags |= REQ_FLAG_2_C;		
						}					
				}
			}
			
			if (!$found) {
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "unknown '$val'!");
				$newvpath[] = $val;
			}
		}
		
		if (empty($cname)) {
			
			$app = Factory::GetApp($aname);	
			$cname = !empty($options['cname'])?$options['cname']:$app->getDefaultComponent();
			
			if (!$this->checkIfComponentOf($cname, $aname)) {	
				$options['use_default_component'] = true;		
			}
			
		} else {
			
			if ($nr == 0 && $aname) { //admin.php
				if ($this->checkIfComponentOf($cname, $aname)) {	
					//	var_dump($aname);	exit;			
				}
			}
		}
		
		//fixed $options['base']
		if ($options['rewrite'] && (($reqFlags&REQ_FLAG_1_C) == REQ_FLAG_1_C)) { //reset 
			$options['base'] = $options['basepath'];
		} 
		
		
		//API
		if ($isapi) {
			$options['use_api_component'] = true;
			//api不需要再加	
			$options['_base'] = $options['base'];
			$options['_baseurl'] = $options['baseurl'];		
		}  else {
			$options['_base'] = $options['base'].'/'.$cname;
			$options['_baseurl'] = $options['baseurl'].'/'.$cname;
		}	
		
		
		
		$options['aname'] = $aname;
		$options['cname'] = $cname;
		$options['tname'] = $tname;
		$options['oname'] = $oname;
		$options['vpath'] = $newvpath;
		$options['isapi'] = $isapi;
		//$options['_cname'] = $_cname;
		
		$options['component'] = $cname;		
		$options['task'] = $tname;	
		
		
		$options['_basename'] = $options['base']; //兼容
		$options['_basenameurl'] = $options['_rooturl'].$options['_basename']; //兼容
		
		//$options['_baseuri'] = $options['_base'].'/'.$tname;
		if ($options['is_default_index'] && $rewrite_enable) { //强开重定向
			$options['base'] = $options['basepath'];
		}
		
		if (isset($menus[$cname])) { //组件信息
			$options['componentinfo'] = $menus[$cname];	
		} else {
			$options['componentinfo'] = array();	
		}
		//rlog(RC_LOG_DEBUG, __FILE__,__LINE__, __FUNCTION__, $options, $vpath, $newvpath);exit;
		
		
		$this->checkLicense($aname, $options);
		
		$this->setRunApp($aname);
		
		return true;
	}
	
	

	protected function setAppTemplateDir($tplname, &$options=array())
	{
		empty($tplname) && $tplname = 'default';
		
		$options['tdir'] = !is_dir($this->_rundir.DS.'templates'.DS.$tplname)?
		$this->_rundir.DS.'templates'.DS.'default':$this->_rundir.DS.'templates'.DS.$tplname;	
		
		$options['app_tdir'] = $this->_appdir.DS.'templates'.DS.'default';			
		if ($tplname != 'default')	
			$options['cfg_tdir'] = RPATH_TEMPLATES.DS.$tplname;	
			
		//$options['system_tdir'] = RPATH_TEMPLATES.DS.'default';	
	}
		
	protected function initAppTemplate(&$options=array())
	{
		$cf = get_config();	
		$this->setAppTemplateDir($cf['tplname'], $options);			
	}
	
	
	protected function initAppI18n(&$options=array())
	{
		if (!empty($options['lang'])) {
			$lang = $options['lang'];
		} else {
			$cf = get_config();
			$lang = isset($cf['lang'])?$cf['lang']:$this->_lang;
			$options['lang'] = $lang;
		}
		$this->_lang = $lang;
	}	
	
	protected function probeModels($appname, &$mdb)
	{
		//编历目录
		$dir = RPATH_APPS.DS.$appname.DS."models";
		$udb = s_readdir($dir);
		if (!$udb)
			return false;
		
		foreach ($udb as $key=>$v) {
			$modname = $v;
			$extname = s_extname($modname);
			if ($extname != 'php')
				continue;
				
			$mdb[$modname] = array('appname'=>$appname, 'modname'=>$modname, 'modpath'=>$dir.DS.$v);
		}
	}
	
	
	protected function cacheModels()
	{
		$apps = Factory::GetApps();
		$mdb = array();
		foreach ($apps as $key=>$v) {
			if ($key == 'index')
				continue;
				
			$this->probeModels($key, $mdb);			
		}
		
		//skip current app modules
		if ($this->_name != 'index')//前端不缓存
			$this->probeModels($this->_name, $mdb);
			
		cache_array('models', $mdb);
	}
	
	protected function initModels()
	{
		if (!file_exists(RPATH_CACHE.DS.'models.php')) 
			$this->cacheModels();		
	}
	
	protected function initApiRequest($options)
	{
		$this->_session = Factory::GetUser();
	}
	
		
	protected function init(&$options=array())
	{
		$cf = get_config();	
		//语言
		$this->initAppI18n($options);
		
		$this->initModels();
		
		$this->initSession();
								
		$this->initAppComponent($options, $cf['rewrite_enable']);		
		//模板
		$this->initAppTemplate($options);		
		
		$options['appdir'] = $this->_appdir;
		$options['rundir'] = $this->_rundir;
				
		//logo
		$logo = !empty($cf['logo'])?$cf['logo']:$options['_dstroot'].'/img/logo-default.png';
		
		$options['_logo'] = $logo;
						
		$options['_appname'] = $this->_name;
		$options['_lang'] = $this->_lang;
		$options['_tplname'] = $this->_tplname;
		$options['_thename'] = $cf['thename'];
		
		$options['_layout'] = $cf['layout'];
		$options['_layout_container'] = $cf['layout']=='boxed'?'container':'';
		$options['_enable_simple_layout'] = $cf['enable_simple_layout'];
		$options['_showrankid'] = $cf['show_rankid'];
		
		//dataurl
		if (isset($cf['datauri']) && $cf['datauri'])
			$dataurl = $options['_rooturl'].$cf['datauri'];
		else 
			$dataurl = $options['_weburl'].'/data';						
		$options['dataurl'] = $dataurl;		
		
		if (isset($cf['datadir']) && $cf['datadir'])
			$datadir = $cf['datadir'];
		else 
			$datadir = RPATH_PUBDATA;						
		$options['datadir'] = $datadir;
		
				
		//system variables
		$appcfg = $this->getAppCfg();
			
		$options['sys_name'] = $this->getSysName();
		$options['sys_version'] = $this->getSysVersion();
		$options['sys_lang'] = $this->_lang;
		$options['sys_title']  = isset($cf['title'])? $cf['title'] : i18n('str_system_title', $appcfg['description']);
		$options['sys_app_title'] = i18n($options['_appname']);
		$options['sys_component_name'] = i18n('menu_'.$options['cname']);
		
		//$sys_component_name
		
		$options['sys_copyright_corp'] = i18n('str_system_copyright_corp');
		$options['sys_current_year'] = tformat_current("Y");
		$options['sys_current_date'] = tformat_current("Y-m-d");
		$options['sys_current_time'] = tformat_current("H:i:s");	
		
		$options['sys_copyright'] = $cf['copyright'];
		$options['sys_description'] = $cf['description'];
		$options['sys_website'] = $cf['website'];
		
		//api
		if ($options['isapi'])
			$this->initApiRequest($options);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT ....");
		$options['hasPrivilege'] = false;
		return true;		
	}


	/**
	 * 任务分配, 处理选项
	 *
	 * @return mixed This is the return value description
	 *
	 */
	protected function dispatch(&$options=array())
	{
		return false;
	}

	
	/**
	 * 呈现，渲染
	 *
	 * @return mixed This is the return value description
	 *
	 */
	protected function render(&$options = array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $options);
		$menus = $this->getMenus();
		$name = $options['component'];
		$params = isset($menus[$name])?$menus[$name]:array();
		$params['appname'] = $this->_name;
		$params['appdir'] = $this->_appdir;
		$params['rundir'] = $this->_rundir;
		
		rlog(RC_LOG_DEBUG, __CLASS__, __FUNCTION__, __LINE__,  "{$options['method']} {$options['uri']} | REQU: appname={$this->_name}, component=$name, aname=".$options['aname'].",cname=".$options['cname'].',tname='.$options['tname']);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		
		$com = Factory::GetComponent($name, $params);
		
		$this->_activeComponent = $com;
		$data = $com->render($options);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT....");
		echo $data;
	}
	
	
	//////////////////////////////////////////// app public methods /////////////////////////////////
	public function run($options=array())
	{
		//session_start会使响应头加入： 
		//Cache-Control	no-store, no-cache, must-reval…te, post-check=0, pre-check=0
		//session_cache_limiter控制不输出响应头
		session_cache_limiter( "private, must-revalidate" );
		session_start();
		
				
		$this->init($options);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN1 ....");
		$this->dispatch($options);					
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN2....");
		
		$this->render($options);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN3....");
		
		
		return true;
	}

	
}