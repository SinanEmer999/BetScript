<?php

declare(strict_types=1);

namespace BetScript\Services;

class KickScriptIntegrationService
{
    private string $kickScriptDataPath;
    private ?string $kickScriptApiUrl;

    public function __construct()
    {
        $this->kickScriptDataPath = $_ENV['KICKSCRIPT_DATA_PATH'] ?? '../kickScript/kickLiga/data';
        $this->kickScriptApiUrl = $_ENV['KICKSCRIPT_API_URL'] ?? null;
    }

    /**
     * Get all matches from KickScript
     */
    public function getAllMatches(): array
    {
        $matchesFile = $this->kickScriptDataPath . '/matches.json';
        
        if (!file_exists($matchesFile)) {
            return [];
        }

        $content = file_get_contents($matchesFile);
        $data = json_decode($content, true);
        
        return $data ?? [];
    }

    /**
     * Get specific match by ID
     */
    public function getMatchById(string $matchId): ?array
    {
        $matches = $this->getAllMatches();
        
        foreach ($matches as $match) {
            if ($match['id'] === $matchId) {
                return $match;
            }
        }
        
        return null;
    }

    /**
     * Get upcoming matches that can be bet on
     */
    public function getUpcomingMatches(): array
    {
        $matches = $this->getAllMatches();
        $upcoming = [];
        
        foreach ($matches as $match) {
            // Only include matches that haven't been played yet
            if (!isset($match['player1Goals']) || !isset($match['player2Goals'])) {
                $upcoming[] = $match;
            }
        }
        
        return $upcoming;
    }

    /**
     * Get player statistics from KickScript
     */
    public function getPlayerStats(string $playerId): array
    {
        $playersFile = $this->kickScriptDataPath . '/players.json';
        
        if (!file_exists($playersFile)) {
            return [
                'id' => $playerId,
                'name' => 'Unknown Player',
                'elo' => 1500,
                'wins' => 0,
                'losses' => 0,
                'draws' => 0,
                'recentMatches' => [],
            ];
        }

        $content = file_get_contents($playersFile);
        $players = json_decode($content, true) ?? [];
        
        foreach ($players as $player) {
            if ($player['id'] === $playerId) {
                // Calculate recent form
                $matches = $this->getPlayerMatches($playerId);
                $player['recentMatches'] = array_slice($matches, -5);
                return $player;
            }
        }
        
        return [
            'id' => $playerId,
            'name' => 'Unknown Player',
            'elo' => 1500,
            'wins' => 0,
            'losses' => 0,
            'draws' => 0,
            'recentMatches' => [],
        ];
    }

    /**
     * Get all matches for a specific player
     */
    private function getPlayerMatches(string $playerId): array
    {
        $allMatches = $this->getAllMatches();
        $playerMatches = [];
        
        foreach ($allMatches as $match) {
            if (isset($match['player1Id'], $match['player2Id'])) {
                if ($match['player1Id'] === $playerId || $match['player2Id'] === $playerId) {
                    // Determine result
                    if (isset($match['player1Goals'], $match['player2Goals'])) {
                        $isPlayer1 = $match['player1Id'] === $playerId;
                        $playerGoals = $isPlayer1 ? $match['player1Goals'] : $match['player2Goals'];
                        $opponentGoals = $isPlayer1 ? $match['player2Goals'] : $match['player1Goals'];
                        
                        if ($playerGoals > $opponentGoals) {
                            $match['result'] = 'win';
                        } elseif ($playerGoals < $opponentGoals) {
                            $match['result'] = 'loss';
                        } else {
                            $match['result'] = 'draw';
                        }
                        
                        $playerMatches[] = $match;
                    }
                }
            }
        }
        
        return $playerMatches;
    }

    /**
     * Get current season information
     */
    public function getCurrentSeason(): ?array
    {
        $seasonsFile = $this->kickScriptDataPath . '/seasons.json';
        
        if (!file_exists($seasonsFile)) {
            return null;
        }

        $content = file_get_contents($seasonsFile);
        $seasons = json_decode($content, true) ?? [];
        
        // Find the most recent season
        foreach (array_reverse($seasons) as $season) {
            if (isset($season['active']) && $season['active']) {
                return $season;
            }
        }
        
        return $seasons[count($seasons) - 1] ?? null;
    }

    /**
     * Get all players
     */
    public function getAllPlayers(): array
    {
        $playersFile = $this->kickScriptDataPath . '/players.json';
        
        if (!file_exists($playersFile)) {
            return [];
        }

        $content = file_get_contents($playersFile);
        return json_decode($content, true) ?? [];
    }
}
