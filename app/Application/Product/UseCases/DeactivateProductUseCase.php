<?php

namespace App\Application\Product\UseCases;

use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DeactivateProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository
    ) {}

    public function execute(int $id): Product
    {
        $product = $this->repository->findById($id);

        if (! $product) {
            throw new ModelNotFoundException('Product not found.');
        }

        return $this->repository->deactivate($product);
    }
}
