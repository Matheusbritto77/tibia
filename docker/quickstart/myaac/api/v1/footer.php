<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

require_once __DIR__ . '/../../common.php';

$visitors = 1;
$views = 1;

if (class_exists('OTS_Buffer') || function_exists('setting')) {
	try {
		$db = $GLOBALS['db'] ?? null;
		if ($db) {
			$visitors = (int) ($db->query("SELECT COUNT(*) FROM `myaac_visitors` WHERE `time` > " . (time() - 300))->fetchColumn() ?? 1);
			$views = (int) ($db->query("SELECT `value` FROM `myaac_config` WHERE `name` = 'views'")->fetchColumn() ?? 1);
		}
	} catch (Throwable $e) {
		// Fallback to defaults
	}
}

echo json_encode([
	'success' => true,
	'visitors' => max(1, $visitors),
	'page_views' => max(1, $views),
	'powered_by' => 'Britto Dev',
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
