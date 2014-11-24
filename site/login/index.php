<?php 
/* if title is missing set it to install */
if(strlen(@$settings['siteTitle'])==0) { $settings['siteTitle'] = "phpipam IP management installation"; }

# start session if not set
if(!isset($_SESSION)) { session_start(); }

/* logout? */
if (isset($_SESSION['ipamusername'])) 	{ 
	# destroy session 
	session_destroy();	
	# update table
	updateLogTable ('User has logged out', 0); 
	# set logout flag
	$logout = true;
}

# set default language
if(isset($settings['defaultLang']) && !is_null($settings['defaultLang']) ) {
	# get language
	$lang = getLangById ($settings['defaultLang']);
	
	putenv("LC_ALL=$lang[l_code]");
	setlocale(LC_ALL, $lang['l_code']);		// set language		
	bindtextdomain("phpipam", "./functions/locale");	// Specify location of translation tables
	textdomain("phpipam");								// Choose domain
}
?>
	
<?php 
	
if($_GET['page'] == "login") 				{ include_once('loginForm.php'); }
else if ($_GET['page'] == "request_ip") 	{ include_once('requestIPform.php'); }
else 										{ $_GET['subnetId'] = "404"; print "<div id='error'>"; include_once('site/error.php'); print "</div>"; }
?>

<!-- login response -->
<div id="loginCheck"><?php if ($logout) print '<div class="alert alert-success">'._('You have logged out').'</div>'; ?></div>