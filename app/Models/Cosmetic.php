<?php

declare(strict_types=1);

namespace BetScript\Models;

class Cosmetic
{
    private string $id;
    private string $name;
    private string $category; // 'hat', 'glasses', 'background', 'frame', 'badge'
    private string $rarity; // 'common', 'rare', 'epic', 'legendary'
    private int $price;
    private string $imageUrl;
    private string $description;

    public function __construct(
        string $id,
        string $name,
        string $category,
        string $rarity,
        int $price,
        string $imageUrl,
        string $description = ''
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->category = $category;
        $this->rarity = $rarity;
        $this->price = $price;
        $this->imageUrl = $imageUrl;
        $this->description = $description;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getRarity(): string
    {
        return $this->rarity;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'rarity' => $this->rarity,
            'price' => $this->price,
            'imageUrl' => $this->imageUrl,
            'description' => $this->description,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['name'],
            $data['category'],
            $data['rarity'],
            $data['price'],
            $data['imageUrl'],
            $data['description'] ?? ''
        );
    }
}
