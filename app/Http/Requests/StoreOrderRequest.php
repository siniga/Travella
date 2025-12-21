<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow authenticated users to create orders
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'draft_id' => 'required|string|max:50|unique:orders,draft_id',
            'user_id' => 'required|integer|exists:users,id',
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|max:255',
            
            // KYC validation
            'kyc.passport_id' => 'required|string|max:50',
            'kyc.passport_country' => 'required|string|size:2',
            'kyc.nationality' => 'required|string|max:100',
            'kyc.gender' => 'required|in:Male,Female,Other',
            'kyc.reason_for_travel' => 'required|string|max:100',
            
            // Trip validation
            'trip.destination_country' => 'required|string|max:100',
            'trip.arrival_date' => 'required|date|after:today',
            'trip.departure_date' => 'required|date|after:trip.arrival_date',
            'trip.duration_days' => 'required|integer|min:1',
            
            // Items validation
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:bundle,service',
            'items.*.bundle_id' => 'nullable|integer|exists:bundles,id',
            'items.*.bundle_name' => 'required|string|max:120',
            'items.*.data_amount' => 'nullable|integer|min:0',
            'items.*.validity_days' => 'nullable|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.currency' => 'required|string|size:3',
            
            // Pricing validation
            'pricing.subtotal' => 'required|numeric|min:0',
            'pricing.discount_amount' => 'nullable|numeric|min:0',
            'pricing.discount_code' => 'nullable|string|max:50',
            'pricing.total_amount' => 'required|numeric|min:0',
            'pricing.currency' => 'required|string|size:3',
            
            // Order metadata validation
            'order_metadata.source' => 'nullable|string|max:50',
            'order_metadata.platform' => 'nullable|string|max:50',
            'order_metadata.created_at' => 'nullable|date',
            'order_metadata.status' => 'nullable|in:pending_payment,paid,processing,completed,cancelled',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'draft_id.unique' => 'This draft ID already exists.',
            'user_id.exists' => 'The specified user does not exist.',
            'kyc.passport_country.size' => 'Passport country must be a 2-letter country code.',
            'trip.arrival_date.after' => 'Arrival date must be in the future.',
            'trip.departure_date.after' => 'Departure date must be after arrival date.',
            'items.min' => 'At least one item is required.',
            'items.*.type.in' => 'Item type must be either bundle or service.',
            'pricing.currency.size' => 'Currency must be a 3-letter code.',
        ];
    }
}
