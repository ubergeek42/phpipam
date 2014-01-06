<?php

/**
 * Script to print add / edit / delete widget
 *************************************************/

/* required functions */
require_once('../../functions/functions.php'); 

/* verify that user is admin */
checkAdmin();

/* get lang details */
$w = getwidgetById ($_POST['wid']);
?>


<!-- header -->
<div class="pHeader">
<?php
/**
 * If action is not set get it form post variable!
 */
if($_POST['action'] == "edit")  		{ print _('Edit widget'); }
elseif($_POST['action'] == "delete") 	{ print _('Delete widget'); }
else 									{ print _('Add new widget'); }
?>
</div>

<!-- content -->
<div class="pContent">

	<form id="widgetEdit" name="widgetEdit">
	<table class="table table-noborder table-condensed">

	<!-- name -->
	<tr>
	    <td><?php print _('Title'); ?></td> 
	    <td><input class="input-xlarge" type="text" name="wtitle" value="<?php print @$w['wtitle']; ?>" <?php if($_POST['action'] == "delete") print "readonly"; ?>></td>
    </tr>

    <!-- description -->
    <tr>
    	<td><?php print _('Description'); ?></td> 
    	<td>
    		<input class="input-xlarge" type="text" name="wdescription" value="<?php print @$w['wdescription']; ?>" <?php if($_POST['action'] == "delete") print "readonly"; ?>>

    		<input type="hidden" name="wid" value="<?php print $_POST['wid']; ?>">
    		<input type="hidden" name="action" value="<?php print $_POST['action']; ?>">
    	</td>   
    </tr>
   
	<!-- File -->
	<tr>
	    <td><?php print _('File'); ?></td> 
	    <td><input class="input-xlarge" type="text" name="wfile" value="<?php print @$w['wfile']; ?>.php" <?php if($_POST['action'] == "delete") print "readonly"; ?>></td>
    </tr>

	<!-- Admin -->
	<tr>
	    <td><?php print _('Admin only'); ?></td> 
	    <td>
	    	<select name="wadminonly" class="input-small">
	    		<option value="no"  <?php if(@$w['wadminonly']=='no')  print "selected='selected'"; ?>><?php print _('No'); ?></option>
	    		<option value="yes" <?php if(@$w['wadminonly']=='yes') print "selected='selected'"; ?>><?php print _('Yes'); ?></option>

	    	</select>
	    </td>
    </tr>

	<!-- Active -->
	<tr>
	    <td><?php print _('Active'); ?></td> 
	    <td>
	    	<select name="wactive" class="input-small">
	    		<option value="no"  <?php if(@$w['wactive']=='no')  print "selected='selected'"; ?>><?php print _('No'); ?></option>
	    		<option value="yes" <?php if(@$w['wactive']=='yes') print "selected='selected'"; ?>><?php print _('Yes'); ?></option>

	    	</select>
	    </td>
    </tr>

	<!-- Link to file -->
	<tr>
	    <td><?php print _('Link to page'); ?></td> 
	    <td>
	    	<select name="whref" class="input-small">
	    		<option value="no"  <?php if(@$w['whref']=='no')  print "selected='selected'"; ?>><?php print _('No'); ?></option>
	    		<option value="yes" <?php if(@$w['whref']=='yes') print "selected='selected'"; ?>><?php print _('Yes'); ?></option>

	    	</select>
	    </td>
    </tr>  
  
</table>
</form>

</div>




<!-- footer -->
<div class="pFooter">
	<div class="btn-group">
		<button class="btn btn-small hidePopups"><?php print _('Cancel'); ?></button>
		<button class="btn btn-small <?php if($_POST['action']=="delete") { print "btn-danger"; } else { print "btn-success"; } ?>" id="widgetEditSubmit"><i class="icon-white <?php if($_POST['action']=="add") { print "icon-plus"; } else if ($_POST['action']=="delete") { print "icon-trash"; } else { print "icon-ok"; } ?>"></i> <?php print ucwords(_($_POST['action'])); ?></button>
	</div>

	<!-- Result -->
	<div class="widgetEditResult"></div>
</div>