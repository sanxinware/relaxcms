<?php
/**
 * @file
 *
 * @brief 
 *
 */
class HelloModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function HelloModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
		
	protected function show(&$options=array())
	{
		$runapp = Factory::GetApp($options['aname']);
		$appcfg = $runapp->getAppCfg();
		
		$this->assign('appcfg', $appcfg);
	}	
}