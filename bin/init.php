<?php

declare(strict_types=1);

/**
 * Initialize BetScript with default data
 * Run this once after installation: php bin/init.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use BetScript\Services\DataService;
use BetScript\Services\CosmeticService;
use BetScript\Services\UserService;

echo "🎲 BetScript Initialisierung...\n\n";

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Create data directories
$dataDir = __DIR__ . '/../data';
$logsDir = __DIR__ . '/../logs';

if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
    echo "✅ Data-Verzeichnis erstellt\n";
}

if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
    echo "✅ Logs-Verzeichnis erstellt\n";
}

// Initialize services
$dataService = new DataService();
$userService = new UserService($dataService);
$cosmeticService = new CosmeticService($dataService, $userService);

// Initialize empty data files if they don't exist
$files = [
    'users.json' => [],
    'bets.json' => [],
    'matches.json' => [],
    'game_crash.json' => [],
    'game_plinko.json' => [],
    'game_blackjack.json' => [],
];

foreach ($files as $file => $defaultData) {
    $filepath = $dataDir . '/' . $file;
    if (!file_exists($filepath)) {
        file_put_contents($filepath, json_encode($defaultData, JSON_PRETTY_PRINT));
        echo "✅ $file erstellt\n";
    } else {
        echo "⏭️  $file existiert bereits\n";
    }
}

// Initialize default cosmetics
echo "\n📦 Initialisiere Standard-Cosmetics...\n";
$cosmeticService->initializeDefaultCosmetics();
echo "✅ Cosmetics initialisiert\n";

// Create demo user (optional)
if (!empty($_ENV['CREATE_DEMO_USER']) && $_ENV['CREATE_DEMO_USER'] === 'true') {
    echo "\n👤 Erstelle Demo-User...\n";
    
    $demoUser = $userService->createUser(
        'demo',
        'demo@betscript.local',
        'demo123'
    );
    
    if ($demoUser) {
        echo "✅ Demo-User erstellt:\n";
        echo "   Username: demo\n";
        echo "   Password: demo123\n";
        echo "   FIETZ Points: " . $demoUser->getFietzPoints() . "\n";
    } else {
        echo "⚠️  Demo-User konnte nicht erstellt werden (existiert bereits?)\n";
    }
}

echo "\n✨ Initialisierung abgeschlossen!\n";
echo "\n🚀 Starte den Server mit: php -S localhost:1338 -t public\n";
echo "🌐 Öffne im Browser: http://localhost:1338\n\n";
