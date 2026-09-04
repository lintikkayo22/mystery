<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInteractableRequest extends FormRequest
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
        return [
            'name'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|in:object,door,container,decoration',
            'position_x'  => 'required|numeric|min:0|max:100',
            'position_y'  => 'required|numeric|min:0|max:100',
            'width'       => 'required|numeric|min:0|max:100',
            'height'      => 'required|numeric|min:0|max:100',
            'status'      => 'required|in:draft,published',
        ];
    }
}
