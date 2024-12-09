<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Folder;
use App\Models\Tag;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SiteController extends Controller {
    public function index() {
        try {
            if (!Auth::user()->id == 1) return Response("Forbidden", 403);


            return array(
                array('title' => 'categories', "count"  => Category::count()),
                array('title' => 'folders', "count"  => Folder::count()),
                array('title' => 'videos', "count"  => Video::count()),
                array('title' => 'users', "count"  => User::count()),
                array('title' => 'tags', "count"  => Tag::count()),
                array('title' => 'views', "count" => Tag::count())
            );
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get stats. Error: ' . $th->getMessage(), 500);
        }
    }
}
