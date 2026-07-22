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
    <title>Canary MMORPG - Official OpenTibia Server</title>
    <link rel="stylesheet" href="/templates/modern/style.css">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="/" class="brand">
            <div class="brand-icon">C</div>
            <div class="brand-title">CANARY <span>ONLINE</span></div>
        </a>

        <ul class="nav-links">
            <li><a href="/" class="nav-link active">Home</a></li>
            <li><a href="#downloads" class="nav-link">Downloads</a></li>
            <li><a href="#boosted" class="nav-link">Boosted</a></li>
            <li><a href="#ranking" class="nav-link">Highscores</a></li>
            <li><a href="#info" class="nav-link">Server Info</a></li>
        </ul>

        <div class="nav-status">
            <div class="status-dot"></div>
            <span>Server Online</span>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero">
        <div class="hero-badge">⚔️ Season 1 - The Legacy of Canary</div>
        <h1 class="hero-title">Experience the Ultimate <span>Tibia Universe</span></h1>
        <p class="hero-subtitle">
            A 100% faithful, high-performance MMORPG server featuring the official Tibia global mechanics,
            custom events, instant in-game account registration, and dual macOS & Windows native clients.
        </p>
        <div class="hero-cta-group">
            <a href="#downloads" class="btn btn-primary">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>
                Download Client
            </a>
            <a href="http://209.126.81.68:8080/create_account.php" class="btn btn-secondary">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                Create Account In-Game
            </a>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="container">

        <!-- Download Cards Grid -->
        <section id="downloads" style="margin-bottom: 60px;">
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="font-family: var(--font-heading); font-size: 32px; color: var(--gold-light);">Download Official Client</h2>
                <p style="color: var(--text-muted);">Choose your operating system and start playing instantly with ultra-low latency.</p>
            </div>

            <div class="grid-3">
                <!-- Windows Card -->
                <div class="card download-card">
                    <div class="download-icon">🪟</div>
                    <h3 class="download-title">Windows Client</h3>
                    <p class="download-desc">Includes DirectX/OpenGL launcher, full high-res assets, and auto-updater for Windows 10/11.</p>
                    <a href="/downloads/otclient-windows.zip" class="btn btn-primary" style="width: 100%;">
                        Download for Windows (.zip)
                    </a>
                </div>

                <!-- macOS Card -->
                <div class="card download-card">
                    <div class="download-icon">🍎</div>
                    <h3 class="download-title">macOS Native Client</h3>
                    <p class="download-desc">Optimized Apple Silicon & Intel Metal graphics client bundle for macOS Monterey, Ventura & Sonoma.</p>
                    <a href="/downloads/otclient-macos.zip" class="btn btn-primary" style="width: 100%;">
                        Download for macOS (.zip)
                    </a>
                </div>

                <!-- Features Card -->
                <div class="card download-card">
                    <div class="download-icon">⚡</div>
                    <h3 class="download-title">In-Game Direct Registration</h3>
                    <p class="download-desc">No web hassle required! Launch OTClient, click <b>Create Account</b> and start playing immediately.</p>
                    <a href="#info" class="btn btn-secondary" style="width: 100%;">
                        View Server Rules & Info
                    </a>
                </div>
            </div>
        </section>

        <!-- Middle 2-Column Grid: Boosted & Highscores -->
        <div class="grid-2">

            <!-- Left Column: Boosted & News -->
            <div>
                <!-- Boosted Panel -->
                <section id="boosted" class="card" style="margin-bottom: 30px;">
                    <div class="card-header">
                        <div class="card-title">
                            <span>🔥 Today's Boosted Creatures</span>
                        </div>
                        <span style="font-size: 12px; color: var(--gold-light); font-weight: 600;">Rotates Every 24h</span>
                    </div>

                    <div class="boosted-item">
                        <div class="boosted-avatar">
                            <span style="font-size: 24px;">🐲</span>
                        </div>
                        <div class="boosted-info">
                            <h4>Boosted Monster: <?= htmlspecialchars($stats['boostedCreature']) ?></h4>
                            <p>⚡ +100% Experience Point Bonus &nbsp;|&nbsp; 💰 2x Loot Drop &nbsp;|&nbsp; ⌛ 2x Faster Respawn</p>
                        </div>
                    </div>

                    <div class="boosted-item">
                        <div class="boosted-avatar">
                            <span style="font-size: 24px;">👹</span>
                        </div>
                        <div class="boosted-info">
                            <h4>Boosted Boss: <?= htmlspecialchars($stats['boostedBoss']) ?></h4>
                            <p>🏆 +250% Boss Loot Bonus &nbsp;|&nbsp; 🎯 +3 Extra Kills for Bosstiary</p>
                        </div>
                    </div>
                </section>

                <!-- Server News Timeline -->
                <section class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <span>📜 Latest Server News & Updates</span>
                        </div>
                    </div>

                    <div class="news-item">
                        <div class="news-date">July 22, 2026</div>
                        <h3 class="news-heading">Direct In-Game Account Creation & Instant Client Launch</h3>
                        <p class="news-snippet">Players can now create accounts directly inside the OTClient app with instant real-time password requirement feedback!</p>
                    </div>

                    <div class="news-item">
                        <div class="news-date">July 20, 2026</div>
                        <h3 class="news-heading">Canary Engine 13.40+ Release & Bosstiary Mechanics</h3>
                        <p class="news-snippet">Full implementation of Bosstiary multipliers, Wheel of Destiny, and high-performance login-server web services.</p>
                    </div>
                </section>
            </div>

            <!-- Right Column: Highscores & Quick Stats -->
            <div>
                <!-- Top Players Card -->
                <section id="ranking" class="card" style="margin-bottom: 30px;">
                    <div class="card-header">
                        <div class="card-title">
                            <span>🏆 Top Highscores</span>
                        </div>
                    </div>

                    <table class="table">
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
                                <td style="font-weight: 600;"><?= htmlspecialchars($player['name']) ?></td>
                                <td style="color: var(--gold-light); font-weight: 700;"><?= (int)$player['level'] ?></td>
                                <td style="color: var(--text-muted); font-size: 13px;"><?= htmlspecialchars($vocations[(int)$player['vocation']] ?? 'Player') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>

                <!-- Server Specs Card -->
                <section id="info" class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <span>⚙️ Server Specifications</span>
                        </div>
                    </div>

                    <ul style="list-style: none; space-y: 12px;">
                        <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-glass);">
                            <span style="color: var(--text-muted);">Protocol</span>
                            <span style="font-weight: 600; color: var(--gold-light);">13.40 / 15.25</span>
                        </li>
                        <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-glass);">
                            <span style="color: var(--text-muted);">World Type</span>
                            <span style="font-weight: 600; color: var(--emerald-green);">Open PvP</span>
                        </li>
                        <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-glass);">
                            <span style="color: var(--text-muted);">Experience Rate</span>
                            <span style="font-weight: 600;">Staged (10x - 2x)</span>
                        </li>
                        <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-glass);">
                            <span style="color: var(--text-muted);">Skill / Magic Rate</span>
                            <span style="font-weight: 600;">5x / 3x</span>
                        </li>
                        <li style="display: flex; justify-content: space-between; padding: 8px 0;">
                            <span style="color: var(--text-muted);">Loot Rate</span>
                            <span style="font-weight: 600;">3x</span>
                        </li>
                    </ul>
                </section>
            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-brand">CANARY ONLINE MMORPG</div>
        <p>© 2026 OpenTibiaBR Canary Project. All rights reserved. Powered by Docker & OTClient Redemption.</p>
    </footer>

</body>
</html>
