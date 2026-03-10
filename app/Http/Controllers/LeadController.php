<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use App\Services\FacebookConversionService;

class LeadController extends Controller
{
    public function store(StoreLeadRequest $request)
    {
        $lead = Lead::create($request->validated());

        $eventId = $request->input('event_id');
        if ($eventId) {
            $facebookService = new FacebookConversionService();
            $facebookService->sendLeadEvent($lead->toArray(), $eventId);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lead stored',
            'lead_id' => $lead->id,
        ], 201);
    }
}
