<?php
include "mcse_common.php";
global $conn, $tables;

$accessTokens = <<<EOT
CREATE TABLE `accessTokens` (
 `id` binary(16) DEFAULT NULL,
 `accessToken` binary(32) DEFAULT NULL,
 `clientToken` binary(16) DEFAULT NULL,
 `time` bigint(20) NOT NULL,
 UNIQUE KEY `accessToken` (`accessToken`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOT;

$blockedProfiles = <<<EOT
CREATE TABLE `blockedProfiles` (
 `blockerId` binary(16) NOT NULL,
 `blockedId` binary(16) NOT NULL,
 PRIMARY KEY (`blockerId`,`blockedId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOT;

$blockedservers = <<<EOT
CREATE TABLE `blockedservers` (
 `server` tinytext DEFAULT NULL,
 `serverHash` tinytext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOT;

$hash_id = <<<EOT
CREATE TABLE `hash_id` (
 `sha256` binary(32) NOT NULL,
 `uuid` binary(16) NOT NULL,
 UNIQUE KEY `sha256` (`sha256`),
 UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOT;

$hasJoined = <<<EOT
CREATE TABLE `hasJoined` (
 `selectedProfile` binary(16) DEFAULT NULL,
 `serverId` tinytext DEFAULT NULL,
 `time` bigint(20) NOT NULL,
 `ipv4` binary(4) DEFAULT NULL,
 `ipv6` binary(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOT;

$joinserver_legacy = <<<EOT
CREATE TABLE `joinserver_legacy` (
 `selectedProfile` binary(16) NOT NULL,
 `serverId` tinytext NOT NULL,
 `time` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOT;

$legacySessionIds = <<<EOT
CREATE TABLE `legacySessionIds` (
 `id` binary(16) NOT NULL,
 `sessionId` binary(20) NOT NULL,
 `time` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOT;

$profiles = <<<EOT
CREATE TABLE `profiles` (
 `id` binary(16) DEFAULT NULL,
 `profileId` binary(16) DEFAULT NULL,
 `name` varchar(255) DEFAULT NULL,
 `skin` binary(32) DEFAULT NULL,
 `cape` binary(32) DEFAULT NULL,
 `model` tinytext NOT NULL,
 `timestamp_start` bigint(20) NOT NULL,
 `timestamp_end` bigint(20) NOT NULL,
 UNIQUE KEY `profileId_timestamp_start` (`profileId`,`timestamp_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOT;

$realms = <<<EOT
CREATE TABLE `realms` (
 `id` bigint(20) NOT NULL AUTO_INCREMENT,
 `remoteSubscriptionId` binary(16) NOT NULL,
 `ownerUUID` binary(16) NOT NULL,
 `name` text DEFAULT NULL,
 `motd` text DEFAULT NULL,
 `state` text NOT NULL,
 `time` bigint(20) NOT NULL,
 `trial` tinyint(1) NOT NULL,
 `worldType` text NOT NULL,
 `minigameId` binary(16) DEFAULT NULL,
 `activeSlot` tinyint(4) NOT NULL,
 `paidTime` bigint(20) NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `id` (`id`),
 UNIQUE KEY `remoteSubscriptionId` (`remoteSubscriptionId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8
EOT;

$realmsBackingServers = <<<EOT
CREATE TABLE `realmsBackingServers` (
 `remoteSubscriptionId` binary(16) NOT NULL,
 `port` int(11) NOT NULL,
 `rconPort` int(11) NOT NULL,
 `address` text NOT NULL,
 `rconPassword` text NOT NULL,
 PRIMARY KEY (`remoteSubscriptionId`),
 UNIQUE KEY `remoteSubscriptionId` (`remoteSubscriptionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOT;

$realmsInvites = <<<EOT
CREATE TABLE `realmsInvites` (
 `remoteSubscriptionId` binary(16) NOT NULL,
 `profileId` binary(16) NOT NULL,
 `accepted` tinyint(1) NOT NULL,
 `time` bigint(20) NOT NULL,
 `invitationId` int(11) NOT NULL AUTO_INCREMENT,
 PRIMARY KEY (`invitationId`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8
EOT;

$realmsMinigames = <<<EOT
CREATE TABLE `realmsMinigames` (
 `minigameId` binary(16) NOT NULL,
 `minigameName` text NOT NULL,
 `minigameImage` binary(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOT;
$realmsOps = <<<EOT
CREATE TABLE `realmsOps` (
 `remoteSubscriptionId` binary(16) NOT NULL,
 `profileId` binary(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOT;

$sales = <<<EOT
CREATE TABLE `sales` (
 `time` bigint(20) NOT NULL,
 `count` bigint(20) NOT NULL,
 `type` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOT;

$security_answers = <<<EOT
CREATE TABLE `security_answers` (
 `id` bigint(20) NOT NULL AUTO_INCREMENT,
 `answer` varbinary(255) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOT;

$security_ips = <<<EOT
CREATE TABLE `security_ips` (
 `ipv4` binary(4) DEFAULT NULL,
 `ipv6` binary(16) DEFAULT NULL,
 `time` bigint(20) NOT NULL,
 `id` binary(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOT;

$snoop = <<<EOT
CREATE TABLE `snoop` (
 `time` bigint(20) NOT NULL,
 `ipv4` binary(4) NOT NULL,
 `ipv6` binary(16) NOT NULL,
 `version` tinyint(4) NOT NULL,
 `type` tinytext NOT NULL,
 `data` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOT;

$telemetry = <<<EOT
CREATE TABLE `telemetry` (
 `telemetry_id` binary(16) NOT NULL,
 `GET` text DEFAULT NULL,
 `POST` text DEFAULT NULL,
 `COOKIE` text DEFAULT NULL,
 `ipv4` binary(4) DEFAULT NULL,
 `ipv6` binary(16) DEFAULT NULL,
 `time` bigint(20) DEFAULT NULL,
 `telemetry_level` int(11) DEFAULT NULL,
 `response` text DEFAULT NULL,
 `response_code` int(11) DEFAULT NULL,
 `SERVER` text NOT NULL,
 `http_host` text DEFAULT NULL,
 `http_path` text DEFAULT NULL,
 `debug` text DEFAULT NULL,
 PRIMARY KEY (`telemetry_id`),
 UNIQUE KEY `telemetry_id` (`telemetry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOT;

$users = <<<EOT
CREATE TABLE `users` (
 `username` varchar(255) DEFAULT NULL,
 `password` varchar(255) DEFAULT NULL,
 `id` binary(16) DEFAULT NULL,
 `selectedProfile` binary(16) DEFAULT NULL,
 `security_question_1` tinyint(4) NOT NULL,
 `security_answer_1` text NOT NULL,
 `security_question_2` tinyint(4) NOT NULL,
 `security_answer_2` text NOT NULL,
 `security_question_3` tinyint(4) NOT NULL,
 `security_answer_3` text NOT NULL,
 `dateOfBirth` bigint(20) NOT NULL,
 `email` varchar(255) NOT NULL,
 `emailVerified` tinyint(1) NOT NULL,
 `legacyUser` tinyint(1) NOT NULL,
 `demoUser` tinyint(1) NOT NULL,
 `verifiedByParent` tinyint(1) NOT NULL,
 `onlineChatPrivilege` tinyint(1) DEFAULT NULL,
 `multiplayerServerPrivilege` tinyint(1) DEFAULT NULL,
 `multiplayerRealmsPrivilege` tinyint(1) DEFAULT NULL,
 `telemetryPrivilege` tinyint(1) DEFAULT NULL,
 `realmsTrialUsed` tinyint(1) NOT NULL,
 `realmsTosAgreed` tinyint(1) NOT NULL,
 UNIQUE KEY `username` (`username`),
 UNIQUE KEY `id` (`id`),
 UNIQUE KEY `selectedProfile` (`selectedProfile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8"
EOT;

foreach($tables as $table)
{
	$stmt = $conn->prepare($GLOBALS[$table]);
	$stmt->execute();
}
