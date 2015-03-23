<script type="text/javascript">
$(document).ready(function() {
	if ($("[rel=tooltip]").length) { $("[rel=tooltip]").tooltip(); }

	return false;
});
</script>


<?php
# required functions
if(!is_object($User)) {
	require( dirname(__FILE__) . '/../../../functions/functions.php' );
	# classes
	$Database	= new Database_PDO;
	$User 		= new User ($Database);
	$Tools 		= new Tools ($Database);
	$Subnets 	= new Subnets ($Database);
}

# user must be authenticated
$User->check_user_session ();

# if direct request that redirect to tools page
if($_SERVER['HTTP_X_REQUESTED_WITH']!="XMLHttpRequest")	{
	header("Location: ".create_link("tools","favourites"));
}

# fetch favourite subnets with details
$fsubnets = $User->fetch_favourite_subnets ();

# print if none
if(!$fsubnets) {
	print "<blockquote style='margin-top:20px;margin-left:20px;'>";
	print "<p>"._("No favourite subnets selected")."</p><br>";
	print "<small>"._("You can add subnets to favourites by clicking star icon in subnet details")."!</small><br>";
	print "</blockquote>";
}
else {
	print "<table class='table table-condensed table-hover table-top favs'>";

	# headers
	print "<tr>";
	print "	<th>"._('Object')."</th>";
	print "	<th>"._('Description')."</th>";
	print "	<th>"._('Section')."</th>";
	print "	<th>"._('VLAN')."</th>";
	print "	<th style='width:5px;'></th>";
	print "</tr>";

	# subnets
	foreach($fsubnets as $f) {

		# must be either subnet or folder
		if(sizeof($f)>0) {

			print "<tr class='favSubnet-$f[subnetId]'>";

			if($f['isFolder']==1) {
				print "	<td><a href='".create_link("folder",$f['sectionId'],$f['subnetId'])."'><i class='fa fa-sfolder fa-folder'></i> $f[description]</a></td>";
			}
			else {
				# leaf?
				if(!$Subnets->fetch_subnet_slaves ($f['subnetId'])) {
					print "	<td><a href='".create_link("subnets",$f['sectionId'],$f['subnetId'])."'><i class='fa fa-sfolder fa-sitemap' ></i> ".$Subnets->transform_address($f['subnet'])."/$f[mask]</a></td>";
				}
				else {
					print "	<td><a href='".create_link("subnets",$f['sectionId'],$f['subnetId'])."'><i class='fa fa-sfolder fa-folder-o'></i> ".$Subnets->transform_address($f['subnet'])."/$f[mask]</a></td>";
				}
			}
			print "	<td>$f[description]</td>";
			print "	<td><a href='".create_link("subnets",$f['sectionId'])."'>$f[section]</a></td>";

			# get vlan info
			if(strlen($f['vlanId'])>0 && $f['vlanId']!=0) {
				$vlan = $Tools->fetch_object("vlans", "vlanId", $f['vlanId']);
				print "	<td>$vlan->number</td>";
			} else {
				print "	<td>/</td>";
			}

			# remove from favourites
			print "	<td><a class='btn btn-xs btn-default editFavourite favourite-$f[subnetId]' data-subnetId='$f[subnetId]' data-action='remove' data-from='widget' rel='tooltip' data-placement='left' title='"._('Click to remove from favourites')."'><i class='fa fa-star'></i></a></td>";
			print "</tr>";
		}
	}

	print "</table>";
}
?>