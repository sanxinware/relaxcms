<?php

/**
 * @file
 *
 * @brief 
 *  基本应用管理类,实现应用安装,卸载
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class AppComponent extends CDTFileComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function AppComponent($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	protected function _initModel()
	{
		$this->_modname = 'local_app';
		$this->_default_viewtype=1;
	}
	
	
	protected function show(&$options=array())
	{
		$this->disableMenuItemAll();

		$this->addMenuItem(
				array(
					'name'=>'cleancache',
					'icon'=>'fa fa-cleanup',
					'title'=>'清理缓存',
					'action'=>'button',
					'sort'=>12,
					'tmask'=>array('show'))
				);
		
		$this->addMenuItem(
				array(
					'name'=>'installall',
					'icon'=>'fa fa-setup',
					'title'=>'安装',
					'action'=>'button',
					'sort'=>11,
					'tmask'=>array('show'))
				);
		
		
		parent::show($options);

		

		$this->resetTpl();

		$m = Factory::GetModel('app');
		$m->loadApp();
		
		//$res = parent::show($options);	
		$this->setActiveTab(2);
		
		return true;
	}
	
	
	protected function detail(&$options=array())
	{
		$this->setActiveTab(2);

		
		$res = parent::detail($options);

		$m = Factory::GetModel('app');
		$vdb = $m->getAppVersionList($this->_id);

		$this->assign('vdb', $vdb);
		$this->setTpl('app_detail');

		return $res;
	}
	
	protected function cleancache(&$options=array())
	{
		$m = Factory::GetModel('app');		
		$res = $m->cleancache($this->_id, $options);		
		showStatus($res?0:-1, ($res)?array('refresh'=>1):array());
	}
	
	/**
	 * 插件安装
	 *
	 * @return mixed 成功true, 失败false
	 *
	 */
	protected function install(&$options=array())
	{
		$m = Factory::GetModel('app');		
		$res = $m->install($this->_id, $options);		
		showStatus($res?0:-1, ($res)?array('refresh'=>1):array());
	}
	
	protected function installall(&$options=array())
	{
		$ids = $this->request('id');
		$m = Factory::GetModel('app');
		$res = $m->installAll($ids, $options);
		
		$data = isset($options['data'])?$options['data']:array();
		
		if (!$res) {
			showStatus(-1, $data, $data?true:false);
		} else {
			$data['refresh'] =1;						
			showStatus($res, $data);
		}
	}
	
	//installFromRemote
	protected function installFromRemote(&$options=array())
	{
		$m = Factory::GetModel('app');		
		$res = $m->installFromRemote($this->_id, $options);	
		$data = array();
		if (isset($options['data'])){
			$data = $options['data'];
		}		
		if (!$res) {
			showStatus(-1, $data, $data?true:false);
		} else {
			$data['refresh'] =1;									
			showStatus($res, $data);
		}
	}
	
	
	protected function upgradeFromRemote(&$options=array())
	{
		$m = Factory::GetModel('app');
		$res = $m->upgradeFromRemote($this->_id, $options);
		
		$data = array();
		if (isset($options['data'])){
			$data = $options['data'];
		}		
		if (!$res) {
			showStatus(-1, $data, $data?true:false);
		} else {
			$data['refresh'] =1;						
			showStatus($res, $data);
		}
	}
	
	/**
	 * 卸载插件
	 *
	 * @return mixed This is the return value description
	 *
	 */
	protected function uninstall(&$options=array())
	{
		$m = Factory::GetModel('app');		
		$res = $m->uninstall($this->_id);	

		showStatus($res?0:-1, $res?array('refresh'=>1):array());
	}

	protected function uninstallall(&$options=array())
	{
		$m = Factory::GetModel('app');		
		$res = $m->uninstall($this->_id, true);	

		showStatus($res?0:-1, $res?array('refresh'=>1):array());
	}
	
	
	protected function remove(&$options=array())
	{
		$m = Factory::GetModel('app');		
		$res = $m->remove($this->_id);		
		showStatus($res?0:-1, $res?array('redirect'=>$options['_base']):array());
	}
	
	protected function del(&$options=array())
	{
		return $this->remove($options);
	}	
}