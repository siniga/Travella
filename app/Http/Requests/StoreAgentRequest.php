<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) ($this->user()?->isAdmin());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id', 'unique:agent_location,user_id'],
            'name' => ['required_without:user_id', 'string', 'max:100'],
            'email' => [
                'required_without:user_id',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => ['required_without:user_id', 'string', 'min:8'],
            'phone' => ['required', 'string', 'max:32'],
            'work_station' => ['required', 'string', 'max:255'],
            'current_location' => ['nullable', 'string', 'max:255'],
        ];
    }
}
