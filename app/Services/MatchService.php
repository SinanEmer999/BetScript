<?php

declare(strict_types=1);

namespace BetScript\Services;

use BetScript\Models\GameMatch;

class MatchService
{
    private DataService $dataService;

    public function __construct(DataService $dataService)
    {
        $this->dataService = $dataService;
    }

    public function createMatch(string $player1Id, string $player2Id, string $player1Name, string $player2Name): GameMatch
    {
        $gameMatch = new GameMatch();
        $gameMatch->player1Id = $player1Id;
        $gameMatch->player2Id = $player2Id;
        $gameMatch->player1Name = $player1Name;
        $gameMatch->player2Name = $player2Name;

        $matches = $this->getAllMatches();
        $matches[] = $gameMatch->toArray();
        $this->dataService->save('matches.json', $matches);

        return $gameMatch;
    }

    public function getMatchById(string $matchId): ?GameMatch
    {
        $matches = $this->getAllMatches();
        foreach ($matches as $matchData) {
            if ($matchData['id'] === $matchId) {
                return GameMatch::fromArray($matchData);
            }
        }
        return null;
    }

    public function getAllMatches(): array
    {
        return $this->dataService->load('matches.json');
    }

    public function getUpcomingMatches(): array
    {
        $matches = $this->getAllMatches();
        $upcoming = [];
        foreach ($matches as $matchData) {
            if ($matchData['status'] === 'upcoming') {
                $upcoming[] = GameMatch::fromArray($matchData);
            }
        }
        return $upcoming;
    }

    public function getLiveMatches(): array
    {
        $matches = $this->getAllMatches();
        $live = [];
        foreach ($matches as $matchData) {
            if ($matchData['status'] === 'live') {
                $live[] = GameMatch::fromArray($matchData);
            }
        }
        return $live;
    }

    public function getCompletedMatches(): array
    {
        $matches = $this->getAllMatches();
        $completed = [];
        foreach ($matches as $matchData) {
            if ($matchData['status'] === 'completed') {
                $completed[] = GameMatch::fromArray($matchData);
            }
        }
        return $completed;
    }

    public function startMatch(string $matchId): bool
    {
        $matches = $this->getAllMatches();
        foreach ($matches as &$matchData) {
            if ($matchData['id'] === $matchId) {
                $matchData['status'] = 'live';
                return $this->dataService->save('matches.json', $matches);
            }
        }
        return false;
    }

    public function completeMatch(string $matchId, int $player1Score, int $player2Score): bool
    {
        $matches = $this->getAllMatches();
        foreach ($matches as &$matchData) {
            if ($matchData['id'] === $matchId) {
                $matchData['status'] = 'completed';
                $matchData['player1Score'] = $player1Score;
                $matchData['player2Score'] = $player2Score;
                
                if ($player1Score > $player2Score) {
                    $matchData['winner'] = 'player1';
                } elseif ($player2Score > $player1Score) {
                    $matchData['winner'] = 'player2';
                } else {
                    $matchData['winner'] = 'draw';
                }
                
                $matchData['completedAt'] = date('Y-m-d H:i:s');
                return $this->dataService->save('matches.json', $matches);
            }
        }
        return false;
    }

    public function cancelMatch(string $matchId): bool
    {
        $matches = $this->getAllMatches();
        foreach ($matches as &$matchData) {
            if ($matchData['id'] === $matchId) {
                $matchData['status'] = 'cancelled';
                return $this->dataService->save('matches.json', $matches);
            }
        }
        return false;
    }

    public function getPlayerStats(string $playerId): array
    {
        $matches = $this->getCompletedMatches();
        $wins = 0;
        $losses = 0;
        $draws = 0;

        foreach ($matches as $gameMatch) {
            if ($gameMatch->player1Id === $playerId) {
                if ($gameMatch->winner === 'player1') {
                    $wins++;
                } elseif ($gameMatch->winner === 'player2') {
                    $losses++;
                } else {
                    $draws++;
                }
            } elseif ($gameMatch->player2Id === $playerId) {
                if ($gameMatch->winner === 'player2') {
                    $wins++;
                } elseif ($gameMatch->winner === 'player1') {
                    $losses++;
                } else {
                    $draws++;
                }
            }
        }

        $totalMatches = $wins + $losses + $draws;
        $winRate = $totalMatches > 0 ? $wins / $totalMatches : 0.5;

        return [
            'wins' => $wins,
            'losses' => $losses,
            'draws' => $draws,
            'totalMatches' => $totalMatches,
            'winRate' => $winRate
        ];
    }
}
