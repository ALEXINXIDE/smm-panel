<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReplyTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'message' => 'required|string|max:5000|min:3',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Please enter a message.',
            'message.min' => 'Message must be at least 3 characters.',
            'message.max' => 'Message cannot exceed 5000 characters.',
        ];
    }
}
