<?php

/**
 * @file
 *
 * @brief 
 * 
 * 文件基础模型类
 * 
 * Copyright (c), 2024, relaxcms.com
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


class CAdminFileModel extends CFileModel
{	
	
	public function __construct($name, $options=array())
	{
		$options['modname'] = 'file';
		parent::__construct($name, $options);
	}
	
	public function CAdminFileModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}	
}