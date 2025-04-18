<?php

namespace App\Modules\Property\Controllers;

use App\Modules\Property\Models\RoomCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class RoomCategoryController extends Controller
{
    /**
     * Display a listing of all room categories with their rooms and amenities.
     */
    public function index()
    {   
        $categories = RoomCategory::with('rooms', 'amenities')->get();
        foreach ($categories as $category) {
            $category->formatted_created_at = Carbon::parse($category->created_at)->format('jS F Y');
            //rooms count
            $category->rooms_count = $category->rooms->count();
        }
        return response()->json($categories);
    }

    /**
     * Store a new room category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = RoomCategory::create($validated);

        return response()->json(['message' => 'Room category created successfully', 'category' => $category], 201);
    }

    /**
     * Show details of a specific room category with its related rooms and amenities.
     */
    public function show($id)
    {
        $category = RoomCategory::with(['rooms', 'amenities'])->find($id);

        $category->formatted_created_at = Carbon::parse($category->created_at)->format('jS F Y');

        if (!$category) {
            return response()->json(['error' => 'Room category not found'], 404);
        }

        return response()->json($category);
    }

    /**
     * Update an existing room category.
     */
    public function update(Request $request, $id)
    {
        $category = RoomCategory::find($id);

        if (!$category) {
            return response()->json(['error' => 'Room category not found'], 404);
        }

        $validated = $request->validate([
            'label'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return response()->json(['message' => 'Room category updated successfully', 'category' => $category]);
    }

    /**
     * Delete a room category.
     */
    public function destroy($id)
    {
        $category = RoomCategory::find($id);

        if (!$category) {
            return response()->json(['error' => 'Room category not found'], 404);
        }

        $category->delete();
        return response()->json(['message' => 'Room category deleted successfully']);
    }
}
