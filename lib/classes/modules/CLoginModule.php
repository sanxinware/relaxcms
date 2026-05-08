<?php
/**
 * @file
 *
 * @brief 
 * 登录模块
 *
 */
class CLoginModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function CLoginModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
		
	protected function show(&$options=array())
	{	
		//配置	
		$m = Factory::GetModel('home_config');
		$homecfg = $m->getParams();
		
		$this->assign('pkey', isset($this->_attribs['__aeskey'])?$this->_attribs['__aeskey']:'');
		
		$thirdAccountLoginOptions = array(); 
		if (is_model('op_platform')) {
			$m = Factory::GetModel('op_platform');		
			$thirdAccountLoginOptions = $m->getOAuthPlatformList($options);
		}
		$this->assign('thirdAccountLoginOptions', $thirdAccountLoginOptions);
		
		//验证码超时
		$cf = get_config();
		$seccodetimeout = isset($cf['seccodetimeout'])?$cf['seccodetimeout']:5;
		$this->assign('seccodetimeout', $seccodetimeout);
		
		//登录方式
		$login_by_name = isset($homecfg['login_by_name']) ?$homecfg['login_by_name']:1;
		$login_by_mobile = isset($homecfg['login_by_mobile']) ?$homecfg['login_by_mobile']:0;
		$login_by_email = isset($homecfg['login_by_email']) ?$homecfg['login_by_email']:1;
		$login_by_qrcode = isset($homecfg['login_by_qrcode']) ?$homecfg['login_by_qrcode']:0;
		$login_by_qq = isset($homecfg['login_by_qq']) ?$homecfg['login_by_qq']:0;
		
		
		//注册方式
		$reg_by_name = isset($homecfg['reg_by_name'])? $homecfg['reg_by_name']:1;
		$reg_by_mobile = isset($homecfg['reg_by_mobile'])?$homecfg['reg_by_mobile']:0;
		$reg_by_email = isset($homecfg['reg_by_email'])?$homecfg['reg_by_email']:1;
		
		//是否开通注册
		$is_reg = isset($homecfg['is_reg']) && $homecfg['is_reg'] ? true:false;
		$this->assign('reg_hidden', $is_reg?'':'hidden');
		
		//邮箱验证 email_seccode
		$is_email_seccode = isset($homecfg['email_seccode']) && $homecfg['email_seccode'] ? true:false;
		//手机验证 mobile_seccode
		$is_mobile_seccode = isset($homecfg['mobile_seccode']) && $homecfg['mobile_seccode'] ? true:false;
		
		$text_name = i18n('Username');
		$text_email = i18n('Email');
		$text_mobile = i18n('Mobile');
		
		//验证码登录		
		$placeholder_seccode_login = '';
		$seccode_login_icon = 'user';//user  envelope-o phone
		if ($is_email_seccode && $login_by_email) {
			$placeholder_seccode_login = $text_email . ' '.$placeholder_seccode_login;
			$seccode_login_icon = 'envelope-o';
		}
		
		if ($is_mobile_seccode && $login_by_mobile) {
			$placeholder_seccode_login = $text_mobile . ' '.$placeholder_seccode_login;
			$seccode_login_icon = 'phone';
		}
		
		$this->assign('seccode_hidden', !$is_email_seccode && !$is_mobile_seccode?'hidden':'');
		$this->assign('placeholder_seccode_login', $placeholder_seccode_login);
		$this->assign('seccode_login_icon', $seccode_login_icon);
		
		//普通登录
		$placeholder_name_login = $placeholder_seccode_login;
		$name_login_icon = $seccode_login_icon;
		if ($login_by_name) {
			$placeholder_name_login = $text_name . ' '.$placeholder_name_login;
			$name_login_icon = 'user';
		}
		
		$this->assign('placeholder_name_login', $placeholder_name_login);
		$this->assign('name_login_icon', $name_login_icon);
		
		//seccode_reg_placeholder
		$seccode_type = 0;
		$seccode_reg_placeholder = '';
		$seccode_reg_icon = 'envelope-o';//envelope-o phone
		if ($is_email_seccode && $reg_by_email) {
			$seccode_reg_placeholder = $text_email . ' '.$seccode_reg_placeholder;
			$seccode_reg_icon = 'envelope-o';
			$seccode_type = 1;
		}
		if ($is_mobile_seccode && $reg_by_mobile) {
			$seccode_reg_placeholder = $text_mobile . ' '.$seccode_reg_placeholder;
			$seccode_reg_icon = 'phone';
			$seccode_type = 2;
		}
		$this->assign('seccode_reg_placeholder', $seccode_reg_placeholder);
		$this->assign('seccode_reg_icon', $seccode_reg_icon);
		$this->assign('seccode_type', $seccode_type);
		$this->assign('seccode_require', $seccode_type==1?'email:true':'');
		
		
		
		//default
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $this->_attribs);
		
		return true;
	}	
}