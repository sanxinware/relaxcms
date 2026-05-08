<?php
/**
 * @file
 *
 * @brief 
 * 
 * 菜单
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class MenuModel extends CParamsModel
{
	public function __construct($name, $options=array())
	{	
		parent::__construct($name, $options);
	}
		
	public function MenuModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}

	
	protected function initDefaultParams(&$params=array())
	{
		return $params;				
	}

	public function set(&$params=array(), &$options=array())
	{
		$res = parent::set($params, $options);
		if ($res) {
			$app = Factory::GetApp();
			$app->cacheMenus();
		}
		return $res;
	}

	public function reset(&$params=array())
	{
		$res = parent::reset($params);
		if ($res) {
			$app = Factory::GetApp();
			$app->cacheMenus();
		}
		return true;
	}

}
