<?php
include "mcse_common.php";
global $conn;

$json = json_decode(file_get_contents("php://input"), true);

if (array_key_exists("server", $json) and $json["server"] != "")
{
	$serverHash = mcsha1($json["server"]);
}
else if (array_key_exists("serverHash", $json) and $json["serverHash"] != "")
{
	$serverHash = $json["serverHash"];
}
else
{
	http_response_code(400);
	echo "No Server or Hash";
	exit();
}

$stmt = $conn->prepare("SELECT COUNT(*) FROM `blockedservers` WHERE `serverHash` = :serverHash");
$stmt->bindParam(':serverHash', $serverHash);
$stmt->execute();

$result = $stmt->fetchColumn();

if ($result != 0)
{
	http_response_code(400);
	echo "Server already blocked";
	exit();
}

$server = array_key_exists("server", $json) ? $json["server"] : "";

if ($server != "")
{
	$stmt = $conn->prepare("INSERT INTO `blockedservers`(`server`, `serverHash`)
	VALUES (:server,:serverHash)");
	$stmt->bindParam(':server', $server);
	$stmt->bindParam(':serverHash', $serverHash);
	$stmt->execute();
}
else
{
	$stmt = $conn->prepare("INSERT INTO `blockedservers`(`serverHash`)
	VALUES (:serverHash)");
	$stmt->bindParam(':serverHash', $serverHash);
	$stmt->execute();
}

http_response_code(204);