<?php
	include "mcse_common.php";
	global $mcse_config;
	check_availabilty_realms();
	check_request("GET");

	$profile = check_realms_sid();
	check_realms_name($profile);

	header('Content-Type: text/plain');

	$version_manifest = json_decode(file_get_contents(dirname($_SERVER["DOCUMENT_ROOT"], 3) . "/distribution/launchermeta.mojang.com/mc/game/version_manifest.json"), true);

	$response = "OTHER";

	if ($version_manifest["latest"]["release"] == $_COOKIE["version"])
		$response = "COMPATIBLE";
	else
	{
		foreach ($version_manifest["versions"] as $version)
		{
			if ($version["id"] == $_COOKIE["version"])
			{
				if ($version["type"] == "release")
				{
					//Official only send OUTDATED if it's an outdated release version, OTHER is sent for outdated snapshots, and invalid strings.
					$response = "OUTDATED";
				}

				break;
			}
		}
	}

	response_telemetry($response, 200);
	echo $response;