<?php

/**
 * Script to display favourite networks
 */

# verify that user is logged in
$User->check_user_session();

# fetch favourite subnets
$favourite_subnets = $User->fetch_favourite_subnets();

# title
print "<h4>"._('Favourite subnets')."</h4>";
print "<hr>";

# print if none
if(sizeof($favourite_subnets) == 0 || !isset($favourite_subnets[0])) {
	print "<blockquote style='margin-top:20px;margin-left:20px;'>";
	print "<p>"._("No favourite subnets selected")."</p><br>";
	print "<small>"._("You can add subnets to favourites by clicking star icon in subnet details")."!</small><br>";
	print "</blockquote>";
}
else {
	print "<table class='table table-condensed table-striped table-hover table-top favs'>";

	# headers
	print "<tr>";
	print "	<th>"._('Object')."</th>";
	print "	<th>"._('Description')."</th>";
	print "	<th>"._('Section')."</th>";
	print "	<th class='hidden-xs hidden-sm'>"._('VLAN')."</th>";
	print "	<th class='hidden-xs hidden-sm'>"._('Used')."</th>";
	print "	<th style='width:5px;'></th>";
	print "</tr>";

	# logs
	foreach($favourite_subnets as $f) {
		# if subnet already removed (doesnt exist) dont print it!
		if(sizeof($f)>0) {
			print "<tr class='favSubnet-$f[subnetId]'>";

			if($f['isFolder']==1) {
				print "	<td><a href='".create_link("folder",$f['sectionId'],$f['subnetId'])."'><i class='fa fa-folder fa-sfolder'></i> $f[description]</a></td>";
			}
			else {
				//master?
				if($Subnets->has_slaves ($f['subnetId'])) { $master = true;	print "	<td><a href='".create_link("subnets",$f['sectionId'],$f['subnetId'])."'><i class='fa fa-sfolder fa-folder-o'></i>".$Subnets->transform_to_dotted($f['subnet'])."/$f[mask]</a></td>"; }
				else 									  { $master = false;print "	<td><a href='".create_link("subnets",$f['sectionId'],$f['subnetId'])."'><i class='fa fa-sfolder fa-sitemap' ></i>".$Subnets->transform_to_dotted($f['subnet'])."/$f[mask]</a></td>"; }
			}

			print "	<td>$f[description]</td>";
			print "	<td><a href='".create_link("folder",$f['sectionId'])."'>$f[section]</a></td>";

			# vlan
			$vlan = $Tools->fetch_object("vlans", "vlanId", $f['vlanId']);
			$vlan = $vlan===false ? "" : $vlan->number;
			print "	<td class='hidden-xs hidden-sm'>$vlan</td>";

			# usage
			if($f['isFolder']==1) {
				print  '<td class="hidden-xs hidden-sm"></td>';
			}
			elseif(!$master) {
	    		$address_count = $Addresses->count_subnet_addresses ($f['subnetId']);
	    		$subnet_usage = $subnet_usage  = $Subnets->calculate_subnet_usage (gmp_strval($address_count), $f['mask'], $f['subnet']);

	    		print ' <td class="used hidden-xs hidden-sm">'.$Subnets->reformat_number($subnet_usage['used']) .'/'. $Subnets->reformat_number($subnet_usage['maxhosts']) .' ('.$Subnets->reformat_number($subnet_usage['freehosts_percent']) .' %)</td>';
	    	}
	    	else {
				print '<td class="hidden-xs hidden-sm"></td>'. "\n";
			}

			# remove
			print "	<td><a class='btn btn-xs btn-default editFavourite' data-subnetId='$f[subnetId]' data-action='remove' data-from='widget'><i class='fa fa-star favourite-$f[subnetId]' rel='tooltip' title='"._('Click to remove from favourites')."'></i></a></td>";

			print "</tr>";
		}
	}

	print "</table>";
}
?>