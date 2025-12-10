<?php

declare(strict_types=1);

namespace BetScript\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use BetScript\Services\MatchService;
use BetScript\Services\UserService;
use BetScript\Services\BettingService;

class HomeController
{
    private Twig $twig;
    private MatchService $matchService;
    private UserService $userService;
    private BettingService $bettingService;

    public function __construct(
        Twig $twig,
        MatchService $matchService,
        UserService $userService,
        BettingService $bettingService
    ) {
        $this->twig = $twig;
        $this->matchService = $matchService;
        $this->userService = $userService;
        $this->bettingService = $bettingService;
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $_SESSION['user_id'] ?? null;
        $user = $userId ? $this->userService->getUserById($userId) : null;

        $upcomingMatches = array_map(fn($m) => $m->toArray(), $this->matchService->getUpcomingMatches());
        $leaderboard = $this->userService->getLeaderboard(10);

        return $this->twig->render($response, 'home.twig', [
            'user' => $user,
            'upcomingMatches' => array_slice($upcomingMatches, 0, 5),
            'leaderboard' => $leaderboard,
        ]);
    }
}
