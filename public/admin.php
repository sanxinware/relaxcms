<?php

//error_reporting(E_ALL^E_STRICT);
//error_reporting(E_ALL^E_NOTICE);	
define('RPATH_BASE', dirname(__FILE__) );
define('RPATH_PARENT', dirname(RPATH_BASE) );
define('APPNAME', 'admin');
try {
	//require_once (RPATH_BASE.'/../lib/base.php');
	require_once (RPATH_PARENT.'/lib/base.php');
	RC::run(APPNAME);
} catch(CException $e) {	
	echo $e->errorMessage();
} 

?>
