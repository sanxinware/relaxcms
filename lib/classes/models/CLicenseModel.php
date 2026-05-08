<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CLicenseModel extends CDataModel
{
	protected $_cachefile = null;
	
	public function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
		$this->_cachefile = RPATH_CACHE.DS.$name.'_sn_update.cache';
	}
	
	public function CLicenseModel($name, $options=null)
	{
		$this->__construct($name, $options);
	}


	protected function formatLicense(&$cfg=array())
	{
		//status 
		$status = $cfg['status'];

		$cfg['_status'] = formatColorTitle($status , $cfg['_status']);

		$expired = $cfg['expired'];
		$delta = $cfg['expired'] - time();

		if ($status < 0) {
			$name =  '';
			$color = 0;		
		} else if ($delta == 0) {
			$name =  i18n('Unlimited');
			$color = 1;		
		}  else {
			
			$days = ceil($delta/RC_TIMESEC_DAY);
			if ($days < 0) {
				$name = i18n('Expired');
			} else {
				$name = $days.i18n('Days');	
			}
			
			if ($days > 90) {
				$color = 1;				
			} else if ($days > 30) {
				$color = 2;				
			} else {
				$color = 3;
			}

			$name = tformat_cstdate($expired)." ( ".tformat_expired($expired)." ) ";			
		}

		$cfg['_expired'] = formatColorTitle($color, $name);
		
		//_uid

	}


	public function getLicense($reload=false)
	{
		$m = Factory::GetConfig('license');
		$cfg = $m->load($reload);

		//expired
		if (!$cfg) { //无许可证
			$cfg = array();
			$cfg['status'] = -1;
			$cfg['expired'] = 0;
			
			//status：0待激活，1已激活，2已过期
			$cfg['_status'] = '待激活';

			//默认邮件地址
			$manager = get_manager();
			$cfg['email'] = $manager['manager_email'];
			$cfg['odb'] = array();
		} else {
			$cfg['odb'] = deParamsBase64($cfg['odb'] );
		}

		
		//$this->formatLicense($cfg);
		//rlog($cfg);
		
		return $cfg;
	}


	protected function checkLicense($sninfo)
	{
		$product_id = $sninfo['product_id'];
		$ctime = $sninfo['ctime'];
		
		//check product_id
		$product_id = get_product_id();
		if ($product_id != $product_id) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "invalid product_id '$product_id'!");
			return false;
		}
		
		return true;		
	}
	
	
	
	protected function setLicense($cfg)
	{
		if (!$cfg)
			return false;

		//最后更新时间记录一下
		$ts = time();
		$cfg['ts'] = $ts;
		$res = set_license($cfg);
		
		s_write($this->_cachefile, $ts);	

		return $res;
	}
	
	protected function get_crab_product_id($homedir)
	{
		$crabadmininstalldir = str_replace('/', DS, $homedir.DS.'admin'.DS.'www');		//默认根目录，eg: /opt/crab/admin/www
		
		$crab_product_id = get_product_id($crabadmininstalldir);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $crabadmininstalldir, $crab_product_id);
		
		return $crab_product_id;
	}


	protected function requestLicense($params, &$options=array())
	{
		$cf = get_config();
		
		$url = $cf['updateapi'].'/getLicense';
		
		//add admin crab_product_id
		$params['crab_product_id'] = $this->get_crab_product_id($cf['homedir']);
		//evid
		$evidfile = RPATH_CACHE.DS.'.evid';
		if (file_exists($evidfile)) {
			$params['evid'] = s_read($evidfile);
		}
		
		//product_id
		$res = requestSAPI($url, array('params'=>$params));
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $res, $params);
		
		$data = array();
		if ($res) {
			$res2 = CJson::decode($res);
			if (!$res2) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "WARNING:invalid result", $res, $res2);
			}
			$data = $res2['data'];
			if (!$this->checkLicense($data)) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "check license failed!", $data);
				return false;				
			}
			$this->setLicense($data);
		}
		
		return $data;
	}

	public function activeLicense($account, &$options=array())
	{
		$params = get_sysinfo();
		$params['account'] = $account;
		$params['current_status'] = 1;

		
		$res =  $this->requestLicense($params);

		return $res;
	}


	public function updateLicense($account='', &$options=array())
	{
		$params = get_sysinfo();
		$params['current_status'] = 0;
		if ($account)
			$params['account'] = $account;
		
		
		
		$res =  $this->requestLicense($params);

		return $res;
	}



	public function uploadLicense()
	{
		$fdb = get_upload_tmpfiles();
		if (!$fdb) {
			//rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no upload file!");
			return false;
		}
		
		
		$finfo = array();
		foreach ($fdb as $v){
			$finfo = $v;
			break;
		}
		
		$dst = RPATH_CACHE.DS."license.lic";		
		$tmpfile = $finfo['tmp_name'];
		
		$data = s_read($tmpfile);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $data);
		
		$product_id = get_product_id();
		$key = get_product_key($product_id);

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "key=".$key);
		
		$r = Factory::GetEncrypt();
		$data = $r->mcrypt_des_decode($key, $data);
		
		$data = file_data_fdecrypt($data);
		if (!$data) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call fdecrypt_get_content failed!");
			return false;
		}
		
		$sn = unserialize($data);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $sn);
		
		if (!$this->checkLicense($sn)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "check license failed!");
			return false;
		}
				
		$res = $this->setLicense($sn);
		
		return $res;
		
	}

	/**
	 * 绑定或解绑帐户发验证码
	 */
	public function sendSecurityCode($account, $action)
	{
		//rlog('$account='.$account.', action='.$action);


		$cf = get_config();
		
		$url = $cf['updateapi'].'/sendSecurityCodeForBinding';
		
		$params = get_sysinfo();
		$params['account'] = $account;
		$params['action'] = $action;
			
		//product_id
		$res = requestSAPI($url, array('params'=>$params));
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $res);
		
		$status = false;
		if ($res) {
			$data = CJson::decode($res);
			$status = ($data && intval($data['status']) === 0)?true:false;
		}		
		return $status;
	}

	protected function delCacheFile()
	{
		if (file_exists($this->_cachefile))
			@unlink($this->_cachefile);
	}


	public function bindAccount($_params, &$options=array())
	{
		$cf = get_config();
		
		$url = $cf['updateapi'].'/bindingAccountForLicense';
		
		$params = get_sysinfo();
		$params['account'] = $_params['account'];
		$params['action'] = $_params['action'];
		$params['seccode'] = $_params['seccode'];
			
		//product_id
		$res = requestSAPI($url, array('params'=>$params));
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $res);

		if ($res) {
			$res2 = CJson::decode($res);
			if ($res2 && intval($res2['status']) === 0) {
				$options['data'] = $res2['data'];
				$this->delCacheFile();
			} else {
				$res = false;
			}
		}		
		return $res;
	}

	public function unbindingAccount(&$options=array())
	{
		$cf = get_config();
		
		$url = $cf['updateapi'].'/unbindingAccountForLicense';
		$params = get_sysinfo();
			
		//product_id
		$res = requestSAPI($url, array('params'=>$params));
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $res);

		if ($res) {

			$res2 = CJson::decode($res);
			if ($res2 && intval($res2['status']) === 0) {
				$options['data'] = $res2['data'];

				$this->delCacheFile();
			} else {
				$res = false;
			}
			
		}		
		return $res;
	}



	public function autoUpdateLicense()
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN");

		/*$cf = get_config();
		if (!$cf['updatetype']) { // 更新服务
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "update type closing!");
			//return false;
		}

		$m = Factory::GetConfig('license');
		$cfg = $m->load($reload);

		//expired
		if (!$cfg) { //无许可证
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no license!");
			return false;
		}*/

		$ts = time();
		$last_time = intval(s_read($this->_cachefile));
		if ($ts - $last_time < 30) {
			//$delta = $ts - $last_time ;
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "time limit '$delta'!");
			return false;
		}

		$res = $this->updateLicense();

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT");
		
		return $res;
	}
	
	
	public function checkLicenseExpired($appname, &$msg='')
	{
		$linfo = $this->getLicense();
		if (!$linfo) {
			$msg = '未找到许可证';	
			return false;
		}
		
		$product_id = $linfo['product_id'];
		//check product_id
		$product_id = get_product_id();
		if ($product_id != $product_id) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "invalid product_id '$product_id'!");
			$msg = '不是有效许可证';			
			return false;
		}
		
		//检查过期时间
		$status = $linfo['status'];		
		if ($status <= 0) {
			$msg = '许可证状态无效';	
			return false;	
		} 

		if ($status == 3) {
			$msg = '许可证被撤销，请联系管理员';	
			return false;	
		} 
		
		
		$expired = intval($linfo['expired']);
		if ($expired === 0) {
			return true;	
		}
			
		$delta = $expired - time();
		$days = ceil($delta/RC_TIMESEC_DAY);
		if ($days < 0) {
			$msg = '许可证已过期';	
			return false;
		} 

		if ($days <= 30) {
			$msg = '许证可即将过期，请检查续订许可证';		
		}
		
		//检查app
		foreach ($linfo['odb'] as $key=>$v) {
			if ($v['name'] == $appname) {
				$expired = $v['expired'];
				if ($expired > 0) {
					$delta = $expired - time();
					$days = ceil($delta/RC_TIMESEC_DAY);
					if ($days < 0) {
						$msg = "应用扩展[$appname]许可证已过期";	
						return false;
					} 					
					if ($days <= 30) {
						$msg = "应用扩展[$appname]证可证即将过期，请检查续订许可证";		
					}
				}
			}
		}
					
		return true;
	}
	
}