<?php

namespace App\Modules\Comms\Controllers;

use App\Modules\Comms\Models\Notice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NoticesController extends Controller
{
    // Get all notices
    public function index()
    {
        return response()->json(Notice::with('user')->latest()->get());
    }

    // Store a new notice
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:general,complaint,payment,maintenance,vacancy,audit',
            'source_id' => 'nullable|integer',
            'source_type' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);

        $notice = Notice::create($validated);

        return response()->json(['message' => 'Notice created successfully', 'data' => $notice], 201);
    }

    // Get a single notice
    public function show($id)
    {
        if (!is_numeric($id)) {
            return response()->json(['error' => 'Invalid notice ID'], 400);
        }
        $notice = Notice::with('user')->find($id);

        return response()->json($notice->load('user'));
    }

    // Update a notice
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'message' => 'sometimes|string',
            'type' => 'sometimes|in:general,complaint,payment,maintenance,vacancy,audit',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);

        $notice = Notice::find($id);

        $notice->update($validated);

        return response()->json(['message' => 'Notice updated successfully', 'data' => $notice]);
    }

    // Delete a notice
    public function destroy($id)
    {
        $notice = Notice::find($id);

        $notice->delete();

        return response()->json(['message' => 'Notice deleted successfully']);
    }
}