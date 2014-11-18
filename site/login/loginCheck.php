<?php

/**
 *
 * Script to verify userentered input and verify it against database
 *
 * If successfull write values to session and go to main page! 
 *
 */


/* require scripts */
require_once('../../functions/functions-install.php');

/* fetch username / pass if they are provided */
if( !empty($_POST['ipamusername']) && !empty($_POST['ipampassword']) )  {

	# initialize array
	$ipampassword = array();

	$ipamusername = $_POST['ipamusername'];
	$ipampassword['raw'] = $_POST['ipampassword'];
	$ipampassword['md5'] = $_POST['ipampassword'];
	
	// verify that there are no invalid characters
	if(strpos($ipamusername, " ") >0 ) 			{ die("<div class='alert alert-danger'>"._("Invalid characters in username")."!</div>"); }
	if(strpos($$ipampassword['raw'], " ") >0 ) 	{ die("<div class='alert alert-danger'>"._("Invalid characters in password")."!</div>"); }
	
	
	// check failed table
	if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))	{ $ip = $_SERVER['HTTP_X_FORWARDED_FOR']; }
	else										{ $ip = $_SERVER['REMOTE_ADDR']; }
	$cnt = check_blocked_ip ($ip);
	if($cnt>4) {
		updateLogTable ("Login IP blocked", "Login from IP address $ip was blocked because of 5 minute block after 5 failed attempts", 1);
		die('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button>'._('You have been blocked for 5 minutes due to authentication failures').'!</div>');
	}
		
	/* check local login */
	checkLogin ($ipamusername, $ipampassword['md5'], $ipampassword['raw']);
}
//Username / pass not provided
else {
	die('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button>'._('Please enter your username and password').'!</div>');
}

?>