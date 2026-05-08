<?php

defined('RPATH_BASE') or die();

class CDigestModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function CDigestModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	
	
	protected function formatTitle(&$row, $maxlen)
	{
		
		if ($maxlen > 0)
			$row['title'] = utf8_substr($row['name'], 0, $maxlen);	
	}
	
	
	protected function formatDateTime(&$row, $time_format)
	{
		$row['timelong'] = tformat_timelong($row['ts']);
		if ($time_format) {
			$row['time'] = tformat($row['ts'], $time_format);
		} else {
			$vt1 = tformat_vtime($row['ts']);
			$vt2 = tformat_vtime(time());
			$tf = ($vt1['year'] != $vt2['year'])?'Y-m-d':'m-d';
			$row['time'] = tformat($row['ts'], $tf);
		}
	}
	
	protected function getList($params, $num, $options)
	{
		$m =  Factory::GetModel('content');
		$udb = $m->getList($params, $num, $options); 
		
		return $udb;
	}
	
	public function show(&$options=array())
	{
		$res = parent::show($options);
		
		
		$flags = isset($this->_attribs['flags'])?intval($this->_attribs['flags']):0;
		$num = isset($this->_attribs['num'])?intval($this->_attribs['num']):12;
		$cid = isset($this->_attribs['cid'])?intval($this->_attribs['cid']):0;
		$mid = isset($this->_attribs['mid'])?$this->_attribs['mid']:'';
		$strict = isset($this->_attribs['strict'])?$this->_attribs['strict']:'';
		
		
		!$num && $num = 6;
		
		$this->_attribs["num"] = $num; 
		$this->_attribs["flags"] = $flags;
		
		$maxlen = isset($this->_attribs['maxlen'])?intval($this->_attribs['maxlen']):128;
		$notitle = isset($this->_attribs['notitle'])?true:false;
		$time_format = isset($this->_attribs['time_format'])?$options['time_format']:'';
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "strict=$strict, cid=$cid");
		
		$params = array();
		if ($flags > 0)
			$params['flags'] = $flags;
		if ($cid > 0)
			$params['cid'] = $cid;
		
		$params['mid'] = $mid;
		
		$udb = $this->getList($params, $num, $options);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params, $udb);
		
		$moreurl='';
		$_udb = array();
		foreach ($udb as $key=>&$v) {
			// ...
			!$cid && $cid = $v['cid'];
			
			$this->formatTitle($v, $maxlen);
			$this->formatDateTime($v, $time_format);
			
			//icon
			//$icon = ?'fa fa-image ':$v['icon'];
			$icon = trim($v['icon']);
			if (empty($icon)) {
				if ($v['__ctype'] == FT_VIDEO) {
					$icon = 'fa fa-film';
				} else {
					$icon = 'fa fa-image';
				}
			} 
			
			if (is_uripath($icon)) {
				$v['_icon'] = "<img src='$icon' />";
			} else {
				// <i class="widget-thumb-icon bg-blue-madison $v[icon] " style="width:96px; line-height: 54px; font-size: 36px;" > </i>
				$v['_icon'] = "<i class='widget-thumb-icon bg-blue-madison $icon ' > </i>";
			}
			
			
			
			
			$ctags = $v['playurl']?'class="videoplay" data-url="'.$v['playurl'].'" type="video/mp4"':'';
			!$ctags && $ctags = $v['photo']?'class="gallery-img" ':'';
			
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $ctags);
			
			$v['ctags'] = $ctags;
			
			$_udb[] = $v;
		}
		if ($cid > 0) {
			$m2 = Factory::GetModel("catalog");
			$catalog = $m2->getForView($cid, $options);
			$this->assign('catalog', $catalog);
		}
		
		
		$this->setColumn($_udb);
		
		$this->assign('udb', $_udb);	
		
		return $_udb;		
	}
}