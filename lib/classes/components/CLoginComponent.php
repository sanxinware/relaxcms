<?php
/**
 * @file
 *
 * @brief 
 *  登录基类
 *
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CLoginComponent extends CUIComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CLoginComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function initBG(&$options=array())
	{
		//fixed js and css
		//$this->enableJSCSS(array('datatables', 'jquery_blockui', 'jquery_fileupload', 'datatable'), false);
		//$this->enableJSCSS(array('jquery_backstretch', 'crypto', 'encrypt', 'bootstrap_toastr'), true);
		
		//bg
		if (is_model('splashclient')) {
			$m = Factory::GetModel('splashclient');
			$m->updateDesktopBackground();
		}
		
	}

		
	protected function initLoginToken(&$options=array())
	{
		$token = $this->genRequestToken($options);

		//背景
		$bgdb = loadgb();
		$bgurls = array();
		foreach ($bgdb as $key => &$v) {
			$url = $options['_dataroot'].'/bg/'.$v['name'];
			$v['url'] = $url;
			$bgurls[] = $url;
		}
		
		$token['bgurls'] = $bgurls;
		return $token;

	}

	protected function setDefaultUserName($euid)
	{
		$params = deUID($euid);
		if ($params) {
			$uid = $params['uid'];
			$m = Factory::GetModel('user');
			$accountinfo = $m->getAccountInfo($uid);
			if ($accountinfo) {
				$this->assign('defaultUserName', $accountinfo['name']) ;
				$this->assign('defaultEmail', $accountinfo['email']) ;
				$this->assign('defaultMobile', $accountinfo['mobile']) ;
			}
		}
	}
	
	protected function show(&$options=array())
	{
		if ($this->_sbt) { //提交
			return $this->login($options);
		}
		
		$this->initBG($options);	
		$token = $this->initLoginToken($options);	
		
		$this->assign('_bgdb', $token['bgurls']);
		$cf = get_config();
		$savecookie = $cf['savecookie'];	
		$enable_captcha = $cf['enable_captcha'];	

		
		$this->assign('savecookie', $savecookie);	
		$this->assign('enable_captcha', $enable_captcha);
		
		$backurl = $this->request('backurl');
		!$backurl && $backurl = $options['uri'];
		
		//rlog(RC_LOG_DEBUG, __FUNCTION__, $backurl);
		$this->assign('backurl', $backurl);	

		$this->assign('seccodeimg', $token['seccodeimg']);	

		//登录ID
		if (isset($_REQUEST['euid']))
			$this->setDefaultUserName($_REQUEST['euid']);

		return true;
	}
	
	protected function edit(&$options=array())
	{
		return $this->show($options);
	}
	
	protected function add(&$options=array())
	{
		return $this->show($options);
	}
	protected function detail(&$options=array())
	{
		return $this->show($options);
	}


	protected function gentoken(&$options=array())
	{
		$token = $this->initLoginToken($options);		
		showStatus(0, $token);
	}


	protected function getLoginToken(&$options=array())
	{
		$token = $this->initLoginToken($options);
		showStatus(0, $token);
		return 0;
	}



	protected function checkCaptcha($captcha)
	{
		$cf = get_config();

		if (!isset($cf['enable_captcha']) || !$cf['enable_captcha']) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING disable captcha!");
			return true;
		}

		$captcha = strtolower($captcha);
		if ($captcha != $_SESSION['seccode']){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "Invalid captcha '$captcha'!");
			return false;
		}
		return true;
	}
	

	protected function login(&$options=array())
	{
		if ($this->_sbt) {
			$this->getParams($params);
			if (isset($params['captcha']))
				$captcha = $params['captcha'];
			else 
				$captcha = '';	

			if (!$this->checkCaptcha($captcha)) {
				rlog(RC_LOG_INFO, __FILE__, __LINE__, __FUNCTION__, "invalid captcha!");
				showStatus(RC_E_INVALID_CAPTCHA);
				return false;
			}

			$app = Factory::GetApp();
			$params['login_type'] = 1; //普通WEB
			if (($res = $app->login($params, $options)) === true) {
				//$gourl = $this->_basename;
				//redirect($gourl);
				$backurl = $this->request('backurl');
				!$backurl && $backurl = str_replace('/login', '', $options['_uri']);
				rlog(RC_LOG_INFO, __FILE__, __LINE__, __FUNCTION__, "login OK.");
				
				showStatus(0, array('backurl'=>$backurl));
				return true;
			} else {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "login failed! res=$res");
				showStatus($res);
			}
		} else {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid sbt");
			showStatus(RC_E_INVALID_SBT);
		}
		showStatus(RC_E_FAILED);
		return false;
	}
	
	protected function postLogin(&$options=array())
	{
		return $this->login($options);
	}
	
	protected function logout(&$options=array())
	{
		$app = Factory::GetApp();
		$app->logout();
		redirect($options['_basename']);
	}
		
	protected function forgetPassword(&$options=array())
	{
		$account = $this->request('account');
		
		$m = Factory::GetModel('user');
		$res = $m->forgetPassword($account, $options);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$email='.$email.',$res='.$res);
		showStatus($res?0:-1);
	}
	
	
	protected function register(&$options=array())
	{
		
		if ($this->_sbt) {
			$params = array();
			$this->getParams($params);
			
			//兼容性处理
			if (!$params)
				$params = $_REQUEST;
			if (!isset($params['name']) && isset($params['username']))
				$params['name'] = $params['username'];
			
			$m = Factory::GetModel('user');
			$res = $m->register($params, $options);
			$data = $res?array('autobackurl'=>$options['_webroot']):array();
			showStatus($res?0:-1, $data);
		}
		
		$scf = Factory::GetSiteConfiguration();
		!isset($scf['logo']) && $scf['logo'] = $options['_dstroot'].'/img/logo.png';
		$this->assign('scf', $scf);	
		
		
		$token = $this->genRequestToken($options);
		$this->assign('seccodeimg', $token['seccodeimg']);	
		
	}
	
	protected function postRegister(&$options=array())
	{
		return $this->register($options);
	}
}