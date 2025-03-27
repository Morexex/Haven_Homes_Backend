<?php
namespace App\Modules\Comms\Controllers;

use App\Modules\Comms\Models\BulkCommunication;
use App\Modules\Comms\Models\BulkCommunicationRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class BulkCommunicationController extends Controller
{
    // Get all bulk communications
    public function index()
    {
        return response()->json(BulkCommunication::with('creator', 'recipients.user')->latest()->get());
    }

    // Store a new bulk communication
    public function store(Request $request)
    {
        $validated = $request->validate([
            'created_by' => 'required|exists:users,id',
            'type' => 'required|in:whatsapp,email,sms',
            'recipients' => 'required|array',
            'recipients.*.user_id' => 'required|exists:users,id',
            'recipients.*.message' => 'required|string',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        // Create bulk communication record
        $bulkCommunication = BulkCommunication::create([
            'created_by' => $validated['created_by'],
            'type' => $validated['type'],
            'status' => 'pending',
            'scheduled_at' => $validated['scheduled_at'] ?? null,
        ]);

        // Attach recipients
        foreach ($validated['recipients'] as $recipient) {
            BulkCommunicationRecipient::create([
                'bulk_communication_id' => $bulkCommunication->id,
                'user_id' => $recipient['user_id'],
                'message' => $recipient['message'],
                'status' => 'pending',
            ]);
        }

        return response()->json(['message' => 'Bulk communication created successfully', 'data' => $bulkCommunication], 201);
    }

    // Get a single bulk communication
    public function show($id)
    {
        if (!is_numeric($id)) {
            return response()->json(['error' => 'Invalid bulk communication ID'], 400);
        }
        return response()->json(BulkCommunication::with('creator', 'recipients.user')->find($id));
    }

    // Process and send the bulk messages
    public function sendBulkMessages($id)
    {
        if (!is_numeric($id)) {
            return response()->json(['error' => 'Invalid bulk communication ID'], 400);
        }

        $bulkCommunication = BulkCommunication::with('recipients')->find($id);

        if ($bulkCommunication->status !== 'pending') {
            return response()->json(['message' => 'Bulk communication has already been processed'], 400);
        }

        // Simulate sending messages
        foreach ($bulkCommunication->recipients as $recipient) {
            try {
                // TODO: Implement actual sending logic (SMS, Email, WhatsApp API)
                $recipient->update(['status' => 'sent']);
            } catch (\Exception $e) {
                Log::error("Failed to send message to user {$recipient->user_id}: " . $e->getMessage());
                $recipient->update(['status' => 'failed', 'failure_reason' => $e->getMessage()]);
            }
        }

        // Update bulk communication status
        $bulkCommunication->update(['status' => 'sent']);

        return response()->json(['message' => 'Bulk communication processed successfully']);
    }

    // Delete a bulk communication
    public function destroy($id)
    {
        if (!is_numeric($id)) {
            return response()->json(['error' => 'Invalid bulk communication ID'], 400);
        }

        $bulkCommunication = BulkCommunication::find($id);

        $bulkCommunication->delete();

        return response()->json(['message' => 'Bulk communication deleted successfully']);
    }
}