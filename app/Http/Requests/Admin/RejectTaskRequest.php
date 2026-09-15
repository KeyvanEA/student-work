<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RejectTaskRequest extends FormRequest
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
            'rejection_reason' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ]
        ];
    }
    public function messages(){
        return ['rejection_reason.required' => 'برای رد کردن تسک باید دلیل آن را ذکر کنید.',
                'rejection_reason.min' => 'دلیل شما باید حداقل 10 کاراکتر داشته باشد',
                'rejection_reason.max' => 'دلیل شما باید حداکثر 5000 کاراکتر داشته باشد'
                ];
    }
}
