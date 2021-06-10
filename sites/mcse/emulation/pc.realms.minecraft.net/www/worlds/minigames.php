<?php
include "mcse_common.php";

$profile = check_realms("GET");

$realm = get_realm_by_id(basename($_GET["path"]));

if ($realm["ownerUUID"] != $profile["profileId"])
{
	//supposed to respond with a json error, haven't added one with 403 yet.
	realms_error(403);
}

//Minigames functionality does not exist.
$response = "false";

response_telemetry($response, 200);
echo $response;