<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    /**
     * Standard Success Response
     */
    public function success(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200,
        ?array $meta = null,
        ?string $code = null
    ): JsonResponse {

        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data ?? [],
            'errors' => [],
        ];

        if ($meta) {
            $response['meta'] = $meta;
        }

        if ($code) {
            $response['code'] = $code;
        }

        return response()->json($response, $status);
    }

    /**
     * Standard Error Response
     */
    public function error(
        string|array|null $errors = null,
        string $message = 'Error',
        int $status = 400,
        ?string $code = null
    ): JsonResponse {

        $response = [
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors ? (array) $errors : [],
        ];

        if ($code) {
            $response['code'] = $code;
        }

        return response()->json($response, $status);
    }

    /**
     * Validation Error Response
     */
    public function validationError(
        array $errors,
        string $message = 'Validation Error',
        int $status = 422,
        ?string $code = 'VALIDATION_ERROR'
    ): JsonResponse {

        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
            'code' => $code
        ], $status);
    }

    /**
     * Paginated Response
     */
    public function paginated(
        LengthAwarePaginator $paginator,
        string $message = 'Success'
    ): JsonResponse {

        return $this->success(
            data: $paginator->items(),
            message: $message,
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'has_more' => $paginator->hasMorePages(),
            ]
        );
    }
}
