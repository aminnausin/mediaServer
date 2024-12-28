<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller {
    use HttpResponses;

    /**
     * Display a listing of the resource.
     */
    public function index() {
        try {
            if (! Auth::user() || Auth::user()->id !== 1) {
                abort(403, 'Unauthorized action.');
            }

            return
                UserResource::collection(
                    User::all()->sortBy('name')
                );
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get list of users. Error: ' . $th->getMessage(), 500);
        }
    }


    public function SessionCount() {
        try {
            if (! Auth::user() || Auth::user()->id !== 1) {
                abort(403, 'Unauthorized action.');
            }

            $count = DB::table('sessions')
                ->whereNotNull('user_id')
                ->count();

            return $count;
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get count of logged in  of users. Error: ' . $th->getMessage(), 500);
        }
    }
}
