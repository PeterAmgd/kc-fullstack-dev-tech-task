<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Services\CategoryService;

class CategoryController
{
    private CategoryService $service;

    public function __construct()
    {
        $this->service = new CategoryService();
    }

    public function index(): void
    {
        $this->jsonResponse($this->service->getAll());
    }

    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if ($error = CategoryRequest::validateCreate($data)) {
            $this->jsonError($error, 400);
            return;
        }

        $result = $this->service->create($data);
        if (isset($result['error'])) {
            $this->jsonError($result['error'], 400);
        } else {
            $this->jsonResponse($result);
        }
    }

    public function show(string $id): void
    {
        if ($error = CategoryRequest::validateGetById(['id' => $id])) {
            $this->jsonError($error, 400);
            return;
        }

        $category = $this->service->getById($id);
        if (!$category) {
            $this->jsonError('Category not found', 404);
            return;
        }

        $this->jsonResponse($category);
    }

    public function tree(): void
    {
        $categories = $this->service->getCategoryTreeWithCourses();
        $this->jsonResponse($categories);
    }

    private function jsonResponse($data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    private function jsonError(string $message, int $code): void
    {
        http_response_code($code);
        $this->jsonResponse(['error' => $message]);
    }
}
