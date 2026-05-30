<?php

namespace App\Application\Product\UseCases;

use App\Application\Product\DTOs\CreateProductDTO;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Product;

class CreateProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository
    ) {
    }

    public function execute(CreateProductDTO $dto): Product
    {
        return $this->repository->create($dto->toArray());
    }
}