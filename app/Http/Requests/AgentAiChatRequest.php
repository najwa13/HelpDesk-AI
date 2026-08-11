<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgentAiChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => [
                'required',
                'string',
                'min:2',
                'max:2000',
            ],

            'conversation_id' => [
                'nullable',
                'string',
                'size:36',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Le message est obligatoire.',
            'message.string' => 'Le message doit être une chaîne de caractères.',
            'message.min' => 'Le message est trop court.',
            'message.max' => 'Votre message dépasse la limite autorisée.',
            'conversation_id.string' => 'La conversation est invalide.',
            'conversation_id.size' => 'La conversation est invalide.',
        ];
    }
}
