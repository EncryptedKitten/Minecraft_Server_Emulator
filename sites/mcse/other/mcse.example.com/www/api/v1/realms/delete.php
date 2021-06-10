<?php
include "mcse_common.php";
global $conn, $now, $timestamp_end;

$json = json_decode(file_get_contents("php://input"), true);

if (!array_key_exists("id", $json))
{
	http_response_code(400);
	echo "No ID";
	exit();
}

$id = hex2bin(uuid_to_hex($json["id"]));

if (strlen($id) != 16)
{
	http_response_code(400);
	echo "ID is not 16 bytes";
	exit();
}

$stmt = $conn->prepare("SELECT COUNT(*) FROM `realms` WHERE `remoteSubscriptionId` = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();

$result = $stmt->fetchColumn();

if ($result == 0)
{
	http_response_code(400);
	echo "Realm does not exist";
	exit();
}

$stmt = $conn->prepare("DELETE FROM `realms` WHERE `remoteSubscriptionId` = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();

$stmt = $conn->prepare("DELETE FROM `realmsBackingServers` WHERE `remoteSubscriptionId` = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();

http_response_code(204);