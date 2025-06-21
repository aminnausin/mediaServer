<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait HasTags {
    /**
     * Generates tag relationships for any model
     *
     * @param  int  $relationId  Related model Id (metadata_id, series_id)
     * @param  array  $tags  New Tags
     * @param  array  $deletedTags  Deleted Tag Relationship Ids
     * @param  string  $foreignKey  Related Foreign Key
     * @param  string  $modelClass  Related Model Class
     */
    public function generateTagRelationships(
        int $relationId,
        array $tags,
        array $deletedTags,
        string $foreignKey,
        string $modelClass
    ) {
        try {
            foreach ($tags as $tag) {
                $modelClass::firstOrCreate([
                    'tag_id' => $tag['id'],
                    $foreignKey => $relationId,
                ]);
            }

            $modelClass::destroy($deletedTags);
        } catch (\Throwable $th) {
            Log::error('Faild creating/deleting tag relationships', [
                'message' => $th->getMessage(),
                'trace' => $th->getTrace(),
            ]);
        }
    }
}
