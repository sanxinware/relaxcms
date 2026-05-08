<?php

/**
 * @file
 *
 * @brief 
 * 
 * ORG 模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class OrgModel extends COrgModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);		
	}
		
	public function OrgModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}