<?php

include_once "../conf/conf.php";

define('DB_SERVER', $dolibarr_main_db_host);
define('DB_NAME', $dolibarr_main_db_name);
define('DB_USER', $dolibarr_main_db_user);
define('DB_PASS', $dolibarr_main_db_pass);

function connectDB() {

	error_reporting(E_ERROR);

	$con = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
	mysql_set_charset('utf8',$con);
	mysql_select_db(DB_NAME, $con) or die('ERROR con la conexión: '.mysql_error());

}

?>