<?php

/**
 * @file
 *
 * @brief 
 *  �û���
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class GroupComponent extends CGroupComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function GroupComponent($name, $options)
	{
		$this->__construct($name, $options);
	}

	protected function add(&$options=array())
	{
		parent::add($options);
		$this->setTpl('group_edit');
	}

	protected function edit(&$options=array())
	{
		parent::edit($options);
		$this->setTpl('group_edit');
	}
}