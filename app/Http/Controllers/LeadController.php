<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeadController extends Controller
{
    public function index()
    {
        return response()->json(Lead::latest()->paginate(50));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:500',
            'linkedin_url' => 'nullable|url|max:500',
            'source' => 'nullable|string|max:100',
            'company_size' => 'nullable|string|max:50',
            'industry' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'enrichment_data' => 'nullable|json',
            'notes' => 'nullable|string',
        ]);

        // Decode enrichment_data from JSON string if present
        if (isset($validated['enrichment_data']) && is_string($validated['enrichment_data'])) {
            $validated['enrichment_data'] = json_decode($validated['enrichment_data'], true);
        }

        $lead = Lead::create($validated);

        Log::info('New lead created via API', ['lead_id' => $lead->id, 'source' => $lead->source]);

        return response()->json([
            'ok' => true,
            'message' => 'Lead created successfully',
            'lead' => $lead,
        ], 201);
    }

    public function show(Lead $lead)
    {
        return response()->json($lead);
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:500',
            'linkedin_url' => 'nullable|url|max:500',
            'source' => 'nullable|string|max:100',
            'status' => 'nullable|in:new,contacted,qualified,converted,lost',
            'company_size' => 'nullable|string|max:50',
            'industry' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $lead->update($validated);

        return response()->json([
            'ok' => true,
            'message' => 'Lead updated successfully',
            'lead' => $lead,
        ]);
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Lead deleted',
        ]);
    }
}
