<?php

declare(strict_types=1);

namespace BetScript\Services;

use BetScript\Models\User;

class DataService
{
    private string $dataPath;

    public function __construct(?string $dataPath = null)
    {
        $this->dataPath = $dataPath ?? __DIR__ . '/../../data';
        $this->ensureDirectoryExists();
    }

    private function ensureDirectoryExists(): void
    {
        if (!is_dir($this->dataPath)) {
            mkdir($this->dataPath, 0755, true);
        }
    }

    private function getFilePath(string $filename): string
    {
        return $this->dataPath . '/' . $filename;
    }

    public function load(string $filename): array
    {
        $filepath = $this->getFilePath($filename);
        
        if (!file_exists($filepath)) {
            return [];
        }

        $content = file_get_contents($filepath);
        if ($content === false) {
            return [];
        }

        $data = json_decode($content, true);
        return $data ?? [];
    }

    public function save(string $filename, array $data): bool
    {
        $filepath = $this->getFilePath($filename);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        $fp = fopen($filepath, 'c');
        if (!$fp) {
            return false;
        }

        if (flock($fp, LOCK_EX)) {
            ftruncate($fp, 0);
            fwrite($fp, $json);
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
            return true;
        }

        fclose($fp);
        return false;
    }

    public function loadUsers(): array
    {
        $data = $this->load('users.json');
        $users = [];
        
        foreach ($data as $userData) {
            $users[$userData['id']] = User::fromArray($userData);
        }
        
        return $users;
    }

    public function saveUsers(array $users): bool
    {
        $data = [];
        foreach ($users as $user) {
            $data[] = $user->toArray();
        }
        
        return $this->save('users.json', $data);
    }

    public function loadBets(): array
    {
        return $this->load('bets.json');
    }

    public function saveBets(array $bets): bool
    {
        return $this->save('bets.json', $bets);
    }

    public function loadCosmetics(): array
    {
        return $this->load('cosmetics.json');
    }

    public function saveCosmetics(array $cosmetics): bool
    {
        return $this->save('cosmetics.json', $cosmetics);
    }

    public function loadMatches(): array
    {
        return $this->load('matches.json');
    }

    public function saveMatches(array $matches): bool
    {
        return $this->save('matches.json', $matches);
    }

    public function loadGameResults(string $game): array
    {
        return $this->load("game_{$game}.json");
    }

    public function saveGameResults(string $game, array $results): bool
    {
        return $this->save("game_{$game}.json", $results);
    }
}
