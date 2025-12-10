<?php

declare(strict_types=1);

use Slim\App;
use BetScript\Controllers\HomeController;
use BetScript\Controllers\AuthController;
use BetScript\Controllers\BettingController;
use BetScript\Controllers\ProfileController;
use BetScript\Controllers\GamesController;
use BetScript\Controllers\MatchController;
use BetScript\Controllers\ApiController;

return function (App $app) {
    // Home
    $app->get('/', [HomeController::class, 'index']);

    // Auth
    $app->get('/login', [AuthController::class, 'showLogin']);
    $app->post('/login', [AuthController::class, 'login']);
    $app->get('/register', [AuthController::class, 'showRegister']);
    $app->post('/register', [AuthController::class, 'register']);
    $app->get('/logout', [AuthController::class, 'logout']);

    // API
    $app->get('/api/user/points', [ApiController::class, 'getUserPoints']);

    // Match Management
    $app->get('/matches/create', [MatchController::class, 'create']);
    $app->post('/matches/create', [MatchController::class, 'store']);
    $app->post('/matches/start', [MatchController::class, 'start']);
    $app->post('/matches/complete', [MatchController::class, 'complete']);
    $app->post('/matches/cancel', [MatchController::class, 'cancel']);

    // Betting
    $app->get('/betting/matches', [BettingController::class, 'showMatches']);
    $app->post('/betting/place', [BettingController::class, 'placeBet']);
    $app->get('/betting/my-bets', [BettingController::class, 'showMyBets']);
    $app->get('/api/odds/{matchId}', [BettingController::class, 'getOdds']);

    // Profile & Shop
    $app->get('/profile', [ProfileController::class, 'showProfile']);
    $app->get('/shop', [ProfileController::class, 'showShop']);
    $app->post('/shop/purchase', [ProfileController::class, 'purchaseCosmetic']);
    $app->post('/profile/avatar', [ProfileController::class, 'updateAvatar']);

    // Games - Crash
    $app->get('/games/crash', [GamesController::class, 'showCrash']);
    $app->post('/games/crash/start', [GamesController::class, 'startCrash']);
    $app->post('/games/crash/cashout', [GamesController::class, 'crashCashOut']);

    // Games - Plinko
    $app->get('/games/plinko', [GamesController::class, 'showPlinko']);
    $app->post('/games/plinko/play', [GamesController::class, 'playPlinko']);

    // Games - Blackjack
    $app->get('/games/blackjack', [GamesController::class, 'showBlackjack']);
    $app->post('/games/blackjack/start', [GamesController::class, 'startBlackjack']);
    $app->post('/games/blackjack/hit', [GamesController::class, 'blackjackHit']);
    $app->post('/games/blackjack/stand', [GamesController::class, 'blackjackStand']);
};
