<?php

/**
 * @file
 *
 * @brief 
 * 
 * file model
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class My_fileModel extends CMyFileModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function My_fileModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}