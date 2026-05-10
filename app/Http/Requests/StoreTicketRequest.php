<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'subject' => 'required|string|max:255|min:3',
            'message' => 'required|string|max:5000|min:10',
            'category' => 'required|in:order,payment,technical,other',
            'order_id' => 'nullable|integer|exists:orders,id',
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => 'Please enter a subject.',
            'subject.min' => 'Subject must be at least 3 characters.',
            'subject.max' => 'Subject cannot exceed 255 characters.',
            'message.required' => 'Please describe your issue.',
            'message.min' => 'Message must be at least 10 characters.',
            'message.max' => 'Message cannot exceed 5000 characters.',
            'category.required' => 'Please select a category.',
            'category.in' => 'Invalid category selected.',
            'order_id.exists' => 'Selected order not found.',
        ];
    }
}
