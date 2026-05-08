<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class ServerModel extends CServerModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function ServerModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}	
}

