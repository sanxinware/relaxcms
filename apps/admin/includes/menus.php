<?php
/**
 * @file
 *
 * @brief 
 * 初始菜单
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

$menus = array(
		'system'=>array(
			'name'	=>'system',
			'component'=>'system',
			'task'	=>array('show'=>'r', 'reboot'=>'x','shutdown'=>'x'),
			'icon' => '',
			'class' => 'icon-settings',
			'pid'=>'300',
			'parent' => '',
			'level' => 2,
			'pos'=> 'top',
			'sort'=>13,
			),
		
		'api'=>array(
			'name'	=>'api',
			'component'=>'api',
			'task'	=> array('helloapi','localwebservice'=>'i', 'todoForNew'=>'i'),
			'icon' => '',
			'level' => 7,
			'pid'=>'0',
			'hidden' => true,
			'parent' => 'system',
			),		
		
		/*
		'system_var'=>array (
			'name'	=>'system_var',
			'component'=>'system_var',
			'task'=>array('show'=>'r', 'edit'=>'w'),
			'icon' => '',
			'parent' => 'system',
			'level' => 2
			),*/

		'config'=>array(
			'name'	=>'config',
			'component'=>'config',
			'task'	=>array('show'=>'r', 'apply'=>'w'),
			'icon' => '',
			'pid'=>'302',
			'level' => 2,
			'parent' => 'system',
			'is_default'=>true,
			'sort'=>1,
			),
			
		'user'=>array(
			'name'	=>'user',
			'component'=>'user',
			'task'	=>array('show'=>'r', 'add'=>'a', 'edit'=>'u', 'del'=>'d', 'authBasicRequest'=>'i'),
			'modname' => 'user',
			'level' => 2,
			'parent' => 'system',
			'sort'=>3,
			),	
		'group'=>array(
			'name'	=>'group',
			'component'=>'group',
			'task'	=>array('show'=>'r', 'add'=>'a', 'edit'=>'u', 'delete'=>'d'),
			'icon' => '',
			'pid'=>'307',
			'level' => 2,
			'parent' => 'system',
			'sort'=>5,		
			),	
		'role'=>array(
			'name'	=>'role',
			'component'=>'role',
			'task'	=>array('show'=>'r', 'add'=>'a', 'edit'=>'u', 'delete'=>'d'),
			'icon' => '',
			'pid'=>'308',
			'level' => 2,
			'parent' => 'system',
			'sort'=>6,		
			),	
		
		'file'=>array(
			'name'	=>'file',
			'component'=>'file',
			'task'	=>array('getFrameFromVideo'=>'i', 'uploadFile'=>'io', 'f'=>'i'),
			'modname' => 'file',
			'parent' => 'system',
			'level' => 1,	
			'sort'=>7,	
			),
		'storage'=>array(
			'name'	=>'storage',
			'component'=>'storage',
			'task'	=>array('authBasicRequestForStorage'=>'i'),
			'parent' => 'system',
			'level' => 1,	
			'sort'=>8,	
			),
		'session'=>array(
			'name'	=>'session',
			'component'=>'session',
			'task'	=>'',
			'icon' => '',
			'pid'=>'308',
			'level' => 2,
			'parent' => 'system',
			'sort'=>9,		
			),	
		'log'=>array(
			'name'	=>'log',
			'component'=>'log',
			'task'	=>array('show'=>'r', 'delete'=>'d'),
			'icon' => '',
			'pid'=>'309',
			'level' => 2,
			'parent' => 'system',
			'sort'=>11,		
			),	
			
		'backup'=>array(
			'name'	=>'backup',
			'component'=>'backup',
			'task'	=>array('show'=>'r', 'add'=>'w'),
			'icon' => '',
			'pid'=>'310',
			'level' => 2,
			'parent' => 'system',
			'sort'=>13,		
			),	
		
		'super'=>array(
			'name'	=>'super',
			'component'=>'super',
			'task'	=>'',
			'icon' => '',
			'pid'=>'311',
			'level' => 8,
			'parent' => 'system',
			'sort'=>15,
			),
		'var'=>array (
			'name'	=>'var',
			'component'=>'var',
			'task'=>array('show'=>'r', 'edit'=>'w'),
			'icon' => '',
			'parent' => 'system',
			'level' => 2,
			'sort'=>17,
			),
		'menu'=>array (
			'name'	=>'menu',
			'component'=>'menu',
			'task'	=>'',
			'icon' => '',
			'parent' => 'system',
			'level' => 2,	
			'sort'=>19,		
			),
			
		'app'=>array (
			'name'	=>'app',
			'component'=>'app',
			'task'	=>'',
			'icon' => '',
			'parent' => 'system',
			'level' => 2,	
			'sort'=>21,		
			),
		
			
		//help menu items
		'help'=>array(
			'name'	=>'help',
			'component'=>'help',
			'task'	=>'',
			'icon' => '',
			'class' => 'icon-question',
			'pid'=>'400',
			'parent' => '',
			'sort'=>14,
			),	
		
		'help_version'=>array(
			'name'	=>'help_version',
			'component'=>'help_version',
			'task'	=>'',
			'icon' => '',
			'pid'=>'402',
			'parent' => 'help',
			'sort'=>20,
			),
		'help_sysinfo'=>array(
			'name'	=>'help_sysinfo',
			'component'=>'help_sysinfo',
			'task'	=>'',
			'icon' => '',
			'parent' => 'help',
			'sort'=>22,
			),
			
		'help_license'=>array(
			'name'	=>'help_license',
			'component'=>'help_license',
			'task'	=>'',
			'icon' => '',
			'parent' => 'help',
			'sort'=>24,
			),

		'help_manual'=>array(
			'name'	=>'help_manual',
			'component'=>'help_manual',
			'task'	=>'',
			'icon' => '',
			'parent' => 'help',
			'sort'=>26,
			),
		'help_download'=>array(
			'name'	=>'help_download',
			'component'=>'help_download',
			'task'	=>'',
			'icon' => '',
			'parent' => 'help',
			'sort'=>28,
			
			),
			
		'help_upgrade'=>array(
			'name'	=>'help_upgrade',
			'component'=>'help_upgrade',
			'task'	=>array('checkICloudClientVersion'=>'i'),
			'icon' => '',
			'level' => 4,			
			'parent' => 'help',
			'sort'=>30,
			),			
			
		'help_about'=>array(
			'name'	=>'help_about',
			'component'=>'help_about',
			'task'	=>'',
			'icon' => '',
			'parent' => 'help',
			'hidden'=>true,
			'sort'=>32,
			),
);			
?>