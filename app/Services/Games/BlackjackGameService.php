<?php

declare(strict_types=1);

namespace BetScript\Services\Games;

use BetScript\Services\UserService;
use BetScript\Services\DataService;

class BlackjackGameService
{
    private UserService $userService;
    private DataService $dataService;

    public function __construct(UserService $userService, DataService $dataService)
    {
        $this->userService = $userService;
        $this->dataService = $dataService;
    }

    public function startGame(string $userId, int $betAmount): ?array
    {
        $user = $this->userService->getUserById($userId);
        if (!$user || !$user->deductPoints($betAmount)) {
            return null;
        }

        $deck = $this->createDeck();
        $playerHand = [$this->drawCard($deck), $this->drawCard($deck)];
        $dealerHand = [$this->drawCard($deck)];

        $gameId = uniqid('blackjack_', true);
        $game = [
            'id' => $gameId,
            'userId' => $userId,
            'betAmount' => $betAmount,
            'deck' => $deck,
            'playerHand' => $playerHand,
            'dealerHand' => $dealerHand,
            'status' => 'active',
            'winAmount' => 0,
            'startedAt' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];

        $this->userService->updateUser($user);
        $this->saveGameState($game);

        return [
            'gameId' => $gameId,
            'playerHand' => $playerHand,
            'dealerHand' => $dealerHand,
            'playerScore' => $this->calculateScore($playerHand),
        ];
    }

    public function hit(string $gameId): ?array
    {
        $games = $this->loadActiveGames();
        
        foreach ($games as &$game) {
            if ($game['id'] === $gameId && $game['status'] === 'active') {
                $card = $this->drawCard($game['deck']);
                $game['playerHand'][] = $card;
                
                $playerScore = $this->calculateScore($game['playerHand']);
                
                // Check if bust
                if ($playerScore > 21) {
                    $game['status'] = 'lost';
                    $this->saveGameState($game);
                    
                    return [
                        'playerHand' => $game['playerHand'],
                        'playerScore' => $playerScore,
                        'status' => 'bust',
                        'winAmount' => 0,
                    ];
                }
                
                $this->saveGameState($game);
                
                return [
                    'playerHand' => $game['playerHand'],
                    'playerScore' => $playerScore,
                    'status' => 'active',
                ];
            }
        }
        
        return null;
    }

    public function stand(string $gameId): ?array
    {
        $games = $this->loadActiveGames();
        
        foreach ($games as &$game) {
            if ($game['id'] === $gameId && $game['status'] === 'active') {
                // Dealer plays
                while ($this->calculateScore($game['dealerHand']) < 17) {
                    $game['dealerHand'][] = $this->drawCard($game['deck']);
                }
                
                $playerScore = $this->calculateScore($game['playerHand']);
                $dealerScore = $this->calculateScore($game['dealerHand']);
                
                // Determine winner
                if ($dealerScore > 21 || $playerScore > $dealerScore) {
                    $game['status'] = 'won';
                    $game['winAmount'] = $game['betAmount'] * 2;
                } elseif ($playerScore === $dealerScore) {
                    $game['status'] = 'push';
                    $game['winAmount'] = $game['betAmount']; // Return bet
                } else {
                    $game['status'] = 'lost';
                    $game['winAmount'] = 0;
                }
                
                // Award winnings
                if ($game['winAmount'] > 0) {
                    $user = $this->userService->getUserById($game['userId']);
                    if ($user) {
                        $user->addPoints($game['winAmount']);
                        $this->userService->updateUser($user);
                    }
                }
                
                $this->saveGameState($game);
                
                return [
                    'playerHand' => $game['playerHand'],
                    'dealerHand' => $game['dealerHand'],
                    'playerScore' => $playerScore,
                    'dealerScore' => $dealerScore,
                    'status' => $game['status'],
                    'winAmount' => $game['winAmount'],
                ];
            }
        }
        
        return null;
    }

    private function createDeck(): array
    {
        $suits = ['♠', '♥', '♦', '♣'];
        $values = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];
        $deck = [];
        
        foreach ($suits as $suit) {
            foreach ($values as $value) {
                $deck[] = ['suit' => $suit, 'value' => $value];
            }
        }
        
        shuffle($deck);
        return $deck;
    }

    private function drawCard(array &$deck): array
    {
        return array_pop($deck);
    }

    private function calculateScore(array $hand): int
    {
        $score = 0;
        $aces = 0;
        
        foreach ($hand as $card) {
            $value = $card['value'];
            
            if ($value === 'A') {
                $aces++;
                $score += 11;
            } elseif (in_array($value, ['J', 'Q', 'K'])) {
                $score += 10;
            } else {
                $score += (int)$value;
            }
        }
        
        // Adjust for aces
        while ($score > 21 && $aces > 0) {
            $score -= 10;
            $aces--;
        }
        
        return $score;
    }

    private function saveGameState(array $game): void
    {
        $games = $this->dataService->loadGameResults('blackjack');
        $found = false;
        
        foreach ($games as &$g) {
            if ($g['id'] === $game['id']) {
                $g = $game;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $games[] = $game;
        }
        
        $this->dataService->saveGameResults('blackjack', $games);
    }

    private function loadActiveGames(): array
    {
        return $this->dataService->loadGameResults('blackjack');
    }

    public function getGameHistory(string $userId, int $limit = 10): array
    {
        $games = $this->dataService->loadGameResults('blackjack');
        $userGames = [];
        
        foreach ($games as $game) {
            if ($game['userId'] === $userId && $game['status'] !== 'active') {
                $userGames[] = $game;
            }
        }
        
        return array_slice(array_reverse($userGames), 0, $limit);
    }
}
