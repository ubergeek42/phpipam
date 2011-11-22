<?php

/**
 *
 * Script to calculate IP subnetting
 *
 */

/* include required scripts */
require_once('../../functions/functions.php');

/* check referer and requested with */
CheckReferrer();

/* get requested IP addresses */
$cidr = $_POST['cidr'];

/* verify input */
$errors = verifyCidr ($cidr,0);

if (sizeof($errors) != 0) {
    die('<div class="error">Invalid input: '.  $errors[0] .'</div>');
}

/* calculate results */
$ipCalcResults = calculateIpCalcResult ($cidr);

?>

<!-- IPcalc result table -->
<div class="normalTable">
<table class="normalTable ipCalcResult">

    <!-- title -->
    <tr class="th">
        <th colspan="2">Subnetting details for <?php print $cidr; ?></th>
    </tr>
    
    <!-- IP details -->
    <?php
    $m = 0;		//needed for add subnet mapping
    foreach ($ipCalcResults as $key=>$line) 
    {
        print '<tr>';
        
        print '<td>'. $key .'</td>';
        print '<td id="sub'. $m .'">'. $line .'</td>';
        
        print '</tr>';
        
        $m++;
    }
    
    ?>
    
    <!-- add subnet button -->
    <tr style="border-top: 1px solid white">
    	<td></td>
    	<td style="padding-top:10px">
    		<img src="css/images/add.png" class="createSubnetFromCalc"> Create subnet from result
    	</td>
    </tr>
    
    <!-- select section -->
	<tr id="selectSection" style="display:none">
		<td style="text-align:right">Select Section:</td>
		<td>
		
		<select name="selectSectionfromIPCalc" id="selectSectionfromIPCalc">
			<option value="">Please select:</option>
		<?php
			//get all sections
			$sections = fetchSections ();
			
			foreach($sections as $section) {
				print '<option value="'. $section['id'] .'">'. $section['name'] .'</option>';
			}
		?>
		</select>
		
		</td>
	</tr>

</table>
</div>