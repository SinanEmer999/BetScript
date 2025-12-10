<?php

declare(strict_types=1);

namespace BetScript\Models;

class KickerMatch
{
    private string $id;
    private string $player1Id;
    private string $player2Id;
    private ?int $player1Goals;
    private ?int $player2Goals;
    private \DateTime $scheduledAt;
    private ?DateTime $playedAt;
    private string $status; // 'upcoming', 'live', 'finished'
    private string $seasonId;

    public function __construct(
        string $id,
        string $player1Id,
        string $player2Id,
        \DateTime $scheduledAt,
        string $seasonId,
        ?int $player1Goals = null,
        ?int $player2Goals = null
    ) {
        $this->id = $id;
        $this->player1Id = $player1Id;
        $this->player2Id = $player2Id;
        $this->scheduledAt = $scheduledAt;
        $this->seasonId = $seasonId;
        $this->player1Goals = $player1Goals;
        $this->player2Goals = $player2Goals;
        $this->playedAt = null;
        $this->status = 'upcoming';
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPlayer1Id(): string
    {
        return $this->player1Id;
    }

    public function getPlayer2Id(): string
    {
        return $this->player2Id;
    }

    public function getPlayer1Goals(): ?int
    {
        return $this->player1Goals;
    }

    public function getPlayer2Goals(): ?int
    {
        return $this->player2Goals;
    }

    public function getScheduledAt(): \DateTime
    {
        return $this->scheduledAt;
    }

    public function getPlayedAt(): ?\DateTime
    {
        return $this->playedAt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function setResult(int $player1Goals, int $player2Goals): void
    {
        $this->player1Goals = $player1Goals;
        $this->player2Goals = $player2Goals;
        $this->playedAt = new \DateTime();
        $this->status = 'finished';
    }

    public function getWinner(): ?string
    {
        if ($this->player1Goals === null || $this->player2Goals === null) {
            return null;
        }

        if ($this->player1Goals > $this->player2Goals) {
            return 'player1';
        } elseif ($this->player2Goals > $this->player1Goals) {
            return 'player2';
        }

        return 'draw';
    }

    public function getSeasonId(): string
    {
        return $this->seasonId;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'player1Id' => $this->player1Id,
            'player2Id' => $this->player2Id,
            'player1Goals' => $this->player1Goals,
            'player2Goals' => $this->player2Goals,
            'scheduledAt' => $this->scheduledAt->format('Y-m-d H:i:s'),
            'playedAt' => $this->playedAt ? $this->playedAt->format('Y-m-d H:i:s') : null,
            'status' => $this->status,
            'seasonId' => $this->seasonId,
        ];
    }

    public static function fromArray(array $data): self
    {
        $match = new self(
            $data['id'],
            $data['player1Id'],
            $data['player2Id'],
            new \DateTime($data['scheduledAt']),
            $data['seasonId'],
            $data['player1Goals'] ?? null,
            $data['player2Goals'] ?? null
        );

        $match->status = $data['status'] ?? 'upcoming';
        if (isset($data['playedAt']) && $data['playedAt']) {
            $match->playedAt = new \DateTime($data['playedAt']);
        }

        return $match;
    }
}
