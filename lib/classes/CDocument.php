<?php
/**
 * @file
 *
 * @brief 
 * 
 * 文档管理类
 * 
 * @copyright

 * Copyright (c), 2024, relaxcms.com
 *
 */

class CDocument extends CObject
{
	function __construct($name = "default")
	{
		$this->_name = $name;
	}
	
	function CTemplate($name="default")
	{
		$this->__construct($name);
	}
	
	static function &GetInstance( $name = 'default' )
	{
		static $instance;
		if (!is_object($instance)) {
			$instance = new CDocument($name);
		}
		return $instance;
	}
	
		
	public function getDocumentList($appname, &$docmenus=array())
	{
		$ddb = array();

		$app = Factory::GetApp($appname);
		if (!$app) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no app '$appname'!");
			return false;
		}
		$appcfg = $app->getAppcfg();		
		
		$file = RPATH_APPS.DS.$appname.DS."docs".DS."manual.php";
		if (file_exists($file)) {
			require $file;
			if ($docdb) {
				$_docdb = array();
				foreach ($docdb as $k2=>$v2) {
					if (!isset($v2['parent'])) {
						$v2['parent'] = $appname;
					}
					$v2['linkurl'] = $v2['htmurl'];
					$_docdb[$k2] = $v2;
				}
				$ddb = array_merge($ddb, $_docdb);	
			}	
		}	
		
		//一级菜单
		foreach ($ddb as $key=>$v) {
			if (isset($v['parent'])) {
				$pkey = $v['parent'];
				if (!empty($ddb[$pkey])) {
					$docmenus[$pkey] = $ddb[$pkey];
				}
			}
		}
		if (!$docmenus) {
			$docmenus['default'] = array(
				'name'  => 'default',
				'title' => '操作手册',
				'class' => 'icon-folder',
			);
		}

		//二级
		$_ddb = array();

		foreach ($ddb as $key=>$v) {
			if (isset($docmenus[$key])) //跳过
				continue;

			//找到父节点
			$pkey = isset($v['parent'])?$v['parent'] : 'default';
			if (isset($docmenus[$pkey])) {
				$pitem = $docmenus[$pkey];
			} else {				
				$pkey = 'default';
				$pitem = $docmenus[$pkey];
				$v['parent'] = $pkey;
			}

			$v['target'] = ' ';
			
			$_ddb[$key] = $v;
			
			
			$children = isset($pitem['children'])?$pitem['children']:array();
			$children[$key] = $v;
			$pitem['children'] = $children;
			$docmenus[$pkey] = $pitem;
		}
				
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $docmenus, $_ddb);
					
		return $_ddb;
	}
	
	protected function t2docOne($menus, $params, $tplfile, $options)
	{
		$name = $params['name'];	
			
		$outFile = RPATH_DIST.DS.$params['htmurl'];
		$htmlFile = ($tplfile && is_file($tplfile))?$tplfile:$outFile;
		if (!is_file($htmlFile)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no HTML '$htmlFile'!");
			return false;
		}
		
		$content = s_read($htmlFile);
		if (!$content) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no content!");
			return false;
		}
				
		//查找
		//SIDEBAR
		$matches = array();
		$res = preg_match_all("/<!--\s*[\s]*BEGIN SIDEBAR MENU\s*[\s]*-->(.+)<!--\s*[\s]*END SIDEBAR MENU\s*[\s]*-->/isU", $content, $matches);
		if (!$res || count($matches[1]) != 1) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no tag of 'BEGIN SIDEBAR MENU'!", $matches, $content);
			return false;
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $matches);
		//set active of menus
		$menus[$params['parent']]['active'] = true;
		$menus[$params['parent']]['children'][$name]['active'] = true;
		
		//module
		$attribs = array();
		$options['menus'] = $menus;
		$mod = Factory::GetModule('sidebar', $attribs);
		$data = $mod->render($options);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $data);		
		$content = str_replace($matches[0], $data, $content);

		//pdf
		$pdf = $options['_webroot'].'/'.$params['pdfurl'];
		$content = str_replace('#pdf', $pdf, $content);

//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $outFile);

		$res = s_write($outFile, $content);	
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call s_write failed!");
			return false;
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $res, $content);

		return $res;		
	}
	
	/**
	 * t2doc 
	 * 
	 * pandoc生成的html再次转换为导航栏文档
	 *
	 * @param mixed $name This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function t2doc($appname, $name, $tplfile, $options=array())
	{
		$docmenus = array();
		$ddb = $this->getDocumentList($appname, $docmenus);
		
		$res = false;
		
		foreach ($ddb as $key=>$v) {
			if (!$name || $name == $v['name']) {
				if ($name) {
					$v['active'] = true;
				}
				$res = $this->t2docOne($docmenus, $v, $tplfile,$options);				
			}
		}		
		return $res;
	}
	
}