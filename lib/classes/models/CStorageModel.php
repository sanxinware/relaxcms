<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

define('ST_DEFAULT',0);
define('ST_LOCAL',	1);
define('ST_SMB',	2);
define('ST_WEBDAV', 3);
define('ST_ISCSI',	4);
define('ST_NODE',	5);


class CStorageModel extends CTableModel
{
	protected $_storages = array();

	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function CStorageModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			case 'master':
				$f['input_type'] = 'yesno';
				break;
			case 'auth':
			case 'status':
			case 'type':
				$f['input_type'] = 'selector';
				break;
			
			case 'sid':
				$f['model'] = 'server';
				$f['default'] = true;
				break;			
				
			case 'oid':
			case 'suid':
			case 'npath':
			case 'nmountdir':
				$f['edit'] = false;
			case 'username':
			case 'vod_prefix':
			case 'sync_prefix':
			case 'lan_vod_prefix':
			case 'lan_sync_prefix':
			case 'lanvodrooturl':
			case 'download_prefix':
			case 'lan_download_prefix':
				$f['show'] = false;				
				break;
			case 'password':
				$f['input_type'] = "password";
				$f['show'] = false;
				break;
			case 'total':
			case 'used':
				$f['input_type'] = "SIZE";			
				$f['edit'] = false;
				break;
			case 'ctime':
				$f['readonly'] =  true;
				$f['show'] = false;
			case 'ts':
				$f['input_type'] = 'TIMESTAMP';				
				break;
			default:
				break;
		}
		
		return true;
	}
	
	public function formatForView(&$row, &$options=array())
	{
		parent::formatForView($row, $options);
		
		//status
		$row['_status'] = $this->formatLabelColorForView($row['status'], $row['_status']);
		$row['_master'] = $this->formatLabelColorForView($row['master'], $row['_master']);
		//_path
		$row['_path'] = "<a href='".$row['path']."' target=_blank >".$row['path']."</a>";
		
		
	}
	
	protected function newID(&$params=array())
	{
		$id = parent::newID($params);		
		if (!isset($params['suid'])) {
			$params['suid'] = md5($id.$params['name']);
		}
		
		return $id;		
	}
	
	protected function initLocalStorage()
	{
		$cf = get_config();
		
		$params = Factory::GetParams();
		$webroot = $params['_webroot'];
		
		$name = 'default';
		$datadir = (isset($cf['datadir']) && is_dir($cf['datadir']))?$cf['datadir']:RPATH_DATA;
		$path = '/'.$name;
		
		//默认本地
		$mountdir = $datadir;
		if (!is_dir($mountdir))
			mkdir($mountdir);

		$mountdir = str_replace(DS, '/', $mountdir);
		
		$download_prefix = $path;
		$vod_prefix = $path;
		$lan_download_prefix = $path;
		$lan_vod_prefix = $path;
		
		$suid = md5('1'.'-'.$path);				
		$storageinfo = array(
				'id' => 1,
				'name' => $name,
				'oid' => 0,
				'sid' => 0,
				'type'=> 0,
				'status'=> 1,
				'mountdir'=> $mountdir,
				'path'=> $path,
				'download_prefix'=> $download_prefix,
				'vod_prefix'=> $vod_prefix,
				'lan_download_prefix'=> $lan_download_prefix,
				'lan_vod_prefix'=> $lan_vod_prefix,
				'suid'=> $suid,
				);
				
		$storageinfo['total'] = disk_total_space($mountdir);
		$storageinfo['free'] = disk_free_space($mountdir);
		$storageinfo['used'] = $storageinfo['total'] - $storageinfo['free'];

		$res = $this->add($storageinfo);

		return $storageinfo;
	}
	
	
	protected function getServerInfo(&$params)
	{
		//$sid
		$sid = $params['sid'];
		
		$m = Factory::GetModel('server');
		$serverinfo = $m->get($sid);

		if ($serverinfo) {
			$path = !empty($params['npath'])?$params['npath']:$params['path'];
			
			$params['web_prefix'] = $serverinfo['web_prefix'];
			
			$params['vodrooturl'] = $serverinfo['vod_prefix'].$path;
			$params['downloadrooturl'] = $serverinfo['download_prefix'].$path; 
			$params['lanvodrooturl'] = $serverinfo['lan_vod_prefix'].$path;
			$params['landownloadrooturl'] = $serverinfo['lan_download_prefix'].$path; //用于下载
			
		} else {
			$params['vodrooturl'] = $params['vod_prefix'].$params['path'];
			$params['downloadrooturl'] = $params['download_prefix'].$params['path']; 
			$params['lanvodrooturl'] = $params['lan_vod_prefix'].$params['path'];
			$params['landownloadrooturl'] = $params['lan_download_prefix'].$params['path']; //用于下载
		}
	}
	
	public function get($id)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN ", $id);
		
		if (!$id)
			$id = 1;
		$res = parent::get($id) ;
		if (!$res) {
			$this->initLocalStorage();
			$res = parent::get($id) ;
		}
		
		if ($res) {
			$this->getServerInfo($res);	
		}	
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT...");
		
		return $res;
	}	

	protected function checkLocalStorage()
	{
		$res = parent::get(1) ;
		if (!$res) {
			$this->initLocalStorage();
		}
	} 
	
	public function getStorageDir($id)
	{
		$storageinfo = $this->get($id);
		if (!$storageinfo)
			return false;

		return $storageinfo['mountdir'];
	}
	
	public function getStorageTotalSpace($id)
	{
		$storageinfo = $this->get($id);
		if (!$storageinfo)
			return false;

		return $storageinfo['total'];
	}
		
	public function getStorageFreeSpace($id)
	{
		$storageinfo = $this->get($id);
		if (!$storageinfo)
			return false;

		return $storageinfo['free'];
	}
	
	
	public function getStorageInfo($sid, $uid=0)
	{
		//查询用户所在单位分配置的空间
		$dispatch_total = 0;
		$used_total = 0;
		$max_freespace = 0;
		$max_freespace_sid = 1;
		$max_freespace_oid = 0;
		$no_any_org = false;
		
		if ($uid > 0) {
			$sql = "select * from cms_user2org where uid=$uid";
			$res = $this->_db->get_one($sql);
			if ($res) {
				$oid = $res['oid'];				
				$sql = "select * from cms_storage_dispatch where oid=$oid";
				$udb = $this->_db->select($sql);
				//rlog($udb);
				
				//可用空间最大的存储为默认存储
				foreach ($udb as $v) { //空闲空间最大的
					$dispatch_total += $v['dispatch'];
					$used_total += $v['used'];					
					$v['free'] = $v['dispatch'] - $v['used'];		
					if ($max_freespace < $v['free']  ) {
						$max_freespace = $v['free'] ;
						$max_freespace_sid = $v['sid'];
						$oid = $v['oid'];
					}
				}
			} else { //未加入组织，未限制空间，使用默认本地空间
				$no_any_org = true;
				$max_freespace_sid = 1;
			}
		}
		if ($sid == 0)
			$sid = $max_freespace_sid;
				
		$storageinfo = $this->get($sid);
		if (!$storageinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no storage '$sid'");
			return false;
		}
		
		
		$basepath = $uid.'/'.tformat(0, 'Ym');
		$basedir = $storageinfo['mountdir'].DS.$basepath; //eg:1/202007
		if (!is_dir($basedir))
			s_mkdir($basedir);		
		
		$storageinfo['basedir'] = $basedir;
		$storageinfo['basepath'] = $basepath;
		
		if ($uid > 0 && !$no_any_org ) {
			
			$storageinfo['dispatch'] = $dispatch_total;
			$storageinfo['used'] = $used_total;
			$storageinfo['free'] = $max_freespace;
			$storageinfo['oid'] = $oid;
			
		} else { //默认
				
			$storageinfo['dispatch'] = $storageinfo['total'];				
			$storageinfo['free'] = $storageinfo['total'] - $storageinfo['used'];	
			$storageinfo['oid'] = 0;			
		}
		
		return $storageinfo;
	}
	
	
	/**
	 * 更新自动挂载脚本或批处理
	 * 
	 * 注：WINDOWS批处理暂不支持（测试环境）
	 *
	 * @param mixed $params This is a description
	 * @return mixed This is the return value description
	 *
	 */

	protected function updateStorageAutomount()
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		$udb = $this->select(array('status'=>1));
		if (!$udb) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no any storage!");
			return false;		
		}
		$cf = get_config();	
		
		$homevardir = $cf['homedir'].DS.'var';
		$storagecfgdir = $homevardir.DS.'conf'.DS.'storage';
		if (!is_dir($storagecfgdir))
			mkdir($storagecfgdir);
			
		$automount = $storagecfgdir.DS."automount.sh";
		$data = "#!/bin/sh\n";
		$data .= "LOGFILE=/dev/null\n";
		$data .= "\n";
		
		foreach ($udb as $key=>$v) {
			$type = $v['type'];
			$mountdir = $v['mountdir'];
			$path = $v['path']; //eg : //192.168.10.238/a
			$username = $v['username'];
			$password = $v['password'];
			
			switch($type) {
				case ST_SMB:   //SMB
					$data .= "mount -t cifs -o username=$username,password=$password $path $mountdir\n";
					break;
				case ST_WEBDAV:  //WEBDAV
					$data .= "mount -t davfs -o rw,uid=crab,gid=root $path $mountdir << EOF >> \$LOGFILE 2>&1\n$username\n$password\nEOF\n";
					break;
				default:  
					break;
			}
		}		
		$data .= "\n";
		$res = s_write($automount, $data);
			
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		return $res > 0;
	}
	
	protected function getDefaultStorageConfigDir()
	{
		$cf = get_config();
		$sscfgdir = $cf['homedir'].DS.'var'.DS.'conf'.DS.'storage';
		
		//默认VHOST配置
		$vhostcfgdir = RPATH_CONFIG.DS.'conf';	
		if (is_dir($vhostcfgdir)) {
			$sscfgdir = $vhostcfgdir;
		}	
			
		return $sscfgdir;
	}
	
	protected function setLocationForPath($sinfo, $options=array(), $restart=true)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...");
		
		$id = $sinfo['id'];		
		$mountdir = $sinfo['mountdir'];
		$path = $sinfo['path'];
		
		$prefix = substr($mountdir, 0, strlen(RPATH_PUBLIC));
		$prefix = str_replace(DS, '/', $prefix);
		$pubdir = str_replace(DS, '/', RPATH_PUBLIC);
		
		/*if ($prefix == $pubdir) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "path '$path' is subdir of '$pubdir'!");
			return false;
		}*/
		
		if (!is_dir($mountdir)) 
			s_mkdir($mountdir);
		
		//创建location or alias 
		/*
		LoadModule h264_streaming_module modules/mod_mp4.so 
		
		Alias /avod /opt/crab/var/webdav/a
		*/
		$name = substr($path, 1); 
		if (!is_name($name)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "invalid webpath '$path'!");
			return false;
		}
		$data = '';
		
		//vod with mod_h264_streaming 
		if (!is_windows()) {
			$vodpath = $path.'vod';	//download location		
			$data = "<IfModule !mod_h264_streaming>\n";
			$data .= "  LoadModule h264_streaming_module modules/mod_h264_streaming.so\n";
			$data .= "</IfModule>\n";
			
			$data .= "Alias $vodpath $mountdir\n";
			$data .= "\n\n";
			$data .= "<Location $vodpath>\n";
			$data .= "AddHandler h264-streaming.extensions .mp4\n";
			$data .= "</Location>\n";
			$data .= "\n\n";
		}
		
		$data .= "Alias $path $mountdir\n";
		$data .= "\n\n";
		
		
				
		
		$data .= "<Directory $mountdir>\n";
		$data .= "Options +Indexes \n";
		$data .= "IndexOptions FancyIndexing\n";
		$data .= "AddDefaultCharset UTF-8\n";
		$data .= "Order allow,deny\n";
		$data .= "Allow from all\n";
		$data .= "\n\n";	
		
		//auth
		$auth = intval($sinfo['auth']);		
		$username = $sinfo['username'];
		if ($auth) {
			$cf = get_config();
			$apiurl = !empty($cf['apiurl'])?$cf['apiurl']:$options['_weburl'].'/api';
			$authurl = $apiurl.'/authBasicRequestForStorage';
			
			//url
			$data .= "<IfModule !mod_auth_url>
					LoadModule url_auth_module modules/mod_auth_url.so
					</IfModule>\n";
						
			$data .= "AuthType Basic\n";
			$data .= "Require valid-user\n";
			$data .= "AuthName \"Storage $name \"\n";
			//AuthBasicProvider file
			$data .= "AuthBasicProvider url\n";
			$data .= "AuthUrlEnable On\n";
			$data .= "AuthUrl $authurl\n";
			$data .= "AuthBasicAuthoritative off\n";
			//$data .= "Require user $username \n";
		}
		
		$data .= "</Directory>\n";
		$data .= "\n";
		
		
		
		
		//cfg
		$sscfgdir = $this->getDefaultStorageConfigDir();
		if (!is_dir($sscfgdir))
			s_mkdir($sscfgdir);
					
		//check old
		if (isset($params['__old'])) {
			@unlink($sscfgdir.DS.'sd'.$params['__old']['id'].'.conf');			
		}
		
		$fname = 'sd'.$id;						
		$cfgfile = $sscfgdir.DS.$fname.'.conf';
		@s_write($cfgfile, $data);
		
		//重启 server
		if ($restart)
			sapi_restart_apache();
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT ...", $cfgfile);
		return true;
	}
		
	public function cacheStorage($options=array())
	{
		$udb = $this->select(array('status'=>1));
		if (!$udb) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no any storage!");
			return false;		
		}
		
		$nr = count($udb);
		$idx = 0;
		foreach ($udb as $key=>$v) {
			$res = $this->setLocationForPath($v, $options, $idx++ == $nr-1);
		}		
		return $res;				
	}
	
	
	
	protected function setStorageExtInfo($params, &$options=array())
	{
		$res = $this->setLocationForPath($params, $options);
		
		if (!isset($options['noupdate']))
			$res = $this->updateStorageAutomount();
		
		//type
		$_params = array();
		if ($params['type'] <= ST_LOCAL) { // local		
			$total = disk_total_space($params['mountdir']);
			$free = disk_free_space($params['mountdir']);
			$_params['total'] = $total;
			$_params['used'] = $total - $free;
		}
		
		//oid
		if (isset($params['sid'])) {
			$sid = $params['sid'];
			$m = Factory::GetModel('server');
			$serverinfo = $m->get($sid);
			if ($serverinfo) {
				$_params['oid'] = $serverinfo['oid'];
			}
		}
				
		if ($_params) {
			$_params['id'] = $params['id'];
			$res2 = $this->update($_params);
			if (!$res2) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "WARNING: call update failed!", $_params, $_params);
			}
		}
		
		return $res;
	}
	
	public function updateAutoMountConfig()
	{
		return $this->updateStorageAutomount();
	}
		
	protected function checkParams(&$params, &$options=array())
	{
		$res = parent::checkParams($params, $options);
		if (!$res)
			return $res;
		
		//本地目录状态总是'正常'
		if ($params['stype'] == 1)
			$params['status'] = 1;
		
		//检查WEB
		$params['mountdir'] = trim($params['mountdir']);
		
		//检查
		$path = trim($params['path']);
		if (!$path) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no path !");
			return false;
		}
		
		if (!is_start_slash($path))
			$path ='/'.$path;
		
		if (!is_uripath($path)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid path '$path'!");
			return false;
		}
		
		$params['path'] = $path;
		
		if (isset($params['password'])) {
			$password = trim($params['password']);
			if (empty($password)) {
				unset($params['password']);
			} else {
				$params['password'] = encryptPassword($password);
			}
		}
		
		//检查PASSWORD
		/*$password = trim($params['password']);
		if (!$password) {//
			$res = parent::get($params['id']);
			if ($res) {
				$params['password'] = $res['password'];
			}
		}*/
		
		
		
		return true;
	}
	
	public function set(&$params, &$options=array())
	{
		$res = parent::set($params, $options);
		if ($res) {
			$this->setStorageExtInfo($params, $options);
		}
		
		return $res;
	}
	
	public function del($id, &$options=array())
	{
		$oldinfo = $this->get($id);
		if (!$oldinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no storage id '$id'");
			return false;
		}
		
		//查询一下可有文件在用
		$m = Factory::GetModel('file');
		$exists = $m->getOne(array('sid'=>$id));
		if ($exists) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "storage '$id' is busy!");
			return false;
		}
						
		$res = parent::del($id, $options);
		if ($res) {
			$this->updateStorageAutomount();
		}
		
		return $res;
	}
	
	
	protected function checkLocalStorageSpace($storageinfo)
	{
		$deafult_files_data_dir = $storageinfo['mountdir'];
		$total = disk_total_space($deafult_files_data_dir);
		$free = disk_free_space($deafult_files_data_dir);
		$used = $total - $free;
		
		$id = $storageinfo['id'];
		
		$sql = "update cms_storage set used=$used where id=$id";
		$res = $this->_db->exec($sql);		
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "update local storage used failed! sql=$sql");
		}
	}
	
	
	public function checkStorageStatusSingle($params)
	{
		if ($params['type'] == 1) {//本地不检
			//check space
			//$this->checkLocalStorageSpace($params);
			return false;
		}
		
		$url = $params['spath'].'/index.html';
		$res = http_request($url, $httpCode);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "check $url, httpCode=$httpCode, res=".$res);
		
		if ($httpCode == 401 
			//|| $httpCode == 404 
			|| $httpCode == 200) {
				
				//更新为: '正常'
			$sql = 'update cms_storage set status=1 where id='.$params['id'];
		} else {
			$sql = 'update cms_storage set status=2 where id='.$params['id'];
		}
		
		$this->_db->exec($sql);
	}
	
	
	public function checkStorageStatus()
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN");
		$udb = $this->select();
		foreach ($udb as $key=>$v) {
			//$this->checkStorageStatusSingle($v);
		}
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT");
	}
	
	
	protected function getUserStorageDispathInfo($oid, $uid)
	{
		//状态正常，并助oid相同
		$filter = array('status'=>1, 'oid'=>$oid);
		$sdb = $this->gets($filter);		
		if (!$sdb) {
			if ($oid == 0) {
				$this->initLocalStorage();	
				$sdb = $this->gets($filter);
				if (!$sdb) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no storage!", $filter);
					return false;
				}		
			} else {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no storage!", $filter);
				return false;
			}
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__,  __FUNCTION__, $sdb);
		
		//可用空间最大的存储为默认存储
		//查询用户所在单位分配置的空间
		$dispatch_total = 0;
		$used_total = 0;
		$max_freespace = 0;
		$max_freespace_sid = 1;
		
		//主控优选
		$master_sinfo = array();
		$slave_sinfo = array();
		
		foreach ($sdb as $v) { //空闲空间最大的
			
			//节点
			$sid = $v['sid']; 
			if ($sid > 0) {
				$m = Factory::GetModel('server');
				$serverinfo = $m->get($sid);
				if ($serverinfo && $serverinfo['status'] != 1) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "skip NOT enable server '$sid'!");
					continue;
				}
			}
			
			$free = $v['total'] - $v['used'];		
			if ($max_freespace < $free  ) {
				$max_freespace = $free ;
				
				$sinfo = $v;
			}
			
			if ($v['master'] == 1) {
				$master_sinfo = $v;
			} 
			
		}
		
		if ($master_sinfo) {
			$sinfo = $master_sinfo;
		}
		
		//查一下配额
		if (is_model('storage_dispatch')){
			$m2 = Factory::GetModel('storage_dispatch');
			$dispatchinfo = $m2->getOne(array('uid'=>$uid));		
			if ($dispatchinfo) {
				if ($dispatchinfo['total'] > 0 && $dispatchinfo['total'] < $sinfo['total']) {
					$sinfo['total'] < $dispatchinfo['total'];
					$sinfo['used'] < $dispatchinfo['used'];
				}
			}
		}
				
		return $sinfo;
	}
	
	public function getUserStorageInfo($uid)
	{
		$m = Factory::GetModel('user');
		$userinfo = $m->get($uid);
		if (!$userinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no id '$uid'!");
			return false;
		}
		
		
		//oid
		$oid = $userinfo['oid'];		
		$storageinfo = $this->getUserStorageDispathInfo($oid, $uid);
		if (!$storageinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no storage for '$uid'!oid=$oid");
			return false;
		}
		
		//附加		
		$storageinfo['uid'] = $uid;
		$storageinfo['free'] = $storageinfo['total'] - $storageinfo['used'];
		
		$basepath = $uid.'/'.tformat(0, 'Ym');
		$basedir = $storageinfo['mountdir'].DS.$basepath; //eg:1/202007
		if (!is_dir($basedir))
			s_mkdir($basedir);		
		
		$storageinfo['basedir'] = $basedir;
		$storageinfo['basepath'] = $basepath;
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $storageinfo);
		
		return $storageinfo;
	}
	
	
	public function notifyLocalStorage($action, $params, $options)
	{
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, $action, $params);
			
		
		$suid = $params['suid'];
		$name = $params['name'];
		$path = $params['path'];
		$mountdir = $params['mountdir'];
		if (!$suid) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no suid '$suid'!");
			return false;
		}
		if (!$mountdir)
			return false;
		if (!is_dir($mountdir))
			return false;
		
		$total = disk_total_space($mountdir);
		$free = disk_free_space($mountdir);
		$params['total'] = $total;
		$params['used'] = $total - $free;
		
		$type = ST_LOCAL;
		$sinfo = $this->getOne(array('suid'=>$suid));
		if (!$sinfo) {
			if ($action == 2) { //删除
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no storage!", $params);
				return true;				
			}
			
			//检查
			$this->checkLocalStorage();
			
			//创建
			$params['type'] = $type;
			$res =  $this->set($params, $options);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call set failed!", $params);
				return true;				
			}
		} else {
			if ($action == 2) { //删除
				$res =  $this->del($sinfo['id']);	
			} else { //更新
				$params['id'] = $sinfo['id'];
				$params['type'] = $type;
				
				$res = $this->set($params, $options);				
				if (!$res) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call set failed!", $params);
					return true;				
				}
			}
		}
		
		return $res;
	}
	
	public function updateUsedBy($id, $uid, $delta)
	{
		
		$m = Factory::GetModel('storage_dispatch'); 
		$sd = $m->getOne(array('uid'=>$uid));
		
		$params = array();
		if ($sd) {
			$params['id'] = $sd['id'];
			$used = $sd['used'];
		} else {
			$used = 0;
		}
		
		$used += $delta;
		
		$params['used'] = $used;
		$params['uid'] = $uid;
				
		$m->set($params);
				
	}
	
	
	public function authBasicRequestForStorage($params) 
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN ...", $params);
		
		//check sign
		$uri = ltrim($params['uri'], '/');
		$udb = explode('/', $uri);
		$name = $udb[0];
		
		$storageinfo = $this->getOne(array('name'=>$name));
		if (!$storageinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no name '$name'!");
			return false;
		}
		
		
		//check user
		$username = $params['user'];
		if ($username != $storageinfo['username']) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "username '$username' error!");
			return false;
		}
		
		//check password
		$pwd = str_replace(' ', '+', $params['pwd']);
		$key = get_accesskey();
		$pwd2 = $this->decryptPassword($pwd, $key);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN2 ...", $key, $pwd, $pwd2);
		
		$epwd2 = encryptPassword($pwd2);
		if ($storageinfo['password'] !== $epwd2) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "password error!");
			return false;
		}
		
		return true;
	}
	
	
	public function updateStorageSpaceBySID($sid, $total, $used)
	{
		if (!$total) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: invalid total '$total'!");
			return false;
		}	
		
		
		$storageinfo = $this->getBy("where sid=$sid");
		if (!$storageinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no sid '$sid'!");
			return false;
		}
		
		$id = $storageinfo['id'];
		$_params = array();
		$_params['id'] = $id;
		$_params['total'] = $total;
		$_params['used'] = $used;
		
		$res = $this->update($_params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "WARNING: call update failed!", $_params);
		}
		
		return $res;
	}
	
	public function getStorageInfoByOID($oid, $options=array())
	{
		$sinfo = array();
		
		$udb = $this->gets(array('oid'=>$oid));
		foreach ($udb as $v) {
			if ($v['status'] != 1) //非启用
				continue;				
			if (!$sinfo) //第1条
				$sinfo = $v;					
			if ($v['master'] == 1) {
				$sinfo = $v;
				break;
			}
		}
		
		//if ($sinfo) {
		//	$this->formatForView($sinfo, $options);
		//}
		
		$this->getServerInfo($sinfo);
		
		return $sinfo;
	}
	
	
	public function getStorageListByOID($oid, &$defaultStorageInfo=array(), $options=array())
	{
		$sinfo = array();
		
		$udb = $this->gets(array('oid'=>$oid));
		foreach ($udb as $v) {
			if ($v['status'] != 1) //非启用
				continue;				
			if (!$sinfo) //第1条
				$sinfo = $v;					
			if ($v['master'] == 1) {
				$sinfo = $v;
				break;
			}
		}
		
		$defaultStorageInfo = $sinfo;
		
		return $udb;
	}
}

