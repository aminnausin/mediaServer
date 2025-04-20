<?php

namespace App\Traits;

trait HttpResponses {
    protected function success($data, $message = null, $code = 200) {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function error($data, $message, $code) {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function unauthorized() {
        abort(401, 'Unauthorized action.');
    }

    protected function forbidden() {
        return Response('Forbidden', 403);
    }
}
