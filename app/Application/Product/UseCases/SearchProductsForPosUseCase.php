<?php

namespace App\Application\Product\UseCases;

use App\Domain\Product\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Collection;

class SearchProductsForPosUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository
    ) {
    }

    public function execute(array $filters = []): Collection
    {
        return $this->repository->searchForPos($filters);
    }
}