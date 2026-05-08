<?php

/* 
 * eg: php <WWWDIR>/bin/t2hf.php <TPLNAME>
 * replace header and footer with blank.html
 */

define('APPNAME', 'admin');
define('RPATH_BASE', dirname(__FILE__) );
require_once (RPATH_BASE.'/../lib/base.php');

$name =isset($argv[1])?$argv[1]:'aitv';

$m = Factory::GetTemplate();

$res = $m->t2hf($name);
if (!$res) {
	exit("t2hf: convert '$name' header and footer failed!");
}

echo "OK";
