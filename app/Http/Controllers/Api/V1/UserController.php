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
        if (! Auth::user() || Auth::user()->id !== 1) {
            abort(403, 'Unauthorized action.');
        }

        try {
            return
                UserResource::collection(
                    User::all()->sortBy('name')
                );
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get list of users. Error: ' . $th->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user) {
        if (! Auth::user() || Auth::user()->id !== 1 || Auth::user()->id === $user->id) {
            abort(403, 'Unauthorized action.');
        }

        return $user->delete() ? $this->success('', 'Success', 200) : $this->error('', 'Not found', 404);
    }

    public function SessionCount() {
        if (! Auth::user() || Auth::user()->id !== 1) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $count = DB::table('sessions')
                ->whereNotNull('user_id')
                ->count();

            return $count;
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get count of logged in  of users. Error: ' . $th->getMessage(), 500);
        }
    }
}
