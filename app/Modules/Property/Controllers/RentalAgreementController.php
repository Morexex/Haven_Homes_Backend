<?php

namespace App\Modules\Property\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Property\Models\RentalAgreement;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class RentalAgreementController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'payment_date' => 'required|date',
            'tenancy_start_date' => 'required|date',
            'tenant_name' => 'required|string',
            'tenant_email' => 'required|email',
            'tenant_phone' => 'required|string',
            'room_agreement' => 'required|in:yes,no',
            'breakdownCharges' => 'nullable|array',
            'charges_agreement' => 'nullable|array',
            'amenities_agreement' => 'nullable|array',
            'id_front' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'id_back' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        // Store ID images
        $tenantName = str_replace(' ', '_', strtolower($validated['tenant_name']));
        $uploadDate = now()->format('Ymd_His');

        $idFrontPath = $request->file('id_front') 
            ? $request->file('id_front')->storeAs('agreements', "{$tenantName}_id_front_{$uploadDate}.{$request->file('id_front')->extension()}", 'public') 
            : null;

        $idBackPath = $request->file('id_back') 
            ? $request->file('id_back')->storeAs('agreements', "{$tenantName}_id_back_{$uploadDate}.{$request->file('id_back')->extension()}", 'public') 
            : null;

        // Store agreement
        $agreement = RentalAgreement::create([
            'room_id' => $request->room_id,
            'payment_date' => $validated['payment_date'],
            'tenancy_start_date' => $validated['tenancy_start_date'],
            'tenant_name' => $validated['tenant_name'],
            'tenant_email' => $validated['tenant_email'],
            'tenant_phone' => $validated['tenant_phone'],
            'room_agreement' => $validated['room_agreement'],
            'charges_agreement' => $validated['charges_agreement'],
            'amenities_agreement' => $validated['amenities_agreement'],
            'id_front' => $idFrontPath,
            'id_back' => $idBackPath,
            'property_code' => $request->header('Property-Code'),
        ]);

        return response()->json(['message' => 'Agreement submitted successfully'], 201);
    }

    // Retrieve all rental agreements
    public function index()
    {
        $agreements = RentalAgreement::with('room')->get();
        return response()->json($agreements);
    }

    // Retrieve a single rental agreement
    public function show($id)
    {
        $agreement = RentalAgreement::with('room')->findOrFail($id);
        return response()->json($agreement);
    }

    // Update a rental agreement
    public function update(Request $request, $id)
    {
        if ($request->isMethod('put')) {
            $request->request->add($request->all());
        }

        Log::debug('Request Data:', $request->all());

        $agreement = RentalAgreement::findOrFail($id);

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'tenant_name' => 'required|string',
            'tenant_email' => 'required|email',
            'tenant_phone' => 'required|string',
            'room_agreement' => 'required|in:yes,no',
            'breakdownCharges' => 'nullable|array',
            'charges_agreement' => 'nullable|array',
            'amenities_agreement' => 'nullable|array',
            'id_front' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'id_back' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        // Handle file uploads in a reusable function
        $agreement->id_front = $this->handleFileUpload($request, 'id_front', $validated['tenant_name'], $agreement->id_front);
        $agreement->id_back = $this->handleFileUpload($request, 'id_back', $validated['tenant_name'], $agreement->id_back);

        // Only update the validated fields
        $agreement->update(array_filter($validated));

        return response()->json(['message' => 'Agreement updated successfully'], 200);
    }

    /**
     * Helper function for image uploads
     */
    private function handleFileUpload(Request $request, $fieldName, $tenantName, $existingFilePath)
    {
        if ($request->hasFile($fieldName)) {
            $tenantName = str_replace(' ', '_', strtolower($tenantName));
            $uploadDate = now()->format('Ymd_His');
            $file = $request->file($fieldName);

            // Delete old file if exists
            if ($existingFilePath) {
                Storage::disk('public')->delete($existingFilePath);
            }

            // Store new file
            return $file->storeAs('agreements', "{$tenantName}_{$fieldName}_{$uploadDate}.{$file->extension()}", 'public');
        }

        return $existingFilePath; // Keep old file if no new one is uploaded
    }

    // Delete a rental agreement
    public function destroy($id)
    {
        $agreement = RentalAgreement::findOrFail($id);
        $agreement->delete();
        return response()->json(['message' => 'Agreement deleted successfully'], 200);
    }
}
