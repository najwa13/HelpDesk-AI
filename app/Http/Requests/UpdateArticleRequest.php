<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titre' => ['sometimes', 'required', 'string', 'max:255'],
            'contenu' => ['sometimes', 'required', 'string'],
            'categorie_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:categories,id',
            ],
            'published_at' => [
                'sometimes',
                'nullable',
                'date',
            ],
        ];
    }
}
