<?php

namespace App\Http\Resources\Pulse;

use Closure;
use Cron\CronExpression;
use DateTimeZone;
use Illuminate\Console\Application;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule as IlluminateSchedule;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use ReflectionClass;
use ReflectionFunction;

// need to replace or actually use this resource: use function Safe\preg_replace;

class ScheduleResource extends PulseResource {
    public int|string|null $ignoreAfter = null;

    public function toArray(Request $request): array {
        $kernel = app(ConsoleKernel::class);
        $schedule = app(IlluminateSchedule::class);

        [$events, $time, $runAt] = $this->remember(function () use ($kernel, $schedule) {
            $kernel->bootstrap();

            $timezone = new DateTimeZone(config('app.timezone')); // @phpstan-ignore-line

            return collect($schedule->events())
                ->map(fn (Event $event): array => [
                    'command' => $this->getCommand($event),
                    'expression' => $this->getExpression($event),
                    'next_due' => $this->getNextDueDateForEvent($event, $timezone)
                        ->diffForHumans(),
                ]);
        });

        return [
            'schedule' => [
                'schedule' => $events,
                'time' => $time,
                'runAt' => $runAt,
            ],
        ];
    }

    private function getClosureLocation(CallbackEvent $event): string {
        $refClass = new ReflectionClass($event);

        if (! $refClass->hasProperty('callback')) {
            return 'unknown';
        }

        $closure = '';

        $prop = $refClass->getProperty('callback');
        $prop->setAccessible(true);
        $callback = $prop->getValue($event);

        if ($callback instanceof Closure) {
            $function = new ReflectionFunction($callback);

            $closure = sprintf(
                '%s:%s',
                str_replace(app()->basePath() . DIRECTORY_SEPARATOR, '', $function->getFileName() ?: ''),
                $function->getStartLine()
            );
        } elseif (is_string($callback)) {
            $closure = $callback;
        } elseif (is_array($callback)) {
            $className = is_string($callback[0]) ? $callback[0] : $callback[0]::class;

            $closure = sprintf('%s::%s', $className, $callback[1]);
        } else {
            $closure = sprintf('%s::__invoke', $callback::class); // @phpstan-ignore-line
        }

        return $closure;
    }

    private function getCommand(Event $event): string {
        $command = str_replace([Application::phpBinary(), Application::artisanBinary()], [
            'php',
            preg_replace("#['\"]#", '', Application::artisanBinary()),
        ], $event->command ?? '');

        if ($event instanceof CallbackEvent) {
            $command = $event->getSummaryForDisplay();

            if (in_array($command, ['Closure', 'Callback'], true)) {
                $command = 'Closure at: ' . $this->getClosureLocation($event);
            }
        }

        return mb_strlen($command) > 1 ? "{$command} " : '';
    }

    private function getExpression(Event $event): string {
        if (! $event->isRepeatable()) {
            return $event->getExpression();
        }

        return "{$event->getExpression()} ({$event->repeatSeconds}s)";
    }

    private function getNextDueDateForEvent(Event $event, DateTimeZone $timezone): Carbon {
        $nextDueDate = Carbon::instance(
            (new CronExpression($event->expression))
                ->getNextRunDate(today()->setTimezone($event->timezone))
                ->setTimezone($timezone)
        );

        if (! $event->isRepeatable()) {
            return $nextDueDate;
        }

        $previousDueDate = Carbon::instance(
            (new CronExpression($event->expression))
                ->getPreviousRunDate(today()->setTimezone($event->timezone), allowCurrentDate: true)
                ->setTimezone($timezone)
        );

        $now = today()->setTimezone($event->timezone);

        if (! $now->copy()->startOfMinute()->eq($previousDueDate)) {
            return $nextDueDate;
        }

        return $now
            ->endOfSecond()
            ->ceilSeconds($event->repeatSeconds); // @phpstan-ignore-line
    }
}
