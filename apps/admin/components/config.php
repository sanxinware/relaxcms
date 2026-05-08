<?php

/**
 * @file
 *
 * @brief 
 *  系统配置
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class ConfigComponent extends CFileDTComponent
{
	protected $_bgdir;
	protected $_logodir;


	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function ConfigComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function show(&$options = array())
	{
		$this->initActiveTab(10);
		
		
		$params = get_config();
				
		$this->assign("stime", tformat_current());
		
		$this->assign('tplname_select', get_common_select('template', $params['tplname']));
		$this->assign('thename_select', get_common_select('theme', $params['thename']));
		//var_dump($params['enable_simple_layout']);exit;
		$this->assignArray(ifcheck($params['enable_captcha'], "enable_captcha")); 
		$this->assignArray(ifcheck($params['enable_simple_layout'], "enable_simple_layout")); 	
		
		//title
		$params['title']  = isset($params['title'])? $params['title'] : i18n('str_system_title', "RC");
		
		//WEBROOT
		(!isset($params['webroot']) || !$params['webroot']) && $params['webroot'] = $options['_webroot'];
		
		//DATADIR
		(!isset($params['datadir']) || !$params['datadir']) && $params['datadir'] = str_replace(DS, '/', RPATH_PUBDATA);
		
		//DATAURL
		(!isset($params['datauri']) || !$params['datauri']) && $params['datauri'] = $options['_webroot'].'/data';
		
		
		//dbinfo
		$db = Factory::GetDBO();
		$dbcfg = $db->db_options();
		$params['dbhost'] = $dbcfg['dbhost'];
		$params['dbport'] = $dbcfg['dbport'];
		$params['dbuser'] = $dbcfg['dbuser'];
		$params['dbtype'] = $dbcfg['dbtype'];
		$params['dbname'] = $dbcfg['dbname'];
		$params['dbcharset'] = $dbcfg['dbcharset'];
		
		$this->assignArray(ifcheck($params['newalias'], "newalias"));

		//loglevel
		/*
		<option value="7">开启</option>
		<option value="6">关闭</option>
		*/
		
		$this->assign('loglevel_select', get_common_select('loglevel', $params['loglevel']));
		
		$this->assign('enable_captcha_select', get_common_select('enable', $params['enable_captcha']));
		$this->assign('savecookie_select', get_common_select('enable', $params['savecookie']));
		$this->assign('ajaxsystime_select', get_common_select('onoff', $params['ajaxsystime']));
		$this->assign('webtimer_select', get_common_select('onoff', $params['webtimer']));
		$this->assign('apiaccess_select', get_common_select('enable', $params['apiaccess']));
		$this->assign('updatetype_select', get_common_select('enable', $params['updatetype']));
		
		$this->assign('proxyapi_select', get_common_select('enable', $params['proxyapi_enable']));
		
		//layout
		$this->assign('layout_select', get_common_select('layout', $params['layout']));
		//language
		$this->assign('language_select', get_common_select('language', $params['langname']));

		//safepwd
		$this->assign('safepwd_select', get_common_select('onoff', $params['safepwd']));
		
		//web
		$webprefix = $options['_weburl'];
		!isset($params['webprefix']) && $params['webprefix'] = $webprefix;
		
		//api
		$apiurl = $options['_weburl'].'/api';
		
		$this->assign('apiurl', $apiurl);
		!isset($params['apiurl']) && $params['apiurl'] = $apiurl;
		!isset($params['updateapi']) && $params['updateapi'] = $apiurl;
		
		//xss_access
		$this->assign('xss_access_select', get_common_select('enable', $params['xss_access']));
		//seccodeonleynum
		$this->assign('seccodeonleynum_select', get_common_select('enable', $params['seccodeonleynum']));
		
		
		
		//local timer
		if (!isset($params['webtimer_request_api'])) {
			$hostname = s_url2hostname($apiurl);
			$params['webtimer_request_api'] = str_replace($hostname, "127.0.0.1", $apiurl);
		}

		$this->assign('login_session_mode_select', get_common_select('login_session_mode', $params['login_session_mode']));
	
		
		//邮件
		$this->assignSelectEnable('api_smtp_enable', $params['api_smtp_enable']);
		//email SSL 
		$this->assign('smtp_auth_type_checked', $params['smtp_auth_type'] == 'ssl'?'checked':'');
		
		//wechat
		$this->assignSelectEnable('api_oauth_wechat_enable', $params['api_oauth_wechat_enable']);
		
		//github
		$this->assignSelectEnable('api_oauth_github_enable', $params['api_oauth_github_enable']);
		//qq
		$this->assignSelectEnable('api_oauth_qq_enable', $params['api_oauth_qq_enable']);

		//db1
		$db1 = get_dbconfig('db1');
		$this->assign("db1_dbtype_select", get_common_select('dbtype', $db1['dbtype']));
		$this->assign('db1', $db1);
		$this->assign('db1_enable', $db1['enable'] == 1?'checked':'');
		$this->assign('db1_dbcharset_select', get_common_select('dbcharset', $db1['dbcharset']));

		//db2
		$db2 = get_dbconfig('db2');
		
		$this->assign("db2_dbtype_select", get_common_select('dbtype', $db2['dbtype']));
		$this->assign('db2', $db2);
		$this->assign('db2_enable', $db2['enable'] == 1?'checked':'');
		$this->assign('db2_dbcharset_select', get_common_select('dbcharset', $db2['dbcharset']));

		//db3
		$db3 = get_dbconfig('db3');
		$this->assign("db3_dbtype_select", get_common_select('dbtype', $db3['dbtype']));
		$this->assign('db3', $db3);
		$this->assign('db3_enable', $db3['enable'] == 1?'checked':'');
		$this->assign('db3_dbcharset_select', get_common_select('dbcharset', $db3['dbcharset']));
		
		//默认起始组件, 注意权限
		//default_component_select
		$app = Factory::GetApp();
		$default_component = isset($params['default_component'])?$params['default_component']:'main';
		$default_component_select = '';
		$menus = $app->getCurrentMenuTree($default_component);
		foreach($menus as $key=>$v) {			
			if ($v['children']) {
				foreach($v['children'] as $k2=>$v2) {
					$selected = $default_component == $k2?'selected':'';
					$default_component_select .= "<option value='$k2' $selected>$v[title] -> $v2[title]</option>";
				}
			}
		}
		$this->assign('default_component_select', $default_component_select);
		
		//rewrite
		$this->assignSelectEnable('rewrite_enable', $params['rewrite_enable']);
		
		//show_optmenu_button_title
		$this->assignSelectEnable('show_optmenu_button_title', $params['show_optmenu_button_title']);
		$this->assignSelectEnable('show_rankid', $params['show_rankid']);
		$this->assignSelectEnable('show_id', $params['show_id']);
		
		$this->assign('params', $params);

		return $params;
	}


	protected function formatForFS($tdir, $name, $baseurl, &$fdb=array(), $format=false)
	{
		$id = $name;
		$ext = s_fileext($id);
		
		$item = array();
		
		$item['id'] = $id;
		$item['name'] = $name;
		$item['ctype'] = 1;
		$item['type'] = 4;
		$item['mimetype'] = CFileType::ext2mimetype($ext);	
		$item['url'] = $baseurl.'/'.$name;
		$item['lpreviewUrl'] = $item['url'];
				
		if ($format) {	
			$item['path'] = $tdir.DS.$name;
			$fdb[$id] = $item;
		} else {	
			$fdb[] = $item;
		}
		
	}
		
	protected function loadDir($type, $options=array(), $format=false)
	{
		$id = 1;
		
		$tdir = RPATH_PUBDATA.DS.$type;
		$baseurl = $options['_dataroot'].'/'.$type;
		
		$fdb = array();
		if (($files  = s_readdir($tdir, "files"))) {			
			foreach ($files as $key => $value) {
				$this->formatForFS($tdir, $value, $baseurl, $fdb, $format);
			}
		}	
		return $fdb;
	}
	
	
	protected function loadLogo($options=array(), $format=false)
	{
		$fdb = $this->loadDir('logo', $options, $format);			
		return $fdb;
	}
	
	protected function loadBG($options=array(), $format=false)
	{
		$fdb = $this->loadDir('bg', $options, $format);			
		return $fdb;
	}
		
	/*	
	  [logo] => Array
	      (
	          [0] => 5
	      )
		
	*/
	
	protected function saveDir($type, $max, $width, $height, &$params, &$options = array())
	{
		
		$fdb = $this->loadDir($type, $options, true);
		$ndb = $params[$type.'s']; //读取数组
		
		
		$ddb = array();
		foreach ($fdb as $key=>$v) {
			if (!$ndb || !in_array($key, $ndb))
				$ddb[$key] = $v;
		}
		
		foreach ($ddb as $key=>$v) {
			unset($fdb[$key]);
			unlink($v['path']);
		}	
		
		$nr = count($fdb);			
		if ($ndb) {
			$newdb = array();
			foreach ($ndb as $key=>$id)  {
				if (!isset($fdb[$id])) {
					$newdb[] = $id;
				}
			}
						
			$tdir = RPATH_PUBDATA.DS.$type;		
			if (!is_dir($tdir))
				s_mkdir($tdir);
				
			$baseurl = $options['_dataroot'].'/'.$type;
					
			$m = Factory::GetModel('file');			
			foreach ($newdb as $key=>$fid) {
				$finfo = $m->get($fid);
				if ($finfo) {
					$id = $nr+1;
					$filename = $id.'_'.$finfo['fileid'];
					$src = $finfo['opath'];
					$dst = $tdir.DS.$filename;
					$res = $m->resizeImage($src, $dst, $width, $height, $resizeinfo, true, true);
					if ($res) {
						$filename .= '.'.$resizeinfo['extname'];						
						$this->formatForFS($tdir, $filename, $baseurl, $fdb, true);	
						$nr ++;
						if ($nr >= $max)
							break;					
					} else {
						rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "call resizeImage failed!", $src);
					}
				}
			}
		}
				
		return $fdb;
	}
	
	protected function saveLogo(&$params, $options=array())
	{
		$fdb = $this->saveDir('logo', 1, 160, 68, $params, $options, $format);		
		//rlog(RC_LOG_ERROR, __FILE__, __LINE__, $fdb);	exit;
		$logo = '';
		foreach ($fdb as $key=>$v) {
			$logo = $v['url'];
			break;
		}	
		$params['logo'] = $logo;
		return true;
	}
	
	protected function saveBG(&$params, $options=array())
	{
		$fdb = $this->saveDir('bg', 3, 1920, 1080, $params, $options, $format);			
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $fdb);
		$params['bg'] = $fdb;
		
		return true;
	}
		
	
	protected function createAliasDir()
	{
		$params = get_config(true);
		
		//检查DATA
		$defaultrootdir = str_replace(DS, '/', RPATH_ROOT);
		if (strpos($params['datadir'], $defaultrootdir) !== false) { 
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no need create web subdir!");
			return false;
		}
		
		//创建alias
		$fname = 'sd_'.md5($params['datauri']);
		$extdir = $params['vardir'].DS.'conf'.DS.'storage';
		$datadir = $params['datadir'];
		if (!is_dir($datadir))
			s_mkdir($datadir);
		
		if (!is_dir($extdir))
			s_mkdir($extdir);
		
		$cfgfile = $extdir.DS.$fname.'.conf';						
		createAliasDir($cfgfile, $params['datauri'], $params['datadir']);
		
		return false;
	}	
	
	protected function setWebTimer($apiurl)
	{
		$cf = get_config(true);
		$params = array();
		
		$params['timeout'] = intval($cf['webtimer']);
		$params['apiurl'] = $apiurl;
		$params['accesskey'] = $cf['accesskey'];
		
		$res = sapi_setwebtimer($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call sapi_setwebtimer failed!", $params);
		}
	}
	
	
	protected function edit(&$options = array())
	{
		$db1 = $this->request('db1');
		$db2 = $this->request('db2');
		$db3 = $this->request('db3');
		
		if (!$db1){
			$db1['enable'] = 0;
			$res1 = set_dbconfig('db1', $db1);
		}
		if (!$db2) {
			$db2['enable'] = 0;
			$res2 = set_dbconfig('db2', $db2);
		}
		if (!$db3){
			$db3['enable'] = 0;
			$res3 = set_dbconfig('db3', $db3);
		}
		

		if ($this->_sbt) {
			if (!($res = $this->getParams($params))) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no params!");
				return false;
			}	
			
			foreach ($params as $key=>&$v) {
				if (is_string($v))
					$v = trim($v);				
			}
			
			//检查登录背景
			$this->saveBG($params, $options);
			//检查logo
			$this->saveLogo($params, $options);
			
			$params['hash'] = md5('rc1@3$_'.$params['cookie']);
			!$params['thename'] && $params['thename'] ='default';
			!$params['tplname'] && $params['tplname'] = 'default';
			
			//appId, appSecret
			$manager = get_manager();
			$m = Factory::GetModel('user');
			$userinfo = $m->getOne(array('name'=>$manager['manager']));
			if ($userinfo) {
				if ($params['apiaccess']) {
					$updatetoken = $this->requestInt('updatetoken', 0);
					$appinfo = $m->createToken($userinfo['id'], $updatetoken);
					$params['apiAccessKey'] = $appinfo['token'];
					$params['apiAccessSecret'] = $appinfo['secret'];
				} else {
					$res = $m->deleteToken($userinfo['id']);
					$params['apiAccessKey'] = '';
					$params['apiAccessSecret'] = '';
				}
			}
			
			//enable_simple_layout
			if (!isset($params['enable_simple_layout'])) {
				$params['enable_simple_layout'] = false;
			}
			
			$mainappcfg = get_mainapp_config();
			if ($mainappcfg) {
				$params['description'] = $mainappcfg['description'];
				$params['copyright'] = $mainappcfg['copyright'];
				$params['website'] = $mainappcfg['website'];
			}
			
			
			set_config($params, false);						
			if (isset($params['loglevel'])) {
				$logcfg = array();
				$logcfg['loglevel'] = $params['loglevel'];
				$l = Factory::GetLog();
				$l->set_logcfg($logcfg);
			}
			
			//local timer
			if (isset($params['webtimer_request_api'])) {
				$apiurl = $params['webtimer_request_api'];				
			} else {
				$apiurl = $options['_weburl'].'/api';	
				$hostname = s_url2hostname($apiurl);
				$apiurl = str_replace($hostname, "127.0.0.1", $apiurl);
			}
			$this->setWebTimer($apiurl);
			
			//rewrite
			set_htaccess($options['_webroot'], $params['rewrite_enable']);
			
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'update system config', $apiurl);	
			
			//检查datadir
			$newalias = intval($_REQUEST['newalias']);
			if ($newalias)
				$this->createAliasDir();

			showStatus(0);
		}
	}

	protected function fileselectorForSelected(&$options=array())
	{
		$mid = $this->requestInt('mid');
		if ($mid == 1) {
			$res = $this->loadBG($options);
		} else {
			$res = $this->loadLogo($options);
		}
		showStatus(0, $res);
	}

	protected function saveDB(&$options=array())
	{
		$name = '';
		$db1 = $this->request('db1');
		$db2 = $this->request('db2');
		$db3 = $this->request('db3');

		if ($db1 && is_array($db1)){
			$db1['enable'] = 1;
			$res1 = set_dbconfig('db1', $db1);
			$name = 'db1';
		}
		if ($db2 && is_array($db2)) {
			$db2['enable'] = 1;
			$res2 = set_dbconfig('db2', $db2);

			$name = 'db2';
		}
		if ($db3 && is_array($db3)){
			$db3['enable'] = 1;
			$res3 = set_dbconfig('db3', $db3);

			$name = 'db3';
		}


		$res = $res1 || $res2 || $res3;

		$db = Factory::GetDBO($name);
		if (!$db || !$db->is_connected()) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "invalid dbconfig '$name'!");
			$res = false;
		}

		showStatus($res?0:-1);
	}
		
	protected function copyForLoginBackground($files, $options=array())
	{
		$tdir = $this->_bgdir;
		if (!is_dir($tdir))
			mkdir($tdir);
		
		foreach ($files as $key=>$v) {
			$targetfile = $tdir.DS.$v['id'].'.'.$v['extname'];
			$res = copy($v['opath'], $targetfile);
		}
	}
	
	protected function uploadloginbackground(&$options=array())
	{
		$options['_fbase'] = $options['_base'].'/uploadloginbackground';
		$options['deletecallback'] = true;
		$options['uploadcallback'] = true;
		
		$m = Factory::GetModel('file');
		$fileinfo = $m->get($this->_id);
		$res = $m->tileupload($options);
		if ($options['issbt']) {
			if ($res && $options['files']) {
				$this->copyForLoginBackground($options['files'], $options);
				CJson::encodedPrint(array('files' => $options['files']));
				exit;
			} else {
				showStatus(-1);
			}
		}
		
		if (isset($options['delete_fid'])) {	
			$targetfile = $this->_bgdir.DS.$options['delete_fid'].'.'.$fileinfo['extname'];
			unlink($targetfile);
			showStatus(0);
		}
	}
	
	protected function copyForLogo($files, $options=array())
	{
		$tdir = $this->_logodir;
		if (!is_dir($tdir))
			mkdir($tdir);
		
		foreach ($files as $key=>$v) {
			$targetfile = $tdir.DS.'adminlogo.'.$v['extname'];
			$res = copy($v['opath'], $targetfile);
			break;
		}
	}
	
	protected function uploadlogo(&$options=array())
	{
		$options['_fbase'] = $options['_base'].'/uploadlogo';
		$options['deletecallback'] = true;
		$options['uploadcallback'] = true;
		
		$m = Factory::GetModel('file');
		$fileinfo = $m->get($this->_id);
		$res = $m->tileupload($options);
		if ($options['issbt']) {
			if ($res && $options['files']) {
				$this->copyForLogo($options['files'], $options);
				CJson::encodedPrint(array('files' => $options['files']));
				exit;
			} else {
				showStatus(-1);
			}
		}
		
		if (isset($options['delete_fid'])) {	
			//$fileinfo = $options['fileinfo'];
			$targetfile = $this->_logodir.DS.'adminlogo.'.$fileinfo['extname'];
			unlink($targetfile);
			showStatus(0);
		}
	}
	
	
	protected function updateDesktopBackground(&$options=array())
	{
		$m = Factory::GetModel('splashclient');
		$res = $m->updateDesktopBackground($this->_bgdir, true);
		
		showStatus($res?0:-1);
	}
	
	
	protected function testemail(&$options=array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN....");
		
		$_params = array();
		$this->getParams($_params);
		
		$params = array();
		
		$params['smtp_auth_type'] = $_params['smtp_auth_type'];
		$params['smtp_server_host'] = $_params['smtp_server_host'];
		$params['smtp_server_port'] = $_params['smtp_server_port'];
		$params['smtp_auth_account'] = $_params['smtp_auth_account'];
		$params['smtp_auth_passwd'] = $_params['smtp_auth_passwd'];
		
		if (!isset($params['smtp_auth_type']))
			$params['smtp_auth_type'] = '';
				
		$params['smtp_target'] = $_params['smtp_auth_account'];
		$params['subject'] = '测试邮件';
		$params['is_html'] = true;
		$params['content'] = "<HTML><BODY><br/>这是SMTP测试电子邮件，请勿回复！ <br/></BODY></HTML>";
		
		$mail = Factory::GetMail();			
		$res = $mail->send($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call mail send failed!", $params);
		}
		
		showStatus($res?0:-1);
	}
	
	
	//测试短信发送
	protected function testsms(&$options=array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN....");
		
		$params = array();
		$this->getParams($params);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $params);
		 	
		$smsparams = array();
		$smsparams['url'] = $params['api_sm_apiurl'];
		$smsparams['appCode'] = $params['api_sm_app_id'];
		$smsparams['signId'] = $params['api_sm_app_sign_id'];
		$smsparams['templateId'] = $params['api_sm_template_id'];
		$smsparams['phone'] = $params['api_sm_test_mobile_no'];
		$smsparams['params'] = '{"code": "7865"}'; //变量
		
		//${code}
		
		//$smsparams['subject'] = '测试短信';
		//$smsparams['content'] = "这是测试短信，请勿回复！";
		
		$sms = Factory::GetSms();			
		$res = $sms->send($smsparams);
		
		showStatus($res?0:-1);
	}	
}