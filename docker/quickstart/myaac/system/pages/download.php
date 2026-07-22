<?php
/**
 * Official Tibia.com Download Client Page for MyAAC
 */
defined('MYAAC') or die('Direct access not allowed.');

$template_path = $template_path ?? (isset($config['template_path']) ? $config['template_path'] : 'templates/tibiacom');

// Handle direct client zip/dmg download
if (isset($_GET['action']) && $_GET['action'] === 'download') {
	$platform = strtolower(trim($_GET['platform'] ?? 'windows'));

	if ($platform === 'macos' || $platform === 'mac') {
		$targetFiles = ['otclient-macos.dmg', 'otclient-macos.zip', 'OTClient.app'];
		$downloadName = 'Tibia-Client-macOS.dmg';
		$contentType = 'application/x-apple-diskimage';
	} elseif ($platform === 'linux') {
		$targetFiles = ['otclient-linux.zip', 'otclient-windows.zip'];
		$downloadName = 'Tibia-Client-Linux.zip';
		$contentType = 'application/zip';
	} else {
		$targetFiles = ['otclient.exe', 'otclient_gl_x64.exe', 'otclient-windows.zip'];
		$downloadName = 'otclient.exe';
		$contentType = 'application/x-msdownload';
	}

	$searchPaths = [
		'/var/www/html/downloads/',
		__DIR__ . '/../../downloads/',
		__DIR__ . '/../../../client/',
		__DIR__ . '/../../client/',
		'/var/www/html/system/downloads/',
	];

	foreach ($targetFiles as $targetFile) {
		foreach ($searchPaths as $basePath) {
			$localPath = rtrim($basePath, '/') . '/' . $targetFile;
			if (file_exists($localPath) && filesize($localPath) > 0) {
				if (str_ends_with($targetFile, '.exe')) {
					$downloadName = 'otclient.exe';
					$contentType = 'application/x-msdownload';
				} elseif (str_ends_with($targetFile, '.zip')) {
					$downloadName = 'Tibia-Client-' . ucfirst($platform) . '.zip';
					$contentType = 'application/zip';
				} elseif (str_ends_with($targetFile, '.dmg')) {
					$downloadName = 'Tibia-Client-macOS.dmg';
					$contentType = 'application/x-apple-diskimage';
				}
				while (ob_get_level() > 0) {
					@ob_end_clean();
				}
				if (ini_get('zlib.output_compression')) {
					@ini_set('zlib.output_compression', 'Off');
				}
				header('Content-Type: ' . $contentType);
				header('Content-Disposition: attachment; filename="' . $downloadName . '"');
				header('Content-Length: ' . filesize($localPath));
				header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Expires: 0');
				readfile($localPath);
				exit;
			}
		}
	}

	// Direct download fallback from GitHub Release Assets
	$githubReleases = [
		'macos' => 'https://github.com/Matheusbritto77/tibia/releases/download/v4.1.0/otclient-macos.dmg',
		'mac' => 'https://github.com/Matheusbritto77/tibia/releases/download/v4.1.0/otclient-macos.dmg',
		'windows' => 'https://github.com/Matheusbritto77/tibia/releases/download/v4.1.0/otclient-windows.zip',
		'linux' => 'https://github.com/Matheusbritto77/tibia/releases/download/v4.1.0/otclient-windows.zip',
	];

	if (isset($githubReleases[$platform])) {
		while (ob_get_level() > 0) {
			@ob_end_clean();
		}
		header('Location: ' . $githubReleases[$platform]);
		exit;
	}

	if (class_exists('ZipArchive')) {
		$zip = new ZipArchive();
		$tmp = tempnam(sys_get_temp_dir(), 'tibia_') . '.zip';
		if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
			$clientSrc = null;
			$possibleSrcs = [
				'/var/www/html/downloads/otclient',
				__DIR__ . '/../../downloads/otclient',
				__DIR__ . '/../../../client/otclient',
				__DIR__ . '/../../client/otclient',
			];
			foreach ($possibleSrcs as $src) {
				if (is_dir($src)) {
					$clientSrc = realpath($src);
					break;
				}
			}

			if ($clientSrc) {
				$files = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator($clientSrc, RecursiveDirectoryIterator::SKIP_DOTS),
					RecursiveIteratorIterator::LEAVES_ONLY
				);
				foreach ($files as $file) {
					if (!$file->isDir()) {
						$filePath = $file->getRealPath();
						$relativePath = 'otclient/' . substr($filePath, strlen($clientSrc) + 1);
						$zip->addFile($filePath, $relativePath);
					}
				}
			} else {
				$zip->addFromString('config.otclient', "ip = \"localhost\"\nport = 7171\nhttp-port = 8088\nplatform = \"{$platform}\"\nversion = \"15.25\"\n");
				$zip->addFromString('README.txt', "astarOT CLIENT - " . strtoupper($platform) . "\n\n1. Launch astarOT Client for " . ucfirst($platform) . ".\n2. Login URL: http://localhost:8088/login\n");
			}
			$zip->close();

			while (ob_get_level() > 0) {
				@ob_end_clean();
			}
			if (ini_get('zlib.output_compression')) {
				@ini_set('zlib.output_compression', 'Off');
			}
			header('Content-Type: application/zip');
			header('Content-Disposition: attachment; filename="' . $downloadName . '"');
			header('Content-Length: ' . filesize($tmp));
			header('Cache-Control: no-cache, must-revalidate');
			readfile($tmp);
			@unlink($tmp);
			exit;
		}
	}

	header('Content-Type: text/plain');
	header('Content-Disposition: attachment; filename="astarOT-Client-' . ucfirst($platform) . '.txt"');
	echo "astarOT Client for " . ucfirst($platform) . "\nEndpoint: http://localhost:8088/login";
	exit;
}
?>
<div class="TableContainer">
	<div class="CaptionContainer">
		<div class="CaptionInnerContainer">
			<span class="CaptionEdgeLeftTop" style="background-image:url(<?= $template_path ?>/images/global/content/box-frame-edge.gif);"></span>
			<span class="CaptionEdgeRightTop" style="background-image:url(<?= $template_path ?>/images/global/content/box-frame-edge.gif);"></span>
			<span class="CaptionBorderTop" style="background-image:url(<?= $template_path ?>/images/global/content/table-headline-border.gif);"></span>
			<span class="CaptionVerticalLeft" style="background-image:url(<?= $template_path ?>/images/global/content/box-frame-vertical.gif);"></span>
			<div class="Text">Download Client</div>
			<span class="CaptionVerticalRight" style="background-image:url(<?= $template_path ?>/images/global/content/box-frame-vertical.gif);"></span>
			<span class="CaptionBorderBottom" style="background-image:url(<?= $template_path ?>/images/global/content/table-headline-border.gif);"></span>
			<span class="CaptionEdgeLeftBottom" style="background-image:url(<?= $template_path ?>/images/global/content/box-frame-edge.gif);"></span>
			<span class="CaptionEdgeRightBottom" style="background-image:url(<?= $template_path ?>/images/global/content/box-frame-edge.gif);"></span>
		</div>
	</div>
	<div class="TableContentContainer">
		<table class="TableContent" width="100%" style="border:1px solid #faf0d7;">
			<tr>
				<td style="padding: 12px; background-color: #fff6e4;">

					<!-- Official Tibia Client (Windows Primary) -->
					<div class="TableShadowContainer" style="margin-bottom: 16px;">
						<div class="TableShadowTopLeft" style="background-image:url(<?= $template_path ?>/images/global/content/table-shadow-tl.gif);"></div>
						<div class="TableShadowTopRight" style="background-image:url(<?= $template_path ?>/images/global/content/table-shadow-tr.gif);"></div>
						<div class="InnerTableContainer" style="background-color: #d4c0a1; border: 1px solid #795d37; padding: 2px;">
							<table class="TableContent" width="100%">
								<tr class="TableHeadRow">
									<th style="background-color: #795d37; color: #fff; text-align: center; font-size: 15px; font-weight: bold; padding: 7px; font-family: Verdana, Arial, sans-serif;">Official astarOT Client</th>
								</tr>
								<tr>
									<td style="text-align: center; padding: 22px; background-color: #e7d8c1;">
										<div style="margin-bottom: 12px;">
											<a href="?subtopic=download&action=download&platform=windows" style="display:inline-block; text-decoration:none;">
												<img src="<?= $template_path ?>/images/header/tibia-download-icon.png" alt="Windows Client" style="border:0; max-height: 64px; vertical-align: middle;" />
											</a>
										</div>
										<div style="font-weight: bold; font-size: 15px; margin-bottom: 6px;">
											<a href="?subtopic=download&action=download&platform=windows" style="color: #002e97; text-decoration: underline;">Download astarOT<br />Windows Client</a>
										</div>
										<div style="font-size: 11px;">
											[<a href="#system_requirements" style="color: #002e97;" onclick="alert('Windows 10 / 11 (64-bit)\nDirectX 11/12 & OpenGL 4.5\n4 GB RAM | 1 GB Free Disk Space\nAuto-Updater & In-Game Registration'); return false;">system requirements</a>]
										</div>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Supported Multiplatform Clients (macOS & Linux) -->
					<div class="TableShadowContainer" style="margin-bottom: 16px;">
						<div class="InnerTableContainer" style="background-color: #d4c0a1; border: 1px solid #795d37; padding: 2px;">
							<table class="TableContent" width="100%">
								<tr class="TableHeadRow">
									<th colspan="2" style="background-color: #795d37; color: #fff; text-align: center; font-size: 14px; font-weight: bold; padding: 7px; font-family: Verdana, Arial, sans-serif;">Supported Multiplatform Clients</th>
								</tr>
								<tr>
									<td width="50%" style="text-align: center; padding: 20px; background-color: #e7d8c1; border-right: 1px solid #b8a282;">
										<div style="margin-bottom: 10px;">
											<a href="?subtopic=download&action=download&platform=macos" style="display:inline-block; text-decoration:none;">
												<img src="<?= $template_path ?>/images/header/tibia-download-icon.png" alt="macOS Client" style="border:0; max-height: 52px; vertical-align: middle;" />
											</a>
										</div>
										<div style="font-weight: bold; font-size: 14px; margin-bottom: 6px;">
											<a href="?subtopic=download&action=download&platform=macos" style="color: #002e97; text-decoration: underline;">Download astarOT<br />macOS Client</a>
										</div>
										<div style="font-size: 11px;">
											[<a href="#macos_info" style="color: #002e97;" onclick="alert('macOS Monterey, Ventura, Sonoma, Sequoia\nUniversal Binary (Apple Silicon M1/M2/M3/M4 & Intel)\nMetal Graphics Acceleration'); return false;">information</a>]
										</div>
									</td>
									<td width="50%" style="text-align: center; padding: 20px; background-color: #e7d8c1;">
										<div style="margin-bottom: 10px;">
											<a href="?subtopic=download&action=download&platform=linux" style="display:inline-block; text-decoration:none;">
												<img src="<?= $template_path ?>/images/header/tibia-download-icon.png" alt="Linux Client" style="border:0; max-height: 52px; vertical-align: middle;" />
											</a>
										</div>
										<div style="font-weight: bold; font-size: 14px; margin-bottom: 6px;">
											<a href="?subtopic=download&action=download&platform=linux" style="color: #002e97; text-decoration: underline;">Download astarOT<br />Linux Client</a>
										</div>
										<div style="font-size: 11px;">
											[<a href="#linux_info" style="color: #002e97;" onclick="alert('Ubuntu 20.04+, Debian, Fedora (64-bit)\nOpenGL 2.1+ / Vulkan acceleration'); return false;">information</a>]
										</div>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Disclaimer Box -->
					<div class="TableShadowContainer">
						<div class="InnerTableContainer" style="background-color: #d4c0a1; border: 1px solid #795d37; padding: 2px;">
							<table class="TableContent" width="100%">
								<tr class="TableHeadRow">
									<th style="background-color: #795d37; color: #fff; text-align: left; font-size: 12px; font-weight: bold; padding: 5px 10px; font-family: Verdana, Arial, sans-serif;">Disclaimer</th>
								</tr>
								<tr>
									<td style="font-size: 11px; padding: 10px; background-color: #e7d8c1; color: #3a3a3a; line-height: 1.45;">
										The software and any related documentation is provided "as is" without warranty of any kind. The entire risk arising out of use of the software remains with you. In no event shall astarOT or Britto Dev be liable for any damages to your computer or loss of data.
									</td>
								</tr>
							</table>
						</div>
					</div>

				</td>
			</tr>
		</table>
	</div>
</div>