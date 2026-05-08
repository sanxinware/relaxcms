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
		'my'=>array(
			'name'	=>'my',
			'class' => 'icon-home',
			'component'=>'my',
			'task'	=>'',
			'icon' => '',
			'pid' => '100',
			'parent'=> '',
			'sort'=>0
		),
		'my_info'=>array(
			'name'	=>'my_info',
			'component'=>'my_info',
			'task'	=>array('getUserInfo'=>'i'),
			'icon' => '',
			'pid' => '101',
			'parent' => 'my',
			'sort'=>8,
			),
		
		
		'my_password'=>array(
			'name'	=>'my_password',
			'component'=>'my_password',
			'task'	=>'',
			'icon' => '',
			'pid'=>'102',
			'parent' => 'my',
			'sort'=>9,
			),
		'my_resetpassword'=>array(
			'name'	=>'my_resetpassword',
			'component'=>'my_resetpassword',
			'task'	=>'',
			'icon' => '',
			'pid'=>'0',
			'hidden' => true,
			'parent' => 'my',
			),
			
		'my_ip'=>array(
			'name'	=>'my_ip',
			'component'=>'my_ip',
			'task'	=>'',
			'icon' => '',
			'parent' => 'my',
			'hidden' => true,
			),
		'my_file'=>array(
			'name'	=>'my_file',
			'component'=>'my_file',
			'task'	=>'',
			'icon' => '',
			'parent' => 'my',
			),
		'login'=>array(
			'name'	=>'login',
			'component'=>'login',
			'task'	=>array('postLogin'=>'i', 'postRegister'=>'i', 'getLoginToken'=>'i', 'register'=>'i'),
			'icon' => '',
			'pid'=>'0',
			'hidden' => 'true',
			'parent' => 'my',
			),
		
		'seccode'=>array(
			'name'	=>'seccode',
			'component'=>'seccode',
			'task'	=>'',
			'icon' => '',
			'pid'=>'0',
			'hidden' => 'true',
			'parent' => 'my',
			),
			
		'logout'=>array(
			'name'	=>'logout',
			'component'=>'logout',
			'task'	=>'',
			'icon' => '',
			'pid'=>'0',
			'hidden' => 'true',
			'parent' => 'my',
			),
		
		'main'=>array(
			'name'	=>'main',
			'component'=>'main',
			'task'	=>'',
			'icon' => '',
			'parent' => 'my',
			'pid'=>'1',
			),
		
);			
?>