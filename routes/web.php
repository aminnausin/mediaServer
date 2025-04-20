<?php

use App\Http\Controllers\MediaController;
use App\Http\Middleware\MetadataSSR;
use App\Models\Category;
use App\Models\Folder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

if (env('APP_DEBUG')) {
    Route::get('/test-headers', function () {
        return response()->json(request()->header());
    });

    Route::get('/debug-scheme', function () {
        return response()->json([
            'scheme' => request()->getScheme(),
            'headers' => request()->header(),
            'isSecure' => request()->isSecure(),
            'trustedProxies' => request()->getTrustedProxies(),
            'trustedHeaders' => request()->getTrustedHeaderSet(),
            'realIP' => request()->header('X-Real-IP'),
            'for' => request()->header('X-Forwarded-For'),
        ]);
    });
}

// private

// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/jobs/verifyFiles', [JobController::class, 'verifyFiles']);
//     Route::get('/jobs/syncFiles', [JobController::class, 'syncFiles']);
//     Route::get('/jobs/indexFiles', [JobController::class, 'indexFiles']);
//     Route::get('/jobs/cleanPaths', [JobController::class, 'cleanPaths']);
// });

// public

// Route::get('php', function () {
//     phpinfo();
// })->name('php');

// For serving videos in private storage folder without leaking urls
Route::get('/storage/{path}', [MediaController::class, 'show'])->where('path', '.*')->name('media.serve');
Route::get('/signed-url/{path}', function ($path) {
    return URL::temporarySignedRoute(
        'media.serve',
        now()->addSeconds(5), // URL is valid for 5 seconds
        ['path' => $path]
    );
})->middleware('auth')->where('path', '.*');

// Route::get('/broadcast', function () {
//     $task = Task::first();
//     dump($task);
//     broadcast(new TaskEnded($task));
// });

Route::middleware('web')->group(function () {
    Route::get('/pulse', function () {
        if (Gate::allows('viewPulse', Auth::user())) {
            return view('vendor.pulse.dashboard');
        }
        abort(403);
    });

    Route::get('/welcome', function () {
        return view('welcome');
    });

    Route::get('/', function () {
        if (Auth::user()) {
            $category = Category::oldest('id')->first();
        } else {
            $category = Category::where('is_private', false)->oldest('id')->first();
        }

        // If no category is found, redirect to /setup
        if (! $category) {
            return redirect('/setup');
        }

        $folder = $category->default_folder_id ? Folder::find($category->default_folder_id) : $category->folders()->first();

        // Otherwise, redirect to the category's name route
        return redirect("/{$category->name}/{$folder->name}");
    });

    Route::middleware(MetadataSSR::class)->get('/{dir?}/{folderName?}', function () {
        return view('home');
    })->name('root');

    Route::get('/{any}', function () {
        return view('home');
    })->where('any', '.*');
});
