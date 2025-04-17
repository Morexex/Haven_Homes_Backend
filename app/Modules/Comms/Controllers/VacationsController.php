<?php

namespace App\Modules\Comms\Controllers;
use App\Modules\Comms\Models\Vacation;
use Illuminate\Http\Request;
use App\Models\PropertyUser;
use App\Modules\Property\Models\Room;
use App\Modules\Comms\Models\Notice;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class VacationsController extends Controller
{
    // Get all vacation requests
    public function index()
    {
        $vacations = Vacation::with('tenant', 'room')->latest()->get();
        foreach ($vacations as $vacation ) {
            $vacation->tenant_name = $vacation->tenant->name;
            $vacation->room_name = $vacation->room->label;
            $vacation->formatted_application_date = Carbon::parse($vacation->created_at)->format('jS F Y');
            $vacation->proposed_vacation_date_formatted = Carbon::parse($vacation->proposed_vacation_date)->format('jS F Y');
        }
        return response()->json($vacations);
    }

    // Submit a new vacation request
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:property_users,id',
            'room_id' => 'required|exists:rooms,id',
            'reason' => 'nullable|string',
            'proposed_vacation_date' => 'required|string',
        ]);

        Log::info($validated);

        $vacation = Vacation::create($validated);

        $tenant_name = PropertyUser::find($validated['tenant_id'])->name;
        $proposed_date = Carbon::parse($validated['proposed_vacation_date'])->format('jS F Y');
        $room_name = Room::find($validated['room_id'])->name;
        // Create a notification for the vacation request
        $noticeData = [
            'title' => 'New Vacation Request',
            'message' => 'A new vacation request has been submitted by ' . $tenant_name . ' for room ' . $room_name . 'proposed to vacate on:' .$proposed_date,
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
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:pending,approved,rejected',
            'actual_vacation_date' => 'nullable|string',
            'room_id' => 'required|exists:rooms,id',
            'reason' => 'nullable|string',
            'proposed_vacation_date' => 'required|string',
        ]);

        $vacation = Vacation::find($id);

        $vacation->update($validated);

        // Create a notification for the vacation request status update
        if(!empty($validated['status']))
        {
            $noticeData = [
                'title' => 'Vacation Request Status Update',
                'message' => 'Your vacation request for room ' . $vacation->room->name . ' has been ' . $validated['status'],
                'type' => 'vacation',
                'source_id' => $vacation->id,
                'source_type' => Vacation::class,
                'user_id' => $vacation->tenant_id,
                'published_at' => now(),
            ];
        }

        try {
            if(!empty($validated['status'])){
                $notice = Notice::create($noticeData);
            } else {
                return;
            }
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
