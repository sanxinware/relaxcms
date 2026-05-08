<?php
/**
 * @file
 *
 * @brief 
 * 
 * 请求类
 * 
 * @copyright
 * Copyright (c), 2024, relaxcms.com
 *
 */
class CRequest extends CObject
{
	/**
	 * 请求方法
	 *
	 * @var mixed 
	 *
	 */
	protected $_method;
	
	/**
	 * URL
	 *
	 * @var mixed 
	 *
	 */
	protected $_url = null;
	
	
	/**
	 * URI
	 *
	 * @var mixed 
	 *
	 */
	protected $_uri = null;
	
	/**
	 * 应用程序调用者
	 *
	 * @var mixed 
	 *
	 */
	protected $_appname = null;
	protected $_org_appname = null;
	
	/**
	* the script name with out path, eg : 
	* SCRIPT_NAME = '/a1/index.php' 
	* $_basename = '/a1/index.php'
	* 
	* @var mixed 
	*
	*/
	protected $_basename;
	protected $_base;
	/**
	 * 去掉文件名称后剩下部分
	 * 
	 * 如：basename = '/rc5/admin.php
	 * 则：basepath = '/rc5
	 * 注：不含'/'
	 *
	 * @var mixed 
	 *
	 */
	protected $_basepath;
	
	protected $_basenameurl;
	protected $_baseurl;
	protected $_basepathurl;
	
	
	
	/**
	 * WEB相对根路径，格式如：/rc5,如果直接部署在wwwroot下，则 webroot= "" 
	 * 
	 * SCRIPT_NAME = '/rc5/index.php' 
	 * $_webroot = '/rc5'
	 */
	protected $_webroot;
	protected $_webrooturl;
	protected $_webpath = '';
	protected $_querystring = '';
	
	
	/**
	 * URI解析路径定位
	 *
	 * @var mixed 
	 *
	 */
	protected $_pathinfo = null;
	protected $_path = null;
	protected $_vpath = array();
	/**
	 * 是否重写：$_SERVER[REQUEST_RUI] == $_SERVER[SCRIPT_NAME] 前缀相同 非重写，否则是重写
	 *
	 * @var mixed 
	 *
	 */
	protected $_rewrite = false;
	
	protected $_is_default_index = false;
	
	
	/**
	 * 浏览器相关信息
	 *
	 * @var mixed 
	 *
	 */
	protected $_schema;
	protected $_client;
	protected $_realip;
	protected $_host;
	
	protected $_domain;
	protected $_rooturl;
	
	protected $_port;
	protected $_is_ssl;
	
	protected $_useragent;
	protected $_useragent_browser;
	protected $_useragent_browser_ver;
	protected $_useragent_os;
	protected $_useragent_os_ver;
	protected $_uainfo;
	protected $_language;
	
	/**
	 * 格式如：http[s]://192.168.10.238/rc5
	 *
	 * @var mixed 
	 *
	 */
	protected $_weburl;
	
	protected $_aname = '';
	protected $_cname = '';
	protected $_tname = '';
	protected $_oname = '';
	
	
	public function __construct($options=array())
	{
		$this->_options = $options;
		$this->_init();	
	}
	
	public function CRequest($options=array())
	{
		$this->__construct($options);	
	}
	
	static function &GetInstance($options=array())
	{
		static $instance;		
		if (!is_object($instance)) {
			$instance	= new CRequest($options);
		}		
		return $instance;
	}
	
	/**
		 * eg :
		 * SCRIPT_FILENAME = J:/dvlp/icloud/trunk/src/icloud/index.php
		 * WEB_PATH=J:/dvlp/icloud/trunk/src/icloud
		 * WEB_PATH=J:/dvlp/icloud/trunk/src/icloud/lib
				
		 * suburi = /index.php
		 * 
		 * 非标准)CGI/SSI环境变量，SCRIPT_URL和SCRIPT_URI

		 * [SCRIPT_URL] => /webdav/123
	   [SCRIPT_URI] => http://192.168.10.108/webdav/123
	      * 
	   [HTTP_HOST] => 192.168.10.108
	   [HTTP_USER_AGENT] => Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:99.0) Gecko/20100101 Firefox/99.0
	   [HTTP_ACCEPT] => text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*;q=0.8
	   [HTTP_ACCEPT_LANGUAGE] => zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2
	   [HTTP_ACCEPT_ENCODING] => gzip, deflate
	   [HTTP_CONNECTION] => keep-alive
	   [HTTP_COOKIE] => PHPSESSID=b31n1mlrus2d904l3ngm20u7d5
	   [HTTP_UPGRADE_INSECURE_REQUESTS] => 1
	   [HTTP_CACHE_CONTROL] => max-age=0
	   [PATH] => /usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/games:/usr/local/games
	   [SERVER_SIGNATURE] => 
	   [SERVER_SOFTWARE] => Apache/2.2.31 (Unix) mod_ssl/2.2.31 OpenSSL/1.0.2u DAV/2 SVN/1.8.5 PHP/5.6.40
	   [SERVER_NAME] => 192.168.10.108
	   [SERVER_ADDR] => 192.168.10.108
	   [SERVER_PORT] => 80
	   [REMOTE_ADDR] => 192.168.10.1
	   [DOCUMENT_ROOT] => /opt/crab/var/www
	   [SERVER_ADMIN] => admin@relaxcms.com
	   [SCRIPT_FILENAME] => /home/jonny/dvlp/rc6/trunk/src/web/test/test_webdav.php
	   [REMOTE_PORT] => 60448
	   [GATEWAY_INTERFACE] => CGI/1.1
	   [SERVER_PROTOCOL] => HTTP/1.1
	   [REQUEST_METHOD] => GET
	   [QUERY_STRING] => t=PROPFIND
	   [REQUEST_URI] => /webdav/123?t=PROPFIND
	   [SCRIPT_NAME] => /rc6/test/test_webdav.php
	   [PATH_INFO] => /123
	   [PATH_TRANSLATED] => /opt/crab/var/www/123
	   [PHP_SELF] => /rc6/test/test_webdav.php/123
	   [REQUEST_TIME_FLOAT] => 1651563221.973
	   [REQUEST_TIME] => 1651563221
	
	*/
	protected function _init()
	{
		//check rewrite
		$scriptfilename = basename($_SERVER['SCRIPT_FILENAME']);		
		$scriptname = $_SERVER['SCRIPT_NAME'];
		//script_name: /rc/index.php/f/preview/67/新建文件夹-3.
		//script_name: /rc/index.php/index
		if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO']) {
			$pos = strpos($scriptname, $scriptfilename);
			
			//basepath, 不含'/', eg: [SCRIPT_NAME] => /rc/test/test_base.php, 则basepath为：/rc/test			
			$basepath = substr($scriptname, 0, $pos-1);//不带 '/'
			$basename = $basepath.'/'.$scriptfilename;
			$scriptname = $basename;
		} else {
			$basename = $scriptname;
			$pos = strpos($basename, $scriptfilename);
			$basepath = substr($basename, 0, $pos-1);
			/*$basepath = dirname($basename);	
			if (is_windows()) {
				$basepath = str_replace(DS, '/', $basepath);
			}*/
		}
		
		$len1 = strlen($_SERVER['REQUEST_URI']);
		$len2 = strlen($scriptname);
		$len = min($len1, $len2);
		
		$rewrite = 0 === strncmp($_SERVER['REQUEST_URI'], $scriptname, $len)?false:true;
		
		//basename
		/*
		//    [REQUEST_URI] => /rc/test/base/a/b/c?id=1
		//    [DOCUMENT_ROOT] => F:/dvlp/icloud/trunk/src/web
		//    [SCRIPT_FILENAME] => F:/dvlp/rc/trunk/src/web/public/test/test_base.php
		//    [SCRIPT_NAME] => /rc/test/test_base.php
		*/		
		/*$basename = '';
		if (!IS_CLI) {
			if (basename($_SERVER['SCRIPT_NAME']) === $scriptfilename) {
				$basename = $_SERVER['SCRIPT_NAME'];
			} elseif (basename($_SERVER['PHP_SELF']) === $scriptfilename) {
				$basename = $_SERVER['PHP_SELF'];
			} elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptfilename) {
				$basename = $_SERVER['ORIG_SCRIPT_NAME'];
			} elseif (($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptfilename)) !== false) {
				$basename = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $scriptfilename;
			} elseif (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0) {
				$basename = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
			}				
		}*/
		
		//uri
		if (isset($_SERVER['REQUEST_URI'])) {
			$uri = $_SERVER['REQUEST_URI'];
		} elseif (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
			$uri = $_SERVER['HTTP_X_REWRITE_URL'];
		} elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
			$uri = $_SERVER['ORIG_PATH_INFO'] . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
		} elseif (isset($_SERVER['argv'][1])) {
			$uri =  $_SERVER['argv'][1];
		} else {
			$uri= '';
		}
		
		//path
		$pos = strpos($uri,'?');
		if ($pos === false) {
			$uripath = $uri;
			$querystring = '';
		} else {
			$uripath = substr($uri, 0, $pos);
			$querystring = substr($uri, $pos+1);
		}
		
		
		//path
		$is_default_index = false;		
		if ($rewrite) {
			/*
			重定向后，base不同于 [SCRIPT_NAME]
			eg:
			[REQUEST_URI] => /rc/test/base/a/b/c?id=1
			[SCRIPT_NAME] => /rc/test/test_base.php
			
			eg2:
			 [REQUEST_URI] => /rc/index.php/f/preview/67/%E6%96%B0%E5%BB%BA%E6%96%87%E4%BB%B6%E5%A4%B9-3.
			 [SCRIPT_NAME] => /rc/index.php/f/preview/67/新建文件夹-3.
			 [PATH_INFO] => /f/preview/67/新建文件夹-3
			 [PATH_TRANSLATED] => C:\crab\var\www\public\f\preview\67\新建文件夹-3
			 [PHP_SELF] => /rc/index.php/f/preview/67/新建文件夹-3.
			*/
			//path
			$len3 = strlen($scriptfilename);
			//取path时，跳过这个长度, //以'/'开头
			$path = $len2 > $len3?substr($uripath, $len2-$len3-1):'/';			
			
			$base = $basepath; 
			
		} else {
			//eg: /rc, '/rc/', '/rc/index.php', '/rc/index.php/'
			$len5 = strlen($uripath);
			if ($len5 > $len2) { //默认页面，不带文件名，如：/rc/
				$path = substr($uripath, $len2);		
			} else {		
				$path = '/';		
				if ($len5 < $len2)	
					$is_default_index = true;		
			}			
			
			$base = $basename;			
		}
		//appname
		//default appname, eg: admin or index, ...
		$appname = s_filename2name($scriptfilename);
		
		//first part of path, eg: admin, list 
		$vpath = explode('/', $path, 3);
		$firstpartname = $vpath[1];
		
		//重定向
		if ($firstpartname) {
			switch ($firstpartname) {
				case 'file':
					$appname ='admin';	
					break;					
				case 'f': //api定位
					$path = '/api'.$path;	
					break;	
				case 'api':		
					$appname ='admin';		
					$base .= '/'.$firstpartname;
					break;			
				case 'admin': //重定向
				case 'webdav':
					$appname = $firstpartname;		
					$path = '/'.$vpath[2];		
					$base .= '/'.$firstpartname;			
					break;	
					
				case 'system'://后台别名
					$path = '/'.$vpath[2];		
					$base .= '/'.$firstpartname;			
					$appname ='admin';		
					break;							
											
				default:
					if (file_exists(RPATH_PUBLIC.DS.$firstpartname.'.php')) {
						$appname = $firstpartname;
						if ($rewrite)
							$base .= '/'.$firstpartname;
					} else if (file_exists(RPATH_APPS.DS.$firstpartname.DS.$firstpartname.'.php')) {
						//aname
						$this->_aname = $firstpartname;
					}
					break;
			}
						
		}
		
		/*rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "rewrite=$rewrite, 
					scriptfilename=$scriptfilename, 
					scriptname=$scriptname, 
					basename=$basename, 
					basepath=$basepath, 
					uripath=$uripath,
					querystring=$querystring,
					path=$path, 
					base=$base,
					appname=$appname",					
					$vpath,
					$_SERVER);*/
		//exit;
		
		
		//check rewrite
		$this->_rewrite = $rewrite;	
		$this->_basename = $basename;	
		$this->_basepath = $basepath;	
		$this->_base = $base;	
		$this->_appname = $appname;	
		$this->_path = $path;	
		$this->_uri = $uri;	
		$this->_querystring = $querystring;	
		
		$this->_is_default_index = $is_default_index;
		
	}
	
	public function method()
    {
        if (!$this->_method) {
            if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
                $this->_method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
            } else {
                $this->_method = $_SERVER['REQUEST_METHOD'];
            }
        }
        return $this->_method;
    }
	
	
	
	public function client()
	{
		if (!$this->_client) {
			//client
			$client = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'127.0.0.1';
			
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {
				$realip = $_SERVER['HTTP_CLIENT_IP'];
			} else {
				$realip = $_SERVER['REMOTE_ADDR'];
			}		
			
			//(!is_ip4($client) && !is_ip6($client)) && $client = 'unknown';
			
			$this->_client = $client;		
			$this->_realip = $realip;		
			
		}
		return $this->_client;		
	}
	
	
	public function realip()
	{
		if (!$this->_realip) {
			$this->client();
		}
		
		return $this->_realip;
	}
	
	/**
	 * url
	 * eg: http://localhost/rc/test/test_base.php/a/b/c?id=1
	 * 
	 * 重写时，如：http://localhost/rc/test/base/a/b/c?id=1
	
	*/
	public function url()
	{
		if (!$this->_url) {
			$this->_url = $this->schema().'://'.$this->host().$this->_uri;			
		}		
		return $this->_url;
	}
	
	public function uri()
	{
		return  $this->_uri;
	}	
	
	public function host($strict = false)
	{
		if (!$this->_host) {
			if (isset($_SERVER['HTTP_X_REAL_HOST'])) {
				$host = $_SERVER['HTTP_X_REAL_HOST'];
			} else {
				$host = $_SERVER['HTTP_HOST'];
			}			
			$this->_host = $host;
		}		
		return true === $strict && strpos($this->_host, ':') ? strstr($this->_host, ':', true) : $this->_host;
	}
	
		
	public function isSsl()
	{
		if (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
			return true;
		} elseif (isset($_SERVER['REQUEST_SCHEME']) && 'https' == $_SERVER['REQUEST_SCHEME']) {
			return true;
		} elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
			return true;
		} elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO']) {
			return true;
		} 
		return false;
	}
	
	/**
    * 当前URL地址中的scheme参数
    * @access public
    * @return string
    */
	public function schema()
	{
		if (!$this->_schema) {
			$this->_schema = $this->isSsl() ? 'https' : 'http';
		}
		return $this->_schema;
	}
	
	public function port()
	{
		return $_SERVER['SERVER_PORT'] ;
	}

	public function domain()
    {
		return $this->host(true);
    }
	
	public function rooturl()
	{
		if (!$this->_rooturl) {
			$this->_rooturl = $this->schema().'://'.$this->host();
		}
		return $this->_rooturl;
	}
	
	public function path()
	{
		return $this->_path;
	}
	
	public function vpath()
	{
		if (!$this->_vpath) {
			$path = $this->path();			
			if ($path) {
				$path = s_urldecode($path);
				//$path = safeEncoding($path);
				$vpath = ltrim($path, '/');
				if ($vpath) {			
					$this->_vpath = explode('/', $vpath);
				}
			}
		}
		return $this->_vpath;
	}
	
	/**
	 * basename
	 * 
	 *  
	 * 
	
	*/	
	public function basename($url=false)
	{
		return $this->_basename;			
	}
	
	public function basenameurl()
	{
		if (!$this->_basenameurl) {
			$rooturl = $this->rooturl();
			$this->_basenameurl = $rooturl.$this->_basename;	
		}
		return $this->_basenameurl;
	}
	
	public function base()
	{
		return $this->_base;
	}
	
	
	public function baseurl()
	{
		if (!$this->_baseurl) {
			$rooturl = $this->rooturl();
			$this->_baseurl = $rooturl.$this->_base;	
		}
		return $this->_baseurl;
	}
	
	public function basepath()
	{
		return $this->_basepath;
	}	
		
	public function basepathurl()
	{
		if (!$this->_basepathurl) {
			$rooturl = $this->rooturl();
			$this->_basepathurl = $rooturl.$this->_basepath;	
		}
		return $this->_basepathurl;
	}
	
			
	
	
	public function webroot()
	{
		if (!$this->_webroot) {
			$basepath = $this->_basepath;
			$filenamelen = strlen($_SERVER['SCRIPT_FILENAME']);
			$pathnamelen = strlen(RPATH_BASE);			
			if ($filenamelen < $pathnamelen) { //相对目录 ./index.php
				$len = 0;
			} else {
				$len = strlen($basepath) - ($pathnamelen - strlen(RPATH_PUBLIC));
			}
			$webroot = substr($basepath, 0, $len);		
			
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'len='.$len.', $basepath='.$basepath.',webroot='.$webroot, RPATH_PUBLIC, RPATH_BASE);exit;
			$this->_webroot = $webroot;
		}
		
		return $this->_webroot;
	}
	
	public function root()
	{
		return $this->webroot();
	}	
		
	
	public function aname()
	{
		//解析<ANAME>/<COMPONENT>/<TASK>		
		if (!$this->_aname) {
			if (isset($_REQUEST['app'])) {
				$aname = $_REQUEST['app'];
			} elseif (isset($_REQUEST['a'])) {
				$aname = $_REQUEST['a'];
			} else {
				$aname = '';
			}
			$this->_aname = $aname;
		}
		return $this->_aname;
	}
	
	
	public function cname($default='')
	{
		//解析<ANAME>/<COMPONENT>/<TASK>		
		if (!$this->_cname) {		
			if (isset($_REQUEST['component'])) {
				$cname = $_REQUEST['component'];
			} elseif (isset($_REQUEST['c'])) {
				$cname = $_REQUEST['c'];
			} else {
				$cname = $default;
			}
			$this->_cname = $cname;
		}
		return $this->_cname;
	}
			
			
	public function tname()
	{
		//解析<ANAME>/<COMPONENT>/<TASK>		
		if (!$this->_tname) {
			if (isset($_REQUEST['task'])) {
				$this->_tname = $_REQUEST['task'];
			} elseif (isset($_REQUEST['t'])) {
				$this->_tname = $_REQUEST['t'];
			}
		}
		return $this->_tname;
	}	
	
	public function oname()
	{
		//解析<ANAME>/<COMPONENT>/<TASK>		
		if (!$this->_oname) {
			if (isset($_REQUEST['output'])) {
				$oname = $_REQUEST['output'];
			} elseif (isset($_REQUEST['o'])) {
				$oname = $_REQUEST['o'];
			} else {
				$oname = ''; 
			}
			$this->_oname = $oname;
		}
		return $this->_oname;
	}	
				
	/**
	 * initOptions
	 *
	 * @param mixed $app This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function initOptions(&$options=array())
	{
		//method
		$options['method'] = $this->method();
		//url, eg: http://localhost/rc/test/base/a/b/c?id=1
		$options['url'] = $this->url();
						
		//uri, eg: /rc/test/base/a/b/c?id=1
		$options['uri'] = $this->_uri;
		$options['rewrite'] = $this->_rewrite; 
		$options['is_default_index'] = $this->_is_default_index; 
		
		//appname 应用名，不带扩展名的脚本名或重定向名称
		$options['appname'] = $this->_appname;
		
		//basepath, 保留[path]前缀到script_name或uri重写部分，eg：/rc/test/base, or /rc/test/test_base.php
		$options['base'] = $this->_base;
		$options['basename'] = $this->_basename; 
		//path, nwithout querystring of uri, eg: /rc/test/base/a/b/c
		$options['path'] = $this->_path;
		
		//base不带script_name或appname
		$options['basepath'] = $this->_basepath;
		
		$options['baseurl'] = $this->baseurl();
		$options['basenameurl'] = $this->basenameurl();
		$options['basepathurl'] = $this->basepathurl();
		//$options['baseroot'] = $this->baseroot();
		
		//rpath, relative path, the [path] without[pathbase], eg: /a/b/c
		//$options['rpath'] = $this->path();
		
		$options['_root'] = $this->root();
		$options['_rooturl'] = $this->rooturl();
		$options['_webroot'] = $this->webroot();
		$options['_weburl'] = $this->weburl();
		
		//$options['_path'] = $this->path();
		$options['vpath'] = $this->vpath();
		
		//$options['basename'] = $this->basename(); 
		//$options['basenameurl'] = $this->basenameurl();
		//$options['base'] = $this->base();
		
		//$options['_basename'] = $this->__basename();
		//$options['_basenameurl'] = $this->__basenameurl();
		//$options['_base'] = $this->__base();
		//$options['_baseroot'] = $this->__baseroot();
		//$options['_baseurl'] = $this->__baseurl();
		
		$options['_schema'] = $this->schema();
		$options['_host'] = $this->host();
		$options['_port'] = $this->port();
		$options['_domain'] = $this->domain();
		$options['_client'] = $this->client();
		$options['_realip'] = $this->realip();
		
		$options['_useragent'] = $this->useragent();
		//browser
		$options['_browser'] = $this->browser();
		$options['_browser_ver'] = $this->browserver();
		$options['_ismobile'] = $this->isMobile();
		
		
		$options['_dstroot'] = $options['_webroot'].'/static';		
		$options['_dstrooturl'] = $options['_weburl'].'/static';		
		$options['_dataroot'] = $options['_webroot'].'/data';
		$options['_theroot'] = $options['_dstroot'].'/themes';		
		
		$options['_dsturl'] = $options['_rooturl'].$options['_dstroot'];	
		$options['_theurl'] = $options['_rooturl'].$options['_theroot'];	
		
		$options['aname'] = $this->aname();
		$options['cname'] = $this->cname(isset($options['cname'])?$options['cname']:'');
		$options['tname'] = $this->tname();
		$options['oname'] = $this->oname();
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $options);exit;
		
		return $options;
	}
	
	public function dispatch($defappname='index', &$options=array())
	{	
		/*
			OPTIONS预检
			
			[HTTP_X_REQUESTED_WITH] => XMLHttpRequest
		    [HTTP_ORIGIN] => http://localhost:8080
		    [HTTP_CONNECTION] => keep-alive
		    [HTTP_REFERER] => http://localhost:8080/
		    [HTTP_COOKIE] => timetamp=1696334730595; PHPSESSID=fasu8296o9vsno3q8fpio0sjs5; login_first=1
		    [HTTP_SEC_FETCH_DEST] => empty
		    [HTTP_SEC_FETCH_MODE] => cors
		    [HTTP_SEC_FETCH_SITE] => same-site
			*/
		if (isset($_SERVER['HTTP_SEC_FETCH_MODE']) && $_SERVER['HTTP_SEC_FETCH_MODE'] == 'cors' 
				&& isset($_SERVER['HTTP_ORIGIN']) && $this->rooturl() != $_SERVER['HTTP_ORIGIN'] 
				&& $this->isOPTIONS()) 
			showStatus(0);
			
		if (!$this->_rewrite) { //非重写，不改变
			$appname = $defappname;
		} else {
			$appname = $this->_appname;
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $defappname, $appname, $this->_rewrite);
		
		$this->initOptions($options);			
		
		return $appname;
	}
	
	
	
	public function getWebroot()
	{
		return $this->_webroot;
	}
	
	public function getWeburl()
	{
		return $this->_weburl;
	}
	
	
	public function isIE8()
	{
		if (!$this->isIE())
			return false;
			
		return floor($this->browserver()) ==  8;
	}	

	public function isLeIE9()
	{
		if (!$this->isIE())
			return false;		
		return floor($this->browserver()) <= 9;
	}
	
	public function isIE()
	{
		return $this->browser() == 'msie';
	}	
	
	
	public function isChrome()
	{
		return $this->browser() == 'chrome';
	}	
	
	public function isChrome_49_0_2623_110()
	{
		if (!$this->isChrome())
			return false;	
		return $this->browserver() == '49.0.2623.110';
	}

	public function isMobile()
	{
		if (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap")) {
			return true;
		} elseif (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtoupper($_SERVER['HTTP_ACCEPT']), "VND.WAP.WML")) {
			return true;
		} elseif (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
			return true;
		} elseif (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT'])) {
			return true;
		} else {
			return false;
		}
	}
	
	
	public function isPOST()
	{
		return $this->method() == "POST";
	}
	
	public function isDELETE()
	{
		return $this->method() == "DELETE";
	}
	public function isGET()
	{
		return $this->method() == "GET";
	}
	
	public function isOPTIONS()
	{
		return $this->method() == "OPTIONS";
	}
	
	public function webrooturl()
	{
		return $this->rooturl().$this->_webroot;		
	}
	
	public function weburl()
	{
		return $this->rooturl().$this->_webroot;		
	}
	
	
	public function useragent()
	{
		if (!$this->_useragent) {
			
			$useragent = isset($_SERVER["HTTP_USER_AGENT"])?$_SERVER["HTTP_USER_AGENT"]:'UNKNOWN';
			$this->_useragent = $useragent;
			
			//firefox
			//Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:63.0) Gecko/20100101 Firefox/63.0
			
			//ie8
			//Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.0729; Media Center PC 6.0; .NET4.0C; .NET4.0E)
			
			//Mozilla/5.0 (Linux; Android 9; Redmi Note 8 Build/PKQ1.190616.001; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/86.0.4240.99 XWEB/4317 MMWEBSDK/20220505 Mobile Safari/537.36 MMWEBID/8179 MicroMessenger/8.0.23.2160(0x28001759) WeChat/arm64 Weixin NetType/WIFI Language/zh_CN ABI/arm64
			
			//Mozilla/5.0 (iPhone; CPU iPhone OS 15_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 MicroMessenger/8.0.29(0x18001d30) NetType/WIFI Language/zh_CN
			
			//Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/106.0.0.0 Safari/537.36
			
			$useragent = trim($useragent);
			if ($useragent) {
				
				//firefox : Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:63.0) Gecko/20100101 Firefox/63.0
				//ie8: Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727;
				// .NET CLR 3.5.30729; .NET CLR 3.0.0729; Media Center PC 6.0; .NET4.0C; .NET4.0E)
				$agent = strtolower($useragent);
				$pos = strpos($agent, "msie");
				if ($pos !== false) {
					$msie = substr($agent, $pos, 20);
					$q = strpos($msie, ';');
					$len = $q - 5;
					$ver = substr($msie, 5, $len);
					$ver = floor($ver);
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $ver);				
					$this->_useragent_browser_ver = $ver;
				}
				
				$uainfo = array();
				$uainfo['useragent'] = $useragent;
				
				$udb = explode(' ', $useragent);
				$nr = count($udb);
				
				$os_name = '';
				$os_ver = '';
				
				for ($i=0; $i<$nr; $i++) {
					$name = trim($udb[$i]);
					$val = true;
					if (($pos = strpos($name, '/')) !== false) {
						$val = substr($name, $pos+1);	
						$name = substr($name, 0, $pos);
					}
					
					$name = str_replace(array('(', ')',';'), array('', ''), $name);
					$name = strtolower($name);
					
					switch($name) {
						case 'mozilla':
							$this->_mozilla = $name;
							$this->_mozilla_ver = $val;
							break;						
						case 'chrome':
						case 'firefox': //Firefox/109.0, Chrome/109.0.0.0 Safari/537.36
							$this->_useragent_browser = $name;
							$this->_useragent_browser_ver = $val;
							break;						
						case 'msie':
							$this->_useragent_browser = $name;
							$this->_useragent_browser_ver = isset($udb[$i+1])?$udb[++$i]:'no';
							break;
						case 'iphone':
						case 'windows':
							$os_name = $name;
							break;
						case 'android':
							$os_name = $name;
						case 'nt':
							$os_ver = rtrim($udb[++$i],';');//Windows NT 6.1; 
							break;
						case 'language':
							$this->_language = $udb[++$i];
							break;
						default:
							break;
					}
					
					$uainfo[$name] = $val;			
				}
				
				$this->_uainfo = $uainfo;
				$this->_useragent_os = $os_name;
				$this->_useragent_os_ver = $os_ver;
			}
			
		}
		return $this->_useragent;		
	}
	
	public function browser()
	{
		if (!$this->_useragent_browser) {
			$this->useragent();
		}
		return $this->_useragent_browser;
	}
	public function browserver()
	{
		if (!$this->_useragent_browser) {
			$this->useragent();
		}
		return $this->_useragent_browser_ver;
	}	
	
	public function mozilla()
	{
		if (!$this->_useragent_browser) {
			$this->useragent();
		}
		return $this->_mozilla_ver;
	}
	
	public function browseros()
	{
		if (!$this->_useragent_browser) {
			$this->useragent();
		}
		return $this->_useragent_os;
	}
	
	public function browserosver()
	{
		if (!$this->_useragent_browser) {
			$this->useragent();
		}
		return $this->_useragent_os_ver;
	}
	
	
	public function language()
	{
		if (!$this->_useragent_browser) {
			$this->useragent();
		}
		return $this->_language;
	}
	
		
	public function getPathInfo()
	{
		return $this->_path;
	}

	
	protected function __clean_value_html($value, $type)
	{
		switch (strtoupper($type))
		{
			case 'INT' :
			case 'INTEGER' :
				$result = intval($value);
				break;
			case 'FLOAT' :
			case 'DOUBLE' :
				$result = floatval($value);
				break;
			case 'BOOL' :
			case 'BOOLEAN' :
				$result = (bool) $value;
				break;			
			case 'STRING' :
				$result = (string) $this->__remove($this->__decode((string) $value));
				break;
			
			case 'ARRAY' :
				$result = (array) $value;
				break;			
			case 'PATH' :
				$pattern = '/^[A-Za-z0-9_-]+[A-Za-z0-9_\.-]*([\\\\\/][A-Za-z0-9_-]+[A-Za-z0-9_\.-]*)*$/';
				preg_match($pattern, (string) $value, $matches);
				$result = @ (string) $matches[0];
				break;			
			case 'USERNAME' :
				$result = (string) preg_replace( '/[\x00-\x1F\x7F<>"\'%&]/', '', $value );
				break;
			default :
				break;
		}
		return $result;
	}
	
	protected static function __clean_value_array_item(&$value) {
		if (is_string($value))
			$value = trim($value);
	}
	
	
	protected function __clean_value_nohtml($value, $type)
	{
		if (is_array($value)) {
			array_walk_recursive($value, array('CRequest', '__clean_value_array_item'));
		}
		
		return $value;
	}
	
	
	protected function __clean_value($value, $mask = 0, $type=null)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $value);
		
		if (!($mask & 1) && is_string($value)) 
		{
			$value = trim($value);
		}
		
		if ($mask & 2)
		{
			//ARRAY			
		}
		elseif ($mask & 4)
		{
			$value = $this->__clean_value_html($value, $type);
		}
		else
		{
			$value = $this->__clean_value_nohtml($value, $type);
		}
		return $value;
	}
	
	protected function __clean_tags($source)
	{
		/*
		 * In the beginning we don't really have a tag, so everything is
		 * postTag
		 */
		$preTag		= null;
		$postTag	= $source;
		$currentSpace = false;
		$attr = '';	 // moffats: setting to null due to issues in migration system - undefined variable errors
		
		// Is there a tag? If so it will certainly start with a '<'
		$tagOpen_start	= strpos($source, '<');
		
		while ($tagOpen_start !== false)
		{
			// Get some information about the tag we are processing
			$preTag			.= substr($postTag, 0, $tagOpen_start);
			$postTag		= substr($postTag, $tagOpen_start);
			$fromTagOpen	= substr($postTag, 1);
			$tagOpen_end	= strpos($fromTagOpen, '>');
			
			// Let's catch any non-terminated tags and skip over them
			if ($tagOpen_end === false) {
				$postTag		= substr($postTag, $tagOpen_start +1);
				$tagOpen_start	= strpos($postTag, '<');
				continue;
			}
			
			// Do we have a nested tag?
			$tagOpen_nested = strpos($fromTagOpen, '<');
			$tagOpen_nested_end	= strpos(substr($postTag, $tagOpen_end), '>');
			if (($tagOpen_nested !== false) && ($tagOpen_nested < $tagOpen_end)) {
				$preTag			.= substr($postTag, 0, ($tagOpen_nested +1));
				$postTag		= substr($postTag, ($tagOpen_nested +1));
				$tagOpen_start	= strpos($postTag, '<');
				continue;
			}
			
			// Lets get some information about our tag and setup attribute pairs
			$tagOpen_nested	= (strpos($fromTagOpen, '<') + $tagOpen_start +1);
			$currentTag		= substr($fromTagOpen, 0, $tagOpen_end);
			$tagLength		= strlen($currentTag);
			$tagLeft		= $currentTag;
			$attrSet		= array ();
			$currentSpace	= strpos($tagLeft, ' ');
			
			// Are we an open tag or a close tag?
			if (substr($currentTag, 0, 1) == '/') {
				// Close Tag
				$isCloseTag		= true;
				list ($tagName)	= explode(' ', $currentTag);
				$tagName		= substr($tagName, 1);
			} else {
				// Open Tag
				$isCloseTag		= false;
				list ($tagName)	= explode(' ', $currentTag);
			}
			
			
			/*
			 * Time to grab any attributes from the tag... need this section in
			 * case attributes have spaces in the values.
			 */
			while ($currentSpace !== false)
			{
				$attr			= '';
				$fromSpace		= substr($tagLeft, ($currentSpace +1));
				$nextSpace		= strpos($fromSpace, ' ');
				$openQuotes		= strpos($fromSpace, '"');
				$closeQuotes	= strpos(substr($fromSpace, ($openQuotes +1)), '"') + $openQuotes +1;
				
				// Do we have an attribute to process? [check for equal sign]
				if (strpos($fromSpace, '=') !== false) {
					/*
					 * If the attribute value is wrapped in quotes we need to
					 * grab the substring from the closing quote, otherwise grab
					 * till the next space
					 */
					if (($openQuotes !== false) && (strpos(substr($fromSpace, ($openQuotes +1)), '"') !== false)) {
						$attr = substr($fromSpace, 0, ($closeQuotes +1));
					} else {
						$attr = substr($fromSpace, 0, $nextSpace);
					}
				} else {
					/*
					 * No more equal signs so add any extra text in the tag into
					 * the attribute array [eg. checked]
					 */
					if ($fromSpace != '/') {
						$attr = substr($fromSpace, 0, $nextSpace);
					}
				}
				
				// Last Attribute Pair
				if (!$attr && $fromSpace != '/') {
					$attr = $fromSpace;
				}
				
				// Add attribute pair to the attribute array
				$attrSet[] = $attr;
				
				// Move search point and continue iteration
				$tagLeft		= substr($fromSpace, strlen($attr));
				$currentSpace	= strpos($tagLeft, ' ');
			}
			
			// Is our tag in the user input array?
			$tagFound = in_array(strtolower($tagName), $this->tagsArray);
			
			// If the tag is allowed lets append it to the output string
			if ((!$tagFound && $this->tagsMethod) || ($tagFound && !$this->tagsMethod)) {
				
				// Reconstruct tag with allowed attributes
				if (!$isCloseTag) {
					// Open or Single tag
					$attrSet = $this->_cleanAttributes($attrSet);
					$preTag .= '<'.$tagName;
					for ($i = 0; $i < count($attrSet); $i ++)
					{
						$preTag .= ' '.$attrSet[$i];
					}
					
					// Reformat single tags to XHTML
					if (strpos($fromTagOpen, '</'.$tagName)) {
						$preTag .= '>';
					} else {
						$preTag .= ' />';
					}
				} else {
					// Closing Tag
					$preTag .= '</'.$tagName.'>';
				}
			}
			
			// Find next tag's start and continue iteration
			$postTag		= substr($postTag, ($tagLength +2));
			$tagOpen_start	= strpos($postTag, '<');
		}
		
		// Append any code after the end of tags and return
		if ($postTag != '<') {
			$preTag .= $postTag;
		}
		return $preTag;
	}
	
	
	
	protected function __remove($value)
	{
		
		$loopCounter = 0;
		
		// Iteration provides nested tag protection
		while ($value != $this->_clean_tags($value))
		{
			$value = $this->__clean_tags($value);
			$loopCounter ++;
		}
		return $source;
	}
	
	protected function __decode($source)
	{
		// entity decode
		$trans_tbl = get_html_translation_table(HTML_ENTITIES);
		foreach($trans_tbl as $k => $v) 
		{
			$ttr[$v] = utf8_encode($k);
		}
		
		$source = strtr($source, $ttr);
		
		// convert decimal
		$source = preg_replace('/&#(\d+);/me', "utf8_encode(chr(\\1))", $source); // decimal notation
		
		// convert hex
		$source = preg_replace('/&#x([a-f0-9]+);/mei', "utf8_encode(chr(0x\\1))", $source); // hex notation
		return $source;
	}
	
	/**
	 * 取变量值
	 *
	 * @param mixed $name 变量名
	 * @param mixed $default 默认值
	 * @param mixed $hash 提定全局hash 表，如GET, POST, COOKIE等
	 * @param mixed $type This is a description
	 * @param mixed $mask This is a description
	 * @return mixed This is the return value description
	 *
	 */
	function get_var($name, $default = null, $hash = 'default', $type = 'none', $mask = 0)
	{
		$hash = strtoupper( $hash );
		if ($hash == 'METHOD') 
			$hash = strtoupper($_SERVER['REQUEST_METHOD']);
		
		$type	= strtoupper( $type );
		switch ($hash)
		{
			case 'GET' :
				$input = &$_GET;
				break;
			case 'POST' :
				$input = &$_POST;
				break;
			case 'FILES' :
				$input = &$_FILES;
				break;
			case 'COOKIE' :
				$input = &$_COOKIE;
				break;
			case 'ENV'    :
				$input = &$_ENV;
				break;
			case 'SERVER'    :
				$input = &$_SERVER;
				break;
			default:
				$input = &$_REQUEST;
				$hash = 'REQUEST';
				break;
		}
		
		if (!isset($input[$name]) || $input[$name] === null)
			return $default;
			
		$var = $this->__clean_value($input[$name], $mask, $type);	
		return $var;
		
	}
	
	public function set_var($name, $value = null, $hash = 'method', $overwrite = true)
	{
		if (!$overwrite && array_key_exists($name, $_REQUEST)) 
		{
			return $_REQUEST[$name];
		}
		
		$hash = strtoupper($hash);
		if ($hash === 'METHOD') 
			$hash = strtoupper($_SERVER['REQUEST_METHOD']);
		
		$previous	= array_key_exists($name, $_REQUEST) ? $_REQUEST[$name] : null;
		
		switch ($hash)
		{
			case 'GET' :
				$_GET[$name] = $value;
				$_REQUEST[$name] = $value;
				break;
			case 'POST' :
				$_POST[$name] = $value;
				$_REQUEST[$name] = $value;
				break;
			case 'COOKIE' :
				$_COOKIE[$name] = $value;
				$_REQUEST[$name] = $value;
				break;
			case 'FILES' :
				$_FILES[$name] = $value;
				break;
			case 'ENV':
				$_ENV['name'] = $value;
				break;
			case 'SERVER':
				$_SERVER['name'] = $value;
				break;
		}
		
		return $previous;
	}
	
	public function getComponent($default=null)
	{
		if ($this->component)
			return $this->component;
		if ($default)
			$this->component = $default;
		
		return $default;
	}
	
	public function setComponent($cname)
	{
		$this->component = $cname;		
	}
	
	public function getTask($default=null)
	{
		if ($this->task)
			return $this->task;
		if ($default)
			$this->task = $default;		
		return $default;
	}
	
	public function setTask($tname)
	{
		$this->task = $tname;		
	}
	
	//前缀
	protected function getCookiePre()
	{
		$cf = get_config();
		$hash = $cf["hash"];
		return substr(md5($hash),0,5);
	}	
	
	
	//COOKIE
	//设置
	
	/**
	 * setCookie
	 * 设置
	 *
	 * @param mixed $ck_var This is a description
	 * @param mixed $ck_value This is a description
	 * @param mixed $ck_time This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function setCookie($ck_var, $ck_value, $ck_time = 0)
	{
		$cf = get_config();
		
		$ts = time();
		$ssl = $_SERVER['SERVER_PORT'] == '443' ? 1:0;
		
		$ckdomain = $cf['ckdomain'];
		!$ckdomain && $ckdomain = "";
		
		$ckpath = s_slashify($this->_webroot);
		$ckpath2 = s_slashify($this->_baseroot);
		
		if ($ck_value) {
			if (is_array($ck_value)) {
				$res = serialize($ck_value);
			} else {
				$res = $ck_value;
			}
			$e = Factory::GetEncrypt();
			$ck_value = $e->mcrypt_des_encode($cf['ckey'], $res);
		}				
		
		//session_start();
		$ckname = $this->getCookiePre().'_'.$ck_var;
		
		if (!$ck_value)
		{
			$res = setcookie($ckname, $ck_value, 0);
			$res = setcookie($ckname, $ck_value, time()-30*3600*24, $ckpath, $ckdomain, $ssl);
			$res = setcookie($ckname, $ck_value, time()-30*3600*24, $ckpath2, $ckdomain, $ssl);
		}
		elseif ($ck_time === 0)
		{
			$res = setcookie($ckname, $ck_value);
		}
		else
		{
			$ck_time += $ts;
			$res = setcookie($ckname, $ck_value, $ck_time, $ckpath, $ckdomain, $ssl);			
		}
		
		return true;
	}
	
	public function getCookie($ck_var)
	{
		$e = Factory::GetEncrypt();
		$sid = $_COOKIE[$this->getCookiePre().'_'.$ck_var];
		
		$cf = get_config();		
		//$old = base64_decode($sid);
		//$str = des($old, $cf['ckey'], 1);
		$str = $e->mcrypt_des_decode($cf['ckey'], $sid);
		
		//解密		
		return $str;
	}
	
	public function viewtype()
	{
		return $_SERVER['HTTP_VIEWTYPE'];
	}
}


//提取整值
function get_int($name, $default = 0, $hash = 'default')
{
	if (!isset($_REQUEST[$name]))
		return $default;
	return intval($_REQUEST[$name]);
}

//提取浮点值
function get_float($name, $default = 0.0, $hash = 'default')
{
	$r = Factory::GetRequest();
	return $r->get_var($name, $default, $hash, 'float');
}

//提取BOOL值
function get_bool($name, $default = false, $hash = 'default')
{
	$r = Factory::GetRequest();
	return $r->get_var($name, $default, $hash, 'bool');
}

//提取Word类型值
function get_word($name, $default = '', $hash = 'default')
{
	$r = Factory::GetRequest();
	return $r->get_var($name, $default, $hash, 'word');
}

//提取cmd
function get_cmd($name, $default = '', $hash = 'default')
{
	$r = Factory::GetRequest();
	return $r->get_var($name, $default, $hash, 'cmd');
}

//提取字串
function get_string($name, $default = '', $hash = 'default', $mask = 0)
{
	$r = Factory::GetRequest();
	return $r->get_var($name, $default, $hash, 'string', $mask);
}

function get_var_raw($name, $default = '')
{
	$r = Factory::GetRequest();
	return $r->get_var($name, $default, $hash, 'default', 2);
}

function get_var($name, $default = null, $hash = 'default', $type = 'none', $mask = 0)
{
	$r = Factory::GetRequest();
	return $r->get_var($name, $default, $hash, $type, $mask);
}

function get_array($name)
{
	$arr = get_var($name);
	if (!$arr)
		return array();
	return $arr;
}

function get_vars()
{
	return $_REQUEST;
}

function set_var($name, $value = null, $method = 'POST', $overwrite = true)
{
	$r = Factory::GetRequest();
	return $r->set_var($name, $value, $method, $overwrite);
}

