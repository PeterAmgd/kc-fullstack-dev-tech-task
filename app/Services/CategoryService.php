<?php

namespace App\Services;

use Ramsey\Uuid\Uuid;
use App\Repositories\CourseRepository;
use App\Repositories\CategoryRepository;

class CategoryService
{
    private $repository;

    public function __construct()
    {
        $this->repository = new CategoryRepository();
    }

    public function getAll(): array
    {
        $categories = $this->repository->getAll();
        foreach ($categories as &$category) {
            $category['count_of_courses'] = $this->getCourseCount($category['id']);
        }
        return $categories;
    }

    public function getById(string $id): ?array
    {
        $category = $this->repository->getById($id);
        if ($category) {
            $category['count_of_courses'] = $this->getCourseCount($id);
        }
        return $category;
    }

    private function getCourseCount(string $categoryId): int
    {
        return $this->repository->countCourses($categoryId);
    }

    function generateUuidV4(): string
    {
        $data = random_bytes(16);

        // Set version to 0100
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function create(array $data): array
    {
        $parentId = $data['parent_id'] ?? null;

        if ($parentId) {
            $depth = $this->repository->getDepth($parentId);
            if ($depth >= 4) {
                return ['error' => 'Maximum category tree depth (4) exceeded'];
            }
        }

        $data['id'] = $this->generateUuidV4();
        $success = $this->repository->create($data);

        if (!$success) {
            return ['error' => 'Failed to create category'];
        }

        return ['message' => 'Category created successfully', 'id' => $data['id']];
    }

    public function getCategoryTreeWithCourses(): array
    {
        $grouped = $this->repository->getAllGroupedByParent();
        $allCourses = (new CourseRepository())->getAll(); // Get all courses once

        return $this->buildTree('root', $grouped, $allCourses);
    }

    private function buildTree(string $parentId, array $grouped, array $allCourses, int $level = 1): array
    {
        if (!isset($grouped[$parentId]) || $level > 4) {
            return [];
        }

        $tree = [];

        foreach ($grouped[$parentId] as $category) {
            $categoryId = $category['id'];

            // Get all subcategory ids for course counting
            $subcategoryIds = $this->repository->getSubcategoryIds($categoryId);
            $subcategoryIds[] = $categoryId;

            // Filter courses
            $courses = array_filter($allCourses, fn($course) => in_array($course['category_id'], $subcategoryIds));

            $tree[] = [
                'id' => $category['id'],
                'name' => $category['name'],
                'description' => $category['description'],
                'parent_id' => $category['parent_id'],
                'count_of_courses' => count($courses),  // Count courses from this category and its children
                'courses' => array_values(array_filter($courses, fn($course) => $course['category_id'] === $categoryId)),
                'children' => $this->buildTree($categoryId, $grouped, $allCourses, $level + 1),
            ];
        }

        return $tree;
    }
}
