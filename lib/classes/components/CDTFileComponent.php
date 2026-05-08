<?php

class CDTFileComponent extends CFileDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CDTFileComponent($name, $options)
	{
		$this->__construct($name, $options);
	}		
	
	protected function show(&$options=array())
	{
		parent::show($options);
		$this->setTpl('dt_show');
	}
}