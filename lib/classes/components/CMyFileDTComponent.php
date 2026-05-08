<?php
/**
 * @file
 *
 * @brief 
 *  个人文件组件
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CMyFileDTComponent extends CFileDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CMyFileDTComponent($name, $options)
	{
		$this->__construct($name, $options);
	}	
	
	
	protected function show(&$options=array())
	{
		parent::show($options);
		$this->setTpl('my_file');		
	}
	
	protected function getFileModelName()
	{
		return 'my_file';
	}
	
	protected function doGetSubDir($pid, $params=array(), &$options=array() )
	{
		$m = Factory::GetModel('my_file');
		$fdb = $m->getSubDir($pid, $params, $options);
		
		return $fdb;
		
	}
}