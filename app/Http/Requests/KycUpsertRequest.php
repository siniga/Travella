<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KycUpsertRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules() {
        return [
          'passport_id'      => ['required','string','max:64', 'regex:/^[A-Z0-9<]+$/i'],
          'passport_country' => ['nullable','string','size:2'],
          'arrival_date'     => ['required','date','after_or_equal:today'],
          'departure_date'   => ['required','date','after:arrival_date'],
          'reason'           => ['nullable','string','max:100'],
        ];
      }
      public function prepareForValidation() {
        if ($this->passport_id) {
          $this->merge(['passport_id' => strtoupper(trim($this->passport_id))]);
        }
      }
      
}
