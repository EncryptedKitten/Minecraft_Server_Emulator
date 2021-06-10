<?php
include "mcse_common.php";

header('Content-Type: text/plain');
check_request("GET");

$blockedservers = get_blocked_servers();

$response = "";
foreach($blockedservers as $blockedserver)
{
	$response .= ($blockedserver["serverHash"] . "\n");
}

response_telemetry("", 200);
echo $response;
?>