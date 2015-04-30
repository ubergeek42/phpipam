<?php

/**
 * Function to add / edit / delete subnet
 ********************************************/

/* functions */
require( dirname(__FILE__) . '/../../../functions/functions.php');

# initialize user object
$Database 	= new Database_PDO;
$User 		= new User ($Database);
$Admin	 	= new Admin ($Database, false);
$Subnets	= new Subnets ($Database);
$Sections	= new Sections ($Database);
$Addresses	= new Addresses ($Database);
$Tools		= new Tools ($Database);
$Result 	= new Result ();

# verify that user is logged in
$User->check_user_session();


# ID must be numeric
if($_POST['action']=="add") {
	if(!is_numeric($_POST['sectionId']))						{ $Result->show("danger", _("Invalid ID"), true); }
} else {
	if(!is_numeric($_POST['subnetId']))							{ $Result->show("danger", _("Invalid ID"), true); }
	if(!is_numeric($_POST['sectionId']))						{ $Result->show("danger", _("Invalid ID"), true); }
}
# if show name than description must be set
if(@$_POST['showName']==1 && strlen($_POST['description'])==0) 	{ $Result->show("danger", _("Please enter subnet description to show as name!"), true); }

# verify that user has permissions to add subnet
if($_POST['action']=="add") {
	if($Sections->check_permission ($User->user, $_POST['sectionId']) != 3) { $Result->show("danger", _('You do not have permissions to add new subnet in this section')."!", true, true); }
}
# otherwise check subnet permission
else {
	if($Subnets->check_permission ($User->user, $_POST['subnetId']) != 3) 	{ $Result->show("danger", _('You do not have permissions to add edit/delete this subnet')."!", true, true); }
}

# we need old values for mailing
if($_POST['action']=="edit" || $_POST['action']=="delete") {
	$subnet_old_details = (array) $Subnets->fetch_subnet(null, $_POST['subnetId']);
}

# get mask and subnet
$_POST['mask']=trim(strstr($_POST['subnet'], "/"),"/");
$_POST['subnet']=strstr($_POST['subnet'], "/",true);
$_POST['id']=$_POST['subnetId'];
//set cidr
$_POST['cidr'] = $_POST['subnet']."/".$_POST['mask'];


# get section details
$section = (array) $Sections->fetch_section(null, $_POST['sectionId']);
# fetch custom fields
$custom = $Tools->fetch_custom_fields('subnets');

# get master subnet details for folder overrides
if($_POST['masterSubnetId']!=0)	{
	$master_section = (array) $Subnets->fetch_subnet(null, $_POST['masterSubnetId']);
	if($master_section['isFolder']==1)	{ $parent_is_folder = true; }
	else								{ $parent_is_folder = false; }
}
else 									{ $parent_is_folder = false; }


/**
 * If request came from IP address subnet edit and
 * action2 is Delete then change action
 */
if(	(isset($_POST['action2'])) && ($_POST['action2']=="delete") ) {
	$_POST['action'] = $_POST['action2'];
}

/**
 *	If section changes then do checks!
 */
if ( ($_POST['sectionId'] != @$_POST['sectionIdNew']) && $_POST['action']=="edit" ) {
	//reset masterId - we are putting it to root
	$_POST['masterSubnetId'] = 0;

    //check for overlapping
    if($section['strictMode']==1 && !$parent_is_folder) {
    	/* verify that no overlapping occurs if we are adding root subnet */
    	$overlap=$Subnets->verify_subnet_overlapping ($_POST['sectionIdNew'], $_POST['cidr'], $_POST['vrfId']);
    	if($overlap!==false) {
	    	$errors[] = $overlap;
	    }
    }
}
/**
 * Execute checks on add only and when root subnet is being added
 */
else if (($_POST['action']=="add") && ($_POST['masterSubnetId']==0)) {
    //verify cidr
    $cidr_check = $Subnets->verify_cidr_address($_POST['cidr']);
    if(strlen($cidr_check)>5) {
	    $errors[] = $cidr_check;
	}
    //check for overlapping
    if($section['strictMode']==1 && !$parent_is_folder) {
    	/* verify that no overlapping occurs if we are adding root subnet
	       only check for overlapping if vrf is empty or not exists!
    	*/
    	$overlap=$Subnets->verify_subnet_overlapping ($_POST['sectionId'], $_POST['cidr'], $_POST['vrfId']);
    	if($overlap!==false) {
	    	$errors[] = $overlap;
	    }
    }
}
/**
 * Execute different checks on add only and when subnet is nested
 */
else if ($_POST['action']=="add") {
    //verify cidr
    $cidr_check = $Subnets->verify_cidr_address($_POST['cidr']);
    if(strlen($cidr_check)>5) {
	    $errors[] = $cidr_check;
	}
    //disable checks for folders and if strict check enabled
    if($section['strictMode']==1 && !$parent_is_folder ) {

	    //verify that nested subnet is inside root subnet
	    if($_POST['masterSubnetId']!=0) {
	        if (!$Subnets->verify_subnet_nesting($_POST['masterSubnetId'], $_POST['cidr'])) {
	            $errors[] = _('Nested subnet not in root subnet!');
	        }
        }

	    //nested?
	    if($_POST['masterSubnetId']!= 0) {
	        $overlap = $Subnets->verify_nested_subnet_overlapping($_POST['sectionId'], $_POST['cidr'], $_POST['vrfId'], $_POST['masterSubnetId']);
			if($overlap!==false) {
	            $errors[] = $overlap;
	        }
	    }
	    //not nested
	    else {
	       $overlap = $Subnets->verify_subnet_overlapping($_POST['sectionId'], $_POST['cidr'], $_POST['vrfId']);
	    	if($overlap!==false) {
	            $errors[] = $overlap;
	        }
	    }
    }
}
/**
 * Check if slave is under master
 */
else if ($_POST['action']=="edit") {
    if($section['strictMode']==1 && !$parent_is_folder) {
    	/* verify that nested subnet is inside root subnet */
    	if($_POST['masterSubnetId'] != 0) {
	    	if (!$overlap = $Subnets->verify_subnet_nesting($_POST['masterSubnetId'], $_POST['cidr'])) {
	    		$errors[] = _('Nested subnet not in root subnet!');
	    	}
    	}
    }
    /* for nesting - MasterId cannot be the same as subnetId! */
    if ( $_POST['masterSubnetId']==$_POST['subnetId'] ) {
    	$errors[] = _('Subnet cannot nest behind itself!');
    }
}
else {}


//custom fields
if(sizeof($custom) > 0) {
	foreach($custom as $myField) {
		//booleans can be only 0 and 1!
		if($myField['type']=="tinyint(1)") {
			if($_POST[$myField['name']]>1) {
				$_POST[$myField['name']] = "";
			}
		}
		//not empty
		if($myField['Null']=="NO" && strlen($_POST[$myField['name']])==0) {
			$errors[] = "Field \"$myField[name]\" cannot be empty!";
		}
	}
}

# Set permissions if adding new subnet
if($_POST['action']=="add") {
	# root
	if($_POST['masterSubnetId']==0) {
		$_POST['permissions'] = $section['permissions'];
	}
	# nested - inherit parent permissions
	else {
		# get parent
		$parent = $Subnets->fetch_subnet(null, $_POST['masterSubnetId']);
		$_POST['permissions'] = $parent->permissions;
	}
}


/* If no errors are present execute request */
if (sizeof(@$errors)>0) {
    print '<div class="alert alert-danger"><strong>'._('Please fix following problems').'</strong>:';
    foreach ($errors as $error) { print "<br>".$error; }
    print '</div>';
    die();
}
/* delete confirmation */
elseif ($_POST['action']=="delete" && !isset($_POST['deleteconfirm'])) {
	# for ajax to prevent reload
	print "<div style='display:none'>alert alert-danger</div>";
	# result
	print "<div class='alert alert-warning'>";

	# print what will be deleted
	//fetch all slave subnets
	$Subnets->fetch_subnet_slaves_recursive ($_POST['subnetId']);
	$subcnt = sizeof($Subnets->slaves);
	foreach($Subnets->slaves as $s) {
		$slave_array[$s] = $s;
	}
	$ipcnt = $Addresses->count_addresses_in_multiple_subnets($slave_array);

	print "<strong>"._("Warning")."</strong>: "._("I will delete").":<ul>";
	print "	<li>$subcnt "._("subnets")."</li>";
	if($ipcnt>0) {
	print "	<li>$ipcnt "._("IP addresses")."</li>";
	}
	print "</ul>";

	print "<hr><div style='text-align:right'>";
	print _("Are you sure you want to delete above items?")." ";
	print "<div class='btn-group'>";
	print "	<a class='btn btn-sm btn-danger editSubnetSubmitDelete' id='editSubnetSubmitDelete'>"._("Confirm")."</a>";
	print "</div>";
	print "</div>";
	print "</div>";
}
/* execute */
else {

	# create array of default update values
	$values = array("id"=>@$_POST['subnetId'],
					"isFolder"=>0,
					"masterSubnetId"=>$_POST['masterSubnetId'],
					"subnet"=>$Subnets->transform_to_decimal($_POST['subnet']),
					"mask"=>$_POST['mask'],
					"description"=>@$_POST['description'],
					"vlanId"=>$_POST['vlanId'],
					"allowRequests"=>$Admin->verify_checkbox(@$_POST['allowRequests']),
					"showName"=>$Admin->verify_checkbox(@$_POST['showName']),
					"discoverSubnet"=>$Admin->verify_checkbox(@$_POST['discoverSubnet']),
					"pingSubnet"=>$Admin->verify_checkbox(@$_POST['pingSubnet'])
					);
	# for new subnets we add permissions
	if($_POST['action']=="add") {
		$values['permissions']=$_POST['permissions'];
		$values['sectionId']=$_POST['sectionId'];
	}
	else {
		# if section change
		if(@$_POST['sectionId'] != @$_POST['sectionIdNew']) {
			$values['sectionId']=$_POST['sectionIdNew'];
		}
		# if vrf change
		if(@$_POST['vrfId'] != @$_POST['vrfIdOld']) {
			$values['vrfId']=$_POST['vrfId'];
		}
	}
	# append custom fields
	$custom = $Tools->fetch_custom_fields('subnets');
	if(sizeof($custom) > 0) {
		foreach($custom as $myField) {

			//replace possible ___ back to spaces
			$myField['nameTest'] = str_replace(" ", "___", $myField['name']);
			if(isset($_POST[$myField['nameTest']])) { $_POST[$myField['name']] = $_POST[$myField['nameTest']];}

			//booleans can be only 0 and 1!
			if($myField['type']=="tinyint(1)") {
				if($_POST[$myField['name']]>1) {
					$_POST[$myField['name']] = 0;
				}
			}
			//not null!
			if($myField['Null']=="NO" && strlen($_POST[$myField['name']])==0) { $Result->show("danger", $myField['name'].'" can not be empty!', true); }

			# save to update array
			$values[$myField['name']] = $_POST[$myField['name']];
		}
	}

	# execute
	if(!$Admin->object_modify("subnets", $_POST['action'], "id", $values))	{ $Result->show("danger", _('Error editing subnet'), true); }
	else {
		# update also all slave subnets!
		if(isset($values['sectionId'])&&$_POST['action']!="add") {
			$Subnets->reset_subnet_slaves_recursive();
			$Subnets->fetch_subnet_slaves_recursive($_POST['subnetId']);
			$Subnets->remove_subnet_slaves_master($_POST['subnetId']);
			if(sizeof($Subnets->slaves)>0) {
				foreach($Subnets->slaves as $slaveId) {
					$Admin->object_modify ("subnets", "edit", "id", array("id"=>$slaveId, "sectionId"=>$_POST['sectionIdNew']));
				}
			}
		}

		# edit success
		if($_POST['action']=="delete")	{ $Result->show("success", _('Subnet, IP addresses and all belonging subnets deleted successfully').'!', false); }
		else							{ $Result->show("success", _("Subnet $_POST[action] successfull").'!', true); }

    	# send mail
		# sendObjectUpdateMails("subnet", $_POST['action'], $subnet_old_details, $_POST);
	}
}
?>