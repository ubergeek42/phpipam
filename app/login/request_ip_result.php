<?php

/* functions */
require( dirname(__FILE__) . '/../../functions/functions.php');
/* @mail functions ------------------- */
require( dirname(__FILE__) . '/../../functions/functions-mail.php');

# initialize user object
$Database 	= new Database_PDO;
$Subnets 	= new Subnets ($Database);
$Tools	 	= new Tools ($Database);
$Result 	= new Result ();

# fetch settings, user is not authenticated !
$settings = $Tools->fetch_settings();

# requests must be enabled!
if($settings['enableIPrequests']==1) {
	# verify email
	if(!validate_email($_POST['requester']) ) 	{ $Result->show("danger", _('Please provide valid email address').'! ('._('requester').': '.$_POST['requester'].')', true); }
	# add request
	if($Tools->request_add ($_POST)) {
												{ $Result->show("success", _('Request submitted successfully')); }
		# send mail
		if(!sendIPReqEmail($_POST))				{ $Result->show("danger",  _('Sending mail for new IP request failed'), true); }
		else									{ $Result->show("success", _('Sending mail for IP request succeeded')); }
	}
	else 										{ $Result->show("danger",  _('Error submitting new IP address request'), true); }

} else 											{ $Result->show("danger",  _('IP requests disabled'), true); }

?>