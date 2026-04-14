<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\EmailUpdateRequest;

class EmailController extends Controller {
    public function update(EmailUpdateRequest $request) {
        $request->user()->update([
            'email' => $request->validated('email'),
        ]);

        return response()->noContent();
    }
}
