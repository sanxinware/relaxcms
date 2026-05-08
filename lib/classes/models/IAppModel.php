<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

define('APP_LANG_C', 1);
define('APP_LANG_PHP', 2);
define('APP_LANG_JAVA', 4);
define('APP_LANG_JS', 8);
define('APP_LANG_CSS', 16);
define('APP_LANG_SQL', 32);

define('APP_PLT_LINUX', 1);
define('APP_PLT_WINDOWS', 2);
define('APP_PLT_ANDROID', 4);
define('APP_PLT_MACOS', 8);
define('APP_PLT_CRAB', 16);
define('APP_PLT_RELAXCMS', 32);
define('APP_PLT_X86_32', 64);
define('APP_PLT_X86_64', 128);

define('APP_PLT_CRAB_AND_RC', APP_PLT_CRAB|APP_PLT_RELAXCMS);

//'1' =>'服务端应用', '2' =>'客户端应用', '3' =>'WEB应用',

define ('AT_SERVERAPP',	1);
define ('AT_CLIENTAPP',	2);
define ('AT_WEBAPP',	3);

define ('AT_RCAPP',	4);
define ('AT_RCTPL',	5);
define ('AT_RCTHE',	6);

define ('VT_UNSPEC',0);
define ('VT_NORNAL',1);
define ('VT_UPDATE',2);
define ('VT_EXTEND',4);

define ('VT_EXTRCAPP',5);
define ('VT_EXTRCTPL',6);
define ('VT_EXTRCTHE',7);

//install
define ('AI_DEFAULT',	1); //默认安装
define ('AI_ONEKEY',	2); //一键安装
define ('AI_CRABINSTALL',	4); //CRAB扩展安装
define ('AI_RCINSTALL',	8); //RC扩展安装
define ('AI_VHOSTINSTALL',	16); //VHOST安装

define ('AE_SN_NOT_FOUND',	-2);
define ('AE_SN_NO_UID',   	-3);
define ('AE_SN_UID_INVALID',   	-3);
define ('AE_USER_FORBIDDEN',-4);
define ('AE_USER_NO_BEAN',  -5);
define ('AE_APP_NOT_ORDER', -6);
define ('AE_APP_NOT_PAYED', -7);
define ('AE_NO_RKEY', 		-8);

interface IAppModel
{
		
}