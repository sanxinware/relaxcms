<?php
/**
 * @file
 *
 * @brief 
 * Copyright (c), 2023, relaxcms.com
 */

class HelloComponent extends CDTFileComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function HelloComponent($name, $options)
	{
		$this->__construct($name, $options);
	}	
	
	protected function detail(&$options=array())
	{
		$this->setActiveTab(2);
		
		parent::detail($options);
		
		$this->setTpl('hello_detail');
	}
}