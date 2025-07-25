<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Folder;
use App\Models\Record;
use App\Models\Tag;
use App\Models\User;
use App\Models\Video;
use App\Traits\HasPeriod;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller {
    use HasPeriod;
    use HttpResponses;

    public function index(Request $request) {
        $this->period = $request->query('period', '1_hour');
        $interval = $this->periodAsInterval();
        $startDate = today()->sub($interval);

        $changes = Cache::flexible("stats_$this->period", [60, 90], $this->generateStats($startDate));

        return ['changes' => $changes, 'period' => $this->periodForHumans()];
    }

    public function getDashboardStats(Request $request) {
        // Unimplemented
    }

    private function generateStats(Carbon $startDate) {
        $currentCounts = [
            'categories' => Category::count(),
            'folders' => Folder::count(),
            'videos' => Video::count(),
            'users' => User::count(),
            'tags' => Tag::count(),
            'views' => Record::count(),
        ];

        $previousCounts = [
            'categories' => Category::where('created_at', '<', $startDate)->count(),
            'folders' => Folder::where('created_at', '<', $startDate)->count(),
            'videos' => Video::where('created_at', '<', $startDate)->count(),
            'users' => User::where('created_at', '<', $startDate)->count(),
            'tags' => Tag::where('created_at', '<', $startDate)->count(),
            'views' => Record::where('created_at', '<', $startDate)->count(),
        ];

        $changes = [];
        foreach ($currentCounts as $key => $currentCount) {
            $previousCount = $previousCounts[$key];
            $changePercentage = $previousCount > 0 ? (($currentCount - $previousCount) / $previousCount) * 100 : 0;
            $changes[] = [
                'title' => $key,
                'count' => $currentCount,
                'change' => $currentCount - $previousCounts[$key],
                'change_pct' => round($changePercentage, 2),
                'prev' => $previousCount,
            ];
        }

        return $changes;
    }
}
