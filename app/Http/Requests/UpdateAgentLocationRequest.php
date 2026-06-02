<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAgentLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) ($this->user()?->isAgent());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'current_location' => ['required', 'string', 'max:255'],
        ];
    }
}
