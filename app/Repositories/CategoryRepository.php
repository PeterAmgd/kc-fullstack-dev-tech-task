<?php

namespace App\Repositories;

use App\Database\Connection;
use App\Models\Category;

class CategoryRepository
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Connection::getInstance();
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM categories');
        return $stmt->fetchAll();
    }

    public function getById(string $id): ?array
    {
        if (!Category::isValidGuid($id)) {
            return null;
        }
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getSubcategoryIds(string $categoryId, array &$ids = []): array
    {
        $stmt = $this->pdo->prepare('SELECT id FROM categories WHERE parent_id = ?');
        $stmt->execute([$categoryId]);
        foreach ($stmt->fetchAll() as $row) {
            $ids[] = $row['id'];
            $this->getSubcategoryIds($row['id'], $ids); // Recursion
        }
        return $ids;
    }

    public function countCourses(string $categoryId): int
    {
        if (!Category::isValidGuid($categoryId)) {
            return 0;
        }
        $subcategories = $this->getSubcategoryIds($categoryId);
        $subcategories[] = $categoryId;
        $placeholders = implode(',', array_fill(0, count($subcategories), '?'));
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM courses WHERE category_id IN ($placeholders)");
        $stmt->execute($subcategories);
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO categories (id, name, description, parent_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ');

        return $stmt->execute([
            $data['id'],
            $data['name'],
            $data['description'] ?? null,
            $data['parent_id'] ?? null,
        ]);
    }

    public function getDepth(string $categoryId): int
    {
        $depth = 0;
        $currentId = $categoryId;

        while ($currentId) {
            $stmt = $this->pdo->prepare('SELECT parent_id FROM categories WHERE id = ?');
            $stmt->execute([$currentId]);
            $row = $stmt->fetch();

            if (!$row || !$row['parent_id']) {
                break;
            }

            $depth++;
            $currentId = $row['parent_id'];

            if ($depth >= 4) {
                break; // Prevent infinite loop in case of circular references
            }
        }

        return $depth;
    }

    public function getAllGroupedByParent(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM categories');
        $categories = $stmt->fetchAll();

        $grouped = [];
        foreach ($categories as $category) {
            $grouped[$category['parent_id'] ?? 'root'][] = $category;
        }

        return $grouped;
    }
}
