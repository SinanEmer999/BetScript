<?php

declare(strict_types=1);

namespace BetScript\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use BetScript\Services\UserService;
use BetScript\Services\Games\CrashGameService;
use BetScript\Services\Games\PlinkoGameService;
use BetScript\Services\Games\BlackjackGameService;

class GamesController
{
    private Twig $twig;
    private UserService $userService;
    private CrashGameService $crashService;
    private PlinkoGameService $plinkoService;
    private BlackjackGameService $blackjackService;

    public function __construct(
        Twig $twig,
        UserService $userService,
        CrashGameService $crashService,
        PlinkoGameService $plinkoService,
        BlackjackGameService $blackjackService
    ) {
        $this->twig = $twig;
        $this->userService = $userService;
        $this->crashService = $crashService;
        $this->plinkoService = $plinkoService;
        $this->blackjackService = $blackjackService;
    }

    // Crash
    public function showCrash(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $user = $this->userService->getUserById($userId);
        return $this->twig->render($response, 'games/crash.twig', ['user' => $user]);
    }

    public function startCrash(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->getBody()->write(json_encode(['success' => false]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $data = $request->getParsedBody();
        $amount = (int)($data['amount'] ?? 0);

        $game = $this->crashService->startGame($userId, $amount);
        $response->getBody()->write(json_encode($game ?? ['success' => false]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function crashCashOut(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getParsedBody();
        $gameId = $data['gameId'] ?? '';
        $multiplier = (float)($data['multiplier'] ?? 0);

        $winAmount = $this->crashService->cashOut($gameId, $multiplier);
        $response->getBody()->write(json_encode(['winAmount' => $winAmount]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Plinko
    public function showPlinko(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $user = $this->userService->getUserById($userId);
        return $this->twig->render($response, 'games/plinko.twig', ['user' => $user]);
    }

    public function playPlinko(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->getBody()->write(json_encode(['success' => false]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $data = $request->getParsedBody();
        $amount = (int)($data['amount'] ?? 0);
        $risk = $data['risk'] ?? 'medium';

        $game = $this->plinkoService->playGame($userId, $amount, $risk);
        $response->getBody()->write(json_encode($game ?? ['success' => false]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Blackjack
    public function showBlackjack(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $user = $this->userService->getUserById($userId);
        return $this->twig->render($response, 'games/blackjack.twig', ['user' => $user]);
    }

    public function startBlackjack(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->getBody()->write(json_encode(['success' => false]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $data = $request->getParsedBody();
        $amount = (int)($data['amount'] ?? 0);

        $game = $this->blackjackService->startGame($userId, $amount);
        $response->getBody()->write(json_encode($game ?? ['success' => false]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function blackjackHit(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getParsedBody();
        $gameId = $data['gameId'] ?? '';

        $result = $this->blackjackService->hit($gameId);
        $response->getBody()->write(json_encode($result ?? ['success' => false]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function blackjackStand(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getParsedBody();
        $gameId = $data['gameId'] ?? '';

        $result = $this->blackjackService->stand($gameId);
        $response->getBody()->write(json_encode($result ?? ['success' => false]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
