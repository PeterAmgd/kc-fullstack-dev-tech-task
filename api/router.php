<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CourseController;

// Default response header
header('Content-Type: application/json');

// Normalize method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');

// Instantiate controllers
$categoryController = new CategoryController();
$courseController = new CourseController();

switch ($method) {
    case 'GET':
        if ($uri === '/categories') {
            $categoryController->index();
        } elseif (preg_match('#^/categories/([0-9a-f-]{36})$#', $uri, $matches)) {
            $categoryController->show($matches[1]);
        } elseif ($uri === '/courses') {
            $courseController->index();
        } elseif (preg_match('#^/courses/([A-Za-z0-9_-]+)$#', $uri, $matches)) {
            $courseController->show($matches[1]);
        } else {
            notFound();
        }
        break;

    case 'POST':
        if ($uri === '/categories') {
            $categoryController->store();
        } else {
            notFound();
        }
        break;

    default:
        notFound();
        break;
}

function notFound(): void
{
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
}
