<?php

namespace App\Application\Product\DTOs;

readonly class CreateProductDTO
{
    public function __construct(
        public int $categoryId,
        public string $name,
        public string $sku,
        public ?string $barcode,
        public ?string $description,
        public float $costPrice,
        public float $sellingPrice,
        public int $currentStock,
        public int $minimumStock,
        public ?string $imageUrl,
        public bool $isActive,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            categoryId: (int) $data['category_id'],
            name: $data['name'],
            sku: $data['sku'],
            barcode: $data['barcode'] ?? null,
            description: $data['description'] ?? null,
            costPrice: (float) ($data['cost_price'] ?? 0),
            sellingPrice: (float) $data['selling_price'],
            currentStock: (int) ($data['current_stock'] ?? 0),
            minimumStock: (int) ($data['minimum_stock'] ?? 0),
            imageUrl: $data['image_url'] ?? null,
            isActive: $data['is_active'] ?? true,
        );
    }

    public function toArray(): array
    {
        return [
            'category_id' => $this->categoryId,
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'cost_price' => $this->costPrice,
            'selling_price' => $this->sellingPrice,
            'current_stock' => $this->currentStock,
            'minimum_stock' => $this->minimumStock,
            'image_url' => $this->imageUrl,
            'is_active' => $this->isActive,
        ];
    }
}