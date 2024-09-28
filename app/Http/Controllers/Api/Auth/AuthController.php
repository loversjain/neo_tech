<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;
use App\Traits\ApiResponseTrait;
use App\Enums\StatusCode;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // Attempt to login the user
            if (Auth::attempt($request->only('email', 'password'))) {
                // Get authenticated user
                $user = Auth::user();
    
                // Check if the user is active
                if (!$user->is_active) {
                    // Log failed login attempt due to inactive account
                    Log::warning('Inactive account login attempt.', ['email' => $request->email]);
    
                    // Return error if the user account is inactive
                    return $this->errorResponse('Your account is inactive. Please contact support.', StatusCode::FORBIDDEN);
                }
    
                // Create a token for the user
                $token = $user->createToken('MyApp')->plainTextToken;
    
                // Log successful login
                Log::info('User logged in successfully.', ['user_id' => $user->id]);
    
                // Prepare response data
                $responseData = [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'created_at' => $user->created_at,
                    ]
                ];
    
                // Return success response with token and user details
                return $this->successResponse($responseData, 'Login successful', StatusCode::OK);
            }
    
            // Log failed login attempt
            Log::warning('Failed login attempt.', ['email' => $request->email]);
    
            // Return error if authentication fails
            return $this->errorResponse('Invalid email or password', StatusCode::UNAUTHORIZED);
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred while trying to log in.', StatusCode::INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Handle user logout.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        try {
            $user = Auth::user();
    
            // Delete the current access token
            $user->currentAccessToken()->delete();
    
            // Log successful logout
            Log::info('User logged out successfully.', ['user_id' => $user->id]);
    
            return $this->successResponse(['data' => 'User logged out successfully.'], 'Logout successful', StatusCode::OK); // Pass the enum directly
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred while trying to log out.', StatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Handle token refresh request.
     *
     * @return JsonResponse
     */
    public function refreshToken(): JsonResponse
    {
        try {
            $user = Auth::user();
            // Revoke the current token
            $user->currentAccessToken()->delete();

            // Create a new token
            $newToken = $user->createToken('MyApp')->plainTextToken;

            Log::info('Token refreshed successfully.', ['user_id' => $user->id]);

            return $this->successResponse(['token' => $newToken], 'Token refreshed successfully', StatusCode::OK);
        } catch (\Exception $e) {
            Log::error('Token refresh error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred while trying to refresh the token.', StatusCode::INTERNAL_SERVER_ERROR);
        }
    }
}
