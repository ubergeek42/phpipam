<?php

/**
 * Check for fresh installation
 ****************************************************/
if(!tableExists("widgets")) { 
	if(defined('BASE')) { header("Location: ".BASE.create_link("install", null,null,null,null,true)); }
	else 				{ header("Location: ".create_link("install",null,null,null,null,true));} 
	die();
}
?>