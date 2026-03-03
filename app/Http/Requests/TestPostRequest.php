<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TestPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'event' => 'required|in:PageView,Lead,Purchase',
        ];
    }
}
