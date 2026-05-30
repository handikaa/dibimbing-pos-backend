<?php

namespace App\Application\Category\UseCases;

use App\Application\Category\DTOs\UpdateCategoryDTO;
use App\Domain\Category\Repositories\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class UpdateCategoryUseCase
{
    public function __construct(
        private readonly CategoryRepositoryInterface $repository
    ) {}

    public function execute(int $id, UpdateCategoryDTO $dto): Category
    {
        $category = $this->repository->findById($id);

        if (! $category) {
            throw new ModelNotFoundException('Category not found.');
        }

        $data = $dto->toUpdateArray();

        if (array_key_exists('name', $data)) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $this->repository->update($category, $data);
    }
}
