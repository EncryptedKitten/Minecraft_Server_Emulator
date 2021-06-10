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

$stmt = $conn->prepare("SELECT COUNT(*) FROM `users` WHERE `id` = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();

$result = $stmt->fetchColumn();

if ($result == 0)
{
	http_response_code(400);
	echo "User does not exist";
	exit();
}

$stmt = $conn->prepare("DELETE FROM `users` WHERE `id` = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();

$stmt = $conn->prepare("UPDATE `profiles` SET timestamp_end = :now WHERE `id` = :id AND timestamp_end = :timestamp_end");
$stmt->bindParam(':id', $id);
$stmt->bindParam(':now', $now);
$stmt->bindParam(':timestamp_end', $timestamp_end);
$stmt->execute();

$stmt = $conn->prepare("DELETE FROM `accessTokens` WHERE `id` = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();

$stmt = $conn->prepare("DELETE FROM `blockedProfiles` WHERE `blockerId` = :id OR `blockedId` = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();

http_response_code(204);