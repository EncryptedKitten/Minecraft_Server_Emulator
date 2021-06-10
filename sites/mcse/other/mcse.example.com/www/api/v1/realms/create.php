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
	var_dump($id);
	echo count($id);
	echo "ID is not 16 bytes";
	exit();
}

if (!array_key_exists("name", $json) or $json["name"] == "")
{
	http_response_code(400);
	echo "No Name";
	exit();
}

$name = json["name"];

if (!array_key_exists("motd", $json) or $json["motd"] == "")
{
	http_response_code(400);
	echo "No MOTD";
	exit();
}

$motd = $json["motd"];

if (!array_key_exists("port", $json) or $json["port"] == "")
{
	http_response_code(400);
	echo "No Port";
	exit();
}

$port = intval($json["port"]);

if (!array_key_exists("rconPort", $json) or $json["rconPort"] == "")
{
	http_response_code(400);
	echo "No RCON Port";
	exit();
}

$rconPort = intval($json["rconPort"]);

if (!array_key_exists("address", $json) or $json["address"] == "")
{
	http_response_code(400);
	echo "No Address";
	exit();
}

$address = $json["address"];

if (!array_key_exists("rconPassword", $json) or $json["rconPassword"] == "")
{
	http_response_code(400);
	echo "No RCON Password";
	exit();
}

$rconPassword = $json["rconPassword"];

$stmt = $conn->prepare("SELECT COUNT(*) FROM `realms` WHERE `ownerUUID` = :ownerUUID");
$stmt->bindParam(':ownerUUID', $id);
$stmt->execute();

$result = $stmt->fetchColumn();

if ($result != 0)
{
	http_response_code(400);
	echo "Realm already exists";
	exit();
}

$remoteSubscriptionId = random_bytes(16);

$paidTime = $timestamp_end - $now;

$stmt = $conn->prepare("INSERT INTO `realms`(`remoteSubscriptionId`, `ownerUUID`, `name`, `motd`, `time`, `trial`, `worldType`, `activeSlot`, `paidTime`, `state`)
 VALUES (:remoteSubscriptionId, :ownerUUID, :name, :motd, :time, 0, \"NORMAL\", 1, :paidTime, \"OPEN\")");
$stmt->bindParam(':remoteSubscriptionId', $remoteSubscriptionId);
$stmt->bindParam(':ownerUUID', $id);
$stmt->bindParam(':name', $name);
$stmt->bindParam(':motd', $motd);
$stmt->bindParam(':time', $now);
$stmt->bindParam(':paidTime', $paidTime);
$stmt->execute();

$stmt = $conn->prepare("INSERT INTO realmsBackingServers (`remoteSubscriptionId`, `port`, `rconPort`, `address`, `rconPassword`)
VALUES (:remoteSubscriptionId, :port, :rconPort, :address, :rconPassword)");
$stmt->bindParam(':remoteSubscriptionId', $remoteSubscriptionId);
$stmt->bindParam(':port', $port);
$stmt->bindParam(':rconPort', $rconPort);
$stmt->bindParam(':address', $address);
$stmt->bindParam(':rconPassword', $rconPassword);
$stmt->execute();

http_response_code(204);