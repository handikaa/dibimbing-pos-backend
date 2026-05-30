<?php

namespace App\Application\Product\DTOs;

readonly class UpdateProductDTO
{
    public function __construct(
        public ?int $categoryId = null,
        public ?string $name = null,
        public ?string $sku = null,
        public ?string $barcode = null,
        public ?string $description = null,
        public ?float $costPrice = null,
        public ?float $sellingPrice = null,
        public ?int $minimumStock = null,
        public ?string $imageUrl = null,
        public ?bool $isActive = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            name: $data['name'] ?? null,
            sku: $data['sku'] ?? null,
            barcode: $data['barcode'] ?? null,
            description: $data['description'] ?? null,
            costPrice: isset($data['cost_price']) ? (float) $data['cost_price'] : null,
            sellingPrice: isset($data['selling_price']) ? (float) $data['selling_price'] : null,
            minimumStock: isset($data['minimum_stock']) ? (int) $data['minimum_stock'] : null,
            imageUrl: $data['image_url'] ?? null,
            isActive: $data['is_active'] ?? null,
        );
    }

    public function toUpdateArray(): array
    {
        return array_filter([
            'category_id' => $this->categoryId,
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'cost_price' => $this->costPrice,
            'selling_price' => $this->sellingPrice,
            'minimum_stock' => $this->minimumStock,
            'image_url' => $this->imageUrl,
            'is_active' => $this->isActive,
        ], fn ($value) => ! is_null($value));
    }
}