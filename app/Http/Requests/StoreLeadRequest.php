<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'   => 'required|string|min:2|max:50',
            'last_name'    => 'required|string|min:2|max:50',
            'email'        => 'required|email|max:255',
            'phone_number' => 'required|string|min:7|max:20',
            'ip_address'   => 'required|ip',
            'user_agent'   => 'required|string|min:5|max:255',
            'query_params' => 'nullable|string',
            'event_id'     => 'nullable|string|max:100',
        ];
    }
}
