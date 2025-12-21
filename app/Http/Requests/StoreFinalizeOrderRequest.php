<?php

    // app/Http/Requests/StoreFinalizeOrderRequest.php
    namespace App\Http\Requests;
    
    use Illuminate\Foundation\Http\FormRequest;
    
    class StoreFinalizeOrderRequest extends FormRequest
    {
        public function authorize(): bool { return true; }
    
        public function rules(): array
        {
            return [
                'draft_id' => ['required','string'],
                'user_id'  => ['required','integer','exists:users,id'],
    
                // KYC now required at finalize time
                'kyc.passport_id'       => ['required','string','max:50'],
                'kyc.passport_country'  => ['required','string','size:2'],
                'kyc.nationality'       => ['required','string','max:80'],
                'kyc.gender'            => ['required','in:Male,Female,Other'],
                'kyc.reason_for_travel' => ['required','string','max:120'],
    
                // Optional payment data (if you want to mark as paid)
                'payment.status'        => ['nullable','in:paid,pending'],
                'payment.reference'     => ['nullable','string','max:120'],
                'payment.method'        => ['nullable','string','max:50'],
                'payment.paid_at'       => ['nullable','date'],
            ];
        }
    }

