<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Views\Twig;
use BetScript\Services\DataService;
use BetScript\Services\UserService;
use BetScript\Services\BettingService;
use BetScript\Services\OddsService;
use BetScript\Services\CosmeticService;
use BetScript\Services\KickScriptIntegrationService;
use BetScript\Services\Games\CrashGameService;
use BetScript\Services\Games\PlinkoGameService;
use BetScript\Services\Games\BlackjackGameService;
use Nyholm\Psr7\Factory\Psr17Factory;

return [
    // PSR-17 Factories
    ResponseFactoryInterface::class => function () {
        return new Psr17Factory();
    },

    // Twig
    Twig::class => function (ContainerInterface $c) {
        return Twig::create(__DIR__ . '/../../templates', [
            'cache' => false,
            'debug' => $_ENV['APP_DEBUG'] === 'true',
        ]);
    },

    // Data Service
    DataService::class => function () {
        return new DataService();
    },

    // KickScript Integration
    KickScriptIntegrationService::class => function () {
        return new KickScriptIntegrationService();
    },

    // User Service
    UserService::class => function (ContainerInterface $c) {
        return new UserService($c->get(DataService::class));
    },

    // Odds Service
    OddsService::class => function (ContainerInterface $c) {
        return new OddsService($c->get(KickScriptIntegrationService::class));
    },

    // Betting Service
    BettingService::class => function (ContainerInterface $c) {
        return new BettingService(
            $c->get(DataService::class),
            $c->get(UserService::class),
            $c->get(OddsService::class)
        );
    },

    // Cosmetic Service
    CosmeticService::class => function (ContainerInterface $c) {
        return new CosmeticService(
            $c->get(DataService::class),
            $c->get(UserService::class)
        );
    },

    // Game Services
    CrashGameService::class => function (ContainerInterface $c) {
        return new CrashGameService(
            $c->get(UserService::class),
            $c->get(DataService::class)
        );
    },

    PlinkoGameService::class => function (ContainerInterface $c) {
        return new PlinkoGameService(
            $c->get(UserService::class),
            $c->get(DataService::class)
        );
    },

    BlackjackGameService::class => function (ContainerInterface $c) {
        return new BlackjackGameService(
            $c->get(UserService::class),
            $c->get(DataService::class)
        );
    },
];
