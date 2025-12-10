<?php

declare(strict_types=1);

namespace BetScript\Services;

use BetScript\Models\Bet;

class OddsService
{
    private KickScriptIntegrationService $kickScriptService;

    public function __construct(KickScriptIntegrationService $kickScriptService)
    {
        $this->kickScriptService = $kickScriptService;
    }

    /**
     * Calculate odds based on ELO ratings and recent performance
     */
    public function calculateOdds(string $matchId): array
    {
        $match = $this->kickScriptService->getMatchById($matchId);
        if (!$match) {
            return [
                'player1' => 2.0,
                'player2' => 2.0,
                'draw' => 3.5,
            ];
        }

        $player1Stats = $this->kickScriptService->getPlayerStats($match['player1Id']);
        $player2Stats = $this->kickScriptService->getPlayerStats($match['player2Id']);

        // ELO-based probability
        $elo1 = $player1Stats['elo'] ?? 1500;
        $elo2 = $player2Stats['elo'] ?? 1500;

        $expectedScore1 = 1 / (1 + pow(10, ($elo2 - $elo1) / 400));
        $expectedScore2 = 1 - $expectedScore1;

        // Recent form adjustment (last 5 games)
        $form1 = $this->calculateRecentForm($player1Stats);
        $form2 = $this->calculateRecentForm($player2Stats);

        // Adjust probabilities based on form
        $prob1 = $expectedScore1 * (1 + ($form1 - 0.5) * 0.2);
        $prob2 = $expectedScore2 * (1 + ($form2 - 0.5) * 0.2);

        // Draw probability (typically lower in table football)
        $drawProb = 0.15;

        // Normalize probabilities
        $total = $prob1 + $prob2 + $drawProb;
        $prob1 = $prob1 / $total;
        $prob2 = $prob2 / $total;
        $drawProb = $drawProb / $total;

        // Convert to odds with house edge (5%)
        $houseEdge = 0.95;
        $odds1 = round(($houseEdge / $prob1), 2);
        $odds2 = round(($houseEdge / $prob2), 2);
        $oddsDraw = round(($houseEdge / $drawProb), 2);

        // Minimum odds of 1.10
        $odds1 = max($odds1, 1.10);
        $odds2 = max($odds2, 1.10);
        $oddsDraw = max($oddsDraw, 1.10);

        return [
            'player1' => $odds1,
            'player2' => $odds2,
            'draw' => $oddsDraw,
        ];
    }

    private function calculateRecentForm(array $playerStats): float
    {
        if (!isset($playerStats['recentMatches']) || count($playerStats['recentMatches']) === 0) {
            return 0.5; // Neutral form
        }

        $wins = 0;
        $total = min(5, count($playerStats['recentMatches']));

        foreach (array_slice($playerStats['recentMatches'], -5) as $match) {
            if ($match['result'] === 'win') {
                $wins++;
            }
        }

        return $wins / $total;
    }

    /**
     * Update odds dynamically based on betting volume
     */
    public function adjustOddsForBettingVolume(string $matchId, array $currentOdds, array $bets): array
    {
        $volumeByOutcome = [
            'player1' => 0,
            'player2' => 0,
            'draw' => 0,
        ];

        foreach ($bets as $bet) {
            if ($bet['matchId'] === $matchId && $bet['status'] === 'pending') {
                $volumeByOutcome[$bet['prediction']] += $bet['amount'];
            }
        }

        $totalVolume = array_sum($volumeByOutcome);
        if ($totalVolume === 0) {
            return $currentOdds;
        }

        // Adjust odds based on betting imbalance
        $adjustedOdds = [];
        foreach ($currentOdds as $outcome => $odds) {
            $outcomeShare = $volumeByOutcome[$outcome] / $totalVolume;
            
            // If more people bet on an outcome, reduce the odds slightly
            $adjustment = 1 - ($outcomeShare * 0.1); // Max 10% adjustment
            $adjustedOdds[$outcome] = round($odds * $adjustment, 2);
            $adjustedOdds[$outcome] = max($adjustedOdds[$outcome], 1.10); // Min odds
        }

        return $adjustedOdds;
    }

    /**
     * Get live odds that update based on current game state
     */
    public function getLiveOdds(string $matchId): ?array
    {
        $match = $this->kickScriptService->getMatchById($matchId);
        
        if (!$match || $match['status'] !== 'live') {
            return null;
        }

        $baseOdds = $this->calculateOdds($matchId);
        
        // Adjust odds based on current score (if available)
        if (isset($match['currentScore'])) {
            $score1 = $match['currentScore']['player1'];
            $score2 = $match['currentScore']['player2'];
            $scoreDiff = $score1 - $score2;

            // Significantly adjust odds based on current score
            if ($scoreDiff > 0) {
                $baseOdds['player1'] *= (1 - ($scoreDiff * 0.15));
                $baseOdds['player2'] *= (1 + ($scoreDiff * 0.15));
            } elseif ($scoreDiff < 0) {
                $baseOdds['player1'] *= (1 + (abs($scoreDiff) * 0.15));
                $baseOdds['player2'] *= (1 - (abs($scoreDiff) * 0.15));
            }

            // Ensure minimum odds
            foreach ($baseOdds as $key => $value) {
                $baseOdds[$key] = max(round($value, 2), 1.01);
            }
        }

        return $baseOdds;
    }
}
