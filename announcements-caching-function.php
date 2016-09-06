<?php

/* This is a code snippet of a function designed to cache the most recently set
announcement flag state. You should either integrate it into your own script which
is calling announcements.php, or with amendments into the announcements.php script */

/* When you call the function, use the variables $station and $announcement_active
to set the parameters station and active passed to the announcements.php script */

function announcement($station,$announcement_active) {

/* Set this variables to the URL of the announcements.php script */

$announcement_url = "http://127.0.0.1/announcements.php";

/* Set this variable to a directory where this script can create, read and write
files which hold the cached state of the announcement flag */

$cache_directory = "announcements/";

	if (false == ($announcement_handle = fopen($cache_directory.$station,"c+"))) return;
	if (false == ($announcement_current = fgets($announcement_handle, 20))) $announcement_current="0";
	if (intval($announcement_current) <> intval($announcement_active)) { 
		$debug = "Announcement flag update to ".$announcement_active." for ".$station." ";
		$headers = (get_headers($announcement_url."?station=".$station."&active=".$announcement_active));
		/* This a hacky way of checking that the announcements.php script completed successfully */
		if (substr($headers[0],9,3)=="200") {
			$debug .= "OK";
		} else {
			$debug .= " failed with error code ".$headers[0];
		}
		fseek($announcement_handle,0);
		fwrite($announcement_handle,$announcement_active);
		} 
	fclose($announcement_handle); 

} 
	
?>
