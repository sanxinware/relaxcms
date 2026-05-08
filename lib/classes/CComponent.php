<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

/**
 * CComponent
 * 
 * 组件基类
 *
 */
class CComponent  extends CObject
{
	protected $_name;				
	protected $_options=array();

	/** 默认模型　*/
	protected $_modname = null;
	
	/**
	 * 方法
	 *
	 * @var mixed 
	 *
	 */
	protected $_task = '';
	
	/**
	 * 输出变量，模板直接使用
	 *
	 * @var mixed 
	 *
	 */
	protected $_var = array();
	
	/**
	 * ID标识
	 *
	 * @var mixed 
	 *
	 */
	protected $_id;
	
	/**
	 * UUID
	 *
	 * @var mixed 
	 *
	 */
	protected $_uuid;
	
	public function __construct($name, $options=array())
	{
		$this->_name = $name;		
		$this->_modname = $name;		
		$this->_options = $options;
		
		$this->_initModel();			
		$this->_init();			
	}
	
	public function CComponent($name=null, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	
	protected function _initModel()
	{
		if (isset($this->_options['modname'])) {
			$this->_modname = $this->_options['modname'];
		} 	
	}
	
	protected function _init()
	{
		return true;
	}
		
	//创建
	static function &GetInstance($name, $options=array())
	{
		static $instances;
		
		if (!isset( $instances )) 
			$instances = array();
		
		$sig = md5($name.serialize($options));		
		if (empty($instances[$sig])) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $options);
			if (isset($options['appdir']))  {
				//$appname = $options['appname'];
				$classfile  = $options['rundir'].DS.'components'.DS.$name.'.php';				
				if (file_exists($classfile)) {
					require_once($classfile);		
				} else {
					
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no classfile '$classfile'!");
					
					$classfile = $options['appdir'].DS.'components'.DS.$name.'.php';
					
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "try to use '$classfile'!");
					
					if (file_exists($classfile)) {
						require_once($classfile);
					} else {
						//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no classfile '$classfile' use lib components !");
						$classfile = RPATH_COMPONENTS.DS.$name.'.php';
						if (file_exists($classfile)) {
							require_once($classfile);
						} else {
							rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no classfile '$classfile'!");
						}
					}
				}
			} else {				
				$classfile = RPATH_COMPONENTS.DS.$name.'.php';
				if (file_exists($classfile))
					require_once($classfile);
				
			}
			
			
			$class = "";
			$arrs = explode('_', $name);
			
			foreach($arrs as $key=>$v) {
				$class .= ucfirst($v);
			}
			$class = $class.'Component';			
			if (!class_exists($class)) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: no class '$class' use default 'CComponent'!");
				$class = 'CComponent';				
			} 
			
			$options['class'] = $class;
			$options['classfile'] = $classfile;
			//$options['appname'] = $appname;
			
			$instance	= new $class($name, $options);		
			$instances[$sig] = $instance;
		}
		
		return $instances[$sig];
	}
	
	
	
	
	/* ==============================
	 * Utility Helper functions
	 * =============================*/
	protected function getModelName()
	{
		$modname = $this->request('modname', $this->_modname);
		!$modname && $modname = $this->_modname;
		return $modname;
	}
	
	protected function getModel()
	{
		$modname = $this->getModelName();
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "modname=$modname");
		return Factory::GetModel($modname);
	}
	
	protected function setTpl($tpl)
	{
		$this->_tpl = $tpl;
	}
	protected function resetTpl()
	{
		$this->_tpl = $this->_name;
	}
	
	
	protected function getTpl($tpl)
	{
		return $this->_tpl;
	}
	
	protected function initActiveTab($nr, $force_active_id=-1)
	{
		if ($force_active_id < 0) {
			$name = 'atid'.$this->_name.$this->_task;
			$active_table_id = isset($_COOKIE[$name])? intval($_COOKIE[$name]):0;
			if (isset($_REQUEST['atid'])) {
				$active_table_id = intval($_REQUEST['atid']);
			}
		}		
		else 
			$active_table_id = $force_active_id;
		
		if ($active_table_id >= $nr || $active_table_id <0)
			$active_table_id = 0;
		
		
		
		$navtabs = array();
		for($id=0; $id<$nr; $id++) {
			$v = array();
			
			$v['id'] = $id;
			$v['title'] = 'tab'.$id;
			if ($active_table_id == $id) {
				$v['active'] = 'active';
				$v['in'] = 'active in';				
			} else {
				$v['active'] = '';
				$v['in'] = '';		
			}
			
			$navtabs[$id] = $v;
		}	
		return $navtabs;
	}

	protected function setActiveTab($nr, $force_active_id=-1, $selector='')
	{
		$tabs = $this->initActiveTab($nr, $force_active_id);
		foreach ($tabs as $key => $v) {
			$this->assign('navtab'.$v['id'], $v);
		}		
		
		if ($selector) {
			$sdb = get_i18n($selector);
			foreach ($tabs as $key => &$v) {
				if (isset($sdb[$v['id']]))
					$v['title'] = $sdb[$v['id']];
			}
		}
		
		$this->assign('navtabs', $tabs);
		
		return $tabs;
	}
	
	public function getActiveTab()
	{
		return $this->_var['navtabs'];
	}
	
	/* ==============================
	 * Params Helper functions
	 * =============================*/
	protected function assign($key, $v=null)
	{
		$old = isset($this->_var[$key])?$this->_var[$key]:null;
		if ($v !== null)		
			$this->_var[$key] = $v;	
			
		return $old;
	}
	
	public function assigns($av)
	{
		foreach ($av as $k=>$v)
			$this->assign($k, $v);
	}
	
	protected function assignArray($arr)
	{
		if (!is_array($arr)) 
			return false;
		
		foreach($arr as $key=>$v){
			$this->_var[$key] = $v;
		}
	}
	
	protected function assignSession($key, $v)
	{
		$this->assign($key, $v);	
		$_SESSION[$key] = $v; 
	}
	
	
	protected function assignSelectEnable($name, $val=0)
	{
		$this->assign($name.'_select', get_common_select('enable', $val));
	}
	
			
	protected function setParams($params=array())
	{
		$this->assign("params", $params);
	}
	
	protected function getParams(&$params=array())
	{
		$params = get_var("params", array());
		if (!$this->checkParams($params)) {
			$this->setParams($params);
			return $params;
		}			
		return $params;
	}
		
	protected function checkParams(&$params) 
	{
		return true;
	}
	
	protected function request($key, $default='')
	{
		$val = get_var($key, $default);
		$this->_var[$key] = $val;
		return $val;
	}
	
	protected function requestInt($key, $default=0)
	{
		$v = $this->request($key, $default);
		return intval($v);
	}
	
	protected function requestBool($key, $default=true)
	{
		$v = $this->request($key, $default?'true':'false');
		return $v == 'true' || $v === true;
	}	
		
	protected function get_int($key, $default=0)
	{
		return $this->requestInt($key, $default);
	}
		
	protected function get_bool($key, $default=true)
	{
		return $this->requestBool($key, $default);
	}	
	
	
	
	
	/* ==============================
	 * task functions
	 * =============================*/
	protected function show(&$options=array())
	{		
		$this->_tpl = $this->_name.'_show';
		return false;
	}

	
	
	protected function add(&$options=array())
	{
		$this->_tpl = $this->_name.'_add';
		return false;
	}
	
	
	protected function edit(&$options=array())
	{
		$this->_tpl = $this->_name.'_edit';
		return false;
	}
	
	protected function detail(&$options=array())
	{
		$this->_tpl = $this->_name.'_detail';
		return false;
	}

	protected function del(&$options=array())
	{
		return false;
	}
	
	protected function hasPrivilegeOf(&$options=array())
	{
		if ($options['hasPrivilege'] == true)
			return true;
			
		if (!hasPrivilegeOf($this->_name, $options['tname'])) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no privilege of '{$this->_name} > {$options['tname']}' for '{$options['_uid']}'!");
			return false;
		}
		$options['hasPrivilege'] = true;
		//$methods = get_class_methods($this);  
		//rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, $methods);
		
		return true;		
	}
	
	protected function noprivilege(&$options=array())
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no privilege!!");
		header("HTTP/1.1 403 No Permission!");
		$this->setTpl('403');
		return false;	
	}

	protected $_vpath_args_pos = 0;		
	protected function initTask(&$options=array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN ... ",$options);
		//$tname
		$options['_isdefaulttask'] = false;				
		$tname = isset($options['tname'])?$options['tname']:'';
		if ($tname && method_exists($this, $tname)) 
			return $tname;			
	
		if (isset($options['vpath'])) {
			$nr = count($options['vpath']);
			for($i=$nr-1; $i>=0; $i--) {
				$tname = $options['vpath'][$i];
				if (method_exists($this, $tname)) {
					$options['vpath_offset'] = $i+1;
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN1 ...", $tname);
					return $tname;				
				}					
			}			
		}
		
		$tname = $this->_task;
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN2 ...", $tname);
		
		if ($tname && method_exists($this, $tname)) 
			return $tname;
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT.");
		
		$options['_isdefaulttask'] = true;				
		return 'show';	
	}
	
	protected function initI18n(&$options=array())
	{
		//! 默认语言包名称
		if (isset($_COOKIE['lang']))
			$options['_lang'] = $_COOKIE['lang'];
		!isset($options['_lang']) && $options['_lang'] = 'zh_CN';
	}
		
	protected function init(&$options=array())
	{
		//component name
		$name = $this->_name;
		
		//task
		$tname = $this->initTask($options);
		
		//i18n
		$this->initI18n($options);
		
		//鉴权
		$options['tname'] = $tname;
		if (!$this->hasPrivilegeOf($options)) {
			$tname = 'noprivilege';	
		}

		$options['task'] = $tname;
		$this->_task = $tname;
		
		//id
		if (isset($options['id'])) {
			$this->_id = $options['id'];
		} else {
			$id = $this->initId($options);
			$options['id'] = $id;
		}
		
		//uuid
		if (isset($options['uuid'])) {
			$this->_uuid = $options['uuid'];
		} else {
			$uuid = $this->initUUID($options);
			$options['uuid'] = $uuid;
		}
		
		//rlog($options);
		return true;
	}
	
	protected function fini(&$options=array())
	{
		return false;
	}
	
	
	protected function initId($options)
	{
		$id = $this->requestInt('id');		
		if (!$id) {
			if (isset($options['vpath'])) {
				foreach ($options['vpath'] as $key=>$v) {
					if (is_numeric($v)) {
						$id = intval($v);
						break;
					}
			}	}
		}
		
		$this->_id = $id;		
		return $id;			
	}
	
	protected function get_id()
	{
		return $this->_id;
	}
	
	
	protected function initUUID($options)
	{
		$uuid = $this->request('uuid');		
		if (!is_md5($uuid)) {
			if (isset($options['vpath'])) {
				foreach ($options['vpath'] as $key=>$v) {
					if (is_md5($v)) {
						$uuid = $v;
						break;
					}
			}	}
		}
		
		$this->_uuid = $uuid;		
		return $uuid;			
	}
	
	protected function get_uuid()
	{
		return $this->_uuid;
	}
		
	protected function probID($options)
	{
		$id = $this->_id;		
		if (!$id) {
			foreach ($options['vpath'] as $key=>$v) {
				if (is_numeric($v)) {
					$id = intval($v);
					break;
				}
			}			
		}		
		return $id;			
	}
	
	protected function run(&$options=array())
	{
		$task = $options['task'];
		
		$res = $this->$task($options);
		return $res;
	}
			
	/**
	 * UI 呈现
	 *
	 * @param mixed $params This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function render(&$options=array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN ....");
		$this->init($options);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN1 ....");
		$res = $this->run($options);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN2 ....");
		$this->fini($options);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT ....");
		
		return $res;
	}
}