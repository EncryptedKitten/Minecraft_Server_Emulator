<?php
include "mcse_common.php";

if(is_null($_GET["name"]) or is_null($_GET["session"]))
{
	http_response_code(400);
	response_telemetry("Bad response", 400);

	echo "Bad response";
	exit();
}

$user = get_user_by_username($_GET["username"]);
$sessionId = hex2bin($_GET["session"]);

if (is_null(find_session_id($user["id"], $sessionId)))
{
	http_response_code(400);
	response_telemetry("", 400);
	exit();
}

response_telemetry("", 200);
?>