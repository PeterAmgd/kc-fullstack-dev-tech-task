<?php

namespace App\Services;

use App\Repositories\CourseRepository;
use App\Repositories\CategoryRepository;

class CourseService
{
    private $repository;
    private $categoryRepository;

    public function __construct()
    {
        $this->repository = new CourseRepository();
        $this->categoryRepository = new CategoryRepository();
    }

    public function getAll(?string $categoryId = null): array
    {
        if ($categoryId) {
            // Fetch only direct courses for the given category
            return $this->repository->getByCategoryIds([$categoryId]);
        }
        return $this->repository->getAll();
    }

    public function getById(string $id): ?array
    {
        return $this->repository->getById($id);
    }
}
