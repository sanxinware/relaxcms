<?php
/**
 * @file
 *
 * @brief 
 * 
 * 模板编译类
 * 
 * @copyright
 * Copyright (c), 2024, relaxcms.com
 *
 */

define( 'TT_UNSPEC', 0);
define( 'TT_CONTENT', 1);
define( 'TT_CATALOG', 2);
define( 'TT_VAR', 4);
define( 'TT_SYSVAR', 8);
define( 'TT_SCFVAR', 0x10);


class CTemplate extends CObject
{
	var $_name;
	var $_data = array();
	
	//'zh_CN'=>'简体中文'/*, 'zh_TR'=>'繁体中文'*/, 'en'=>'English'
	var $_i18nnames = array(
		'zh_CN'=>'简体中文',
		'zh_TR'=>'繁体中文',
		'en'=>'English',
		'ko'=>'韩文',
		'jp'=>'日语');
	
	
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
			$instance = new CTemplate($name);
		}
		return $instance;
	}
	
	///////////////////////////// 模板转换 /////////////////////////////////////////////////////////////////////
	
	
	protected function isDefaultContentTplname($tplname)
	{
		$tdb = explode('-', $tplname);
		$nr = count($tdb);
		
		$name = $tdb[0];
		if ($name != 'content')
			return false;
			
		if ($nr == 1)
			return true;
			
		if (intval($tdb[1]) > 0)
			return true;		
		return false;
	}
	
	
	protected function matchContentByTag($tag, $data, $multiLine=true)
	{
		$matches = array();	
		$_content = stripslashes($data);
		
		$pattern = "/<(\w+)([^>]*\s+{$tag}=[^>]*)\s*>(.+)<\/(\\1)>/iU";
		if ($multiLine)
			$pattern .="s";
			 
		$res = preg_match_all($pattern, $_content, $matches);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no match tag of '$tag'!");
			return false;
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $tag, $matches);
		
		$contentdb = array();		
		$nr = count($matches[0]);
		for ($i=0; $i<$nr; $i++) {
			
			$taghtml = trim($matches[0][$i]);
			$tagname = trim($matches[1][$i]);
			
			$val = trim($matches[3][$i]);
			$attr = $matches[2][$i];
			
			$attrdb = attr2array2($attr);
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $lang, $attr, $attrdb);
			if (!isset($attrdb[$tag]))
				continue;
			
			//$cid = 0;
			//if (isset($attrdb['cid']))
			//	$cid = intval($attrdb['cid']);
			
			$name = trim($attrdb[$tag]);	
			if (!$name) {
				$name = $val;
			}
			//fixed: It's Your Turn
			//$name = addslashes($name);
			
			$title = '';
			$photo = '';
			$icon = '';
			$video = '';
			$description = '';
			
			$innerHTML = $val;
			
			//<img src="img/crab.png" />
			
			if (strpos($val, "<img") !== false) {
				
				//<img
				//$res = preg_match_all("/src=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?/i", $val, $mdb);
				//if ($res) {
				//	$photo = $mdb[1][0];
				//}
				$imgattrdb = attr2array($val);
				
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "##############", $imgattrdb);
				if (isset($imgattrdb['src'])) {
					$alt = isset($imgattrdb['alt'])?$imgattrdb['alt']:'';
					if ($alt == 'icon') { //small icon
						$icon = $imgattrdb['src'];
					} else {
						$photo = $imgattrdb['src'];
					}
				}
			} 
			
			if (strpos($val, "<video") !== false) {
				//<img
				$res = preg_match_all("/src=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?/i", $val, $mdb);
				if ($res) {
					$video = $mdb[1][0];
				}
			} 
			
			$params = array();
							
			$params = $attrdb;
			$params['tag'] = $tag;
			$params['taghtml'] = $taghtml;
			$params['innerHTML'] = $innerHTML;
			
			$params['tagname'] = $tagname;			
			$params['name'] = $name;
			$params['photo'] = $photo;
			$params['icon'] = $icon;
			$params['video'] = $video;
						
			$contentdb[] = $params;
		}
		
		
		return $contentdb;
	}
	
	
	
	protected function isDefaultCatalogTplname($tplname)
	{
		$tdb = explode('-', $tplname);
		$nr = count($tdb);
		
		$name = $tdb[0];
		if ($name != 'list')
			return false;
		
		if ($nr == 1)
			return true;
		
		if (intval($tdb[1]) > 0)
			return true;		
		return false;
	}
	
	
	protected function fixedTplFoot($name, $data, $cfginfo)
	{
		$version = $cfginfo['version'];
		if ($version) {
			//theme
			$udb = $this->matchContentByTag('class', $data, false);
			if ($udb) {
				foreach ($udb as $key=>$v) {
					if ($v['name'] == 'theme') {
						//innerHTML
						$taghtml = $v['taghtml'];
						$innerHTML = trim($v['innerHTML']);
						
						if ($innerHTML) {
							$new = "UI:$name $version";
							$newtaghtml = str_replace($innerHTML, $new, $taghtml);						
							$data = str_replace($taghtml, $newtaghtml, $data);
							
						}
					}
				}
			}
		}
		
		return $data;
	}
	
	
	protected function t2tParseIncludeHeader($dirname, $filename, $tdir, $data, $config)
	{
		$is_index = $filename == 'index.html'?true:false;
		
		//<!-- BEGIN PAGECONTENT -->
		//<!-- END PAGECONTENT -->
		$matches = array();
		$res = preg_match_all("/<!--\s*[\s]*BEGIN PAGECONTENT\s*([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\s*[\s]*-->(.+)<!--\s*[\s]*END PAGECONTENT\s*[\s]*-->/isU", $data, $matches);
		
		
		
		if ($res && count($matches[1]) == 1) {
			$prefix = 'i';
			$isdefault = false;
			$nr = count($matches);
			if ($nr > 1 && $matches[1][0]) {
				$args = attr2array2(strtolower($matches[1][0]));
				foreach ($args as $k2 => $v2) {
					if ($k2 == 'prefix') {
						$prefix = trim($v2);
					}
					if ($k2 == 'default' && intval($v2) == 1) {
						$isdefault = true;
					}
				}
			}
			
			$head = $prefix.'head';
			$foot = $prefix.'foot';
			
			$pagecontent_data = $matches[3][0];
			$len = strlen($pagecontent_data);
			
			$pos = strpos($data, $pagecontent_data);
			
			$head_data = substr($data, 0, $pos);
			$foot_data = substr($data, $pos+$len);
			
			//header.htm
			$file = $tdir.DS."$head.htm";
			if ((!file_exists($file) || $is_index) && !$isdefault) {
				s_write($file, $head_data);
			}
			
			//footer.htm
			$file = $tdir.DS."$foot.htm";
			if ((!file_exists($file) || $is_index) && !$isdefault) {
				//INIT FOOT
				$footdata = $this->fixedTplFoot($dirname, $foot_data, $config['index']);
				s_write($file, $footdata);
			}
			
			//replace
			$data = str_replace($head_data, '<rdoc:include file="'.$head.'.htm" />', $data);
			$data = str_replace($foot_data, '<rdoc:include file="'.$foot.'.htm" />', $data);
		}
		
		return $data;
		
		
	}
	
	protected function t2tParseSysVar($data)
	{
		//BEGIN SYSVAR MYPROFILE
		
		//CSS
		/*$matches = array();
		$res = preg_match_all("/<!--\s*[\s]*BEGIN SYSVAR (\w+)\s*[\s]*-->(.+)<!--\s*[\s]*END SYSVAR\s*[\s]*-->/isU", $data, $matches);
		
		if ($res && count($matches[1]) > 0) {
			$nr = count($matches[1]);
			
			$new = array();
			for($i=0; $i<$nr; $i++) {
				$name = strtolower($matches[1][$i]);
				$new[] = "\$sys_$name";
			}
			
			//
			$data = str_replace($matches[0], $new, $data);
		}  */
		
		
		//theme
		$udb = $this->matchContentByTag('sysvar', $data, false);
		if ($udb) {
			foreach ($udb as $key=>$v) {
				$name = $v['name'];
				$taghtml = $v['taghtml'];
				$innerHTML = trim($v['innerHTML']);
				
				if ($innerHTML) {
					$new = '$sys_'.$name;
					$newtaghtml = str_replace($innerHTML, $new, $taghtml);						
					$data = str_replace($taghtml, $newtaghtml, $data);
					
				}
			}
		}
		
		return $data;
	}
	
	
	
	
	protected function getTags( $dom, $tagName, $attrName, $attrValue ){
		$html = '';
		$domxpath = new DOMXPath($dom);
		$newDom = new DOMDocument;
		$newDom->formatOutput = true;
		
		$filtered = $domxpath->query("//$tagName" . '[@' . $attrName . "='$attrValue']");
		// $filtered =  $domxpath->query('//div[@class="className"]');
		// '//' when you don't know 'absolute' path
		
		// since above returns DomNodeList Object
		// I use following routine to convert it to string(html); copied it from someone's post in this site. Thank you.
		$i = 0;
		while( $myItem = $filtered->item($i++) ){
			$node = $newDom->importNode( $myItem, true );    // import node
			$newDom->appendChild($node);                    // append node
		}
		$html = $newDom->saveHTML();
		return $html;
	}
	
	
	
	
	
	protected function t2tParseModuleSingle($dirname, $name, $args, $innerData)
	{
		//rlog(RC_LOG_DEBUG, __FUNCTION__, $name, $args, $innerData);
		
		$dir = RPATH_MODULES.DS.$name;
		$dir2 = RPATH_LIBMODULES.DS.$name;
		$file = $dir.DS.$name.'.htm';
		if (!is_dir($dir) && !is_dir($dir2)) {//模块不存在，创建一个
			s_mkdir($dir);
			s_write($file, $innerData) ;
			
			$tpl_module = <<<EOT
<?php
class %sModule extends CModule
{
	function __construct(\$name, \$attribs)
	{
		parent::__construct(\$name, \$attribs);
	}
	
	function %sModule(\$name, \$attribs)
	{
		\$this->__construct(\$name, \$attribs);
	}
}
EOT;
			$cname = ucfirst($name);
			$module_data = sprintf($tpl_module, $cname, $cname);
			s_write($dir.DS.$name.'.php', $module_data);
		}
	}
	
	/*
	Array
	(
	   [0] => Array
	       (
	           [0] => <div class="scroller" style="height:395px" data-rail-visible="1" >
	       )
	
	   [1] => Array
	       (
	           [0] => div class="scroller" style="height:395px" data-rail-visible="1"
	       )
	
	   [2] => Array
	       (
	           [0] => scroller
	       )
	
	)
	
	*/
	
	protected function t2tParseModuleForTileview($innerData, &$args)
	{
		
		//scroller
		
		//<div class="scroller" style="height:195px" data-rail-visible="1" >
		$pattern = "/<(\w+[^>]*\s+class=\s*[\s]*[\'\"]?([^\'\"]*scroller.*)[\'\"]?[^>]*)\s*>/iU";
		$res = preg_match_all($pattern, $innerData, $matches);
		if ($res) {			
			$nr = count($matches[1]);
			if ($nr > 0)  {
				//style, height:395px
				$args2 = attr2array2(strtolower($matches[1][0]));
				$style = $args2['style'];
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,$style);
				
				if ($style) {
					$args['style'] = $style;
				}	
			}
		}
		
		//view
		$matches = array();
		//<div class="portlet-body tileview-listimg  listview-listimg ">
		$pattern = "/<(\w+[^>]*\s+class=\s*[\s]*[\'\"]?([^\'\"]*tileview-\w+\s)[\'\"]?[^>]*)\s*>/iU";
		$res = preg_match_all($pattern, $innerData, $matches);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no match!");
			return false;
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,$matches);
		/*		
		(
		  [0] => Array
		      (
		          [0] => <div class="portlet-body tileview-large margin-bottom-5 ">
		          [1] => <div class="portlet-body tileview-listimg  listview-listimg  hidden">
		      )
		
		  [1] => Array
		      (
		          [0] => div class="portlet-body tileview-large margin-bottom-5 "
		          [1] => div class="portlet-body tileview-listimg  listview-listimg  hidden"
		      )
		
		  [2] => Array
		      (
		          [0] => portlet-body tileview-large
		          [1] => portlet-body tileview-listimg
		      )
		
		)		
		*/
		$all_view_list  = array(
				'large'=>array('name'=>'large', 'vmask'=>1),
				'listimg'=>array('name'=>'listimg','vmask'=>2),
				'detail'=>array('name'=>'detail','vmask'=>4),
				'small'=>array('name'=>'detail','vmask'=>8),
				'newstitle'=>array('name'=>'newstitle','vmask'=>16),
				);
				
		$view = '';
		$vmask = 0;
		
		$nr = count($matches[1]);
		if ($nr <= 0) 
			return false;
		
		$nr_view = 0;
		for ($i=0; $i<$nr; $i++) {
			$tag = $matches[1][$i];
			
			foreach ($all_view_list as $key=>$v) {
				$name = 'tileview-'.$key;
				if (strpos($tag, $name) !== false) {
					$vmask |= $v['vmask'];
					$nr_view ++;
					if (strpos($tag, 'hidden') === false) {
						$view = $key;						
					}
				}
			}
		}
		
		if ($vmask > 0) {			
			$args['vmask'] = $vmask;
			if ($view) {
				$args['view'] = $view;
			}
		}	
		
		if (!isset($args['num'])) {
			$udb = $this->matchContentByTag('title', $innerData);
			if ($udb) {
				$ttmdb = array();
				foreach ($udb as $key=>$v) {
					$ttmdb[$v['title']] = $v['title'];
				}
				
				$num = count($ttmdb);
				if ($nr_view > 0 && $num > 0) {
					$num = ceil($num/$nr_view);
					if ($num > 1) {
						$args['num'] = $num;
					}
				}
			}
		}	
		
	}
	
	protected function t2tParseModuleForPosition($innerData, &$args, $isContentPage=false)
	{
		//_cid=xxx
		$positionbar = array();
		$this->t2tParsePositionBar($innerData, $positionbar);
		if ($positionbar) {
			/*Array
				(
				[下载] => 首页
			)
			*/
			
			$nr = count($positionbar);
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $nr, $positionbar);
			
			$_cid = '';
			foreach ($positionbar as $key=>$v) {
				$_cid = $isContentPage && $nr > 1?$v:$key;
				break;
			}
			if ($_cid) {
				$args['_cid'] = $_cid;
			}
		}		
	}
	
	
	protected function buildMenuTreeDIV($node, &$menu=array(), $pid='') 
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__ ,__FUNCTION__, "TODO ... pid=".$pid);
		$dlList = $node->getElementsByTagName('dl');
		
		if ($dlList->length > 0) {
			for($i=0; $i< $dlList->length; $i++) {
				$dlNode = $dlList[$i];
				//dt
				$title = '';
				foreach ($dlNode->childNodes as $child) {
					if ($child->nodeName === 'dt') {
						//dt
						foreach ($child->childNodes as $child2) {
							if ($child2->nodeName === 'a') {
								$title = $child2->getAttribute('title');
							}
						}
						rlog(RC_LOG_DEBUG, __FILE__, __LINE__ ,__FUNCTION__, "############## dt ... $i, title=$title");
					}
					
					if ($child->nodeName === 'dd') {
						//dd
						rlog(RC_LOG_DEBUG, __FILE__, __LINE__ ,__FUNCTION__, "############## dd ... $i");
						
						$item = array(
								//'text' => trim($child->textContent),
								'title' => $title,
								'pid' => $pid,
								'style' => 3, //CATALOG_STYLE_DROPDOWNDTAB
								'children' => array()
								);
						rlog(RC_LOG_DEBUG, __FILE__, __LINE__ ,__FUNCTION__, "####3", $title);
												
						//ul
						$ulChildren = $child->getElementsByTagName('ul');
						//$ulChildren->length
						rlog(RC_LOG_DEBUG, __FILE__, __LINE__ ,__FUNCTION__, "####3 pid=$pid, title=$title, ulChildren->length=".$ulChildren->length);
						
						if ($ulChildren->length > 0) {
							$children = array();
							for($j=0; $j<$ulChildren->length; $j++) {
								$children[] = $this->buildMenuTree($ulChildren->item($j), $menu, $title);
							}
							$item['children'] = $children;
						}
						
						$menu[$title] = $item;
					}
				}
				
			}
		}
	}
	
	protected function buildMenuTree($node, &$menu=array(), $pid='') 
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__ ,__FUNCTION__, "IN ... ####0 pid=".$pid);
		
		
		$tree = array();
		
		foreach ($node->childNodes as $child) {
			
			if ($child->nodeName === 'li') {
				$ulList = array();
				$divList = array();
				foreach ($child->childNodes as $child2) {
					if ($child2->nodeName === 'a') {
						$title = $child2->getAttribute('title');
					}
					
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__ ,__FUNCTION__, "####1 child2->nodeName=".$child2->nodeName);
					//ul list
					if ($child2->nodeName === 'ul') {
						$ulList[] = $child2;
					}
					if ($child2->nodeName === 'div') {
						$divList[] = $child2;
					}
										
				}
				if (!$title) {
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__ ,__FUNCTION__, "####2 no title! skip!!", $ulList, $divList);
					foreach ($divList as $div)
						$this->buildMenuTreeDIV($div, $menu, $pid);
					
					continue;
				}
				
				$item = array(
					//'text' => trim($child->textContent),
						'title' => $title,
						'pid' => $pid,
						'children' => array()
				);
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__ ,__FUNCTION__, "####3", $title);
				
				//ul	
				/*$ulChildren = $child->getElementsByTagName('ul');
				//$ulChildren->length
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__ ,__FUNCTION__, "####3 pid=$pid, title=$title, ulChildren->length=".$ulChildren->length);
				
				if ($ulChildren->length > 0) {
					$children = array();
					for($i=0; $i< $ulChildren->length; $i++) {
						$children[] = $this->buildMenuTree($ulChildren->item($i), $menu, $title);
					}
					$item['children'] = $children;
				}*/
				
				if ($ulList) {
					$children = array();
					for($i=0; $i<count($ulList); $i++) {
						$children[] = $this->buildMenuTree($ulList[$i], $menu, $title);
					}
					$item['children'] = $children;
				}
				$menu[$title] = $item;
				
				
				$tree[] = $item;
			}
		}
		
		
		return $tree;
	}

	protected function t2tParseModuleForMenu($innerData, &$args)
	{
		//解析多级目录
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__ ,__FUNCTION__, "IN...", $innerData);
		//过滤掉注释
		$innerData = preg_replace('/<!--.*?-->/', '', $innerData);
		$innerData = '<?xml version="1.0" encoding="utf-8"?>'.$innerData;
		$dom = new DOMDocument();
		$html = $dom->loadHtml($innerData);
				
		if (!$html) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "not HTML '$innerData'!");
			return false;			
		}
		
		$ulElements = $dom->getElementsByTagName('ul');
		$menu = array();
		$this->buildMenuTree($ulElements->item(0), $menu); // 从第一个ul开始构建树结构	
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__ ,__FUNCTION__, $menu);exit;
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__ ,__FUNCTION__, "OUT", $menu);
		return $menu;
	}
	
	
	protected function t2tParseModule($dirname, $lang, $tplfile, $tplname, $data, &$modules=array())
	{
		//BEGIN MODULE MYPROFILE
		
		//CSS
		$matches = array();
		$res = preg_match_all("/<!--\s*[\s]*BEGIN MODULE (\w+)\b\s*([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\s*[\s]*-->(.+)<!--\s*[\s]*END MODULE (\w+)\s*[\s]*-->/isU", $data, $matches);
		//var_dump($matches);
		if ($res && count($matches[1]) > 0) {
			$nr = count($matches[1]);
			
			$new = array();
			for($i=0; $i<$nr; $i++) {
				$name = strtolower($matches[1][$i]);
				$innerData = $matches[4][$i];
				
				
				$args = attr2array2(strtolower($matches[2][$i]));
				
				//
				switch($name) {
					case 'contentdetail':
					case 'content':
					case 'content2':
						if (!isset($args['tid']))
							$args['tid'] = '$tid';
						break;
					case 'onecontentpage':
						if (!isset($args['tid'])) {
							$args['cid'] = '$cid';
							$args['tid'] = '$tid';
						}
						$this->t2tParseModuleForPosition($innerData, $args, true);
						break;
					case 'list':
					case 'list2':
					case 'catalog':
						$this->t2tParseModuleForPosition($innerData, $args);
						$this->t2tParseModuleForTileview($innerData, $args);
					case 'searchbox':
					case 'search':
						$args['keyword'] = '$keyword';
						break;						
					case 'hormenu':
						$args['tree'] = '1';
					case 'menu':
					case 'mainmenu':
					case 'nav':						
						if (!isset($args['cid']))
							$args['cid'] = '$cid';
						break;
					
					case 'tileview':
						//check scroller height
						$this->t2tParseModuleForTileview($innerData, $args);
						break;
						
					default:
						
						break;
				}
				
				if (isset($args['cid']) && !is_start_with($args['cid'], '$') ) {
					$cname = $args['cid'];
					$args['cid'] = '$cid';
					$args['_cid'] = $cname;
				}
				
				$_args = array2attr($args);
				
				$new[] = "<rdoc:include type='module' name='$name' $_args />";
				
				
				//单个解析
				$this->t2tParseModuleSingle($dirname, $name, $_args, $innerData);
				
				$modinfo = array();
				$modinfo['tplfile'] = $tplfile;
				$modinfo['tplname'] = $tplname;
				$modinfo['lang'] = $lang;
				
				$modinfo['name'] = $name;
				$modinfo['innerHTML'] = $innerData;
				$modinfo['args'] = $args;
				
				
				$modules[] = $modinfo;
			}
			
			//
			$data = str_replace($matches[4], $new, $data);
		}  
		return $data;
	}
	
	/**
	 * This is method t2tParsePositionBar
	 *
	 * @param mixed $data This is a description
	 * @return mixed This is the return value description
	 *
	 * 
	 * eg:
	 * <div class='position noprint wb'>
		<ul class="page-breadcrumb breadcrumb ">
			<li>
				  <i class="fa fa-home"></i><a href='index.html'>首页</a>        
			</li>
			  
			<li>
				  <a href='list.html' >点播</a>        
			</li>

			<li>
                   长征        
			</li>
		</ul>
	</div> 
	 * 
	 * 
	 * Array
	(
	   [0] => Array
	       (
	           [0] => <div class='position noprint wb'>
	                                                                                                         <ul class="page-breadcrumb breadcrumb ">
	                                                                                                                 <li>
	                                                                                                                         <i class="fa fa-home"></i><a href='$_webroot/'>首页</a>
	                                                                                                                 </li>
	
	                                                                                                                 <li>
	                                                                                                                         <a href='list.html'>点播</a>
	                                                                                                                 </li>
	
	                                                                                                                 <li>
	                                                           长征
	                                                                                                                 </li>
	                                                                                                         </ul>
	
	                                                                                                 </div>
	       )
	
	   [1] => Array
	       (
	           [0] => div
	       )
	
	   [2] => Array
	       (
	           [0] =>  class='position noprint wb'
	       )
	
	   [3] => Array
	       (
	           [0] => positio
	       )
	
	   [4] => Array
	       (
	           [0] =>
	                                                                                                         <ul class="page-breadcrumb breadcrumb ">
	                                                                                                                 <li>
	                                                                                                                         <i class="fa fa-home"></i><a href='$_webroot/'>首页</a>
	                                                                                                                 </li>
	
	                                                                                                                 <li>
	                                                                                                                         <a href='list.html'>点播</a>
	                                                                                                                 </li>
	
	                                                                                                                 <li>
	                                                           长征
	                                                                                                                 </li>
	                                                                                                         </ul>
	
	
	       )
	
	   [5] => Array
	       (
	           [0] => div
	       )
	
	)
	
	
	 */
	protected function t2tParsePositionBar($data, &$positiondb)
	{
		$matches = array();	
		$_content = stripslashes($data);
		
		//class=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?
		
		$pattern = "/<(\w+)([^>]*\s+class=\s*[\s]*[\'\"]?([^\'\"]*position*)[\'\"]?[^>]*)\s*>(.+)<\/(\\1)>/isU";
		$res = preg_match_all($pattern, $_content, $matches);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no match!");
			return array();
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $matches); 
		
		$nr = count($matches[0]);
		if ($nr != 1)
			return array();
		
		$ulData = $matches[4][0];
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $ulData); 
		
		$matches2 = array();
		$pattern = "/<li>(.*)<\/li>/isU";
		$res = preg_match_all($pattern, $ulData, $matches2);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no match!");
			return array();
		}	
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $matches2); 
		
		$posnames = array();
		
		$nr = count($matches2[1]);
		for($i=0; $i<$nr; $i++) {
			$name = trimHTML($matches2[1][$i]);
			$posnames[] = $name;
		}
		$currentpositiondb = array();
					
		for ($i=$nr-1; $i>0; $i--) {
			$name = $posnames[$i];
			$pname = $posnames[$i-1];			
			//current
			$currentpositiondb[$name] = $pname;
			//all
			$positiondb[$name] = $pname;
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $positiondb);
		
		return $currentpositiondb;
	}
	
	/*
	
	<i>首页</i>
	<i id=a>首页2</i>
	<i id='a3' >首页3</i>
	<i id="a4" class="aa" >首页4</i>
	
	
	

		[0] => Array
			(
				[0] => <i>首页</i>
				[1] => <i id=a>首页2</i>
				[2] => <i id='a3' >首页3</i>
				[3] => <i id="a4" class="aa" >首页4</i>
			)

		[1] => Array
			(
				[0] =>
				[1] => id=a
				[2] => id='a3'
				[3] => id="a4" class="aa"
			)

		[2] => Array
			(
				[0] =>
				[1] =>
				[2] =>
				[3] =>
			)

		[3] => Array
			(
				[0] => 首页
				[1] => 首页2
				[2] => 首页3
				[3] => 首页4
			)

	)

	*/
	protected function t2tParseI18n($lang, $dirname, $tplname, $tplfile, $data, &$config=array())
	{
		$item = array();
		//([\w+\s*=('|\"|?)[^(\1)].+(\1)?])
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "TODO ... $name, $tplfile");
		$res = preg_match_all("/<i\s*?([id=('|\"|?)[^(\1)].+(\1)?]*)?>(.+)<\/i>/isU", $data, $matches);
		if ($res) {
						
			$new = array();
			$vardb = isset($config[$tplfile]['i18n'])?$config[$tplfile]['i18n']:array();
			
			$nr = count($matches[0]);
			for($i=0; $i<$nr; $i++) {
				$text = $matches[3][$i];
				$arr = attr2array2($matches[1][$i]);
				$key = trim(isset($arr['id'])?$arr['id']:$text);
				if (!is_name($key))
					continue;
					
				$iid = md5($name.'-'.$dirname.'-'.$lang.'-'.$key);
									
				$vardb[$iid] = array('iid'=>$iid, 'name'=>$key, 'value'=>$text, 'i18n'=>$lang, 'dirname'=>$dirname, 'tplname'=>$tplname);
				
				$args = array2attr($arr);			
				$new[] = "<i iid='$iid' $args >$text</i>";
			}
			
			//fixed add iid
			$data = str_replace($matches[0], $new, $data);
			
			
			$config[$tplfile]['i18n'] = $vardb;
			
			
		}
		
		return $data;
	}
	
	protected function t2tParseConfig($lang, $dirname, $tplname, $tplfile, $data, &$config)
	{
		// <title></title>
		$res = preg_match_all("/<title>(.+)<\/title>/i", $data, $matches);
		if ($res && count($matches[1]) == 1) {
			$title = $matches[1][0];
		} 
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "title ...$title" );
		
		//description : <meth
		// <meta content="RelaxCMS" name="description" />
		// <meta content="" name="version" />
		$matches = array();
		$res = preg_match_all("/<meta \s*?([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\/?>/iU", $data, $matches);
		if ($res) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "META ...", $matches);
			/*
			Array
			(
				[0] => Array
					(
						[0] => <meta charset="utf-8" />
						[1] => <meta http-equiv="X-UA-Compatible" content="IE=edge">
						[2] => <meta content="width=device-width, initial-scale=1" name="viewport" />
						[3] => <meta content="" name="description" />
						[4] => <meta content="" name="author" />
					)

				[1] => Array
					(
						[0] => charset="utf-8"
						[1] => http-equiv="X-UA-Compatible" content="IE=edge"
						[2] => content="width=device-width, initial-scale=1" name="viewport"
						[3] => content="" name="description"
						[4] => content="" name="author"
					)

				[2] => Array
					(
						[0] =>
						[1] =>
						[2] =>
						[3] =>
						[4] =>
					)

			)
			*/
			
			
			$item = array();
			
			$name = $tplfile;
			$ext = s_extname($name);
			$item['name'] = $name;
			$item['title'] = $title;
			$item['tplfile'] = $tplfile;
			
			$nr = count($matches[0]);
			for($i=0; $i<$nr; $i++) {
				$arr = attr2array2($matches[1][$i]);
				if (isset($arr['name'])) { //
					$name = $arr['name'];
					$val = $arr['content'];
					
					switch($name) {
						case 'description':
							$item['description'] = $val;
							break;
						case 'version':
							$item['version'] = $val;
							break;
						default:
							$item[$name] = $val;
							break;
					}
				}
			}
			
			$config[$tplfile] = $item;			
		} 
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "config ...", $config);
	}
	
	protected function t2tParseSiteConfigVar($lang, $dirname, $tplname, $tplfile, $data, &$config, &$resdb)
	{
		$matches = array();	
		$_content = stripslashes($data);
		
		$res = preg_match_all("/<(\w+)([^>]*\s+scf[\s=]?[^>]*)\s*>(.+)<\/(\\1)>/isU", $_content, $matches);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no match tag for '$tplfile'!");
			return $data;
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $matches);
		
		$new = array();
		$scfvardb = isset($config[$tplfile]['scfvardb'])?$config[$tplfile]['scfvardb']:array();
		
		$nr = count($matches[0]);
		for($i=0; $i<$nr; $i++) {
			$content = str_replace("'", '"', trim($matches[0][$i]));
			$tagname =  strtolower(trim($matches[1][$i]));
			$innertext = $matches[3][$i];
			$_innertext = str_replace("'", '"', trim($innertext));
			$val = trimHTML($_innertext);
			
			$arr = attr2array2($matches[2][$i]);
			
			$name = $arr['scf']?$arr['scf']:$val;
			if (!$name)
				continue;
			
			$new_innertext = "\$scf[$name]";
			
			//标题
			$title = isset($arr['title'])?$arr['title']:$name;
			$type = 'varchar';
				
			//href
			$href = $arr['href']?$arr['href']:'';
			
			$input_type = empty($href)?'':'link';
			$innertype = checkInnerHTML($innertext);
			$isRichText = $innertype == 'richtext'?true:false;
			!$input_type && $isRichText && $input_type = 'sneditor';
			//src
			$src = '';
			if ($innertype == 'image') {
				//<img
				$res = preg_match_all("/src=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?/i", $innertext, $mdb);
				if ($res) {
					$val = $mdb[1][0];					
					$new_innertext = str_replace($val, $new_innertext, $innertext);
					//
					$src = $val;
					$file = RPATH_THEME.DS.$dirname.DS.$src;
					if (file_exists($file)) {
						$dname = dirname($src);
						if (!is_start_with($dname,'../../static/')) {
							//copy
							$thedir = RPATH_STATIC_THEMES.DS.$dirname.DS.$dname;
							!is_dir($thedir) && s_mkdir($thedir);
							$dst = RPATH_STATIC_THEMES.DS.$dirname.DS.$src;
							$res2 = copy($file, $dst);				
							if (!$res2) {
								rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call copy filed!src=$file, dst=$dst, thedir=$thedir!");
							}
							
							$rpath = str_replace(DS, '/', substr($file, strlen(RPATH_ROOT)));
							$resdb[$rpath] = $rpath;	
						}						
					} 	
				}
				$input_type = 'image';
			}
			
			unset($arr['scf']);
			$args = array2attr($arr);	
						
			if (!$src && $href) {
				$val = is_start_with($href, 'mailto:')?substr($href, 7):$href;
				$new_content = str_replace($val, $new_innertext, $content);
			} else {
				$new_content = "<$tagname $args >$new_innertext</$tagname>";
			}
			
						
			$new[] = $new_content;
			
			
			$scfvar = $arr;
			$innerData = htmlspecialchars($_innertext, ENT_QUOTES);
			
			$scfvar['name'] = $name;
			$scfvar['type'] = $type;
			$scfvar['length'] = 1024;			
			$scfvar['val'] = $isRichText?$innerData:$val;
			$scfvar['innertext'] = $innerData;
			$scfvar['title'] = $title;
			$scfvar['input_type'] = $input_type;
			
			$scfvardb[$name] = $scfvar;			
			
		}
		
		$data = str_replace($matches[0], $new, $data);
		
		$config[$tplfile]['scfvardb'] = $scfvardb;
		
		return $data;
		
	}
	
	
	/*
	Array
	(
	   [0] => Array
	       (
	           [0] => <a href="http://beian.miit.gov.cn" target="_blank" var>皖ICP备2022016774号-2 </a>
	       )
	
	   [1] => Array
	       (
	           [0] => a
	       )
	
	   [2] => Array
	       (
	           [0] =>  href="http://beian.miit.gov.cn" target="_blank" var
	       )
	
	   [3] => Array
	       (
	           [0] => 皖ICP备2022016774号-2
	       )
	
	   [4] => Array
	       (
	           [0] => a
	       )
	
	)
	*/
	
	protected function t2tParseVar($lang, $dirname, $tplname, $tplfile, $data, &$config)
	{
		$matches = array();	
		$_content = stripslashes($data);
		
		$res = preg_match_all("/<(\w+)([^>]*\s+var[\s=]?[^>]*)\s*>(.+)<\/(\\1)>/isU", $_content, $matches);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no match tag for '$tplfile'!");
			return $data;
		}
		
		
		$new = array();
		$vardb = isset($config[$tplname]['vardb'])?$config[$tplname]['vardb']:array();
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $dirname, $tplfile, $tplname, $matches, $vardb);
		
		
		$nr = count($matches[0]);
		for($i=0; $i<$nr; $i++) {
			$content = str_replace("'", '"', trim($matches[0][$i]));
			$tagname =  trim($matches[1][$i]);
			$innertext = $matches[3][$i];
			$_innertext = str_replace("'", '"', trim($innertext));
			
			$arr = attr2array2($matches[2][$i]);
					
			$name = $arr['var']?$arr['var']:$innertext;
			$title = $arr['title']?$arr['title']:$name;
			$description = $arr['description']?$arr['description']:'';
			
			//href
			$href = $arr['href']?$arr['href']:'';
			//src
			$src = '';
			if (is_start_with($_innertext, "<img")) {
				//<img
				$res = preg_match_all("/src=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?/i", $_innertext, $mdb);
				if ($res) {
					$src = $mdb[1][0];
					$_innertext = trimHTML($_innertext);
				}
			} 
			
			
			$vid = md5($dirname.'-'.$name);
			$item = isset($vardb[$vid])?$vardb[$vid]:array();
						
			if ($lang == 'zh_CN') {
				$item['vid'] = $vid;
				$item['dirname'] = $dirname;
				$item['tplname'] = $tplname;
				$item['name'] = $name;
				$item['title'] = $title;
				$item['description'] = $description;
				$item['tagname'] = $tagname;
				$item['innertext'] = $_innertext;
				$item['content'] = $content;
				
				$item['href'] = $href;
				$item['src'] = $src;
				
				//$item['lang'] = $lang;
				$item['type'] = $type;
			} else {
				
				if (!is_array($item['i18n']))
					$item['i18n'] = array();
				if (!is_array($item['i18n'][$lang]))
					$item['i18n'][$lang] = array();
				
				$title && $item['i18n'][$lang]['title'] = $title;
				$description && $item['i18n'][$lang]['description'] = $description;
				$_innertext && $item['i18n'][$lang]['innertext'] = $_innertext;
			}
								
			
			$args = array2attr($arr);			
			$new[] = "<$tagname vid='$vid' $args >$innertext</$tagname>";
			
			$vardb[$vid] = $item;
		}
		
		//fixed add iid
		$data = str_replace($matches[0], $new, $data);
		
		$config[$tplname]['vardb'] = $vardb;
		
		return $data;
		
	}
	
	protected function parseI18nLang($tplfile, &$tplname='')
	{
		//支持的语种列表
		//lang
		$lang = 'zh_CN';
		$tplname = $tplfile;
		$ext = s_extname($tplname);
		
		if (($pos = strrpos($tplname, '-')) != false) {
			$endname = substr($tplname, $pos+1);
			if (isset($this->_i18nnames[$endname])) {
				$lang = $endname;
				$tplname = substr($tplname, 0, $pos);
			}				
		}
		
		return $lang;			
	}
	
	
	
	protected function t2tParseResources($pattern, $name, $data, &$resdb=array())
	{
		$matches = array();
		$res = preg_match_all($pattern, $data, $matches);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $pattern, $matches);
		
		if ($res && count($matches[1]) > 0) {
			$olddb = array();
			$newdb = array();
			for($i=0; $i<count($matches[1]); $i++) {
				$src = $old = trim($matches[1][$i]);
				if (!$src) {
					//rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, $matches);
					continue;
				}
				
				$ext = s_fileext($src);
				switch($ext) {
					case 'js':
						break;
					default:
						$file = RPATH_THEME.DS.$name.DS.$src;
						if (file_exists($file)) {
							$dname = dirname($src);
							if (is_start_with($dname,'../../static/')) {
								$src = '$_dstroot/'.str_replace($dname, '', $src);	
							} else {
								//copy
								$dname = 'img';
								$thedir = RPATH_STATIC_THEMES.DS.$name.DS.$dname;
								!is_dir($thedir) && s_mkdir($thedir);
								
								$filename = s_filename($src);								
								$res2 = copy($file, RPATH_STATIC_THEMES.DS.$name.DS.$dname.DS.$filename);	
								if (!$res2) {
									rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call copy failed! file=$file", $src, $res2, $thedir);
								}				
								$src = '$_theroot/'.$name."/$dname/".$filename;	
								
								$rpath = str_replace(DS, '/', substr($file, strlen(RPATH_ROOT)));
								
								$resdb[$rpath] = $rpath;	
								//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING1 : file '$file', src='$src'!");
							}						
						} else if (!is_start_with($src,'$')) {
								$no = $ext == 'mp4'?'no.mp4':'no.png';
								$src = '$_dstroot/img/'.$no;	
						} else {
								rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING : file '$file'!");
						}					
						break;
				}
				if (!in_array($src, $newdb)) {
					$olddb[] = $old;			
					$newdb[] = $src;	
				}
			}
			$data = str_replace($olddb, $newdb, $data);
		}
		
		return $data;
	}
	
	
	
	protected function t2tData($name, $tplfile, $data, &$modules=array(), &$config=array(), &$titledb=array(), &$resdb=array())
	{
		$lang = $this->parseI18nLang($tplfile, $tplname);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "lang=$lang",$tplfile );
		
		//titles
		$item = array();
		$tpltitledb = $this->matchContentByTag('title', $data);
		
		$item['lang'] = $lang;
		$item['tpltitledb'] = $tpltitledb;
		$item['tplfile'] = $tplfile;
		$item['dirname'] = $name;
		$item['tplname'] = $tplname;
		
		$titledb[$tplfile] = $item;
		
		
		// title/description/version
		$this->t2tParseConfig($lang, $name, $tplname, $tplfile, $data, $config);
		
		$data = $this->t2tParseSiteConfigVar($lang, $name, $tplname,  $tplfile, $data, $config, $resdb);
		//var
		$data = $this->t2tParseVar($lang, $name, $tplname,  $tplfile, $data, $config);
		//i18n
		$data = $this->t2tParseI18n($lang, $name, $tplname, $tplfile, $data, $config);
		
		
		//title
		$matches = array();
		$res = preg_match_all("/<title>(.+)<\/title>/i", $data, $matches);
		if ($res && count($matches[1]) == 1) {
			$data = str_replace($matches[0], "<title> \$_content_title \$scf[title] </title><meta name=\"_dstroot\" content=\"\$_dstroot\">", $data);
		}  
		//img src "/src\b\s*=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?/i"
		$data = $this->t2tParseResources("/src\b\s*=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?/i", $name, $data, $resdb);
		/*
		$matches = array();
		$res = preg_match_all("/src\b\s*=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?/i", $data, $matches);
		if ($res && count($matches[1]) > 0) {
			$olddb = array();
			$newdb = array();
			for($i=0; $i<count($matches[1]); $i++) {
				$src = $old = $matches[1][$i];
				$ext = s_fileext($src);
				switch($ext) {
					case 'js':
						break;
					default:
						$file = RPATH_THEME.DS.$name.DS.$src;
						if (file_exists($file)) {
							$dname = dirname($src);
							if (is_start_with($dname,'../../static/')) {
								$src = '$_dstroot/'.str_replace($dname, '', $src);	
							} else {
								//copy
								$dname = 'img';
								$thedir = RPATH_STATIC_THEMES.DS.$name.DS.$dname;
								!is_dir($thedir) && s_mkdir($thedir);
								
								$filename = s_filename($src);								
								$res2 = copy($file, RPATH_STATIC_THEMES.DS.$name.DS.$dname.DS.$filename);	
								if (!$res2) {
									rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call copy failed! file=$file", $res2, $thedir);
								}				
								$src = '$_theroot/'.$name."/$dname/".$filename;	
								
								$rpath = str_replace(DS, '/', substr($file, strlen(RPATH_ROOT)));
								$resdb[$rpath] = $rpath;	
							}						
						} else if (!is_start_with($src,'$')) {
							$src = '$_dstroot/img/no.png';	
						}					
						break;
				}
				if (!in_array($src, $newdb)) {
					$olddb[] = $old;			
					$newdb[] = $src;	
				}
			}
			$data = str_replace($olddb, $newdb, $data);
		}*/
		
		//poster
		$data = $this->t2tParseResources("/poster=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?/i", $name, $data, $resdb);
		//url
		$data = $this->t2tParseResources("/url=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?/i", $name, $data, $resdb);
		
		
		//url : url("img/banner2.jpg")
		$data = $this->t2tParseResources("/url\(\s*[\'\"]?([^\'\"]*)[\'\"]?\)/i", $name, $data, $resdb);
		
		/*$matches = array();
		$res = preg_match_all("/url\(\s*[\'\"]?([^\'\"]*)[\'\"]?\)/i", $data, $matches);
		if ($res && count($matches[1]) > 0) {
			$olddb = array();
			$newdb = array();
			for($i=0; $i<count($matches[1]); $i++) {
				$src = $old = $matches[1][$i];
				$file = str_replace('/', DS, RPATH_THEME.DS.$name.DS.$src);
				
				if (file_exists($file)) {
					//$src = '$_theroot/'.$name.'/'.$src;	
					
					$dname = dirname($src);
					if (is_start_with($dname,'../../static/')) {
						$src = '$_dstroot/'.str_replace($dname, '', $src);	
					} else {
						//copy
						$dname = 'img';
						$thedir = RPATH_STATIC_THEMES.DS.$name.DS.$dname;
						!is_dir($thedir) && s_mkdir($thedir);
						$filename = s_filename($src);		
						
						$res2 = copy($file, RPATH_STATIC_THEMES.DS.$name.DS.$dname.DS.$filename);		
						if (!$res2) {
							rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call copy failed! file=$file", $res2, $thedir);
						}		
						$src = '$_theroot/'.$name."/$dname/".$filename;		
						
						$rpath = str_replace(DS, '/', substr($file, strlen(RPATH_ROOT)));
						$resdb[$rpath] = $rpath;	
					}
				} else {
					$src = '$_dstroot/img/no.png';	
				}					
				
				if (!in_array($src, $newdb)) {
					$olddb[] = $old;			
					$newdb[] = $src;	
				}
			}
			
			$data = str_replace($olddb, $newdb, $data);
		}*/
		
		
		//CSS
		$matches = array();
		$res = preg_match_all("/<!--\s*[\s]*BEGIN CSS\s*([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\s*[\s]*-->(.+)<!--\s*[\s]*END CSS\s*[\s]*-->/isU", $data, $matches);
		if ($res && count($matches[0]) == 1) {
			$_name = $name;
			$nr = count($matches);
			if ($nr > 1 && $matches[1][0]) {
				$args = attr2array2(strtolower($matches[1][0]));
				foreach ($args as $k2 => $v2) {
					if ($k2 == 'name') {
						$_name = trim($v2);
						break;
					}
				}
			}
			$cssinclude_data = '<link href="$_dstroot/css/'.$_name.'.css" rel="stylesheet">';
			$cssinclude_data .= '<!--# foreach($cssdb as $key=>$v) { #-->';
			$cssinclude_data .= '<link href="$v" rel="stylesheet" type="text/css" id="$key" />';
			$cssinclude_data .= '<!--# } #-->';
			$data = str_replace($matches[$nr-1], $cssinclude_data, $data);
		} 
		
		//THEMECSS
		$matches = array();
		$res = preg_match_all("/<!--\s*[\s]*BEGIN THEMECSS\s*[\s]*-->(.+)<!--\s*[\s]*END THEMECSS\s*[\s]*-->/isU", $data, $matches);
		if ($res && count($matches[1]) == 1) {
			$cssinclude_data = '<link href="$_dstroot/css/'.$name.'_theme_$scf[theme].css" rel="stylesheet">';
			$data = str_replace($matches[1], $cssinclude_data, $data);
		} 
		
		
		//JS
		$matches = array();
		$res = preg_match_all("/<!--\s*[\s]*BEGIN JS\s*([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\s*[\s]*-->(.+)<!--\s*[\s]*END JS\s*[\s]*-->/isU", $data, $matches);
		if ($res && count($matches[1]) == 1) {
			$_name = $name;
			$nr = count($matches);
			if ($nr > 1 && $matches[1][0]) {
				$args = attr2array2(strtolower($matches[1][0]));
				foreach ($args as $k2 => $v2) {
					if ($k2 == 'name') {
						$_name = trim($v2);
						break;
					}
				}
			}
			
			$jsinclude_data = '$sys_JS_G';
			$jsinclude_data .= '<script src="$_dstroot/js/'.$_name.'.js" type="text/javascript"></script>';
			$jsinclude_data .= '<!--# foreach($jsdb as $key=>$v) { #-->';
			$jsinclude_data .= '<script src="$v" type="text/javascript" id="$key"></script>';
			$jsinclude_data .= '<!--# } #-->';
			
			$data = str_replace($matches[$nr-1], $jsinclude_data, $data);
		}  
		
		//js
		$data = str_replace("../../static/", '$_dstroot/', $data);
		
		//index.html
		$data = str_replace("index.html", '$_webroot/', $data);
		
		
		//module
		$data = $this->t2tParseModule($name, $lang, $tplfile, $tplname, $data, $modules);
		//sysvar
		$data = $this->t2tParseSysVar($data);
		
		
		return $data;
	}
	
	protected function readdir($dir)
	{
		$udb = s_readdir($dir, "files");
		
		$prefixdb = array('index'=>'1', 'list'=>'2', 'content'=>'3');
		
		$fdb = array();
		foreach ($udb as $key=>$v) {
			$item = array();
			
			$filename = $v;
			$name = $v;
			
			s_extname($name);
			
			$item['name'] = $name;
			$item['filename'] = $filename;
			
			$sortname = '9'.$name;
			foreach ($prefixdb as $k2=>$v2) {
				if (is_start_with($name, $k2)) {
					$sortname = $v2.$name;
					break;					
				}
			}
			
			$item['sortname'] = $sortname;
			
			$fdb[] = $item;
		}
		
		array_sort_by_field($fdb, 'sortname');
		
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $fdb);
		
		return $fdb;
	}
	
	
	protected function checkTemplateFile($filename)
	{
		$ext = s_extname($filename);
		if ($ext != 'html')
			return false;
		
		$tdb = explode('-', $filename);
		
		$nr = count($tdb);
		
		for ($i=$nr-1; $i>0; $i--) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "$filename tdb[$i] = $tdb[$i]");
			if (intval($tdb[$i]) > 0)	//eg: content-1.html		
				return false;
		}
		return true;
	}
	
	
	protected function checkModuleInfoForContent($dirname, $moduleinfo)
	{
		if (!isset($moduleinfo['innerHTML']))
			return false;
			
		$data = $moduleinfo['innerHTML'];
		
		$matches = array();	
		$_content = stripslashes($data);
		
		$_content = str_replace("title=", "content=", $_content);
		$res = preg_match_all("/<(\w+)([^>]*\s+content[\s=]?[^>]*)\s*>(.+)<\/(\\1)>/isU", $_content, $matches);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no match tag!");
			return false;
		}
		
		$ndb = array();
		$nr = count($matches[0]);
		for ($i=0; $i<$nr; $i++) {
			
			$val = trim($matches[3][$i]);
			$attr = $matches[2][$i];
			
			$attrdb = attr2array2($attr);
			if (!isset($attrdb['content']))
				continue;
			
			$name = trim($attrdb['content']);	
			if (!$name && isset($attrdb['tid']))
				$name = trim($attrdb['tid']);
				
			if (!$name)
				$name = $val;
			
			$ndb[] = $name;
		}
		
		$args = $moduleinfo['args'];
		$file = RPATH_TEMPLATES.DS.$dirname.DS.'contentdb.php';
		$contentdb = get_cache_array('contentdb', $file);		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $ndb, $cid, $moduleinfo['args']);
		
		$changed = false;
		foreach ($ndb as $key=>$name) {
			if (isset($contentdb[$name])) {
				$cinfo = $contentdb[$name]; 				
				if (!isset($cinfo['flags']) && isset($args['flags'])) {
					$cinfo['flags'] = $args['flags'];	
					$changed = true;				
				}
				
				if (!isset($cinfo['cid']) && isset($args['cid']) && !is_start_with($args['cid'], '$')) {
					$cinfo['cid'] = $args['cid'];					
					$changed = true;
				}				
				$contentdb[$name] = $cinfo;				
			}
		}		
		cache_array('contentdb', $contentdb, $file);		
	}
	
	protected function getPrefixOfTplname($tplname)
	{
		$tdb1 = explode('-', $tplname);
		$tdb2 = explode('_', $tplname);
		$name1= $tdb1[0];
		$name2 = $tdb2[0];
		
		if (strlen($name1) < strlen($name2)) {
			return $name1;
		} else {
			return $name2;
		}
	}
	
	protected function parseTitleForBackground(&$titleinfo)
	{
		if (empty($titleinfo['innerHTML']))
			return false;
			
		if (empty($titleinfo['style']))
			return false;
		
		//style="background-image: url(img/p0.jpg)"
		$style = $titleinfo['style'];
		
		$res = preg_match_all("/url\(\s*[\'\"]?([^\'\"]*)[\'\"]?\)/i", $style, $matches);
		if ($res && count($matches[1]) > 0) {
			$photo = $matches[1][0];
			if (empty($titleinfo['photo']))
				$titleinfo['photo'] = $photo;
		}
		
		$tdb = $this->matchContentByTag('title', $titleinfo['innerHTML']);
		if (!$tdb) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "call matchContentByTag failed! no title!", $titleinfo['innerHTML']);
			return false;
		}
		foreach ($tdb as $key=>$v) {
			if ($v['name'] == $titleinfo['name']) {
				foreach ($v as $k2=>$v2) {
					if (!isset($titleinfo[$k2])) {
						$titleinfo[$k2] = $v2;						
					}
				}
				
				$titleinfo['innerHTML'] = $v['innerHTML'];				
				break;
			}
		}
	}
	
	protected function t2tParseTplTitleOne($dirname, $tplname, $lang, $newinfo, &$tdb)
	{
		$name = $newinfo['name'];
		
		$tagname = $newinfo['tagname'];
		
		if (isset($newinfo['style'])) 
			$this->parseTitleForBackground($newinfo);		
				
		$title = isset($newinfo['title'])?$newinfo['title']:'';
		$description = isset($newinfo['description'])?$newinfo['description']:'';
		$photo = isset($newinfo['photo'])?$newinfo['photo']:'';
		$icon = isset($newinfo['icon'])?$newinfo['icon']:'';
		$video = isset($newinfo['video'])?$newinfo['video']:'';
		$href = isset($newinfo['href'])?$newinfo['href']:'';
		
		$hasDetail = isset($newinfo['detail'])?$newinfo['detail']:'';
		$hasDesc = isset($newinfo['desc'])?$newinfo['desc']:'';
		
		if (!$href && $hasDetail)//详细页面，无链接
			$href = $tplname.'.html';
		
		$tid = 0;
		
		$ttype = 0;
		$is_content = false;
		$is_catalog = false;
		if (isset($newinfo['content'])) {
			if (is_numeric($newinfo['content'])) 
				$tid = intval($newinfo['content']);				
			$ttype |= TT_CONTENT;
		}	
			
		if (isset($newinfo['catalog'])) {
			if (is_numeric($newinfo['catalog'])) {
				$tid = intval($newinfo['catalog']);	
			}
			
			$ttype |= TT_CATALOG;
		}	
		
		if (isset($newinfo['var'])) {
			if (is_numeric($newinfo['var'])) {
				$tid = intval($newinfo['var']);	
			}
			
			$ttype |= TT_VAR;
		}	
		
		if (isset($newinfo['scf'])) {
			$ttype = TT_SCFVAR;
		}	
		
		//cid
		$cid = '';
		if (isset($newinfo['cid'])) {
			$cid = $newinfo['cid'];
		}	
		
		$content = '';
		
		if ($hasDesc)
			$description = $innerHTML;
		if ($hasDetail)
			$content = $innerHTML;	
		
		$innerHTML = $newinfo['innerHTML'];
		$isRichText = strpos($innerHTML,'</p>') !== false?true:false;	
		$isRichText && !$content && $content = $innerHTML;
		
		switch($tagname) {
			case 'a': //标题或摘要
				if ($href == '#') { //事件操作，跳过
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "unkown href '$href' title '$title'!");	
					return false;
				}
							
			case 'span':
				if (isset($newinfo['no'])) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "unkown span title '$title'!");	
					return false;
				}
				!$description && $description = trimHTML($innerHTML);
				break;				
			case 'div':
			default:
				
				break;
		}
		
		//tplname
		$file = RPATH_THEME.DS.$dirname.DS.$href;
		if (file_exists($file)) {
			$tpl_content = $this->filename2tplname($href); 			
		} else {
			$tpl_content = $hasDetail?$tplname:'';
		}
			
		$newitem = $newinfo;
		//unset($newitem['innerHTML']);
		//unset($newitem['taghtml']);
		
		$newitem['tid'] = $tid;
		$newitem['ttype'] = $ttype;
		
		$newitem['name'] = $name;
		$newitem['photo'] = $photo;
		$newitem['icon'] = $icon;
		$newitem['video'] = $video;			
		$newitem['title'] = $title;			
		$newitem['description'] = $description;
		$newitem['content'] = $content;
		
		$newitem['tplname'] = $tpl_content;
		
		//更新
		$old = isset($tdb[$name])?$tdb[$name]:array();		
		if (!$old) {			
			if ($lang == 'zh_CN') {
				$old = $newitem;
			} else {
				$i18n = array();
				$i18n[$lang] = $newitem;
				$old['i18n'] = $i18n;
			}			
		} else {
			if ($lang == 'zh_CN') {
				foreach ($newitem as $key=>$v) {
					if ($v) {
						$old[$key] = $v;
					}
				}
			} else {
				if (!isset($old['i18n'])) {
					$old['i18n'] = array();
				}
				$i18nitem = isset($old['i18n'][$lang])?$old['i18n'][$lang]:array();
				if (!$i18nitem) {
					$i18nitem = $newitem;
				} else {
					foreach ($newitem as $key=>$v) {
						if ($v) {
							$i18nitem[$key] = $v;
						}
					}
				}
				$old['i18n'][$lang] = $i18nitem;				
			}
		}
				
		$tdb[$name] = $old;
	}
	
	/**
	 * This is method t2tParseTitleForContentAndCatalogOne
	 *
	 * @param mixed $tpltitleinfo This is a description
	 * @param mixed $prefixtplname This is a description
	 * @param mixed $tplmodules This is a description
	 * @param mixed $contentdb This is a description
	 * @param mixed $catalogdb This is a description
	 * @return mixed This is the return value description
	 *
	 *  Array
	(
	   [0] => Array
	       (
	           [content] => 1
	           [cid] => 1
	           [href] => content.html
	           [title] => CRAB运行环境
	           [tag] => title
	           [taghtml] => <a content=1 cid=1 href='content.html' title="CRAB运行环境" ><img  src="img/crab.png" /></a>
	           [innerHTML] => <img  src="img/crab.png" />
	           [tagname] => a
	           [id] => 0
	           [name] => CRAB运行环境
	           [photo] => img/crab.png
	           [video] =>
	       )
	
	   [1] => Array
	       (
	           [href] => content.html
	           [content] => 1
	           [title] => 系统采用分层架构设计，从下至上可分为：系统层、服务层、架构层及应用层。
	           [class] => h3
	           [tag] => title
	           [taghtml] => <a href="content.html" content=1 title="系统采用分层架构设计，从下至上可分为：系统层、服务层、架构层及应用层。" class="h3"> CRAB运行环境 </a>
	           [innerHTML] => CRAB运行环境
	           [tagname] => a
	           [id] => 0
	           [name] => 系统采用分层架构设计，从下至上可分为：系统层、服务层、架构层及应用层。
	           [photo] =>
	           [video] =>
	       )
	       ...	
	
	 */
	protected function t2tParseTplTitle($tpltitleinfo, $tplmodules, &$alltdb=array())
	{
		$dirname = $tpltitleinfo['dirname'];
		$lang = $tpltitleinfo['lang'];
		$tplname = $tpltitleinfo['tplname'];
		$tdb = $tpltitleinfo['tpltitledb'];
		if (!$tdb) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no tpltitledb!", $tpltitleinfo);
			return false;
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $prefixtplname, $lang, $tplname, $tdb);
		foreach ($tdb as $key=>$v) {
			$this->t2tParseTplTitleOne($dirname, $tplname, $lang, $v, $alltdb);			
		}
		
		//解析模块标题
		
		
	}
	
	
	protected function t2tParseModuleTitle($moduleinfo, &$alltdb=array())
	{
		$ttmdb = $this->matchContentByTag('title', $moduleinfo['innerHTML']);
		
		if (!$ttmdb)
			return false;
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $ttmdb, $moduleinfo);
		$margs = $moduleinfo['args'];
		
		$baseclass = isset($margs['baseclass'])?$margs['baseclass']:$moduleinfo['name'];
		
		$mmenus = array();
		switch($baseclass) {
			case 'nav':							
			case 'hormenu':							
			case 'mainmenu':
			case 'menu':													
			case 'catalog':
				$mmenus = $this->t2tParseModuleForMenu($moduleinfo['innerHTML'], $margs);
				break;
			default:
				break;
		}
		
		
		
		
		
		$cid = isset($margs['cid'])?$margs['cid']:'';
		$pid = isset($margs['pid'])?$margs['pid']:'';
		$flags = isset($margs['flags'])?$margs['flags']:'';
		$multiset = isset($margs['multiset'])?intval($margs['multiset']):0; //多集
		
		if (isset($margs['_pid']))
			$pid = $margs['_pid'];
		if (isset($margs['_cid']))
			$cid = $margs['_cid'];
			
		$pttype = 0;
		$pinfo = array();
		if ($pid && isset($alltdb[$pid])) {
			$pinfo = $alltdb[$pid];
			$pttype = $alltdb[$pid]['ttype'];
		}
		
		//org pid
		$_org_cid = $cid;			
		$_org_pid = $pid;
		
		if ($multiset == 1) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "TODO...",  $ttmdb, $multiset);
		}			
		
		foreach($ttmdb as $key=>$v) {
			$name = $v['name'];
			if ($pinfo && $name == $pinfo['name']) {
				continue;
			}
			
			if (isset($v['_pid'])) 
				$pid = $_org_pid;
				
			if (isset($v['_cid'])) {
				$cid = $v['_cid'];
				continue;
			}
			if (isset($mmenus[$name])) {
				$pid = isset($mmenus[$name]['pid'])?$mmenus[$name]['pid']:'';
			}
			
			
			if (isset($alltdb[$name])) {
				$tinfo = $alltdb[$name];
				
				//ttype
				$ttype = $tinfo['ttype'];
				//if ($ttype == 0) {
					$defaultType = 0;
					switch($baseclass) {
						case 'nav':							
						case 'hormenu':							
						case 'mainmenu':
						case 'menu':													
							$tinfo['_nav'] = true;							
						case 'catalog':
							$ttype |= TT_CATALOG;
							if (isset($mmenus[$name]) ) {
								if (isset($mmenus[$name]['style']) ) {
									$tinfo['style'] = $mmenus[$name]['style'];	
								}
							}
							break;
						case 'aboutus':
						case 'onecontentpage':												
					case 'content':
						if ($ttype != TT_SCFVAR)
							$ttype |= TT_CONTENT;
						break;
					default:
						if ($pttype > 0) { //子目录
							$ttype = $pttype;
						} else {
							//检查是不是已有标记
							if ($v['title'] == $tinfo['title']) {
								if ($ttype == 0)
									$ttype |= TT_CONTENT;
							} elseif ($ttype != TT_SCFVAR)
								$ttype |= TT_CONTENT;
						}
						break;
					}
				//}				
				$tinfo['ttype'] = $ttype;
				
				
				//cid
				if (($ttype & TT_CONTENT) && empty($tinfo['cid']) && !is_start_with($cid,'$')) {
					$tinfo['cid'] = $cid;					
				}
				
				if ($pid && empty($tinfo['pid'])) {
					$tinfo['pid'] = $pid;
				}
				
				//flags
				if ($flags) {
					empty($tinfo['flags']) && $tinfo['flags'] = array();
					$tinfo['flags'][$flags] = $flags;
				}
								
				$alltdb[$name] = $tinfo;				
			}
			
			if (isset($v['_pid'])) { //多级目录
				$pid = $name;
			}
		}
		
	}		
	
	
	protected function t2tParseTitleForContentAndCatalog($titledb, $modules)
	{
		$alltdb = array();
		foreach ($titledb as $key=>$v) {
			$this->t2tParseTplTitle($v, $tplmodules, $alltdb);
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $alltdb);
		foreach ($modules as $k2=>$v2) {
			$this->t2tParseModuleTitle($v2, $alltdb);			
		}
				
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $alltdb);
		
		return $alltdb;
		
	}
	
	protected function filename2tplname($filename)
	{
		$tplfilename = s_filename2name($filename);
		
		$pos = strrpos($tplfilename, '-');
		if ($pos === false) {
			$pos = strrpos($tplfilename, '_');
			if ($pos === false)
				return $tplfilename;		
		}
		
		$num = substr($tplfilename, $pos+1);
		if (is_numeric($num))	//eg: content-1.html		
			return substr($tplfilename, 0, $pos);
		
		return $tplfilename;
	}
	
	protected function initCatalogTplListAndTplContent($dirname, &$cataloginfo, $alltdb)
	{
		if (empty($cataloginfo['href']))
			return false;
		
		$href = $cataloginfo['href'];	
		$file = RPATH_THEME.DS.$dirname.DS.$href;
		if (!file_exists($file)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no file '$file'!");
			$cataloginfo['link'] = $href;
			return false;			
		}
		$tplname = $this->filename2tplname($href); 			
		$cataloginfo['tpl_list'] = $tplname;
		
		$tpl_contentdb = array();
		
		foreach ($alltdb as $key=>$v) {
			if ($v['cid'] == $cataloginfo['name']) {
				$href2 = isset($v['href'])?$v['href']:$v['tplname'].'.html';	
				$file = RPATH_THEME.DS.$dirname.DS.$href2;
				if (file_exists($file)) {
					$tplname = $this->filename2tplname($href2);
					if (!$tplname) {
						rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no tplname '$tplname' for '$href2'",$v);
					} 	
					if (!isset($tpl_contentdb[$tplname])) {
						$tpl_contentdb[$tplname] = 1;
					} else {
						$tpl_contentdb[$tplname]++;
					}
					//break;
				}
			}
		}		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $cataloginfo['name'], $tpl_contentdb);
		
		$max_nr = 0;
		$tpl_content = '';
		foreach ($tpl_contentdb as $key=>$v) {
			if ($max_nr < $v) {
				$max_nr = $v;
				$tpl_content = $key;
			}
		}
		//默认tpl_content
		if ($tpl_content) {
			$cataloginfo['tpl_content'] = $tpl_content;
		}
		
		return true;	
	}	
	
	protected function t2tProbeContentCIDByPosition($positiondb)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...", $positiondb);
		
		$cid = '';
		$nr = count($positiondb);
		foreach ($positiondb as $key=>$v) {
			$cid = $nr > 1?$v:$key;
			break;
		}
		return $cid;
	}
	
	protected function matchContentByPattern($pattern, $data)
	{
		//CSS
		$matches = array();
		$res = preg_match_all($pattern, $data, $matches);
		if ($res && count($matches[0]) == 1) {
			$_name = $name;
			$nr = count($matches);
			return $matches[$nr-1];
		} 
		
	}
	
	
	protected function parseHtmlMeta($data, &$metadb=array())
	{
		//description : <meth
		// <meta content="RelaxCMS" name="description" />
		// <meta content="" name="version" />
		$matches = array();
		$res = preg_match_all("/<meta \s*?([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\/?>/iU", $data, $matches);
		if ($res) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "META ...", $matches);
			/*
			Array
			(
				[0] => Array
					(
						[0] => <meta charset="utf-8" />
						[1] => <meta http-equiv="X-UA-Compatible" content="IE=edge">
						[2] => <meta content="width=device-width, initial-scale=1" name="viewport" />
						[3] => <meta content="" name="description" />
						[4] => <meta content="" name="author" />
					)
			
				[1] => Array
					(
						[0] => charset="utf-8"
						[1] => http-equiv="X-UA-Compatible" content="IE=edge"
						[2] => content="width=device-width, initial-scale=1" name="viewport"
						[3] => content="" name="description"
						[4] => content="" name="author"
					)
			
				[2] => Array
					(
						[0] =>
						[1] =>
						[2] =>
						[3] =>
						[4] =>
					)
			
			)
			*/
			
			
			$nr = count($matches[0]);
			for($i=0; $i<$nr; $i++) {
				$arr = attr2array2($matches[1][$i]);
				if (isset($arr['name'])) { //
					$name = $arr['name'];
					$val = $arr['content'];
					
					switch($name) {
						case 'description':
							$metadb['description'] = $name;
							break;
						case 'version':
							$metadb['version'] = $val;
							break;
						default:
							$metadb[$name] = $val;
							break;
					}
				}
			}	
		} 
		
		
	}
	
	protected function checkResByTag($tag, $data, &$olddb, &$newdb, $resurl, $outdir)
	{
		//检查资源(src='')是否存在，不存在则替换
		$matches = array();
		$res = preg_match_all("/$tag\b\s*=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?/i", $data, $matches);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $pattern, $matches);
		
		if ($res && count($matches[1]) > 0) {
			for($i=0; $i<count($matches[1]); $i++) {
				$src = $old = trim($matches[1][$i]);
				if (!$src) {
					//rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, $matches);
					continue;
				}
				
				$ext = s_fileext($src);
				switch($ext) {
					case 'js':
						break;
					default:
						$fname = s_filename($src);	
						$ofile = $outdir.DS.'img'.DS.$fname;							
						if (!file_exists($ofile)) {
							$src = $resurl.'/img/'.$fname;														
						} 					
						break;
				}
				if (!in_array($src, $newdb) && $src != $old) {
					$olddb[] = $old;			
					$newdb[] = $src;	
				}
			}
		}
		
		return $res;
	}
	
	
	protected function fixedCssJSSWithBlank($data, $blanData)
	{
		//CCS
		$pattern1 = "/<!--\s*[\s]*BEGIN CSS\s*([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\s*[\s]*-->(.+)<!--\s*[\s]*END CSS\s*[\s]*-->/isU";
		$cssdata1 = $this->matchContentByPattern($pattern1, $data);		
		$cssdata2 = $this->matchContentByPattern($pattern1, $blanData);
		
		$changed = false;
		if ($cssdata1 && $cssdata2) {
			$data = str_replace($cssdata1, $cssdata2, $data);
			$changed = true;
		}
		
		//JS
		$pattern2 = "/<!--\s*[\s]*BEGIN JS\s*([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\s*[\s]*-->(.+)<!--\s*[\s]*END JS\s*[\s]*-->/isU";
		$jsdata1 = $this->matchContentByPattern($pattern2, $data);		
		$jsdata2 = $this->matchContentByPattern($pattern2, $blanData);	
		
		if ($jsdata1 && $jsdata2) {
			$data = str_replace($jsdata1, $jsdata2, $data);
			$changed = true;
		}
				
		if (!$changed) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WANGING : no changed!");	
		}
		
		return $data;
		
	}	
	
	public function t2res($name, $outdir='')
	{
		if (!$name) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no name!");
			return false;
		}
		
		$dir = RPATH_THEME.DS.$name;
		if (!is_dir($dir)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no TPL dir '$dir'!");
			return false;
		}
		
		$blankfile = $dir.DS.'blank.html';
		$blankData = s_read($blankfile);
		if (!$blankData) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no blank file '$blankfile'!");
			return false;
		}
		
		$indexfile = $dir.DS.'index.html';
		//resurl
		$data = s_read($indexfile);
		if (!$data) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no index file '$indexfile'!");
			return false;
		}
		
		$this->parseHtmlMeta($data, $metadb);		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "metadb ...", $metadb);
		if (!isset($metadb['resurl']))
			return false;
			
		$resurl = $metadb['resurl'];
		
		
		
		//out
		!$outdir && $outdir = $dir.DS.'out';
		if (!is_dir($outdir))
			s_mkdir($outdir);
					
		if (!is_dir($outdir)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no outdir '$outdir'!");
			return false;
		}
		
		$fdb = $this->readdir($dir);
		foreach ($fdb as $key=>$v) {
			
			$filename = $v['filename'];
			$srcfile = $dir.DS.$filename;
			$data = s_read($srcfile);
			
			//CSS/JS替换
			$data = $this->fixedCssJSSWithBlank($data, $blankData);
			
			$olddb = array();
			$newdb = array();
			//src
			$res1 = $this->checkResByTag('src', $data, $olddb, $newdb, $resurl, $outdir);
			//url
			$res2 = $this->checkResByTag('url', $data, $olddb, $newdb, $resurl, $outdir);
			
			if ($newdb) {
				$data = str_replace($olddb, $newdb, $data);					
				$outfile = $outdir.DS.$filename;	
				s_write($outfile, $data);	
			}
		}
		$res = $res1 || $res2;
		
		return $res;
		
	}	
	
	
	
	protected function getHeaderAndFooterBody($data, &$headerData, &$footerData, &$bodyData)
	{
		//<!-- BEGIN PAGECONTENT -->
		//<!-- END PAGECONTENT -->
		$matches = array();
		$res = preg_match_all("/<!--\s*[\s]*BEGIN PAGECONTENT\s*([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\s*[\s]*-->(.+)<!--\s*[\s]*END PAGECONTENT\s*[\s]*-->/isU", $data, $matches);
		
		if ($res && count($matches[1]) == 1) {
			$prefix = false;
			$nr = count($matches);
			if ($nr > 1 && $matches[1][0]) {
				$args = attr2array2(strtolower($matches[1][0]));
				foreach ($args as $k2 => $v2) {
					if ($k2 == 'prefix') {
						$prefix = trim($v2);
					}
				}
			}
			
			if ($prefix) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "skip has prefix tpl!");
				return false;
			}
			
			
			$bodyData = $matches[3][0];
			$len = strlen($bodyData);
			
			$pos = strpos($data, $bodyData);
			
			$headerData = substr($data, 0, $pos);
			$footerData = substr($data, $pos+$len);
			
			return true;
		}	
		
		return false;
	}
	
	protected function fixedHeaderByBlank($filename, $hData, $blankHeaderData)
	{
		// title
		$title = strtoupper(s_filename2name($filename));
		$blankHeaderData = str_replace('BLANK', $title, $blankHeaderData);
		
		//导航先定位位到
		$res = preg_match_all("/<!--\s*[\s]*BEGIN\s+NAVBAR\s*[\s]*-->(.+)<!--\s*[\s]*END\s+NAVBAR\s*[\s]*-->/isU", $blankHeaderData, $matches);
		if ($res && !empty($matches[1][0])) {
			$navbarData = $matches[0][0];
			
			//NAV 加active eg:<li class="dropdown"> <a href="catalog-07.html" class="btn btn-lg " title="新闻关注"> 新闻关注 </a> </li>
			$pattern = "/<li([^>]*)>(\s*[\s]*<a\b\s*href=\s*[\s]*[\'\"]?([^\'\"]*$filename*[^\'\"]+)[\'\"]?.+>(.+)<\/a>.*)<\/li>/isU";
			$res = preg_match_all($pattern, $navbarData, $matches);
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $filename, $matches);
			
			/*
			Array
			(
			    [0] => Array
			        (
			            [0] => <li class="dropdown"> <a href="catalog.html" class="btn btn-lg " title="思政教育"> 思政教育 </a> </li>
			        )
					
			    [1] => Array
			        (
			            [0] =>  class="dropdown"
			        )
					
			    [2] => Array
			        (
			            [0] =>  <a href="catalog.html" class="btn btn-lg " title="思政教育"> 思政教育 </a>
			        )
					
			    [3] => Array
			        (
			            [0] => catalog.html
			        )
					
			    [4] => Array
			        (
			            [0] =>  思政教育
			        )
					
			)
			*/
			if ($res && ($nr = count($matches[0])) == 1) {
				
				$line = $matches[0][0];
				$attrstr = $matches[1][0];
				$inner = $matches[2][0];
				if (!empty($attrstr)) {
					$attr = attr2array2($attrstr);
					$attr['class'] .= ' active';
					$attrstr = array2attr($attr);
				} else {
					$attrstr = ' class="active" ';
				}
				$newline = "<li $attrstr>$inner</li>";
				
				//$navbarData
				$newNavbarData = str_replace($line, $newline, $navbarData);
				
				$blankHeaderData = str_replace($navbarData, $newNavbarData, $blankHeaderData);
			}
		}
		
		return $blankHeaderData;
		
	}
	
	/*
	 * t2hf
     *	
	 * transfer html's header and footer
	
	 * 用blank.html的头替换除index.html之外的所有页面的头与尾
	*/
	public function t2hf($name, $outdir='')
	{
		if (!$name) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no name!");
			return false;
		}
		
		$dir = RPATH_THEME.DS.$name;
		if (!is_dir($dir)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no TPL dir '$dir'!");
			return false;
		}
		
		$blankfile = $dir.DS.'blank.html';
		$blankData = s_read($blankfile);
		if (!$blankData) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no blank file '$blankfile'!");
			return false;
		}
		
		$res = $this->getHeaderAndFooterBody($blankData, $headerData, $footerData, $bodyData);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "call getHeaderAndFooterBody failed!");
			return false;
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "====================================HEADER====================================", $headerData);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "====================================FOOTER====================================", $footerData);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "====================================BODY====================================", $bodyData);
		
		//标题
		//导航 active

		
		//out
		!$outdir && $outdir = $dir; //.DS.'out';
		if (!is_dir($outdir))
			s_mkdir($outdir);
		
		if (!is_dir($outdir)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no outdir '$outdir'!");
			return false;
		}
		
		$skipdb = array('index.html', 'blank.html');
		
		$fdb = $this->readdir($dir);
		foreach ($fdb as $key=>$v) {
			
			$filename = $v['filename'];
			if (in_array($filename, $skipdb))
				continue;
			
			$srcfile = $dir.DS.$filename;
			$data = s_read($srcfile);
			
			$res1 = $this->getHeaderAndFooterBody($data, $hData, $fData, $bData);
			if (!$res1) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "call getHeaderAndFooterBody failed!");
				continue;
			}
			
			//制作新头
			$newHeaderData = $this->fixedHeaderbyBlank($filename, $hData, $headerData);
			
			//header
			$data = str_replace($hData, $newHeaderData, $data);
			$data = str_replace($fData, $footerData, $data);
			$outfile = $outdir.DS.$filename;	
			$res2 = s_write($outfile, $data);	
			
		}
		$res = $res1 || $res2;
		
		return $res;
		
	}	
	
		
	public function t2index($name)
	{
		if (!$name) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no name!");
			return false;
		}
		
		$dir = RPATH_THEME.DS.$name;
		if (!is_dir($dir)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no TPL dir '$dir'!");
			return false;
		}
		
		$blankfile = $dir.DS.'blank.html';
		$indexfile = $dir.DS.'index.html';
		$dstindexfile = $dir.DS.'index.html.dst';
		
		$data1 = s_read($blankfile);
		if (!$data1) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no blank file '$blankfile'!");
			return false;
		}
		$data2 = s_read($indexfile);
		if (!$data2) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no index file '$indexfile'!");
			return false;
		}
		
		//CCS
		$pattern1 = "/<!--\s*[\s]*BEGIN CSS\s*([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\s*[\s]*-->(.+)<!--\s*[\s]*END CSS\s*[\s]*-->/isU";
		$cssdata1 = $this->matchContentByPattern($pattern1, $data1);		
		$cssdata2 = $this->matchContentByPattern($pattern1, $data2);
		
		$changed = false;
		if ($cssdata1 && $cssdata2) {
			$data2 = str_replace($cssdata2, $cssdata1, $data2);
			$changed = true;
		}
		
		//JS
		$pattern2 = "/<!--\s*[\s]*BEGIN JS\s*([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\s*[\s]*-->(.+)<!--\s*[\s]*END JS\s*[\s]*-->/isU";
		$jsdata1 = $this->matchContentByPattern($pattern2, $data1);		
		$jsdata2 = $this->matchContentByPattern($pattern2, $data2);	
			
		if ($jsdata1 && $jsdata2) {
			$data2 = str_replace($jsdata2, $jsdata1, $data2);
			$changed = true;
		}
		
		
		if (!$changed) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "WANGING : no changed!");	
			return false;
		}
		
		$res = s_write($dstindexfile, $data2);
		
		return $res;
	
	}	
	
	/**
	 * t2t 
	 * Theme to Template
	 * 主题设计转换成模板
	 *
	 * @param mixed $name This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function t2t($name)
	{
		if (!$name) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no name!");
			return false;
		}
		
		$dir = RPATH_THEME.DS.$name;
		if (!is_dir($dir)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no TPL dir '$dir'!");
			return false;
		}
		
		$tdir = RPATH_TEMPLATES.DS.$name;
		if (!is_dir($tdir))
			mkdir($tdir);
		
		//清理缓存
		$file = $tdir.DS.'catalogdb.php';
		file_exists($file) && unlink($file);
		$file = $tdir.DS.'contentdb.php';
		file_exists($file) && unlink($file);
		
		$config = array();
		$modules = array();
		
		$titledb = array();
		//resource files
		$resdb = array();
		$positiondb = array();
		$allpositiondb = array();
		
		$fdb = $this->readdir($dir);
		foreach ($fdb as $key=>$v) {
			
			$filename = $v['filename'];
			
			$data = s_read($dir.DS.$filename);
			
			//PositionBar
			$allpositiondb[$filename] = $this->t2tParsePositionBar($data, $positiondb);
			
			$data = $this->t2tData($name, $filename, $data, $modules, $config, $titledb, $resdb);
			
			//include
			$data = $this->t2tParseIncludeHeader($name, $filename, $tdir, $data, $config);
			
			//检查文件名
			if (!$this->checkTemplateFile($filename)) {
				continue;
			}
			
			$tname = ($filename == $name.'.html')?'index.htm':str_replace('html', 'htm', $filename);
			
			$tplfile = $tdir.DS.$tname;	
			s_write($tplfile, $data);		
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $allpositiondb);
		
		//解titles
		$alltdb = $this->t2tParseTitleForContentAndCatalog($titledb, $modules);	
				
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$alltdb ...', $alltdb);
		
		//moduleNames
		$moduleNames = array();
		foreach ($modules as $key=>$v) {
			$moduleNames[$v['name']] = $v['name'];
		}
		$dmodules = implode(' ', $moduleNames);
		
		$indextplname = 'index.html';
		$indexinfo = array();
		foreach ($config as $key=>$v) {
			if (!$indexinfo) {
				$indexinfo = $v;
			}
			if ($key == $indextplname) {
				$indexinfo = $v;
				break;
			}
		}
		
		$title = isset($indexinfo['title'])?$indexinfo['title']:$name;
		$description = isset($indexinfo['description'])?$indexinfo['description']:$name;
		$version = isset($indexinfo['version'])?$indexinfo['version']:'0.1.0';
		$resurl = isset($indexinfo['resurl'])?$indexinfo['resurl']:'';
		
		//index
		/*$tplname = 'index';
		$scfvardb = isset($config[$tplname]['scfvardb'])?$config[$tplname]['scfvardb']:array();
		if ($resurl && !isset($scfvardb['resurl'])) {
			$scfvardb['resurl'] = $resurl;
			$config[$tplname]['scfvardb'] = $scfvardb;
		}*/
				
		
		//config
		$configdata = <<<EOT
<?php
\$appcfg = array (
	'name' => '$name',
	'title' => '$title',	
	'description' => '$description',	
	'dmodules'=>'$dmodules',
	'version' => '$version',
	'resurl' => '$resurl',
	'copyright' => 'RC',
	'website' => 'https://www.relaxcms.com',
	'uninstall' => true,
);
EOT;
		$cfgfile = $tdir.DS.'config.php';		
		s_write($cfgfile, $configdata);	
		
		//preview.jpg
		$previewdir =  $dir.DS.'img';
		$previewfile = $previewdir.DS.'preview.jpg';
		if (!file_exists($previewfile)) 
			$previewfile = $previewdir.DS.'preview.png';
		
		if (file_exists($previewfile)) {
			copy($previewfile, $tdir.DS.s_filename($previewfile));
		} 
		
	
		//template config
		$tpldb_file = $tdir.DS.'tplcfg.php';
		cache_array('tplcfg', $config, $tpldb_file);
		
		
		
		$contentdb = array();
		$catalogdb = array();
		$vardb = array();
		
		foreach ($alltdb as $key=>$v) {
			//
			//htmlspecialchars($v['innerHTML']);
			//unset($v['taghtml']);
			//unset($v['innerHTML']);
			$v['taghtml'] = htmlspecialchars($v['taghtml'], ENT_QUOTES);
			$v['innerHTML'] = htmlspecialchars($v['innerHTML'], ENT_QUOTES);
			$v['content'] = htmlspecialchars($v['content'], ENT_QUOTES);
			
			
			if ($v['ttype'] & TT_CATALOG) {
				//fixed for 'pid'
				if (!isset($v['pid']) && isset($positiondb[$key])) {
					$pname = $positiondb[$key];
					if (isset($alltdb[$pname])) {
						$v['pid'] = $pname;
					}
				}
					
				$catalogdb[$key] = $v;
			}
			
			if ($v['ttype'] & TT_CONTENT) {
				//fixed for 'cid'
				if (!isset($v['cid']) && isset($positiondb[$key])) {
					$pname = $positiondb[$key];
					if (isset($catalogdb[$pname])) {
						$v['cid'] = $pname;
					}
				}
				
				if (empty($v['cid']) && isset($allpositiondb[$v['href']])) { //未知cid
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, " ######### ", $v);
					$v['cid'] = $this->t2tProbeContentCIDByPosition($allpositiondb[$v['href']]);					
				}
				
				$contentdb[$key] = $v;				
			}
			
			if ($v['ttype'] & TT_VAR) {
				$vardb[$key] = $v;
			}
		}
		
		foreach ($catalogdb as $key=>&$v2) {
			//_modname
			$href = $v2['href'];
			$_modname = (isset($config[$href]) && isset($config[$href]['modname']))?$config[$href]['modname']:'catalog'; 
			
			$v2['_modname'] = $_modname;
			
			$this->initCatalogTplListAndTplContent($name, $v2, $contentdb);			
		}
		
		
		//check model link and style
		foreach ($catalogdb as $key=>&$_v2) {
			//_modname
			$href = $_v2['href'];
			if (!empty($_v2['link']) && !isset($config[$href]) && $_v2['_modname'] == 'catalog') {
				foreach ($catalogdb as $k3=>$v3) {
					if ($v3['pid'] == $_v2['name'] && $v3['_modname'] != 'catalog') { //包含子目录
						$_v2['type'] = 2;//CATALOG_TYPE_LINKMODELTREE
						
						if (isset($v3['style']))
							$_v2['style'] = $v3['style'];// eg:CATALOG_STYLE_DROPDOWNDTAB
						
						$_v2['modname'] = $v3['_modname'];
						break;	
					}										
				}
			}
		}	
			
			
		
				
		cache_array('contentdb', $contentdb, RPATH_TEMPLATES.DS.$name.DS.'contentdb.php');
		cache_array('catalogdb', $catalogdb, RPATH_TEMPLATES.DS.$name.DS.'catalogdb.php');
		
		//write resource file list
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $resdb);
		$resdata = '';
		foreach ($resdb as $key=>$v) {
			$resdata .= "$key\n";
		}
		s_write(RPATH_TEMPLATES.DS.$name.DS.'resource.txt', $resdata);
		
		
		//t2index
		//$this->t2index($name);	
		
		return true;
	}
	
	
	
	///////////////////////////// 模板编译 /////////////////////////////////////////////////////////////////////
		
	protected function __parseTemplateInclude($data, $options)
	{
		
		$tdir = $options['tdir'];
		$app_tdir = $options['app_tdir'];
		$def_tdir = RPATH_TEMPLATE_DEFAULT;
		$libdef_tdir = RPATH_LIBTEMPLATE_DEFAULT;
		
		$replace = array();
		$matches = array();
		if (preg_match_all('#<rdoc:include\s+file=(.*)\s*(/?>|(\s*>(.*)</rdoc:include>))#isU', $data, $matches))
		{
			$tpls = $matches[1];
			$i = 0;
			//开头与结尾的' " 定界符去掉
			foreach($tpls as $key=>$v) {
				$tplfile = trimfilename($v);
				if (!isset($options['cfg_tdir']) || !file_exists($options['cfg_tdir'].DS.$tplfile)) {
					$tplpathname  = $tdir.DS.$tplfile; //app template dir
					if (!file_exists($tplpathname)) {
						rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "NOT Found TPL '$tplpathname'!");					
						$tplpathname  = $app_tdir.DS.$tplfile; // current template dir
						if (!file_exists($tplpathname)) {
							rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "NOT Found TPL '$tplpathname'!");
							$tplpathname  = $def_tdir.DS.$tplfile; // default tempatel dir						
							if (!file_exists($tplpathname)) {
								rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "NOT Found TPL '$tplpathname'!");
								$tplpathname  = $libdef_tdir.DS.$tplfile; // lib default tempatel dir						
							}						
						}
					}
				} else {
					$tplpathname = $options['cfg_tdir'].DS.$tplfile;
				}
				
				if (!file_exists($tplpathname)) {
					$tmp = "NOT FOUND template : $tplpathname";
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "NOT Found TPL '$tplpathname'!");
				} else {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "Found TPL '$tplpathname'!");
					
					$tmp = s_read($tplpathname);
					$tmp = strim_bom($tmp);
				}		
				$replace[$i++] = $tmp;
			}	
			$data = str_replace($matches[0], $replace, $data);	
			$data = $this->__parseTemplateInclude($data, $options);
		}	
		return $data;
	}
	
	
	
	
	protected function initCatalog_unused($name)
	{
		$m = Factory::GetModel('catalog');
		$res = $m->getOne(array('name'=>$name));
		if ($res) 
			return $res['id'];
			
		$params = array();
		$params['name'] = $name;
		
		$res = $m->set($params);
		
		if ($res) {
			return $params['id'];
		}
		return false;		
	}
	
	protected function autoCreateTplModule($params)
	{
		$m = Factory::GetModel('module');
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $params);
		$res = $m->getOne(array('mid'=>$params['mid']));
		if ($res)
			$params['id'] = $res['id'];
		
		//content
		//$content = $params['content'];
		//str_replace('\'', '"', $content);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $content);
		
		$params['content'] = htmlspecialchars($params['content']);
		
		$res = $m->set($params);										
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set TPL module failed!", $params);
			return false;
		}
		
		return $params;
	}
	
	
	protected function parseTemplateForI18n($iid, $text, $toLang)
	{
		$m = Factory::GetModel('template');
		$text = $m->i18n($iid, $text, $toLang);
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "$iid, $text, $toLang", $text);
		
		
		return $text;
	}
	
	protected function parseTemplateForVar($params, $content, $toLang)
	{
		
		$m = Factory::GetModel('templatevar');
		$content = $m->parseTemplateVarForI18n($params, $content, $toLang);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $content, $toLang, $params);
		
		
		return $content;
	}
	
	
	//解析
	private function parseTemplate($tplfile, $data, $options)
	{
		$cdir = dirname($tplfile);

		//$tplname
		$tplname = s_filename($cdir);
		
		//INCLUDE FILE
		$data = $this->__parseTemplateInclude($data, $options);
				
		//{form_rule fr=$formRules name='curpassword'}
		$replace = array();
		$matches = array();
		if (preg_match_all('#{(\w+)\s+(.*)}#isU', $data, $matches)) {
			$count = count($matches[1]);	
			for ($i=0; $i<$count; $i++) {
				$params = str_replace('=', '=>', $matches[2][$i]);
				$params = trim($params);
				$params = str_replace(' ', ',', $params);
				
				$fn  = $matches[1][$i].'(array('.$params.'))';				
				$replace[$i] = "\r\nEOT;\r\necho $fn;\r\nprint <<<EOT\r\n";
			}
			$data = str_replace($matches[0], $replace, $data);
		}		
		
		//function
		$s = array("{@", "@}", "<!--#", "#-->");			
		$e = array("\r\nEOT;\r\necho ", ";\r\nprint <<<EOT\r\n", "\nEOT;\n", "\nprint <<<EOT\n");
		
		if (function_exists('str_ireplace')){
			$data = str_ireplace($s, $e, $data);
		} else {
			$data = str_replace($s, $e, $data);
		}
		
		//language
		$t = $options['_i18ndb'];
		
		//t
		$replace = array();
		$matches = array();		
		if (preg_match_all('#@t\[(.*)\]#isU', $data, $matches)) {
			$nr = count($matches[1]);
			
			for ($i=0; $i<$nr; $i++) {
				$key = trim($matches[1][$i], '\'\"');			
				if (isset($t[$key])) {
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "$i : found key '$key'");
					$replace[$i] = $t[$key]; //'{$t[\''.$key.'\']}'; //格式化：{$t['$key']}
				} /*else if (isset($T[$key])) {
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "$i : found key '$key'");
					$replace[$i] = '{$T[\''.$key.'\']}'; //格式化：{$T['$key']}
				}*/  else {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING: $i : no key '$key'!");
					$replace[$i] = $key;
				}
			}
			$data = str_replace($matches[0], $replace, $data);
		}
		//<i>???</i>
		$replace = array();
		$matches = array();		
		$res = preg_match_all("/<i\s*?([id=('|\"|?)[^(\1)].+(\1)?]*)?>(.+)<\/i>/isU", $data, $matches);
		if ($res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $matches);
			$nr = count($matches[0]);
			for($i=0; $i<$nr; $i++) {
				$text = $matches[3][$i];
				$arr = attr2array2($matches[1][$i]);
				
				//译
				$text = $this->parseTemplateForI18n($arr['iid'], $text, $options['_lang']);
				
												
				$replace[$i] = $text;
			}
			
			$ext = s_extname($tplfile);						
			
			$data = str_replace($matches[0], $replace, $data);
		}
		
		//var
		$replace = array();
		$matches = array();	
		$res = preg_match_all("/<(\w+)([^>]*\s+var[\s=]?[^>]*)\s*>(.+)<\/(\\1)>/isU", $data, $matches);	
		if ($res) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $matches);
			$nr = count($matches[0]);
			for($i=0; $i<$nr; $i++) {
				$content = $matches[0][$i];
				$arr = attr2array2($matches[2][$i]);
				
				$tagname =  trim($matches[1][$i]);
				$innertext = $matches[3][$i];
				$_innertext = str_replace("'", '"', trim($innertext));
				
				$name = $arr['var']?$arr['var']:$innertext;
				$title = $arr['title']?$arr['title']:$name;
				$description = $arr['description']?$arr['description']:'';
				
				//href
				$href = $arr['href']?$arr['href']:'';
				//src
				$src = '';
				if (is_start_with($_innertext, "<img")) {
					//<img
					$res = preg_match_all("/src=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?/i", $_innertext, $mdb);
					if ($res) {
						$src = $mdb[1][0];
						$_innertext = trimHTML($_innertext);
					}
				} 
				
				$arr['tagname'] = $tagname;
				$arr['src'] = $src;
				$arr['href'] = $href;
				$arr['innertext'] = $_innertext;
				
				
								
				//译
				$content = $this->parseTemplateForVar($arr, $content, $options['_lang']);
				
				
				$replace[$i] = $content;
			}
			
			$ext = s_extname($tplfile);						
			
			$data = str_replace($matches[0], $replace, $data);
		}
		
		$hasModule = is_model('module');
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$hasModule='.$hasModule);
		
		$m = Factory::GetModel('module');
		//$m2 = Factory::GetModel('module_params');
		$m3 = Factory::GetModel('module2tplfile');
		
		//links
		if ($hasModule && ($links = parseTplLinkData($data))) {
			//matchs
			$mdb = $links['mdb'];
			$nr = count($mdb);
			$olddb = array();
			$newdb = array();
			
			for ($i=0; $i<$nr; $i++) {
				$params = $mdb[$i];
				
				$mid = $params['mid'];
				$res = $m->getOne(array('mid'=>$mid));
				if (!$res) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: no mid '$mid' auto create!", $params);					
					$res = $this->autoCreateTplModule($params);
					if (!$res) {
						rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set TPL module failed!", $params);
						continue;
					}
				}
				
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
				
				$content = $params['content'];
				$url = $params['url'];				
				$src = $params['src'];
				$title = $params['title'];
				
				$olddb[$i] = $content;
				
				if ($res) {
					//content
					$mid = $res['id'];
					//查询 module_params
					$newurl = $res['url'];
					$newsrc = $res['src'];
					$newtitle = $res['title'];
					
					//replace content
					if ($url != $newurl)
						$content = str_replace($url, $newurl, $content);
					if ($src != $newsrc) {
						$content = str_replace($src, $newsrc, $content);
					} else {
						//check src
						/*if ($newsrc && !is_url($newsrc) && !is_start_slash($newsrc) ) {
							$newsrc = $options['_theroot']."/$tplname/$newsrc";
							$content = str_replace($src, $newsrc, $content);
						}*/
					}
					
					if ($title != $newtitle)
						$content = str_replace($title, $newtitle, $content);
					
					//
					$_params = array();
					$_params['mid'] = $mid;
					$_params['tplfile'] = $tplfile;					
					$m3->set($_params);
				}
				$newdb[$i] = $content;	
			}
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $olddb, $newdb);
			
			if ($newdb) {
				$data = str_replace($olddb, $newdb, $data);
			}
		}
		
		//modules
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$tplfile='.$tplfile);
		if ($hasModule && ($modules = parseTplModuleData($data))) {
			//matchs
			$mdb = $modules['mdb'];
						
			$nr = count($mdb);
			
			$olddb = array();		
			$newdb = array();		
			
			for ($i=0; $i<$nr; $i++) {
				$params = $mdb[$i];
								
				$mid = $params['mid'];
				
				$res = $m->getOne(array('mid'=>$mid));
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: no mid '$mid' auto create!", $res, $params);					
				
				if (!$res) {
					$res = $this->autoCreateTplModule($params);
					if (!$res) {
						rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set TPL module failed!", $params);
						continue;
					}
				}
				
				$content = $params['content'];
				$old_attribs = $params['attribs'];
				$attribs = attr2array($old_attribs);
				$attribs['mid'] = $mid;				
				$olddb[$i] = $content;
				
				if ($res) {
					//content
					$mid = $res['id'];
					
					//check cid
					if (isset($attribs['cid']) && !is_numeric($attribs['cid']) 
							&& !is_start_with($attribs['cid'], '$')) {
						$modcatalog = Factory::GetModel('catalog');
						$cid = $modcatalog->createCatalog(array('name'=>$attribs['cid']));
						if ($cid) {
							$attribs['cid'] = $cid;
						}
					}
					
					//查询 module_params
					/*$res2 = $m2->getOne(array('mid'=>$mid));
					if ($res2) {*/
						//cid
						if ($res['cid'] > 0) {
						$attribs['cid'] = $res['cid'];
						}
						//flags
						if ($res['flags'] > 0) {
						$attribs['flags'] = $res['flags'];
						}
						//tags
					//}
					
					//title
					if ( !isset($attribs['title']) ) {
						$attribs['title'] = $res['title']?$res['title']:$res['name'];
					}
					
					
					$newattrs = array2attr($attribs);
					$content = str_replace($old_attribs, $newattrs, $content);
					
					
					//
					$_params = array();
					$_params['mid'] = $mid;
					$_params['tplfile'] = $tplfile;					
					$m3->set($_params);
				}
				
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $content);
				
				$newdb[$i] = $content;	
			}
						
			$data = str_replace($olddb, $newdb, $data);						
		}
		
		
		//clean
		$data = preg_replace ("#print <<<EOT\s+EOT;#", '', $data);
		$data = "<?php\r\nprint <<<EOT\r\n".$data."\r\nEOT;\n?>";
				
		return $data;
	}
	
	public function compileTemplate($tpl, $options, $tplfilename='')
	{
		$dir = RPATH_TEMPLATE_CPL;
		!is_dir($dir) && mkdir($dir);
		$lang = $options['_lang'];	
		$cache_file = $dir.DS.$tplfilename.'_'.md5($tpl.$options['_appname']).$lang.'.tpl';
		$mt = 0;
		//特定语言的模板，
		$i18n_tpl = str_replace('.htm', '-'.$lang.'.htm', $tpl);
		if (file_exists($i18n_tpl)) {
			$tpl = $i18n_tpl;
		}
			
		if (!file_exists($tpl)) {
			$data = "NO TPL '$tplfilename'!";			
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, $data);
		} else {
			$mt = filemtime($tpl);
		}
		
		if (!file_exists($cache_file) || !$mt || $mt > filemtime($cache_file)) {
			$mt && $data = s_read($tpl);						
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "load and compile TPL($lang) '$tpl' ...");			
			$data = $this->parseTemplate($tpl, $data, $options);
			s_write($cache_file, $data);			
		}
		return $cache_file;
	}
}
