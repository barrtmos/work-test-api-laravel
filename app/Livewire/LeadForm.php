<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Livewire\Component;

class LeadForm extends Component
{
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone_number = '';
    public string $eventId = '';
    public ?int $leadId = null;
    public bool $success = false;
    public bool $authError = false;
    public ?string $serverError = null;

    protected function rules(): array
    {
        return [
            'first_name' => 'required|string|min:2|max:50',
            'last_name' => 'required|string|min:2|max:50',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|min:7|max:20',
        ];
    }

    public function mount(): void
    {
        session(['query_params' => request()->query()]);
        $this->eventId = 'lead_' . time() . '_' . Str::random(8);
        session(['event_id' => $this->eventId]);
    }

    public function submit(): void
    {
        if ($this->success) {
            return;
        }

        $this->resetErrorBag();
        $this->authError = false;
        $this->serverError = null;

        $this->validate();

        $apiBase = config('app.api_base_url') ?: request()->getSchemeAndHttpHost();

        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->post($apiBase . '/api/lead', [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'query_params' => json_encode(session('query_params')),
            'event_id' => session('event_id', $this->eventId),
        ]);

        if ($response->status() === 201) {
            $this->success = true;
            $this->leadId = $response->json('lead_id');

            return;
        }

        if ($response->status() === 401) {
            $this->authError = true;

            return;
        }

        if ($response->status() === 422) {
            foreach ($response->json('errors', []) as $field => $messages) {
                foreach ((array) $messages as $message) {
                    $this->addError($field, $message);
                }
            }

            return;
        }

        $this->serverError = $response->status() . ' ' . ($response->json('message') ?? 'Server error');
    }

    public function render()
    {
        return view('livewire.lead-form');
    }
}
