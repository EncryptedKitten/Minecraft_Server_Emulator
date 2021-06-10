<?php
	global $mcse_config;
	
	$conn = new PDO($mcse_config["database"]["source"], $mcse_config["database"]["username"], $mcse_config["database"]["password"]);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$tables = [
		"accessTokens",
		"blockedProfiles",
		"blockedservers",
		"hash_id",
		"hasJoined",
		"joinserver_legacy",
		"legacySessionIds",
		"profiles",
		"realms",
		"realmsBackingServers",
		"realmsInvites",
		"realmsMinigames",
		"realmsOps",
		"sales",
		"security_answers",
		"security_ips",
		"snoop",
		"telemetry",
		"users"
	];
?>