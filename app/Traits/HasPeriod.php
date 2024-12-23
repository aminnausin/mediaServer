<?php

namespace App\Traits;

use Carbon\CarbonInterval;

trait HasPeriod {
    /**
     * The usage period.
     *
     * @var '1_hour'|'6_hours'|'24_hours'|'7_days'|null| any valid text
     */
    public ?string $period = '1_hour';

    /**
     * The period as an Interval instance.
     */
    public function periodAsInterval(): CarbonInterval {
        if (!$this->period || !preg_match('/(\d+)_(hours?|days?|weeks?|months?|years?)/', $this->period, $matches)) {
            return CarbonInterval::hours(24);
        }
        // Extract the value and unit from the period string
        [$fullMatch, $value, $unit] = $matches;

        // Dynamically create the CarbonInterval based on the unit
        switch ($unit) {
            case 'hour':
            case 'hours':
                return CarbonInterval::hours($value);
            case 'day':
            case 'days':
                return CarbonInterval::hours($value * 24);
            case 'week':
            case 'weeks':
                return CarbonInterval::hours($value * 24 * 7);
            case 'month':
            case 'months':
                return CarbonInterval::hours($value * 24 * 30);
            case 'year':
            case 'years':
                return CarbonInterval::hours($value * 24 * 365);
            default:
                return CarbonInterval::hours(1);
        }
        // return CarbonInterval::createFromDateString($this->period);
        // return CarbonInterval::hours(match ($this->period) {
        //     '6_hours' => 6,
        //     '24_hours' => 24,
        //     '7_days' => 168,
        //     default => 1,
        // });
    }

    /**
     * The human friendly representation of the selected period.
     */
    public function periodForHumans(): string {
        return match ($this->period) {
            '6_hours' => '6 hours',
            '24_hours' => '24 hours',
            '7_days' => '7 days',
            '1_hour' => 'hour',
            default => str_replace($this->period, '_', ' '),
        };
    }
}
