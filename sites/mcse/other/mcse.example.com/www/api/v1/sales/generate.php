<?php
include "mcse_common.php";
global $conn, $mcse_config;

$stmt = $conn->prepare("TRUNCATE TABLE `sales`");
$stmt->execute();

$stmt = $conn->prepare("INSERT INTO `sales` (`time`, `count`, `type`) VALUES
(179608881, 37123689, 'item_sold_minecraft'),
(1620808962, 12764, 'item_sold_minecraft'),
(179608881, 4782079, 'prepaid_card_redeemed_minecraft'),
(1620808962, 0, 'prepaid_card_redeemed_minecraft'),
(179608881, 41722, 'item_sold_cobalt'),
(1620808962, 0, 'item_sold_cobalt'),
(179608881, 132261, 'item_sold_scrolls'),
(1620808962, 0, 'item_sold_scrolls'),
(179608881, 1, 'prepaid_card_redeemed_cobalt'),
(1620808962, 0, 'prepaid_card_redeemed_cobalt'),
(179608881, 275189, 'item_sold_dungeons'),
(1620808962, 87, 'item_sold_dungeons')");
$stmt->execute();

http_response_code(204);