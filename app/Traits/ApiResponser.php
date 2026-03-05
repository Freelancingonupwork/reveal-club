<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponser
{
    protected function successResponse($message = null, $data = NULL, $code = 200, $token = null): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'status' => 'success',
        ], $code);
    }

    protected function notFoundResponse($message = null, $data = NULL, $code = 200, $token = null): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'status' => 'fail',
        ], $code);
    }

    protected function errorResponse($message = null, $errors = [], $code = null): JsonResponse
    {
        return response()->json([
            "status" => "fail",
            'message' => $message,
            'errors' => $errors,
            'code' => $code,
        ], $code);
    }

    protected function getSingleResponse($message = null, $data = NULL, $code = 200, $token = null): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'status' => 'success',
        ], $code);
    }

    public function validationError($status = null, $message = null, $code = null)
    {
        return response()->json(
            [
                "status" => $status,
                // 'errors' => $validator->getMessageBag(),
                "data" => (object)[],
                'message' => $message,
            ],
            $code
        );
    }

    public function throwableError($message = null, $error = null, $code = null)
    {
        return response()->json(
            [
                "status" => "Fail",
                "message" => $message,
                'errors' => $error,
            ],
            $code
        );
    }
}
