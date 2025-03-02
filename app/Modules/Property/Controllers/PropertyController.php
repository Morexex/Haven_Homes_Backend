<?php

namespace App\Modules\Property\Controllers;

use Illuminate\Http\Request;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\PropertyImage;
use App\Models\PropertyUser;
use App\Models\AdminUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;



class PropertyController extends Controller
{
    /**
     * List all properties.
     */
    public function index()
    {
        // Fetch all properties
        $properties = Property::all();

        // Return properties as JSON response
        return response()->json($properties);
    }

    /**
     * Show a specific property.
     */
    public function show($property_code)
    {
        // Find the property based on the given property_code
        $property = Property::where('property_code', $property_code)->firstOrFail();

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

        // Update the property details
        $property->update([
            'property_name'    => $validated['property_name'] ?? $property->property_name,
            'property_address' => $validated['property_address'] ?? $property->property_address,
        ]);

        // Return a success response
        return response()->json(['message' => 'Property updated successfully'], 200);
    }

    public function uploadImages(Request $request, $id)
    {
        $property = Property::findOrFail($id);

        $validated = $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'tag' => 'required|string|max:255',
        ]);

        $tag = Str::slug($validated['tag']); // Slugify the tag
        $propertyName = Str::slug($property->property_name); // Slugify the property name
        $imageData = [];

        // ✅ Check if a logo already exists for this property
        if ($tag === 'logo') {
            $existingLogo = PropertyImage::where('property_id', $property->id)
                ->where('tag', 'logo')
                ->first();

            if ($existingLogo) {
                return response()->json([
                    'error' => 'A logo already exists for this property. Please Update the existing logo.'
                ], 400);
            }
        }

        foreach ($validated['images'] as $image) {
            // Generate a unique filename: propertyname_tag_timestamp.extension
            $extension = $image->getClientOriginalExtension();
            $filename = "{$propertyName}_{$tag}_" . time() . ".{$extension}";

            // Store the image in 'public/properties' with the custom filename
            $path = $image->storeAs('properties', $filename, 'public');

            // Save image record in the database
            $propertyImage = PropertyImage::create([
                'property_id' => $property->id,
                'image_path' => $path,
                'tag' => $validated['tag'],
            ]);

            // Add image info to the response
            $imageData[] = [
                'id' => $propertyImage->id,
                'image_url' => asset("storage/$path"),
                'tag' => $validated['tag'],
            ];
        }

        return response()->json([
            'message' => 'Images uploaded successfully',
            'images' => $imageData,
        ], 200);
    }
    public function updateImage(Request $request, $property_id, $image_id)
    {
        // Find the existing image in the database
        $propertyImage = PropertyImage::where('property_id', $property_id)
            ->where('id', $image_id)
            ->firstOrFail();
    
        // Validate request
        $validated = $request->validate([
            'tag' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Image is optional
        ]);

        //Restrict multiple logos (only when changing the tag to 'logo')
        if ($validated['tag'] === 'logo') {
            $existingLogo = PropertyImage::where('property_id', $property_id)
                ->where('tag', 'logo')
                ->first();

            if ($existingLogo) {
                return response()->json([
                    'error' => 'A logo already exists for this property. Please update the existing logo instead of adding a new one.'
                ], 400);
            }
        }
    
        // Update tag (whether image is replaced or not)
        $propertyImage->tag = $validated['tag'];
        
    
        // If a new image is uploaded, replace the old one
        if ($request->hasFile('image')) {
            // Delete the old image from storage
            Storage::disk('public')->delete($propertyImage->image_path);
    
            // Generate new filename
            $property = Property::findOrFail($property_id);
            $propertyName = Str::slug($property->property_name);
            $tag = Str::slug($validated['tag']);
            $extension = $request->file('image')->getClientOriginalExtension();
            $filename = "{$propertyName}_{$tag}_" . time() . ".{$extension}";
    
            // Store the new image
            $path = $request->file('image')->storeAs('properties', $filename, 'public');
    
            // Update image path in the database
            $propertyImage->image_path = $path;
        }
    
        // Save changes
        $propertyImage->save();
    
        return response()->json([
            'message' => 'Image updated successfully',
            'image' => [
                'id' => $propertyImage->id,
                'image_url' => asset("storage/{$propertyImage->image_path}"),
                'tag' => $propertyImage->tag,
            ]
        ], 200);
    }

    public function getPropertyImages($id)
    {
        $propertyImages = PropertyImage::where('property_id', $id)->get();

        $formattedImages = $propertyImages->map(function ($image) {
            return [
                'id' => $image->id,
                'image_url' => asset("storage/{$image->image_path}"),
                'tag' => $image->tag,
            ];
        });

        return response()->json(['images' => $formattedImages], 200);
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
