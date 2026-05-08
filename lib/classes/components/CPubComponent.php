<?php

defined( 'RMAGIC' ) or die( 'Restricted access' );

class CPubComponent extends CDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CPubComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function initParamsForDetail(&$params, &$options=array())
	{
		$res = parent::initParamsForDetail($params, $options);
		//发布信息
		$m = $this->getModel();
		$m->loadPubInfoForView($params, $options);
		
		
		return $res;
	}
}