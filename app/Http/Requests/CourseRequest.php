<?php
namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Course;

class CourseRequest
{
    public static function validateGetAll(array $params): ?string
    {
        if (isset($params['category_id']) && !Category::isValidGuid($params['category_id'])) {
            return 'Invalid category ID';
        }
        return null;
    }

    public static function validateGetById(array $params): ?string
    {
        if (!isset($params['id']) || !Course::isValidId($params['id'])) {
            return 'Invalid or missing course ID';
        }
        return null;
    }
}