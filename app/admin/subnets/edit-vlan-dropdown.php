<?php

/*
 * Print select vlan in subnets
 *******************************/

/* required functions */
if(!is_object($User)) {
	/* functions */
	require( dirname(__FILE__) . '/../../../functions/functions.php');

	# initialize user object
	$Database 	= new Database_PDO;
	$User 		= new User ($Database);
	$Tools	 	= new Tools ($Database);
	$Result 	= new Result ();

	# fetch vlans
	$vlans = $Tools->fetch_object("vlans", "number");
}

# verify that user is logged in
$User->check_user_session();
?>

<select name="vlanId" class="form-control input-sm input-w-auto">
	<option disabled="disabled"><?php print _('Select VLAN'); ?>:</option>
	<?php
	if($_POST['action'] == "add") { $vlan['vlanId'] = 0; }

	$tmp[0]['vlanId'] = 0;
	$tmp[0]['number'] = _('No VLAN');

	# on-the-fly
	$tmp[1]['vlanId'] = 'Add';
	$tmp[1]['number'] = _('+ Add new VLAN');

	array_unshift($vlans, $tmp[0]);
	array_unshift($vlans, $tmp[1]);

	foreach($vlans as $vlan) {
		//cast
		$vlan = (array) $vlan;
		/* set structure */
		$printVLAN = $vlan['number'];

		if(!empty($vlan['name'])) { $printVLAN .= " ($vlan[name])"; }

		/* selected? */
		if(@$subnetDataOld['vlanId']==$vlan['vlanId']) 	{ print '<option value="'. $vlan['vlanId'] .'" selected>'. $printVLAN .'</option>'. "\n"; }
		elseif(@$_POST['vlanId'] == $vlan['vlanId']) 	{ print '<option value="'. $vlan['vlanId'] .'" selected>'. $printVLAN .'</option>'. "\n"; }
		else 											{ print '<option value="'. $vlan['vlanId'] .'">'. $printVLAN .'</option>'. "\n"; }
	}
	?>
</select>
