<?php

namespace App\Modules\Comms\Controllers;
use App\Modules\Comms\Models\Vacation;
use Illuminate\Http\Request;
use App\Models\PropertyUser;
use App\Modules\Property\Models\Room;
use App\Modules\Comms\Models\Notice;
use App\Http\Controllers\Controller;

class VacationsController extends Controller
{
    // Get all vacation requests
    public function index()
    {
        return response()->json(Vacation::with('tenant', 'room')->latest()->get());
    }

    // Submit a new vacation request
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'reason' => 'nullable|string',
        ]);

        $vacation = Vacation::create($validated);

        $tenant_name = PropertyUser::find($validated['tenant_id'])->name;
        $room_name = Room::find($validated['room_id'])->name;
        // Create a notification for the vacation request
        $noticeData = [
            'title' => 'New Vacation Request',
            'message' => 'A new vacation request has been submitted by ' . $tenant_name . ' for room ' . $room_name,
            'type' => 'vacation',
            'source_id' => $vacation->id,
            'source_type' => Vacation::class,
            'user_id' => $validated['tenant_id'],
            'published_at' => now(),
        ];

        try {
            $notice = Notice::create($noticeData);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Vacation request submitted, but notice failed.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Vacation request submitted', 'data' => $vacation], 201);
    }

    // Get a single vacation request
    public function show($id)
    {
        if (!is_numeric($id)) {
            return response()->json(['error' => 'Invalid vacation ID'], 400);
        }
        $vacation = Vacation::with('tenant', 'room')->find($id);

        return response()->json($vacation->load('tenant', 'room'));
    }

    // Approve or reject a vacation request
    public function update($id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $vacation = Vacation::find($id);

        $vacation->update($validated);

        // Create a notification for the vacation request status update
        $noticeData = [
            'title' => 'Vacation Request Status Update',
            'message' => 'Your vacation request for room ' . $vacation->room->name . ' has been ' . $validated['status'],
            'type' => 'vacation',
            'source_id' => $vacation->id,
            'source_type' => Vacation::class,
            'user_id' => $vacation->tenant_id,
            'published_at' => now(),
        ];

        try {
            $notice = Notice::create($noticeData);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Vacation status updated, but notice failed.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Vacation status updated', 'data' => $vacation]);
    }

    // Delete a vacation request
    public function destroy($id)
    {
        $vacation = Vacation::find($id);
        $vacation->delete();

        // Create a notification for the vacation request deletion
        $noticeData = [
            'title' => 'Vacation Request Deleted',
            'message' => 'Your vacation request for room ' . $vacation->room->name . ' has been deleted',
            'type' => 'vacation',
            'source_id' => $vacation->id,
            'source_type' => Vacation::class,
            'user_id' => $vacation->tenant_id,
            'published_at' => now(),
        ];

        try {
            $notice = Notice::create($noticeData);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Vacation request deleted, but notice failed.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Vacation request deleted']);
    }
}
