<?php

/* 
 * eg: php <WWWDIR>/bin/t2index.php <TPLNAME> 
 */

define('APPNAME', 'admin');
define('RPATH_BASE', dirname(__FILE__) );
require_once (RPATH_BASE.'/../lib/base.php');

$name =isset($argv[1])?$argv[1]:'aitv';
$outdir =isset($argv[2])?$argv[2]:'';

$m = Factory::GetTemplate();

$res = $m->t2res($name, $outdir);
if (!$res) {
	exit("convert index.html for public failed");
}

echo "OK";
