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
class PubModel extends CPubModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function PubModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}