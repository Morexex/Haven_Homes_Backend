<?php

namespace App\Modules\Property\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Property\Models\RoomCharge;

use Illuminate\Http\Request;
use Carbon\Carbon;

class RoomChargeController extends Controller
{
    public function index()
    {
        $charges = RoomCharge::with('room')->get();
        foreach ($charges as $charge) {
            $charge->formatted_created_at = Carbon::parse($charge->created_at)->format('jS F Y');
            //rooms count
            $charge->room_name = $charge->room->label;
        }
        return response()->json($charges);
    }

    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'amount' => 'required|numeric',
            'charge_type' => 'required|string',
            'description' => 'nullable|string',
            'effective_date' => 'required|date',
        ]);

        $charge = RoomCharge::create($request->all());

        return response()->json(['message' => 'Charge added successfully', 'data' => $charge]);
    }

    public function show(RoomCharge $roomCharge)
    {
        return response()->json($roomCharge->load('room'));
    }

    public function update(Request $request, $id)
    {
        $charge = RoomCharge::find($id);

        if (!$charge) {
            return response()->json(['error' => 'Amenity not found'], 404);
        }

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'amount' => 'required|numeric',
            'charge_type' => 'required|string',
            'description' => 'nullable|string',
            'effective_date' => 'required|date',
        ]);

        $charge->update($validated);

        return response()->json(['message' => 'Charge updated successfully', 'charge' => $charge]);
    }

    public function destroy($id)
    {
        $charge = RoomCharge::find($id);

        if (!$charge) {
            return response()->json(['error' => 'Charge not found'], 404);
        }

        $charge->delete();
        return response()->json(['message' => 'Charge deleted successfully']);
    }
}
