<?php

/**
 * Script to display available VLANs
 */

# verify that user is logged in
$User->check_user_session();

# all or details
if(isset($_GET['subnetId']))	{ include('vlan-print-details.php'); }
else 							{ include('vlan-print.php'); }
?>