<?php

/**
 * @file
 *
 * @brief 
 * ÆðÊ¼Ò³
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


class HelpVersionComponent extends CUIComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function HelpVersionComponent($name, $options=null)
	{
		$this->__construct($name, $options);
	}

	
	public function show(&$options=array())
	{		
		
		$pname = get_product_name();
		
		$changelogFile= RPATH_DOCUMENT.DS.'ChangeLog.txt';
		$pChangeLogFile = RPATH_DOCUMENT.DS.'ChangeLog-'.$pname.'.txt';
		
		$changelogUICore = '';
		if (file_exists($pChangeLogFile)) {
			$isA = true;
			$changelog = s_read($pChangeLogFile);
			$changelogUICore = s_read($changelogFile);			
		} else {
			$changelog = s_read($changelogFile);			
		}
		
		$changelog = str_replace("\n", "<br>", $changelog);	
		
		if ($changelogUICore)			
			$changelogUICore = str_replace("\n", "<br>", $changelogUICore);	
			
		$this->assign("changelog", $changelog);		
		$this->assign("changelogUICore", $changelogUICore);		
		$this->assign('sys_product_id', get_product_id());
		$this->assign('sys_product_version', get_product_version());
		$this->assign('sys_product_fullname', get_product_fullname());
		$this->assign('sys_product_model', get_product_model());
		
	
	}
}