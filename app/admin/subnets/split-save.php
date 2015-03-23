<?php

/*
 * Print resize subnet
 *********************/

/* functions */
require( dirname(__FILE__) . '/../../../functions/functions.php');

# initialize user object
$Database 	= new Database_PDO;
$User 		= new User ($Database);
$Admin	 	= new Admin ($Database);
$Subnets	= new Subnets ($Database);
$Addresses	= new Addresses ($Database);
$Result 	= new Result ();

# verify that user is logged in
$User->check_user_session();


# id must be numeric
if(!is_numeric($_POST['subnetId']))			{ $Result->show("danger", _("Invalid ID"), true); }

# get subnet details
$subnet_old = $Subnets->fetch_subnet (null, $_POST['subnetId']);

# verify that user has write permissions for subnet
$subnetPerm = $Subnets->check_permission ($User->user, $subnet_old->id);
if($subnetPerm < 3) 					{ $Result->show("danger", _('You do not have permissions to resize subnet').'!', true); }



# get new mask - how much we need to add to old mask?
switch($_POST['number']) {
	case "2":   $mask_diff = 1; break;
	case "4":   $mask_diff = 2; break;
	case "8":   $mask_diff = 3; break;
	case "16":  $mask_diff = 4; break;
	case "32":  $mask_diff = 5; break;
	case "64":  $mask_diff = 6; break;
	case "128": $mask_diff = 7; break;
	case "256": $mask_diff = 8; break;
	//otherwise die
	default:	$Result->show("danger", _("Invalid number of subnets"), true);
}
//set new mask
$mask = $subnet_old->mask + $mask_diff;
//set number of subnets
$number_of_subnets = pow(2,$mask_diff);
//set max hosts per new subnet
$max_hosts = $Subnets->get_max_hosts ($mask, $Subnets->identify_address($Subnets->transform_to_dotted($subnet_old->subnet)), false);


# create array of new subnets based on number of subnets (number)
for($m=0; $m<$number_of_subnets; $m++) {
	$newsubnets[$m] 		 = (array) $subnet_old;
	$newsubnets[$m]['id']    = $m;
	$newsubnets[$m]['mask']  = $mask;

	# if group is selected rewrite the masterSubnetId!
	if(@$_POST['group']=="yes") {
		$newsubnets[$m]['masterSubnetId'] = $subnet_old->id;
	}

	# recalculate subnet
	if($m>0) {
		$newsubnets[$m]['subnet'] = gmp_strval(gmp_add($newsubnets[$m-1]['subnet'], $max_hosts));
	}
}

# recalculate old hosts to put it to right subnet
$ipaddresses   = $Addresses->fetch_subnet_addresses ($subnet_old->id, "ip_addr", "asc");		# get all IP addresses
$subSize = sizeof($newsubnets);		# how many times to check
$n = 0;								# ip address count

foreach($ipaddresses as $ip) {
	//cast
	$ip = (array) $ip;
	# check to which it belongs
	for($m=0; $m<$subSize; $m++) {

		# check if between this and next - strict
		if($_POST['strict'] == "yes") {
			# check if last
			if(($m+1) == $subSize) {
				if($ip['ip_addr'] > $newsubnets[$m]['subnet']) {
					$ipaddresses[$n]->subnetId = $newsubnets[$m]['id'];
				}
			}
			elseif( ($ip['ip_addr'] > $newsubnets[$m]['subnet']) && ($ip['ip_addr'] < @$newsubnets[$m+1]['subnet']) ) {
				$ipaddresses[$n]->subnetId = $newsubnets[$m]['id'];
			}
		}
		# unstrict - permit network and broadcast
		else {
			# check if last
			if(($m+1) == $subSize) {
				if($ip['ip_addr'] >= $newsubnets[$m]['subnet']) {
					$ipaddresses[$n]->subnetId = $newsubnets[$m]['id'];
				}
			}
			elseif( ($ip['ip_addr'] >= $newsubnets[$m]['subnet']) && ($ip['ip_addr'] < $newsubnets[$m+1]['subnet']) ) {
				$ipaddresses[$n]->subnetId = $newsubnets[$m]['id'];
			}
		}
	}

	# if subnetId is still the same save to error
	if($ipaddresses[$n]->subnetId == $subnet_old->id) {
		$errors[] = $Subnets->transform_to_dotted($ip['ip_addr']);
	}

	# next IP address
	$n++;
}

# die if errors
if(isset($errors)) {
	print "<div class='alert alert-danger'>"._('Wrong IP addresses (subnet or broadcast)')."<ul>";
	foreach($errors as $error) {
		print "<li>$error</li>";
	}
	print "</ul></div>";
	die();
}


# check if new overlap (e.g. was added twice)
$nested_subnets = $Subnets->fetch_subnet_slaves ($subnet_old->id);
if($nested_subnets!==false) {
	//loop through all current slaves and check
	foreach($nested_subnets as $nested_subnet) {
		//check all new
		foreach($newsubnets as $new_subnet) {
			$new_subnet = (object) $new_subnet;
			if($Subnets->verify_overlapping ($Subnets->transform_to_dotted($new_subnet->subnet)."/".$new_subnet->mask, $Subnets->transform_to_dotted($nested_subnet->subnet)."/".$nested_subnet->mask)===true) {
				$Result->show("danger", _("Subnet overlapping - ").$Subnets->transform_to_dotted($new_subnet->subnet)."/".$new_subnet->mask." overlapps with ".$Subnets->transform_to_dotted($nested_subnet->subnet)."/".$nested_subnet->mask, true);
			}
		}
	}
}


# create new subnets and change subnetId for recalculated hosts
$m = 0;
foreach($newsubnets as $subnet) {
	//set new subnet insert values
	$values = array("description"=>strlen($subnet['description'])>0 ? $subnet['description']."_$m" : "split_subnet_$m",
					"subnet"=>$subnet['subnet'],
					"mask"=>$subnet['mask'],
					"sectionId"=>$subnet['sectionId'],
					"masterSubnetId"=>$subnet['masterSubnetId'],
					"vlanId"=>@$subnet['vlanId'],
					"vrfId"=>@$subnet['vrfId'],
					"allowRequests"=>@$subnet['allowRequests'],
					"showName"=>@$subnet['showName']
					);
	//create new subnet
	if(!$Admin->object_modify("subnets", "add", "id", $values))		{ $Result->show("danger", _("Failed to create subnet ").$Subnets->transform_to_dotted($subnet['subnet'])."/".$subnet['mask'], true); }

	//get all address ids
	unset($ids);
	foreach($ipaddresses as $ip) {
		if($ip->subnetId == $m) {
			$ids[] = $ip->id;
		}
	}

	//replace all subnetIds in IP addresses to new subnet
	if(isset($ids)) {
		if(!$Admin->object_modify("ipaddresses", "edit-multiple", $ids, array("subnetId"=>$Admin->lastId)))	{ $Result->show("danger", _("Failed to move IP address"), true); }
	}

	# next
	$m++;
}

# do we need to remove old subnet?
if(@$_POST['group']!="yes") {
	if(!$Admin->object_modify("subnets", "delete", "id", array("id"=>$subnet_old->id)))						{ $Result->show("danger", _("Failed to remove old subnet"), true); }
}

# all good
$Result->show("success", _("Subnet splitted ok")."!", true);

?>