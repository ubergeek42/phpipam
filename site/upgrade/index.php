<?php

# make upgrade and php build checks
include('functions/dbUpgradeCheck.php'); 	# check if database needs upgrade 
include('functions/checkPhpBuild.php');		# check for support for PHP modules and database connection 

# verify that user is logged in
isUserAuthenticatedNoAjax(); 
?>

<!DOCTYPE HTML>
<html lang="en">

<head>
	<base href="<?php print $url; ?>" />

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="Cache-Control" content="no-cache, must-revalidate">
	
	<meta name="Description" content=""> 
	<meta name="title" content="<?php print $settings['siteTitle']; ?>"> 
	<meta name="robots" content="noindex, nofollow"> 
	<meta http-equiv="X-UA-Compatible" content="IE=9" >
	
	<meta name="viewport" content="width=device-width, initial-scale=0.7, maximum-scale=1, user-scalable=no">
	
	<!-- chrome frame support -->
	<meta http-equiv="X-UA-Compatible" content="chrome=1">
  
	<!-- title -->
	<title><?php print $settings['siteTitle']; ?></title>
	
	<!-- css -->
	<link rel="stylesheet" type="text/css" href="css/bootstrap/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap/bootstrap-custom.css">
	<link rel="stylesheet" type="text/css" href="css/font-awesome/font-awesome.min.css">
	<link rel="shortcut icon" href="css/images/favicon.ico">
		
	<!-- js -->
	<script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="js/jclock.jquery.js"></script>
	<script type="text/javascript" src="js/login.js"></script>
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
	<script type="text/javascript">
	$(document).ready(function(){
	     if ($("[rel=tooltip]").length) { $("[rel=tooltip]").tooltip(); }
	});
	</script>
	<!--[if lt IE 9]>
	<script type="text/javascript" src="js/dieIE.js"></script>
	<![endif]-->
</head>

<!-- body -->
<body>

<!-- wrapper -->
<div class="wrapper">

<!-- jQuery error -->
<div class="jqueryError">
	<div class='alert alert-danger' style="width:400px;margin:auto">jQuery error!</div>
	<div class="jqueryErrorText"></div><br>
	<a href="<?php print create_link(null); ?>" class="btn btn-sm btn-default" id="hideError" style="margin-top:0px;">Hide</a>
</div>

<!-- Popups -->
<div id="popupOverlay"></div>
<div id="popup" class="popup popup_w400"></div>
<div id="popup" class="popup popup_w500"></div>
<div id="popup" class="popup popup_w700"></div>

<!-- loader -->
<div class="loading"><?php print _('Loading');?>...<br><i class="fa fa-spinner fa-spin"></i></div>

<!-- header -->
<div class="row header-install" id="header">
	<div class="col-xs-12">
		<div class="hero-unit" style="padding:20px;margin-bottom:10px;">
			<a href="<?php print create_link(null); ?>"><?php print $settings['siteTitle']." | "._('login');?></a>
		</div>
	</div>		
</div>  

<!-- content -->
<div class="content_overlay">
<div class="container-fluid" id="mainContainer">


	<div class="content upgrade-db" style="width:600px;margin:auto;background:white;padding:10px;margin-top:40px;border-radius:6px;border:1px solid #eee;">
	<?php
	
	/**
	 * Check if database needs upgrade to newer version
	 ****************************************************/
	
	
	/**
	 * checks
	 *
	 *	$settings['version'] = installed version (from database)
	 *	VERSION 			 = file version
	 *	LAST_POSSIBLE		 = last possible for upgrade
	 */
	
	
	// not logged in users
	if (isUserAuthenticatedNoAjax()) {
		header("Location: ".create_link("login"));	
	}
	// logged in, but not admins
	elseif (!checkAdmin(false)) {
		//version ok
		if ($settings['version'] == VERSION) {
			header("Location: ".create_link("login"));
		} 
		//upgrade needed
		else {
			print '<h4>phpipam upgrade required</h4><hr>';
			print '<div class="alert alert-danger">Database needs upgrade. Please contact site administrator (<a href="mailto:'. $settings['siteAdminMail'] .'">'. $settings['siteAdminName'] .'</a>)!</div>';
		}
	}
	// admins
	elseif(checkAdmin(false)) {
		//version ok
		if ($settings['version'] == VERSION) {
			print "<h4>Database upgrade check</h4><hr>";
			print "<div class='alert alert-success'>Database seems up to date and doesn't need to be upgraded!</div>";
			print '<a href="'.create_link(null).'"><button class="btn btn-sm btn-default">Go to dashboard</button></a>';		
		}
		//version too old
		elseif ($settings['version'] < LAST_POSSIBLE) {
			die("<div class='alert alert-danger'>Your phpIPAM version is too old to be upgraded, at least version ".LAST_POSSIBLE." is required for upgrade.</div>");
		}
		//upgrade needed
		elseif ($settings['version'] < VERSION) {
			//upgrade html + script
			include('upgradePrint.php');
		}
		//upgrade not needed
		else {
			header("Location: ".create_link("login"));		
		}
	}
	//default, smth is wrong
	else {
		header("Location: ".create_link("login"));		
	}
	
	?>
	</div>

</div>
</div>

<!-- Base for IE -->
<div class="iebase hidden"><?php print BASE; ?></div>

<!-- pusher -->
<div class="pusher"></div>

<!-- end wrapper -->
</div>

<!-- weather prettyLinks are user, for JS! -->
<div id="prettyLinks" style="display:none"><?php print $settings['prettyLinks']; ?></div>

<!-- Page footer -->
<div class="footer"><?php include('site/footer.php'); ?></div>

<!-- export div -->
<div class="exportDIV"></div>

<!-- end body -->
</body>
</html>
<?php ob_end_flush(); ?>