<?php

/**
 * @file
 *
 * @brief 
 * 
 * Ðí¿ÉÖ¤
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


class HelpLicenseComponent extends CFileDTComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function HelpLicenseComponent($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	public function show(&$options=array())
	{
		$this->setActiveTab(3);
		$this->enableJSCSS(array('fileview'));

		$m = Factory::GetModel('license');
		$m->autoUpdateLicense();
		
		$linfo = $m->getLicense(true);


		if (empty($linfo['email'])) { //默认绑定邮件地址
			$manager = get_manager();
			$linfo['email'] = $manager['manager_email'];
		}

		
		$this->assign('linfo', $linfo);
		$this->assign('sys_product_id', get_product_id());
		$this->assign('sysinfo', get_sysinfo());
		
		$cf = get_config();		
		$url = rtrim($cf['updateapi'], 'api').'register';
		$this->assign('registerUrl', $url);
		
	}
		
	
	protected function upload(&$options=array())
	{
		$m = Factory::GetModel('license');
		$res = $m->uploadLicense();				
		showStatus($res?0:-1);
	}
	
	
	//activeLicense
	protected function activeLicense(&$options=array())
	{
		$account = $this->request('account');

		$m = Factory::GetModel('license');
		$data = $m->activeLicense($account, $options);
		showStatus($data?0:-1, $data);
	}
		
	protected function updateLicense(&$options=array())
	{
		$account = $this->request('account');
		$m = Factory::GetModel('license');
		$data = $m->updateLicense($account, $options);
		showStatus($data?0:-1, $data);		
	}


	protected function sendSecurityCode(&$options=array())
	{
		$account = $this->request('account');	
		$action = $this->requestInt('action');	

		$m = Factory::GetModel('license');
		$res = $m->sendSecurityCode($account, $action);		
		showStatus($res?0:-1);
	}

	//绑帐户与解绑
	protected function bindingAccount(&$options=array())
	{
		$action = $this->requestInt('action');
		$type = $this->requestInt('type');
		
		if ($this->_sbt) {
			$this->getParams($params);
			$m = Factory::GetModel('license');
			$res = $m->bindAccount($params, $options);
			$data = $res?$options['data']:array();
			showStatus($res?0:-1, $data);
		}	

		$params = get_license();
		$cf = get_config();
		$seccodetimeout = isset($cf['seccodetimeout'])?$cf['seccodetimeout']:300;

		$this->assign('params', $params);
		$this->assign('seccodetimeout', $seccodetimeout);
		
		$options['dlg'] = 1;
		$this->setTpl('help_license_binding');		
	}

	protected function unbindingAccount(&$options=array())
	{
		$m = Factory::GetModel('license');
		$res = $m->unbindingAccount($options);
		$data = $res?$options['data']:array();
		showStatus($res?0:-1, $data);	
	}
		
}