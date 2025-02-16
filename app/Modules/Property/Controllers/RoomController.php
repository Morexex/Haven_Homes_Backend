<?php

namespace App\Modules\Property\Controllers;

use App\Http\Controllers\Controller;
use App\Helpers\DatabaseHelper;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\Room;
use App\Modules\Property\Models\RoomCategory;
use App\Services\DatabaseService;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    protected $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }
    /**
     * Display a listing of all rooms.
     */
    public function index()
    {
        $rooms = Room::with('category.amenities')->get();
        return response()->json($rooms);
    }

    /**
     * Store a new room.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label'       => 'required|string|max:255',
            'is_vacant'   => 'boolean',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:room_categories,id',
            'quantity'    => 'required|integer|min:1',
            'floor'       => 'nullable|integer',
            'property_code'=> 'required|'
        ]);

        $room = Room::create($validated);

        return response()->json(['message' => 'Room created successfully', 'room' => $room], 201);
    }

    /**
     * Show details of a specific room.
     */
    public function show($id)
    {
        $room = Room::with('category.amenities')->find($id);

        if (!$room) {
            return response()->json(['error' => 'Room not found'], 404);
        }

        return response()->json($room);
    }

    /**
     * Update an existing room.
     */
    public function update(Request $request, $id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json(['error' => 'Room not found'], 404);
        }

        $validated = $request->validate([
            'label'       => 'sometimes|string|max:255',
            'is_vacant'   => 'sometimes|boolean',
            'description' => 'nullable|string',
            'category_id' => 'sometimes|exists:room_categories,id',
            'quantity'    => 'sometimes|integer|min:1',
            'floor'       => 'nullable|integer',
        ]);

        $room->update($validated);

        return response()->json(['message' => 'Room updated successfully', 'room' => $room]);
    }

    /**
     * Delete a room.
     */
    public function destroy($id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json(['error' => 'Room not found'], 404);
        }

        $room->delete();
        return response()->json(['message' => 'Room deleted successfully']);
    }
}
