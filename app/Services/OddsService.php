<?php

declare(strict_types=1);

namespace BetScript\Services;

use BetScript\Models\Bet;

class OddsService
{
    private MatchService $matchService;
    private BettingService $bettingService;
    private const HOUSE_EDGE = 0.05; // 5% house edge
    private const MIN_ODDS = 1.10;

    public function __construct(MatchService $matchService)
    {
        $this->matchService = $matchService;
    }

    public function setBettingService(BettingService $bettingService): void
    {
        $this->bettingService = $bettingService;
    }

    /**
     * Calculate odds based on player win/loss statistics
     */
    public function calculateOdds(string $matchId): array
    {
        $match = $this->matchService->getMatchById($matchId);
        if (!$match) {
            return [
                'player1' => 2.0,
                'player2' => 2.0,
                'draw' => 3.5,
            ];
        }

        $player1Stats = $this->matchService->getPlayerStats($match->player1Id);
        $player2Stats = $this->matchService->getPlayerStats($match->player2Id);

        // Win rate based probability
        $winRate1 = $player1Stats['winRate'];
        $winRate2 = $player2Stats['winRate'];

        // Normalize probabilities (ensure they sum to ~1)
        $totalWinRate = $winRate1 + $winRate2;
        if ($totalWinRate > 0) {
            $prob1 = $winRate1 / $totalWinRate * 0.85; // 85% for win probabilities
            $prob2 = $winRate2 / $totalWinRate * 0.85;
        } else {
            $prob1 = 0.425;
            $prob2 = 0.425;
        }

        // Draw probability (15% base)
        $probDraw = 0.15;
        $drawProb = 0.15;

        // Normalize probabilities
        $total = $prob1 + $prob2 + $drawProb;
        $prob1 = $prob1 / $total;
        $prob2 = $prob2 / $total;
        $drawProb = $drawProb / $total;

        // Convert to odds with house edge
        $odds1 = round(((1 - self::HOUSE_EDGE) / $prob1), 2);
        $odds2 = round(((1 - self::HOUSE_EDGE) / $prob2), 2);
        $oddsDraw = round(((1 - self::HOUSE_EDGE) / $probDraw), 2);

        // Minimum odds
        $odds1 = max($odds1, self::MIN_ODDS);
        $odds2 = max($odds2, self::MIN_ODDS);
        $oddsDraw = max($oddsDraw, self::MIN_ODDS);

        return [
            'player1' => $odds1,
            'player2' => $odds2,
            'draw' => $oddsDraw,
        ];
    }

    /**
     * Update odds dynamically based on betting volume
     */
    public function adjustOddsForBettingVolume(string $matchId, array $currentOdds): array
    {
        if (!$this->bettingService) {
            return $currentOdds;
        }

        $bets = $this->bettingService->getMatchBets($matchId);
        
        $volumeByOutcome = [
            'player1' => 0,
            'player2' => 0,
            'draw' => 0,
        ];

        foreach ($bets as $bet) {
            if ($bet->status === 'pending') {
                $volumeByOutcome[$bet->prediction] += $bet->amount;
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
            $adjustedOdds[$outcome] = max($adjustedOdds[$outcome], self::MIN_ODDS);
        }

        return $adjustedOdds;
    }

    /**
     * Get live odds that update based on current game state
     */
    public function getLiveOdds(string $matchId): ?array
    {
        $match = $this->matchService->getMatchById($matchId);
        
        if (!$match || $match->status !== 'live') {
            return null;
        }

        $baseOdds = $this->calculateOdds($matchId);
        
        // Adjust odds based on current score
        if ($match->score1 !== null && $match->score2 !== null) {
            $scoreDiff = $match->score1 - $match->score2;

            // Adjust odds based on score difference
            if ($scoreDiff > 0) {
                $baseOdds['player1'] *= (1 - ($scoreDiff * 0.15));
                $baseOdds['player2'] *= (1 + ($scoreDiff * 0.15));
            } elseif ($scoreDiff < 0) {
                $baseOdds['player1'] *= (1 + (abs($scoreDiff) * 0.15));
                $baseOdds['player2'] *= (1 - (abs($scoreDiff) * 0.15));
            }

            // Ensure minimum odds
            foreach ($baseOdds as $key => $value) {
                $baseOdds[$key] = max($value, self::MIN_ODDS);
            }
        }

        return $baseOdds;
    }
}
