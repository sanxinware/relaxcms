<?php

/**
 * @file
 *
 * @brief 
 * 
 * PUBTO
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class PubtoModel extends CPubtoModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function PubtoModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}