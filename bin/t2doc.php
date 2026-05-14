<?php

/* 
 * eg: php <WWWDIR>/bin/t2man.php <NAME> [TPLFILE]
 */

define('APPNAME', 'admin');
define('RPATH_BASE', dirname(__FILE__) );
require_once (RPATH_BASE.'/../lib/base.php');


$largv = array();
for ($i=1; $i<$argc; $i++) {
	$val = $argv[$i];
	switch ($val) {
		case '-p':
		case '--webroot':
			$webroot = $argv[++$i];
			break;
		case '-a':
		case '--appname':
			$appname = $argv[++$i];
			break;
		case '-n':
		case '--name':
			$name = $argv[++$i];
			break;
		case '-i':
		case '--input':
			$tplfile = $argv[++$i];
			break;
		default:
			$largv[] = $val;
			break;
	}
}

!$appname && $appname =isset($largv[0])?$largv[0]:'admin';
!$name && $name =isset($largv[1])?$largv[1]:'';
!$tplfile && $tplfile =isset($largv[2])?$largv[2]:'';

if (empty($webroot)) {
	$cfg = get_config();
	$webroot = isset($cfg['webroot'])?$cfg['webroot']:'';
} else if ($webroot=="root"){
	$webroot="";
}

//rlog(RC_LOG_DEBUG, 'webroot='.$webroot, $tplfile);

$m = Factory::GetDocument();
$ioparams = array();
$ioparams['_webroot'] = $webroot;
$res = $m->t2doc($appname, $name, $tplfile, $ioparams);

if (!$res) {
	exit("convert to DOC failed\n");
}

echo "OK\n";
