<?php

namespace App\Modules\Property\Controllers;

use App\Modules\Property\Models\Amenity;
use App\Modules\Property\Models\RoomCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AmenitiesController extends Controller
{
    /**
     * Display all amenities.
     */
    public function index()
    {
        $amenities = Amenity::with('category')->get();
        return response()->json($amenities);
    }

    /**
     * Store a new amenity under a category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'size'        => 'nullable|string|max:255',
            'image'       => 'nullable|string', // Store image as a URL or path
            'color'       => 'nullable|string|max:50',
            'condition'   => 'required|string|max:50',
            'category_id' => 'required|exists:room_categories,id',
        ]);

        $amenity = Amenity::create($validated);

        return response()->json(['message' => 'Amenity created successfully', 'amenity' => $amenity], 201);
    }

    /**
     * Show a specific amenity.
     */
    public function show($id)
    {
        $amenity = Amenity::with('category')->find($id);

        if (!$amenity) {
            return response()->json(['error' => 'Amenity not found'], 404);
        }

        return response()->json($amenity);
    }

    /**
     * Update an existing amenity.
     */
    public function update(Request $request, $id)
    {
        $amenity = Amenity::find($id);

        if (!$amenity) {
            return response()->json(['error' => 'Amenity not found'], 404);
        }

        $validated = $request->validate([
            'label'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'size'        => 'nullable|string|max:255',
            'image'       => 'nullable|string',
            'color'       => 'nullable|string|max:50',
            'condition'   => 'sometimes|string|max:50',
            'category_id' => 'sometimes|exists:room_categories,id',
        ]);

        $amenity->update($validated);

        return response()->json(['message' => 'Amenity updated successfully', 'amenity' => $amenity]);
    }

    /**
     * Delete an amenity.
     */
    public function destroy($id)
    {
        $amenity = Amenity::find($id);

        if (!$amenity) {
            return response()->json(['error' => 'Amenity not found'], 404);
        }

        $amenity->delete();
        return response()->json(['message' => 'Amenity deleted successfully']);
    }
}
