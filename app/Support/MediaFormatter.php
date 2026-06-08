<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterval;

class MediaFormatter {
    public static function getMediaReleaseSeason(?string $dateString): ?string {
        if (! $dateString) {
            return null;
        }

        try {
            $date = Carbon::parse($dateString);

            $season = match (true) {
                $date->month <= 3 => 'Winter',
                $date->month <= 6 => 'Spring',
                $date->month <= 9 => 'Summer',
                default => 'Fall',
            };

            return "$season {$date->year}";
        } catch (\Throwable) {
            return null;
        }
    }

    public static function formatDate(?string $date, ?string $errorMessage = 'Unknown Date') {
        return $date ? Carbon::createFromDate($date)->format('F Y') : $errorMessage;
    }

    public static function formatDuration(?int $seconds): string {
        return $seconds ? CarbonInterval::seconds($seconds)->cascade()->forHumans(['short' => true]) : 'Unknown Duration';
    }

    /**
     * Mimics /js/service/util.ts/formatFileSize()
     */
    public static function formatFileSize(int $size = 0, bool $space = true, int $divisor = 1024): string {
        if ($size < 0) {
            return 'Unknown size';
        }

        $unitIndex = 0;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        while ($size >= $divisor && $unitIndex < count($units) - 1) {
            $size /= $divisor;
            $unitIndex++;
        }

        // 2 decimal places
        $formattedSize = round($size, 2);

        return "$formattedSize" . ($space ? ' ' : '') . $units[$unitIndex];
    }
}
