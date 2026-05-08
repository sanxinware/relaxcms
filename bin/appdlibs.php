<?php

/* 
 * 通过php <WWWDIR>/bin/appinfo.php <APPNAME> 执行, 
 */

define('APPNAME', 'admin');
define('RPATH_BASE', dirname(__FILE__) );
require_once (RPATH_BASE.'/../lib/base.php');

$name =isset($argv[1])?$argv[1]:'';
$type =isset($argv[2])?intval($argv[2]):0;

$appinfo = getAppInfo($name, $type);

$dlibs='';
if ($appinfo && isset($appinfo['dlibs'])) {
	$dlibs = $appinfo['dlibs'];
}	

echo $dlibs;
exit;