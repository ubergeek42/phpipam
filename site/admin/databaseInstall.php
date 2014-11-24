<?php

/*
 *	Script to upgrade database
 **************************************/

/* use required functions */
require_once('../../config.php');
require_once('../../functions/functions-install.php');

/* filter input */
$_POST = filter_user_input($_POST, true, true, false);

/* get root username and pass */
$root['user'] = $_POST['mysqlrootuser'];
$root['pass'] = $_POST['mysqlrootpass'];

/* try to install new database */
if(installDatabase($root)) {
	print '<div class="alert alert-block alert-success">Database installed successfully! <br> <a href="'.create_link("login").'" class="btn btn-sm btn-default">Login to phpIPAM</a><hr>Default credentials are <strong>Admin/ipamadmin</strong></div>';
}

?>