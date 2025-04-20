<?php

namespace App\Controllers;

class BaseController
{
    protected function sendJsonResponse($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header("Access-Control-Allow-Origin: http://cc.localhost");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Content-Type: application/json");
        echo json_encode($data);
    }
}