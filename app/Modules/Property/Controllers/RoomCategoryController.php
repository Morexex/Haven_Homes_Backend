<?php

namespace App\Modules\Property\Controllers;

use App\Modules\Property\Models\RoomCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RoomCategoryController extends Controller
{
    protected $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }
    /**
     * Display a listing of all room categories with their rooms and amenities.
     */
    public function index()
    {   

        $categories = RoomCategory::with(['rooms', 'amenities'])->get();
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
