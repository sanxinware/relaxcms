<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class COneContentPageModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function COneContentPageModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	
	protected function show(&$options=array())
	{
		$tid = $this->_attribs['tid'];
		$cid = $this->_attribs['cid'];
		
		
		$tinfo = array();
		$m = Factory::GetModel('content');
		if ($tid == 0) {		
			$contentinfo = $m->getOne(array('cid'=>$cid));
			if (!$contentinfo)	 {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "invalid cid '$cid'!");
				return false;
			}
			$tid = $contentinfo['id'];
		}
		
		$tinfo = $m->getForView($tid, $options);
		$this->assign('contentinfo', $tinfo);	
		
		
		$m2 = Factory::GetModel('catalog');		
		$position = $m2->position($tinfo['cid'], $options);		
		$position .= "<li> <span>$tinfo[title]</span>  </li> ";		
		$this->assign('position', $position);
		
		$url = $options['_rooturl'].$tinfo['url'];
		$qr = Factory::GetQRCode();
		$data = $qr->qrData($url);	
		$this->assign('qrData', $data);
		
	
		return $tinfo;
	}	
	
}