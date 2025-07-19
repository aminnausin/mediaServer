<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller {
    use HttpResponses;

    /**
     * Display a listing of the resource.
     */
    public function index() {
        try {
            $users = Auth::id() === 1 || (app()->environment('demo') && Auth::user()->email === config('demo.auth_email'))
                ? User::all()->sortBy('name')
                : User::where('id', Auth::id())->get();

            return UserResource::collection($users);
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
        if (Auth::id() !== 1 || Auth::id() === $user->id || (app()->environment('demo') && $user->email === config('demo.auth_email'))) {
            return $this->forbidden();
        }

        return $user->delete() ? $this->success('', 'Success', 200) : $this->error('', 'Not found', 404);
    }

    public function sessionCount() {
        // Try returning plausible data if api response is successful
        try {
            $token = config('services.plausible.token');
            $siteId = config('services.plausible.site_id');
            $plausibleDomain = config('services.plausible.domain');

            if ($token && $siteId && $plausibleDomain) {
                $response = app('plausible.client')->get('/api/v1/stats/realtime/visitors', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                    ],
                    'query' => [
                        'site_id' => $siteId,
                    ],
                ]);

                if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                    return json_decode($response->getBody(), true);
                }

                Log::warning('Plausible API responded with non-success status: ' . $response->getBody());
            }
        } catch (\Throwable $th) {
            Log::warning('Plausible Internal Error: ' . $th->getMessage());
        }

        // Default to returning internal session count and only throw this error
        try {
            return
                DB::table('sessions')
                    ->whereNotNull('user_id')
                    ->count();
        } catch (\Throwable $th) {
            return $this->error(0, 'Unable to get count of logged in users. Error: ' . $th->getMessage(), 500);
        }
    }
}
