<?php
declare(strict_types=1);

function get_server_stats(): array {
    $host = getenv('CANARY_DB_HOST') ?: 'db';
    $port = getenv('CANARY_DB_PORT') ?: '3306';
    $name = getenv('CANARY_DB_NAME') ?: 'canary';
    $user = getenv('CANARY_DB_USER') ?: 'canary';
    $password = getenv('CANARY_DB_PASSWORD') ?: 'canary';

    $playersOnline = 0;
    $topPlayers = [];
    $boostedCreature = 'Dragon Lord';
    $boostedBoss = 'Gaz\'haragoth';

    try {
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        // Online count
        $stmt = $pdo->query("SELECT COUNT(*) FROM players WHERE online > 0");
        $playersOnline = (int)$stmt->fetchColumn();

        // Top players
        $stmt = $pdo->query("SELECT name, level, vocation FROM players WHERE group_id < 3 ORDER BY level DESC, experience DESC LIMIT 5");
        $topPlayers = $stmt->fetchAll();

        // Boosted creature
        if ($stmtBC = $pdo->query("SELECT boostname FROM boosted_creature LIMIT 1")) {
            if ($bc = $stmtBC->fetchColumn()) {
                $boostedCreature = $bc;
            }
        }

        // Boosted boss
        if ($stmtBB = $pdo->query("SELECT boostname FROM boosted_boss LIMIT 1")) {
            if ($bb = $stmtBB->fetchColumn()) {
                $boostedBoss = $bb;
            }
        }

    } catch (Throwable $e) {
        // Database fallback if connecting before setup
    }

    if (empty($topPlayers)) {
        $topPlayers = [
            ['name' => 'Eternal Knight', 'level' => 750, 'vocation' => 4],
            ['name' => 'Sorcerer Supreme', 'level' => 710, 'vocation' => 1],
            ['name' => 'Elder Saint', 'level' => 695, 'vocation' => 2],
            ['name' => 'Royal Deadeye', 'level' => 680, 'vocation' => 3],
            ['name' => 'Vanguard Warrior', 'level' => 650, 'vocation' => 4],
        ];
    }

    return [
        'online' => $playersOnline,
        'topPlayers' => $topPlayers,
        'boostedCreature' => $boostedCreature,
        'boostedBoss' => $boostedBoss,
    ];
}

$stats = get_server_stats();
$vocations = [0 => 'No Vocation', 1 => 'Sorcerer', 2 => 'Druid', 3 => 'Paladin', 4 => 'Knight', 5 => 'Master Sorcerer', 6 => 'Elder Druid', 7 => 'Royal Paladin', 8 => 'Elite Knight'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tibia - OpenTibiaBR Canary Official Server</title>
    <link rel="stylesheet" href="/templates/modern/style.css">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
</head>
<body>

    <!-- Header Banner -->
    <header class="top-header">
        <div class="top-header-inner">
            <a href="/" class="tibia-logo">
                <div class="logo-shield">C</div>
                <div class="logo-text">TIBIA <span>CANARY</span></div>
            </a>

            <div class="server-status-pill">
                <div class="status-dot"></div>
                <span>Server Online &nbsp;|&nbsp; <?= $stats['online'] ?> Players Online</span>
            </div>
        </div>
    </header>

    <!-- Main Layout (3 Columns: Left Menu | Main Content | Right Sidebar) -->
    <div class="tibia-layout">

        <!-- LEFT COLUMN: Tibia.com Navigation Sidebar -->
        <aside>
            <div class="tibia-box">
                <div class="tibia-box-header">
                    <div class="tibia-box-title">
                        <span>📜 Navigation</span>
                    </div>
                </div>
                <div class="tibia-box-content" style="padding: 10px;">
                    <nav class="sidebar-nav">
                        <div class="menu-category">News</div>
                        <a href="/" class="menu-item active">
                            <span class="menu-item-icon">📰</span>
                            <span>Latest News</span>
                        </a>
                        <a href="#news-ticker" class="menu-item">
                            <span class="menu-item-icon">📌</span>
                            <span>News Ticker</span>
                        </a>

                        <div class="menu-category">Account</div>
                        <a href="http://209.126.81.68:8080/create_account.php" class="menu-item">
                            <span class="menu-item-icon">✨</span>
                            <span>Create Account</span>
                        </a>
                        <a href="http://209.126.81.68:8080/?subtopic=accountmanagement" class="menu-item">
                            <span class="menu-item-icon">🔑</span>
                            <span>Account Manager</span>
                        </a>

                        <div class="menu-category">Community</div>
                        <a href="#highscores" class="menu-item">
                            <span class="menu-item-icon">🏆</span>
                            <span>Highscores</span>
                        </a>
                        <a href="#boosted" class="menu-item">
                            <span class="menu-item-icon">🔥</span>
                            <span>Boosted Creatures</span>
                        </a>
                        <a href="#downloads" class="menu-item">
                            <span class="menu-item-icon">💻</span>
                            <span>Downloads</span>
                        </a>

                        <div class="menu-category">Library</div>
                        <a href="#info" class="menu-item">
                            <span class="menu-item-icon">⚙️</span>
                            <span>Server Information</span>
                        </a>
                        <a href="https://github.com/mehah/otclient/wiki" target="_blank" class="menu-item">
                            <span class="menu-item-icon">📖</span>
                            <span>Client Documentation</span>
                        </a>
                    </nav>
                </div>
            </div>
        </aside>

        <!-- CENTER COLUMN: Main Content -->
        <main>

            <!-- Download Buttons (Modern High-Res Tibia.com Style) -->
            <section id="downloads" class="download-grid">
                <a href="/downloads/otclient-windows.zip" class="btn-download">
                    <div class="btn-download-icon">🪟</div>
                    <div class="btn-download-text">
                        <h4>Download for Windows</h4>
                        <p>OTClient Native (.zip)</p>
                    </div>
                </a>
                <a href="/downloads/otclient-macos.zip" class="btn-download">
                    <div class="btn-download-icon">🍎</div>
                    <div class="btn-download-text">
                        <h4>Download for macOS</h4>
                        <p>OTClient Native Bundle (.zip)</p>
                    </div>
                </a>
            </section>

            <!-- Boosted Creatures Box -->
            <section id="boosted" class="tibia-box">
                <div class="tibia-box-header">
                    <div class="tibia-box-title">
                        <span>🔥 Today's Boosted Creature & Boss</span>
                    </div>
                    <span style="font-size: 11px; color: var(--tibia-gold-light);">Daily Rotation</span>
                </div>
                <div class="tibia-box-content">
                    <div class="boosted-grid">
                        <div class="boosted-card">
                            <div class="boosted-card-icon">🐲</div>
                            <div class="boosted-card-body">
                                <h5>Monster: <?= htmlspecialchars($stats['boostedCreature']) ?></h5>
                                <p>⚡ +100% XP &nbsp;|&nbsp; 💰 2x Loot</p>
                            </div>
                        </div>
                        <div class="boosted-card">
                            <div class="boosted-card-icon">👹</div>
                            <div class="boosted-card-body">
                                <h5>Boss: <?= htmlspecialchars($stats['boostedBoss']) ?></h5>
                                <p>🏆 +250% Loot &nbsp;|&nbsp; 🎯 +3 Kills</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- News Ticker / Latest News Box -->
            <section class="tibia-box">
                <div class="tibia-box-header">
                    <div class="tibia-box-title">
                        <span>📰 Featured News & Patch Notes</span>
                    </div>
                </div>
                <div class="tibia-box-content">
                    <article style="margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--tibia-border-glass);">
                        <div style="font-size: 12px; color: var(--tibia-gold-light); font-weight: 600; margin-bottom: 4px;">July 22, 2026 - Feature Release</div>
                        <h3 style="font-family: var(--font-heading); font-size: 18px; color: #fff; margin-bottom: 8px;">Direct In-Game Account Creation & Instant Client Login</h3>
                        <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6;">
                            Players can now create accounts directly inside the OTClient without visiting a web browser!
                            Enjoy instant real-time password requirement validation and direct entry into Dawnport.
                        </p>
                    </article>

                    <article>
                        <div style="font-size: 12px; color: var(--tibia-gold-light); font-weight: 600; margin-bottom: 4px;">July 20, 2026 - Server Status</div>
                        <h3 style="font-family: var(--font-heading); font-size: 18px; color: #fff; margin-bottom: 8px;">Canary Engine 13.40+ & Bosstiary Multipliers</h3>
                        <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6;">
                            Full implementation of official Tibia global mechanics: Wheel of Destiny, Bosstiary kill bonuses, and high-performance microservices.
                        </p>
                    </article>
                </div>
            </section>

            <!-- Top Highscores Table -->
            <section id="highscores" class="tibia-box">
                <div class="tibia-box-header">
                    <div class="tibia-box-title">
                        <span>🏆 Highscores Ranking</span>
                    </div>
                </div>
                <div class="tibia-box-content" style="padding: 0;">
                    <table class="tibia-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Level</th>
                                <th>Vocation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['topPlayers'] as $index => $player): ?>
                            <tr>
                                <td>
                                    <span class="rank-badge rank-<?= $index + 1 ?>">
                                        <?= $index + 1 ?>
                                    </span>
                                </td>
                                <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($player['name']) ?></td>
                                <td style="color: var(--tibia-gold-light); font-weight: 700;"><?= (int)$player['level'] ?></td>
                                <td style="color: var(--text-muted); font-size: 13px;"><?= htmlspecialchars($vocations[(int)$player['vocation']] ?? 'Player') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>

        <!-- RIGHT COLUMN: Account CTA & Server Info Sidebar -->
        <aside>

            <!-- Account Box CTA -->
            <div class="tibia-box cta-box">
                <div class="tibia-box-header">
                    <div class="tibia-box-title">
                        <span>✨ Account & Login</span>
                    </div>
                </div>
                <div class="tibia-box-content">
                    <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 16px;">
                        Start your journey! Register directly inside OTClient or create your account via web.
                    </p>
                    <a href="http://209.126.81.68:8080/create_account.php" class="btn-cta" style="margin-bottom: 10px;">
                        Create Account
                    </a>
                    <a href="http://209.126.81.68:8080/?subtopic=accountmanagement" class="btn-cta" style="background: rgba(30, 41, 59, 0.8); color: var(--text-main); border: 1px solid var(--tibia-border-gold);">
                        Manage Account
                    </a>
                </div>
            </div>

            <!-- Server Information Box -->
            <div id="info" class="tibia-box">
                <div class="tibia-box-header">
                    <div class="tibia-box-title">
                        <span>⚙️ Server Status</span>
                    </div>
                </div>
                <div class="tibia-box-content">
                    <ul style="list-style: none;">
                        <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                            <span style="color: var(--text-muted); font-size: 13px;">Protocol</span>
                            <span style="font-weight: 600; color: var(--tibia-gold-light); font-size: 13px;">13.40 / 15.25</span>
                        </li>
                        <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                            <span style="color: var(--text-muted); font-size: 13px;">World Type</span>
                            <span style="font-weight: 600; color: var(--tibia-green); font-size: 13px;">Open PvP</span>
                        </li>
                        <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                            <span style="color: var(--text-muted); font-size: 13px;">Experience</span>
                            <span style="font-weight: 600; font-size: 13px;">Staged (10x - 2x)</span>
                        </li>
                        <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                            <span style="color: var(--text-muted); font-size: 13px;">Skill Rate</span>
                            <span style="font-weight: 600; font-size: 13px;">5x</span>
                        </li>
                        <li style="display: flex; justify-content: space-between; padding: 8px 0;">
                            <span style="color: var(--text-muted); font-size: 13px;">Loot Rate</span>
                            <span style="font-weight: 600; font-size: 13px;">3x</span>
                        </li>
                    </ul>
                </div>
            </div>

        </aside>

    </div>

    <!-- Footer -->
    <footer class="tibia-footer">
        <div class="footer-logo">TIBIA CANARY ONLINE</div>
        <p>© 2026 OpenTibiaBR Canary Project. Official Tibia.com layout modernized for High-Resolution displays.</p>
    </footer>

</body>
</html>
