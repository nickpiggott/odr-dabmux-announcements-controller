<?php

/* There should be a README with this file, to explain how to configure dab-odrmux to signal announcements and how to call this script */

/* Change the variable $announcement_command_suffix to reflect the value used in your odr-dabmux configuation */
$announcement_command_suffix = "_traffic_announcement";

$active = ($_GET["active"]);
$station = ($_GET["station"]);
$http_output = "";
$http_code = 500;

if ($active == false) $active = "0";
if ($station==false) {
	$http_output .= "No station defined in URL.";
	$http_code = 400;
} else {
	$service_port = "12721";
	$address = "127.0.0.1";

	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	$http_output .= "Attempting to create socket : ";
	if ($socket === false) {
	    $http_output .= "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "<br />\n";
	} else {
	    $http_output .= "OK.<br />\n";
	}

	$http_output .= "Attempting to connect to '$address' on port '$service_port' : ";
	$result = socket_connect($socket, $address, $service_port);
	if ($result === false) {
	    $http_output .= "socket_connect() failed.<br />Reason: ($result) " . socket_strerror(socket_last_error($socket)) . "<br />\n";
	} else {
	    $http_output .= "OK.<br />\n";
	}

	if (read_response($socket)) break;

	send_command($socket,"set ".$station.$announcement_command_suffix." active ".$active);
	if (read_response($socket)) break;

	send_command($socket,"quit");
	if (read_response($socket)) break;

	$http_output .= "Closing socket...<br />\n";
	socket_close($socket);
	$http_code =200;
}
http_response_code($http_code);

function read_response($socket) {
	global $http_output;
    $out = "";
	$start = time();
	$timed_out = false;
    while (substr($out,-2) !== "> ") {
		$timed_out = ((time()-$start)>5);
		if (false == (socket_recv($socket, $out, 2048, false))) break;
		$http_output .= str_replace("\n","<br />\n",$out);
	}
return $timed_out;
}

function send_command($socket,$in) {
	global $http_output;
    $http_output .= "<b>" . $in . "</b><br />\n";
    socket_write($socket, $in."\n", strlen($in)+1);
return;
}

?>
<html>
<head>
<title>Announcement Flag Controller</title>
<body>
<h2>Announcement Flag Controller</h2>
<?php echo $http_output ?>
</body></html>
