<?php

/* include required scripts */
require_once('../../functions/functions.php');

/* verify posted data */
CheckReferrer();

/* verify that user is authenticated! */
isUserAuthenticated ();
$user = getActiveUserDetails();

/* sanitize */
$_POST = filter_user_input ($_POST, true, true, false);

/* check lenghts */
if(strlen($_POST['ipampassword1'])!=32)							{ die("<div class='alert alert-danger'>"._("Invalid password")."</div>"); }
if(strlen($_POST['ipampassword2'])!=32)							{ die("<div class='alert alert-danger'>"._("Invalid password")."</div>"); }

/* check match */
if($_POST['ipampassword1']!=$_POST['ipampassword2'])			{ die("<div class='alert alert-danger'>"._("Passwords do not match")."</div>"); }

/* all good, update password! */
if(!update_user_password($user['id'],$_POST['ipampassword1']))	{ }
else															{ print "<div class='alert alert-success'>Hi, $user[real_name], your password was updated. <a class='btn btn-sm btn-default' href='/'>Dashboard</a>"; }
?>