<?php

declare(strict_types=1);

/**
 * CLI Script to initialize default cosmetics
 */

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use BetScript\Services\DataService;
use BetScript\Services\UserService;
use BetScript\Services\CosmeticService;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$dataService = new DataService();
$userService = new UserService($dataService);
$cosmeticService = new CosmeticService($dataService, $userService);

echo "Initializing cosmetics...\n";
$cosmeticService->initializeDefaultCosmetics();
echo "Done! Cosmetics have been initialized.\n";
