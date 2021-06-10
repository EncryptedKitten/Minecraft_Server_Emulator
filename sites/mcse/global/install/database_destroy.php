<?php
include "mcse_common.php";
global $conn, $tables;

foreach($tables as $table)
{
	$stmt = $conn->prepare("DROP TABLE `" . $table . "`");
	$stmt->execute();
}
