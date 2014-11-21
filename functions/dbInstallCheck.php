<?php

/**
 * Check for fresh installation
 ****************************************************/
if(!tableExists("ipaddresses")) { 
	if(defined('BASE')) { header("Location: ".BASE.create_link("install")); }
	else 				{ header("Location: ".create_link("install"));} 
	die();
}
?>