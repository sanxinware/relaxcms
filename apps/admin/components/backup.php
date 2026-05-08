<?php
/**
 * @file
 *
 * @brief 
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class BackupComponent extends CBackupComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function BackupComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
		
}