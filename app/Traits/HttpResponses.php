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

    protected function unauthorised() {
        abort(401, 'Unauthorised action.');
    }

    protected function forbidden($message = 'Forbidden') {
        return Response($message, 403);
    }
}
