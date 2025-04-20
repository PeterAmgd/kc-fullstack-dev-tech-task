<?php

namespace App\Repositories;

use App\Database\Connection;
use App\Models\Category;
use App\Models\Course;

class CourseRepository
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Connection::getInstance();
    }

    public function getAll(?string $categoryId = null): array
    {
        if ($categoryId && !Category::isValidGuid($categoryId)) {
            return [];
        }
        $query = '
            SELECT c.*, cat.name AS main_category_name
            FROM courses c
            JOIN categories cat ON c.category_id = cat.id
        ';
        if ($categoryId) {
            $query .= ' WHERE c.category_id = ?';
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$categoryId]);
        } else {
            $stmt = $this->pdo->query($query);
        }
        return $stmt->fetchAll();
    }

    public function getById(string $id): ?array
    {
        if (!Course::isValidId($id)) {
            return null;
        }
        $stmt = $this->pdo->prepare('
            SELECT c.*, cat.name AS main_category_name
            FROM courses c
            JOIN categories cat ON c.category_id = cat.id
            WHERE c.id = ?
        ');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getByCategoryIds(array $categoryIds): array
    {
        if (empty($categoryIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
        $stmt = $this->pdo->prepare("
            SELECT c.*, cat.name AS main_category_name
            FROM courses c
            JOIN categories cat ON c.category_id = cat.id
            WHERE c.category_id IN ($placeholders)
        ");
        $stmt->execute($categoryIds);

        return $stmt->fetchAll();
    }
}
