<?php

namespace App\Http\Controllers\Api\V1\Metadata;

use App\Enums\SeriesRelationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\SeriesRelationsUpdateRequest;
use App\Http\Resources\SeriesResource;
use App\Models\Series;
use App\Traits\LogsModelChanges;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SeriesRelationsController extends Controller {
    use LogsModelChanges;

    public function index(Series $series) {
        return response()->json($this->relationsResponse($series));
    }

    public function update(SeriesRelationsUpdateRequest $request, Series $series) {
        if ($this->syncRelations($series, $request->input('upserts', []), $request->input('deletes', []))) {
            $this->logModelChanges($series, ['relations_changed' => true], Auth::user());
        }

        return response()->json($this->relationsResponse($series));
    }

    private function syncRelations(Series $series, array $upserts, array $deletes): bool {
        return DB::transaction(function () use ($series, $upserts, $deletes) {
            $changed = false;

            // delete a relationship in either direction
            $deletePair = fn (int $otherId) => DB::table('series_relations')
                ->where(fn ($q) => $q->where('series_id', $series->id)->where('related_series_id', $otherId))
                ->orWhere(fn ($q) => $q->where('series_id', $otherId)->where('related_series_id', $series->id))
                ->delete();

            foreach ($deletes as $otherId) {
                $changed = $deletePair((int) $otherId) > 0 || $changed;
            }

            foreach ($upserts as $entry) {
                $otherId = (int) $entry['series_id'];
                $type = SeriesRelationType::from($entry['type']);

                // flip relationship based on isStorable
                [$subject, $object, $stored] = $type->isStorable() ? [$series->id, $otherId, $type] : [$otherId, $series->id, $type->inverse()];

                $deletePair($otherId); // direction or type may differ, and the pair index allows one row

                DB::table('series_relations')->insert([
                    'series_id' => $subject,
                    'related_series_id' => $object,
                    'type' => $stored->value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $changed = true;
            }

            return $changed;
        });
    }

    private function relationsResponse(Series $series): array {
        $series->load([
            'directRelations.folder.category',
            'directRelations.primaryPoster',
            'inverseRelations.folder.category',
            'inverseRelations.primaryPoster',
        ]);

        return (new SeriesResource($series))->relatedSeriesPayload();
    }
}
