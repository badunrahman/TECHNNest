<?php

declare(strict_types=1);

namespace App\Domain\Services;

class CategoryService extends BaseService
{

    public function validateCategory(array $data): array
    {

        // keeping track of errors and cleaning up data here
        $errors = [];
        $sanitized = [];
        $old = [];

        $name = trim((string) ($data['name'] ?? ''));
        $old['name'] = $name;

        // name is mandatory so check that first
        if ($name === '') {
            $errors['name'] = 'Category name is required.';
        } else {
            $sanitized['name'] = $name;
        }

        $description = trim((string) ($data['description'] ?? ''));
        $old['description'] = $description;
        $sanitized['description'] = $description;


        // if something went wrong, return false and send back errors/old data
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors,
                'data' => $old,
            ];
        }

        return [
            'success' => true,
            'data' => $sanitized,
        ];
    }
}
