#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Initialize container
$container = require __DIR__ . '/../app/Config/container.php';
$containerBuilder = new \DI\ContainerBuilder();
$containerBuilder->addDefinitions($container);
$app = $containerBuilder->build();

// Get UserService
$userService = $app->get(\BetScript\Services\UserService::class);

// Create test user
echo "Creating test user...\n";
$user = $userService->createUser('testuser', 'test@example.com', 'password123');

if ($user) {
    echo "✓ Test user created successfully!\n";
    echo "  Username: testuser\n";
    echo "  Email: test@example.com\n";
    echo "  Password: password123\n";
    echo "  FIETZ Points: " . $user->getFietzPoints() . "\n";
} else {
    echo "✗ Failed to create test user (might already exist)\n";
}

// Test authentication
echo "\nTesting authentication...\n";
$authUser = $userService->authenticate('testuser', 'password123');

if ($authUser) {
    echo "✓ Authentication successful!\n";
    echo "  User ID: " . $authUser->getId() . "\n";
    echo "  Username: " . $authUser->getUsername() . "\n";
} else {
    echo "✗ Authentication failed!\n";
}

// Also test with email
echo "\nTesting authentication with email...\n";
$authUser2 = $userService->authenticate('test@example.com', 'password123');

if ($authUser2) {
    echo "✓ Email authentication successful!\n";
} else {
    echo "✗ Email authentication failed!\n";
}
