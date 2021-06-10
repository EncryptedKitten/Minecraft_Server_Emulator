<?php
	include "mcse_common.php";
	global $mcse_config;

	header('Content-Type: application/json');

	$filename = $mcse_config["paths"]["yggdrasil_key"]["public"];

	$response = array(
		"yggdrasil_session_pubkey" => "data:application/x-x509-ca-cert;base64," . base64_encode(file_get_contents($filename)),
		"no_ssl" => true
	);

	echo json_encode($response);
?>