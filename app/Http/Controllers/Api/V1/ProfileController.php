<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileResource;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller {
    use HttpResponses;

    /**
     * Display the specified resource.
     * Get user by id
     *
     * @return \Illuminate\Http\Response
     */
    public function show(int $user_id) {
        try {
            return new ProfileResource(User::findOrFail($user_id ?? Auth::id()));
        } catch (ModelNotFoundException $th) {
            return $this->error(null, 'User not found', 404);
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get user. Error: ' . $th->getMessage(), 500);
        }
    }

    public function findUser(Request $request) {
        try {
            return new ProfileResource(User::where('name', $request->username ?? Auth::user()->name)->firstOrFail());
        } catch (ModelNotFoundException $th) {
            return $this->error(null, 'User not found', 404);
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get user. Error: ' . $th->getMessage(), 500);
        }
    }

    public function destroy(Request $request): Response {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::guard("web")->logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
