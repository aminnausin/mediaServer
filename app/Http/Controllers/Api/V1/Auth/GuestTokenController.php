<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class GuestTokenController extends Controller {
    public function issue(): JsonResponse {
        return response()->json(['token' => (string) Str::uuid()]);
    }
}
