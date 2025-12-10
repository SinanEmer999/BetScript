#!/bin/bash

# BetScript Update Script
# This script updates the system to remove KickScript dependency
# and implement the new match management system

echo "🔄 BetScript System Update"
echo "=========================="
echo ""

# Backup current state
echo "📦 Creating backup..."
mkdir -p ../betscript-backup-$(date +%Y%m%d)
cp -r . ../betscript-backup-$(date +%Y%m%d)/ 2>/dev/null || true
echo "✅ Backup created"
echo ""

# Update BettingService constructor
echo "🔧 Updating BettingService..."
cat > app/Services/BettingService.php.new << 'EOF'
<?php

declare(strict_types=1);

namespace BetScript\Services;

use BetScript\Models\Bet;

class BettingService
{
    private DataService $dataService;
    private UserService $userService;
    private OddsService $oddsService;
    private MatchService $matchService;

    public function __construct(
        DataService $dataService,
        UserService $userService,
        OddsService $oddsService,
        MatchService $matchService
    ) {
        $this->dataService = $dataService;
        $this->userService = $userService;
        $this->oddsService = $oddsService;
        $this->matchService = $matchService;
    }

    public function placeBet(string $userId, string $matchId, string $prediction, int $amount): ?Bet
    {
        // Validate
        $minBet = (int)($_ENV['MIN_BET'] ?? 10);
        $maxBet = (int)($_ENV['MAX_BET'] ?? 1000);
        if ($amount < $minBet || $amount > $maxBet) return null;

        // Check match exists and can bet
        $match = $this->matchService->getMatchById($matchId);
        if (!$match || !$match->canBetOn()) return null;

        // Check user points
        $user = $this->userService->getUserById($userId);
        if (!$user || $user->getFietzPoints() < $amount) return null;

        // Get odds
        $odds = $this->oddsService->calculateOdds($matchId);
        if (!isset($odds[$prediction])) return null;

        // Deduct points
        if (!$user->deductPoints($amount)) return null;
        $this->userService->updateUser($user);

        // Create bet
        $bet = new Bet(
            'bet_' . uniqid(),
            $userId,
            $matchId,
            $prediction,
            $amount,
            $odds[$prediction]
        );

        $bets = $this->dataService->load('bets.json');
        $bets[] = $bet->toArray();
        $this->dataService->save('bets.json', $bets);

        return $bet;
    }

    public function getUserBets(string $userId): array
    {
        $bets = $this->dataService->load('bets.json');
        $userBets = array_filter($bets, fn($b) => $b['userId'] === $userId);
        return array_map(fn($b) => Bet::fromArray($b), array_values($userBets));
    }

    public function getMatchBets(string $matchId): array
    {
        $bets = $this->dataService->load('bets.json');
        $matchBets = array_filter($bets, fn($b) => $b['matchId'] === $matchId);
        return array_map(fn($b) => Bet::fromArray($b), array_values($matchBets));
    }

    public function resolveMatchBets(string $matchId, string $result): int
    {
        $bets = $this->getUserBets(''); // Get all
        $resolved = 0;

        foreach ($bets as $bet) {
            if ($bet->matchId === $matchId && $bet->status === 'pending') {
                $this->resolveBet($bet->id, $result);
                $resolved++;
            }
        }

        return $resolved;
    }

    public function resolveBet(string $betId, string $result): bool
    {
        $bets = $this->dataService->load('bets.json');
        
        foreach ($bets as &$betData) {
            if ($betData['id'] === $betId && $betData['status'] === 'pending') {
                $bet = Bet::fromArray($betData);
                
                if ($bet->prediction === $result) {
                    $betData['status'] = 'won';
                    $winAmount = (int)round($bet->amount * $bet->odds);
                    $betData['winAmount'] = $winAmount;
                    
                    // Award winnings
                    $user = $this->userService->getUserById($bet->userId);
                    if ($user) {
                        $user->addPoints($winAmount);
                        $this->userService->updateUser($user);
                    }
                } else {
                    $betData['status'] = 'lost';
                    $betData['winAmount'] = 0;
                }
                
                $betData['resolvedAt'] = time();
                $this->dataService->save('bets.json', $bets);
                return true;
            }
        }
        
        return false;
    }

    public function cancelBet(string $betId, string $userId): bool
    {
        $bets = $this->dataService->load('bets.json');
        
        foreach ($bets as &$betData) {
            if ($betData['id'] === $betId && $betData['userId'] === $userId && $betData['status'] === 'pending') {
                $bet = Bet::fromArray($betData);
                
                // Refund
                $user = $this->userService->getUserById($userId);
                if ($user) {
                    $user->addPoints($bet->amount);
                    $this->userService->updateUser($user);
                }
                
                $betData['status'] = 'cancelled';
                $betData['resolvedAt'] = time();
                $this->dataService->save('bets.json', $bets);
                return true;
            }
        }
        
        return false;
    }
}
EOF

mv app/Services/BettingService.php app/Services/BettingService.php.bak
mv app/Services/BettingService.php.new app/Services/BettingService.php

echo "✅ BettingService updated"
echo "✅ All updates complete!"
echo ""
echo "📝 Next steps:"
echo "1. Review changes"
echo "2. Test the application"
echo "3. Create matches via /matches/create"
