<?php

define('APPNAME', 'admin');
define('RPATH_BASE', dirname(__FILE__) );
require_once (RPATH_BASE.'/../lib/base.php');
//apiurl
$apiurl = RPATH_CACHE.DS.'.apiurl';
if (file_exists($apiurl)) {
	$updateapi = s_read($apiurl);
	$updatetype = 1;
} else {
	$updateapi = "https://crab.relaxcms.com/api";
	$updatetype = 0;
}

$manager = "admin";
$manager_pwd = randstr();
$params = array(
		//重写
		'exists_rewrite'=>1,
		//产品
		'product_name'=>"CRAB",
		
		//DB
		'dbtype'=>"mysqlpdo",
		'dbtype'=>"mysqlpdo",
		'dbhost'=>"127.0.0.1:20336",
		'dbport'=>"",
		'dbuser'=>"root",
		'dbpassword'=>"",
		'dbname'=>"crabdb",
		'dbcharset'=>"utf8",
		'prefix'=>"cms_",
		
		//更新
		'updatetype'=>$updatetype,
		'updateapi'=>$updateapi,
		
		//管理员
		'manager'=>$manager,
		'manager_pwd'=>$manager_pwd,
		'manager_email'=>"admin@relaxcms.com",
		
		//应用
		'apps'=>array('crab','net', 'vhost'),
		);

$m = Factory::GetInstallation();
$res = $m->install($params);

$m2 = Factory::GetModel('net_nif');
$udb = $m2->getAddrs();
echo "====== CRABADMIN HTTP/HTTPS AND Administrator ======\n";
foreach ($udb as $key => $v) {
	$name = $v['name'];
	$ip = $v['ip'];
	$host = is_ip4($ip)?$ip:"[$ip]";
	
	echo "$name: http://$host:40080\n";
	echo "$name: https://$host:40443\n";
}
echo "Administrator: $manager\n";
echo "Password: $manager_pwd\n";

echo "====== Default HTTP/HTTPS ======\n";
foreach ($udb as $key => $v) {
	if (!$v['isdefault'])
		continue;
		
	$name = $v['name'];
	$ip = $v['ip'];
	$host = is_ip4($ip)?$ip:"[$ip]";
	
	echo "$name: http://$host\n";
	echo "$name: https://$host\n";
}