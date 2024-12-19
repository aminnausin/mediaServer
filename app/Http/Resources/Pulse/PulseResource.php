<?php

namespace App\Http\Resources\Pulse;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Laravel\Pulse\Facades\Pulse;
use Laravel\Pulse\Livewire\Concerns\HasPeriod;
use Laravel\Pulse\Livewire\Concerns\RemembersQueries;

class PulseResource extends JsonResource {
    use HasPeriod, RemembersQueries;

    public function __construct($resource, $period) {
        parent::__construct($resource);
        $this->period = $period;
    }

    /**
     * Retrieve values for the given type.
     *
     * @param  list<string>  $keys
     * @return \Illuminate\Support\Collection<string, object{
     *     timestamp: int,
     *     key: string,
     *     value: string
     * }>
     */
    protected function values(string $type, ?array $keys = null): Collection {
        return Pulse::values($type, $keys);
    }

    /**
     * Retrieve aggregate values for plotting on a graph.
     *
     * @param  list<string>  $types
     * @param  'count'|'min'|'max'|'sum'|'avg'  $aggregate
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<string, int|null>>>
     */
    protected function graph(array $types, string $aggregate): Collection {
        return Pulse::graph($types, $aggregate, $this->periodAsInterval());
    }

    /**
     * Retrieve aggregate values for the given type.
     *
     * @param  'count'|'min'|'max'|'sum'|'avg'|list<'count'|'min'|'max'|'sum'|'avg'>  $aggregates
     * @return \Illuminate\Support\Collection<int, mixed>
     */
    protected function aggregate(
        string $type,
        string|array $aggregates,
        ?string $orderBy = null,
        string $direction = 'desc',
        int $limit = 101,
    ): Collection {
        return Pulse::aggregate($type, $aggregates, $this->periodAsInterval(), $orderBy, $direction, $limit);
    }

    /**
     * Retrieve aggregate values for the given types.
     *
     * @param  string|list<string>  $types
     * @param  'count'|'min'|'max'|'sum'|'avg'  $aggregate
     * @return \Illuminate\Support\Collection<int, mixed>
     */
    protected function aggregateTypes(
        string|array $types,
        string $aggregate,
        ?string $orderBy = null,
        string $direction = 'desc',
        int $limit = 101,
    ): Collection {
        return Pulse::aggregateTypes($types, $aggregate, $this->periodAsInterval(), $orderBy, $direction, $limit);
    }

    /**
     * Retrieve an aggregate total for the given types.
     *
     * @param  string|list<string>  $types
     * @param  'count'|'min'|'max'|'sum'|'avg'  $aggregate
     * @return float|\Illuminate\Support\Collection<string, int>
     */
    protected function aggregateTotal(
        array|string $types,
        string $aggregate,
    ): float|Collection {
        return Pulse::aggregateTotal($types, $aggregate, $this->periodAsInterval());
    }
}
