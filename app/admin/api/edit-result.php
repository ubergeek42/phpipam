<?php

/**
 * Script to disaply api edit result
 *************************************/

/* functions */
require( dirname(__FILE__) . '/../../../functions/functions.php');

# initialize user object
$Database 	= new Database_PDO;
$User 		= new User ($Database);
$Admin	 	= new Admin ($Database);
$Result 	= new Result ();

# verify that user is logged in
$User->check_user_session();

/* checks */
$error = array();

if($_POST['action']!="delete") {
	# code must be exactly 32 chars long and alfanumeric
	if(strlen($_POST['app_code'])!=32 || !ctype_alnum($_POST['app_code']))									{ $error[] = "Invalid application code"; }
	# name must be more than 2 and alphanumberic
	if(strlen($_POST['app_id'])<3 || strlen($_POST['app_id'])>12 || !ctype_alnum($_POST['app_id']))			{ $error[] = "Invalid application id"; }
	# permissions must be 0,1,2
	if(!($_POST['app_permissions']==0 || $_POST['app_permissions']==1 || $_POST['app_permissions'] ==2 || $_POST['app_permissions'] ==3 ))	{ $error[] = "Invalid permissions"; }
}

# die if errors
if(sizeof($error) > 0) {
	print "<div class='alert alert alert-danger'>";
	print _('Error');
	print "<ul>";
	foreach($error as $err) {
		print "<li>"._($err)."</li>";
	}
	print "</ul>";
	print "</idv>";
}
else {
	# create array of values for modification
	$values = array("id"=>@$_POST['id'],
					"app_id"=>$_POST['app_id'],
					"app_code"=>@$_POST['app_code'],
					"app_permissions"=>@$_POST['app_permissions'],
					"app_comment"=>@$_POST['app_comment']);

	# execute
	if(!$Admin->object_modify("api", $_POST['action'], "id", $values)) 	{ $Result->show("danger",  _("API $_POST[action] error"), true); }
	else 																{ $Result->show("success", _("API $_POST[action] success"), true); }
}

?>