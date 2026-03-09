<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LeadFormController extends Controller
{
    public function show(Request $request)
    {
        return view('lead-form', [
            'queryParams' => $request->query(),
        ]);
    }

    public function submit(Request $request)
    {
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
            'query_params' => $request->input('query_params'),
        ]);

        if ($response->status() === 201) {
            return view('lead-form', [
                'success' => true,
                'leadId'  => $response->json('lead_id'),
            ]);
        }

        if ($response->status() === 401) {
            return view('lead-form', ['authError' => true]);
        }

        if ($response->status() === 422) {
            return back()->withErrors($response->json('errors'))->withInput();
        }

        return view('lead-form', [
            'serverError' => $response->status() . ' ' . $response->json('message'),
        ]);
    }
}
