<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\DatabaseService;

class DynamicAuthMiddleware
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        // Set default guards if none are provided
        if (empty($guards)) {
            $guards = ['admin_user', 'property_user'];
        }

        // Initialize an array to hold the authentication results
        $authResults = [];
        $authenticatedUser = null;

        // Try authenticating each guard one after the other
        foreach ($guards as $guard) {
            if ($guard === 'property_user') {
                // Get Property-Code from the request header
                $propertyCode = $request->header('Property-Code');

                if (!$propertyCode) {
                    Log::warning("Missing Property-Code header for property authentication.");
                    return response()->json([
                        'error' => 'Property-Code header is required for property authentication'
                    ], 400);
                }

                Log::info("Switching to property database for Property-Code: $propertyCode");
                DatabaseService::switchConnection($propertyCode);
            }

            // log connection and database before auth
            Log::info("Current database connection: " . config('database.default'));
            Log::info("Current database name: " . config('database.connections.' . config('database.default') . '.database'));
            $user = Auth::guard($guard)->user();
            $authResults[$guard] = $user ? 'Authenticated' : 'Not Authenticated';

            Log::info("Guard: $guard, User: " . ($user ? 'Authenticated' : 'Not Authenticated'));

            if ($user) {
                $authenticatedUser = $user;
                break; // Stop checking once we find an authenticated user
            }
        }

        // If authentication is successful, proceed with the request
        if ($authenticatedUser) {
            $request->attributes->set('authenticated_user', $authenticatedUser);
            return $next($request);
        }

        // If authentication fails, return unauthorized response
        return response()->json([
            'error' => 'Unauthorized',
            'results' => $authResults
        ], 401);
    }
}
