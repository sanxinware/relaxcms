<?php

/**
 * @file
 *
 * @brief 
 * 
 * 验证码模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class User_tokenModel extends CTableModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function User_tokenModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}