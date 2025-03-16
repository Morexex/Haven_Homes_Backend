<?php

namespace App\Modules\Property\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Property\Models\Room;
use App\Modules\Property\Models\RoomCategory;
use Illuminate\Http\Request;
use App\Modules\Property\Models\RoomImage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
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
            'floor'       => 'nullable|string|max:255',
        ]);

        $room = Room::create($validated);

        return response()->json(['message' => 'Room created successfully', 'room' => $room], 201);
    }
    public function uploadImages(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $validated = $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'tag' => 'required|string|max:255',
        ]);

        $tag = Str::slug($validated['tag']); // Slugify the tag
        $roomName = Str::slug($room->label); // Slugify the property name
        $imageData = [];

        foreach ($validated['images'] as $image) {
            // Generate a unique filename: propertyname_tag_timestamp.extension
            $extension = $image->getClientOriginalExtension();
            $filename = "{$roomName}_{$tag}_" . time() . ".{$extension}";

            // Store the image in 'public/properties' with the custom filename
            $path = $image->storeAs('rooms', $filename, 'public');

            // Save image record in the database
            $roomImage = RoomImage::create([
                'room_id' => $room->id,
                'image_path' => $path,
                'tag' => $validated['tag'],
            ]);

            // Add image info to the response
            $imageData[] = [
                'id' => $roomImage->id,
                'image_url' => asset("storage/$path"),
                'tag' => $validated['tag'],
            ];
        }

        return response()->json([
            'message' => 'Images uploaded successfully',
            'images' => $imageData,
        ], 200);
    }
    public function updateImage(Request $request, $room_id, $image_id)
    {
        // Find the existing image in the database
        $roomImage = RoomImage::where('room_id', $room_id)
            ->where('id', $image_id)
            ->firstOrFail();
    
        // Validate request
        $validated = $request->validate([
            'tag' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Image is optional
        ]);
    
        // Update tag (whether image is replaced or not)
        $roomImage->tag = $validated['tag'];
        
    
        // If a new image is uploaded, replace the old one
        if ($request->hasFile('image')) {
            // Delete the old image from storage
            Storage::disk('public')->delete($roomImage->image_path);
    
            // Generate new filename
            $room = Room::findOrFail($room_id);
            $roomName = Str::slug($room->label);
            $tag = Str::slug($validated['tag']);
            $extension = $request->file('image')->getClientOriginalExtension();
            $filename = "{$roomName}_{$tag}_" . time() . ".{$extension}";
    
            // Store the new image
            $path = $request->file('image')->storeAs('rooms', $filename, 'public');
            // Update image path in the database
            $roomImage->image_path = $path;
        }
    
        // Save changes
        $roomImage->save();
    
        return response()->json([
            'message' => 'Image updated successfully',
            'image' => [
                'id' => $roomImage->id,
                'image_url' => asset("storage/{$roomImage->image_path}"),
                'tag' => $roomImage->tag,
            ]
        ], 200);
    }

    public function getRoomImages($id)
    {
        $roomImages = RoomImage::where('room_id', $id)->get();

        $formattedImages = $roomImages->map(function ($image) {
            return [
                'id' => $image->id,
                'image_url' => asset("storage/{$image->image_path}"),
                'tag' => $image->tag,
            ];
        });

        return response()->json(['images' => $formattedImages], 200);
    }

    /**
     * Show details of a specific room.
     */
    public function show($id)
    {
        $room = Room::where('id', $id)->first();
    
        if (!$room) {
            return response()->json(['error' => 'Room not found'], 404);
        }
    
        return response()->json(['room_details' => $room->roomDetailsArray()], 200);
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
            'floor'       => 'nullable|string|max:255',
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
