<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Folder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

// TODO: Finish this. Unused for now.

class PathResolverService {
    public function resolveCategory(string $identifier, array $select = ['id', 'name', 'default_folder_id', 'is_private']): Category {
        return $this->firstSuccessful([
            fn () => $this->resolveCategoryByName($identifier, $select),
            fn () => $this->resolveCategoryById($identifier, $select),
        ], "No category found matching '{$identifier}'");
    }

    private function resolveCategoryByName(string $identifier, array $select): ?Category {
        return Category::select(...$select)
            ->whereRaw('LOWER(name) = ?', [strtolower($identifier)])
            ->first();
    }

    private function resolveCategoryById(string $identifier, array $select): ?Category {
        if (! ctype_digit($identifier) || (int) $identifier <= 0) {
            return null;
        }

        return Category::select(...$select)->find((int) $identifier);
    }

    public function resolveFolder(string $identifier, Collection $folders, Category $category): Folder {
        return $this->firstSuccessful([
            fn () => $this->resolveFolderByName($identifier, $folders, $category),
            fn () => $this->resolveFolderById($identifier, $folders, $category),
        ], "No folder found in category '{$category->name}' matching '{$identifier}'");
    }

    private function resolveFolderById(string $identifier, Collection $folders, Category $category): ?Folder {
        if (! ctype_digit($identifier) || (int) $identifier <= 0) {
            return null;
        }

        return $folders->where('category_id', $category->id)->find((int) $identifier);
    }

    private function resolveDefaultFolder(Collection $folders, Category $category): Folder {
        return $category->default_folder_id
            ? $folders->firstWhere('id', $category->default_folder_id)
            : $folders->first();
    }

    private function resolveFolderByName(string $identifier, Collection $folders, Category $category): ?Folder {
        if (! $identifier) {
            return $this->resolveDefaultFolder($folders, $category);
        }

        $identifier = strtolower($identifier);

        return $folders->first(function ($folder) use ($identifier) {
            return strtolower($folder->name) === $identifier;
        }) ?? $folders->first(function ($folder) use ($identifier) {
            return str_contains(strtolower($folder->name), $identifier);
        });
    }

    private function firstSuccessful(array $resolvers, string $errorMessage) {
        foreach ($resolvers as $resolver) {
            $result = $resolver();
            if ($result) {
                return $result;
            }
        }

        throw new ModelNotFoundException($errorMessage);
    }
}
