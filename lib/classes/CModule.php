<?php
/**
 * @file
 *
 * @brief 
 * 模块类
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CModule
{
	protected $_name;
	protected $_task;
	protected $_var = array();
	protected $_attribs = array();
	protected $_template;
	protected $_tdir;
	
	public function __construct($name, $options)
	{
		$this->_name = $name;
		$this->_attribs = $options;
		
		if (isset($options['tpl']))
			$this->_template = $options['tpl'];
		else 
			$this->_template = $options['tpl'] = $name;
		
		$this->_tdir = dirname($options['classfile']);
		
		$this->_init();
	}
	
	public function CModule($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	//创建
	static function &GetInstance($name, $options)
	{
		static $instances;
		
		if (!isset( $instances )) 
		{
			$instances = array();
		}
		
		$sig = serialize(array($name, $options));		
		if (empty($instances[$sig]))
		{	
			$class = "";
			$cn = explode('_', $name);
			foreach($cn as $key=>$v) {
				$class .= ucfirst($v);
			}
			$class = $class.'Module';
			
			//rundir
			$rundir = isset($options['rundir'])?$options['rundir']:'';
			$classfile =$rundir.DS.'modules'.DS.$name.DS.$name.'.php';
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "classfile =$classfile");
			if (file_exists($classfile)) {//本地应用目录下的modules优先
				require_once($classfile);
			} else {
				$classfile = RPATH_MODULES.DS.$name.DS.$name.".php";
				if (file_exists($classfile)) {				
					require_once $classfile;	
				} else {//内置
					$classfile = RPATH_LIBMODULES.DS.$name.DS.$name.".php";
					if (file_exists($classfile)) {				
						require_once $classfile;	
					}	
				}
			}
												
			if (!class_exists($class)) {
				$class	= "CModule";
			}
			
			$options['classname'] = $class;
			$options['classfile'] = $classfile;
			
			$instance	= new $class($name, $options);
			$instances[$sig] =& $instance;
		}
		
		return $instances[$sig];
	}
	
	protected function _init()
	{
		return false;
	}
	
	protected function getModelName()
	{
		$modname = isset($this->_attribs['modname'])?$this->_attribs['modname']:'';		
		!$modname = isset($this->_var['modname'])?$this->_var['modname']:'content';		
		return $modname;
	}
	
	protected function show(&$options=array())
	{
		if (!isset($this->_attribs['mid'])) {
			$mid = 'mod'.randName();
			$this->assign('mid', $mid);	
		}
		return true;
	}

	protected function setMessageBox($options, $level=null)
	{
		//!$options['msg_backurl'] =  $options['msg_backurl'] =  $this->_base;
		foreach($options as $key=>$v) 
			$this->_attribs[$key] = $v;
		
		$this->_template = 'messagebox';
	}

	protected function showMessageBox($msg, $backurl=null, $target="_self", $ext=null, $type="error")
	{
		$msg = i18n($msg);
		$msg_alert_types = get_i18n('msg_alert_types');
		
		$options = $msg_alert_types[$type];
		$options['msg_text'] = $msg;
		$options['msg_backurl'] =  $backurl;
		$options['msg_target' ] = $target;
		$options['msg_ext' ] = $ext;
		$options['msg_type' ] = $type;

		$this->setMessageBox($options);
	}


	protected function showError($msg, $backurl="", $target="_self", $ext=null)
	{
		$this->showMessageBox($msg, $backurl, $target, $ext, "error");
	}
	
	protected function loadTemplate($options = array())
	{
		$i18n = get_i18n();
		$T = $i18n;
		//t
		/*if (isset($i18n['t_'.$this->_name]))
			$t = $i18n['t_'.$this->_name];	
		else
			$t = array();*/
			
		$t = $options['_i18ndb'];
			
		$task = $this->_task;	
		
		if (isset($i18n['str_'.$task]))
			$str_task = $i18n['str_'.$task];
		else	
			$str_task = $task;	
		
		//IO参数
		extract($options);
		
		//展开数组
		extract($this->_attribs);
		//展开变化
		extract($this->_var);
		
		$tpl_filename = $this->_template.'.htm';
		$tpl_pathname = $this->_tdir.DS.$tpl_filename;
		if (!file_exists($tpl_pathname)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING :  no tpl '$tpl_pathname' of module '{$this->_name}'!");
			$tpl_pathname = RPATH_MODULES.DS.'default'.DS.$tpl_filename;
		}
		
		$tpl = Factory::GetTemplate();		
		$cpl_file = $tpl->compileTemplate($tpl_pathname, $options, $tpl_filename);
		
		$data = "";
		
		ob_start();
		require $cpl_file;
		$data = ob_get_contents();
		$data = strim_bom($data);
		
		ob_end_clean();
		return $data;
	}
	
	protected function initI18nDB(&$options=array())
	{
		$i18nfile = RPATH_MODULES.DS.$this->_name.DS."i18n".DS.$options['_lang'].DS."i18n.php";
		if (!file_exists($i18nfile)) {
			$i18nfile = RPATH_LIBMODULES.DS.$this->_name.DS."i18n".DS.$options['_lang'].DS."i18n.php";
			if (!file_exists($i18nfile)) {
				return false;
			}
		}
		
		require $i18nfile;			
		$options['_i18ndb'] = array_merge($options['_i18ndb'], $i18n);			
		return true;
	}
	
	protected function init(&$options=array(), $cvar=array())
	{
		if ($cvar)
			$this->_var = $cvar;
			
		return false;		
	}
	
	
	
	//设置变量	
	public function set_var($key, $v)
	{
		$this->_var[$key] = $v;	
	}

	
	//设置数组
	protected function set_array($arr)
	{
		if (!is_array($arr)) 
			return false;
		
		foreach($arr as $key=>$v){
			$this->_var[$key] = $v;
		}
	}
	
	protected function get_var($key)
	{
		return $this->_var[$key];	
	}

	
	public function assign($k, $v)
	{
		$this->set_var($k, $v);
	}
	
	public function assigns($av)
	{
		foreach ($av as $k=>$v)
			$this->set_var($k, $v);
	}
	
	public function getName()
	{
		return $this->_name;
	}
	
	protected function setTpl($tname)
	{
		$this->_template = $tname;
	}
	
	
	protected function getCols()
	{
		return isset($this->_attribs['cols'])?intval($this->_attribs['cols']):2;		
	}
	
	protected function setColumn($udb)
	{
		$nr = count($udb);
		
		$nr_col = $this->getCols();
		$nr_row = ceil($nr/$nr_col);
		$col_width = floor(12/$nr_col);
		
		$this->assign('rows', $nr_row);	
		$this->assign('cols', $nr_col);	
		$this->assign('col_width', $col_width);	
		$this->assign('nr', $nr);
		
	}
	
	
	//展现
	public function render($options=array(), $cvar=array())
	{
		$this->init($options, $cvar);
		
		//加载模块 i18n
		$this->initI18nDB($options);
		
		$task = $options['task'];
		if (!method_exists($this, $task)){
			$task = 'show';
		}
		
		$this->_task = $task;
		$res = $this->$task($options);
		
		//加载模板
		$content = $this->loadTemplate($options);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT ....................");
		
		return $content;
	}		
	
}