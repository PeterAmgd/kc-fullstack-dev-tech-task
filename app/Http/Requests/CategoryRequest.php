<?php

namespace App\Http\Requests;

use App\Models\Category;

class CategoryRequest
{
    public static function validateGetById(array $params): ?string
    {
        if (!isset($params['id']) || !Category::isValidGuid($params['id'])) {
            return 'Invalid or missing category ID';
        }
        return null;
    }
    public static function validateCreate(array $data): ?string
    {
        if (!isset($data['name']) || empty(trim($data['name']))) {
            return 'Name is required';
        }

        if (isset($data['parent_id']) && !Category::isValidGuid($data['parent_id'])) {
            return 'Invalid parent ID';
        }

        return null;
    }
}