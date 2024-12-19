<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Folder;
use App\Models\Record;
use App\Models\Tag;
use App\Models\User;
use App\Models\Video;
use Illuminate\Support\Facades\Auth;

class SiteController extends Controller {
    public function index() {
        try {
            if (! Auth::user()->id == 1) {
                return Response('Forbidden', 403);
            }

            return [
                ['title' => 'categories', 'count' => Category::count()],
                ['title' => 'folders', 'count' => Folder::count()],
                ['title' => 'videos', 'count' => Video::count()],
                ['title' => 'users', 'count' => User::count()],
                ['title' => 'tags', 'count' => Tag::count()],
                ['title' => 'views', 'count' => Record::count()],
            ];
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get stats. Error: ' . $th->getMessage(), 500);
        }
    }
}
