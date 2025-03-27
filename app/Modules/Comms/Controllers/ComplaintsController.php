<?php

namespace App\Modules\Comms\Controllers;

use App\Modules\Comms\Models\Complaint;
use App\Modules\Comms\Models\Notice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ComplaintsController extends Controller
{
    // Get all complaints
    public function index()
    {
        return response()->json(Complaint::with('complainant', 'assignee')->latest()->get());
    }

    // Store a new complaint
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:50',
            'priority' => 'required|in:Low,Medium,High,Urgent',
            'status' => 'required|in:Pending,In Progress,Resolved,Rejected',
            'complainant_id' => 'required|exists:users,id',
            'incident_date' => 'required|date',
            'evidence_url' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $complaint = Complaint::create($validated);

        // Create a notification for the complaint
        $noticeData = [
            'title' => 'New Complaint Filed: ' . $validated['title'],
            'message' => 'A new complaint has been filed with priority: ' . $validated['priority'],
            'type' => 'complaint',
            'source_id' => $complaint->id,
            'source_type' => Complaint::class,
            'user_id' => $validated['complainant_id'],
            'published_at' => now(),
        ];

        try {
            $notice = Notice::create($noticeData);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Complaint created, but notice failed.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Complaint submitted successfully', 'data' => $complaint], 201);
    }

    // Get a single complaint
    public function show($id)
    {
        if (!is_numeric($id)) {
            return response()->json(['error' => 'Invalid complaint ID'], 400);
        }
        $complaint = Complaint::with('complainant', 'assignee')->find($id);

        return response()->json($complaint->load('complainant', 'assignee'));
    }

    // Update a complaint (e.g., assign staff, update status)
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:Pending,In Progress,Resolved,Rejected',
            'assigned_to' => 'nullable|exists:users,id',
            'resolution_notes' => 'nullable|string',
            'resolved_at' => 'nullable|date',
        ]);

        $complaint = Complaint::find($id);

        $complaint->update($validated);

        // Create a notification for the complaint status update
        $noticeData = [
            'title' => 'Complaint Status Update: ' . $complaint->title,
            'message' => 'The status of your complaint has been updated to: ' . $complaint->status,
            'type' => 'complaint',
            'source_id' => $complaint->id,
            'source_type' => Complaint::class,
            'user_id' => $complaint->complainant_id,
            'published_at' => now(),
        ];

        try {
            $notice = Notice::create($noticeData);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Complaint updated, but notice failed.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Complaint updated successfully', 'data' => $complaint]);
    }

    // Delete a complaint
    public function destroy($id)
    {
        $complaint = Complaint::find($id);

        $complaint->delete();

        // Create a notification for the complaint deletion
        $noticeData = [
            'title' => 'Complaint Deleted: ' . $complaint->title,
            'message' => 'Your complaint has been deleted',
            'type' => 'complaint',
            'source_id' => $complaint->id,
            'source_type' => Complaint::class,
            'user_id' => $complaint->complainant_id,
            'published_at' => now(),
        ];

        try {
            $notice = Notice::create($noticeData);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Complaint deleted, but notice failed.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Complaint deleted successfully']);
    }
}

