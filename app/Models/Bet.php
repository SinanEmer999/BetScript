<?php

declare(strict_types=1);

namespace BetScript\Models;

class Bet
{
    private string $id;
    private string $userId;
    private string $matchId;
    private string $prediction; // 'player1', 'player2', 'draw'
    private int $amount;
    private float $odds;
    private int $potentialWin;
    private string $status; // 'pending', 'won', 'lost', 'cancelled'
    private \DateTime $placedAt;
    private ?\DateTime $resolvedAt;

    public function __construct(
        string $id,
        string $userId,
        string $matchId,
        string $prediction,
        int $amount,
        float $odds
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->matchId = $matchId;
        $this->prediction = $prediction;
        $this->amount = $amount;
        $this->odds = $odds;
        $this->potentialWin = (int)($amount * $odds);
        $this->status = 'pending';
        $this->placedAt = new \DateTime();
        $this->resolvedAt = null;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getMatchId(): string
    {
        return $this->matchId;
    }

    public function getPrediction(): string
    {
        return $this->prediction;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getOdds(): float
    {
        return $this->odds;
    }

    public function getPotentialWin(): int
    {
        return $this->potentialWin;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        if (in_array($status, ['won', 'lost', 'cancelled'])) {
            $this->resolvedAt = new \DateTime();
        }
    }

    public function getPlacedAt(): \DateTime
    {
        return $this->placedAt;
    }

    public function getResolvedAt(): ?\DateTime
    {
        return $this->resolvedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'matchId' => $this->matchId,
            'prediction' => $this->prediction,
            'amount' => $this->amount,
            'odds' => $this->odds,
            'potentialWin' => $this->potentialWin,
            'status' => $this->status,
            'placedAt' => $this->placedAt->format('Y-m-d H:i:s'),
            'resolvedAt' => $this->resolvedAt ? $this->resolvedAt->format('Y-m-d H:i:s') : null,
        ];
    }

    public static function fromArray(array $data): self
    {
        $bet = new self(
            $data['id'],
            $data['userId'],
            $data['matchId'],
            $data['prediction'],
            $data['amount'],
            $data['odds']
        );

        $bet->status = $data['status'];
        if (isset($data['placedAt'])) {
            $bet->placedAt = new \DateTime($data['placedAt']);
        }
        if (isset($data['resolvedAt']) && $data['resolvedAt']) {
            $bet->resolvedAt = new \DateTime($data['resolvedAt']);
        }

        return $bet;
    }
}
