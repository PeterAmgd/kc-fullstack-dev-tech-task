<?php
namespace App\Models;

class Course
{
    public string $id;
    public string $title;
    public string $description;
    public string $image_preview;
    public ?string $main_category_name;
    public string $category_id;
    public string $created_at;
    public string $updated_at;

    public static function isValidId(string $id): bool
    {
        return preg_match('/^[A-Za-z0-9]+$/', $id) === 1;
    }
}