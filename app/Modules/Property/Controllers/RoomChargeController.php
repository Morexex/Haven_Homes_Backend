<?php

namespace App\Modules\Property\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Property\Models\RoomCharge;

use Illuminate\Http\Request;

class RoomChargeController extends Controller
{
    public function index()
    {
        return response()->json(RoomCharge::with('room')->get());
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

    public function update(Request $request, RoomCharge $roomCharge)
    {
        $request->validate([
            'amount' => 'numeric',
            'charge_type' => 'string',
            'description' => 'nullable|string',
            'effective_date' => 'date',
        ]);

        $roomCharge->update($request->all());

        return response()->json(['message' => 'Charge updated successfully', 'data' => $roomCharge]);
    }

    public function destroy(RoomCharge $roomCharge)
    {
        $roomCharge->delete();
        return response()->json(['message' => 'Charge deleted successfully']);
    }
}
