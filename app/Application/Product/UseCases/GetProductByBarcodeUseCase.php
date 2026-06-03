<?php

namespace App\Application\Product\UseCases;

use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GetProductByBarcodeUseCase
{
    public function __construct(private readonly ProductRepositoryInterface $repository) {}

    public function execute(string $barcode): Product
    {
        $product = $this->repository->findByBarcode($barcode);

        if (!$product) {
            throw new ModelNotFoundException("Product with barcode {$barcode} not found");
        }

        return $product;
    }
}