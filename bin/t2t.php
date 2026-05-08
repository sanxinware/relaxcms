<?php

/* 
 * eg: php <WWWDIR>/bin/t2t.php <TPLNAME> 
 */

define('APPNAME', 'admin');
define('RPATH_BASE', dirname(__FILE__) );
require_once (RPATH_BASE.'/../lib/base.php');

$name =isset($argv[1])?$argv[1]:'aitv';

$m = Factory::GetTemplate();

$res = $m->t2t($name);
if (!$res) {
	exit("convert theme to TPL failed");
}

echo "OK";
