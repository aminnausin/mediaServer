<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Folder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PathResolverService {
    private bool $onlyPublic = false;

    public function onlyPublic(bool $value = true): self {
        $this->onlyPublic = $value;

        return $this;
    }

    private function addPrivacyFilter(Builder $query): Builder {
        return $this->onlyPublic
            ? $query->where('is_private', false)
            : $query;
    }

    public function resolveCategory(string $identifier, array $select = ['id', 'name', 'default_folder_id', 'is_private']): Category {
        return $this->firstSuccessful([
            fn() => $this->resolveCategoryByName($identifier, $select),
            fn() => $this->resolveCategoryById($identifier, $select),
        ], "No category found matching '{$identifier}'");
    }

    private function resolveCategoryByName(string $identifier, array $select): ?Category {
        return $this->addPrivacyFilter(Category::query())
            ->select(...$select)->where('name', 'ilike', '%' . $identifier . '%')
            ->orderByRaw("POSITION(lower(?) in lower(name))", [$identifier])
            ->first();
    }

    private function resolveCategoryById(string $identifier, array $select): ?Category {
        if (! ctype_digit($identifier) || (int) $identifier <= 0) {
            return null;
        }

        return $this->addPrivacyFilter(Category::query())
            ->select(...$select)->find((int) $identifier);
    }

    public function resolveFolder(string $identifier, Category $category, ?Collection $folders = null): ?Folder {
        if (! $folders) {
            $folders = Folder::where('category_id', $category->id)->get();
        }

        return $this->firstSuccessful([
            fn() => $this->resolveFolderByName($identifier, $category, $folders),
            fn() => $this->resolveFolderById($identifier, $category, $folders),
        ], "No folder found in category '{$category->name}' matching '{$identifier}'");
    }

    private function resolveFolderById(string $identifier, Category $category, Collection $folders): ?Folder {
        if (! ctype_digit($identifier) || (int) $identifier <= 0) {
            throw new ModelNotFoundException("No folder found in category '{$category->name}' matching '{$identifier}'");
        }

        return $folders->where('category_id', $category->id)->find((int) $identifier);
    }

    private function resolveDefaultFolder(Category $category, Collection $folders): Folder {
        return $category->default_folder_id
            ? $folders->firstWhere('id', $category->default_folder_id)
            : $folders->first();
    }

    private function resolveFolderByName(string $identifier, Category $category, Collection $folders): ?Folder {
        if (! $identifier) {
            return $this->resolveDefaultFolder($category, $folders);
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
