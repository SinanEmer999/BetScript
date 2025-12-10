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
                'id' => 'hat_cap',
                'name' => 'Baseball Cap',
                'category' => 'hat',
                'rarity' => 'common',
                'price' => 150,
                'imageUrl' => '/assets/images/cosmetics/hat_cap.svg',
                'description' => 'Classic red baseball cap',
            ],
            [
                'id' => 'hat_tophat',
                'name' => 'Top Hat',
                'category' => 'hat',
                'rarity' => 'epic',
                'price' => 1500,
                'imageUrl' => '/assets/images/cosmetics/hat_tophat.svg',
                'description' => 'Classy gentleman\'s top hat',
            ],
            [
                'id' => 'hat_crown',
                'name' => 'Royal Crown',
                'category' => 'hat',
                'rarity' => 'legendary',
                'price' => 5000,
                'imageUrl' => '/assets/images/cosmetics/hat_crown.svg',
                'description' => 'For the true FIETZ champion',
            ],
            
            // Glasses
            [
                'id' => 'glasses_nerd',
                'name' => 'Nerd Glasses',
                'category' => 'glasses',
                'rarity' => 'common',
                'price' => 100,
                'imageUrl' => '/assets/images/cosmetics/glasses_nerd.svg',
                'description' => 'Classic nerd glasses',
            ],
            [
                'id' => 'glasses_shades',
                'name' => 'Cool Shades',
                'category' => 'glasses',
                'rarity' => 'rare',
                'price' => 800,
                'imageUrl' => '/assets/images/cosmetics/glasses_shades.svg',
                'description' => 'Badass sunglasses',
            ],
            
            // Backgrounds
            [
                'id' => 'bg_sunset',
                'name' => 'Sunset Sky',
                'category' => 'background',
                'rarity' => 'rare',
                'price' => 500,
                'imageUrl' => '/assets/images/cosmetics/bg_sunset.svg',
                'description' => 'Beautiful sunset gradient',
            ],
            [
                'id' => 'bg_space',
                'name' => 'Deep Space',
                'category' => 'background',
                'rarity' => 'epic',
                'price' => 1200,
                'imageUrl' => '/assets/images/cosmetics/bg_space.svg',
                'description' => 'Starry space background',
            ],
            
            // Frames
            [
                'id' => 'frame_rgb',
                'name' => 'RGB Frame',
                'category' => 'frame',
                'rarity' => 'epic',
                'price' => 2000,
                'imageUrl' => '/assets/images/cosmetics/frame_rgb.svg',
                'description' => 'Animated RGB border',
            ],
            [
                'id' => 'frame_gold',
                'name' => 'Golden Frame',
                'category' => 'frame',
                'rarity' => 'legendary',
                'price' => 3500,
                'imageUrl' => '/assets/images/cosmetics/frame_gold.svg',
                'description' => 'Luxurious golden frame',
            ],
            [
                'id' => 'frame_diamond',
                'name' => 'Diamond Frame',
                'category' => 'frame',
                'rarity' => 'legendary',
                'price' => 7500,
                'imageUrl' => '/assets/images/cosmetics/frame_diamond.svg',
                'description' => 'Sparkling diamond frame',
            ],
            
            // Badges
            [
                'id' => 'badge_trophy',
                'name' => 'Trophy Badge',
                'category' => 'badge',
                'rarity' => 'epic',
                'price' => 1000,
                'imageUrl' => '/assets/images/cosmetics/badge_trophy.svg',
                'description' => 'Winner\'s trophy badge',
            ],
            [
                'id' => 'badge_vip',
                'name' => 'VIP Badge',
                'category' => 'badge',
                'rarity' => 'legendary',
                'price' => 5000,
                'imageUrl' => '/assets/images/cosmetics/badge_vip.svg',
                'description' => 'Exclusive VIP member badge',
            ],
        ];

        $cosmetics = [];
        foreach ($defaultCosmetics as $data) {
            $cosmetics[] = $data;
        }

        $this->dataService->saveCosmetics($cosmetics);
    }
}
