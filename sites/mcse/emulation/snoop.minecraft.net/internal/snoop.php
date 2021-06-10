<?php
include "mcse_common.php";
global $conn, $now, $mcse_config;

if (!$mcse_config["snoop"]["enabled"])
	exit();

$version = intval($_GET["version"]);
$data = file_get_contents('php://input');
if(filter_var($_SERVER["REMOTE_ADDR"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
	$ipv4 = inet_pton($_SERVER["REMOTE_ADDR"]);
	$stmt = $conn->prepare("INSERT INTO snoop (time, ipv4, version, type, data) VALUES (:time, :ipv4, :version, :type, :data)");
	$stmt->bindParam(':ipv4', $ipv4);

}
else {
	$ipv6 = inet_pton($_SERVER["REMOTE_ADDR"]);
	$stmt = $conn->prepare("INSERT INTO snoop (time, ipv6, version, type, data) VALUES (:time, :ipv6, :version, :type, :data)");
	$stmt->bindParam(':ipv6', $ipv6);
}

$stmt->bindParam(':time', $now, PDO::PARAM_INT);
$stmt->bindParam(':version', $version, PDO::PARAM_INT);
$stmt->bindParam(':type', $type);
$stmt->bindParam(':data', $data);

$stmt->execute();

response_telemetry("", 200);
?>