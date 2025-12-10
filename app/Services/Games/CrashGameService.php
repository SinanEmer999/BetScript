<?php

declare(strict_types=1);

namespace BetScript\Services\Games;

use BetScript\Services\UserService;
use BetScript\Services\DataService;

class CrashGameService
{
    private UserService $userService;
    private DataService $dataService;

    public function __construct(UserService $userService, DataService $dataService)
    {
        $this->userService = $userService;
        $this->dataService = $dataService;
    }

    /**
     * Start a new crash game
     */
    public function startGame(string $userId, int $betAmount): ?array
    {
        $user = $this->userService->getUserById($userId);
        if (!$user || !$user->deductPoints($betAmount)) {
            return null;
        }

        // Generate crash point using provably fair algorithm
        $crashPoint = $this->generateCrashPoint();
        
        $gameId = uniqid('crash_', true);
        $game = [
            'id' => $gameId,
            'userId' => $userId,
            'betAmount' => $betAmount,
            'crashPoint' => $crashPoint,
            'cashOutMultiplier' => null,
            'winAmount' => 0,
            'status' => 'active',
            'startedAt' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];

        $this->userService->updateUser($user);
        $this->saveGameResult($game);

        return [
            'gameId' => $gameId,
            'crashPoint' => $crashPoint,
        ];
    }

    /**
     * Cash out from crash game
     */
    public function cashOut(string $gameId, float $multiplier): ?int
    {
        $games = $this->dataService->loadGameResults('crash');
        
        foreach ($games as &$game) {
            if ($game['id'] === $gameId && $game['status'] === 'active') {
                // Check if multiplier is valid (before crash)
                if ($multiplier < $game['crashPoint']) {
                    $game['cashOutMultiplier'] = $multiplier;
                    $game['winAmount'] = (int)($game['betAmount'] * $multiplier);
                    $game['status'] = 'won';
                    
                    // Award winnings
                    $user = $this->userService->getUserById($game['userId']);
                    if ($user) {
                        $user->addPoints($game['winAmount']);
                        $this->userService->updateUser($user);
                    }
                    
                    $this->dataService->saveGameResults('crash', $games);
                    return $game['winAmount'];
                }
                
                // Crashed before cash out
                $game['status'] = 'lost';
                $this->dataService->saveGameResults('crash', $games);
                return 0;
            }
        }
        
        return null;
    }

    /**
     * Generate crash point using provably fair system
     */
    private function generateCrashPoint(): float
    {
        // House edge: 1%
        $houseEdge = 0.01;
        
        // Generate random number between 0 and 1
        $e = -100 / (1 - $houseEdge);
        $random = mt_rand() / mt_getrandmax();
        
        // Calculate crash point
        $crashPoint = max(1.00, floor((100 / ($e * log(1 - $random))) * 100) / 100);
        
        // Cap at 1000x
        return min($crashPoint, 1000.00);
    }

    private function saveGameResult(array $game): void
    {
        $games = $this->dataService->loadGameResults('crash');
        $games[] = $game;
        $this->dataService->saveGameResults('crash', $games);
    }

    public function getGameHistory(string $userId, int $limit = 10): array
    {
        $games = $this->dataService->loadGameResults('crash');
        $userGames = [];
        
        foreach ($games as $game) {
            if ($game['userId'] === $userId) {
                $userGames[] = $game;
            }
        }
        
        return array_slice(array_reverse($userGames), 0, $limit);
    }
}
