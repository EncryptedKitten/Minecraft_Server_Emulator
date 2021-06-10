<?php
include "mcse_common.php";
include site_dir() . "/internal/security_questions.php";

header('Content-Type: application/json');

check_request("POST");

$accessTokenData = check_authentication();

$user = get_user_by_id($accessTokenData["id"]);

$security_questions = security_questions();

$response = array(
	array(
		"answer" => array(
			"id" => $user["security_answer_1"]
		),
		"question" => array(
			"id" => $user["security_question_1"],
			"question" => $security_questions[$user["security_question_1"]]
		)
	),
	array(
		"answer" => array(
			"id" => $user["security_answer_2"]
		),
		"question" => array(
			"id" => $user["security_question_2"],
			"question" => $security_questions[$user["security_question_2"]]
		)
	),
	array(
		"answer" => array(
			"id" => $user["security_answer_3"]
		),
		"question" => array(
			"id" => $user["security_question_3"],
			"question" => $security_questions[$user["security_question_3"]]
		)
	)
);

response_telemetry(json_encode($response), 200);
echo json_encode($response);
?>