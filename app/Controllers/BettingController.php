<?php

declare(strict_types=1);

namespace BetScript\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use BetScript\Services\BettingService;
use BetScript\Services\OddsService;
use BetScript\Services\MatchService;
use BetScript\Services\UserService;

class BettingController
{
    private Twig $twig;
    private BettingService $bettingService;
    private OddsService $oddsService;
    private MatchService $matchService;
    private UserService $userService;

    public function __construct(
        Twig $twig,
        BettingService $bettingService,
        OddsService $oddsService,
        MatchService $matchService,
        UserService $userService
    ) {
        $this->twig = $twig;
        $this->bettingService = $bettingService;
        $this->oddsService = $oddsService;
        $this->matchService = $matchService;
        $this->userService = $userService;
    }

    public function showMatches(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $user = $this->userService->getUserById($userId);
        $upcomingMatchesData = $this->matchService->getUpcomingMatches();
        $liveMatchesData = $this->matchService->getLiveMatches();
        
        $allMatchesData = array_merge($upcomingMatchesData, $liveMatchesData);
        
        // Add odds to each match
        $matches = [];
        foreach ($allMatchesData as $matchData) {
            $gameMatch = \BetScript\Models\GameMatch::fromArray($matchData);
            $matchArray = $gameMatch->toArray();
            $matchArray['odds'] = $this->oddsService->calculateOdds($gameMatch->id);
            $matches[] = $matchArray;
        }

        return $this->twig->render($response, 'betting/matches.twig', [
            'user' => $user,
            'matches' => $matches,
        ]);
    }

    public function placeBet(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->getBody()->write(json_encode(['success' => false, 'error' => 'Not authenticated']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $data = $request->getParsedBody();
        $matchId = $data['matchId'] ?? '';
        $prediction = $data['prediction'] ?? '';
        $amount = (int)($data['amount'] ?? 0);

        $bet = $this->bettingService->placeBet($userId, $matchId, $prediction, $amount);

        if ($bet) {
            $response->getBody()->write(json_encode([
                'success' => true,
                'bet' => $bet->toArray(),
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'Failed to place bet',
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    public function showMyBets(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $user = $this->userService->getUserById($userId);
        $bets = $this->bettingService->getUserBets($userId);
        $stats = $this->bettingService->getBettingStatistics($userId);

        return $this->twig->render($response, 'betting/my-bets.twig', [
            'user' => $user,
            'bets' => $bets,
            'stats' => $stats,
        ]);
    }

    public function getOdds(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $matchId = $args['matchId'] ?? '';
        $odds = $this->oddsService->calculateOdds($matchId);

        $response->getBody()->write(json_encode($odds));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
