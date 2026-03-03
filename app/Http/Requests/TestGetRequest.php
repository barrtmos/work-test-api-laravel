<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TestGetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|min:2|max:50',
            'age'  => 'nullable|integer|min:18|max:99',
        ];
    }
}
