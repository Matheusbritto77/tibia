<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function env_value(string $name, string $default = ''): string
{
    $val = getenv($name);
    return $val === false || $val === '' ? $default : $val;
}

function get_db_connection(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $host = env_value('CANARY_DB_HOST', 'db');
    $port = env_value('CANARY_DB_PORT', '3306');
    $name = env_value('CANARY_DB_NAME', 'canary');
    $user = env_value('CANARY_DB_USER', 'canary');
    $password = env_value('CANARY_DB_PASSWORD', 'canary');

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (!is_array($input)) {
    $input = $_POST;
}

$type = strtolower($input['type'] ?? '');

if ($type === 'getaccountcreationstatus') {
    echo json_encode([
        'RecommendedWorld' => 'Canary',
        'IsCaptchaDeactivated' => true,
        'Worlds' => [
            [
                'Name' => 'Canary',
                'Region' => 'South America',
                'PvPType' => 'Open PvP',
                'PlayersOnline' => 0
            ]
        ]
    ]);
    exit;
}

if ($type === 'generatecharactername') {
    $names = ['Seragon Xedanna', 'Kaelen Vond', 'Thalor Drake', 'Aron Shadow', 'Valerius Dawn', 'Zephyr Dusk', 'Orion Pax'];
    $randName = $names[array_rand($names)];
    echo json_encode(['CharacterName' => $randName, 'GeneratedName' => $randName]);
    exit;
}

if ($type === 'checkemail') {
    $email = trim($input['Email'] ?? $input['email'] ?? '');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'IsValid' => false,
            'errorMessage' => 'This email address has an invalid format.'
        ]);
        exit;
    }

    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare('SELECT 1 FROM accounts WHERE email = ? OR name = ? LIMIT 1');
        $stmt->execute([$email, $email]);
        if ($stmt->fetchColumn()) {
            echo json_encode([
                'IsValid' => false,
                'errorMessage' => 'This email address is already registered.'
            ]);
            exit;
        }
    } catch (Throwable $e) {
        // Fallback OK
    }

    echo json_encode(['IsValid' => true]);
    exit;
}

if ($type === 'checkcharactername') {
    $name = trim($input['CharacterName'] ?? $input['characterName'] ?? '');
    if ($name === '' || strlen($name) < 3 || strlen($name) > 29 || !preg_match('/^[A-Z][a-zA-Z ]+$/', $name)) {
        echo json_encode([
            'CharacterName' => $name,
            'IsAvailable' => false,
            'errorMessage' => 'A character name must start with a capital letter and contain only letters.'
        ]);
        exit;
    }

    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare('SELECT 1 FROM players WHERE name = ? LIMIT 1');
        $stmt->execute([$name]);
        if ($stmt->fetchColumn()) {
            echo json_encode([
                'CharacterName' => $name,
                'IsAvailable' => false,
                'errorMessage' => 'A character with that name already exists.'
            ]);
            exit;
        }
    } catch (Throwable $e) {
        // Fallback OK
    }

    echo json_encode([
        'CharacterName' => $name,
        'IsAvailable' => true
    ]);
    exit;
}

if ($type === 'checkpassword') {
    $pass = $input['Password1'] ?? $input['password'] ?? '';
    $hasUpper = preg_match('/[A-Z]/', $pass) === 1;
    $hasLower = preg_match('/[a-z]/', $pass) === 1;
    $hasNum   = preg_match('/[0-9]/', $pass) === 1;
    $validLen = strlen($pass) >= 10 && strlen($pass) <= 29;
    $invalidChars = preg_match('/[^\x20-\x7E]/', $pass) === 1;

    $isValid = $hasUpper && $hasLower && $hasNum && $validLen && !$invalidChars;

    echo json_encode([
        'PasswordValid' => $isValid,
        'PasswordStrength' => $isValid ? 3 : 1,
        'PasswordStrengthColor' => $isValid ? '#76EE00' : '#EC644B',
        'PasswordRequirements' => [
            'HasUpperCase' => $hasUpper,
            'HasNumber' => $hasNum,
            'PasswordLength' => $validLen,
            'InvalidCharacters' => $invalidChars,
            'HasLowerCase' => $hasLower
        ]
    ]);
    exit;
}

if ($type === 'createaccountandcharacter') {
    $email = trim($input['EMail'] ?? $input['email'] ?? '');
    $password = $input['Password'] ?? $input['password'] ?? '';
    $charName = trim($input['CharacterName'] ?? $input['characterName'] ?? '');
    $charSexStr = strtolower(trim($input['CharacterSex'] ?? $input['characterSex'] ?? 'male'));
    $sex = ($charSexStr === 'female' || $charSexStr === '0') ? 0 : 1;

    if ($email === '' || $password === '' || $charName === '') {
        echo json_encode([
            'Success' => false,
            'success' => false,
            'errorMessage' => 'All fields are required.'
        ]);
        exit;
    }

    try {
        $pdo = get_db_connection();

        // 1. Check if email/account exists
        $stmt = $pdo->prepare('SELECT 1 FROM accounts WHERE email = ? OR name = ? LIMIT 1');
        $stmt->execute([$email, $email]);
        if ($stmt->fetchColumn()) {
            echo json_encode([
                'Success' => false,
                'success' => false,
                'errorMessage' => 'An account with this email already exists.'
            ]);
            exit;
        }

        // 2. Check if player name exists
        $stmt = $pdo->prepare('SELECT 1 FROM players WHERE name = ? LIMIT 1');
        $stmt->execute([$charName]);
        if ($stmt->fetchColumn()) {
            echo json_encode([
                'Success' => false,
                'success' => false,
                'errorMessage' => 'A character with this name already exists.'
            ]);
            exit;
        }

        // 3. Create account
        $passwordHash = sha1($password);
        $stmt = $pdo->prepare(
            'INSERT INTO accounts (`name`, `password`, `email`, `premdays`, `type`, `creation`) VALUES (?, ?, ?, 30, 1, ?)'
        );
        $stmt->execute([$email, $passwordHash, $email, time()]);
        $accountId = (int)$pdo->lastInsertId();

        // 4. Create character
        $lookType = ($sex === 1) ? 128 : 136;
        $stmt = $pdo->prepare(
            "INSERT INTO players (`name`, `group_id`, `account_id`, `level`, `vocation`, `health`, `healthmax`, `experience`, `lookbody`, `lookfeet`, `lookhead`, `looklegs`, `looktype`, `town_id`, `posx`, `posy`, `posz`, `cap`, `sex`, `conditions`) VALUES (?, 1, ?, 1, 0, 150, 150, 0, 68, 76, 78, 58, ?, 1, 32097, 32219, 7, 400, ?, '')"
        );
        $stmt->execute([$charName, $accountId, $lookType, $sex]);

        echo json_encode([
            'Success' => true,
            'success' => true
        ]);
        exit;

    } catch (Throwable $error) {
        echo json_encode([
            'Success' => false,
            'success' => false,
            'errorMessage' => 'Database error: ' . $error->getMessage()
        ]);
        exit;
    }
}

echo json_encode([
    'Success' => false,
    'success' => false,
    'errorMessage' => 'Invalid action.'
]);
