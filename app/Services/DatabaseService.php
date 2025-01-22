<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

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
            // Format the property name into a valid database name (e.g., "Luxury Brands" becomes "Luxury_Brands")
            $databaseName = str_replace(' ', '_', $property->property_name);

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

            // Set the default connection to the new dynamic one
            DB::setDefaultConnection('property');
        } else {
            throw new \Exception("Property with the given code '{$propertyCode}' does not exist.");
        }
    }
}
