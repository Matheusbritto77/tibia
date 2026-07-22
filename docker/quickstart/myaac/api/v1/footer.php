<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

require_once __DIR__ . '/../../common.php';

$visitors = 1;
$views = 1;
$onlinePlayers = 0;
$isOnline = false;

try {
	$db = $GLOBALS['db'] ?? null;
	if ($db) {
		$visitors = (int) ($db->query("SELECT COUNT(*) FROM `myaac_visitors` WHERE `time` > " . (time() - 300))->fetchColumn() ?? 1);
		$views = (int) ($db->query("SELECT `value` FROM `myaac_config` WHERE `name` = 'views'")->fetchColumn() ?? 1);
	}
	$status = @fetch_server_status();
	if (is_array($status)) {
		$isOnline = (bool) ($status['online'] ?? false);
		$onlinePlayers = (int) ($status['players'] ?? 0);
	} else if ($db) {
		$onlinePlayers = (int) ($db->query("SELECT COUNT(*) FROM `players` WHERE `online` > 0")->fetchColumn() ?? 0);
		$isOnline = true;
	}
} catch (Throwable $e) {
	// Fallback
}

echo json_encode([
	'success' => true,
	'visitors' => max(1, $visitors),
	'page_views' => max(1, $views),
	'players_online' => $onlinePlayers,
	'server_online' => $isOnline,
	'powered_by' => 'Britto Dev',
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
