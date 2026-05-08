<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CClientAppModel extends CAppModel
{
	protected $_cacheappfile = null;
	protected $_localAVIDList = array();
	protected $_templates = array();
	
	
	public function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
		$this->_cacheappfile = RPATH_CACHE.DS.$name.'_applist.cache';

	}
	
	public function CClientAppModel($name, $options=null)
	{
		$this->__construct($name, $options);
	}


	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			case 'vtype':
				$f['input_type'] = "selector";	
				break;	
			case 'language':
				$f['input_type'] = 'varmulticheckbox';	
				break;	
			case 'remote':
			case 'local':
			case 'peeravid':
			case 'nextavid':
			case 'path':
			case 'rkey':
			case 'uid':
				$f['show'] = false;	
				break;
			case 'uninstall':
			case 'installed':
			case 'remote':
			case 'local':
				$f['input_type'] = 'selector';	
				$f['selector'] = 'yesno';	
				break;	
			default:
				break;
		}
		return true;
	}

	protected function _initActions()
	{
		parent::_initActions();
		$this->enableAction('del', false);
		$this->enableAction('add', false);
		$this->enableAction('edit', false);
	}
	
	public function formatForView(&$row, &$options = array())
	{
		$res = parent::formatForView($row, $options);
		
		$id = $row['id'];
		$_base = $options['_base'];
		
		
		$current_version = $row['version'];
		
		$hasNewVersion = compareAppVersionId($row['appversion'], $current_version) > 0? true:false;
		
		//extinfo
		$name = $row['name'];
		/*if ($row['local'] != 1) { //不在本地，要下载
			$extinfo .= "<a href='$_base/installFromRemote?id=$id' class='btn btn-sm blue installFromRemote' data-app='$id'>远程安装</a> ";
		} elseif ($row['installed'] != 1) { //安装要
			$extinfo .= "<a href='$_base/install?id=$id' class='btn btn-sm blue install'>安装</a> ";
		} else {
			if ($hasNewVersion) 
				$extinfo .= "<a href='$_base/upgradeFromRemote?id=$id' class='btn btn-sm blue upgradeFromRemote'  data-app='$id'>升级</a> ";
				
			$extinfo .= "<a href='$_base/uninstall?id=$id' class='btn btn-sm red uninstall'>卸载</a> ";
			$extinfo .= " <a href='$_base/uninstallall?id=$id' class='btn btn-sm red uninstall'>完全卸载</a>";
		}
		*/

		if ($row['installed'] > 0) {
			$name .= " (<span class='f10 redf'>已安装</span>) ";
			$row['disabled'] = true;
		}
		
		//$row['url'] = $_base."/detail?id=$id";
		
		//url
		//$row['_extinfo'] = $extinfo;
		$row['_name'] = $name;
		
		
		//name
		//$row['_name'] = $row['name'];
		
		
		if (!empty($row['logo'])) {
			$row['previewUrl'] = $row['logo'];
		} else {
			$vtype = intval($row['vtype']);
			switch($vtype) {
				case VT_EXTRCAPP:
					$imgfile = "rcapp.png";
					break;
				case VT_EXTRCTPL:
					$imgfile = "rctpl.png";
					break;
				case VT_EXTRCTHE:
					$imgfile = "rcthe.png";					
					break;
				default:
					$imgfile = 'app.png';
					break;
			}
			$row['previewUrl'] = $options['_dstroot'].'/img/'.$imgfile;			
		}

		
		
		return $res;
	}

	protected function getActions($row=array(), &$options=array())
	{
		$actions = $this->_default_actions;
		

		//在本地未安装
		if ($row['installed'] != 1 && $row['remote'] == 0 ) {
			$action = array(
			'name'=>'install',
			'icon'=>'fa fa-wrench',
			'title'=>'安装',
			'class'=>'btn-primary',
			'action'=>'button',
			'msg'=>'确定安装吗？',
			'enable'=>true,
			);
			$actions[$action['name']] = $action;
		}
		
		//远程安装
		$vtype = $row['vtype'];
		$isInstall = (
				$vtype == VT_EXTEND
				|| $vtype == VT_UPDATE
				|| $vtype == VT_EXTRCAPP
				|| $vtype == VT_EXTRCTPL
				|| $vtype == VT_EXTRCTHE);
		
		
		if ($row['remote'] == 1 && $row['installed'] != 1  && $isInstall) {
			$action = array(
					'name'=>'installFromRemote',
					'icon'=>'fa fa-wrench',
					'title'=>'远程安装',
					'class'=>'btn-primary',
					'action'=>'button',
					'msg'=>'确定远程安装吗？',
					'enable'=>true,
					);
			$actions[$action['name']] = $action;
		}


		//已安装： 卸载、完全卸载
		if ($row['installed'] == 1 ) {
			$hasNewVersion = compareAppVersionId($row['appversion'], $row['version']) > 0? true:false;
		
			if ($hasNewVersion) {
				$action = array(
				'name'=>'upgradeFromRemote',
						'icon'=>'fa fa-arrow-up',
				'title'=>'升级',
				'class'=>'btn-success',
				'action'=>'button',
				'msg'=>'确定升级吗？',
				'enable'=>true,
				);
				$actions[$action['name']] = $action;
			}
			
			$vtype = $row['vtype'];
			
			$uninstall = $row['uninstall'] == 1 || ($vtype == VT_EXTEND || $vtype == VT_UPDATE);
			
			if ($uninstall) {
				if ($row['remote'] == 0) {
					$action = array(
							'name'=>'uninstall',
							'icon'=>'fa fa-minus-square-o',
							'title'=>'卸载',
							'class'=>'btn-warning',
							'action'=>'button',
							'msg'=>'卸载应用，保留安装文件与数据，确定卸载吗？',
							'enable'=>true,
							);
					$actions[$action['name']] = $action;
				}
				
				
				$action = array(
						'name'=>'uninstallall',
						'icon'=>'fa fa-remove',
						'title'=>'完全卸载',
						'class'=>'btn-danger',
						'action'=>'button',
						'msg'=>'完全卸载将删除运行数据，删除后无法恢复，确定完全卸载吗？',
						'enable'=>true,
						);
				$actions[$action['name']] = $action;
			}
		}
		

		return $actions;
	}
	

	protected function checkRemoteAppVersion(&$info)
	{
		//appversion
		$cf = get_config();			
		$updatetype = intval($cf['updatetype']);
		if ($updatetype == 1) {		
			$apiurl = $cf['updateapi'].'/getAppDetail?appid='.$info['appid'];

			$_params = array('appid'=>$info['appid']);	
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $_params);

			$data = requestSAPI($apiurl, array('params'=>$_params));	
			
			if ($data) {
				$res2 = CJson::decode($data);
				if ($res2) {
					$appinfo = $res2['data'];
					if ($appinfo){
						$info['appversion'] = $appinfo['appversion'];
					}
				} else {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "invalid data '$data'!apiurl=$apiurl", $_params);
				}
			} 
		}
	}

	
		
	public function set(&$params, &$options=array())
	{
		$need_update = true;
		$res = $this->getOne(array('uuid'=>$params['uuid']));
		if ($res) {
			$params['id'] = $res['id'];
			if ($params['remote'] == 0) { //本地更新
				unset($params['description']);
			} 
			
			//检查是否要更新	
			$nr = 0;		
			foreach ($params as $key=>$v) {
				if (isset($res[$key]) && $v != $res[$key]) {
					$nr ++;	
				}
			}
			$need_update = $nr > 0?true:false;
		}
		
		
		$res = $need_update? parent::set($params, $options):true;
			
		return $res;
	}
	
	protected function getLocalAVID($params)
	{
		return md5("0-$params[vtype]-$params[appname]");
	}
	
	protected function setLocalApp($params, $vtype=VT_EXTRCAPP)
	{
		$params['local'] = 1;
		$params['remote'] = 0;
		$params['vtype'] = $vtype;	
		$params['type'] = AT_WEBAPP;
			
		$uuid = $this->getLocalAVID($params);
		$params['uuid'] = $uuid;
		
		//check peeravid
		$remoteappinfo = $this->getOne(array('peeravid'=>$uuid));
		if ($remoteappinfo) {
			$params['installed'] = $remoteappinfo['installed'];
			$params['peeravid'] = $remoteappinfo['uuid'];
		}
		
		
		$res = $this->set($params);
		
		return $res;
	}
	
	protected function setRemoteApp($params)
	{
		$params['remote'] = 1;
		$res = $this->set($params);
		
		return $res;
	}
	
	protected function getRemoteAppCacheInfo($appinfo)
	{
		$appname = $appinfo['appname'];
		$localavid = $this->getLocalAVID($appinfo);
		if ($this->_localAVIDList && isset($this->_localAVIDList[$localavid])) {
			return $this->_localAVIDList[$localavid];
		}
		
		$dir = $this->getCacheAppInstalledDir();
		$cachefile = $dir.DS.$localavid.'.php';
		
		$cacheappinfo = cache_array($appname, null, $cachefile);
		if ($cacheappinfo) {
			$this->_localAVIDList[$localavid] = $cacheappinfo;
			return $cacheappinfo;			
		}	
			
		return false;
	}
	
	protected function checkAppInstalledCache(&$appinfo)
	{
		$cacheappinfo = $this->getRemoteAppCacheInfo($appinfo);
		if (!$cacheappinfo)
			return false;
		
		if ($cacheappinfo['uuid'] != $appinfo['uuid'])
			return false;
		
		$appinfo['installed'] = 1;				
	}
	
	protected function checkLocalWithRemote($appinfo)
	{
		$localavid = $this->getLocalAVID($appinfo);
		
		$localappinfo = $this->getOne(array('uuid'=>$localavid));
		
		if ($localappinfo) {
			$localversion = trim($localappinfo['version']);
			$remote_version = $appinfo['appversion'];
			
			$localappversion = trim($localappinfo['appversion']);
			
			
			$res = compareAppVersionId($remote_version, $localversion);			
			if ($res >= 0 && (empty($localappversion) || $localversion == $localappversion) ) {
				$params = array();
				$params['id'] = $localappinfo['id'];
				$params['appversion'] = $remote_version;
				$params['nextavid'] = $appinfo['uuid'];
				
				$this->update($params);
			}
		}
	}
	
	protected function loadRemoteAppOne($appinfo)
	{
		//查询
		$old = $this->getOne(array('uuid'=>$appinfo['uuid']));
		
		$params = $appinfo;			
		if ($old) {
			$params['id'] = $old['id'];				
		} else {
			$params['id'] = 0;
		}
		
		//检查是否安装
		$this->checkAppInstalledCache($params);	

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);

		$res = $this->setRemoteApp($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "WARNING: setRemoteApp failed!", $params);
			return false;
		}
		
		//检查本地应用
		$this->checkLocalWithRemote($params);
				
		return $res;
	}

	public function loadRemoteApp($type=0)
	{
		//_cacheappfile
		$ts = time();
		$mt = file_exists($this->_cacheappfile)?filemtime($this->_cacheappfile):0;
		if ($mt+60 < $ts) { //缓存3分钟
			$cf = get_config();
			$updatetype = intval($cf['updatetype']);
			if ($updatetype !== 1)
				return false;
				
			$apiurl = $cf['updateapi'].'/getRCAppList';				
			$params = get_sysinfo();		
			$params['type'] = $type;
			$data = requestSAPI($apiurl, array('params'=>$params));		
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'data='.$data);
				
		} else {
			$sec = $ts - $mt;
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "waiting sync after $sec s!");
			return true;
		}
				
		$udb = array();
		if ($data) {
			$res2 = CJson::decode($data);
			if ($res2) {
				$udb = $res2['data'];			
			} else {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "invalid data '$data'!apiurl=$apiurl", $params);
			}
		} 
		if (!$udb) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no udb!", $udb, $data);
			return false;
		}
		
		s_write($this->_cacheappfile, $data);	
		
		//排序
		array_sort_by_field($udb, 'id');
		
		//同步远程APP	
		$res = false;	
		$rdb = array();
		foreach ($udb as $key=>$v) {

			$res = $this->loadRemoteAppOne($v);
			//if (!$res)
			//	rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "setRemoteAppOne failed!", $v);
			
			$rdb[$v['uuid']] = $v;
		}
		
		//检查是否需要删除远程APP缓存的记录，远程APP下架了，本地也就不显示了
		$cdb = $this->gets(array('remote'=>1));
		foreach ($cdb as $key=>$v) {
			$uuid = $v['uuid'];
			if (!isset($rdb[$uuid]) && $v['installed'] != 1) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "sync delete {$v['name']}/$uuid !");
				$this->del($v['id']);
			}
		}
		
		return $res;
	}
	
	public function loadAppFromRemote($params=array(), $options=array())
	{
		$udb = array();
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);

		//从远程加载
		$cf = get_config();			
		$updatetype = intval($cf['updatetype']);
		if ($updatetype == 1) {		
			$apiurl = $cf['updateapi'].'/getRCAppList';				
			
			$_params = array_merge($params, get_sysinfo());	
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $_params);

			$data = requestSAPI($apiurl, array('params'=>$_params));	
			
			if ($data) {
				$res2 = CJson::decode($data);
				if ($res2) {
					$udb = $res2['data'];			
				} else {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "invalid data '$data'!apiurl=$apiurl", $_params);
				}
			} 
		}

		return $udb;
	}
	
	
	protected function loadLocalApp()
	{
		$cf = get_config();
		
		$apps = Factory::GetApps();
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $apps);
		
		//遍历目录
		$dir = RPATH_APPS;
		$udb = s_readdir($dir);
		$hdb = array('.svn');
		
		$tdb = array();
		foreach ($udb as $key=>$v) {
			$name = $v;
			
			if (in_array($name, $hdb))
				continue;
			$app = Factory::GetApp($name);
			if (!$app) 
				continue;
			
			$item = $app->getAppcfg();						
			if (isset($item['embeded']) && $item['embeded'])
				continue;
			
			$item['name'] = $name;
			$item['appname'] = $name;
			if (array_key_exists($name, $apps)) {
				$item['installed'] = 1;
			}
			
			
			
			//version.txt
			$versionfile = $dir.DS.$name.DS.'version.txt';
			if (file_exists($versionfile)) {
				$item['version'] = file_get_contents($versionfile);
			}
			//path
			$item['path'] = str_replace(DS, '/', $dir.DS.$name);
			
			$this->setLocalApp($item);		
		}
		
		return true;
	}
	
	protected function getTpls($key=null)
	{
		if (!$this->_templates) {
			$file = RPATH_CONFIG.DS.'templates.php';
			if (file_exists($file)) {
				require $file;
				if ($templates) {
					$this->_templates = $templates;					
				}
			}
		}		
		return $this->_templates;
	}
	
	
	//loadLocalTpl
	protected function loadLocalTpl()
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN");
			
		$dir = RPATH_TEMPLATES;
		$udb = s_readdir($dir);
		if (!$udb) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no local app!");
			return false;			
		}
		
		$hdb = array('.svn');	
		foreach ($udb as $key=>$v) {
			$name = $v;
			
			if (in_array($name, $hdb))
				continue;
			
			$item = array();	
			$cfgfile = $dir.DS.$name.DS.'config.php';
			if (file_exists($cfgfile)) {
				require $cfgfile;				
				$item = $appcfg;
			} else {
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no config '$cfile'!");
				continue;
			}
			
			$item['name'] = $name;
			$item['appname'] = $name;
			
			//$item['installed'] = (array_key_exists($name, $tpls) && $tpls[$name]['enable'] == true)?1:0;
			//version.txt
			$versionfile = $dir.DS.$name.DS.'version.txt';
			if (file_exists($versionfile)) {
				$item['version'] = file_get_contents($versionfile);
			}
			//path
			$item['path'] = str_replace(DS, '/', $dir.DS.$name);
			
			
			$this->setLocalApp($item, VT_EXTRCTPL);			
		}
		
		return true;
	}
	
	protected function loadLocalThe()
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "TODO...");
		return false;
	}
	
	
	public function loadApp()
	{
		$res1 = $this->loadLocalApp();
		$res2 = $this->loadLocalTpl();
		$res3 = $this->loadLocalThe();
		$res4 = $this->loadRemoteApp();
		
		$res = $res1 || $res2 || $res3 || $res4;
				
		return $res;
	}
		
	
	protected function doInstall($appinfo, &$options=array())
	{
		$name = $appinfo['appname'];
		
		$res = true;
		$apps = Factory::GetApps();
		
		$names = $name;
		if (!is_array($names))
			$names = explode(',', $names);
		
		foreach ($names as $key=>$v) {
			if (!isset($apps[$v]))
				$apps[$v] = false;		
		} 
		
		cache_apps($apps);
		
		$idb = array();
		$install_apps = array();
		$nr_failed = 0;
		
		foreach ($apps as $key=>$v) {
			if (!$v) {
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "key=$key", $apps, $appinfo);
				$app = Factory::GetApp($key);
				if ($app) {
					if (($res = $app->install())) {//表存在，可能报错
						$idb[] = $key;
						$install_apps[$key] = $v;
					} else {
						rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "install app '$key' failed!");
						$nr_failed ++;
						continue;
					}
				}
			} else {
				$install_apps[$key] = $v;
			}
		}
		
		if ($res) {			
			cache_apps($install_apps);
			//重新缓存菜单
			Factory::GetApp()->cache();		
			$iapps = implode(',', $idb);	
			if ($idb) {
				setMsg('str_plugin_install_ok', $iapps);
			} else {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no install '$iapps'!");
				//setErr('str_plugin_install_failed', $iapps);
			}
		}		
		
		return $res;
	}
	
	protected function doInstallTpl($appinfo, &$options=array())
	{
		$m = Factory::GetModel('template');
		
		$res = $m->install($appinfo, $options);
		
		
		return $res;
	}
	
	protected function doInstallUpgrade($appinfo, &$options=array())
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "TODO ...");
		return true;
	}


	protected function doInstallForSetRemote($ainfo)
	{

		//check peeravid
		$rainfo = $this->getOne(array('peeravid'=>$ainfo['uuid']));
		if ($rainfo) {
			$id = $rainfo['id'];
			$params = array();
			$params['id'] = $id;
			$params['installed'] = 1;
			$this->update($params);
		}
	}

	
	protected function installApp($appinfo, &$options=array())
	{
		if (!$appinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no appinfo!");
			return false;
		}
		
		//rkey
		if ($appinfo['rkey'] == 1 && !is_rkey_support()) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no rkey for id '$id'!");
			return false;
		}
		
		//
		$id = $appinfo['id'];
		$type = $appinfo['vtype'];
		switch ($type)
		{
			case VT_EXTRCTHE:
				break;
			case VT_EXTRCTPL:
				$res = $this->doInstallTpl($appinfo, $options);		
				break;
			case VT_UPDATE:
			case VT_EXTEND:
				$res = $this->doInstallUpgrade($appinfo, $options);		
				break;
			default:
				$res = $this->doInstall($appinfo, $options);				
				break;
		}
		//rlog(RC_LOG_DEBUG, __LINE__, __FILE__, __FUNCTION__, '$res='.$res);
		if ($res) {
			//set installed=1
			$_params = array();
			$_params['installed'] = 1;			
			$_params['id'] = $id;			
			$res = $this->update($_params);

			if ($res) {
				$this->doInstallForSetRemote($appinfo);
			}
		}
		
		return $res;
	}
	
	
	public function install($id, &$options=array())
	{
		$appinfo = $this->get($id);
		if (!$appinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no id '$id'!");
			return false;
		}
		
		$res = $this->installApp($appinfo, $options);
		
		return $res;
	}

	public function installAll($ids, &$options=array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__,  __FUNCTION__, $ids);
		if (!is_array($ids)){
			$ids = explode(',', $ids);
		}

		foreach ($ids as $key=>$v) {
			$appinfo = $this->get($v);
			if ($appinfo) {
				if ($appinfo['remote'] == 0) {
					$res = $this->install($v, $options);
				} else {
					$res = $this->installFromRemote($v, $options);	
				}
				
				if (!$res) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "install app failed!", $v);
					break;
				}
			}
			
		}
		return $res;
	}


	private function check_app_depends($name)
	{
		$apps = Factory::GetApps();
		foreach ($apps as $key=>$v) {		
			$deps = isset($v['depends'])?$v['depends']:null;			
			if ($deps) {
				if (!is_array($deps))
					$deps = explode(',',$deps);
				if (in_array($name, $deps))
					return true;
			}
		}
		return false;
	}
	

	protected function doUninstall($appinfo, $dropall = false)
	{
		$name = $appinfo['appname'];
		
		$names = $name;
		if (!is_array($names))
			$names = explode(',', $names);
		
		$apps = Factory::GetApps();
		$pdb = array();			
		$idb = array();
		foreach ($apps as $key=>$v) {
			if (!in_array($key, $names)) {
				$pdb[$key] = $v;
			} else {
				//检查依赖项
				if ($this->check_app_depends($key)) { //依赖存在， 不动
					$pdb[$key] = $v;
				} else {
					$idb[] = $key;
				}
			}
		}
		$apps = $pdb;
		cache_apps($apps);
		
		
		foreach ($names as $key=>$v) {
			if (!file_exists(RPATH_APPS.DS.$v)) {
				$idb[] = $v;
			}
		}		
		
		if ($dropall) { //当前uninstall只清理数据
			foreach ($idb as $key=>$v) {
				$app = Factory::GetApp($v);
				if ($app) {
					$app->uninstall($dropall?1:0);					 
				} 
			}
		}
				
		//重新缓存菜单
		Factory::GetApp()->cache();					
		
		$res = implode(',', $idb);
		setMsg('uninstall app [%s] ok', $res);
		
		return true;
	}
	
	
	protected function doUninstallUpgrade($appinfo)
	{
		$appname = $appinfo['appname'];
		
		//crab 扩展, 如: ffmpeg
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "UNDO ... ", $tinfo);
		$m = Factory::GetUpgrade();
		$res = $m->undoUpgradeCrab($appname);
		
		return $res;
	}
	
	protected function doUninstallForCleanRemoteAppCache($ainfo)
	{
		$rainfo = $this->getOne(array('peeravid'=>$ainfo['uuid']));

		//$rainfo = $this->getRemoteAppCacheInfo($ainfo);
		if ($rainfo) {
			$id = $rainfo['id'];
			$params = array();
			$params['id'] = $id;
			$params['installed'] = 0;
			$this->update($params);
			
			$cachefile = $this->getCacheAppInstalledFile($ainfo);
			if (file_exists($cachefile))
				unlink($cachefile);
		}
	}
	
	protected function doUninstallTpl($ainfo, $dropall=false)
	{
		$m = Factory::GetModel('template');
		$res = $m->uninstall($ainfo, $dropall);
		
		return $res;
	}
		
	public function uninstall($id, $dropall=false)
	{
		$ainfo = $this->get($id);
		if (!$ainfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no id '$id'!");
			return false;
		}
		
		$res = true;
		
		$type = $ainfo['vtype'];
		switch ($type)
		{
			case VT_EXTRCTHE:
				break;
			case VT_EXTRCTPL:
				$res = $this->doUninstallTpl($ainfo, $dropall);		
				break;
			case VT_UPDATE:
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "UNINSTALL UPDATE! Nothing to do!");
				break;
			case VT_EXTEND:
				$res = $this->doUninstallUpgrade($ainfo);		
				break;			
			default:
				$res = $this->doUninstall($ainfo, $dropall);				
				break;
		}	
		
		if ($res) {
			//set installed=0
			$params = array();
			$params['installed'] = 0;
			$params['id'] = $id;			
			$res = $this->update($params);
			
			$this->doUninstallForCleanRemoteAppCache($ainfo);
		}
		
		return $res;
	}
	
	protected function getCacheAppInstalledDir()
	{
		$dir = RPATH_CONFIG.DS."cacheappinstalled";
		if (!is_dir($dir))
			s_mkdir($dir);

		return $dir;
	}
	
	protected function downloadAndUpgradeRemoteApp($vinfo)
	{
		//请求下载地址
		$url = $vinfo['url'];
		
		$dir = $this->getCacheAppInstalledDir();
		$avidfilename = $vinfo['uuid'].'.tgz';	
		$pfile = $dir.DS.$avidfilename;		
				
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $vinfo);
		if (!file_exists($pfile)) {	
			$data = curlGET($url);
			if (!$data) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call curlGET from '$url' failed!", $appinfo);
				return false;
			}
			$res = s_write($pfile, $data);
		}
		
		$up = Factory::GetUpgrade();		
		$res = $up->upgrade($pfile);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call upgrade failed!");
			return false;
		}
		
		return $res;
		
	}


	protected function getAppDownloadVersionInfo($appinfo)
	{
		//请求下载地址
		$cf = get_config();
		$apiurl = $cf['updateapi'].'/getAppDownloadVersionInfo';
		$params = get_sysinfo();		
		$params['uuid'] = $appinfo['uuid'];
		$params['appversion'] = $appinfo['version'];
		$params['rkey'] = is_rkey_support()?1:0;

		$res = requestSAPI($apiurl, array('params'=>$params));		
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call requestSAPI failed!apiurl=$apiurl");
			return false;
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $res);
		
		$res2 = CJson::decode($res);
		if (!isset($res2['data'])) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "invalid requestSAPI result!", $res);
			return false;
		}
		$vinfo = $res2['data'];

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $vinfo);
			
		return $vinfo;
	}

	protected function checkAppDownloadVersion($appinfo, $install=0)
	{
		//请求下载地址
		$cf = get_config();
		$apiurl = $cf['updateapi'].'/checkAppDownloadVersionInfo';
		$params = get_sysinfo();		
		$params['uuid'] = $appinfo['uuid'];
		$params['appversion'] = $appinfo['version'];
		$params['rkey'] = is_rkey_support()?1:0;
		$params['install'] = $install;

		$res = requestSAPI($apiurl, array('params'=>$params));		
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call requestSAPI failed!apiurl=$apiurl");
			return false;
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $res);
		
		$res2 = CJson::decode($res);
		if (!isset($res2['data'])) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "invalid requestSAPI result!", $res);
			return false;
		}
		$vinfo = $res2['data'];

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $vinfo);
			
		return $vinfo;
	}

	/*
	* downloadAppPackageVersion
	
	[order] => 0
    [code] => -6
    [msg] => 此应用还未订购或未完成订购，请登录并完成应用订购再安装！<a href='https://test.relaxcms.com/rc/my_sn/order?euid=ewogICAgImFpZCI6ICIxIiwKICAgICJzaWQiOiAiMiIsCiAgICAidmlkIjogIjEiLAogICAgInVpZCI6ICIxIgp9' target='_blank' class='t3'> 去订购 </a>
    [url] => https://test.relaxcms.com/rc/f/28/relaxcms-0.11.2.54.tar.gz
	*/
	public function downloadAppPackageVersion($appinfo, $dstFile, &$options=array())
	{
		//获取版本与下载URL
		$vinfo = $this->checkAppDownloadVersion($appinfo, 1);
		if ($vinfo['order'] != 1) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no order!", $vinfo, $appinfo);
			$options['data'] = array('status'=>$vinfo['code'], 'msg'=>$vinfo['msg']);
			return false;
		}		
		
		$url = $vinfo['url'];

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $vinfo);
			
		$data = curlGET($url);
		if (!$data) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call curlGET from '$url' failed!", $appinfo);
			return false;
		}

		$res = s_write($dstFile, $data);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call s_write failed!", $dstFile);
			return false;
		}
				
		return 	$vinfo;
	}
	
	protected function getCacheAppInstalledFile($appinfo)
	{
		$dir = $this->getCacheAppInstalledDir();
		
		$uuid = $this->getLocalAVID($appinfo);
		$cachefile = $dir.DS.$uuid.'.php';
		
		return $cachefile;
	}
	

	protected function cacheInstalledAppInfo($appinfo)
	{
		$cachefile = $this->getCacheAppInstalledFile($appinfo);
	
		$oldinfo = cache_array($appinfo['appname'], $appinfo, $cachefile);		
		
		return $oldinfo;
	}
	
	protected function resetOldRemoteAppInfo($oldinfo)
	{
		$uuid = $oldinfo['uuid'];
		$oldappinfo = $this->getOne(array('uuid'=>$uuid));
		if ($oldappinfo) {
			$id = $oldappinfo['id'];
			
			$params = array();
			$params['id'] = $id;
			$params['installed'] = 0;
			$this->update($params);			
		}
	}
	
	public function installFromRemote($id, &$options=array())
	{
		$appinfo = $this->get($id);
		if (!$appinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no id '$id'!");
			return false;
		}
		
		if ($appinfo['remote'] == 0) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "not remote app '$id'!");
			return false;
		}
		
		//版本检查
		

		//获取版本与下载URL（安装）
		$vinfo = $this->checkAppDownloadVersion($appinfo, 1);
		if ($vinfo['order'] != 1) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no order app [%s]!", $appinfo['name']);
			$options['data'] = array('status'=>$vinfo['code'], 'msg'=>$vinfo['msg']);
			//$options['data'] = $vinfo;
			return false;
		}
		
		
		$res = $this->downloadAndUpgradeRemoteApp($vinfo);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "download remote app failed!", $appinfo);
			return false;
		} 
		
		//install
		$res = $this->installApp($appinfo, $options);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "install app failed!", $appinfo);
			return false;
		}
		
		$res = $this->updateRemoteAppVersion($appinfo, $vinfo);		
		if (!$res) { 
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call updateRemoteAppVersion failed!", $appinfo);
			return false;
		}
		
		
		
		//缓存
		$oldappinfo = $this->cacheInstalledAppInfo($appinfo);
		if ($oldappinfo && $oldappinfo['uuid'] != $appinfo['uuid']) { //版本变了
			$this->resetOldRemoteAppInfo($oldappinfo);
		}
		
		return $res;
	}
	
	
	protected function updateRemoteAppVersion($appinfo, $vinfo)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN...", $appinfo);
		
		$localavid = $this->getLocalAVID($appinfo);
		
		//set installed=0
		$params = array();
		$params['id'] = $appinfo['id'];
		$params['peeravid'] = $localavid;		
		$res = $this->update($params);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call update failed!", $params);
			return false;
		}
		
		$localappinfo = $this->getOne(array('uuid'=>$localavid));
		if ($localappinfo) {
			$_params = array();
			$_params['id'] = $localappinfo['id'];
			$_params['version'] = $appinfo['appversion'];
			$_params['peeravid'] = $appinfo['uuid'];
			$this->update($_params);
		}

		return $res;
	}
	
	protected function isLocal($appinfo)
	{
		return $appinfo['remote'] == 0;
	}
	
	public function upgradeFromRemote($id, &$options=array())
	{
		$appinfo = $this->get($id);
		if (!$appinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no id '$id'!");
			return false;
		}
		
		if ($this->isLocal($appinfo)) {
			//upgrade app version
			$uuid = $appinfo['nextavid'];
			$appinfo = $this->getOne(array('uuid'=>$uuid));
			if (!$appinfo) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no remote appversion for uuid '$uuid'!");
				return false;
			}
		}

		$vinfo = $this->checkAppDownloadVersion($appinfo);
		if ($vinfo['order'] != 1) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no order for id '$id'!");
			$options['data'] = array('status'=>$vinfo['code'], 'msg'=>$vinfo['msg']);;
			return false;
		}

				
		$res = $this->downloadAndUpgradeRemoteApp($vinfo);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "download remote app failed", $appinfo);
			return false;
		}
		
		$res = $this->updateRemoteAppVersion($appinfo, $vinfo);		
		if (!$res) { 
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call updateRemoteAppVersion failed!", $appinfo);
			return false;
		}
		
		//缓存
		$oldappinfo = $this->cacheInstalledAppInfo($appinfo);
		if ($oldappinfo && $oldappinfo['uuid'] != $appinfo['uuid']) { //版本变了
			$this->resetOldRemoteAppInfo($oldappinfo);
		}
		
		
		return $res;
	}
	
	
	
	protected function doRemoveApp($appinfo)
	{
		$name = $appinfo['name'];
		
		$dir = RPATH_APPS.DS.$name;
		$res = s_rmdir($dir);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "rmdir '$dir' failed!");
		}
		
		return true;
	}
	
	protected function doRemoveTpl($appinfo)
	{
		$name = $appinfo['name'];
		
		$dir = RPATH_TEMPLATES.DS.$name;
		$res = s_rmdir($dir);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "rmdir '$dir' failed!");
		}
		
		return true;
	}
	
	//remove
	public function remove($id)
	{
		$appinfo = $this->get($id);
		if (!$appinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no id '$id'!");
			return false;
		}
		
		if ($appinfo['embeded']) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "app '$id' is embeded!");
			return false;
		}		
		
		if ($appinfo['installed']) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "app '$id' is installed");
			return false;
		}
		
		//仅本地未远程不能删除物理文件
		$res = false;
		$remote = intval($appinfo['remote']);
		if (!empty($appinfo['nextavid'])) {	
			$vtype = $appinfo['vtype'];
			switch ($vtype) {
				case VT_EXTRCTHE:
					break;
				case VT_EXTRCTPL:
					$res = $this->doRemoveTpl($appinfo);		
					break;
				default:
					$res = $this->doRemoveApp($appinfo);				
					break;
			}
		}
		
		if ($res) {
			$res = $this->del($id);
			
			//clean cache 
			$ts = time() - 57;
			touch($this->_cacheappfile, $ts, $ts);
		}
				
		return $res;
	}

	protected function queryAppRemoteVersionList($id)
	{
		$appinfo = $this->get($id);
		if (!$appinfo) {
			rlog(RC_LOG_DEBUG, __FUNCTION__, "no app '$id'!");
			return false;
		}
		if ($appinfo['remote'] == 0) {
			rlog(RC_LOG_DEBUG, __FUNCTION__, "NOT remote app '$id'!");
			return false;
		}

		$cf = get_config();
		$updatetype = intval($cf['updatetype']);
		if ($updatetype !== 1) {
			rlog(RC_LOG_DEBUG, __FUNCTION__, "Forbbiden APP Update!");
			return false;
		}
			
		$apiurl = $cf['updateapi'].'/getAppVersionList';				
		$params = get_sysinfo();	
		$params['appid'] = $appinfo['appid'];

		$data = requestSAPI($apiurl, array('params'=>$params));		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'data='.$data);
		
		$udb = array();
		if ($data) {
			$res2 = CJson::decode($data);
			if ($res2) {
				$udb = $res2['data'];			
			} else {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "invalid data '$data'!apiurl=$apiurl", $params);
			}
		} 
		if (!$udb) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no udb!", $udb, $data);
			return false;
		}

		return $udb;
	}


	public function getAppVersionList($id)
	{
		$vdb = array();
		$res = $this->queryAppRemoteVersionList($id);
		if ($res) {
			$vdb = $res;
		}

		return $vdb;
	}
	
	
	public function cleancache($id)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN ...");
		$res = $this->truncate();
		
		if (file_exists($this->_cacheappfile))
			@unlink($this->_cacheappfile);
		
		return $res;
	}
	
}