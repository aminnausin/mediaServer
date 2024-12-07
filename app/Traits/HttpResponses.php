<?php

namespace App\Traits;

trait HttpResponses {
    protected function success($data, $message = null, $code = 200) {
        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function error($data, $message = null, $code) {
        return response()->json([
            'success' => false,
            'status' => 'failure',
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
