<?php
include "mcse_common.php";

header('Content-Type: application/json');

check_request("POST");

$json = file_get_contents('php://input');

$data = json_decode($json, true);


if (sizeof($data) > 10) {
	minecraft_error("not_allowed");
}

$response_full = array();
foreach($data as $name)
{

	if (is_null($name))
		minecraft_error("null_name");

	else {
		$profile = name_to_profile($name);

		if (!is_null($profile))
		{
			$user = get_user_by_id($profile["id"]);

			$response = array(
				"id" => bin2hex($profile["id"]),
				"name" => $profile["name"],
			);

			if ($profile["legacy"] == 1)
				$response["legacy"] = $user["legacyUser"];

			if ($profile["demo"] == 1)
				$response["demo"] = $user["demoUser"];

			$response_full[] = $response;
		}
	}
}

response_telemetry(json_encode($response_full), 200);
echo json_encode($response_full);

?>