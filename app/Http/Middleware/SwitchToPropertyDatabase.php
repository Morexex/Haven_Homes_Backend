<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\DatabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SwitchToPropertyDatabase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        //dd($request,'Middleware working here in property');
        try {
            // Retrieve the property code from the request (you can change this logic based on your use case)
            $propertyCode = $request->header('Property-Code');

            if ($propertyCode) {
                // Force close any previous connection
                //DB::disconnect();
                // Log the property code being switched to
                Log::info("Attempting to switch to property database with code: {$propertyCode}");

                // Switch to the property database using the DatabaseService
                DatabaseService::switchConnection($propertyCode);
            } else {
                Log::warning("Property code missing in the request header.");
            }

            // Proceed with the request
            return $next($request);

        } catch (\Exception $e) {
            // Log the error and handle it
            Log::error("Error switching to property database: " . $e->getMessage());
            return response()->json(['error' => 'Database connection error.'], 500);
        }
    }
}
