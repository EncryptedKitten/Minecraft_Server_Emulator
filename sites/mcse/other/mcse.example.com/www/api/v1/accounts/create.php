<?php
include "mcse_common.php";
global $conn, $now, $timestamp_end;

$json = json_decode(file_get_contents("php://input"), true);

if (!array_key_exists("email", $json) or $json["email"] == "")
{
	http_response_code(400);
	echo "No Email";
	exit();
}

if (!array_key_exists("name", $json) or $json["name"] == "")
{
	http_response_code(400);
	echo "No Name";
	exit();
}

if (!array_key_exists("password", $json) or $json["password"] == "")
{
	http_response_code(400);
	echo "No Password";
	exit();
}

$email = $json["email"];
$name = $json["name"];

$password = password_hash($json["password"], PASSWORD_DEFAULT);

$id = (array_key_exists("id", $json)) ? $json["id"] : "";
$id = hex2bin(uuid_to_hex($id));
$id = strlen($id) == 16 ? $id : random_bytes(16);

$profileId = (array_key_exists("profileId", $json)) ? $json["profileId"] : "";
$profileId = hex2bin(uuid_to_hex($profileId));
$profileId = strlen($profileId) == 16 ? $profileId : random_bytes(16);

$model = "steve";

$stmt = $conn->prepare("SELECT COUNT(*) FROM `users` WHERE `username` = :username");
$stmt->bindParam(':username', $email);
$stmt->execute();

$result = $stmt->fetchColumn();

if ($result != 0)
{
	http_response_code(400);
	echo "User already exists";
	exit();
}

$stmt = $conn->prepare("INSERT INTO `users`(`username`, `password`, `id`, `selectedProfile`, `security_question_1`, `security_answer_1`, `security_question_2`, `security_answer_2`, `security_question_3`, `security_answer_3`, `dateOfBirth`, `email`, `emailVerified`, `legacyUser`, `demoUser`, `verifiedByParent`, `onlineChatPrivilege`, `multiplayerServerPrivilege`, `multiplayerRealmsPrivilege`, `telemetryPrivilege`, `realmsTrialUsed`, `realmsTosAgreed`)
 VALUES (:username,:password,:id,:selectedProfile,0,0,0,0,0,0,0,:email,1,0,0,0,1,1,1,1,0,1)");
$stmt->bindParam(':username', $email);
$stmt->bindParam(':password', $password);
$stmt->bindParam(':id', $id);
$stmt->bindParam(':selectedProfile', $profileId);
$stmt->bindParam(':email', $email);
$stmt->execute();

$stmt = $conn->prepare("INSERT INTO profiles (name, profileId, id, model, timestamp_start, timestamp_end)
VALUES (:name, :profileId, :id, :model, :timestamp_start, :timestamp_end)");
$stmt->bindParam(':name', $name);
$stmt->bindParam(':profileId', $profileId);
$stmt->bindParam(':id', $id);
$stmt->bindParam(':model', $model);
$stmt->bindParam(':timestamp_start', $now);
$stmt->bindParam(':timestamp_end', $timestamp_end);
$stmt->execute();

http_response_code(204);