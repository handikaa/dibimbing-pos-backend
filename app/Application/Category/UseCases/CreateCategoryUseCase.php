<?php

namespace App\Application\Category\UseCases;

use App\Application\Category\DTOs\CreateCategoryDTO;
use App\Domain\Category\Repositories\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Category;
use Illuminate\Support\Str;

class CreateCategoryUseCase
{
    public function __construct(
        private readonly CategoryRepositoryInterface $repository
    ) {}

    public function execute(CreateCategoryDTO $dto): Category
    {
        return $this->repository->create([
            'name' => $dto->name,
            'slug' => Str::slug($dto->name),
            'description' => $dto->description,
            'is_active' => $dto->isActive,
        ]);
    }
}
