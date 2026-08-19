<?php

namespace App\Http\Requests\Delivery;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryRequest extends FormRequest
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
            'description' => [
                'required',
                'string',
                'max:5000',
                'min:10',
            ],

            'files' => [
                'required',
                'array',
                'max:3',
            ],

            'files.*' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp, pdf,doc,docx,xls,xlsx,zip,rar',
                'max:20240',
            ],
        ];
    }
}
