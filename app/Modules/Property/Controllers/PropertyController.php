<?php

namespace App\Modules\Property\Controllers;

use Illuminate\Http\Request;
use App\Modules\Property\Models\Property;
use App\Models\PropertyUser;
use App\Services\DatabaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;


class PropertyController extends Controller
{
    protected $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    /**
     * List all properties.
     */
    public function index()
    {
        // Switch to the master database for fetching all properties
        try {
            DatabaseService::switchToMaster();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to switch to master database'], 500);
        }

        // Fetch all properties
        $properties = Property::all();

        // Return properties as JSON response
        return response()->json($properties);
    }

    /**
     * Show a specific property.
     */
    public function show($id)
    {
        // Switch to the master database for user verification
        try {
            DatabaseService::switchToMaster();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to switch to master database'], 500);
        }

        // Find the property based on the given ID
        $property = Property::findOrFail($id);

        // Return the specific property as JSON response
        return response()->json($property);
    }

    /**
     * Create a new property and switch the database.
     */

    /**
     * Update a property.
     */
    public function update(Request $request, $id)
    {   
        // Validate the request data
        $validated = $request->validate([
            'property_name' => ['sometimes', 'string', 'max:255',Rule::unique('properties')->ignore($id),],
            'property_address' => 'sometimes|string',
        ]);

        // Switch to the master database for user verification
        try {
            DatabaseService::switchToMaster();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to switch to master database'], 500);
        }

        // Get the user (owner)
        $user = AdminUser::find($request->owner_id);
        // Find the property based on the given ID
        $property = Property::findOrFail($id);

        // If user doesn't exist, return an error
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Check if the user is an admin and has the 'super_admin' role
        $adminUser = AdminUser::where('email', $user->email)->first();
        $superAdmin = $adminUser && $adminUser->role === 'super_admin';

        if (!$adminUser || !$superAdmin) {
            return response()->json(['error' => 'Only admins can perform this action'], 403);
        }

        // Switch to the property database after verification
        DatabaseService::switchConnection($property->code);

        // Ensure the property code exists
        if (!$property->code) {
            return response()->json(['error' => 'Property code missing'], 400);
        }

        // Update the property details
        $property->update([
            'property_name'    => $validated['property_name'] ?? $property->property_name,
            'property_address' => $validated['property_address'] ?? $property->property_address,
        ]);

        // Return a success response
        return response()->json(['message' => 'Property updated successfully'], 200);
    }

    /**
     * Delete a specific property and drop its associated database.
     * I dont think ill need this function for Data is Money
     */
    public function destroy($id)
    {
        // Switch to the master database for user verification and deleting the property record
        try {
            DatabaseService::switchToMaster();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to switch to master database'], 500);
        }

        // Find the property based on the given ID
        $property = Property::findOrFail($id);

        // Get the property name and generate the associated database name
        $propertyName = $property->property_name;
        $databaseName = strtolower(str_replace(' ', '_', $propertyName));

        // Ensure the property code exists (for switching connections)
        if (!$property->code) {
            return response()->json(['error' => 'Property code missing'], 400);
        }

        // Delete the property record from the properties table in the master database
        try {
            $property->delete();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete the property'], 500);
        }

        // Drop the property-specific database
        try {
            DB::statement("DROP DATABASE IF EXISTS {$databaseName}");
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to drop the property database'], 500);
        }

        // Return a success message as JSON response
        return response()->json(['message' => 'Property and its database deleted successfully']);
    }
}
