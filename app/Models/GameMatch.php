<?php

declare(strict_types=1);

namespace BetScript\Models;

class GameMatch
{
    public string $id;
    public string $player1Id;
    public string $player2Id;
    public string $player1Name;
    public string $player2Name;
    public string $status; // 'upcoming', 'live', 'completed', 'cancelled'
    public ?string $winner; // 'player1', 'player2', 'draw', null
    public ?int $player1Score;
    public ?int $player2Score;
    public ?string $scheduledAt;
    public ?string $completedAt;
    public string $createdAt;

    public function __construct()
    {
        $this->id = uniqid('match_', true);
        $this->status = 'upcoming';
        $this->winner = null;
        $this->player1Score = null;
        $this->player2Score = null;
        $this->scheduledAt = null;
        $this->completedAt = null;
        $this->createdAt = date('Y-m-d H:i:s');
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'player1Id' => $this->player1Id,
            'player2Id' => $this->player2Id,
            'player1Name' => $this->player1Name,
            'player2Name' => $this->player2Name,
            'status' => $this->status,
            'winner' => $this->winner,
            'player1Score' => $this->player1Score,
            'player2Score' => $this->player2Score,
            'scheduledAt' => $this->scheduledAt,
            'completedAt' => $this->completedAt,
            'createdAt' => $this->createdAt,
        ];
    }

    public static function fromArray(array $data): self
    {
        $obj = new self();
        $obj->id = $data['id'];
        $obj->player1Id = $data['player1Id'];
        $obj->player2Id = $data['player2Id'];
        $obj->player1Name = $data['player1Name'];
        $obj->player2Name = $data['player2Name'];
        $obj->status = $data['status'];
        $obj->winner = $data['winner'] ?? null;
        $obj->player1Score = $data['player1Score'] ?? null;
        $obj->player2Score = $data['player2Score'] ?? null;
        $obj->scheduledAt = $data['scheduledAt'] ?? null;
        $obj->completedAt = $data['completedAt'] ?? null;
        $obj->createdAt = $data['createdAt'];
        return $obj;
    }

    public function canBetOn(): bool
    {
        return in_array($this->status, ['upcoming', 'live']);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
