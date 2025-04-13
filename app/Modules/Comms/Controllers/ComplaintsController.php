<?php

namespace App\Modules\Comms\Controllers;

use App\Modules\Comms\Models\Complaint;
use App\Modules\Comms\Models\Notice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Comms\Models\ComplaintMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ComplaintsController extends Controller
{
    // Get all complaints
    public function index()
    {
        $complaints = Complaint::with('complainant', 'assignee', 'messages')->latest()->get();

        $formattedComplaints = $complaints->map(function ($complaint) {
            return [
                'id' => $complaint->id,
                'title' => $complaint->title,
                'description' => $complaint->description,
                'status' => $complaint->status,
                'created_at' => $complaint->created_at,
                'updated_at' => $complaint->updated_at,
                'complainant_name' => $complaint->complainant->name ?? null,
                'assignee_name' => $complaint->assignee->name ?? null,
                'priority' => $complaint->priority,
                'incident_date' => $complaint->incident_date,
                'category' => $complaint->category,
                'messages' => $complaint->messages->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'message' => $message->message,
                        'attachment_url' => $message->attachment_url,
                        'sender' => $message->sender ?? null,
                        'created_at' => $message->created_at,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedComplaints,
            'count' => $formattedComplaints->count()
        ]);
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
            'complainant_id' => 'required|exists:property_users,id',
            'sender' => 'required|string',
            'incident_date' => 'required|string',
            'evidence_url' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'message' => 'nullable|string', // 👈 include initial message
            'attachment_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // 👈 for initial attachment
        ]);

        DB::beginTransaction();
        try {
            // Create the complaint
            $complaintData = collect($validated)->except(['message', 'attachment_url', 'sender'])->toArray();
            $complaint = Complaint::create($complaintData);

            // If an image is uploaded, handle it
            if (isset($validated['attachment_url'])) {
                // Generate a unique filename: complaint_id_sender_timestamp.extension
                $extension = $validated['attachment_url']->getClientOriginalExtension();
                $filename = "complaint_{$validated['complainant_id']}_{$validated['sender']}_" . time() . ".{$extension}";

                // Store the image in 'public/messages' with the custom filename
                $path = $validated['attachment_url']->storeAs('messages', $filename, 'public');

                // Update the attachment_url with the image path
                $validated['attachment_url'] = asset("storage/$path");
            }

            // Create the first message under this complaint
            $message = ComplaintMessage::create([
                'complaint_id' => $complaint->id,
                'sender' => $validated['sender'],
                'message' => $validated['message'],
                'attachment_url' => $validated['attachment_url'] ?? null,
            ]);

            // Create notice
            Notice::create([
                'title' => 'New Complaint Filed: ' . $validated['title'],
                'message' => 'A new complaint has been filed with priority: ' . $validated['priority'],
                'type' => 'complaint',
                'source_id' => $complaint->id,
                'source_type' => Complaint::class,
                'user_id' => $validated['complainant_id'],
                'published_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Complaint submitted successfully',
                'data' => [
                    'complaint' => $complaint,
                    'message' => $message->load('sender')
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to submit complaint.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get a single complaint
    public function show($id)
    {
        if (!is_numeric($id)) {
            return response()->json(['error' => 'Invalid complaint ID'], 400);
        }

        $complaint = Complaint::with('complainant', 'assignee', 'messages.sender')->find($id);

        if (!$complaint) {
            return response()->json(['error' => 'Complaint not found'], 404);
        }

        $formattedComplaint = [
            'id' => $complaint->id,
            'title' => $complaint->title,
            'description' => $complaint->description,
            'status' => $complaint->status,
            'created_at' => $complaint->created_at,
            'updated_at' => $complaint->updated_at,
            'complainant_name' => $complaint->complainant->name ?? null,
            'assignee_name' => $complaint->assignee->name ?? null,
            'priority' => $complaint->priority,
            'category' => $complaint->category,
            'incident_date' => $complaint->incident_date,
            'messages' => $complaint->messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'attachment_url' => $message->attachment_url,
                    'sender' => $message->sender ?? null,
                    'created_at' => $message->created_at,
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $formattedComplaint,
        ]);
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
        DB::beginTransaction();
        try {
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

            $notice = Notice::create($noticeData);

            DB::commit();
            return response()->json(['message' => 'Complaint submitted successfully', 'data' => $complaint], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to submit complaint.', 'error' => $e->getMessage()], 500);
        }
    }

    public function addMessage(Request $request, $complaintId)
    {
        $validated = $request->validate([
            'message' => 'nullable|string',
            'sender' => 'required|string',
            'attachment_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Single image validation
        ]);

        $complaint = Complaint::findOrFail($complaintId);

        // If an image is uploaded, handle it
        if (isset($validated['attachment_url'])) {
            // Generate a unique filename: complaint_id_sender_timestamp.extension
            $extension = $validated['attachment_url']->getClientOriginalExtension();
            $filename = "complaint_{$complaintId}_{$validated['sender']}_" . time() . ".{$extension}";

            // Store the image in 'public/messages' with the custom filename
            $path = $validated['attachment_url']->storeAs('messages', $filename, 'public');

            // Update the attachment_url with the image path
            $validated['attachment_url'] = asset("storage/$path");
        }

        // Create the message with the attachment URL (image if uploaded), also if the message is null, set it to an empty string
        $message = ComplaintMessage::create([
            'complaint_id' => $complaintId,
            'sender' => $validated['sender'],
            'message' => $validated['message'] ?? 'no message',
            'attachment_url' => $validated['attachment_url'] ?? null,
        ]);

        return response()->json([
            'message' => 'Message added successfully',
            'data' => $message,  // Return the newly created message
        ], 200);
    }

    // Delete a complaint
    public function destroy($id)
    {
        $complaint = Complaint::find($id);
        DB::beginTransaction();
        try {
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

            $notice = Notice::create($noticeData);

            DB::commit();
            return response()->json(['message' => 'Complaint submitted successfully', 'data' => $complaint], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to submit complaint.', 'error' => $e->getMessage()], 500);
        }
    }
}

