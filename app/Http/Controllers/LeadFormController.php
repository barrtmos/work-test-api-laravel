<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LeadFormController extends Controller
{
    public function show(Request $request)
    {
        session(['query_params' => $request->query()]);

        $eventId = 'lead_' . time() . '_' . Str::random(8);
        session(['event_id' => $eventId]);

    return view('lead-form', [
        'eventId'   => $eventId,
        'ipAddress' => $request->ip(),
        'userAgent' => $request->userAgent(),
    ]);
    }

    public function submit(Request $request)
    {
        $eventId = session('event_id');
        $apiBase = config('app.api_base_url') ?: $request->getSchemeAndHttpHost();
        $response = Http::withHeaders([
            'X-API-KEY' => config('app.api_key'),
            'Accept'    => 'application/json',
        ])->post($apiBase . '/api/lead', [
            'first_name'   => $request->input('first_name'),
            'last_name'    => $request->input('last_name'),
            'email'        => $request->input('email'),
            'phone_number' => $request->input('phone_number'),
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->userAgent(),
            'query_params' => json_encode(session('query_params')),
            'event_id'     => $eventId,
        ]);

        if ($response->status() === 201) {
            return view('lead-form', [
                'success' => true,
                'leadId'  => $response->json('lead_id'),
                'eventId' => $eventId,
            ]);
        }

        if ($response->status() === 401) {
            return view('lead-form', [
                'authError' => true,
                'eventId' => $eventId,
            ]);
        }

        if ($response->status() === 422) {
            return back()->withErrors($response->json('errors'))->withInput();
        }

        return view('lead-form', [
            'serverError' => $response->status() . ' ' . $response->json('message'),
            'eventId' => $eventId,
        ]);
    }
}
