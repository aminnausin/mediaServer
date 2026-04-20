<?php

namespace App\Services\Auth;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GuestIdentity {
    public static function isGuest(): bool {
        return ! Auth::check();
    }

    public static function guestToken(): ?string {
        return request()->header('X-Guest-Token');
    }

    public static function scope(Builder|Relation $query): Builder|Relation {
        return Auth::check()
            ? $query->where('user_id', Auth::id())
            : $query->where('guest_token', self::guestToken());
    }

    public static function identity(): array {
        return Auth::check()
            ? ['user_id' => Auth::id(), 'guest_token' => null]
            : ['user_id' => null, 'guest_token' => self::guestToken()];
    }

    public static function uniqueKey($fk = 'metadata_id'): array {
        return Auth::check()
            ? ['user_id', $fk]
            : ['guest_token', $fk];
    }

    public static function upsert(string $table, array $data, array $updateColumns): object {
        $conflictTarget = Auth::check()
            ? '(user_id, metadata_id) WHERE user_id IS NOT NULL'
            : '(guest_token, metadata_id) WHERE guest_token IS NOT NULL';

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $updates = implode(', ', array_map(fn($col) => "{$col} = EXCLUDED.{$col}", $updateColumns));

        return DB::selectOne("
            INSERT INTO {$table} ({$columns})
            VALUES ({$placeholders})
            ON CONFLICT {$conflictTarget}
            DO UPDATE SET {$updates}
            RETURNING *
        ", array_values($data));
    }
}
