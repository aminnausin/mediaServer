<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class GuestMergeService {
    // Merge config per table
    private array $tables = [
        'playback_progress' => [
            'conflict_key' => '(user_id, metadata_id) WHERE user_id IS NOT NULL',
            'columns' => [
                'progress_offset' => 'latest', // take most recent progress offset
                'progress_percentage' => 'latest',
                'completion_count' => 'sum', // take sum of all completion counts
                'last_completed_at' => 'latest',
                'updated_at' => 'greatest', // take maximum (date) value
                'record_id' => 'keep_existing',
            ],
        ],
        // TODO: Implement the same system for records
        // 'records' => [
        //     'conflict_key' => '(user_id, metadata_id) WHERE user_id IS NOT NULL',
        //     'columns' => [
        //         'updated_at' => 'greatest',
        //     ],
        // ],
    ];

    public function merge(User $user, ?string $guestToken): void {
        if (! $guestToken) {
            return;
        }

        DB::transaction(function () use ($user, $guestToken) {
            foreach ($this->tables as $table => $config) {
                $this->mergeTable($table, $config, $user, $guestToken);
            }
        });
    }

    private function mergeTable(string $table, array $config, User $user, string $guestToken): void {
        $updateClause = $this->buildUpdateClause($table, $config['columns']);
        $columnNames = implode(', ', array_keys($config['columns']));

        // Select columns where guest_token = $guestToken but substituting the table.user_id (which is null) with the current $user->id
        // Insert into table the values of user_id, metadata_id, ...$columns as user rows instead of guest rows
        // and on conflict use an update strategy

        // So for example, if you have a guest_playback progress, login, and already have a playback progress for said metadata_id, it will conflict and perform merge strategies per column
        DB::statement("
            INSERT INTO {$table} (user_id, metadata_id, {$columnNames})
            SELECT ?, metadata_id, {$columnNames}
            FROM {$table}
            WHERE guest_token = ?
            ON CONFLICT {$config['conflict_key']}
            DO UPDATE SET {$updateClause}
        ", [$user->id, $guestToken]);

        DB::table($table)->where('guest_token', $guestToken)->delete();
    }

    private function buildUpdateClause(string $table, array $columns): string {
        $parts = [];

        // Merge Strategies
        // set column {$column} to {$strategy}
        // Excluded is the conflict row getting inserted
        foreach ($columns as $column => $strategy) {
            $parts[] = match ($strategy ?? 'overwrite') {
                'sum' => "{$column} = EXCLUDED.{$column} + {$table}.{$column}",
                'least' => "{$column} = LEAST(EXCLUDED.{$column}, {$table}.{$column})",
                'greatest' => "{$column} = GREATEST(EXCLUDED.{$column}, {$table}.{$column})",
                'latest' => "{$column} = CASE WHEN EXCLUDED.updated_at >= {$table}.updated_at THEN EXCLUDED.{$column} ELSE {$table}.{$column} END",
                'keep_existing' => "{$column} = COALESCE({$table}.{$column}, EXCLUDED.{$column})",
                'overwrite' => "{$column} = EXCLUDED.{$column}",
                default => throw new \InvalidArgumentException("Unknown strategy: {$strategy}"),
            };
        }

        return implode(', ', $parts);
    }
}
