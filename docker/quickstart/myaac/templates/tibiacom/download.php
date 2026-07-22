<?php
/**
 * Official Tibia.com Download Client Page for MyAAC
 */
defined('MYAAC') or die('Direct access not allowed.');

$template_path = $template_path ?? ($config['template_path'] ?? 'templates/tibiacom');
?>
<div class="TableContainer">
	<div class="CaptionContainer">
		<div class="CaptionInnerContainer">
			<span class="CaptionEdgeLeftTop"
				style="background-image:url(<?= $template_path ?>/images/global/content/box-frame-edge.gif);"></span>
			<span class="CaptionEdgeRightTop"
				style="background-image:url(<?= $template_path ?>/images/global/content/box-frame-edge.gif);"></span>
			<span class="CaptionBorderTop"
				style="background-image:url(<?= $template_path ?>/images/global/content/table-headline-border.gif);"></span>
			<span class="CaptionVerticalLeft"
				style="background-image:url(<?= $template_path ?>/images/global/content/box-frame-vertical.gif);"></span>
			<div class="Text">Download Client</div>
			<span class="CaptionVerticalRight"
				style="background-image:url(<?= $template_path ?>/images/global/content/box-frame-vertical.gif);"></span>
			<span class="CaptionBorderBottom"
				style="background-image:url(<?= $template_path ?>/images/global/content/table-headline-border.gif);"></span>
			<span class="CaptionEdgeLeftBottom"
				style="background-image:url(<?= $template_path ?>/images/global/content/box-frame-edge.gif);"></span>
			<span class="CaptionEdgeRightBottom"
				style="background-image:url(<?= $template_path ?>/images/global/content/box-frame-edge.gif);"></span>
		</div>
	</div>
	<div class="TableContentContainer">
		<table class="TableContent" width="100%" style="border:1px solid #faf0d7;">
			<tr>
				<td style="padding: 12px; background-color: #fff6e4;">

					<!-- Official Tibia Client (Windows Primary) -->
					<div class="TableShadowContainer" style="margin-bottom: 16px;">
						<div class="TableShadowTopLeft"
							style="background-image:url(<?= $template_path ?>/images/global/content/table-shadow-tl.gif);">
						</div>
						<div class="TableShadowTopRight"
							style="background-image:url(<?= $template_path ?>/images/global/content/table-shadow-tr.gif);">
						</div>
						<div class="InnerTableContainer"
							style="background-color: #d4c0a1; border: 1px solid #795d37; padding: 2px;">
							<table class="TableContent" width="100%">
								<tr class="TableHeadRow">
									<th
										style="background-color: #795d37; color: #fff; text-align: center; font-size: 15px; font-weight: bold; padding: 7px; font-family: Verdana, Arial, sans-serif;">
										Official Tibia Client</th>
								</tr>
								<tr>
									<td style="text-align: center; padding: 22px; background-color: #e7d8c1;">
										<div style="margin-bottom: 12px;">
											<a href="/downloads/otclient-windows.zip" style="display:inline-block; text-decoration:none;">
												<img src="<?= $template_path ?>/images/header/tibia-logo-artwork-top.gif" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';" alt="Windows Client" style="border:0; max-height: 42px; vertical-align: middle;" />
												<svg width="42" height="42" viewBox="0 0 24 24" fill="#795d37" style="display:none; vertical-align: middle;"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>
											</a>
										</div>
										<div style="font-weight: bold; font-size: 15px; margin-bottom: 6px;">
											<a href="/downloads/otclient-windows.zip"
												style="color: #002e97; text-decoration: underline;">Download
												Tibia<br />Windows Client</a>
										</div>
										<div style="font-size: 11px;">
											[<a href="#system_requirements" style="color: #002e97;"
												onclick="alert('Windows 10 / 11 (64-bit)\nDirectX 11/12 & OpenGL 4.5\n4 GB RAM | 1 GB Free Disk Space\nAuto-Updater & In-Game Registration'); return false;">system
												requirements</a>]
										</div>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Supported Multiplatform Clients (macOS & Linux) -->
					<div class="TableShadowContainer" style="margin-bottom: 16px;">
						<div class="InnerTableContainer"
							style="background-color: #d4c0a1; border: 1px solid #795d37; padding: 2px;">
							<table class="TableContent" width="100%">
								<tr class="TableHeadRow">
									<th colspan="2"
										style="background-color: #795d37; color: #fff; text-align: center; font-size: 14px; font-weight: bold; padding: 7px; font-family: Verdana, Arial, sans-serif;">
										Supported Multiplatform Clients</th>
								</tr>
								<tr>
									<td width="50%"
										style="text-align: center; padding: 20px; background-color: #e7d8c1; border-right: 1px solid #b8a282;">
										<div style="font-weight: bold; font-size: 14px; margin-bottom: 6px;">
											<a href="/downloads/otclient-macos.zip"
												style="color: #002e97; text-decoration: underline;">Download
												Tibia<br />macOS Client</a>
										</div>
										<div style="font-size: 11px;">
											[<a href="#macos_info" style="color: #002e97;"
												onclick="alert('macOS Monterey, Ventura, Sonoma, Sequoia\nUniversal Binary (Apple Silicon M1/M2/M3/M4 & Intel)\nMetal Graphics Acceleration'); return false;">information</a>]
										</div>
									</td>
									<td width="50%"
										style="text-align: center; padding: 20px; background-color: #e7d8c1;">
										<div style="font-weight: bold; font-size: 14px; margin-bottom: 6px;">
											<a href="/downloads/otclient-windows.zip"
												style="color: #002e97; text-decoration: underline;">Download
												Tibia<br />Linux Client</a>
										</div>
										<div style="font-size: 11px;">
											[<a href="#linux_info" style="color: #002e97;"
												onclick="alert('Ubuntu 20.04+, Debian, Fedora (64-bit)\nOpenGL 2.1+ / Vulkan acceleration'); return false;">information</a>]
										</div>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Disclaimer Box -->
					<div class="TableShadowContainer">
						<div class="InnerTableContainer"
							style="background-color: #d4c0a1; border: 1px solid #795d37; padding: 2px;">
							<table class="TableContent" width="100%">
								<tr class="TableHeadRow">
									<th
										style="background-color: #795d37; color: #fff; text-align: left; font-size: 12px; font-weight: bold; padding: 5px 10px; font-family: Verdana, Arial, sans-serif;">
										Disclaimer</th>
								</tr>
								<tr>
									<td
										style="font-size: 11px; padding: 10px; background-color: #e7d8c1; color: #3a3a3a; line-height: 1.45;">
										The software and any related documentation is provided "as is" without warranty
										of any kind. The entire risk arising out of use of the software remains with
										you. In no event shall CipSoft GmbH or OpenTibiaBR be liable for any damages to
										your computer or loss of data.
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