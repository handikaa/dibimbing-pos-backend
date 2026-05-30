<?php

namespace App\Application\Category\UseCases;

use App\Domain\Category\Repositories\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DeactivateCategoryUseCase
{
    public function __construct(
        private readonly CategoryRepositoryInterface $repository
    ) {}

    public function execute(int $id): Category
    {
        $category = $this->repository->findById($id);

        if (! $category) {
            throw new ModelNotFoundException('Category not found.');
        }

        return $this->repository->deactivate($category);
    }
}
