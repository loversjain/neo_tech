<?php
namespace App\Traits;

use App\Enums\StatusCode;
use App\Enums\ResponseStatus;
use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Return a success response
     *
     * @param mixed $data The data to return in the response
     * @param string $message Optional custom success message
     * @param StatusCode $code HTTP status code
     * @return JsonResponse
     */
    public function successResponse($data, string $message = ResponseStatus::SUCCESS->value, StatusCode $code = StatusCode::OK): JsonResponse
    {
        return response()->json([
            'status' => ResponseStatus::SUCCESS->value,
            'message' => $message,
            'data' => $data,
        ], $code->value);
    }

    /**
     * Return an error response
     *
     * @param string $message Optional custom error message
     * @param StatusCode $code HTTP status code
     * @return JsonResponse
     */
    public function errorResponse(string $message, StatusCode $code = StatusCode::BAD_REQUEST): JsonResponse
    {
        return response()->json([
            'status' => ResponseStatus::ERROR->value,
            'message' => $message,
        ], $code->value);
    }
}
