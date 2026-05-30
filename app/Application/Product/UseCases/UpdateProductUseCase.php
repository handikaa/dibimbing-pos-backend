<?php

namespace App\Application\Product\UseCases;

use App\Application\Product\DTOs\UpdateProductDTO;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UpdateProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository
    ) {}

    public function execute(int $id, UpdateProductDTO $dto): Product
    {
        $product = $this->repository->findById($id);

        if (! $product) {
            throw new ModelNotFoundException('Product not found.');
        }

        return $this->repository->update($product, $dto->toUpdateArray());
    }
}
