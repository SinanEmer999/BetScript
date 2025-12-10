<?php

declare(strict_types=1);

namespace BetScript\Services;

use BetScript\Models\Bet;
use BetScript\Models\User;

class BettingService
{
    private DataService $dataService;
    private UserService $userService;
    private OddsService $oddsService;

    public function __construct(
        DataService $dataService,
        UserService $userService,
        OddsService $oddsService
    ) {
        $this->dataService = $dataService;
        $this->userService = $userService;
        $this->oddsService = $oddsService;
    }

    public function placeBet(
        string $userId,
        string $matchId,
        string $prediction,
        int $amount
    ): ?Bet {
        // Validate amount
        $minBet = (int)($_ENV['MIN_BET'] ?? 10);
        $maxBet = (int)($_ENV['MAX_BET'] ?? 1000);

        if ($amount < $minBet || $amount > $maxBet) {
            return null;
        }

        // Check user has enough points
        $user = $this->userService->getUserById($userId);
        if (!$user || $user->getFietzPoints() < $amount) {
            return null;
        }

        // Get current odds
        $odds = $this->oddsService->calculateOdds($matchId);
        if (!isset($odds[$prediction])) {
            return null;
        }

        // Deduct points
        if (!$user->deductPoints($amount)) {
            return null;
        }

        // Create bet
        $betId = $this->generateBetId();
        $bet = new Bet(
            $betId,
            $userId,
            $matchId,
            $prediction,
            $amount,
            $odds[$prediction]
        );

        // Save bet
        $bets = $this->dataService->loadBets();
        $bets[] = $bet->toArray();
        $this->dataService->saveBets($bets);

        // Update user stats
        $user->incrementBets();
        $this->userService->updateUser($user);

        return $bet;
    }

    public function getUserBets(string $userId): array
    {
        $bets = $this->dataService->loadBets();
        $userBets = [];

        foreach ($bets as $betData) {
            if ($betData['userId'] === $userId) {
                $userBets[] = Bet::fromArray($betData);
            }
        }

        return $userBets;
    }

    public function getBetsByMatch(string $matchId): array
    {
        $bets = $this->dataService->loadBets();
        $matchBets = [];

        foreach ($bets as $betData) {
            if ($betData['matchId'] === $matchId) {
                $matchBets[] = Bet::fromArray($betData);
            }
        }

        return $matchBets;
    }

    public function resolveBet(string $betId, string $result): bool
    {
        $bets = $this->dataService->loadBets();
        $betFound = false;

        foreach ($bets as &$betData) {
            if ($betData['id'] === $betId && $betData['status'] === 'pending') {
                $bet = Bet::fromArray($betData);
                
                // Determine if bet won
                if ($bet->getPrediction() === $result) {
                    $bet->setStatus('won');
                    $winAmount = $bet->getPotentialWin();

                    // Award points to user
                    $user = $this->userService->getUserById($bet->getUserId());
                    if ($user) {
                        $user->addPoints($winAmount);
                        $user->incrementWonBets();
                        $user->addWinnings($winAmount);
                        $this->userService->updateUser($user);
                    }
                } else {
                    $bet->setStatus('lost');
                }

                $betData = $bet->toArray();
                $betFound = true;
                break;
            }
        }

        if ($betFound) {
            $this->dataService->saveBets($bets);
        }

        return $betFound;
    }

    public function resolveMatchBets(string $matchId, string $result): int
    {
        $bets = $this->dataService->loadBets();
        $resolvedCount = 0;

        foreach ($bets as &$betData) {
            if ($betData['matchId'] === $matchId && $betData['status'] === 'pending') {
                $bet = Bet::fromArray($betData);
                
                if ($bet->getPrediction() === $result) {
                    $bet->setStatus('won');
                    $winAmount = $bet->getPotentialWin();

                    $user = $this->userService->getUserById($bet->getUserId());
                    if ($user) {
                        $user->addPoints($winAmount);
                        $user->incrementWonBets();
                        $user->addWinnings($winAmount);
                        $this->userService->updateUser($user);
                    }
                } else {
                    $bet->setStatus('lost');
                }

                $betData = $bet->toArray();
                $resolvedCount++;
            }
        }

        if ($resolvedCount > 0) {
            $this->dataService->saveBets($bets);
        }

        return $resolvedCount;
    }

    public function cancelBet(string $betId, string $userId): bool
    {
        $bets = $this->dataService->loadBets();
        $betFound = false;

        foreach ($bets as &$betData) {
            if ($betData['id'] === $betId && 
                $betData['userId'] === $userId && 
                $betData['status'] === 'pending') {
                
                $bet = Bet::fromArray($betData);
                $bet->setStatus('cancelled');

                // Refund points
                $user = $this->userService->getUserById($userId);
                if ($user) {
                    $user->addPoints($bet->getAmount());
                    $this->userService->updateUser($user);
                }

                $betData = $bet->toArray();
                $betFound = true;
                break;
            }
        }

        if ($betFound) {
            $this->dataService->saveBets($bets);
        }

        return $betFound;
    }

    private function generateBetId(): string
    {
        return uniqid('bet_', true);
    }

    public function getBettingStatistics(string $userId): array
    {
        $bets = $this->getUserBets($userId);
        
        $stats = [
            'total' => count($bets),
            'pending' => 0,
            'won' => 0,
            'lost' => 0,
            'cancelled' => 0,
            'totalWagered' => 0,
            'totalWon' => 0,
            'netProfit' => 0,
        ];

        foreach ($bets as $bet) {
            $stats[$bet->getStatus()]++;
            $stats['totalWagered'] += $bet->getAmount();
            
            if ($bet->getStatus() === 'won') {
                $stats['totalWon'] += $bet->getPotentialWin();
            }
        }

        $stats['netProfit'] = $stats['totalWon'] - $stats['totalWagered'];

        return $stats;
    }
}
