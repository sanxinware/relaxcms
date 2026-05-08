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

class CMyFileModel extends CFileModel
{
	public function __construct($name, $options=array())
	{
		$options['modname'] = 'file';
		parent::__construct($name, $options);
	}
		
	public function CMyFileModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}


	public function select($params=array(), &$options=array())
	{
		$uid = get_uid();
		$params['cuid'] = $uid;
		
		$res = parent::select($params, $options);
		return $res;
	}


	protected function initAddParams(&$params=array(), &$options=array())
	{
		$params['flags'] = 2;
		$params['flags_disablemask'] = ~FF_MYFLAGS;
	}
	
	protected function initEditParams(&$params=array(), &$options=array())
	{
		$params['flags_disablemask'] = ~FF_MYFLAGS;
	}
	

	protected function maskStatus($newStatus, $oldStatus)
	{
		return ($newStatus&FF_MYFLAGS)|($oldStatus&~FF_MYFLAGS);		
	}

	protected function checkParams(&$params, &$options=array())
	{
		$res = parent::checkParams($params, $options);
		if (!$res)
			return false;

		if (isset($params['flags'])) {
			$fileinfo = $this->get($params['id']);
			if ($fileinfo) {			
				$params['flags'] = $this->maskStatus($params['flags'], $fileinfo['flags']);	
			}
		}
		return true;
	}
}