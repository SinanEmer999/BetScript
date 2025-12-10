<?php

declare(strict_types=1);

namespace BetScript\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use BetScript\Services\MatchService;
use BetScript\Services\UserService;

class MatchController
{
    private Twig $twig;
    private MatchService $matchService;
    private UserService $userService;

    public function __construct(
        Twig $twig,
        MatchService $matchService,
        UserService $userService
    ) {
        $this->twig = $twig;
        $this->matchService = $matchService;
        $this->userService = $userService;
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $users = $this->userService->getAllUsers();
        return $this->twig->render($response, 'matches/create.twig', [
            'user' => $this->userService->getUserById($userId),
            'users' => $users,
        ]);
    }

    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Not authenticated']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $data = $request->getParsedBody();
        $player1Id = $data['player1Id'] ?? '';
        $player2Id = $data['player2Id'] ?? '';

        if (empty($player1Id) || empty($player2Id)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Beide Spieler müssen ausgewählt werden']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        if ($player1Id === $player2Id) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Spieler müssen unterschiedlich sein']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $player1 = $this->userService->getUserById($player1Id);
        $player2 = $this->userService->getUserById($player2Id);

        if (!$player1 || !$player2) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Spieler nicht gefunden']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $match = $this->matchService->createMatch(
            $player1Id,
            $player2Id,
            $player1->username,
            $player2->username
        );

        $response->getBody()->write(json_encode([
            'success' => true,
            'matchId' => $match->id,
            'message' => 'Match erstellt!'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function start(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getParsedBody();
        $matchId = $data['matchId'] ?? '';

        $success = $this->matchService->startMatch($matchId);

        $response->getBody()->write(json_encode([
            'success' => $success,
            'message' => $success ? 'Match gestartet!' : 'Fehler beim Starten'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function complete(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getParsedBody();
        $matchId = $data['matchId'] ?? '';
        $score1 = (int)($data['score1'] ?? 0);
        $score2 = (int)($data['score2'] ?? 0);

        $success = $this->matchService->completeMatch($matchId, $score1, $score2);

        $response->getBody()->write(json_encode([
            'success' => $success,
            'message' => $success ? 'Match abgeschlossen!' : 'Fehler beim Abschließen'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function cancel(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getParsedBody();
        $matchId = $data['matchId'] ?? '';

        $success = $this->matchService->cancelMatch($matchId);

        $response->getBody()->write(json_encode([
            'success' => $success,
            'message' => $success ? 'Match abgebrochen!' : 'Fehler beim Abbrechen'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
