<?php

/* Change the variable $announcement_command_suffix to reflect the value used in your odr-dabmux configuation */
$announcement_command_suffix = "_traffic_announcement";
/* Set $announcement_directory to a directory where state files can be written */
$announcement_directory = "announcements/";
/* Set service port to the port that odr-dabmux remote control is running on, usually 12721 */
$service_port = "12721";
/* Set address to the IP address of odr-dabmux. As odr-dabmux only accepts local connections, this will always be 127.0.0.1 */
$address = "127.0.0.1";

openlog("ODR-DabMux-Announcements",LOG_PID,LOG_SYSLOG);

$active = ($_GET["active"]);
$station = ($_GET["station"]);
$delay = ($_GET["delay"]);
$http_output = "Unknown fatal error. Check error logs";
$log_output = "";
$http_code = 500;
$action = "active 0";
$change = false;

/* Allow the script to run beyond the socket close */
ignore_user_abort(true);

if ($active == false) $active = "0";
if ($delay == false) $delay = 0;
if ($station==false) {
	$http_output = "No station defined";
	$http_code = 400;
} else {
	$announcement_handle = fopen($announcement_directory.$station,"c+");
	$announcement_current = fgets($announcement_handle);
	fclose($announcement_handle);
	if (intval($announcement_current) <> intval($active)) { 
		$http_output = "Attempting to set ".$station.$announcement_command_suffix." active " .$active." in ".$delay." seconds";
		$http_code = 200;
		$change = true;
	} else {
		$http_output = "Not changing ".$station.": current state ".intval($announcement_current)." is same as requested state ".$active;
		$http_code = 200;
	}
	syslog(LOG_INFO,$http_output);

}

http_response_code($http_code);
?>
<html>
<head>
<title>Announcement Flag Controller</title>
<body>
<h2>Announcement Flag Controller</h2>
<?php echo $http_output . "<\br> \n";


if ($change ==true) {

	if ($active == false) {
		$lock_handle = fopen($announcement_directory.$station."_ta_pending_inactive","c");
		fclose($lock_handle);
	        syslog(LOG_INFO,"Created ".$station."_ta_pending_inactive file");

		sleep(2);
	        syslog(LOG_INFO,"Checking ".$station."_ta_pending_inactive file");
		if (!file_exists($announcement_directory.$station."_ta_pending_inactive")) {
		        syslog(LOG_INFO,"Couldn't find ".$station."_ta_pending_inactive file");

		    	$log_output = ("Announcements :" .$station." announcement set active 0 aborted as lockfile ".$station."_ta_pending_inactive removed");
		    	syslog(LOG_ERR,$log_output);
		    	exit($log_output);
		} 
	}

    syslog(LOG_INFO,"Deleting ".$station."_ta_pending_inactive file");
	unlink($announcement_directory.$station."_ta_pending_inactive"); 

	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	if ($socket == false) {
	    $log_output = "Announcements :" .$station." socket_create() failed: Reason: " . socket_strerror(socket_last_error());
	    syslog(LOG_ERR,$log_output);
	    exit($log_output);
	} 

	$result = socket_connect($socket, $address, $service_port);
	if ($result == false) {
	    $log_output = "Announcements: " .$station. " socket_connect() failed. Reason: " . socket_strerror(socket_last_error($socket));
	    syslog(LOG_ERR,$log_output);
	    exit($log_output);
	}

	if (read_response($socket)) break;

	if ($delay > 0) {
		if ($active == 1) {
			$action = "start_in ";
		} else {
			$action = "stop_in ";
		}
		$action .= $delay;
	} else {
		$action = "active " . $active;
	}

	send_command($socket,"set ".$station.$announcement_command_suffix." ".$action);
	if (read_response($socket)) break;

	send_command($socket,"quit");
	if (read_response($socket)) break;

	$log_output = "Announcements: " . $station . " sent announcement command " .$action;
    syslog(LOG_INFO,$log_output);
	socket_close($socket);

	$announcement_handle = fopen($announcement_directory.$station,"c+");
	fseek($announcement_handle,0);
	fwrite($announcement_handle,$active);

}



echo $log_output . "<\br> \n";

?>
</body></html>
<?php

function read_response($socket) {
    	$out = "";
	$start = time();
	$timed_out = false;
    	while (substr($out,-2) !== "> ") {
		$timed_out = ((time()-$start)>5);
		if (false == (socket_recv($socket, $out, 2048, false))) break;
	}
return $timed_out;
}

function send_command($socket,$in) {
     	socket_write($socket, $in."\n", strlen($in)+1);
return;
}
	
