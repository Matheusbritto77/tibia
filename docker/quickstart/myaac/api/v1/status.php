<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

require_once __DIR__ . '/../../common.php';

$online = false;
$players = 0;
$serverName = 'astarOT';

try {
	$status = @fetch_server_status();
	if (is_array($status)) {
		$online = (bool) ($status['online'] ?? false);
		$players = (int) ($status['players'] ?? 0);
	} else {
		$db = $GLOBALS['db'] ?? null;
		if ($db) {
			$players = (int) ($db->query("SELECT COUNT(*) FROM `players` WHERE `online` > 0")->fetchColumn() ?? 0);
			$online = true;
		}
	}
} catch (Throwable $e) {
	// Fallback
}

echo json_encode([
	'success' => true,
	'online' => $online,
	'players' => $players,
	'server_name' => $serverName,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
