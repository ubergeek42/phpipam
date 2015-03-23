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
$Sections	= new Sections ($Database);
$Subnets	= new Subnets ($Database);
$Addresses	= new Addresses ($Database);
$Result 	= new Result ();

# verify that user is logged in
$User->check_user_session();


# ID must be numeric
if(!is_numeric($_POST['subnetId']))									{ $Result->show("danger", _("Invalid ID"), true); }
# verify that user has write permissions for subnet
if($Subnets->check_permission ($User->user, $_POST['subnetId'])<3)	{ $Result->show("danger", _('You do not have permissions to resize subnet').'!', true); }
# new mask must be > 8
if($_POST['newMask'] < 8) 											{ $Result->show("danger", _('New mask must be at least /8').'!', true); }


# fetch old subnet details and set new
$subnet_old = (array) $Subnets->fetch_subnet (null, $_POST['subnetId']);
$subnet_new = @$subnet_old;
$subnet_new['mask'] = @$_POST['newMask'];

# fetch section details
$section = $Sections->fetch_section (null, $subnet_old['sectionId']);


/*
 * now we need to check each host against new subnet
 */

# fetch all addresses
$subnet_addresses = $Addresses->fetch_subnet_addresses ($_POST['subnetId'], "ip_addr", "asc");

#verify new address
$verify = $Subnets->verify_cidr_address($Subnets->transform_to_dotted($subnet_new['subnet'])."/".$subnet_new['mask']);
if($verify!==true) 													{ $Result->show("danger", $verify, true); }

# same mask - ignore
if($subnet_new['mask']==$subnet_old['mask']) 						{ $Result->show("warning", _("New network is same as old network"), true); }
# if we are expanding network get new network address!
elseif($subnet_new['mask']<$subnet_old['mask']) {
	//new subnet
	$new_boundaries = $Subnets->get_network_boundaries ($Subnets->transform_to_dotted($subnet_new['subnet']), $subnet_new['mask']);
	$subnet_new['subnet'] = $Subnets->transform_to_decimal($new_boundaries['network']);

	//Checks for strict mode
	if ($section->strictMode==1) {
		//if it has parent make sure it is still within boundaries
		if((int) $subnet_new['masterSubnetId']>0) {
			//if parent is folder check for other in same folder
			$parent_subnet = $Subnets->fetch_subnet(null, $subnet_new['masterSubnetId']);
			if($parent_subnet->isFolder!=1) {
				//check that new is inside its master subnet
				if(!$Subnets->verify_subnet_nesting ($parent_subnet->id, $Subnets->transform_to_dotted($subnet_new['subnet'])."/".$subnet_new['mask'])) {
					$Result->show("danger", _("New subnet not in master subnet")."!", true);
				}
			}
			//folder
			else {
				//fetch all folder subnets, remove old subnet and verify overlapping!
				$folder_subnets = $Subnets->fetch_subnet_slaves ($parent_subnet->id);
				//check
				if(sizeof(@$folder_subnets)>0) {
					foreach($folder_subnets as $fs) {
						//dont check against old
						if($fs->id!=$subnet_old['id']) {
							//verify that all nested are inside its parent
							if($Subnets->verify_overlapping ( $Subnets->transform_to_dotted($subnet_new['subnet'])."/".$subnet_new['mask'], $Subnets->transform_to_dotted($fs->subnet)."/".$fs->mask)) {
								$Result->show("danger", _("Subnet overlapps with")." ".$Subnets->transform_to_dotted($fs->subnet)."/".$fs->mask, true);
							}
						}
					}
				}
			}
		}
		//root subnet, check overlapping !
		else {
			$section_subnets = $Subnets->fetch_section_subnets ($section->id);
			$overlap = $Subnets->verify_subnet_resize_overlapping ($section->id, $Subnets->transform_to_dotted($subnet_new['subnet'])."/".$subnet_new['mask'], $subnet_old['id'], $subnet_new['vrfId']);
			if($overlap!==false) {
				$Result->show("danger", $overlap, true);
			}
		}
	}
}
# we are shrinking subnet
else {
	//check all IP addresses against new subnet
	foreach($subnet_addresses as $ip) {
		$Addresses->verify_address( $Subnets->transform_to_dotted($ip->ip_addr), $Subnets->transform_to_dotted($subnet_new['subnet'])."/".$subnet_new['mask'], false, true );
	}
	//Checks for strict mode
	if ($section->strictMode==1) {
		//if it has slaves make sure they are still inside network
		if($Subnets->has_slaves($subnet_new['id'])) {
			//fetch slaves
			$nested = $Subnets->fetch_subnet_slaves ($subnet_new['id']);
			foreach($nested as $nested_subnet) {
				//if masks and subnets match they are same, error!
				if($nested_subnet->subnet==$subnet_new['subnet'] && $nested_subnet->mask==$subnet_new['mask']) {
					$Result->show("danger", _("Subnet it same as ").$Subnets->transform_to_dotted($nested_subnet->subnet)."/$nested_subnet->mask - $nested_subnet->description)", true);
				}
				//verify that all nested are inside its parent
				if(!$Subnets->is_subnet_inside_subnet ( $Subnets->transform_to_dotted($nested_subnet->subnet)."/".$nested_subnet->mask, $Subnets->transform_to_dotted($subnet_new['subnet'])."/".$subnet_new['mask'])) {
					$Result->show("danger", _("Nested subnet out of new subnet")."!<br>(".$Subnets->transform_to_dotted($nested_subnet->subnet)."/$nested_subnet->mask - $nested_subnet->description)", true);
				}
			}
		}
	}
}


# set update values
$values = array("id"=>$_POST['subnetId'],
				"mask"=>$subnet_new['mask']
				);
if(!$Admin->object_modify("subnets", "edit", "id", $values))	{ $Result->show("danger",  _("Error resizing subnet")."!", true); }
else															{ $Result->show("success", _("Subnet resized successfully")."!", true); }

?>