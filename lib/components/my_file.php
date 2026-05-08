<?php

/**
 * @file
 *
 * @brief 
 * 
 * 文件管理组件
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class MyFileComponent extends CMyFileDTComponent
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function MyFileComponent($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}