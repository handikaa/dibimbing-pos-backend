<?php

namespace App\Application\Pos\UseCases;

use App\Application\Pos\DTOs\SearchPosProductDTO;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Collection;

class SearchProductsForPosUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    public function execute(SearchPosProductDTO $dto): Collection
    {
        return $this->productRepository->searchForPos([
            'search' => $dto->search,
            'barcode' => $dto->barcode,
            'category_id' => $dto->categoryId,
        ]);
    }
}