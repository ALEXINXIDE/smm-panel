<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $rules = [
            'service_id' => 'required|integer|exists:services,id',
            'link'       => 'required|url|max:500',
            'quantity'   => 'required|integer|min:1',
        ];

        if ($this->filled('service_id')) {
            $service = \App\Models\Service::find($this->service_id);
            if ($service) {
                $rules['quantity'] = "required|integer|min:{$service->min}|max:{$service->max}";
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'service_id.required' => 'Please select a service.',
            'service_id.exists' => 'Selected service no longer exists.',
            'link.required' => 'Please enter the target link.',
            'link.url' => 'Please enter a valid URL.',
            'quantity.required' => 'Please enter the quantity.',
            'quantity.integer' => 'Quantity must be a whole number.',
            'quantity.min' => 'Minimum quantity is 1.',
            'quantity.max' => 'Maximum quantity is 10 million.',
        ];
    }
}
