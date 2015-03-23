<?php

/**
 * Tools menu items
 *
 */

# icons
$tools_menu_icons['Tools'] 		= "fa-wrench";
$tools_menu_icons['Subnets'] 	= "fa-sitemap";
$tools_menu_icons['User Menu'] 	= "fa-user";

# Tools
$tools_menu['Tools'][] = array("icon"=>"fa-search", 		"name"=>"Search", 		 		"href"=>"search", 		"description"=>"Search database Addresses, subnets and VLANs");
$tools_menu['Tools'][] = array("icon"=>"fa-calculator",		"name"=>"IP calculator", 		"href"=>"ip-calculator","description"=>"IPv4v6 calculator for subnet calculations");
$tools_menu['Tools'][] = array("icon"=>"fa-clock-o", 		"name"=>"Changelog", 	 		"href"=>"changelog", 	"description"=>"Show changelog for all network objects");
if($User->settings->enableChangelog == 1)
$tools_menu['Tools'][] = array("icon"=>"fa-info", 	  		"name"=>"Instructions",  		"href"=>"instructions", "description"=>"Instructions for managing IP addresses");
$tools_menu['Tools'][] = array("icon"=>"fa-list", 			"name"=>"Log files", 			"href"=>"logs",		 	"description"=>"Browse phpipam log files");


# Subnets
$tools_menu['Subnets'][] 	= array("icon"=>"fa-star", 	  	"name"=>"Favourite networks",  	"href"=>"favourites", 	"description"=>"Show favourite networks");
$tools_menu['Subnets'][] 	= array("icon"=>"fa-sitemap", 	"name"=>"Subnets",  		   	"href"=>"subnets", 		"description"=>"Show all subnets");
$tools_menu['Subnets'][] 	= array("icon"=>"fa-cloud", 	"name"=>"VLAN",  				"href"=>"vlan", 		"description"=>"Show VLANs and belonging subnets");
if($User->settings->enableVRF == 1)
$tools_menu['Subnets'][] 	= array("icon"=>"fa-cloud", 	 "name"=>"VRF",  				"href"=>"vrf", 			"description"=>"Show VRFs and belonging networks");
$tools_menu['Subnets'][] 	= array("icon"=>"fa-desktop", 	 "name"=>"Devices",  			"href"=>"devices", 		"description"=>"Show all configured devices");

# user menu
$tools_menu['User Menu'][] = array("icon"=>"fa-user", 		"name"=>"My account",  			"href"=>"user-menu", 	"description"=>"Manage your account");

?>