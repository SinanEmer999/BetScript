<?php

declare(strict_types=1);

namespace BetScript\Services\Games;

use BetScript\Services\UserService;
use BetScript\Services\DataService;

class PlinkoGameService
{
    private UserService $userService;
    private DataService $dataService;
    private const ROWS = 16;
    private const MULTIPLIERS = [
        'low' => [0.5, 0.8, 1.0, 1.3, 1.5, 1.8, 2.0, 2.2, 2.5, 2.2, 2.0, 1.8, 1.5, 1.3, 1.0, 0.8, 0.5],
        'medium' => [0.3, 0.7, 1.0, 1.5, 2.0, 3.0, 4.0, 5.0, 10.0, 5.0, 4.0, 3.0, 2.0, 1.5, 1.0, 0.7, 0.3],
        'high' => [0.2, 0.5, 0.8, 1.2, 2.0, 5.0, 10.0, 20.0, 50.0, 20.0, 10.0, 5.0, 2.0, 1.2, 0.8, 0.5, 0.2],
    ];

    public function __construct(UserService $userService, DataService $dataService)
    {
        $this->userService = $userService;
        $this->dataService = $dataService;
    }

    public function playGame(string $userId, int $betAmount, string $risk = 'medium'): ?array
    {
        $user = $this->userService->getUserById($userId);
        if (!$user || !$user->deductPoints($betAmount)) {
            return null;
        }

        // Simulate ball drop
        $path = $this->simulateBallDrop();
        $finalPosition = $path[count($path) - 1];
        
        // Get multiplier
        $multipliers = self::MULTIPLIERS[$risk] ?? self::MULTIPLIERS['medium'];
        $multiplier = $multipliers[$finalPosition];
        
        $winAmount = (int)($betAmount * $multiplier);
        
        // Award winnings
        if ($winAmount > 0) {
            $user->addPoints($winAmount);
        }
        
        $gameId = uniqid('plinko_', true);
        $game = [
            'id' => $gameId,
            'userId' => $userId,
            'betAmount' => $betAmount,
            'risk' => $risk,
            'path' => $path,
            'finalPosition' => $finalPosition,
            'multiplier' => $multiplier,
            'winAmount' => $winAmount,
            'playedAt' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];

        $this->userService->updateUser($user);
        $this->saveGameResult($game);

        return $game;
    }

    private function simulateBallDrop(): array
    {
        $path = [8]; // Start in the middle
        
        for ($i = 0; $i < self::ROWS; $i++) {
            // 50/50 chance to go left or right
            $direction = mt_rand(0, 1) === 0 ? -1 : 1;
            $nextPosition = $path[$i] + ($direction > 0 ? 1 : 0);
            
            // Ensure position stays within bounds
            $nextPosition = max(0, min(self::ROWS, $nextPosition));
            $path[] = $nextPosition;
        }
        
        return $path;
    }

    private function saveGameResult(array $game): void
    {
        $games = $this->dataService->loadGameResults('plinko');
        $games[] = $game;
        $this->dataService->saveGameResults('plinko', $games);
    }

    public function getGameHistory(string $userId, int $limit = 10): array
    {
        $games = $this->dataService->loadGameResults('plinko');
        $userGames = [];
        
        foreach ($games as $game) {
            if ($game['userId'] === $userId) {
                $userGames[] = $game;
            }
        }
        
        return array_slice(array_reverse($userGames), 0, $limit);
    }
}
