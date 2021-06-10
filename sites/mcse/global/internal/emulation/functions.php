<?php
global $mcse_config, $conn;
//include "../database.php";
include_once("rcon.class.php");

include "mc_server_ping/MinecraftPing.php";
include "mc_server_ping/MinecraftPingException.php";
include "mc_server_ping/MinecraftQuery.php";
include "mc_server_ping/MinecraftQueryException.php";

$textures_root = $mcse_config["advanced"]["textures_root"];
$timestamp_end = PHP_INT_MAX - 1;
$textures_data_path = $mcse_config["paths"]["textures"];
$minigames_image_path = $mcse_config["paths"]["minigames_images"];
$telemetry_level = -1;

$telemetry_id = initial_telemetry();

$telemetry_debug = "";

function isBinary($str) {
	return preg_match('~[^\x20-\x7E\t\r\n]~', $str) > 0;
}

function initial_telemetry()
{
	global $conn, $now, $telemetry_level, $mcse_config;

	if ($telemetry_level == -1)
	{
		if (array_key_exists('telemetry_staging', $GLOBALS))
			$telemetry_level = $mcse_config["telemetry"]["staging_level"];

		else
			$telemetry_level = $mcse_config["telemetry"]["level"];
	}

	$telemetry_id = NULL;

	if ($telemetry_level >= 1) {
		$telemetry_id = random_bytes(16);

		$t_GET = json_encode($_GET);
		$t_COOKIE = json_encode($_COOKIE);
		$t_POST = file_get_contents('php://input');
		$t_SERVER = json_encode($_SERVER);

		$stmt = $conn->prepare("INSERT INTO telemetry (telemetry_id, time, telemetry_level, SERVER, http_host, http_path) VALUES (:telemetry_id, :time, :telemetry_level, :SERVER, :http_host, :http_path)");
		$stmt->bindParam(':telemetry_id', $telemetry_id);
		$stmt->bindParam(':time', $now, PDO::PARAM_INT);
		$stmt->bindParam(':telemetry_level', $telemetry_level, PDO::PARAM_INT);
		$stmt->bindParam(':SERVER', $t_SERVER);
		$stmt->bindParam(":http_host", $_SERVER["HTTP_HOST"]);
		$stmt->bindParam(":http_path", $_SERVER["REQUEST_URI"]);
		$stmt->execute();

		if (!array_key_exists('telemetry_no_get', $GLOBALS))
		{
			$stmt = $conn->prepare("UPDATE telemetry SET GET = :GET WHERE telemetry_id = :telemetry_id");
			$stmt->bindParam(':telemetry_id', $telemetry_id);
			$stmt->bindParam(':GET', $t_GET);
			$stmt->execute();
		}

		if (!array_key_exists('telemetry_no_post', $GLOBALS) and ! isBinary($t_POST))
		{
			$stmt = $conn->prepare("UPDATE telemetry SET POST = :POST WHERE telemetry_id = :telemetry_id");
			$stmt->bindParam(':telemetry_id', $telemetry_id);
			$stmt->bindParam(':POST', $t_POST);
			$stmt->execute();
		}

		if (!array_key_exists('telemetry_no_cookie', $GLOBALS))
		{
			$stmt = $conn->prepare("UPDATE telemetry SET COOKIE = :COOKIE WHERE telemetry_id = :telemetry_id");
			$stmt->bindParam(':telemetry_id', $telemetry_id);
			$stmt->bindParam(':COOKIE', $t_COOKIE);
			$stmt->execute();
		}

		if (filter_var($_SERVER["REMOTE_ADDR"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			$ipv4 = inet_pton($_SERVER["REMOTE_ADDR"]);

			$stmt = $conn->prepare("UPDATE telemetry SET ipv4 = :ipv4 WHERE telemetry_id = :telemetry_id");
			$stmt->bindParam(':telemetry_id', $telemetry_id);
			$stmt->bindParam(':ipv4', $ipv4);
			$stmt->execute();
		} else {
			$ipv6 = inet_pton($_SERVER["REMOTE_ADDR"]);

			$stmt = $conn->prepare("UPDATE telemetry SET ipv6 = :ipv6 WHERE telemetry_id = :telemetry_id");
			$stmt->bindParam(':telemetry_id', $telemetry_id);
			$stmt->bindParam(':ipv6', $ipv6);
			$stmt->execute();
		}
	}

	return $telemetry_id;
}

function response_telemetry($response, $response_code)
{
	global $conn, $telemetry_id, $telemetry_level;

	if ($telemetry_level >= 1) {

		$stmt = $conn->prepare("UPDATE telemetry SET response = :response, response_code = :response_code WHERE telemetry_id = :telemetry_id");
		$stmt->bindParam(':telemetry_id', $telemetry_id);

		$stmt->bindParam(':response', $response);
		$stmt->bindParam(':response_code', $response_code, PDO::PARAM_INT);

		$stmt->execute();
	}
}

function debug_telemetry($debug_text)
{
	global $telemetry_level, $conn, $telemetry_id, $telemetry_debug;

	if (strpos(site_dir(), "mcse") !== false and $telemetry_level >= 1) {

		$telemetry_debug .= " " . $debug_text;
		$stmt = $conn->prepare("UPDATE telemetry SET debug = :debug WHERE telemetry_id = :telemetry_id");
		$stmt->bindParam(':telemetry_id', $telemetry_id);

		$stmt->bindParam(':debug', $telemetry_debug);

		$stmt->execute();
	}
}

function uuid_to_hex($string)
{
	$hexs = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f");

	$lowered = strtolower($string);
	$hexed = "";

	foreach (str_split($lowered) as $char)
	{
		if (in_array($char, $hexs))
		{
			$hexed .= $char;
		}
	}

	return $hexed;
}

function hex_to_uuid($input)
{
	$uuid = preg_replace("/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i", "$1-$2-$3-$4-$5", $input);
	return $uuid;
}

function check_bearer()
{
	global $conn;
	$accessToken = base64_decode(str_replace("Bearer ", "", $_SERVER['HTTP_AUTHORIZATION']));

	$stmt = $conn->prepare("SELECT * FROM accessTokens WHERE accessToken = :accessToken");
	$stmt->bindParam(':accessToken', $accessToken);
	$stmt->execute();

	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = $stmt->fetchAll();

	return $result[0];
}

function get_user_by_id($id)
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
	$stmt->bindParam(':id', $id);
	$stmt->execute();

	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = $stmt->fetchAll();

	return $result[0];
}

function get_profile_by_id($profileId)
{
	global $conn, $timestamp_end;

	$stmt = $conn->prepare("SELECT * FROM profiles WHERE profileId = :profileId AND timestamp_end = :timestamp_end");
	$stmt->bindParam(':profileId', $profileId);
	$stmt->bindParam(':timestamp_end', $timestamp_end, PDO::PARAM_INT);
	$stmt->execute();

	$stmt->setFetchMode(PDO::FETCH_ASSOC);

	return $stmt->fetch();
}

function get_profile_by_name($name)
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM profiles WHERE name = :name");
	$stmt->bindParam(':name', $name);
	$stmt->execute();

	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = $stmt->fetchAll();

	return $result[0];
}

function get_blocklist_by_profileId($profileId)
{
	global $conn;

	$stmt = $conn->prepare("SELECT blockedId FROM blockedProfiles WHERE blockerId = :blockerId");
	$stmt->bindParam(':blockerId', $profileId);
	$stmt->execute();

	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = $stmt->fetchAll();

	return $result;
}

function get_selectedProfile($user)
{
	return get_profile_by_id($user["selectedProfile"]);
}

function accessToken_valid($accessTokenData)
{
	return !is_null($accessTokenData);
}

function model_to_variant($model)
{
	return ($model == "steve" ? "CLASSIC" : "SLIM");
}

function hash_to_id($hash)
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM hash_id WHERE sha256 = :sha256");
	$stmt->bindParam(':sha256',$hash);
	$stmt->execute();

	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = $stmt->fetchAll();

	return $result[0];
}

function profile_has_skin($profile)
{
	return !is_null($profile["skin"]);
}

function profile_has_cape($profile)
{
	return !is_null($profile["cape"]);
}

function hash_to_texture_url($hash)
{
	global $textures_root;
	return $textures_root . bin2hex($hash);
}
function get_skin_url($profile)
{
	return hash_to_texture_url($profile["skin"]);
}

function get_cape_url($profile)
{
	return hash_to_texture_url($profile["cape"]);
}

function get_hash_uuid($hash)
{
	$hash_id = hash_to_id($hash);

	return hex_to_uuid($hash_id["uuid"]);
}

function get_skin_uuid($profile)
{
	return get_hash_uuid($profile["skin"]);
}

function get_cape_uuid($profile)
{
	return get_hash_uuid($profile["cape"]);
}

function minecraft_error($id)
{
	global $mcse_config;
	$minecraft_errors = json_decode(file_get_contents($mcse_config["paths"]["minecraft_errors"]), true);

	http_response_code($minecraft_errors[$id]["HTTP Code"]);

	$response = array(
			"error" => $minecraft_errors[$id]["Error"],
			"errorMessage" => $minecraft_errors[$id]["Error message"]
	);

	//Optional Error Cause, if exists
	if (array_key_exists("Cause", $minecraft_errors[$id]))
		$response["cause"] = $minecraft_errors[$id]["Cause"];

	response_telemetry(json_encode($response), $minecraft_errors[$id]["HTTP Code"]);

	echo json_encode($response);
	exit();
}

function legacy_minecraft_error($text, $code)
{
	http_response_code($code);

	response_telemetry($text, $code);

	echo $text;
	exit();
}

function realms_error($code)
{
	http_response_code($code);

	response_telemetry("", $code);

	exit();
}

function check_authentication()
{
	$accessTokenData = check_bearer();
	if (accessToken_valid($accessTokenData))
		return $accessTokenData;
	else
		minecraft_error("token_invalid");
}

function check_request($http_type)
{
	if ($_SERVER['REQUEST_METHOD'] != $http_type)
		minecraft_error("not_allowed");
}

function check_request_realms($http_type)
{
	if ($_SERVER['REQUEST_METHOD'] != $http_type)
		realms_error(405);
}

function check_availabilty_realms()
{
	global $mcse_config;
	//If realms isn't enabled, none of these endpoint will exist!
	if (!$mcse_config["realms"]["enabled"])
		realms_error(404);
}

function check_realms_sid()
{	
	$sid = explode(":", $_COOKIE["sid"]);
	if ($sid[0] != "token")
		realms_error(401);

	$accessToken = base64_decode($sid[1]);
	$profileId = hex2bin($sid[2]);

	$accessTokenData = find_access_token($accessToken);
	if (!accessToken_valid($accessTokenData))
		realms_error(401);

	$profile = get_profile_by_id($profileId);

	if (is_null($profile) or $profile["id"] != $accessTokenData["id"])
		realms_error(401);

	return $profile;
}

function check_realms_name($profile)
{
	if ($profile["name"] != $_COOKIE["user"])
		realms_error(401);
}

function check_realms_version()
{
	$version_manifest = json_decode(file_get_contents(dirname($_SERVER["DOCUMENT_ROOT"], 3) . "/distribution/launchermeta.mojang.com/mc/game/version_manifest.json"), true);

	if ($version_manifest["latest"]["release"] != $_COOKIE["version"])
		realms_error(401);
}

function check_authentication_realms()
{
	$profile = check_realms_sid();
	check_realms_name($profile);
	check_realms_version();

	return $profile;
}

function check_realms($http_type)
{
	check_availabilty_realms();
	check_request($http_type);
	return check_authentication_realms();
}

function check_request_multiple($http_types)
{
	if (!in_array($_SERVER['REQUEST_METHOD'], $http_types))
		minecraft_error("not_allowed");
}

function username_available($name)
{
	global $conn, $timestamp_end;
	$stmt = $conn->prepare("SELECT * FROM profiles WHERE name = :name AND timestamp_end = :timestamp_end");
	$stmt->bindParam(':name', $name);
	$stmt->bindParam(':timestamp_end', $timestamp_end);
	$stmt->execute();

	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = $stmt->fetchAll();

	return !is_null($result[0]);
}

function name_change_allowed($profile)
{
	global $conn, $now, $mcse_config;
	$stmt = $conn->prepare("SELECT * FROM profiles WHERE profileId = :profileId AND timestamp_end = :timestamp_end");
	$stmt->bindParam(':profileId', $profile);
	$stmt->bindParam(':timestamp_end', $timestamp_end);
	$stmt->execute();

	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = $stmt->fetchAll();

	$profile = $result[0];

	return $profile["timestamp_start"] < $now - $mcse_config["restrictions"]["name_change"];
}

function name_change_error()
{
	http_response_code(403);

	$response = array(
		"path" => $_GET["path"]
	);

	echo json_encode($response);
	exit();
}

function change_name($name, $profile)
{
	global $conn, $now;

	$stmt = $conn->prepare("UPDATE profiles SET timestamp_end = :now, skin = NULL, cape = NULL, model = NULL, legacy = NULL, demo = NULL WHERE profileId = :profileId AND timestamp_end = :timestamp_end");
	$stmt->bindParam(':profileId', $profile["profileId"]);
	$stmt->bindParam(':now', $now);
	$stmt->bindParam(':timestamp_end', $timestamp_end);
	$stmt->execute();

	$stmt = $conn->prepare("INSERT INTO profiles (name, profileId, id, skin, cape, model, timestamp_start, timestamp_end)
			VALUES (:name, :profileId, :id, :skin, :cape, :model, :timestamp_start, :timestamp_end)");
	$stmt->bindParam(':name', $name);
	$stmt->bindParam(':profileId', $profile["profileId"]);
	$stmt->bindParam(':id', $profile["id"]);
	$stmt->bindParam(':skin', $profile["skin"]);
	$stmt->bindParam(':cape', $profile["cape"]);
	$stmt->bindParam(':skin', $profile["skin"]);
	$stmt->bindParam(':model', $profile["model"]);
	$stmt->bindParam(':timestamp_start', $now);
	$stmt->bindParam(':timestamp_end', $timestamp_end);
	$stmt->execute();
}

function curl_get($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	return curl_exec($ch);
}

function variant_to_model($variant)
{
	return ($variant == "classic" ? "steve" : "alex");
}

function ensure_uuid_for_hash($hash)
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM hash_id WHERE sha256 = :sha256");
	$stmt->bindParam(':sha256', $hash);
	$stmt->execute();

	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = $stmt->fetchAll();

	$hash_id = $result[0];

	if (is_null($hash_id))
	{
		$stmt = $conn->prepare("INSERT INTO hash_id (sha256, uuid) VALUES (:sha256, :uuid)");
		$stmt->bindParam(':sha256', $hash);
		$uuid = random_bytes(16);
		$stmt->bindParam(':uuid', $uuid);
		$stmt->execute();
	}
}

function set_skin($file, $variant, $profile)
{
	global $conn, $textures_data_path;

	$hash = hash('sha256', $file, true);
	$filename = $textures_data_path . bin2hex($hash);

	if(!file_exists ($filename))
	{
		file_put_contents($filename, $file);
		ensure_uuid_for_hash($hash);
	}

	$model = variant_to_model($variant);

	$stmt = $conn->prepare("UPDATE profiles SET skin = :skin, model = :model WHERE profileId = :profileId");
	$stmt->bindParam(':profileId', $profile["profileId"]);
	$stmt->bindParam(':skin', $hash);
	$stmt->bindParam(':model', $model);
	$stmt->execute();
}

function get_time_at()
{
	if (isset($_GET["at"]))
	{
		$i = intval($_GET["at"]);

		if (is_numeric($_GET["at"]) and $i != PHP_INT_MAX and $i != PHP_INT_MIN)
			$now = intval($_GET["at"]);
		else
			minecraft_error("bad_timestamp");
	}
	else
		$now = time();

	return $now;
}

function get_profile_at_time($name, $time)
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM profiles WHERE name = :name AND timestamp_start <= :now AND timestamp_end > :now");
	$stmt->bindParam(':name', $name);
	$stmt->bindParam(':now', $time, PDO::PARAM_INT);

	$stmt->execute();

	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = $stmt->fetchAll();
	return $result[0];
}

function profile_exists_at_time($profile)
{
	if (is_null($profile))
	{
		http_response_code(204);
		exit();
	}
}

function profileId_to_name_history($profile_id)
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM profiles WHERE profileId = :profileId ORDER BY timestamp_start ASC");
	$stmt->bindParam(':profileId', $profile_id);

	$stmt->execute();

	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	return $stmt->fetchAll();
}

function reset_skin($profile)
{
	global $conn, $textures_data_path;

	$stmt = $conn->prepare("UPDATE profiles SET skin = NULL, model = NULL WHERE profileId = :profileId");
	$stmt->bindParam(':profileId', $profile["profileId"]);
	$stmt->execute();

	$stmt = $conn->prepare("SELECT COUNT(*) FROM profiles WHERE skin = :skin");
	$stmt->bindParam(':skin', $profile["skin"]);
	$stmt->execute();

	$result = $stmt->fetchColumn();

	if ($result == 0)
	{
		$stmt = $conn->prepare("DELETE FROM hash_id WHERE sha256 = :sha256");
		$stmt->bindParam(':sha256', $profile["skin"]);
		$stmt->execute();

		$filename = $textures_data_path . bin2hex($profile["skin"]);

		unlink($filename);
	}
}

function name_to_profile($name)
{
	global $conn, $now;

	$stmt = $conn->prepare("SELECT * FROM profiles WHERE name = :name AND timestamp_start <= :now AND timestamp_end > :now");
	$stmt->bindParam(':name', $name);
	$stmt->bindParam(':now', $now, PDO::PARAM_INT);

	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $result[0];
}

function get_all_sales($sale_type)
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM sales WHERE type = :type");
	$stmt->bindParam(':type', $sale_type);
	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	return $result;
}

function get_realms_owned($profileId)
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM realms WHERE ownerUUID = :ownerUUID");
	$stmt->bindParam(':ownerUUID', $profileId);
	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	return $result;
}

function get_realm_by_id($id)
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM realms WHERE id = :id");
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);
	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	return $result[0];
}

function realms_invite_id_check($invitationId, $profileId)
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM realmsInvites WHERE invitationId = :invitationId");
	$stmt->bindParam(':invitationId', $invitationId, PDO::PARAM_INT);
	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if ($result[0]["profileId"] != $profileId)
		realms_error(403);
}

function realms_accept_invite($invitationId, $profileId)
{
	global $conn;

	$result = realms_invite_id_check($invitationId, $profileId);

	realms_rcon($result["remoteSubscriptionId"], "whitelist add " . hex_to_uuid(bin2hex($profileId)));

	$stmt = $conn->prepare("UPDATE realmsInvites SET accepted = 1 WHERE invitationId = :invitationId");
	$stmt->bindParam(':invitationId', $invitationId, PDO::PARAM_INT);
	$stmt->execute();
}

function realms_reject_invite($invitationId, $profileId)
{
	global $conn;

	$result = realms_invite_id_check($invitationId, $profileId);

	$stmt = $conn->prepare("DELETE FROM realmsInvites WHERE invitationId = :invitationId");
	$stmt->bindParam(':invitationId', $invitationId, PDO::PARAM_INT);
	$stmt->execute();
}

function realms_delete_invite($remoteSubscriptionId, $profileId)
{
	global $conn;

	realms_rcon($remoteSubscriptionId, "whitelist remove " . hex_to_uuid(bin2hex($profileId)));

	$stmt = $conn->prepare("DELETE FROM realmsInvites WHERE remoteSubscriptionId = :remoteSubscriptionId AND profileId = :profileId");
	$stmt->bindParam(':remoteSubscriptionId', $remoteSubscriptionId);
	$stmt->bindParam(':profileId', $profileId);
	$stmt->execute();
}

function open_realm($remoteSubscriptionId)
{
	global $conn;

	$stmt = $conn->prepare("UPDATE realms SET state = \"OPEN\" WHERE remoteSubscriptionId = :remoteSubscriptionId");
	$stmt->bindParam(':remoteSubscriptionId', $remoteSubscriptionId);
	$stmt->execute();
}

function close_realm($remoteSubscriptionId)
{
	global $conn;

	$stmt = $conn->prepare("UPDATE realms SET state = \"CLOSED\" WHERE remoteSubscriptionId = :remoteSubscriptionId");
	$stmt->bindParam(':remoteSubscriptionId', $remoteSubscriptionId);
	$stmt->execute();
}

function get_realm_by_remoteSubscriptionId($remoteSubscriptionId)
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM realms WHERE remoteSubscriptionId = :remoteSubscriptionId");
	$stmt->bindParam(':remoteSubscriptionId', $remoteSubscriptionId);
	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	return $result[0];
}

function get_realms_invites_for_profile($profileId)
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM realmsInvites WHERE profileId = :profileId AND accepted = 0");
	$stmt->bindParam(':profileId', $profileId);
	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	return $result;
}

function realms_invite_send($remoteSubscriptionId, $payload)
{
	global $conn, $now;

	if (array_key_exists("uuid", $payload))
	{
		$profileId = hex2bin($payload["uuid"]);
	}
	else{
		$profileId = get_profile_by_name($payload["name"])["profileId"];
	}

	if (array_key_exists("operator", $payload) and $payload["operator"])
		realms_op($remoteSubscriptionId, $profileId);

	$accepted = (array_key_exists("accepted", $payload) and $payload["accepted"]) ? 1 : 0;

	$stmt = $conn->prepare("INSERT INTO realmsInvites (remoteSubscriptionId, profileId, time, accepted) VALUES (:remoteSubscriptionId, :profileId, :time, :accepted)");
	$stmt->bindParam(':remoteSubscriptionId', $remoteSubscriptionId);
	$stmt->bindParam(':profileId', $profileId);
	$stmt->bindParam(':time', $now);
	$stmt->bindParam(':accepted', $accepted, PDO::PARAM_INT);
	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	return $result;
}

function get_realms_invites_for_profile_accepted($profileId)
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM realmsInvites WHERE profileId = :profileId AND accepted = 1");
	$stmt->bindParam(':profileId', $profileId);
	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	return $result;
}

function get_realm_by_invite_id_for_profile($remoteSubscriptionId, $profileId)
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM realmsInvites WHERE remoteSubscriptionId = :remoteSubscriptionId AND profileId = :profileId AND accepted = 1");
	$stmt->bindParam(':remoteSubscriptionId', $remoteSubscriptionId);
	$stmt->bindParam(':profileId', $profileId);
	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	return $result[0];
}

function get_realms_backing_server($remoteSubscriptionId)
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM realmsBackingServers WHERE remoteSubscriptionId = :remoteSubscriptionId");
	$stmt->bindParam(':remoteSubscriptionId', $remoteSubscriptionId);
	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	return $result[0];
}

function realms_rcon($remoteSubscriptionId, $command)
{
	$realm = get_realm_by_remoteSubscriptionId($remoteSubscriptionId);
	$backing_server = get_realms_backing_server($remoteSubscriptionId);
	if ($realm["state"] == "CLOSED")
		realms_error(403);

	$r = new rcon($backing_server["address"], $backing_server["rconPort"], $backing_server["rconPassword"]);
	if(!$r->Auth())
		realms_error(500);

	$r->rconCommand($command);
}

function get_realms_ops($remoteSubscriptionId)
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM realmsOps WHERE remoteSubscriptionId = :remoteSubscriptionId");
	$stmt->bindParam(':remoteSubscriptionId', $remoteSubscriptionId);
	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	return $result;
}

function realms_op($remoteSubscriptionId, $profileId)
{
	global $conn;

	realms_rcon($remoteSubscriptionId, "op " . hex_to_uuid(bin2hex($profileId)));

	$stmt = $conn->prepare("INSERT INTO realmsOps (remoteSubscriptionId, profileId) VALUES (:remoteSubscriptionId, :profileId)");
	$stmt->bindParam(':remoteSubscriptionId', $remoteSubscriptionId);
	$stmt->bindParam(':profileId', $profileId);
	$stmt->execute();
}

function realms_deop($remoteSubscriptionId, $profileId)
{
	global $conn;

	realms_rcon($remoteSubscriptionId, "deop " . hex_to_uuid(bin2hex($profileId)));

	$stmt = $conn->prepare("DELETE FROM realmsOps WHERE remoteSubscriptionId = :remoteSubscriptionId AND profileId = :profileId");
	$stmt->bindParam(':remoteSubscriptionId', $remoteSubscriptionId);
	$stmt->bindParam(':profileId', $profileId);
	$stmt->execute();
}

use xPaw\MinecraftQuery;
use xPaw\MinecraftQueryException;

function get_realm_json($realm, $profile)
{
	global $now, $mcse_config, $minigames_image_path;

	$daysLeft = ($realm["time"] + $realm["paidTime"] - $now)/(60 * 60 * 24);
	if ($daysLeft <= 0)
	{
		$daysLeft = -1;
		$expired = !$realm["trial"];
		$expiredTrial = $realm["trial"];
	}
	else
	{
		$expired = false;
		$expiredTrial = false;
	}

	$Query = new MinecraftQuery( );

	$players = array();

	if ($realm["state"] == "OPEN") {
		try {
			$backing_server = get_realms_backing_server($realm["remoteSubscriptionId"]);

			$Query->Connect($backing_server["address"], $backing_server["port"]);

			if (array_key_exists("sample", $Query->GetPlayers())) {
				foreach ($Query->GetPlayers()["sample"] as $player) {
					$players[] = $player["name"];
				}
			}
		} catch (MinecraftQueryException $e) {
		}
	}



	$realm_response = array(
		"id" => $realm["id"],
		"remoteSubscriptionId" => bin2hex($realm["remoteSubscriptionId"]),
		"owner" => $profile["name"],
		"ownerUUID" => bin2hex($profile["profileId"]),
		"name" => $realm["name"],
		"motd" => $realm["motd"],
		"state" => $realm["state"],
		"daysLeft" => $daysLeft,
		"expired" => $expired,
		"expiredTrial" => $expiredTrial,
		"worldType" => $realm["worldType"],
		"players" => $players,
		"maxPlayers" => $mcse_config["realms"]["maxPlayers"],
		"activeSlot" => $realm["activeSlot"],
		"slots" => null,
		"member" => false
	);

	if(!is_null($realm["minigameId"]))
	{
		$realm_response["minigameId"] = bin2hex($realm["minigameId"]);
		$minigame = get_minigame_owned($realm["minigameId"]);
		$realm_response["minigameName"] = $minigame["minigameName"];
		$realm_response["minigameImage"] = base64_encode(file_get_contents($minigames_image_path . bin2hex($minigame["minigameImage"])));
	}

	return $realm_response;
}

function get_realm_json_invite($realm, $profile)
{
	global $now, $mcse_config, $minigames_image_path, $conn;

	$daysLeft = ($realm["time"] + $realm["paidTime"] - $now)/(60 * 60 * 24);
	if ($daysLeft <= 0)
	{
		$daysLeft = -1;
		$expired = !$realm["trial"];
		$expiredTrial = $realm["trial"];
	}
	else
	{
		$expired = false;
		$expiredTrial = false;
	}

	$players = array();

	$remoteSubscriptionId = $realm["remoteSubscriptionId"];

	$stmt = $conn->prepare("SELECT * FROM realmsInvites WHERE remoteSubscriptionId = :remoteSubscriptionId");
	$stmt->bindParam(':remoteSubscriptionId', $remoteSubscriptionId);
	$stmt->execute();

	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	foreach ($results as $result)
	{
		$stmt = $conn->prepare("SELECT COUNT(*) FROM realmsOps WHERE remoteSubscriptionId = :remoteSubscriptionId AND profileId = :profileId");
		$stmt->bindParam(':remoteSubscriptionId', $remoteSubscriptionId);
		$stmt->bindParam(':profileId', $profileId);
		$stmt->execute();

		$result2 = $stmt->fetchColumn();

		$players[] = array(
			"uuid" => bin2hex($result["profileId"]),
			"name" => get_profile_by_id($result["profileId"])["name"],
			"operator" => $result2 > 0,
			"accepted" => $result["accepted"],
			"online" => false,
			"permission" => "MEMBER"
		);
	}

	$realm_response = array(
		"id" => $realm["id"],
		"remoteSubscriptionId" => bin2hex($realm["remoteSubscriptionId"]),
		"owner" => $profile["name"],
		"ownerUUID" => bin2hex($profile["profileId"]),
		"name" => $realm["name"],
		"motd" => $realm["motd"],
		"state" => $realm["state"],
		"daysLeft" => $daysLeft,
		"expired" => $expired,
		"expiredTrial" => $expiredTrial,
		"worldType" => $realm["worldType"],
		"players" => $players,
		"maxPlayers" => $mcse_config["realms"]["maxPlayers"],
		"activeSlot" => $realm["activeSlot"],
		"slots" => null,
		"member" => false,
		"clubId" => null
	);

	if(!is_null($realm["minigameId"]))
	{
		$realm_response["minigameId"] = bin2hex($realm["minigameId"]);
		$minigame = get_minigame_owned($realm["minigameId"]);
		$realm_response["minigameName"] = $minigame["minigameName"];
		$realm_response["minigameImage"] = base64_encode(file_get_contents($minigames_image_path . bin2hex($minigame["minigameImage"])));
	}

	return $realm_response;
}

function get_minigame_owned($minigameId)
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM realmsMinigames WHERE minigameId = :minigameId");
	$stmt->bindParam(':minigameId', $minigameId);
	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	return $result[0];
}

function get_24h_sales($sale_type)
{
	global $conn;

	$stmt = $conn->prepare("SELECT MAX(time) AS max_time FROM sales WHERE type = :type");
	$stmt->bindParam(':type', $sale_type);
	$stmt->execute();

	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = intval($stmt->fetchColumn());

	$stmt = $conn->prepare("SELECT * FROM sales WHERE type = :type AND time = :latest_time");
	$stmt->bindParam(':type', $sale_type);
	$stmt->bindParam(':latest_time', $result, PDO::PARAM_INT);
	$stmt->execute();

	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	return $result[0]["count"];
}

function get_ip_security($id)
{
	global $conn;

	if(filter_var($_SERVER["REMOTE_ADDR"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
		$ipv4 = inet_pton($_SERVER["REMOTE_ADDR"]);

		$stmt = $conn->prepare("SELECT COUNT(*) FROM security_ips WHERE id = :id");
		$stmt->bindParam(':ipv4', $ipv4);
		$stmt->bindParam(':id', $id);
		$stmt->execute();

		$result = $stmt->fetchColumn();
	}
	else
	{
		$ipv6 = inet_pton($_SERVER["REMOTE_ADDR"]);

		$stmt = $conn->prepare("SELECT COUNT(*) FROM security_ips WHERE id = :id");
		$stmt->bindParam(':ipv6', $ipv6);
		$stmt->bindParam(':id', $id);
		$stmt->execute();

		$result = $stmt->fetchColumn();
	}

	return $result;
}

function check_ip_security($id)
{
	$check = get_ip_security($id);

	if ($check == 0)
		ip_not_secured($id);
}

function get_security_answer_by_id($id)
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM security_answers WHERE id = :id");
	$stmt->bindParam(':id', $id);
	$stmt->execute();

	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = $stmt->fetchAll();
	return $result[0];
}

function check_security_answer($answer)
{
	$security_answer = get_security_answer_by_id($answer["id"]);

	if (!password_verify($answer["answer"], $security_answer["answer"]))
	{
		minecraft_error("bad_security_answer");
	}
}

function security_check($answers, $user)
{
	foreach ($answers as $answer)
	{
		if ($answer["id"] == $user["security_answer_1"] or $answer["id"] == $user["security_answer_2"] or $answer["id"] == $user["security_answer_3"])
		{
			check_security_answer($answer);
		}
		else {
			minecraft_error("bad_security_answer");
		}
	}
}

function get_user_by_username($username)
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
	$stmt->bindParam(':username', $username);
	$stmt->execute();

	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = $stmt->fetchAll();
	return $result[0];
}

function delete_access_tokens_for_id($id)
{
	global $conn;

	$stmt = $conn->prepare("DELETE FROM accessTokens WHERE id = :id");
	$stmt->bindParam(':id', $id);
	$stmt->execute();
}

function get_access_token($id, $clientToken = null)
{
	global $conn, $now;

	$clientToken = (is_null($clientToken) ? random_bytes(16) : hex2bin(uuid_to_hex($clientToken)));
	$accessToken = random_bytes(32);

	//Add the new access token
	$stmt = $conn->prepare("INSERT INTO accessTokens (id, accessToken, clientToken, time) VALUES (:id, :accessToken, :clientToken, :time)");
	$stmt->bindParam(':id', $id);
	$stmt->bindParam(':accessToken', $accessToken);
	$stmt->bindParam(':clientToken', $clientToken);
	$stmt->bindParam(':time', $now, PDO::PARAM_INT);

	$stmt->execute();

	return [$accessToken, $clientToken];
}

function get_profiles_by_id($id)
{
	global $conn, $timestamp_end;
	//$timestamp_end = 9223372036854775806
	$stmt = $conn->prepare("SELECT * FROM `profiles` WHERE id = :id AND timestamp_end = :timestamp_end");
	$stmt->bindParam(':id', $id);
	$stmt->bindParam(':timestamp_end', $timestamp_end, PDO::PARAM_INT);

	$stmt->execute();
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	
	return $stmt->fetchAll();
}

function check_password($payload_password, $user_password)
{
	if (!password_verify($payload_password, $user_password))
		minecraft_error("invalid_credentials");
}

function find_access_token($accessToken)
{
	global $conn, $now, $mcse_config;

	$time = $now + $mcse_config["authentication"]["access_token"]["expiry"];

	$stmt = $conn->prepare("SELECT * FROM accessTokens WHERE accessToken = :accessToken AND time <= :time");
	$stmt->bindParam(':accessToken', $accessToken);
	$stmt->bindParam(':time', $time, PDO::PARAM_INT);
	$stmt->execute();

	$stmt->setFetchMode(PDO::FETCH_ASSOC);

	return $stmt->fetch();
}

function find_access_token_refresh($accessToken)
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM accessTokens WHERE accessToken = :accessToken");
	$stmt->bindParam(':accessToken', $accessToken);
	$stmt->execute();

	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = $stmt->fetchAll();

	return $result[0];
}

function revoke_access_token($accessToken)
{
	global $conn;

	$stmt = $conn->prepare("DELETE FROM accessTokens WHERE accessToken = :accessToken");
	$stmt->bindParam(':accessToken', $accessToken);
	$stmt->execute();
}

function check_client_token($clientToken_1, $clientToken_2)
{
	if ((is_null($clientToken_1)) or (is_null($clientToken_2)) or ($clientToken_1 != $clientToken_2))
		minecraft_error("token_invalid");
}

function refresh_access_token($accessTokenData)
{
	global $conn, $now, $mcse_config;
	$newAccessToken = random_bytes(32);

	//Check access token for refresh expiry, idk the actual, so its 30 days (config default).
	if ($now >= $accessTokenData["time"] + $mcse_config["authentication"]["access_token"]["refresh_expiry"])
		minecraft_error("invalid_credentials");

	$stmt = $conn->prepare("UPDATE accessTokens SET accessToken = :newAccessToken, time = :time WHERE accessToken = :accessToken");
	$stmt->bindParam(':newAccessToken', $newAccessToken);
	$stmt->bindParam(':accessToken', $accessTokenData["accessToken"]);
	$stmt->bindParam(':time', $now, PDO::PARAM_INT);
	$stmt->execute();

	return $newAccessToken;
}

function realms_tos_agree($id)
{
	global $conn;

	$stmt = $conn->prepare("UPDATE users SET realmsTosAgreed = 1 WHERE id = :id");
	$stmt->bindParam(':id', $id);
	$stmt->execute();
}

function get_blocked_servers()
{
	global $conn;

	$stmt = $conn->prepare("SELECT * FROM blockedservers");

	$stmt->execute();

	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	return $stmt->fetchAll();
}

function add_server_connection($selectedProfile, $serverId)
{
	global $conn, $now;

	if(filter_var($_SERVER["REMOTE_ADDR"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
		$ipv4 = inet_pton($_SERVER["REMOTE_ADDR"]);

		$stmt = $conn->prepare("INSERT INTO hasJoined (selectedProfile, serverId, time, ipv4) VALUES (:selectedProfile, :serverId, :time, :ipv4)");
		$stmt->bindParam(':selectedProfile', $selectedProfile);
		$stmt->bindParam(':serverId', $serverId);
		$stmt->bindParam(':time', $now, PDO::PARAM_INT);
		$stmt->bindParam(':ipv4', $ipv4);
	}
	else
	{
		$ipv6 = inet_pton($_SERVER["REMOTE_ADDR"]);

		$stmt = $conn->prepare("INSERT INTO hasJoined (selectedProfile, serverId, time, ipv6) VALUES (:selectedProfile, :serverId, :time, :ipv6)");
		$stmt->bindParam(':selectedProfile', $selectedProfile);
		$stmt->bindParam(':serverId', $serverId);
		$stmt->bindParam(':time', $now, PDO::PARAM_INT);
		$stmt->bindParam(':ipv6', $ipv6);
	}

	$stmt->execute();
}

function add_legacy_server_connection($selectedProfile, $serverId)
{
	global $conn, $now;

	$stmt = $conn->prepare("INSERT INTO joinserver_legacy (selectedProfile, serverId, time) VALUES (:selectedProfile, :serverId, :time)");
	$stmt->bindParam(':selectedProfile', $selectedProfile);
	$stmt->bindParam(':serverId', $serverId);
	$stmt->bindParam(':time', $now, PDO::PARAM_INT);

	$stmt->execute();
}

function check_join($selectedProfile, $serverId)
{
	global $conn, $now, $mcse_config;

	$time = $now - $mcse_config["servers"]["join_expiry"];

	$stmt = $conn->prepare("SELECT * FROM hasJoined WHERE selectedProfile = :selectedProfile AND serverId = :serverId AND time >= :time");
	$stmt->bindParam(':serverId', $serverId);
	$stmt->bindParam(':selectedProfile', $selectedProfile);
	//Join requests expire after 24 hours (config default), idk if theres an actual one, I just picked one.
	$stmt->bindParam(':time', $time, PDO::PARAM_INT);

	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $result[0];
}

function check_legacy_join($selectedProfile, $serverId)
{
	global $conn, $now, $mcse_config;

	$time = $now - $mcse_config["legacy_servers"]["join_expiry"];

	$stmt = $conn->prepare("SELECT * FROM joinserver_legacy WHERE selectedProfile = :selectedProfile AND serverId = :serverId AND time >= :time");
	$stmt->bindParam(':serverId', $serverId);
	$stmt->bindParam(':selectedProfile', $selectedProfile);
	//Join requests expire after 24 hours (config default), idk if theres an actual one, I just picked one.
	$stmt->bindParam(':time', $time, PDO::PARAM_INT);

	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $result[0];
}

function yggdrasil_sign($value)
{
	global $mcse_config;
	$private_key = openssl_pkey_get_private("file://" . $mcse_config["paths"]["yggdrasil_key"]["private"]);

	if (openssl_sign($value, $signature, $private_key, OPENSSL_ALGO_SHA1))
	{
		return $signature;
	}
	else{
		http_response_code(500);
		exit();
	}
}

function get_session_id($id)
{
	global $conn, $now;

	$sessionId = random_bytes(20);

	$stmt = $conn->prepare("INSERT INTO legacySessionIds (id, sessionId, time) VALUES (:id, :sessionId, :time)");
	$stmt->bindParam(':id', $id);
	$stmt->bindParam(':sessionId', $sessionId);
	$stmt->bindParam(':time', $now, PDO::PARAM_INT);

	$stmt->execute();

	return $sessionId;
}

function find_session_id($id, $sessionId)
{
	global $conn, $now, $mcse_config;

	$time = $now - $mcse_config["legacy_authentication"]["token_time"];

	$stmt = $conn->prepare("SELECT * FROM legacySessionIds WHERE id = :id AND sessionId = :sessionId AND time >= :time");
	$stmt->bindParam(':id', $id);
	$stmt->bindParam(':sessionId', $sessionId);
	$stmt->bindParam(':time', $time, PDO::PARAM_INT);
	$stmt->execute();

	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = $stmt->fetchAll();

	return $result[0];
}