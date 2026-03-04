<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    /**
     * Standard Success Response
     *
     * @param mixed $data
     * @param string $message
     * @param int $status
     * @param array|null $meta
     * @return JsonResponse
     */
    public function success(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200,
        ?array $meta = null
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => []
        ];

        if ($meta) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    /**
     * Standard Error Response
     *
     * @param string|array|null $errors
     * @param string $message
     * @param int $status
     * @return JsonResponse
     */
    public function error(
        string|array|null $errors = null,
        string $message = 'Error',
        int $status = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors ?? [],
        ], $status);
    }

    /**
     * Validation Error Response
     *
     * @param array $errors
     * @param string $message
     * @param int $status
     * @return JsonResponse
     */
    public function validationError(array $errors, string $message = 'Validation Error', int $status = 422): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors
        ], $status);
    }

    /**
     * Paginated Response
     *
     * @param LengthAwarePaginator $paginator
     * @param string $message
     * @return JsonResponse
     */
    public function paginated(LengthAwarePaginator $paginator, string $message = 'Success'): JsonResponse
    {
        return $this->success(
            data: $paginator->items(),
            message: $message,
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage()
            ]
        );
    }
}
