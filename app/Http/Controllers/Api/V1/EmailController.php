<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmailController extends Controller {
    public function update(Request $request) {
        $request->merge(['current_email' => $request->user()->email]);

        $validated = $request->validate([
            'password' => ['required', 'current_password'],
            'email' => [
                'required',
                'email',
                'different:current_email',
                Rule::unique('users', 'email')->ignore($request->user()->id),
            ],

        ], [
            'email.different' => 'You already use this email.',
        ]);

        $request->user()->update([
            'email' => $validated['email'],
        ]);

        return response()->noContent();
    }
}
