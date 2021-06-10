<?php
include "mcse_common.php";

$profile = check_realms("GET");

$invites = get_realms_invites_for_profile($profile["profileId"]);

$response = array(
	"invites" => array()
);

foreach ($invites as $invite)
{
	$realm = get_realm_by_remoteSubscriptionId($invite["remoteSubscriptionId"]);
	if (!is_null($realm))
	{
		$response["invites"][] = array(
			"invitationId" => $invite["invitationId"],
			"worldName" => $realm["name"],
			"worldDescription" => $realm["motd"],
			"worldOwnerName" => get_profile_by_id($realm["ownerUUID"])["name"],
			"worldOwnerUuid" => bin2hex($realm["ownerUUID"]),
			"date" => $invite["time"]
		);
	}
}

response_telemetry(json_encode($response), 200);
echo json_encode($response);