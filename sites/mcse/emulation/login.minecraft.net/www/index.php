<?php
$telemetry_no_get = TRUE;
include "mcse_common.php";
global $mcse_config;

header('Content-Type: application/x-www-form-urlencoded');

if(is_null($_GET["user"]) or is_null($_GET["password"]) or is_null($_GET["version"]))
{
	legacy_minecraft_error("Bad response", 400);
}

if($mcse_config["legacy_authentication"]["version_check"] and intval($_GET["version"]) < 13)
{
	legacy_minecraft_error("Old version", 400);
}

$user = get_user_by_username($_GET["username"]);

if (!password_verify($_GET["password"], $user["password"]))
{
	legacy_minecraft_error("Bad login", 401);
}

if ($user["demoUser"] == 1)
{
	legacy_minecraft_error("User not premium", 401);
}

//https://wiki.vg/index.php?title=Legacy_Authentication&oldid=16139
$version = 1343825972000; //Unix timestamp for current version, legacy auth is dead so I don't know what it is.
$download_ticket = "deprecated"; //Deprecated legacy auth download ticket for new launcher versions, idk what the actual one would be because again, legacy auth is dead.
$username = $user["username"];
$sessionId = bin2hex(get_session_id($user["id"]));
$uid = $user["selectedProfile"]; //Unused, IDK if its the player of the profile uuid, ill just said the profile one because it' more useful.

response_telemetry($version . ":" . $download_ticket . ":" . $username . ":" . $sessionId . ":" . $uid, 200);
echo $version . ":" . $download_ticket . ":" . $username . ":" . $sessionId . ":" . $uid;