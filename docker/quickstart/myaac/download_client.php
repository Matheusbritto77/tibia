<?php
/**
 * Direct Client Download Handler for Windows, macOS and Linux
 */

$platform = strtolower(trim($_GET['platform'] ?? $_GET['action'] ?? 'windows'));

$validPlatforms = [
	'windows' => ['file' => 'otclient-windows.zip', 'name' => 'Tibia-Client-Windows.zip'],
	'mac'     => ['file' => 'otclient-macos.zip',   'name' => 'Tibia-Client-macOS.zip'],
	'macos'   => ['file' => 'otclient-macos.zip',   'name' => 'Tibia-Client-macOS.zip'],
	'linux'   => ['file' => 'otclient-linux.zip',   'name' => 'Tibia-Client-Linux.zip'],
];

if (!isset($validPlatforms[$platform])) {
	$platform = 'windows';
}

$info = $validPlatforms[$platform];
$localPath = __DIR__ . '/downloads/' . $info['file'];

// Create downloads directory if missing
if (!is_dir(__DIR__ . '/downloads')) {
	@mkdir(__DIR__ . '/downloads', 0755, true);
}

// 1. If physical zip exists in downloads directory, serve it directly
if (file_exists($localPath) && filesize($localPath) > 0) {
	header('Content-Type: application/zip');
	header('Content-Disposition: attachment; filename="' . $info['name'] . '"');
	header('Content-Length: ' . filesize($localPath));
	header('Cache-Control: no-cache, must-revalidate');
	header('Pragma: no-cache');
	readfile($localPath);
	exit;
}

// 2. If no pre-packaged zip exists yet, generate a client launcher config zip dynamically
$zip = new ZipArchive();
$tempZipPath = tempnam(sys_get_temp_dir(), 'tibia_client_') . '.zip';

if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
	$configContent = "client-name = \"Canary Tibia Client\"\n";
	$configContent .= "ip = \"localhost\"\n";
	$configContent .= "port = 7171\n";
	$configContent .= "http-port = 8088\n";
	$configContent .= "platform = \"" . $platform . "\"\n";
	$configContent .= "version = \"15.25\"\n";

	$readmeContent = "========================================\n";
	$readmeContent .= " CANARY TIBIA CLIENT - " . strtoupper($platform) . "\n";
	$readmeContent .= "========================================\n\n";
	$readmeContent .= "To connect to your local server:\n";
	$readmeContent .= "1. Launch OTClient for " . ucfirst($platform) . ".\n";
	$readmeContent .= "2. Enter your account name and password created on the website.\n";
	$readmeContent .= "3. Connection endpoint: http://localhost:8088/login\n\n";
	$readmeContent .= "Enjoy playing!";

	$zip->addFromString('config.otclient', $configContent);
	$zip->addFromString('README.txt', $readmeContent);
	$zip->close();

	header('Content-Type: application/zip');
	header('Content-Disposition: attachment; filename="' . $info['name'] . '"');
	header('Content-Length: ' . filesize($tempZipPath));
	header('Cache-Control: no-cache, must-revalidate');
	header('Pragma: no-cache');
	readfile($tempZipPath);
	@unlink($tempZipPath);
	exit;
}

// Fallback response if ZipArchive is unavailable
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="client-info.txt"');
echo "Tibia Client for " . ucfirst($platform) . "\nServer: localhost:7171\nLogin URL: http://localhost:8088/login";
exit;
