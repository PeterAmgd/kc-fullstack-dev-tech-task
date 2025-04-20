<?php
namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Services\CourseService;

class CourseController
{
    private $service;

    public function __construct()
    {
        $this->service = new CourseService();
    }

    public function index(): void
    {
        $categoryId = $_GET['category_id'] ?? null;
        $error = CourseRequest::validateGetAll(['category_id' => $categoryId]);
        if ($error) {
            $this->sendError($error, 400);
            return;
        }
        $courses = $this->service->getAll($categoryId);
        $this->sendResponse($courses);
    }

    public function show(string $id): void
    {
        $error = CourseRequest::validateGetById(['id' => $id]);
        if ($error) {
            $this->sendError($error, 400);
            return;
        }
        $course = $this->service->getById($id);
        if (!$course) {
            $this->sendError('Course not found', 404);
            return;
        }
        $this->sendResponse($course);
    }

    private function sendResponse($data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    private function sendError(string $message, int $code): void
    {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode(['error' => $message]);
    }
}