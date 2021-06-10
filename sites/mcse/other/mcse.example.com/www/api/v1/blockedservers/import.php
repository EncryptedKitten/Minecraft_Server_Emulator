<?php
include "mcse_common.php";
global $conn, $mcse_config;

$cracked_hashes = str_replace("\r", "", file_get_contents($mcse_config["paths"]["cracked_hashes"]));

$cracked_hashes_to_block = explode ("\n", $cracked_hashes);

$blockedservers = explode ("\n", str_replace("\r", "", curl_get("https://sessionserver.mojang.com/blockedservers")));

$servers_to_block = array_merge($cracked_hashes_to_block, $blockedservers);

foreach($servers_to_block as $server_to_block)
{
	$server_to_block = explode (":", $server_to_block, 2);
	$serverHash = $server_to_block[0];

	if ($serverHash != "")
	{
		if (count($server_to_block) == 2)
			$server = $server_to_block[1];
		else
			$server = "";

		$stmt = $conn->prepare("SELECT COUNT(*) FROM `blockedservers` WHERE `serverHash` = :serverHash");
		$stmt->bindParam(':serverHash', $serverHash);
		$stmt->execute();

		$result = $stmt->fetchColumn();

		if ($result != 0 and $server != "")
		{
			$stmt = $conn->prepare("UPDATE `blockedservers` SET `server` = :server WHERE `serverHash` = :serverHash");
			$stmt->bindParam(':server', $server);
			$stmt->bindParam(':serverHash', $serverHash);
			$stmt->execute();

		}
		else if ($result == 0 and $server != "")
		{
			$stmt = $conn->prepare("INSERT INTO `blockedservers`(`server`, `serverHash`)
			VALUES (:server,:serverHash)");
			$stmt->bindParam(':server', $server);
			$stmt->bindParam(':serverHash', $serverHash);
			$stmt->execute();
		}
		else if ($result == 0)
		{
			$stmt = $conn->prepare("INSERT INTO `blockedservers`(`serverHash`)
			VALUES (:serverHash)");
			$stmt->bindParam(':serverHash', $serverHash);
			$stmt->execute();
		}
	}
}
http_response_code(204);