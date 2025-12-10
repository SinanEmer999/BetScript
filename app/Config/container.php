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
use BetScript\Services\MatchService;
use BetScript\Services\Games\CrashGameService;
use BetScript\Services\Games\PlinkoGameService;
use BetScript\Services\Games\BlackjackGameService;
use BetScript\Controllers\MatchController;
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

    // Match Service
    MatchService::class => function (ContainerInterface $c) {
        return new MatchService($c->get(DataService::class));
    },

    // User Service
    UserService::class => function (ContainerInterface $c) {
        return new UserService($c->get(DataService::class));
    },

    // Odds Service
    OddsService::class => function (ContainerInterface $c) {
        $oddsService = new OddsService($c->get(MatchService::class));
        return $oddsService;
    },

    // Betting Service
    BettingService::class => function (ContainerInterface $c) {
        $bettingService = new BettingService(
            $c->get(DataService::class),
            $c->get(UserService::class),
            $c->get(OddsService::class),
            $c->get(MatchService::class)
        );
        // Set circular dependency
        $c->get(OddsService::class)->setBettingService($bettingService);
        return $bettingService;
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

    // Controllers
    MatchController::class => function (ContainerInterface $c) {
        return new MatchController(
            $c->get(Twig::class),
            $c->get(MatchService::class),
            $c->get(UserService::class)
        );
    },
];
