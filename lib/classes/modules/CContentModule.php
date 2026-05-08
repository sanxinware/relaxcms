<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CContentModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function CContentModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	
	protected function show(&$options=array())
	{
		$tid = $this->_attribs['tid'];
		$cid = $this->_attribs['cid'];
		
		$tpl = isset($this->_attribs['tpl'])?$this->_attribs['tpl']:'content';
		$miw = isset($this->_attribs['miw'])?$this->_attribs['miw']:'650';
		$this->_attribs['miw'] = $miw;
		
		$sc = isset($this->_attribs['sc'])?$this->_attribs['sc']:'';
		$sh = isset($this->_attribs['sh'])?$this->_attribs['sh']:'';
		$sr = isset($this->_attribs['sr'])?$this->_attribs['sr']:'';
		
		$ha = isset($this->_attribs['ha'])?$this->_attribs['ha']:false;
		
		$m = Factory::GetModel('content');
		if (is_uuid($tid)) {
			$view = $m->getForViewByUUID($tid, $options);
		} else {
			$view = $m->getForView($tid, $options);
		}
		

		$view['share'] = is_var_mask(6, $view['status'])?true:false;
		
		$m2 = Factory::GetModel('catalog');	
		$cataloginfo = $m2->get($view['cid']);	

		//var_dump($view['cid']);exit;
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $cataloginfo['id']);		
		

		$m3 = Factory::GetModel('catalog');	
		
		$position = $m3->position($cataloginfo['id'], $options);		
		$position .= "<li> <span>$view[title]</span>  </li> ";		
		
		$url = $options['_rooturl'].$view['url'];
		$qr = Factory::GetQRCode();
		$data = $qr->qrData($url);	
		$this->assign('qrData', $data);
		
		
		$this->assign('position', $position);
		
		$scf = Factory::GetSiteConfiguration();
		
		$this->assign('view', $view);
		$this->assign('content', $view);
		
		$this->assign('scf', $scf);
		$this->assign('ha', $ha);
		$this->assign('sc', $sc);
		$this->assign('sh', $sh);
		$this->assign('sr', $sr);
		
		return $view;
	}	
	
}