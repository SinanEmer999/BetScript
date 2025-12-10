<?php

declare(strict_types=1);

namespace BetScript\Services;

use BetScript\Models\User;

class UserService
{
    private DataService $dataService;

    public function __construct(DataService $dataService)
    {
        $this->dataService = $dataService;
    }

    public function createUser(string $username, string $email, string $password): ?User
    {
        $users = $this->dataService->loadUsers();

        // Check if username or email already exists
        foreach ($users as $user) {
            if ($user->getUsername() === $username || $user->getEmail() === $email) {
                return null;
            }
        }

        $id = $this->generateUserId();
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $initialPoints = (int)($_ENV['INITIAL_POINTS'] ?? 1000);

        $user = new User($id, $username, $email, $passwordHash, $initialPoints);
        $users[$id] = $user;

        $this->dataService->saveUsers($users);
        return $user;
    }

    public function authenticate(string $username, string $password): ?User
    {
        $users = $this->dataService->loadUsers();

        foreach ($users as $user) {
            if ($user->getUsername() === $username || $user->getEmail() === $username) {
                if (password_verify($password, $user->getPasswordHash())) {
                    $user->updateLastLogin();
                    $this->dataService->saveUsers($users);
                    return $user;
                }
            }
        }

        return null;
    }

    public function getUserById(string $userId): ?User
    {
        $users = $this->dataService->loadUsers();
        return $users[$userId] ?? null;
    }

    public function updateUser(User $user): bool
    {
        $users = $this->dataService->loadUsers();
        $users[$user->getId()] = $user;
        return $this->dataService->saveUsers($users);
    }

    public function addDailyBonus(string $userId): int
    {
        $user = $this->getUserById($userId);
        if (!$user) {
            return 0;
        }

        $bonus = (int)($_ENV['DAILY_BONUS'] ?? 100);
        $user->addPoints($bonus);
        $this->updateUser($user);

        return $bonus;
    }

    public function getLeaderboard(int $limit = 10): array
    {
        $users = $this->dataService->loadUsers();
        
        usort($users, function ($a, $b) {
            return $b->getFietzPoints() <=> $a->getFietzPoints();
        });

        return array_slice($users, 0, $limit);
    }

    public function getAllUsers(): array
    {
        return array_values($this->dataService->loadUsers());
    }

    private function generateUserId(): string
    {
        return uniqid('usr_', true);
    }
}
