<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResolveComplaintRequest extends FormRequest
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
            'decision' => [
                'required',
                'string',
                Rule::in(['valid', 'invalid']),
            ],

            'action' => [
                'required_if:decision,valid',
                'prohibited_if:decision,invalid',
                'nullable',
                'string',
                Rule::in(['revision', 'cancel']),
            ],

            'admin_response' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
        ];
    }
}
