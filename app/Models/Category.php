<?php
namespace App\Models;

class Category
{
    public string $id;
    public string $name;
    public ?string $description;
    public ?string $parent_id;
    public int $count_of_courses;
    public string $created_at;
    public string $updated_at;

    public static function isValidGuid(string $guid): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $guid) === 1;
    }
}