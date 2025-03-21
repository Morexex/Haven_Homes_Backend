<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class DatabaseService
{
    /**
     * Switch database connection to the one belonging to the specified property.
     *
     * @param string $propertyCode
     * @return void
     * @throws \Exception
     */
    public static function switchConnection($propertyCode)
    {
        // Fetch the property from the master database
        $property = Property::where('property_code', $propertyCode)->first();

        if ($property) {
            // Format the property name into a valid database name (e.g., "Luxury Brands" becomes "luxury_brands")
            $databaseName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $property->property_name)));
            // Dynamically configure the connection settings for the specific property database
            Config::set('database.connections.property', [
                'driver'    => 'mysql',
                'host'      => env('DB_HOST', '127.0.0.1'),
                'port'      => env('DB_PORT', '3306'),
                'database'  => $databaseName, // Use the formatted property database name
                'username'  => env('DB_USERNAME', 'root'),
                'password'  => env('DB_PASSWORD', ''),
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);

            // Purge the old connection to ensure the new one is used
            DB::purge('property');
            DB::reconnect('property');

            // Set the default connection to the new dynamic one
            DB::setDefaultConnection('property');


        } else {
            // Log error if the property is not found
            Log::error("Property with the given code '{$propertyCode}' does not exist.");
            throw new \Exception("Property with the given code '{$propertyCode}' does not exist.");
        }
    }

    /**
     * Switch database connection back to the haven_master database.
     *
     * @return void
     */
    public static function switchToMaster()
    {
        // Dynamically configure the connection for the master database
        Config::set('database.connections.mysql', [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => env('DB_DATABASE', 'haven_master'), // Ensure DB_DATABASE is set to 'haven_master'
            'username'  => env('DB_USERNAME', 'root'),
            'password'  => env('DB_PASSWORD', ''),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        // Purge the old connection to ensure the new one is used
        DB::purge('mysql');
        DB::reconnect('mysql');

        // Set the default connection back to the master database
        DB::setDefaultConnection('mysql');

        // Log the successful switch back to the master database
        Log::info("Switched back to the master database.");
    }
}
