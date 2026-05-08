<?php
/**
 * @file
 *
 * @brief 
 *  个人信息
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CMyInfoComponent extends CMyFileDTComponent
{
	protected $_avatardir;
	
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
		$this->_avatardir  = RPATH_PUBDATA.DS."avatar";
	}
	
	function CMyInfoComponent($name, $options)
	{
		$this->__construct($name, $options);
	}

	protected function show(&$options=array())
	{
		$this->enableJSCSS('cropimg');
		
		$userinfo = get_userinfo();
		
	
		$userinfo['last_time'] = tformat($userinfo['last_time']);
		
		/*$uid = $userinfo['id'];
		$avatarfile = $this->_avatardir.DS."avatar$uid.png";
		$userinfo['hasAvatar'] = file_exists($avatarfile)?1:0;*/
		
		//$_base/avatar/128/128
		if (!empty($userinfo['avatar'])) {
			$userinfo['_avatarurl'] = $userinfo['avatar'];
			$userinfo['hasAvatar'] = 1;
		} else {
			$userinfo['_avatarurl'] = $options['_base']."/avatar/128/128";
			$userinfo['hasAvatar'] = 0;
		}
		
		$this->assign("userinfo", $userinfo);
		$this->assign("params", $userinfo);
		$myinfo = array();
		$this->assign('myinfo', $myinfo);	
		
	}
	
	public function edit(&$options=array())
	{
		$userinfo = get_userinfo();		
		$uid = $userinfo['id'];		
		$res = false;
		if ($this->_sbt) {
			$this->getParams($params);
			$m = Factory::GetModel('admin');
			
			$_params = array();
			$_params['id'] = $uid;
			$_params['email'] = $params['email'];
			$_params['nickname'] = $params["nickname"];
											
			$res = $m->update($_params);
		}	
		
		showStatus($res?0:-1);		
	}
	
	protected function avatar(&$options=array())
	{
		$uid = get_uid();		
		$avatarfile = $this->_avatardir.DS."avatar$uid.png";
		
		
		if (is_file($avatarfile))
			$options['imgfile'] = $avatarfile;
		
		$this->showimg($options);
	}
	
	protected function importAvatar($avatarfile, $options)
	{
		//导入文件
		$m = Factory::GetModel('file');
		$fileinfo = $m->importFile($avatarfile);
		if (!$fileinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "importFile failed! avatarfile=$avatarfile");
			return false;
		}
		
		$m->formatForView($fileinfo, $options);
		$avatarurl = $fileinfo['url'];
		
		return $avatarurl;
	}
	
	protected function docropimg(&$options=array())
	{
		$uid = get_uid();
		
		$id = $this->_id;
		if (!is_dir($this->_avatardir))
			s_mkdir($this->_avatardir);
		$avatarfile = $this->_avatardir.DS."avatar$uid.png";
		
		$options['width'] = 128;
		$options['height'] = 128;
		$options['dstimgfile'] = $avatarfile;
		
		$res = $this->__docropimg($options);
		
		if ($res) {
			$avatarurl = $this->importAvatar($avatarfile, $options);
			
			$m2 = Factory::GetModel('user');
			$params = array();
			
			$params['id'] = $uid;
			$params['avatar'] = $avatarurl; //"avatar$uid.png";
			
			$m2->setAvatar($params);
		}
		
		showStatus($res?0:-1);
	}
	
	protected function delcropimg(&$options=array())
	{
		$res = false;
		$userinfo = get_userinfo();
		
		$uid = $userinfo['id'];
		if (!empty($userinfo['avatar'])) {
			$m = Factory::GetModel('file');
			$m->delByUrl($userinfo['avatar']);
		}
		
		$avatarfile = $this->_avatardir.DS."avatar$uid.png";
		if (file_exists($avatarfile)) {
			unlink($avatarfile);	
			
			$m2 = Factory::GetModel('user');
			$params = array();			
			$params['id'] = $uid;
			$params['avatar'] = "";			
			$res = $m2->setAvatar($params);			
		}
		
		showStatus($res?0:-1);
	}


	protected function getUserInfo(&$options=array())
	{	
		$m = Factory::GetModel('my');
		$res = $m->myInfo();
		showStatus($res?0:-1, $res);
	}
}