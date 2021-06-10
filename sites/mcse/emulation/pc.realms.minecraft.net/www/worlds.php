<?php
include "mcse_common.php";

$profile = check_realms("GET");

$response = array(
	"servers" => array()
);

$owned = get_realms_owned($profile["profileId"]);

foreach ($owned as $realm)
{
	$response["servers"][] = get_realm_json($realm, $profile);
}

$accepted_invites = get_realms_invites_for_profile_accepted($profile["profileId"]);

foreach ($accepted_invites as $realm_invite)
{
	$realm = get_realm_by_remoteSubscriptionId($realm_invite["remoteSubscriptionId"]);
	if (!is_null($realm))
		$response["servers"][] = get_realm_json($realm, $profile);
}

response_telemetry(json_encode($response), 200);
echo json_encode($response);