<?php
include "mcse_common.php";

global $now;

$profile = check_realms("GET");

$realm = get_realm_by_id(basename($_GET["path"]));

if ($realm["ownerUUID"] != $profile["profileId"])
{
	//supposed to respond with a json error, haven't added one with 403 yet.
	realms_error(403);
}

$daysLeft = ($realm["time"] + $realm["paidTime"] - $now)/(60 * 60 * 24);
if ($daysLeft <= 0)
{
	$daysLeft = -1;
	$expired = !$realm["trial"];
	$expiredTrial = $realm["trial"];
}
else
{
	$expired = false;
	$expiredTrial = false;
}

//Can't find what subscription type is supposed to be, so ill guess it might have something to do if it's trial or not, normal is a valid value for it though.
$response = array(
	"startDate" => $realm["time"],
	"daysLeft" => $daysLeft,
	"subscriptionType" => $realm["trial"] ? "TRIAL" : "NORMAL"
);

response_telemetry(json_encode($response), 200);
echo json_encode($response);