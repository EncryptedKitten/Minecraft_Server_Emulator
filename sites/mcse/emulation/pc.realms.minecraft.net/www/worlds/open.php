<?php
include "mcse_common.php";

$profile = check_realms("PUT");

$realm = get_realm_by_id(basename(dirname($_GET["path"])));

if ($realm["ownerUUID"] != $profile["profileId"])
{
	//supposed to respond with a json error, haven't added one with 403 yet.
	realms_error(403);
}

open_realm($realm["remoteSubscriptionId"]);

$response = "true";

response_telemetry($response, 200);
echo $response;