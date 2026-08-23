<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMysteryCaseRequest extends FormRequest
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
    public function rules(): array
    {
        $mysteryCase = $this->route('mysteryCase');
        return [
            'title' => ['required', 'string', 'max:255'],

            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('mystery_cases', 'slug')
                    ->ignore($mysteryCase->id),
            ],

            'description' => ['required', 'string'],

            'cover_image' => ['nullable', 'string', 'max:255'],

            'difficulty' => [
                'required',
                'in:easy,medium,hard',
            ],

            'status' => [
                'required',
                'in:draft,published,archived',
            ],
        ];
    }
}
