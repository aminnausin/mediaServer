<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait HasTags {
    /**
     * Generates tag relationships for any model
     *
     * @param  int  $relationId  Related model id (metadata->id, series->id)
     * @param  array  $tags  New tags
     * @param  array  $deletedTags  Deleted tag relationship ids
     * @param  string  $foreignKey  Related foreign key name ('metadata_id')
     * @param  string  $modelClass  Related tag model class
     */
    public function generateTagRelationships(
        int $relationId,
        array $tags,
        array $deletedTags,
        string $foreignKey,
        string $modelClass
    ): bool {
        try {
            $existingIds = $modelClass::where($foreignKey, $relationId)->pluck('tag_id');
            $newTags = collect($tags)->pluck('id')->diff($existingIds);

            if ($newTags->isNotEmpty()) {
                $modelClass::upsert(
                    $newTags->map(fn ($tagId) => [
                        'tag_id' => $tagId,
                        $foreignKey => $relationId,
                    ])->toArray(),
                    ['tag_id', $foreignKey]
                );
            }

            if (! empty($deletedTags)) {
                $modelClass::destroy($deletedTags);
            }

            if ($newTags->isNotEmpty() || ! empty($deletedTags)) {
                Log::info('dirty tags', [$existingIds, $newTags, $tags, $deletedTags]);
            }

            return $newTags->isNotEmpty() || ! empty($deletedTags);
        } catch (\Throwable $th) {
            Log::error('Failed creating/deleting tag relationships', [
                'model' => $modelClass,
                'fk' => $foreignKey,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            throw $th;
        }
    }
}
