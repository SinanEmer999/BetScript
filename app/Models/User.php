<?php

declare(strict_types=1);

namespace BetScript\Models;

class User
{
    private string $id;
    private string $username;
    private string $email;
    private string $passwordHash;
    private int $fietzPoints;
    private array $cosmetics;
    private array $avatar;
    private \DateTime $createdAt;
    private \DateTime $lastLogin;
    private int $totalBets;
    private int $wonBets;
    private int $totalWinnings;

    public function __construct(
        string $id,
        string $username,
        string $email,
        string $passwordHash,
        int $fietzPoints = 1000,
        array $cosmetics = [],
        array $avatar = []
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->fietzPoints = $fietzPoints;
        $this->cosmetics = $cosmetics;
        $this->avatar = $avatar;
        $this->createdAt = new \DateTime();
        $this->lastLogin = new \DateTime();
        $this->totalBets = 0;
        $this->wonBets = 0;
        $this->totalWinnings = 0;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getFietzPoints(): int
    {
        return $this->fietzPoints;
    }

    public function addPoints(int $points): void
    {
        $this->fietzPoints += $points;
    }

    public function deductPoints(int $points): bool
    {
        if ($this->fietzPoints >= $points) {
            $this->fietzPoints -= $points;
            return true;
        }
        return false;
    }

    public function getCosmetics(): array
    {
        return $this->cosmetics;
    }

    public function addCosmetic(string $cosmeticId): void
    {
        if (!in_array($cosmeticId, $this->cosmetics)) {
            $this->cosmetics[] = $cosmeticId;
        }
    }

    public function getAvatar(): array
    {
        return $this->avatar;
    }

    public function updateAvatar(array $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function updateLastLogin(): void
    {
        $this->lastLogin = new \DateTime();
    }

    public function getTotalBets(): int
    {
        return $this->totalBets;
    }

    public function incrementBets(): void
    {
        $this->totalBets++;
    }

    public function getWonBets(): int
    {
        return $this->wonBets;
    }

    public function incrementWonBets(): void
    {
        $this->wonBets++;
    }

    public function getWinRate(): float
    {
        if ($this->totalBets === 0) {
            return 0.0;
        }
        return ($this->wonBets / $this->totalBets) * 100;
    }

    public function getTotalWinnings(): int
    {
        return $this->totalWinnings;
    }

    public function addWinnings(int $amount): void
    {
        $this->totalWinnings += $amount;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'passwordHash' => $this->passwordHash,
            'fietzPoints' => $this->fietzPoints,
            'cosmetics' => $this->cosmetics,
            'avatar' => $this->avatar,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'lastLogin' => $this->lastLogin->format('Y-m-d H:i:s'),
            'totalBets' => $this->totalBets,
            'wonBets' => $this->wonBets,
            'totalWinnings' => $this->totalWinnings,
        ];
    }

    public static function fromArray(array $data): self
    {
        $user = new self(
            $data['id'],
            $data['username'],
            $data['email'],
            $data['passwordHash'],
            $data['fietzPoints'] ?? 1000,
            $data['cosmetics'] ?? [],
            $data['avatar'] ?? []
        );

        if (isset($data['createdAt'])) {
            $user->createdAt = new \DateTime($data['createdAt']);
        }
        if (isset($data['lastLogin'])) {
            $user->lastLogin = new \DateTime($data['lastLogin']);
        }

        $user->totalBets = $data['totalBets'] ?? 0;
        $user->wonBets = $data['wonBets'] ?? 0;
        $user->totalWinnings = $data['totalWinnings'] ?? 0;

        return $user;
    }
}
