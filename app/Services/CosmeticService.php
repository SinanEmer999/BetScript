<?php

declare(strict_types=1);

namespace BetScript\Services;

use BetScript\Models\Cosmetic;

class CosmeticService
{
    private DataService $dataService;
    private UserService $userService;

    public function __construct(DataService $dataService, UserService $userService)
    {
        $this->dataService = $dataService;
        $this->userService = $userService;
    }

    public function getAllCosmetics(): array
    {
        $data = $this->dataService->loadCosmetics();
        $cosmetics = [];

        foreach ($data as $cosmeticData) {
            $cosmetics[] = Cosmetic::fromArray($cosmeticData);
        }

        return $cosmetics;
    }

    public function getCosmeticsByCategory(string $category): array
    {
        $allCosmetics = $this->getAllCosmetics();
        $filtered = [];

        foreach ($allCosmetics as $cosmetic) {
            if ($cosmetic->getCategory() === $category) {
                $filtered[] = $cosmetic;
            }
        }

        return $filtered;
    }

    public function getCosmeticById(string $id): ?Cosmetic
    {
        $cosmetics = $this->getAllCosmetics();

        foreach ($cosmetics as $cosmetic) {
            if ($cosmetic->getId() === $id) {
                return $cosmetic;
            }
        }

        return null;
    }

    public function purchaseCosmetic(string $userId, string $cosmeticId): bool
    {
        $user = $this->userService->getUserById($userId);
        if (!$user) {
            return false;
        }

        $cosmetic = $this->getCosmeticById($cosmeticId);
        if (!$cosmetic) {
            return false;
        }

        // Check if user already owns this cosmetic
        if (in_array($cosmeticId, $user->getCosmetics())) {
            return false;
        }

        // Check if user has enough points
        if ($user->getFietzPoints() < $cosmetic->getPrice()) {
            return false;
        }

        // Deduct points and add cosmetic
        if ($user->deductPoints($cosmetic->getPrice())) {
            $user->addCosmetic($cosmeticId);
            return $this->userService->updateUser($user);
        }

        return false;
    }

    public function initializeDefaultCosmetics(): void
    {
        $defaultCosmetics = [
            // Hats
            [
                'id' => 'hat_cap_black',
                'name' => 'Black Cap',
                'category' => 'hat',
                'rarity' => 'common',
                'price' => 100,
                'imageUrl' => '/assets/cosmetics/hat_cap_black.png',
                'description' => 'Classic black baseball cap',
            ],
            [
                'id' => 'hat_beanie_red',
                'name' => 'Red Beanie',
                'category' => 'hat',
                'rarity' => 'common',
                'price' => 150,
                'imageUrl' => '/assets/cosmetics/hat_beanie_red.png',
                'description' => 'Warm red beanie',
            ],
            [
                'id' => 'hat_crown_gold',
                'name' => 'Golden Crown',
                'category' => 'hat',
                'rarity' => 'legendary',
                'price' => 5000,
                'imageUrl' => '/assets/cosmetics/hat_crown_gold.png',
                'description' => 'For the true champion',
            ],
            
            // Glasses
            [
                'id' => 'glasses_aviator',
                'name' => 'Aviator Sunglasses',
                'category' => 'glasses',
                'rarity' => 'rare',
                'price' => 500,
                'imageUrl' => '/assets/cosmetics/glasses_aviator.png',
                'description' => 'Cool aviator shades',
            ],
            [
                'id' => 'glasses_nerd',
                'name' => 'Nerd Glasses',
                'category' => 'glasses',
                'rarity' => 'common',
                'price' => 200,
                'imageUrl' => '/assets/cosmetics/glasses_nerd.png',
                'description' => 'Classic nerd glasses',
            ],
            
            // Backgrounds
            [
                'id' => 'bg_gradient_blue',
                'name' => 'Blue Gradient',
                'category' => 'background',
                'rarity' => 'common',
                'price' => 50,
                'imageUrl' => '/assets/cosmetics/bg_gradient_blue.png',
                'description' => 'Smooth blue gradient background',
            ],
            [
                'id' => 'bg_galaxy',
                'name' => 'Galaxy',
                'category' => 'background',
                'rarity' => 'epic',
                'price' => 2000,
                'imageUrl' => '/assets/cosmetics/bg_galaxy.png',
                'description' => 'Stunning galaxy background',
            ],
            
            // Frames
            [
                'id' => 'frame_gold',
                'name' => 'Golden Frame',
                'category' => 'frame',
                'rarity' => 'epic',
                'price' => 1500,
                'imageUrl' => '/assets/cosmetics/frame_gold.png',
                'description' => 'Luxurious golden border',
            ],
            [
                'id' => 'frame_neon',
                'name' => 'Neon Frame',
                'category' => 'frame',
                'rarity' => 'rare',
                'price' => 800,
                'imageUrl' => '/assets/cosmetics/frame_neon.png',
                'description' => 'Glowing neon border',
            ],
            
            // Badges
            [
                'id' => 'badge_winner',
                'name' => 'Winner Badge',
                'category' => 'badge',
                'rarity' => 'rare',
                'price' => 1000,
                'imageUrl' => '/assets/cosmetics/badge_winner.png',
                'description' => 'For consistent winners',
            ],
            [
                'id' => 'badge_highroller',
                'name' => 'High Roller',
                'category' => 'badge',
                'rarity' => 'legendary',
                'price' => 10000,
                'imageUrl' => '/assets/cosmetics/badge_highroller.png',
                'description' => 'Only for the biggest gamblers',
            ],
        ];

        $cosmetics = [];
        foreach ($defaultCosmetics as $data) {
            $cosmetics[] = $data;
        }

        $this->dataService->saveCosmetics($cosmetics);
    }
}
