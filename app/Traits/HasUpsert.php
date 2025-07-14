<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait HasUpsert {
    /**
     * Generates tag relationships for any model
     *
     * @param  string  $context  Custom Error Message
     * @param  Throwable  $th  Original Error
     * @param  array  $transactions  Upsert Transactions
     * @param  int  $updateCount  Number of updates attempted
     * @param  int|null  $checkCount  Number of checks attempted (optional)
     */
    public function handleError(string $context, \Throwable $th, array $ids, int $updateCount, ?int $checkCount = null): never {
        $message = sprintf(
            '%s: %s | Cancelling %d updates%s',
            $context,
            $th->getMessage(),
            $updateCount,
            $checkCount !== null ? " and $checkCount checks" : ''
        );

        if (! empty($ids)) {
            $message .= ' with IDs: ' . json_encode($ids);
        }

        dump($message);
        Log::error($message);

        throw $th;
    }
}
